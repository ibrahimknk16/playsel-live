<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Sunucu taraflı GA4 (Measurement Protocol) ve Meta Conversions API gönderimi.
 * Olaylar sipariş durumuna göre gönderilir; her olay sipariş başına yalnızca bir kez (analytics_events_sent).
 */
class Analytics_tracker
{
	private $CI;
	// Yeniden gönderimde olaylara siparişin gerçek oluşturulma zamanı yazılır.
	public $useOrderTime = false;
	// Yeniden gönderimde Meta CAPI atlanır; yalnızca GA4'e gönderilir.
	public $ga4Only = false;
	// Tanı: GA4 /debug/mp/collect ucuna gönderir (olay kaydedilmez, doğrulama mesajları döner).
	public $ga4Debug = false;
	// Tanı: Meta Test Events kodu; doluysa olaylar gerçek veri olarak sayılmaz.
	public $metaTestCode = '';
	// Tanı: her giden isteğin HTTP kodu ve yanıtı (secret içermez).
	public $responseLog = [];

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	// Sipariş oluşturulurken tarayıcı kimliklerini yakalar (order_tracking sütununa yazılır).
	public function captureTracking()
	{
		$tracking = [
			'ip' => function_exists('getIp') ? getIp() : ($_SERVER['REMOTE_ADDR'] ?? ''),
			'ua' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
			'url' => substr((string)(site_url() ?: ''), 0, 255),
		];

		if (!empty($_COOKIE['_ga']) && preg_match('/^GA\d+\.\d+\.(\d+\.\d+)$/', (string)$_COOKIE['_ga'], $m)) {
			$tracking['ga_client_id'] = $m[1];
		}
		foreach ($_COOKIE as $name => $value) {
			if (strpos((string)$name, '_ga_') !== 0 || !is_string($value)) {
				continue;
			}
			if (preg_match('/^GS1\.\d+\.(\d+)\./', $value, $m) || preg_match('/^GS2\.\d+\.s(\d+)\$/', $value, $m)) {
				$tracking['ga_session_id'] = (int)$m[1];
				break;
			}
		}
		if (!empty($_COOKIE['_fbp']) && preg_match('/^fb\.\d\.\d+\.\d+$/', (string)$_COOKIE['_fbp'])) {
			$tracking['fbp'] = (string)$_COOKIE['_fbp'];
		}
		if (!empty($_COOKIE['_fbc']) && preg_match('/^fb\.\d\.\d+\.[A-Za-z0-9_-]+$/', (string)$_COOKIE['_fbc'])) {
			$tracking['fbc'] = (string)$_COOKIE['_fbc'];
		}

		return json_encode($tracking, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	// Kilitli çalıştırma: aynı anda tek işlem; cron'lar bunu çağırır. Kilit alınamazsa null döner.
	public function runLocked($limit = 50)
	{
		$db = $this->CI->db;
		$lock = $db->query("SELECT GET_LOCK('analytics_events_cron', 0) AS got")->row();
		if (empty($lock) || (int)$lock->got !== 1) {
			return null;
		}

		$processed = 0;
		try {
			$this->responseLog = [];
			$processed = (int)$this->processPending($limit);
			$failed = 0;
			foreach ($this->responseLog as $r) {
				if ((int)$r['http'] < 200 || (int)$r['http'] >= 300) {
					$failed++;
				}
			}
			if ($processed > 0) {
				$db->insert('logs', [
					'log_controller' => 'analyticsEvents',
					'log_message' => 'İşlenen sipariş: ' . $processed . ' - Başarısız istek: ' . $failed,
					'log_error_code' => $failed > 0 ? 'analytics_cron_failed' : 'analytics_cron_ok',
				]);
			}
		} catch (Throwable $e) {
			log_message('error', 'Analytics tracker failed: ' . $e->getMessage());
			$db->insert('logs', [
				'log_controller' => 'analyticsEvents',
				'log_message' => substr($e->getMessage(), 0, 500),
				'log_error_code' => 'analytics_cron_exception',
			]);
		}
		$db->query("SELECT RELEASE_LOCK('analytics_events_cron')");
		return $processed;
	}

	private const ORDER_COLUMNS = 'o.order_id, o.order_reseller_id, o.order_product_id, o.order_status, 	o.order_sale_price_tl, o.order_sale_price, o.order_sale_price_currency, o.order_voucher, o.order_tracking, o.order_payment_info, o.order_created_at, p.product_name';
	// Gruptaki bekleyen satır bu süreden uzun takılı kalırsa teslim edilen satırlar beklenmeden gönderilir.
	private const GROUP_MAX_WAIT = 86400;

	// Cron tarafından çağrılır: teslim edilen sepetlerde tek satın alma, sonradan iptal olan satırlarda iptal/iade gönderir.
	public function processPending($limit = 50)
	{
		$db = $this->CI->db;
		$names = $this->eventNames();
		$processed = 0;

		$candidates = $db->select(self::ORDER_COLUMNS)
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('analytics_events_sent as s', "s.order_id = o.order_id AND s.event_key = 'purchase'", 'left', false)
			->where('o.order_status', 1)
			->where('o.order_tracking IS NOT NULL', null, false)
			->where('s.id IS NULL', null, false)
			->order_by('o.order_id', 'ASC')
			->limit($limit * 4)->get()->result();
		$handled = [];
		foreach ($candidates as $order) {
			if ($processed >= $limit) {
				break;
			}
			if (isset($handled[(int)$order->order_id])) {
				continue;
			}
			$group = $this->buildGroup($order);
			if ($group === null) {
				continue;
			}
			foreach ($group['rows'] as $row) {
				$handled[(int)$row->order_id] = true;
			}
			$this->dispatchGroup($group, 'purchase', $names['purchase'], 'Purchase');
			$processed += count($group['rows']);
		}

		// İptal/iade: yalnızca satın alma olayı gönderilmiş satırlar için, satır bazında.
		$hasTx = $this->hasTxColumn();
		$cancelled = $db->select(self::ORDER_COLUMNS . ($hasTx ? ', sp.transaction_id AS analytics_tx' : ''))
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('analytics_events_sent as sp', "sp.order_id = o.order_id AND sp.event_key = 'purchase'", 'inner', false)
			->join('analytics_events_sent as sr', "sr.order_id = o.order_id AND sr.event_key = 'refund'", 'left', false)
			->where('o.order_status', 2)
			->where('sr.id IS NULL', null, false)
			->order_by('o.order_id', 'ASC')
			->limit($limit)->get()->result();
		foreach ($cancelled as $order) {
			$group = $this->makeGroup([$order], !empty($order->analytics_tx) ? (string)$order->analytics_tx : (string)$order->order_id);
			$this->dispatchGroup($group, 'cancel', $names['cancel'], 'Cancel');
			$this->dispatchGroup($group, 'refund', $names['refund'], 'Refund');
			$processed++;
		}

		return $processed;
	}

	private function hasTxColumn()
	{
		static $has = null;
		if ($has === null) {
			$has = $this->CI->db->field_exists('transaction_id', 'analytics_events_sent');
		}
		return $has;
	}

	private function bundleReference($paymentInfo)
	{
		$info = json_decode((string)$paymentInfo, true);
		return is_array($info) ? trim((string)($info['pricing_snapshot']['bundle_reference'] ?? '')) : '';
	}

	// Aynı sepetin (bundle_reference) satırlarını toplar. Gruptaki satırlardan biri hâlâ sonuçlanmadıysa null döner.
	private function buildGroup($order, $eventKey = 'purchase')
	{
		$ref = $this->bundleReference($order->order_payment_info);
		if ($ref === '') {
			return $this->makeGroup([$order], (string)$order->order_id);
		}

		$db = $this->CI->db;
		$siblings = $db->select(self::ORDER_COLUMNS)
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->where('o.order_reseller_id', (int)$order->order_reseller_id)
			->like('o.order_payment_info', $ref)
			->order_by('o.order_id', 'ASC')
			->get()->result();
		$siblings = array_values(array_filter($siblings, function ($s) use ($ref) {
			return $this->bundleReference($s->order_payment_info) === $ref;
		}));
		if (empty($siblings)) {
			$siblings = [$order];
		}

		// Sonuçlanmış durumlar: 1 teslim, 2 iptal/iade, 12 ödeme başarısız.
		$oldest = time();
		foreach ($siblings as $s) {
			$oldest = min($oldest, strtotime((string)$s->order_created_at) ?: time());
		}
		$stale = (time() - $oldest) > self::GROUP_MAX_WAIT;
		foreach ($siblings as $s) {
			if (!$stale && !in_array((int)$s->order_status, [1, 2, 12], true)) {
				return null;
			}
		}

		$ids = array_map(function ($s) {
			return (int)$s->order_id;
		}, $siblings);
		$sentRows = $db->select('order_id')->where('event_key', $eventKey)->where_in('order_id', $ids)->get('analytics_events_sent')->result();
		$sent = array_flip(array_map(function ($r) {
			return (int)$r->order_id;
		}, $sentRows));
		$rows = array_values(array_filter($siblings, function ($s) use ($sent) {
			return (int)$s->order_status === 1 && !isset($sent[(int)$s->order_id]);
		}));
		if (empty($rows)) {
			return null;
		}
		return $this->makeGroup($rows, (string)$rows[0]->order_id);
	}

	// Sipariş satırının TL tutarı: order_sale_price_tl boşsa TL cinsinden order_sale_price'a düşer (admin raporlarıyla aynı mantık).
	public function orderValue($row)
	{
		$tl = round((float)($row->order_sale_price_tl ?? 0), 2);
		if ($tl > 0) {
			return $tl;
		}
		$currency = strtoupper(trim((string)($row->order_sale_price_currency ?? '')));
		if (in_array($currency, ['TRY', 'TL'], true)) {
			return round((float)($row->order_sale_price ?? 0), 2);
		}
		return 0.0;
	}

	// Satırlardan tek işlem (transaction) oluşturur: toplam tutar ve ürün bazında birleştirilmiş items.
	private function makeGroup(array $rows, $transactionId)
	{
		$items = [];
		$value = 0.0;
		foreach ($rows as $row) {
			$price = $this->orderValue($row);
			$value += $price;
			$key = (string)$row->order_product_id;
			if (!isset($items[$key])) {
				$items[$key] = [
					'item_id' => $key,
					'item_name' => (string)($row->product_name ?: 'Dijital Ürün'),
					'quantity' => 0,
					'total' => 0.0,
				];
			}
			$items[$key]['quantity']++;
			$items[$key]['total'] += $price;
		}
		$items = array_values(array_map(function ($i) {
			return [
				'item_id' => $i['item_id'],
				'item_name' => $i['item_name'],
				'quantity' => $i['quantity'],
				'price' => round($i['total'] / $i['quantity'], 2),
			];
		}, $items));

		return [
			'tx' => (string)$transactionId,
			'rows' => $rows,
			'lead' => $rows[0],
			'value' => round($value, 2),
			'items' => $items,
		];
	}
	private function eventNames()
	{
		$names = [];
		foreach (['purchase', 'cancel', 'refund'] as $key) {
			$value = (string)site_setting('analytics_' . $key . '_event_name', $key);
			$names[$key] = preg_match('/^[A-Za-z][A-Za-z0-9_]{0,39}$/', $value) ? $value : $key;
		}
		return $names;
	}

	// Aynı olay aynı satırlarda tekrar gönderilmez; gönderim başarısızsa kayıtlar silinir ve sonraki cron'da denenir.
	private function dispatchGroup(array $group, $eventKey, $gaName, $metaName)
	{
		$db = $this->CI->db;
		$hasTx = $this->hasTxColumn();
		$now = date('Y-m-d H:i:s');
		$ids = [];
		foreach ($group['rows'] as $i => $row) {
			$ids[] = (int)$row->order_id;
			if ($hasTx) {
				$db->query('INSERT IGNORE INTO analytics_events_sent (order_id, event_key, created_at, transaction_id) VALUES (?, ?, ?, ?)', [(int)$row->order_id, $eventKey, $now, $group['tx']]);
			} else {
				$db->query('INSERT IGNORE INTO analytics_events_sent (order_id, event_key, created_at) VALUES (?, ?, ?)', [(int)$row->order_id, $eventKey, $now]);
			}
			if ($i === 0 && $db->affected_rows() < 1) {
				return false;
			}
		}

		$lead = $group['lead'];
		if ($eventKey === 'purchase' && (float)$group['value'] <= 0) {
			$db->insert('logs', [
				'log_controller' => 'analyticsEvents',
				'log_message' => 'Sıfır tutarlı purchase gönderiliyor. İşlem: ' . $group['tx'] . ' - order_sale_price_tl/order_sale_price kontrol edin.',
				'log_error_code' => 'analytics_zero_value',
			]);
		}
		$tracking = json_decode((string)$lead->order_tracking, true);
		$tracking = is_array($tracking) ? $tracking : [];
		$eventTime = time();
		if ($this->useOrderTime && !empty($lead->order_created_at)) {
			$eventTime = strtotime((string)$lead->order_created_at) ?: $eventTime;
		}

		// null: kanal yapılandırılmamış (atlanır), false: gönderim başarısız, true: gönderildi.
		$results = [$this->sendGa4($group, $gaName, $tracking, $eventTime)];
		if ($metaName !== null && !$this->ga4Only) {
			$results[] = $this->sendMeta($group, $metaName, $gaName, $eventKey, $tracking, $eventTime);
		}
		$delivered = in_array(true, $results, true);
		$failed = in_array(false, $results, true);

		// Hiçbir kanala gönderilemediyse ya da biri başarısızsa kayıtlar silinir; sonraki çalışmada yeniden denenir.
		if (!$delivered || $failed) {
			$db->where('event_key', $eventKey)->where_in('order_id', $ids)->delete('analytics_events_sent');
			return false;
		}
		return true;
	}

	// Tanı: son teslim edilen siparişlerden örnek alıp kanallara gönderir; gönderim kaydı yazmaz/silmez.
	public function diagnose($limit = 2)
	{
		$orders = $this->CI->db
			->select('o.order_id, o.order_reseller_id, o.order_product_id, o.order_status, o.order_sale_price_tl, o.order_sale_price, o.order_sale_price_currency, o.order_voucher, o.order_tracking, o.order_created_at, p.product_name')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->where('o.order_status', 1)
			->where('o.order_tracking IS NOT NULL', null, false)
			->order_by('o.order_id', 'DESC')->limit((int)$limit)->get()->result();

		$this->responseLog = [];
		$names = $this->eventNames();
		foreach ($orders as $order) {
			$tracking = json_decode((string)$order->order_tracking, true);
			$tracking = is_array($tracking) ? $tracking : [];
			$group = $this->makeGroup([$order], (string)$order->order_id);
			$this->sendGa4($group, $names['purchase'], $tracking, time());
			$this->sendMeta($group, 'Purchase', $names['purchase'], 'purchase', $tracking, time());
		}
		return ['orders_sampled' => count($orders), 'responses' => $this->responseLog];
	}

	// Tanı: son sipariş satırlarının analytics durumunu ve son cron günlüklerini listeler (secret içermez).
	public function status($limit = 25)
	{
		$rows = $this->CI->db->query(
			"SELECT o.order_id, o.order_status, o.order_type, o.order_created_at, o.order_sale_price_tl, o.order_sale_price, o.order_sale_price_currency,
				(o.order_tracking IS NOT NULL) AS has_tracking,
				(SELECT COUNT(*) FROM analytics_events_sent s WHERE s.order_id = o.order_id AND s.event_key = 				'purchase') AS server_sent
			FROM orders o WHERE o.order_type = 3 ORDER BY o.order_id DESC LIMIT " . (int)$limit
		)->result_array();
		$logs = $this->CI->db->select('log_error_code, log_message, log_created_at')
			->where('log_controller', 'analyticsEvents')->order_by('log_created_at', 'DESC')->limit(10)->get('logs')->result_array();
		return ['recent_orders' => $rows, 'cron_logs' => $logs, 'server_time' => date('Y-m-d H:i:s')];
	}

	// Tanı: gerçek /mp/collect ucuna şu anki zamanla tek bir test olayı gönderir (Gerçek zamanlı raporda görünür).
	public function livePing()
	{
		$this->responseLog = [];
		$measurementId = getenv('GA4_MEASUREMENT_ID');
		$apiSecret = getenv('GA4_API_SECRET');
		if (empty($measurementId) || empty($apiSecret)) {
			return ['sent' => false, 'reason' => 'GA4 env eksik'];
		}
		$payload = [
			'client_id' => '1234567890.' . time(),
			'events' => [['name' => 'server_ping_test', 'params' => ['engagement_time_msec' => 100, 'session_id' => (string)time(), 'debug_mode' => 1]]],
		];
		$url = 'https://www.google-analytics.com/mp/collect?measurement_id=' . rawurlencode($measurementId) . '&api_secret=' . rawurlencode($apiSecret);
		$ok = $this->postJson($url, $payload, 'GA4');
		return [
			'sent' => $ok,
			'measurement_id' => $measurementId,
			'api_secret_length' => strlen((string)$apiSecret),
			'event' => 'server_ping_test',
			'responses' => $this->responseLog,
		];
	}

	// Yapılandırma durumu (secret değerleri döndürülmez).
	public function channelStatus()
	{
		$serverId = strtoupper(trim((string)getenv('GA4_MEASUREMENT_ID')));
		$siteTagId = strtoupper(trim((string)site_setting('google_analytics_id')));
		return [
			// Sunucu (Measurement Protocol) ve tarayıcı etiketi aynı GA4 veri akışına yazmalı; farklıysa raporlar boş/yarım kalır.
			'ga4_server_measurement_id' => $serverId,
			'ga4_site_tag_id' => $siteTagId,
			'ga4_ids_match' => $serverId !== '' && $serverId === $siteTagId,
			'ga4' => !empty(getenv('GA4_MEASUREMENT_ID')) && !empty(getenv('GA4_API_SECRET')),
			'meta' => $this->metaPixelId() !== '' && !empty(getenv('META_CAPI_TOKEN')),
		];
	}

	// META_PIXEL_ID env değeri öncelikli; yoksa admin ayarı kullanılır. Geçersizse boş döner.
	private function metaPixelId()
	{
		$id = trim((string)getenv('META_PIXEL_ID'));
		if ($id === '') {
			$id = trim((string)site_setting('meta_pixel_id'));
		}
		return preg_match('/^[0-9]+$/', $id) ? $id : '';
	}

	private function sendGa4(array $group, $eventName, array $tracking, $eventTime)
	{
		$measurementId = getenv('GA4_MEASUREMENT_ID');
		$apiSecret = getenv('GA4_API_SECRET');
		if (empty($measurementId) || empty($apiSecret)) {
			log_message('warning', 'GA4 credentials are not configured; analytics event skipped.');
			return null;
		}
		$order = $group['lead'];

		$params = [
			'transaction_id' => $group['tx'],
			'value' => $group['value'],
			'currency' => 'TRY',
			'items' => $group['items'],
			'engagement_time_msec' => 1,
		];
		if (!empty($tracking['ga_session_id'])) {
			$params['session_id'] = (string)$tracking['ga_session_id'];
		}

		$payload = [
			'client_id' => $tracking['ga_client_id'] ?? (hexdec(substr(md5('order-' . $order->order_id), 0, 8)) . '.' . (int)$order->order_id),
			'events' => [['name' => $eventName, 'params' => $params]],
		];
		if ((int)$order->order_reseller_id > 0) {
			$payload['user_id'] = (string)$order->order_reseller_id;
		}
		// GA4 geçmiş zamanlı olayı en fazla 72 saat geriye kabul eder; daha eskisi sınırlanır.
		if ($this->useOrderTime) {
			$payload['timestamp_micros'] = (string)(max($eventTime, time() - 71 * 3600) * 1000000);
		}

		$endpoint = $this->ga4Debug ? '/debug/mp/collect' : '/mp/collect';
		$url = 'https://www.google-analytics.com' . $endpoint . '?measurement_id=' . rawurlencode($measurementId) . '&api_secret=' . rawurlencode($apiSecret);
		return $this->postJson($url, $payload, 'GA4');
	}

	private function sendMeta(array $group, $metaName, $gaName, $eventKey, array $tracking, $eventTime)
	{
		$pixelId = $this->metaPixelId();
		$token = getenv('META_CAPI_TOKEN');
		if ($pixelId === '' || empty($token)) {
			return null;
		}
		$order = $group['lead'];
		$user = $this->CI->db->select('user_email, user_phone')->where('user_id', (int)$order->order_reseller_id)->get('users')->row();
		$userData = [
			'external_id' => [hash('sha256', (string)$order->order_reseller_id)],
			'country' => [hash('sha256', 'tr')],
		];
		if (!empty($tracking['ip'])) {
			$userData['client_ip_address'] = $tracking['ip'];
		}
		if (!empty($tracking['ua'])) {
			$userData['client_user_agent'] = $tracking['ua'];
		}
		foreach (['fbp', 'fbc'] as $key) {
			if (!empty($tracking[$key])) {
				$userData[$key] = $tracking[$key];
			}
		}
		if ($user && !empty($user->user_email)) {
			$userData['em'] = [hash('sha256', strtolower(trim($user->user_email)))];
		}
		$phone = $user ? preg_replace('/\D+/', '', (string)$user->user_phone) : '';
		if ($phone !== '') {
			$userData['ph'] = [hash('sha256', $phone)];
		}

		$isStandard = in_array($metaName, ['Purchase'], true);
		$event = [
			'event_name' => $isStandard ? $metaName : $gaName,
			'event_time' => $this->useOrderTime ? max($eventTime, time() - 6 * 86400) : time(),
			'event_id' => $eventKey . '_' . $group['tx'],
			'action_source' => 'website',
			'event_source_url' => $tracking['url'] ?? site_url(),
			'user_data' => $userData,
			'custom_data' => [
				'value' => $group['value'],
				'currency' => 'TRY',
				'order_id' => $group['tx'],
				'content_ids' => array_column($group['items'], 'item_id'),
				'contents' => array_map(function ($i) {
					return ['id' => $i['item_id'], 'quantity' => $i['quantity'], 'item_price' => $i['price']];
				}, $group['items']),
				'content_type' => 'product',
				'num_items' => count($group['rows']),
			],
		];

		$url = 'https://graph.facebook.com/v19.0/' . $pixelId . '/events?access_token=' . rawurlencode($token);
		$body = ['data' => [$event]];
		if ($this->metaTestCode !== '') {
			$body['test_event_code'] = $this->metaTestCode;
		}
		return $this->postJson($url, $body, 'Meta CAPI');
	}

	private function postJson($url, array $payload, $label)
	{
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=UTF-8'],
			CURLOPT_POSTFIELDS => json_encode($payload),
			CURLOPT_TIMEOUT => 10,
		]);
		$body = curl_exec($ch);
		$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);

		$ev = $payload['events'][0] ?? null;
		$mev = $payload['data'][0] ?? null;
		$this->responseLog[] = [
			'channel' => $label,
			'event' => $ev['name'] ?? ($mev['event_name'] ?? null),
			'order_id' => $ev['params']['transaction_id'] ?? ($mev['custom_data']['order_id'] ?? null),
			'value' => $ev['params']['value'] ?? ($mev['custom_data']['value'] ?? null),
			'client_id' => $payload['client_id'] ?? null,
			'session_id' => $ev['params']['session_id'] ?? null,
			'time' => isset($payload['timestamp_micros']) ? date('Y-m-d H:i:s', (int)($payload['timestamp_micros'] / 1000000)) : ($mev ? date('Y-m-d H:i:s', $mev['event_time']) : null),
			'http' => $code,
			'curl_error' => $error,
			'response' => is_string($body) ? substr($body, 0, 300) : null,
		];
		if (count($this->responseLog) > 400) {
			array_shift($this->responseLog);
		}

		// Debug ucu, geçersiz payload'ı 200 ile döner; validationMessages doluysa başarısız say.
		if ($label === 'GA4' && $this->ga4Debug && is_string($body)) {
			$decoded = json_decode($body, true);
			return $code === 200 && is_array($decoded) && empty($decoded['validationMessages']);
		}

		if ($body === false || $code < 200 || $code >= 300) {
			log_message('error', $label . ' event failed. HTTP ' . $code . ' ' . $error . ' ' . (is_string($body) ? substr($body, 0, 300) : ''));
			return false;
		}
		return true;
	}
}
