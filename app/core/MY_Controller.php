<?php defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . "third_party/MX/Controller.php";
class MY_Controller extends MX_Controller
{
	protected function orderApiLog($orderId, $phase, $direction, $request = null, $response = null, array $meta = [])
	{
		$payload = [
			'version' => 1,
			'phase' => (string)$phase,
			'direction' => (string)$direction,
			'request' => $this->sanitizeOrderApiLogValue($request),
			'response' => $this->sanitizeOrderApiLogValue($response),
			'meta' => $this->sanitizeOrderApiLogValue($meta),
		];
		$message = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		if ($message === false) {
			$message = json_encode([
				'version' => 1,
				'phase' => (string)$phase,
				'direction' => (string)$direction,
				'error' => 'order api log serialization failed',
			]);
		}

		$this->db->insert('logs', [
			'log_controller' => 'order.api',
			'log_message' => substr((string)$message, 0, 12000),
			'log_error_code' => 54541571,
			'log_order_id' => (int)$orderId > 0 ? (int)$orderId : null,
		]);
	}

	private function sanitizeOrderApiLogValue($value, $key = '')
	{
		$sensitiveKeys = [
			'cardnumber', 'card_number', 'cc_number', 'cvc', 'cvv',
			'secret', 'password', 'authorization', 'x-signature', 'apikey',
		];
		$normalizedKey = strtolower((string)$key);
		foreach ($sensitiveKeys as $sensitiveKey) {
			if ($normalizedKey === $sensitiveKey || strpos($normalizedKey, $sensitiveKey) !== false) {
				return '[REDACTED]';
			}
		}

		if (is_array($value)) {
			$sanitized = [];
			foreach ($value as $childKey => $childValue) {
				$sanitized[$childKey] = $this->sanitizeOrderApiLogValue($childValue, (string)$childKey);
			}
			return $sanitized;
		}
		if (is_object($value)) {
			return $this->sanitizeOrderApiLogValue((array)$value, $key);
		}
		if (is_string($value)) {
			return strlen($value) > 4000 ? substr($value, 0, 4000) . '...[truncated]' : $value;
		}

		return $value;
	}

	// CLI çalıştırmaya izin verir; HTTP isteğinde env'deki gizli anahtar header veya query ile gelmelidir.
	// Dosya cache tabanlı sabit pencere sayacı; true dönerse istek serbesttir.
	protected function throttleHit($key, $max, $windowSeconds)
	{
		$this->load->driver('cache', ['adapter' => 'file']);
		$cacheKey = 'thr_' . md5($key);
		$entry = $this->cache->get($cacheKey);
		if (!is_array($entry) || ($entry['expires'] ?? 0) <= time()) {
			$entry = ['count' => 0, 'expires' => time() + (int)$windowSeconds];
		}
		if ($entry['count'] >= (int)$max) {
			return false;
		}
		$entry['count']++;
		$this->cache->save($cacheKey, $entry, max(1, $entry['expires'] - time()));
		return true;
	}

	protected function throttleClear($key)
	{
		$this->load->driver('cache', ['adapter' => 'file']);
		$this->cache->delete('thr_' . md5($key));
	}

	protected function requireInternalAccessKey($envName, $headerName, $queryName)
	{
		if (PHP_SAPI === 'cli') {
			return;
		}

		$expected = (string)getenv($envName);
		if ($expected === '') {
			log_message('error', $envName . ' tanımlı değil; ' . $this->router->fetch_class() . ' HTTP erişimi kapalı.');
			show_error('Servis yapılandırması eksik', 503);
		}

		$serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));
		$given = (string)($_SERVER[$serverKey] ?? $this->input->get($queryName) ?? '');
		if ($given === '' || !hash_equals($expected, $given)) {
			log_message('error', 'Yetkisiz iç servis erişimi: ' . $this->router->fetch_class() . '/' . $this->router->fetch_method() . ' IP=' . $this->input->ip_address());
			show_404();
		}
	}

	public function __construct()
	{
		parent::__construct();

		$this->output->set_header('X-Content-Type-Options: nosniff');
		$this->output->set_header('X-Frame-Options: SAMEORIGIN');
		$this->output->set_header('Referrer-Policy: strict-origin-when-cross-origin');
		
		if (!empty($_POST)) {
			array_walk_recursive($_POST, function (&$value, $key) {
				if (is_string($value)) {
					$value = trim($value);
					if (stripos($key, 'password') === false) {
						$value = strip_tags($value);
					}
				}
			});
		}

		$skipGetSanitize = in_array($this->router->fetch_class(), array('callback', 'cron', 'cron2'), true);
		if (!empty($_GET) && !$skipGetSanitize) {
			array_walk_recursive($_GET, function (&$value) {
				if (is_string($value)) {
					$value = strip_tags(trim($value));
				}
			});
		}

		// Yönlendirme parametresi yalnızca site içi göreli yol olabilir
		foreach (array('_POST', '_GET') as $superGlobal) {
			if (isset($GLOBALS[$superGlobal]['redirect'])) {
				$redirectValue = $GLOBALS[$superGlobal]['redirect'];
				if (!is_string($redirectValue) || !preg_match('#^/?[A-Za-z0-9][A-Za-z0-9\-_/.%?=&]*$#', $redirectValue) || strpos($redirectValue, '..') !== false || strpos($redirectValue, '//') !== false) {
					$GLOBALS[$superGlobal]['redirect'] = '';
				}
			}
		}

		$this->captureUserAcquisition();
		$this->userLogInsert();

		$userData = $this->session->userdata('user');
		if ($userData && !in_array($this->router->fetch_class(), array('auth', 'cron', 'cron2', 'callback'), true)) {
			$user = $this->db->select('user_session_version, user_status')
				->where('user_id', (int)$userData['user_id'])
				->get('users')->row();
			$sessionVersion = (int)($userData['user_session_version'] ?? 1);
			$restrictedNow = $user ? customerRestriction((int)$userData['user_id'], 'all') : null;
			if (!$user || (int)$user->user_status !== 1 || (int)$user->user_session_version !== $sessionVersion
				|| $restrictedNow) {
				$this->session->unset_userdata('user');
				$this->session->set_flashdata('alert', array(
					'type' => 'warning',
					'message' => ($restrictedNow || ($user && (int)$user->user_status !== 1))
						? restrictedAccountMessage($restrictedNow)
						: 'Oturumunuz sona erdi, lütfen tekrar giriş yapın.'
				));
				redirect(topupcenter() . (preg_match('/^[a-z]{2}-[A-Z]{2}$/', (string)$this->uri->segment(1)) ? $this->uri->segment(1) : 'tr-TR') . '/login');
			}
		}
	}

	private function captureUserAcquisition()
	{
		$sessionAttribution = $this->session->userdata('user_acquisition');
		$attribution = $sessionAttribution;
		if (!is_array($attribution)) {
			$cookieValue = (string)($_COOKIE['playsel_acquisition'] ?? '');
			$cookieAttribution = json_decode($cookieValue, true);
			$attribution = is_array($cookieAttribution) ? $cookieAttribution : [];
		}

		$utm = [];
		foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $key) {
			$value = $this->input->get($key, true);
			$utm[$key] = is_string($value) ? mb_substr(trim($value), 0, 255) : '';
		}

		$clickId = '';
		$clickSource = '';
		$clickMedium = '';
		foreach (['gclid' => ['google', 'cpc'], 'gbraid' => ['google', 'cpc'], 'wbraid' => ['google', 'cpc'], 'fbclid' => ['facebook', 'social'], 'msclkid' => ['bing', 'cpc'], 'ttclid' => ['tiktok', 'paid_social']] as $key => $details) {
			$value = $this->input->get($key, true);
			if (is_string($value) && trim($value) !== '') {
				$clickId = mb_substr(trim($value), 0, 500);
				$clickSource = $details[0];
				$clickMedium = $details[1];
				break;
			}
		}

		$referrer = $this->acquisitionReferrer();
		$referrerHost = strtolower((string)parse_url($referrer, PHP_URL_HOST));
		$siteHost = strtolower((string)parse_url(base_url(), PHP_URL_HOST));
		$isExternalReferrer = $referrerHost !== '' && !$this->acquisitionHostMatches($referrerHost, [$siteHost]);
		$referrerSource = '';
		$referrerMedium = '';
		if ($isExternalReferrer) {
			$searchEngines = [
				'google' => ['google.com', 'google.co.uk', 'google.de', 'google.fr', 'google.com.tr', 'google.ae', 'google.ru', 'google.co.id', 'google.co.in', 'google.co.jp', 'google.ca', 'google.com.au', 'google.co.nz', 'google.co.za', 'google.com.br', 'google.com.mx', 'google.es', 'google.it', 'google.nl', 'google.pl', 'google.com.sg', 'google.com.my', 'google.com.ph', 'google.com.pk', 'google.com.ng', 'google.com.eg', 'google.com.sa', 'google.com.ua', 'google.com.vn', 'google.com.hk', 'google.com.tw', 'google.com.ar', 'google.com.co', 'google.com.pe', 'google.com.cl', 'google.gr', 'google.se', 'google.no', 'google.dk', 'google.ch', 'google.at', 'google.be', 'google.ie', 'google.pt', 'google.cz', 'google.hu', 'google.ro', 'google.bg', 'google.hr', 'google.sk', 'google.si', 'google.lt', 'google.lv', 'google.ee', 'google.fi'],
				'bing' => ['bing.com'],
				'yahoo' => ['yahoo.com', 'yahoo.co.uk', 'yahoo.co.jp', 'yahoo.com.tr'],
				'yandex' => ['yandex.ru', 'yandex.com', 'yandex.com.tr', 'yandex.kz', 'yandex.by'],
				'duckduckgo' => ['duckduckgo.com'],
				'ecosia' => ['ecosia.org'],
				'brave' => ['search.brave.com'],
			];
			foreach ($searchEngines as $sourceName => $domains) {
				if ($this->acquisitionHostMatches($referrerHost, $domains)) {
					$referrerSource = $sourceName;
					$referrerMedium = 'organic';
					break;
				}
			}

			if ($referrerSource === '') {
				$socialNetworks = [
					'facebook' => ['facebook.com', 'fb.com', 'fb.me'],
					'instagram' => ['instagram.com'],
					'tiktok' => ['tiktok.com'],
					'x' => ['x.com', 'twitter.com', 't.co'],
					'youtube' => ['youtube.com', 'youtu.be'],
					'linkedin' => ['linkedin.com', 'lnkd.in'],
					'reddit' => ['reddit.com', 'redd.it'],
					'pinterest' => ['pinterest.com', 'pin.it'],
				];
				foreach ($socialNetworks as $sourceName => $domains) {
					if ($this->acquisitionHostMatches($referrerHost, $domains)) {
						$referrerSource = $sourceName;
						$referrerMedium = 'social';
						break;
					}
				}
			}
			if ($referrerSource === '') {
				$referrerSource = mb_substr(preg_replace('/[^a-z0-9.-]/', '', $referrerHost), 0, 100);
				$referrerMedium = 'referral';
			}
		}

		$source = $utm['utm_source'] !== '' ? $utm['utm_source'] : ($clickSource ?: ($referrerSource ?: 'direct'));
		$source = mb_substr(strtolower(preg_replace('/[^a-zA-Z0-9._-]/', '', $source)), 0, 100);
		$inferredMedium = $utm['utm_source'] !== ''
			? ($clickSource !== '' && strtolower($utm['utm_source']) === $clickSource ? $clickMedium : 'campaign')
			: ($clickMedium ?: $referrerMedium);
		$medium = $utm['utm_medium'] !== '' ? $utm['utm_medium'] : $inferredMedium;
		$medium = mb_substr(strtolower(preg_replace('/[^a-zA-Z0-9._-]/', '', $medium)), 0, 100);
		$candidate = [
			'source' => $source !== '' ? $source : 'direct',
			'medium' => $medium,
			'campaign' => $utm['utm_campaign'],
			'term' => $utm['utm_term'],
			'content' => $utm['utm_content'],
			'landing_page' => mb_substr('/' . ltrim($this->uri->uri_string(), '/'), 0, 500),
			'referrer' => mb_substr($referrer, 0, 500),
			'click_id' => $clickId,
		];

		$hasAttribution = isset($attribution['source']) && (string)$attribution['source'] !== '';
		if (!$hasAttribution || ((string)$attribution['source'] === 'direct' && $candidate['source'] !== 'direct')) {
			if ($hasAttribution && !empty($attribution['landing_page'])) {
				$candidate['landing_page'] = $attribution['landing_page'];
			}
			$attribution = $candidate;
		}
		$attribution = $this->normalizeUserAcquisition($attribution);
		$unchanged = is_array($sessionAttribution) && $sessionAttribution === $attribution
			&& (string)($_COOKIE['playsel_acquisition'] ?? '') !== '';
		if ($unchanged) {
			return;
		}
		$this->session->set_userdata('user_acquisition', $attribution);

		$cookieOptions = [
			'expires' => time() + (90 * 86400),
			'path' => '/',
			'secure' => (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443,
			'httponly' => true,
			'samesite' => 'Lax',
		];
		setcookie('playsel_acquisition', (string)json_encode($attribution, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $cookieOptions);
	}

	private function acquisitionReferrer()
	{
		$value = (string)($_SERVER['HTTP_REFERER'] ?? '');
		$parts = parse_url($value);
		if (!is_array($parts) || !in_array(strtolower((string)($parts['scheme'] ?? '')), ['http', 'https'], true) || empty($parts['host'])) {
			return '';
		}

		$referrer = strtolower((string)$parts['scheme']) . '://' . strtolower((string)$parts['host']);
		if (!empty($parts['port'])) {
			$referrer .= ':' . (int)$parts['port'];
		}
		$referrer .= (string)($parts['path'] ?? '');
		return mb_substr($referrer, 0, 500);
	}

	private function acquisitionHostMatches($host, array $domains)
	{
		foreach ($domains as $domain) {
			if ($host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain) {
				return true;
			}
		}
		return false;
	}

	private function normalizeUserAcquisition(array $attribution)
	{
		$fields = ['source' => 100, 'medium' => 100, 'campaign' => 255, 'term' => 255, 'content' => 255, 'landing_page' => 500, 'referrer' => 500, 'click_id' => 500];
		$normalized = [];
		foreach ($fields as $field => $maxLength) {
			$value = trim((string)($attribution[$field] ?? ''));
			$normalized[$field] = mb_substr($value, 0, $maxLength);
		}
		if ($normalized['source'] === '') {
			$normalized['source'] = 'direct';
		}
		return $normalized;
	}

	public function saveUserAcquisition($userId, $signupMethod)
	{
		if (!$this->db->table_exists('user_acquisitions')) {
			log_message('error', 'User acquisition could not be saved: user_acquisitions table is missing.');
			return false;
		}

		$attribution = $this->session->userdata('user_acquisition');
		$attribution = $this->normalizeUserAcquisition(is_array($attribution) ? $attribution : []);
		$registrationIp = getIp();
		$registrationIp = filter_var($registrationIp, FILTER_VALIDATE_IP) ? $registrationIp : null;
		$inserted = $this->db->insert('user_acquisitions', [
			'user_id' => (int)$userId,
			'acquisition_source' => $attribution['source'],
			'acquisition_medium' => $attribution['medium'],
			'acquisition_campaign' => $attribution['campaign'],
			'acquisition_term' => $attribution['term'],
			'acquisition_content' => $attribution['content'],
			'acquisition_landing_page' => $attribution['landing_page'],
			'acquisition_referrer' => $attribution['referrer'],
			'acquisition_click_id' => $attribution['click_id'],
			'acquisition_signup_method' => in_array($signupMethod, ['email', 'google'], true) ? $signupMethod : 'unknown',
			'acquisition_ip' => $registrationIp,
			'acquisition_created_at' => date('Y-m-d H:i:s'),
		]);
		if (!$inserted) {
			log_message('error', 'User acquisition could not be saved for user ID ' . (int)$userId . '.');
			return false;
		}
		return true;
	}

	public function locales()
	{
		$locales = $this->db->select('*')
			->from('locales')
			->order_by('locale_sort', 'ASC')
			->where('locale_status', 1)
			->get()
			->result();
		return $locales;
	}

	public function userLogInsert($userEvent = '')
	{
		if (isAjaxRequest() || strpos($this->uri->uri_string(), 'assets-front') !== false || strpos($this->uri->uri_string(), 'uploads') !== false || substr($this->uri->uri_string(), -11) === 'test/search') { // AJAX isteği ise ya da assets-front ile başlayan sayfalar ise loglama yapma
			return;
		}
		$userData = $this->session->userdata('user');
		if ($userData) {
			if (!empty($userEvent)) {
				$userEvent = strtoupper($userEvent);
			} else {
				$page = $this->uri->uri_string();
				if (strpos($page, 'logout') !== false) {
					$userEvent = 'LOGOUT';
				} elseif (strpos($page, 'loginRequest') !== false) {
					$userEvent = 'LOGIN';
				} else {
					$userEvent = 'VIEW';
				}
			}
			$array = [
				'user_logs_user_id' => $userData['user_id'],
				'user_logs_user_event' => $userEvent ?? 'VIEW',
				'user_logs_user_country' => $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '',
				'user_logs_user_ip' => getIp(),
				'user_logs_user_agent' => $this->input->user_agent(),
				'user_logs_user_page' => $this->uri->uri_string(),
			];
			$this->db->insert('user_logs', $array);
		}
	}
}
