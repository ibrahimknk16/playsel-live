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

	public function __construct()
	{
		parent::__construct();
		
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
		if (isAjaxRequest() || strpos($this->uri->uri_string(), 'assets-front') !== false || strpos($this->uri->uri_string(), 'uploads') !== false) { // AJAX isteği ise ya da assets-front ile başlayan sayfalar ise loglama yapma
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
