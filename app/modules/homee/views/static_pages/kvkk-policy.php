<style>
    .ps-static-body h1 {
        font-size: 24px;
        text-align: center;
        color: #2980b9;
        border-bottom: 2px solid #34495e;
        padding-bottom: 12px;
        margin-bottom: 35px;
        text-transform: uppercase;
    }
    .ps-static-body h3 {
        font-size: 17px;
        color: #2980b9;
        margin-top: 25px;
        border-bottom: 1px solid #ecf0f1;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
    .ps-static-body p, .ps-static-body li {
        font-size: 14.5px;
        margin-bottom: 12px;
        text-align: justify;
    }
    .ps-static-body ul {
        margin-top: 5px;
        margin-bottom: 20px;
        padding-left: 25px;
    }
    .ps-static-body .bold {
        font-weight: 700;
    }
    .ps-static-body .highlight {
        background-color: #fef9e7;
        padding: 18px;
        border-left: 4px solid #f1c40f;
        margin: 25px 0;
        border-radius: 4px;
    }
    .ps-static-body .danger {
        background-color: #fdedec;
        padding: 18px;
        border-left: 4px solid #e74c3c;
        margin: 25px 0;
        border-radius: 4px;
    }
</style>

<?php
$user = $this->session->userdata('user');
$alici_ad_soyad = ''; 
$alici_eposta = '';
$alici_tckn_vkn = '';
$alici_fatura_adresi = '';
$alici_telefon = '';
$islem_ip_adresi = '';
$onay_tarih_saati = '';

if (!empty($user) && is_array($user)) {
    $aliciFullName = trim(($user['user_name'] ?? '') . ' ' . ($user['user_surname'] ?? ''));
    $alici_ad_soyad = $aliciFullName !== '' ? $aliciFullName : ($user['user_name'] ?? ''); 
    $alici_eposta = $user['user_email'] ?? '';
    $alici_tckn_vkn = $user['user_bill_tax_number'] ?? '';
    $alici_fatura_adresi = $user['user_bill_address'] ?? '';
    $alici_telefon = $user['user_phone'] ?? '';
    $islem_ip_adresi = getIp();
    $onay_tarih_saati = $user['user_created_at'] ?? '';

    // Kullanıcı sözleşmesini onaylama logu varsa IP ve Tarihi oradan al
    $agreementLog = $this->db->where('user_logs_user_id', $user['user_id'])
                             ->where('user_logs_user_event', 'USER-AGREEMENT')
                             ->order_by('user_logs_id', 'DESC')
                             ->get('user_logs')->row();
    if ($agreementLog) {
        $islem_ip_adresi = $agreementLog->user_logs_user_ip;
        $onay_tarih_saati = $agreementLog->user_logs_created_at;
    }
}
?>

<h1>KİŞİSEL VERİLERİN KORUNMASI (KVKK) AYDINLATMA METNİ</h1>

<h3>1. VERİ SORUMLUSUNUN KİMLİĞİ</h3>
<p>6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, kişisel verileriniz veri sorumlusu sıfatıyla aşağıda bilgileri yer alan <span class="bold">Playsel Elektronik Ticaret Limited Şirketi</span> ("Şirket" veya "Playsell") tarafından işlenmektedir.</p>
<ul>
    <li><span class="bold">Adres:</span> Adalet Mh. Anadolu Cd. No 41/1 İç Kapı No: 1402 My Plaza - Bayraklı/İzmir</li>
    <li><span class="bold">Vergi Dairesi ve VKN:</span> 7300761424</li>
    <li><span class="bold">Mersis No:</span> 0730076142400001</li>
    <li><span class="bold">E-posta:</span> info@playsel.com</li>
    <li><span class="bold">KEP Adresi:</span> playsel@hs04.kep.tr</li>
</ul>

<h3>2. İŞLENEN KİŞİSEL VERİLERİNİZ</h3>
<p>Platformumuza üye olmanız, alışveriş yapmanız veya ziyaret etmeniz halinde aşağıdaki kişisel verileriniz işlenebilmektedir:</p>
<ul>
    <li><span class="bold">Kimlik Verileri:</span> Ad, soyad, T.C. Kimlik Numarası (fatura ve yasal zorunluluklar gereği), doğum tarihi.</li>
    <li><span class="bold">İletişim Verileri:</span> E-posta adresi, telefon numarası, fatura adresi.</li>
    <li><span class="bold">İşlem Güvenliği ve Finansal Veriler:</span> IP adresi, sisteme giriş-çıkış (log) kayıtları, cihaz bilgileri, sipariş bilgileri, şifrelenmiş banka/kredi kartı bilgileri (ilk 6 ve son 4 hanesi).</li>
    <li><span class="bold">KYC (Müşterini Tanı) Verileri:</span> Dolandırıcılık şüphesi olan işlemlerde yasal güvenlik amacıyla talep edilen kimlik belgesi ön yüzü, kimlikle çekilmiş özçekim (selfie) ve adınıza kayıtlı fatura/ikametgah görselleri.</li>
</ul>

<div class="highlight">
    <h3>3. KİŞİSEL VERİLERİN İŞLENME AMAÇLARI</h3>
    <p>Toplanan kişisel verileriniz, KVKK'nın 5. ve 6. maddelerinde belirtilen kişisel veri işleme şartları dâhilinde aşağıdaki amaçlarla işlenmektedir:</p>
    <ul>
        <li>Mesafeli Satış Sözleşmesi ve Kullanıcı Sözleşmesi'nin kurulması ve ifa edilmesi (dijital kodların teslimi).</li>
        <li>E-Fatura / E-Arşiv fatura kesilmesi ve muhasebe kayıtlarının tutulması.</li>
        <li><span class="bold">Güvenlik ve AML (Kara Para Aklamanın Önlenmesi):</span> Çalıntı kredi kartı kullanımının ve dolandırıcılığın (fraud) tespiti ve engellenmesi, şüpheli işlemlerde kimlik doğrulama (KYC) süreçlerinin yürütülmesi.</li>
        <li>5651 sayılı Kanun ve 5549 sayılı Kanun (MASAK mevzuatı) başta olmak üzere yasal bildirim yükümlülüklerimizin yerine getirilmesi.</li>
        <li><span class="bold">Yaş Sınırı Kontrolü:</span> 18 yaş altı kullanıcıların tespit edilmesi ve yasal veli sorumluluk süreçlerinin yönetilmesi.</li>
    </ul>
</div>

<h3>4. İŞLENEN KİŞİSEL VERİLERİN KİMLERE VE HANGİ AMAÇLA AKTARILABİLECEĞİ</h3>
<p>Kişisel verileriniz, yalnızca yukarıda belirtilen amaçların gerçekleştirilmesi doğrultusunda ve KVKK'nın 8. ve 9. maddelerine uygun olarak aktarılmaktadır:</p>
<ul>
    <li>Yasal uyuşmazlıkların giderilmesi, şüpheli işlem ve çalıntı kart ihbarları kapsamında <span class="bold">Emniyet Genel Müdürlüğü, Cumhuriyet Başsavcılıkları, Mahkemeler, Tüketici Hakem Heyetleri ve MASAK</span> gibi yetkili kamu kurum ve kuruluşlarına,</li>
    <li>Ödeme altyapısının sağlanması ve fraud (dolandırıcılık) analizi yapılması amacıyla BDDK lisanslı ödeme kuruluşlarına (Sanal POS sağlayıcıları) ve bankalara,</li>
    <li>Fatura süreçlerinin yürütülmesi amacıyla mali müşavirlerimize ve e-dönüşüm (e-fatura entegratör) firmalarına.</li>
</ul>

<h3>5. KİŞİSEL VERİ TOPLAMANIN YÖNTEMİ VE HUKUKİ SEBEBİ</h3>
<p>Kişisel verileriniz, web sitemiz ve mobil uygulamamız üzerinden doldurduğunuz formlar, sipariş ekranları ve çerezler (cookies) aracılığıyla elektronik ortamda toplanmaktadır. İşlememizdeki hukuki sebepler şunlardır:</p>
<ul>
    <li><span class="bold">KVKK Md. 5/2-c:</span> Bir sözleşmenin kurulması veya ifasıyla doğrudan doğruya ilgili olması kaydıyla, sözleşmenin taraflarına ait kişisel verilerin işlenmesinin gerekli olması (Siparişin teslimi).</li>
    <li><span class="bold">KVKK Md. 5/2-ç:</span> Veri sorumlusunun hukuki yükümlülüğünü yerine getirebilmesi için zorunlu olması (Fatura kesimi, MASAK kuralları, log tutma zorunluluğu).</li>
    <li><span class="bold">KVKK Md. 5/2-f:</span> İlgili kişinin temel hak ve özgürlüklerine zarar vermemek kaydıyla, veri sorumlusunun meşru menfaatleri için veri işlenmesinin zorunlu olması (Sistem güvenliği ve fraud kontrolü).</li>
</ul>

<h3>6. KVKK MADDE 11 KAPSAMINDAKİ HAKLARINIZ</h3>
<p>Kişisel veri sahibi olarak KVKK'nın 11. maddesi uyarınca aşağıdaki haklara sahipsiniz:</p>
<ul>
    <li>Kişisel veri işlenip işlenmediğini öğrenme, işlenmişse bilgi talep etme,</li>
    <li>İşlenme amacını ve amacına uygun kullanılıp kullanılmadığını öğrenme,</li>
    <li>Yurt içinde veya yurt dışında aktarıldığı üçüncü kişileri bilme,</li>
    <li>Eksik veya yanlış işlenmişse düzeltilmesini isteme ve silinmesini/yok edilmesini talep etme (Yasal saklama süreleri dolan veriler için).</li>
</ul>
<p>Bu haklarınızı kullanmak için taleplerinizi kimliğinizi tevsik edici belgelerle birlikte Şirketimizin <span class="bold">Adalet Mh. Anadolu Cd. No 41/1 İç Kapı No: 1402 My Plaza - Bayraklı/İzmir</span> adresine yazılı olarak veya güvenli elektronik imza ile <span class="bold">playsel@hs04.kep.tr</span> KEP adresimize iletebilirsiniz.</p>

<div class="danger">
    <h3>7. KULLANICI BEYANI VE AYDINLATMA ONAYI</h3>
    <p>İşbu Aydınlatma Metni'ni okuduğumu, anladığımı, özellikle güvenliğimin sağlanması ve dolandırıcılığın önlenmesi (AML/KYC) maksadıyla IP adresimin, işlem kayıtlarımın ve gerektiğinde kimlik doğrulama verilerimin işlenerek adli makamlara aktarılabileceğini bildiğimi; 18 yaşından büyük olduğumu veya yasal velimin izniyle işlem yaptığımı özgür irademle kabul ve beyan ederim.</p>
    <ul>
        <li><span class="bold">Adı Soyadı / Unvanı:</span> <?= $alici_ad_soyad ?></li>
        <li><span class="bold">T.C. Kimlik No / VKN:</span> <?= $alici_tckn_vkn ?></li>
        <li><span class="bold">İletişim Adresi (Fatura/İkamet):</span> <?= $alici_fatura_adresi ?></li>
        <li><span class="bold">Telefon Numarası:</span> <?= $alici_telefon ?></li>
        <li><span class="bold">E-posta Adresi:</span> <?= $alici_eposta ?></li>
        <li><span class="bold">İşlem IP Adresi:</span> <?= $islem_ip_adresi ?></li>
        <li><span class="bold">Okuma ve Onay Zamanı:</span> <?= $onay_tarih_saati ?></li>
    </ul>
</div>
