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

	// Cron tarafından çağrılır: teslim edilen siparişlerde satın alma, sonradan iptal olanlarda iptal/iade gönderir.
	public function processPending($limit = 50)
	{
		$db = $this->CI->db;
		$select = 'o.order_id, o.order_reseller_id, o.order_product_id, o.order_status, o.order_sale_price_tl, o.order_voucher, o.order_tracking, o.order_created_at, p.product_name';

		$purchases = $db->select($select)
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('analytics_events_sent as s', "s.order_id = o.order_id AND s.event_key = 'purchase'", 'left', false)
			->where('o.order_status', 1)
			->where('o.order_tracking IS NOT NULL', null, false)
			->where('s.id IS NULL', null, false)
			->limit($limit)->get()->result();
		foreach ($purchases as $order) {
			$this->sendPurchase($order);
		}

		$cancelled = $db->select($select)
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'left')
			->join('analytics_events_sent as sp', "sp.order_id = o.order_id AND sp.event_key = 'purchase'", 'inner', false)
			->join('analytics_events_sent as sr', "sr.order_id = o.order_id AND sr.event_key = 'refund'", 'left', false)
			->where('o.order_status', 2)
			->where('sr.id IS NULL', null, false)
			->limit($limit)->get()->result();
		foreach ($cancelled as $order) {
			$this->sendCancellation($order);
		}

		return count($purchases) + count($cancelled);
	}

	private function sendPurchase($order)
	{
		$names = $this->eventNames();
		$this->dispatch($order, 'purchase', $names['purchase'], 'Purchase');
	}

	private function sendCancellation($order)
	{
		$names = $this->eventNames();
		$this->dispatch($order, 'cancel', $names['cancel'], 'Cancel');
		$this->dispatch($order, 'refund', $names['refund'], 'Refund');
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

	// Aynı olay aynı siparişte tekrar gönderilmez; gönderim başarısızsa kayıt silinir ve sonraki cron'da denenir.
	private function dispatch($order, $eventKey, $gaName, $metaName)
	{
		$db = $this->CI->db;
		$db->query(
			'INSERT IGNORE INTO analytics_events_sent (order_id, event_key, created_at) VALUES (?, ?, ?)',
			[(int)$order->order_id, $eventKey, date('Y-m-d H:i:s')]
		);
		if ($db->affected_rows() < 1) {
			return;
		}

		$tracking = json_decode((string)$order->order_tracking, true);
		$tracking = is_array($tracking) ? $tracking : [];
		$value = round((float)$order->order_sale_price_tl, 2);
		$eventTime = time();
		if ($this->useOrderTime && !empty($order->order_created_at)) {
			$eventTime = strtotime((string)$order->order_created_at) ?: $eventTime;
		}

		// null: kanal yapılandırılmamış (atlanır), false: gönderim başarısız, true: gönderildi.
		$results = [$this->sendGa4($order, $gaName, $value, $tracking, $eventTime)];
		if ($metaName !== null) {
			$results[] = $this->sendMeta($order, $metaName, $gaName, $eventKey, $value, $tracking, $eventTime);
		}
		$delivered = in_array(true, $results, true);
		$failed = in_array(false, $results, true);

		// Hiçbir kanala gönderilemediyse ya da biri başarısızsa kayıt silinir; sonraki çalışmada yeniden denenir.
		if (!$delivered || $failed) {
			$db->where(['order_id' => (int)$order->order_id, 'event_key' => $eventKey])->delete('analytics_events_sent');
		}
	}

	// Tanı: son teslim edilen siparişlerden örnek alıp kanallara gönderir; gönderim kaydı yazmaz/silmez.
	public function diagnose($limit = 2)
	{
		$orders = $this->CI->db
			->select('o.order_id, o.order_reseller_id, o.order_product_id, o.order_status, o.order_sale_price_tl, o.order_voucher, o.order_tracking, o.order_created_at, p.product_name')
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
			$value = round((float)$order->order_sale_price_tl, 2);
			$this->sendGa4($order, $names['purchase'], $value, $tracking, time());
			$this->sendMeta($order, 'Purchase', $names['purchase'], 'purchase', $value, $tracking, time());
		}
		return ['orders_sampled' => count($orders), 'responses' => $this->responseLog];
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
			'events' => [['name' => 'server_ping_test', 'params' => ['engagement_time_msec' => 1, 'session_id' => (string)time()]]],
		];
		$url = 'https://www.google-analytics.com/mp/collect?measurement_id=' . rawurlencode($measurementId) . '&api_secret=' . rawurlencode($apiSecret);
		$ok = $this->postJson($url, $payload, 'GA4');
		return ['sent' => $ok, 'measurement_id' => $measurementId, 'event' => 'server_ping_test', 'responses' => $this->responseLog];
	}

	// Yapılandırma durumu (secret değerleri döndürülmez).
	public function channelStatus()
	{
		return [
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

	private function sendGa4($order, $eventName, $value, array $tracking, $eventTime)
	{
		$measurementId = getenv('GA4_MEASUREMENT_ID');
		$apiSecret = getenv('GA4_API_SECRET');
		if (empty($measurementId) || empty($apiSecret)) {
			log_message('warning', 'GA4 credentials are not configured; analytics event skipped.');
			return null;
		}

		$params = [
			'transaction_id' => (string)$order->order_id,
			'value' => $value,
			'currency' => 'TRY',
			'items' => [[
				'item_id' => (string)$order->order_product_id,
				'item_name' => (string)($order->product_name ?: 'Dijital Ürün'),
				'price' => $value,
				'quantity' => 1,
			]],
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

	private function sendMeta($order, $metaName, $gaName, $eventKey, $value, array $tracking, $eventTime)
	{
		$pixelId = $this->metaPixelId();
		$token = getenv('META_CAPI_TOKEN');
		if ($pixelId === '' || empty($token)) {
			return null;
		}

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
			'event_id' => $eventKey . '_' . $order->order_id,
			'action_source' => 'website',
			'event_source_url' => $tracking['url'] ?? site_url(),
			'user_data' => $userData,
			'custom_data' => [
				'value' => $value,
				'currency' => 'TRY',
				'order_id' => (string)$order->order_id,
				'content_ids' => [(string)$order->order_product_id],
				'content_type' => 'product',
				'num_items' => 1,
			],
		];
		if ($eventKey === 'purchase') {
			$event['event_id'] = 'purchase_' . $order->order_id;
		}

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
