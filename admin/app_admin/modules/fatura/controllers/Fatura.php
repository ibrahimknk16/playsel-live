<?php defined('BASEPATH') or exit('No direct script access allowed');

class Fatura extends MY_Controller
{
	public $data = array();
	public function __construct()
	{
		userCheck();
		parent::__construct();
		$this->load->model('VtModel');
		$this->data['locales'] = $this->locales();
	}

	public function fatura_kesim()
	{
		$data = $this->data; // Kendi base controller yapına uygunsa kalsın
		$data['title'] = 'Toplu Fatura Kesim';
		$data['description'] = 'Reseller ve tarih aralığı seçerek fatura önizleyin veya kesin.';
		$data['resellers'] = $this->db->select('user_id, user_name')
			->from('users')
			//->where('user_type', 3)
			->order_by('user_name', 'ASC')
			->get()
			->result_array();

		$today = date('Y-m-d');
		$data['filters'] = [
			'reseller_id' => (int)$this->input->post('reseller_id'),
			'start_date' => (string)$this->input->post('start_date') ?: $today,
			'end_date' => (string)$this->input->post('end_date') ?: $today,
			'invoice_date' => (string)$this->input->post('invoice_date') ?: $today,
		];
		$data['preview'] = null;
		$data['success'] = null;
		$data['error'] = null;

		if ($this->input->method(true) === 'POST') {
			$action = (string)$this->input->post('invoice_action');
			$reseller_id = (int)$this->input->post('reseller_id');
			$start_input = (string)$this->input->post('start_date');
			$end_input = (string)$this->input->post('end_date');
			$invoice_date_input = (string)$this->input->post('invoice_date');
			$confirm_cut = (string)$this->input->post('confirm_cut');

			if ($reseller_id <= 0 || empty($start_input) || empty($end_input)) {
				$data['error'] = 'Lütfen reseller ve başlangıç-bitiş tarihlerini seçiniz.';
			} elseif (strtotime($start_input) === false || strtotime($end_input) === false) {
				$data['error'] = 'Geçersiz tarih formatı.';
			} elseif (!empty($invoice_date_input) && strtotime($invoice_date_input) === false) {
				$data['error'] = 'Geçersiz fatura tarihi.';
			} elseif (strtotime($start_input) > strtotime($end_input)) {
				$data['error'] = 'Başlangıç tarihi bitiş tarihinden büyük olamaz.';
			} else {
				$start_date = $start_input . ' 00:00:00';
				$end_date = $end_input . ' 23:59:59';
				$invoice_date = !empty($invoice_date_input) ? $invoice_date_input : date('Y-m-d');

				if ($action === 'preview') {
					try {
						$data['preview'] = $this->build_toplu_fatura_payload($reseller_id, $start_date, $end_date, $invoice_date);
					} catch (Exception $e) {
						$data['error'] = $e->getMessage();
					}
				} elseif ($action === 'cut') {
					if ($confirm_cut !== 'yes') {
						$data['error'] = 'Fatura kesimi için onay bilgisi eksik.';
					} else {
						$result = $this->toplu_fatura_kes($reseller_id, $start_date, $end_date, true, $invoice_date);
						if (!empty($result['success'])) {
							$data['success'] = 'Fatura kesimi başarılı. Fatura No: ' . ($result['invoice_no'] ?? '-');
							$data['preview'] = $result['payload'] ?? null;
						} else {
							$data['error'] = $result['message'] ?? 'Fatura kesimi sırasında bir hata oluştu.';
							$data['preview'] = $result['payload'] ?? null;
						}
					}
				} else {
					$data['error'] = 'Geçersiz işlem tipi.';
				}
			}
		}

		$this->template->content->view('fatura_kesim', $data);
		$this->template->publish();
	}

	private function build_toplu_fatura_payload($reseller_id, $start_date, $end_date, $invoice_date = null)
	{
		$orders = $this->db->select('o.order_id, o.order_product_id, o.order_quantity, o.order_sale_price, o.order_cost, o.order_created_at, p.product_name, p.product_is_full_vat, u.*')
			->from('orders as o')
			->join('products as p', 'p.product_id = o.order_product_id', 'inner')
			->join('users as u', 'u.user_id = o.order_reseller_id', 'inner')
			->where('o.order_reseller_id', $reseller_id)
			->where('o.order_created_at >=', $start_date)
			->where('o.order_created_at <=', $end_date)
			->where('o.order_invoice_status', 0)
			->where('o.order_status', 1)
			->order_by('o.order_id', 'ASC')
			->get()->result();

		if (empty($orders)) {
			throw new RuntimeException('Kesilecek veri bulunamadı.');
		}

		$this->load->library('birfatura');
		$user = $orders[0];

		$status = $this->birfatura->check_user_status($user->user_bill_tax_number);
		$is_efatura = !empty($status['is_efatura']) ? true : ($user->user_bill_e_invoice == 1);

		$endpoint = $status['endpoint'] ?? null;
		if (empty($endpoint) && $is_efatura) {
			$endpoint = 'urn:mail:defaultpk';
		}

		$uuid = $this->birfatura->generate_uuid();
		$is_foreign = ($user->user_is_turkish_company == 0);

		$type_code = $is_foreign ? 'ISTISNA' : 'SATIS';
		$exemption_code = $is_foreign ? '302' : '351';
		$exemption_text = $is_foreign ? '11/1-a Hizmet ihracatı' : 'İstisna Olmayan Diğer';

		// --- MAP & REDUCE (GRUPLAMA) KATMANI ---
		$grouped_products = [];
		$hizmet_bedeli_raw = [
			'stock_code' => '0-0',
			'name'       => 'Hizmet Bedeli',
			'total_profit_kdv_dahil' => 0.0 // Ham kâr kümülasyonu için
		];
		$has_hizmet = false;

		$order_ids = [];
		$toplam_odenecek = 0.0;

		// 1. FAZ: DÖNGÜ İÇİNDE SIFIR YUVARLAMA, SADECE HAM VERİ BİRİKTİRME
		foreach ($orders as $row) {
			$order_ids[] = $row->order_id;

			// Satır bazlı tutarlar (Veritabanından KDV dahil geldiğini / birim*adet olarak satır toplamı olduğunu varsayıyoruz)
			$satis_fiyati = (float)$row->order_sale_price;
			$maliyet_fiyati = (float)$row->order_cost;
			$adet = (int)$row->order_quantity;
			$safe_adet = $adet > 0 ? $adet : 1;

			$stok_kodu = $row->order_product_id ?? '0-0';
			// DİKKAT: Ürün adından sipariş numarasını (#12345) siliyoruz.
			$urun_adi = trim($row->product_name);

			// Unique Key (Stok Kodu + İsim kombinasyonu)
			$key = $stok_kodu . '_' . md5($urun_adi);

			if (!isset($grouped_products[$key])) {
				$grouped_products[$key] = [
					'stock_code'      => $stok_kodu,
					'name'            => $urun_adi,
					'qty'             => 0,
					'total_kdv_dahil' => 0.0, // Fiziki / Yurtdışı siparişlerin yekûnü
					'total_maliyet'   => 0.0, // E-Pinler için yekûn maliyet (0% KDV)
					'tax_rate'        => 0,
					'is_full_vat'     => false
				];
			}

			if ($is_foreign) {
				// Yurtdışı tamamen istisna
				$grouped_products[$key]['qty'] += $safe_adet;
				$grouped_products[$key]['total_kdv_dahil'] += $satis_fiyati;
				$toplam_odenecek += $satis_fiyati;
			} else {
				$is_full_vat_product = isset($row->product_is_full_vat) && $row->product_is_full_vat == 1;

				if ($is_full_vat_product) {
					// Tamamı %20 KDV'li (Fiziki/Genel ürün)
					$grouped_products[$key]['qty'] += $safe_adet;
					$grouped_products[$key]['total_kdv_dahil'] += $satis_fiyati;
					$grouped_products[$key]['tax_rate'] = 20;
					$grouped_products[$key]['is_full_vat'] = true;

					$toplam_odenecek += $satis_fiyati;
				} else {
					// E-PİN Kâr Marjlı Mantık (Maliyet %0, Kâr %20)
					$grouped_products[$key]['qty'] += $safe_adet;
					$grouped_products[$key]['total_maliyet'] += $maliyet_fiyati;

					// Hizmet bedeli (Kâr) için ham matrahı biriktir
					$profit_kdv_dahil = $satis_fiyati - $maliyet_fiyati;
					$hizmet_bedeli_raw['total_profit_kdv_dahil'] += $profit_kdv_dahil;
					$has_hizmet = true;

					$toplam_odenecek += $satis_fiyati;
				}
			}
		}

		// 2. FAZ: GRUPLANAN VERİLERİN MATRAH AYRIŞTIRMASI VE YUVARLANMASI
		$items = [];
		foreach ($grouped_products as $gp) {
			if ($gp['qty'] > 0) {
				if ($gp['is_full_vat']) {
					// Tamamı %20 KDV'li olan grupta tek seferlik tersine mühendislik
					$fiyat_kdv_haric = $gp['total_kdv_dahil'] / 1.20;
					$kdv_tutari = $gp['total_kdv_dahil'] - $fiyat_kdv_haric;

					$items[] = [
						'stock_code' => $gp['stock_code'],
						'name'       => $gp['name'],
						'qty'        => $gp['qty'],
						// GİB birim fiyatta 8 haneye kadar destekler, kümülatif miktar kaymalarını önler
						'price'      => round($fiyat_kdv_haric / $gp['qty'], 8),
						'tax_rate'   => 20,
						'tax_amount' => round($kdv_tutari, 2)
					];
				} else {
					// Yurtdışı (%0) veya Yerli E-Pin Maliyet (%0)
					$amount = $is_foreign ? $gp['total_kdv_dahil'] : $gp['total_maliyet'];

					$items[] = [
						'stock_code' => $gp['stock_code'],
						'name'       => $gp['name'],
						'qty'        => $gp['qty'],
						'price'      => round($amount / $gp['qty'], 8),
						'tax_rate'   => 0,
						'tax_amount' => 0.0
					];
				}
			}
		}

		// 3. FAZ: HİZMET BEDELİNİ (KÜMÜLATİF KÂR) TEK SATIR OLARAK FATURAYA EKLE
		if ($has_hizmet && $hizmet_bedeli_raw['total_profit_kdv_dahil'] > 0) {
			$total_profit = $hizmet_bedeli_raw['total_profit_kdv_dahil'];

			// Tek seferlik %20 matrah ayrıştırma işlemi
			$hizmet_kdv_haric = $total_profit / 1.20;
			$kdv_tutari = $total_profit - $hizmet_kdv_haric;

			$items[] = [
				'stock_code' => $hizmet_bedeli_raw['stock_code'],
				'name'       => $hizmet_bedeli_raw['name'],
				'qty'        => 1,
				'price'      => round($hizmet_kdv_haric, 8),
				'tax_rate'   => 20,
				'tax_amount' => round($kdv_tutari, 2)
			];
		}
		// --- GRUPLAMA KATMANI SONU ---

		$ilk_siparis = $order_ids[0];
		$son_siparis = end($order_ids);
		$gosterilecek_siparis_no = ($ilk_siparis == $son_siparis) ? $ilk_siparis : $ilk_siparis . '-' . $son_siparis;

		$notes = [
			'Fatura Kalem Sayısı: ' . count($items),
			'Yalnız ' . costToText(round($toplam_odenecek, 2)),
			'Bu fatura ' . getenv('API_URL') . ' üzerinden satışı yapılan dijital ürün/hizmet (e-pin) teslimat(lar)ını içerir.',
			'İlgili Sipariş Numaraları: ' . $gosterilecek_siparis_no
		];

		if (!empty($exemption_code)) {
			$notes[] = 'Vergi İstisna Muafiyet Sebebi: ' . $exemption_code . ' - ' . $exemption_text;
		}

		$fatura_tarihi = !empty($invoice_date) ? date('Y-m-d', strtotime($invoice_date)) : date('Y-m-d');
		$fatura_datetime = $fatura_tarihi . ' ' . date('H:i:s');

		$invoice_data = [
			'uuid'           => $uuid,
			'invoice_no'     => 'PLYSL-' . strtoupper(substr(str_replace('-', '', $uuid), 0, 8)),
			'date'           => $fatura_datetime,
			'is_foreign'     => $is_foreign,
			'is_efatura'     => $is_efatura,
			'endpoint'       => $endpoint,
			'type_code'      => $type_code,
			'currency'       => 'TRY',
			'customer'       => (array)$user,
			'exemption_code' => $exemption_code,
			'exemption_text' => $exemption_text,
			'notes'          => $notes,
			'order_no'       => $gosterilecek_siparis_no,
			'order_date'     => $fatura_tarihi,
			'sales_channel'  => 'DJPN',
		];

		// GENEL TOPLAMLARIN HESAPLANMASI (GİB standartlarına %100 uyumlu satır çarpanıyla)
		$net_total = 0.0;
		$tax_total = 0.0;
		foreach ($items as $item) {
			$line_qty = (float)($item['qty'] ?? 0);
			$line_price = (float)($item['price'] ?? 0);
			$line_net_total = round($line_qty * $line_price, 2);
			$line_tax_total = round((float)($item['tax_amount'] ?? 0), 2);
			$net_total += $line_net_total;
			$tax_total += $line_tax_total;
		}

		return [
			'orders'            => $orders,
			'user'              => $user,
			'items'             => $items,
			'order_ids'         => $order_ids,
			'invoice_data'      => $invoice_data,
			'toplam_odenecek'   => $toplam_odenecek,
			'order_no'          => $gosterilecek_siparis_no,
			'order_date'        => $fatura_tarihi,
			'invoice_date'      => $fatura_tarihi,
			'total_order_count' => count($orders),
			'total_item_count'  => count($items),
			'net_total'         => round($net_total, 2),
			'tax_total'         => round($tax_total, 2),
			'grand_total'       => round($net_total + $tax_total, 2),
			'date_start'        => $start_date,
			'date_end'          => $end_date,
		];
	}

	public function toplu_fatura_kes($reseller_id, $start_date, $end_date, $return_result = false, $invoice_date = null)
	{
		try {
			$payload = $this->build_toplu_fatura_payload($reseller_id, $start_date, $end_date, $invoice_date);
		} catch (Exception $e) {
			if ($return_result) {
				return [
					'success' => false,
					'message' => $e->getMessage(),
				];
			}
			die($e->getMessage());
		}

		$this->load->library('birfatura');
		$result = $this->birfatura->send_invoice($payload['invoice_data'], $payload['items']);

		if (is_string($result)) {
			$result = json_decode($result, true);
		}

		if (!is_array($result)) {
			$result = [
				'Success' => false,
				'Message' => 'Birfatura cevabı parse edilemedi.',
			];
		}

		if (!empty($result['Success'])) {
			$invoice_no = $result['Result']['invoiceNo'] ?? 'OTOMATIK';
			$updateData = [
				'order_invoice_number' => $invoice_no,
				'order_invoice_status' => 1,
				'order_invoice_uuid'   => $payload['invoice_data']['uuid'],
				'order_invoice_date'   => $payload['invoice_data']['date'],
			];

			// Çok büyük WHERE IN listelerinde CI Query Builder regex limiti patlayabildiği için chunk'lı update.
			$orderIds = array_values(array_unique(array_map('intval', (array)$payload['order_ids'])));
			$chunkSize = 500;
			$chunks = array_chunk($orderIds, $chunkSize);
			foreach ($chunks as $chunk) {
				if (empty($chunk)) {
					continue;
				}
				$this->db->where_in('order_id', $chunk)->update('orders', $updateData);
			}

			if ($return_result) {
				return [
					'success' => true,
					'invoice_no' => $invoice_no,
					'result' => $result,
					'payload' => $payload,
				];
			}

			echo 'Islem Basarili. Fatura No: ' . $invoice_no;
			return;
		}

		if ($return_result) {
			return [
				'success' => false,
				'message' => $result['Message'] ?? 'Fatura oluşturma başarısız oldu.',
				'result' => $result,
				'payload' => $payload,
			];
		}

		header('Content-Type: application/json');
		die(json_encode($result, JSON_PRETTY_PRINT));
	}
}
