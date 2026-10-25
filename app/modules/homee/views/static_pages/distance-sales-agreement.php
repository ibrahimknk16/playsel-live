<style>
    .ps-static-body h1 {
        font-size: 22px;
        text-align: center;
        color: #2980b9;
        border-bottom: 2px solid #2c3e50;
        padding-bottom: 10px;
        margin-bottom: 30px;
        text-transform: uppercase;
    }
    .ps-static-body h2 {
        font-size: 18px;
        color: #2980b9;
        margin-top: 30px;
        border-bottom: 2px solid #ecf0f1;
        padding-bottom: 5px;
    }
    .ps-static-body h3 {
        font-size: 15px;
        color: #2980b9;
        margin-top: 20px;
    }
    .ps-static-body p, .ps-static-body li {
        font-size: 14px;
        margin-bottom: 12px;
        text-align: justify;
    }
    .ps-static-body ul {
        margin-top: 5px;
        margin-bottom: 15px;
        padding-left: 20px;
    }
    .ps-static-body .bold {
        font-weight: bold;
    }
    .ps-static-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 14px;
    }
    .ps-static-body th, .ps-static-body td {
        border: 1px solid #bdc3c7;
        padding: 10px;
        text-align: left;
    }
    .ps-static-body th {
        background-color: #ecf0f1;
        color: #2c3e50;
    }
    .ps-static-body .highlight {
        background-color: #fcf8e3;
        padding: 15px;
        border-left: 4px solid #f39c12;
        margin: 20px 0;
        border-radius: 3px;
    }
    .ps-static-body .danger {
        background-color: #fdeaea;
        padding: 15px;
        border-left: 4px solid #e74c3c;
        margin: 20px 0;
        border-radius: 3px;
    }
</style>

<h1>ÖN BİLGİLENDİRME FORMU VE MESAFELİ SATIŞ SÖZLEŞMESİ</h1>

<h2>BÖLÜM 1: ÖN BİLGİLENDİRME FORMU</h2>

<h3>1. SATICI (ŞİRKET) BİLGİLERİ</h3>
<p><span class="bold">Unvanı:</span> Playsel Elektronik Ticaret Limited Şirketi (Bundan böyle "SATICI" veya "Playsell" olarak anılacaktır.)</p>
<p><span class="bold">Adres:</span> Adalet Mh. Anadolu Cd. No 41/1 İç Kapı No: 1402 My Plaza - Bayraklı/İzmir</p>
<p><span class="bold">Vergi Dairesi ve VKN:</span> 7300761424</p>
<p><span class="bold">Mersis No:</span> 0730076142400001</p>
<p><span class="bold">E-posta:</span> info@playsel.com</p>
<p><span class="bold">KEP Adresi:</span> playsel@hs04.kep.tr</p>
<p><span class="bold">UETS No:</span> 25929-96257-58393</p>
<p><span class="bold">Web Sitesi:</span> www.playsel.com</p>

<h3>2. ALICI (KULLANICI) BİLGİLERİ</h3>
<ul>
    <li><span class="bold">Adı Soyadı / Unvanı:</span> {{alici_ad_soyad}}</li>
    <li><span class="bold">T.C. Kimlik No / VKN:</span> {{alici_tckn_vkn}}</li>
    <li><span class="bold">Fatura Adresi:</span> {{alici_fatura_adresi}}</li>
    <li><span class="bold">Telefon Numarası:</span> {{alici_telefon}}</li>
    <li><span class="bold">E-posta Adresi:</span> {{alici_eposta}}</li>
</ul>

<h3>3. SÖZLEŞME KONUSU ÜRÜN VE HİZMETİN TEMEL NİTELİKLERİ</h3>
<p>İşbu Sözleşme'ye konu olan ve ALICI tarafından sipariş edilen ürün/hizmetlerin türü, miktarı, marka/modeli, satış bedeli ve ödeme şekli aşağıda belirtildiği gibidir. Ürünler fiziki bir kargoya tabi olmayıp, elektronik ortamda teslim edilecek dijital kod/lisans niteliğindedir.</p>

<div style="width: 100%; overflow-x: auto;">
    <table>
        <thead>
            <tr>
                <th>Ürün / Hizmet Adı</th>
                <th>Adet</th>
                <th>Birim Fiyatı</th>
                <th>Ara Toplam</th>
            </tr>
        </thead>
        <tbody>
            <!-- YAZILIMCI NOTU: Sepetteki ürün sayısına göre aşağıdaki tr etiketini döngüye (loop) alınız -->
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <!-- DÖNGÜ SONU -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;" class="bold">Genel Toplam (KDV Dahil):</td>
                <td class="bold"></td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right;" class="bold">Ödeme Yöntemi:</td>
                <td class="bold"></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="highlight">
    <h3>4. CAYMA HAKKI İSTİSNASI (ÖNEMLİ BİLGİLENDİRME)</h3>
    <p>Satın alınan ürünler dijital kod ve lisans anahtarı niteliği taşıdığından, 6502 Sayılı Tüketicinin Korunması Hakkında Kanun ve Mesafeli Sözleşmeler Yönetmeliği'nin 15. Maddesinin 1. Fıkrasının (ğ) bendi uyarınca; <span class="bold">"Elektronik ortamda anında ifa edilen hizmetler veya tüketiciye anında teslim edilen gayrimaddi mallar"</span> kategorisindedir. Bu nedenle, satın alınan dijital kodun veya e-pin'in ALICI'ya elektronik ortamda teslim edilmesiyle (ekranda görünmesi, e-posta veya SMS ile iletilmesi veya hesap profiline yansıtılması ile) birlikte <span class="bold">YASAL CAYMA (İPTAL VE İADE) HAKKI ORTADAN KALKAR.</span></p>
</div>

<h2>BÖLÜM 2: MESAFELİ SATIŞ SÖZLEŞMESİ</h2>

<h3>MADDE 1 – TARAFLAR VE ONAY ZAMANI</h3>
<p>İşbu Mesafeli Satış Sözleşmesi ("Sözleşme"), yukarıda bilgileri yer alan SATICI (Playsell) ile, elektronik ortamda sipariş veren ve IP adresi ile onay zamanı aşağıda kayıt altına alınan ALICI arasında akdedilmiştir.</p>
<ul>
    <li><span class="bold">İşlem IP Adresi:</span> {{islem_ip_adresi}}</li>
    <li><span class="bold">Sözleşme Onay Tarihi ve Saati:</span> {{onay_tarih_saati}}</li>
</ul>

<h3>MADDE 2 – KONU VE KAPSAM</h3>
<p>İşbu Sözleşme'nin konusu; ALICI'nın, SATICI'ya ait www.playsel.com alan adlı internet sitesi üzerinden elektronik ortamda siparişini verdiği ve Bölüm 1 Madde 3'te nitelikleri ile satış fiyatı belirtilen ürünün/hizmetin satışı ve teslimi ile ilgili olarak 6502 sayılı Tüketicinin Korunması Hakkında Kanun, Mesafeli Sözleşmeler Yönetmeliği ve 6098 sayılı Türk Borçlar Kanunu hükümleri gereğince tarafların karşılıklı hak ve yükümlülüklerinin belirlenmesidir.</p>

<h3>MADDE 3 – YAŞ SINIRI VE YETKİ</h3>
<p><span class="bold">3.1.</span> Platform üzerinden sipariş oluşturabilmek için ALICI'nın 18 yaşını doldurmuş olması gerekmektedir. 18 yaşından küçük ALICI'ların işlem yapması, kanuni temsilcilerinin (veli/vasi) onayına tabidir.</p>
<p><span class="bold">3.2.</span> ALICI, sözleşmeyi onayladığı an itibariyle 18 yaşından büyük olduğunu veya velisinin iznine sahip olduğunu, girmiş olduğu tüm kimlik ve iletişim bilgilerinin doğru olduğunu hukuken taahhüt eder. Yanlış beyanlardan doğacak zararlardan SATICI sorumlu değildir.</p>

<h3>MADDE 4 – CAYMA HAKKI VE İADE ŞARTLARI</h3>
<p><span class="bold">4.1.</span> İşbu Sözleşme kapsamında satılan tüm dijital ürünler (Oyun lisansları, E-Pin kodları, platform bakiyeleri vb.), Mesafeli Sözleşmeler Yönetmeliği'nin 15/1-(ğ) bendi kapsamındaki istisnalara tabidir. Ürün dijital ortamda ALICI'ya teslim edildiği an ifa tamamlanır ve cayma hakkı süresi sona erer.</p>
<p><span class="bold">4.2.</span> SATICI, sistem altyapısından kaynaklanan veya stok yetersizliği gibi sebeplerle siparişi teslim edememesi halinde işlemi iptal ederek tahsil edilen tutarı aynen iade etmekle yükümlüdür.</p>

<h3>MADDE 5 – HATALI VEYA GEÇERSİZ KOD PROSEDÜRÜ</h3>
<p><span class="bold">5.1.</span> ALICI, teslim aldığı kodun hatalı, geçersiz veya kullanılmış olduğunu iddia etmesi halinde SATICI'ya bildirimde bulunmalıdır.</p>
<p><span class="bold">5.2. İnceleme Süreci:</span> SATICI, bu iddia üzerine kodun aktif edilme tarih ve saatini tespit etmek amacıyla ilgili Yayıncı/Distribütör firma (Riot Games, Valve, Apple vb.) nezdinde log incelemesi başlatır. Bu süreç, uluslararası yayıncıların dönüş sürelerine bağlı olarak <span class="bold">24 ila 72 saat</span> sürmektedir.</p>
<p><span class="bold">5.3.</span> İnceleme neticesinde, kodun ALICI'ya (sistem üzerinden veya e-posta yoluyla) teslim edildiği tarih ve saatten <span class="bold">sonraki bir zamanda</span> kullanıldığı tespit edilirse, ALICI'nın değişim ve iade talebi kati suretle reddedilir. Kodun, SATICI tarafından teslim edilmeden önce kullanılmış veya arızalı olduğu tespit edilirse, SATICI derhal yeni bir kod temin eder veya para iadesi sağlar.</p>

<div class="danger">
    <h3>MADDE 6 – GÜVENLİK, KYC (MÜŞTERİNİ TANI) VE AML (KARA PARA AKLAMA) ÖNLEMLERİ</h3>
    <p><span class="bold">6.1. Yasal Zorunluluk:</span> SATICI, 5549 sayılı MASAK Kanunu ve TCMB mevzuatları çerçevesinde ödeme güvenliğini sağlamak ve suç gelirlerinin aklanmasını önlemek amacıyla denetim mekanizmalarına sahiptir.</p>
    <p><span class="bold">6.2. Fraud (Dolandırıcılık) ve Blokaj:</span> Çalıntı kredi/banka kartı ile işlem yapılması, sisteme yönelik siber saldırı, izinsiz hesap kullanımı veya risk algoritmalarının hileli bir işlem tespit etmesi (fraud şüphesi) durumunda SATICI; siparişi iptal etme, kod teslimatını durdurma ve ALICI'nın platform hesabını kalıcı olarak bloke etme hakkını haizdir.</p>
    <p><span class="bold">6.3. Kimlik Teyidi (KYC):</span> 3D Secure işleminin başarısız olması, farklı IP/Lokasyon tespiti veya şüpheli bakiye yüklemelerinde SATICI; ödemenin sahibini doğrulamak amacıyla ALICI'dan ek belgeler (Kimlik kartı, ödeme yapılan kartın ön yüzü, dilekçe, e-devlet ikametgahı vb.) talep edebilir. ALICI bu belgeleri sunmaktan kaçınırsa, sipariş iptal edilir ve tutar (yasal bir kısıtlama veya adli bir tedbir kararı yoksa) ödemenin kaynağına iade edilir.</p>
</div>

<h3>MADDE 7 – FİKRİ VE SINAİ MÜLKİYET HAKLARI</h3>
<p><span class="bold">7.1.</span> Platformda listelenen ve satışı yapılan oyunlara ait amblemler, logolar, kapak tasarımları, markalar ve lisansların tüm telif hakları 5846 sayılı FSEK uyarınca ilgili oyun geliştiricilerine ve 3. taraf yayıncılara aittir. Playsell sadece yetkili aracı bir perakende satıcıdır.</p>
<p><span class="bold">7.2.</span> Playsell web sitesinin yazılımı, tasarımı ve veritabanı hakları Playsel Elektronik Ticaret Limited Şirketi'ne ait olup izinsiz kullanılamaz.</p>

<h3>MADDE 8 – KİŞİSEL VERİLERİN KORUNMASI (KVKK) VE TİCARİ İLETİŞİM (ETK)</h3>
<p><span class="bold">8.1.</span> ALICI'ya ait işbu sözleşmede yer alan tüm kişisel veriler, siparişin ifası, e-fatura/e-arşiv düzenlenmesi ve yasal denetimler kapsamında 6698 sayılı KVKK'ya uygun olarak işlenmektedir. Aydınlatma metni site üzerinde mevcuttur.</p>
<p><span class="bold">8.2.</span> ALICI, 6563 sayılı ETK kapsamında, sipariş süreçlerine ilişkin bilgilendirme (SMS/E-posta) mesajlarını almayı kabul eder. Pazarlama maksatlı iletiler ise ALICI'nın ayrıca vereceği ticari iletişim onayına tabidir.</p>

<h3>MADDE 9 – UYUŞMAZLIKLARIN ÇÖZÜMÜ VE YETKİLİ MAHKEME</h3>
<p><span class="bold">9.1.</span> İşbu Sözleşme'nin uygulanmasında ve çıkabilecek uyuşmazlıklarda Türkiye Cumhuriyeti Yasaları uygulanır.</p>
<p><span class="bold">9.2.</span> ALICI'nın Tüketici sıfatını taşıdığı (ticari olmayan) işlemlerde; uyuşmazlığın çözümü için Ticaret Bakanlığı tarafından her yıl belirlenen parasal sınırlar dâhilinde ALICI'nın yerleşim yerindeki veya işlemi yaptığı yerdeki İl veya İlçe <span class="bold">Tüketici Hakem Heyetleri</span> yetkilidir.</p>
<p><span class="bold">9.3.</span> Hakem heyeti sınırını aşan tüketici uyuşmazlıkları ile ALICI'nın Tacir sıfatını haiz olduğu (B2B/Ticari alımlar) uyuşmazlıklarda <span class="bold">İzmir (Bayraklı) Mahkemeleri ve İcra Daireleri</span> münhasıran yetkilidir.</p>

<h3>MADDE 10 – YÜRÜRLÜK</h3>
<p>İşbu Ön Bilgilendirme Formu ve Mesafeli Satış Sözleşmesi, ALICI tarafından elektronik ortamda okunarak ve kabul edilerek {{onay_tarih_saati}} tarihinde onaylandığı an itibariyle karşılıklı olarak yürürlüğe girmiştir. Siparişin tamamlanması, ALICI'nın işbu sözleşmedeki tüm şartları gayrikabili rücu olarak kabul ettiği anlamına gelir. Sözleşmenin bir kopyası {{alici_eposta}} adresine kalıcı veri saklayıcısı vasıtasıyla gönderilmiştir.</p>
