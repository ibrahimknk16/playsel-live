<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Birfatura e-Dönüşüm V2 API Library - Senior Edition
 * Mimar: İbrahim
 * * UBL-TR 2.1 Standartlarına %100 Uyumlu, E-Pin/E-Ticaret Optimize
 * @Property string $api_key Entegratör API Anahtarı
 * @Property string $secret_key Entegratör Secret Anahtarı
 * @Property string $int_key Entegratör Entegrasyon Anahtarı
 * @Property string $base_url Birfatura API Temel URL'si
 * @Property array $supplier Tedarikçi Bilgileri (VKN, Adres, Şehir)
 * @Property CI_Controller $CI CodeIgniter Ana Nesnesi
 * @Property CI_Config $CI->config CodeIgniter Konfigürasyon Kütüphanesi
 */
class Birfatura
{
    protected $CI;
    private $config;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->config('birfatura');
        $this->config = [
            'api_key'      => $this->CI->config->item('birfatura_api_key'),
            'secret_key'   => $this->CI->config->item('birfatura_secret_key'),
            'int_key'      => $this->CI->config->item('birfatura_integration_key'),
            'base_url'     => rtrim($this->CI->config->item('birfatura_base_url'), '/') . '/',
            'supplier'     => $this->CI->config->item('birfatura_supplier')
        ];
    }

    public function check_user_status($vkn_tckn)
    {
        $response = $this->_curl_request("api/OutEBelgeV2/GetUserGB", ["kn" => $vkn_tckn]);

        if (isset($response['success']) && $response['success'] && !empty($response['result'])) {
            return [
                'is_efatura' => true,
                'endpoint'   => $response['result'][0]['name'],
                'title'      => $response['result'][0]['title']
            ];
        }
        return ['is_efatura' => false, 'endpoint' => null];
    }

    // Birfatura kütüphanesine şunu ekle (veya mevcut check_user_status'u şu mantığa çek)
    public function check_user_pk($vkn_tckn)
    {
        $response = $this->_curl_request("api/OutEBelgeV2/GetUserPK", ["kn" => $vkn_tckn]);

        if (empty($response['Result'])) {
            return ['is_efatura' => false, 'endpoint' => null];
        }

        $results = $response['Result'];
        $invoice_endpoint = null;

        // Fatura (INVOICE) tipini bulmak için döngü
        foreach ($results as $item) {
            if ($item['type2'] === 'INVOICE') {
                $invoice_endpoint = $item['name'];
                break;
            }
        }

        // Eğer INVOICE etiketi yoksa, sistem E-Arşiv'dir.
        if (!$invoice_endpoint) {
            return ['is_efatura' => false, 'endpoint' => null];
        }

        return [
            'is_efatura' => true,
            'endpoint'   => $invoice_endpoint
        ];
    }

    public function send_invoice($invoice_data, $items)
    {
        $ubl_data = [
            'uuid'           => $invoice_data['uuid'],
            'invoice_no'     => $invoice_data['invoice_no'],
            'profile_id'     => $invoice_data['is_efatura'] ? 'TICARIFATURA' : 'EARSIVFATURA',
            'type_code'      => $invoice_data['type_code'],
            'date'           => date('Y-m-d', strtotime($invoice_data['date'])),
            'time'           => date('H:i:s', strtotime($invoice_data['date'])),
            'currency'       => $invoice_data['currency'] ?? 'TRY',
            'customer'       => $invoice_data['customer'],
            'items'          => $items,
            'notes'          => $invoice_data['notes'] ?? [],
            'exemption_code' => $invoice_data['exemption_code'] ?? null,
            'exemption_text' => $invoice_data['exemption_text'] ?? null,
            'endpoint'       => $invoice_data['endpoint'] ?? null,
            'order_no'       => $invoice_data['order_no'] ?? null,
            'order_date'     => $invoice_data['order_date'] ?? null,
            'sales_channel'  => $invoice_data['sales_channel'] ?? 'api.dijipin.com'
        ];

        $xml = $this->_generate_ubl_xml($ubl_data);
        $zip_base64 = $this->_create_zip_base64($invoice_data['uuid'], $xml);

        $payload = [
            "DocumentBytes"    => $zip_base64,
            "IsDocumentNoAuto" => true,
            "SystemTypeCodes"  => $invoice_data['is_efatura'] ? "EFATURA" : "EARSIV",
            "ReceiverTag"      => $invoice_data['endpoint'] ?? ""
        ];

        return $this->_curl_request("api/OutEBelgeV2/SendDocument", $payload);
    }

    private function _generate_ubl_xml($d)
    {
        $w = new XMLWriter();
        $w->openMemory();
        $w->setIndent(false);
        $w->startDocument('1.0', 'utf-8');

        // UBL-TR 2.1 Root ve Namespaces
        $w->startElementNS('inv', 'Invoice', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $w->writeAttribute('xmlns:ubltr', 'urn:oasis:names:specification:ubl:schema:xsd:TurkishCustomizationExtensionComponents');
        $w->writeAttribute('xmlns:qdt', 'urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2');
        $w->writeAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $w->writeAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $w->writeAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
        $w->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $w->writeAttribute('xmlns:ccts', 'urn:un:unece:uncefact:documentation:2');
        $w->writeAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $w->writeAttribute('xmlns:xades', 'http://uri.etsi.org/01903/v1.3.2#');
        $w->writeAttribute('xmlns:udt', 'urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2');
        $w->writeAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');

        $w->startElement('ext:UBLExtensions');
        $w->startElement('ext:UBLExtension');
        $w->startElement('ext:ExtensionContent');
        $w->text('');
        $w->endElement();
        $w->endElement();
        $w->endElement();

        $w->writeElement('cbc:UBLVersionID', '2.1');
        $w->writeElement('cbc:CustomizationID', 'TR1.2');
        $w->writeElement('cbc:ProfileID', $d['profile_id']);
        $w->writeElement('cbc:ID', $d['invoice_no']);
        $w->writeElement('cbc:CopyIndicator', 'false');
        $w->writeElement('cbc:UUID', $d['uuid']);
        $w->writeElement('cbc:IssueDate', $d['date']);
        $w->writeElement('cbc:IssueTime', $d['time']);
        $w->writeElement('cbc:InvoiceTypeCode', $d['type_code']);

        foreach ($d['notes'] as $note) {
            $w->writeElement('cbc:Note', $note);
        }

        $w->writeElement('cbc:DocumentCurrencyCode', $d['currency']);
        $w->writeElement('cbc:LineCountNumeric', count($d['items']));

        // --- XSLT TUZAKLARINI AŞAN REFERANS BLOKLARI ---

        // --- SİPARİŞ VE SATIŞ KANALI REFERANSLARI (XSLT ODAKLI) ---
        if (!empty($d['order_no'])) {
            $w->startElement('cac:OrderReference');
            $w->writeElement('cbc:ID', $d['order_no']);
            $w->writeElement('cbc:IssueDate', $d['order_date'] ?? $d['date']);
            $w->endElement();
        }

        // Fatura UUID Referansı (Zorunlu)
        $w->startElement('cac:AdditionalDocumentReference');
        $w->writeElement('cbc:ID', $d['uuid']);
        $w->writeElement('cbc:IssueDate', $d['date']);
        $w->writeElement('cbc:DocumentTypeCode', 'CUST_INV_ID');
        $w->endElement();

        // --- SATIŞ KANALI REFERANSI (Entegratörün İstediği EUPRWEBURI Kodu) ---
        $w->startElement('cac:AdditionalDocumentReference');
        $w->writeElement('cbc:ID', $d['sales_channel']);
        $w->writeElement('cbc:IssueDate', $d['date']);
        $w->writeElement('cbc:DocumentTypeCode', 'EUPRWEBURI');
        $w->endElement();

        // İmza Alanı (Zorunlu Placeholder)
        $w->startElement('cac:Signature');
        $w->startElement('cbc:ID');
        $w->writeAttribute('schemeID', 'VKN_TCKN');
        $w->text($this->config['supplier']['vkn']);
        $w->endElement();
        $w->startElement('cac:SignatoryParty');
        $w->startElement('cac:PartyIdentification');
        $w->startElement('cbc:ID');
        $w->writeAttribute('schemeID', 'VKN');
        $w->text($this->config['supplier']['vkn']);
        $w->endElement();
        $w->endElement();
        $w->startElement('cac:PostalAddress');
        $w->writeElement('cbc:StreetName', '*');
        $w->writeElement('cbc:CityName', $this->config['supplier']['city']);
        $w->startElement('cac:Country');
        $w->writeElement('cbc:Name', 'TÜRKİYE');
        $w->endElement();
        $w->endElement();
        $w->endElement();
        $w->startElement('cac:DigitalSignatureAttachment');
        $w->startElement('cac:ExternalReference');
        $w->writeElement('cbc:URI', '#Signature');
        $w->endElement();
        $w->endElement();
        $w->endElement();

        // Taraf Bilgileri
        $this->_write_party_ubl($w, 'cac:AccountingSupplierParty', $this->config['supplier']);
        $this->_write_party_ubl($w, 'cac:AccountingCustomerParty', $d['customer'], $d['endpoint']);

        // Vergi Gruplaması (Schematron İçin Kritik Dinamik Yapı)
        $tax_groups = [];
        $tax_total = 0;
        $taxable_amount_total = 0;

        foreach ($d['items'] as $item) {
            $rate = (string)(int)$item['tax_rate'];
            if (!isset($tax_groups[$rate])) {
                $tax_groups[$rate] = ['taxable' => 0, 'tax' => 0];
            }
            $line_taxable = round($item['price'] * $item['qty'], 2);
            $tax_groups[$rate]['taxable'] += $line_taxable;
            $tax_groups[$rate]['tax'] += $item['tax_amount'];

            $taxable_amount_total += $line_taxable;
            $tax_total += $item['tax_amount'];
        }

        $w->startElement('cac:TaxTotal');
        $w->startElement('cbc:TaxAmount');
        $w->writeAttribute('currencyID', $d['currency']);
        $w->text(number_format($tax_total, 2, '.', ''));
        $w->endElement();

        foreach ($tax_groups as $rate => $group) {
            $w->startElement('cac:TaxSubtotal');
            $w->startElement('cbc:TaxableAmount');
            $w->writeAttribute('currencyID', $d['currency']);
            $w->text(number_format($group['taxable'], 2, '.', ''));
            $w->endElement();
            $w->startElement('cbc:TaxAmount');
            $w->writeAttribute('currencyID', $d['currency']);
            $w->text(number_format($group['tax'], 2, '.', ''));
            $w->endElement();
            $w->writeElement('cbc:Percent', $rate);

            $w->startElement('cac:TaxCategory');
            if ($rate == 0 && !empty($d['exemption_code'])) {
                $w->writeElement('cbc:TaxExemptionReasonCode', $d['exemption_code']);
                $w->writeElement('cbc:TaxExemptionReason', $d['exemption_text']);
            }
            $w->startElement('cac:TaxScheme');
            $w->writeElement('cbc:Name', 'KDV');
            $w->writeElement('cbc:TaxTypeCode', '0015');
            $w->endElement();
            $w->endElement();
            $w->endElement();
        }
        $w->endElement();

        // Genel Toplamlar
        $w->startElement('cac:LegalMonetaryTotal');
        $w->startElement('cbc:LineExtensionAmount');
        $w->writeAttribute('currencyID', $d['currency']);
        $w->text(number_format($taxable_amount_total, 2, '.', ''));
        $w->endElement();
        $w->startElement('cbc:TaxExclusiveAmount');
        $w->writeAttribute('currencyID', $d['currency']);
        $w->text(number_format($taxable_amount_total, 2, '.', ''));
        $w->endElement();
        $w->startElement('cbc:TaxInclusiveAmount');
        $w->writeAttribute('currencyID', $d['currency']);
        $w->text(number_format($taxable_amount_total + $tax_total, 2, '.', ''));
        $w->endElement();
        $w->startElement('cbc:AllowanceTotalAmount');
        $w->writeAttribute('currencyID', $d['currency']);
        $w->text('0.00');
        $w->endElement();
        $w->startElement('cbc:PayableAmount');
        $w->writeAttribute('currencyID', $d['currency']);
        $w->text(number_format($taxable_amount_total + $tax_total, 2, '.', ''));
        $w->endElement();
        $w->endElement();

        // Fatura Kalemleri (Satırlar)
        foreach ($d['items'] as $idx => $item) {
            $w->startElement('cac:InvoiceLine');
            $w->writeElement('cbc:ID', $idx + 1);
            $w->startElement('cbc:InvoicedQuantity');
            $w->writeAttribute('unitCode', 'NIU');
            $w->text(number_format($item['qty'], 4, '.', ''));
            $w->endElement();
            $w->startElement('cbc:LineExtensionAmount');
            $w->writeAttribute('currencyID', $d['currency']);
            $w->text(number_format($item['price'] * $item['qty'], 2, '.', ''));
            $w->endElement();

            $w->startElement('cac:TaxTotal');
            $w->startElement('cbc:TaxAmount');
            $w->writeAttribute('currencyID', $d['currency']);
            $w->text(number_format($item['tax_amount'], 2, '.', ''));
            $w->endElement();
            $w->startElement('cac:TaxSubtotal');
            $w->startElement('cbc:TaxableAmount');
            $w->writeAttribute('currencyID', $d['currency']);
            $w->text(number_format($item['price'] * $item['qty'], 2, '.', ''));
            $w->endElement();
            $w->startElement('cbc:TaxAmount');
            $w->writeAttribute('currencyID', $d['currency']);
            $w->text(number_format($item['tax_amount'], 2, '.', ''));
            $w->endElement();
            $w->writeElement('cbc:Percent', (int)$item['tax_rate']);
            $w->startElement('cac:TaxCategory');
            if ($item['tax_rate'] == 0 && !empty($d['exemption_code'])) {
                $w->writeElement('cbc:TaxExemptionReasonCode', $d['exemption_code']);
                $w->writeElement('cbc:TaxExemptionReason', $d['exemption_text']);
            }
            $w->startElement('cac:TaxScheme');
            $w->writeElement('cbc:Name', 'KDV');
            $w->writeElement('cbc:TaxTypeCode', '0015');
            $w->endElement();
            $w->endElement();
            $w->endElement();
            $w->endElement();

            $w->startElement('cac:Item');
            $w->writeElement('cbc:Name', $item['name']);
            if (!empty($item['stock_code'])) {
                $w->startElement('cac:SellersItemIdentification');
                $w->writeElement('cbc:ID', $item['stock_code']);
                $w->endElement();
            }
            $w->endElement();

            $w->startElement('cac:Price');
            $w->startElement('cbc:PriceAmount');
            $w->writeAttribute('currencyID', $d['currency']);
            $w->text(number_format($item['price'], 4, '.', ''));
            $w->endElement();
            $w->endElement();

            $w->endElement(); // InvoiceLine
        }

        $w->endElement(); // Invoice

        return $w->outputMemory();
    }

    private function _write_party_ubl($w, $tag, $p, $endpoint = null)
    {
        $w->startElement($tag);
        $w->startElement('cac:Party');

        // Website
        if ($tag === 'cac:AccountingSupplierParty') {
            $w->writeElement('cbc:WebsiteURI', 'https://www.dijipin.com');
        }

        // Endpoint
        if ($endpoint) {
            $w->startElement('cbc:EndpointID');
            $w->writeAttribute('schemeID', 'urn:mail:default');
            $w->text($endpoint);
            $w->endElement();
        }

        // VKN / TCKN
        $vkn = $p['user_bill_tax_number'] ?? $p['vkn'] ?? '';
        $scheme = (strlen($vkn) == 11) ? 'TCKN' : 'VKN';

        $w->startElement('cac:PartyIdentification');
        $w->startElement('cbc:ID');
        $w->writeAttribute('schemeID', $scheme);
        $w->text($vkn);
        $w->endElement();
        $w->endElement();

        $party_name = $p['user_bill_company_name'] ?? $p['company_name'] ?? $p['name'] ?? 'Müşteri';

        // *** ŞAHIS FİRMASI DÜZENLEMESİ ***
        if ($scheme === 'TCKN') {
            $w->startElement('cac:Person');
            // İsim Soyisim parçalama (basitçe boşluğa göre)
            $names = explode(' ', $party_name, 2);
            $w->writeElement('cbc:FirstName', $names[0]);
            $w->writeElement('cbc:FamilyName', $names[1] ?? '');
            $w->endElement(); // Person
        } else {
            $w->startElement('cac:PartyName');
            $w->writeElement('cbc:Name', $party_name);
            $w->endElement();
        }

        $w->startElement('cac:PartyLegalEntity');
        $w->writeElement('cbc:RegistrationName', $party_name);
        $w->endElement();

        // PartyName
        $w->startElement('cac:PartyName');
        $w->writeElement('cbc:Name', $party_name);
        $w->endElement();

        // PartyLegalEntity (Birçok XSL bunu okuyor)
        $w->startElement('cac:PartyLegalEntity');
        $w->writeElement('cbc:RegistrationName', $party_name);
        $w->endElement();

        // Adres
        $w->startElement('cac:PostalAddress');
        $w->writeElement('cbc:StreetName', $p['user_bill_address'] ?? $p['address'] ?? '-');
        $w->writeElement('cbc:CitySubdivisionName', $p['user_bill_district'] ?? $p['district'] ?? '');
        $w->writeElement('cbc:CityName', $p['user_bill_city'] ?? $p['city'] ?? '');

        $w->startElement('cac:Country');
        $w->writeElement('cbc:Name', 'TÜRKİYE');
        $w->endElement();

        $w->endElement();

        // Vergi Dairesi
        $taxOffice = $p['user_bill_tax_office'] ?? $p['tax_office'] ?? '';

        if ($taxOffice != '') {
            $w->startElement('cac:PartyTaxScheme');
            $w->startElement('cac:TaxScheme');
            $w->writeElement('cbc:Name', $taxOffice);
            $w->endElement();
            $w->endElement();
        }

        // İletişim
        $phone = $p['user_phone'] ?? $p['phone'] ?? '';
        $email = $p['user_email'] ?? $p['email'] ?? '';

        if ($phone || $email) {
            $w->startElement('cac:Contact');

            if ($phone) {
                $w->writeElement('cbc:Telephone', $phone);
            }

            if ($email) {
                $w->writeElement('cbc:ElectronicMail', $email);
            }

            $w->endElement();
        }

        $w->endElement(); // Party
        $w->endElement(); // AccountingSupplierParty / AccountingCustomerParty
    }

    private function _curl_request($endpoint, $payload)
    {
        $url = rtrim($this->config['base_url'], '/') . '/' . ltrim($endpoint, '/');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => [
                "X-Api-Key: " . $this->config['api_key'],
                "X-Secret-Key: " . $this->config['secret_key'],
                "X-Integration-Key: " . $this->config['int_key'],
                "Content-Type: application/json",
                "Accept: application/json"
            ]
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }

    private function _create_zip_base64($uuid, $xml)
    {
        $zip = new ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'zip');
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFromString($uuid . '.xml', $xml);
            $zip->close();
        }
        $data = base64_encode(file_get_contents($tempFile));
        unlink($tempFile);
        return $data;
    }

    public function generate_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }

    /**
     * Faturanın PDF linkini UUID üzerinden getirir.
     * @param string $uuid Faturaya ait benzersiz UUID
     * @return string|bool PDF URL'si döner veya hata durumunda false
     */
    /**
     * eBelgeV2 Standartlarına Uygun PDF Link Getirme
     * 404 hatasını aşmak için endpoint GetDocumentLink olarak güncellendi.
     */
    /**
     * eBelgeV2 PDF Link Getirme - api.birfatura.com üzerinden
     * @param string $uuid Fatura UUID
     * @param bool $is_efatura E-Fatura mı? (Default: E-Arşiv)
     */
    public function get_pdf_link_by_uuid($uuid, $system_type = 'EARSIV')
    {
        // Config'den yeni base_url'i alıyoruz
        $url = rtrim($this->config['base_url'], '/') . '/';
        $endpoint = "api/OutEBelgeV2/GetPDFLinkByUUID";
        $url = $url . $endpoint;

        // Dokümana göre: uuids array[string] ve systemType string olmalı
        $payload = [
            "uuids"      => is_array($uuid) ? $uuid : [$uuid],
            "systemType" => strtoupper($system_type)
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => [
                "X-Api-Key: " . $this->config['api_key'],
                "X-Secret-Key: " . $this->config['secret_key'],
                "X-Integration-Key: " . $this->config['int_key'],
                "Content-Type: application/json",
                "Accept: application/json"
            ]
        ]);

        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($res, true);

        /**
         * API Yanıt Yapısı (ApiResponse_GetPDFLinkByUUIDResponse):
         * success: boolean
         * Result: array[object] -> { uuid, pdfLink, success, message }
         */
        if ($http_code === 200 && isset($response['Success']) && $response['Success'] === true) {
            if (!empty($response['Result']) && is_array($response['Result'])) {
                // Tekil sorgulama yapıldıysa doğrudan ilk linki dönelim
                if (!is_array($uuid)) {
                    return $response['Result'][0]['pdfLink'] ?? false;
                }
                // Çoğul sorgulama yapıldıysa tüm Result setini dönelim
                return $response['Result'];
            }
        }

        // Hata durumunda loglama veya debug için response'u kontrol edebilirsin
        // pre($response); die;

        return false;
    }

    /**
     * eBelgeV2 Fatura Detaylarını Getirir (GİB Durumu, Hata Kodları vb.)
     * @param string $uuid Sorgulanacak faturanın UUID'si
     * @param string $system_type EFATURA, EARSIV, EIRSALIYE vb.
     */
    public function get_outbox_document_by_uuid($uuid, $system_type = 'EARSIV')
    {
        $url = rtrim($this->config['base_url'], '/') . '/';
        $endpoint = "api/OutEBelgeV2/GetOutBoxDocumentByUUID";
        $url = $url . $endpoint;

        $payload = [
            "uuid"       => $uuid,
            "systemType" => strtoupper($system_type)
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => [
                "X-Api-Key: " . $this->config['api_key'],
                "X-Secret-Key: " . $this->config['secret_key'],
                "X-Integration-Key: " . $this->config['int_key'],
                "Content-Type: application/json",
                "Accept: application/json"
            ]
        ]);

        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($res, true);

        // Debug: pre($response); die;

        if ($http_code === 200 && isset($response['Success']) && $response['Success'] === true) {
            return $response['Result']; // Faturanın tüm detay objesini döner
        }

        return false;
    }
}
