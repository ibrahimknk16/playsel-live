# Otomatik Fatura Kesim Sistemi - Mevcut Akış ve Geliştirme Adımları

## Amaç ve kapsam

Bu doküman, admin panelindeki manuel toplu fatura kesim akışının bugün nasıl çalıştığını, yeni fatura adresi yapısının siparişe nasıl taşındığını ve son kullanıcıya otomatik fatura kesimine geçmeden önce kapatılması gereken boşlukları kaydeder.

Geliştirme cron'a bağlanmadan ilerliyor. Admin'deki günlük aday önizlemesi ve müşteri bazlı fatura önizlemesi belge göndermez; önizleme hesaplanan UBL kalemlerini ve toplamları gösterir. `/admin/fatura/fatura-kesim` sayfasında açık onaylanan manuel kesim gerçek Birfatura gönderimi yapar. Veritabanı şeması değiştirilmedi ve gerçek gönderim doğrulama testi yapılmadı.

## 1. Bugünkü manuel fatura akışı

İlgili ekran ve servis:

- Admin ekranı: [`Fatura::fatura_kesim`](admin/app_admin/modules/fatura/controllers/Fatura.php) ve [`fatura_kesim.php`](admin/app_admin/modules/fatura/views/fatura_kesim.php)
- Birfatura entegrasyonu: [`Birfatura`](admin/app_admin/libraries/Birfatura.php)
- Test modülünde benzer, ayrı bir eski uygulama daha var: [`Test::fatura_kesim`](admin/app_admin/modules/test/controllers/Test.php) ve [`fatura_kesim.php`](admin/app_admin/modules/test/views/fatura_kesim.php)

Akış adım adım: (admin paneli sadece deneme ve test için olacak.)

1. Admin reseller/kullanıcı ile başlangıç, bitiş ve fatura tarihini seçer.
2. Önizleme veya kesim isteğinde tarih ve kullanıcı seçimi doğrulanır.
3. Siparişler `orders` tablosundan; seçilen `order_reseller_id`, tarih aralığı, `order_status = 1` ve `order_invoice_status = 0` koşullarıyla alınır. Ürün bilgisi `products`, alıcı bilgisi güncel `users` satırından birleştirilir.
4. Sonuç yoksa işlem durur. Siparişler tek bir alıcıya ait olacak şekilde seçilir ve ilk siparişin `users` kaydı fatura alıcısı olarak kullanılır.
5. Servis alıcının `user_bill_tax_number` değeriyle e-Fatura mükellefiyetini sorgular. Sonuç ve eski `user_bill_e_invoice` alanı e-Fatura/e-Arşiv seçimini belirler. (bunlar artık user_addresses tablosundan alıyor.)
6. Sipariş kalemleri ürün, miktar, maliyet ve ürünün KDV tipine göre gruplanır. Mevcut kod e-Pin maliyetini %0, e-Pin kârını hizmet bedeli olarak %20; tam KDV’li ürünleri %20; yabancı firma akışını istisna olarak hesaplar.
7. Fatura alıcı alanları mevcut `users` satırından, fatura kalemleri ise sipariş/ürün kayıtlarından hazırlanır. UBL oluşturulur ve Birfatura `SendDocument` servisine gönderilir.
8. Birfatura başarılı cevap verirse dönen fatura numarası, UUID ve tarih seçilmiş sipariş satırlarına yazılır; `order_invoice_status` 1 yapılır.
9. Önizleme fatura kesmez; kesim ayrı bir onayla başlatılır.

Önemli gözlem: Ekran metninde “reseller” yazsa da kullanıcı filtresindeki `user_type` koşulu devre dışı. Seçim tüm `users` kayıtlarından yapılabiliyor. Bunun reseller döneminden kalan adlandırma olduğu ve yeni son kullanıcı modelinin doğrudan karşılığı olmadığı dikkate alınmalı.

## 2. Sipariş durumu ve otomatik kesim için adaylık

Yeni site siparişleri [`Payment`](app/modules/payment/controllers/Payment.php) içinde başlatılıyor. Sipariş ilk oluşturulduğunda `order_status = 11` ve `order_billing_address` alanına adres snapshot’ı yazılıyor.

Mevcut kodda `order_status = 1`, ödeme onayından ziyade teslimatın tamamlanmasıyla ilişkilidir: [`Cron2::deliverOrder`](app/modules/cron2/controllers/Cron2.php), tedarikçi sonucu `4` olan siparişi `1` durumuna geçiriyor. Kullanıcı sipariş geçmişi de `1` durumunu tamamlanmış sipariş olarak ele alıyor.

Kararlaştırılan otomatik fatura adayı, mevcut manuel seçimle tutarlı biçimde:

- Teslimatı tamamlanmış (`order_status = 1`)
- Henüz faturalanmamış (`order_invoice_status = 0`)
- Kullanılabilir fatura alıcı verisi bulunan

bir sipariş/sipariş grubu olmalıdır. İptal, iade, fraud incelemesi ve kısmi teslimat senaryoları ayrıca doğrulanmalıdır.

## 3. Yeni fatura adresi akışı

İlgili kod:

- Checkout adres formu ve adres seçimi: [`checkout.php`](app/modules/payment/views/checkout.php)
- Checkout adresini kaydetme/seçme: [`Payment::updateBillingCheckout`](app/modules/payment/controllers/Payment.php) ve `selectAddressCheckout`
- Profil fatura adresi CRUD/default seçimi: [`Profile::updateBilling`](app/modules/profile/controllers/Profile.php), `setDefaultAddress`, `deleteAddress`
- Sipariş oluşturulurken snapshot alma: [`Payment`](app/modules/payment/controllers/Payment.php), sipariş kayıt bloğu

Güncel davranış:

1. Fatura adresi `user_addresses` tablosunda tutuluyor; kullanıcı birden fazla adres kaydedebiliyor ve varsayılan adres seçebiliyor.
2. Adres türü bireysel/kurumsal. Türkiye vatandaşlığı/şirketi seçimine göre alan zorunlulukları değişiyor.
3. Profil kaydında ad-soyad veya şirket unvanı, adres ve konum alanları isteniyor. Türk bireysel kullanıcıda TCKN, Türk şirketinde vergi dairesi ve vergi numarası da doğrulanıyor. Yabancı kullanıcı/şirket için bu Türk kimlik/vergi alanları aynı şekilde zorunlu değil.
4. Adres telefonu profil kaydında zorunlu değil. Checkout arayüzünde telefon alanı zorunlu gösteriliyor; ancak sunucu tarafındaki adres kaydetme metodu telefonun boş olmasını reddetmiyor ve kullanıcı telefonuna geri düşebiliyor.
5. E-posta `user_addresses` adres verisinin bir alanı değil; hesap e-postası `users` kaydında bulunabilir.
6. Varsayılan adres seçilince bazı eski `user_bill_*` alanları `users` tablosuyla senkronize ediliyor. Bu eski alanların varlığı, otomatik fatura alıcısı olarak güvenilir veya güncel oldukları anlamına gelmiyor.

## 4. Sipariş anı adres snapshot’ı ve mevcut kopukluk

Sipariş oluşturulurken seçilmiş varsayılan aktif adres snapshot olarak JSON `order_billing_address` alanına kopyalanıyor. Varsayılan adres yoksa son aktif adres deneniyor. Hiç adres yoksa kod eski `users.user_bill_*` alanlarından bir fallback snapshot oluşturuyor.

Snapshot şu bilgileri içeriyor: adres ID’si/başlığı, fatura tipi, vatandaşlık/şirket ülke durumu, ad-soyad, şirket unvanı, vergi dairesi/no, e-Fatura mükellefiyeti, ülke/il/ilçe/posta kodu/açık adres ve snapshot zamanı.

Mevcut snapshot’ta telefon ve e-posta yok. Bunlar fatura adresi tablosunda birlikte yönetilmiyor; telefon adres tablosunda veya kullanıcı profilinde, e-posta ise kullanıcı hesabında olabilir.

Eski manuel kesim `order_billing_address` alanını hiç okumuyor; alıcı bilgilerini kesim anındaki güncel `users.user_bill_*` alanlarından alıyor. Bu davranış, sipariş verildikten sonra kullanıcının varsayılan adresini değiştirmesi halinde geçmiş sipariş faturasının farklı alıcı bilgileriyle hazırlanmasına yol açabilir.

Ayrıca Birfatura UBL eşleyicisi eski `user_bill_*` anahtarlarını ve bazı genel adları (`company_name`, `address`, `city`, `district`, `tax_office`, `vkn`, `name`, `phone`, `email`) okuyabiliyor. Sipariş snapshot’ındaki `full_name` ve `tax_number` anahtarları doğrudan aynı adlar değil. Snapshot’ı doğrudan servise vermek, alan eşlemesi yapılmadan doğru alıcı belgesi üretileceği anlamına gelmez.

## 5. Otomatik son kullanıcı akışında çözülmesi gerekenler

### Alıcı verisi ve doğrulama

- Alıcı, sipariş anında kaydedilmiş `order_billing_address` snapshot'ından kurulmalı; sonradan güncellenen varsayılan adres eski siparişi değiştirmemeli.
- Onaylı istisna kuralı gelene kadar eksik/geçersiz snapshot faturaya gönderilmemeli; kullanıcı/admin'e bildirilecek tamamlama ve yeniden deneme adımı uygulama tasarımında görünür olmalı.
- Birfatura kütüphanesi telefon/e-posta alanlarını mevcut durumda opsiyonel iletişim alanları olarak yazıyor. Bu alanların sipariş snapshot'ında tutulması ve/veya faturaya iletilmesinin gerekli olup olmadığı entegratör/muhasebe gereksinimiyle teyit edilmeli.
- Türkiye/yurt dışı bireysel/kurumsal müşteri için geçerli alan ve belge tipleri netleştirilmeli; yabancı alıcının ülke/adres bilgisi Birfatura UBL çıktısına doğru taşınmalı.

### Fatura sınırı ve zamanlama

- Aynı kullanıcının aynı Türkiye takvim gününde ve aynı alıcı snapshot'ıyla tamamlanan siparişleri tek faturada gruplanmalı; farklı snapshot'lar ayrı gruplardır.
- Bundle/çok kalemli satın alımda tüm satırlar tamamlanmadan fatura kesilmemeli.
- Günlük çalıştırmada bir önceki Türkiye takvim gününe ait siparişler faturalanmalı ve faturanın belge tarihi o önceki gün olmalı. Örneğin 29.10.2026 çalıştırması 28.10.2026 tarihli uygun siparişleri, 28.10.2026 fatura tarihiyle işler.
- Gün aralığı siparişin `order_created_at` zamanına göre `Europe/Istanbul` yerel takvim gününde hesaplanmalı; aday sipariş yine teslimatı tamamlanmış olmalı. Bundle satırlarının tamamlanması sonraki güne sarkarsa, son satır tamamlandıktan sonraki çalıştırmada grup faturalanabilir; fatura belge tarihi, grubun sipariş günü olarak kalır.
- e-Fatura/e-Arşiv seçimi Birfatura mükellefiyet sorgusuyla yapılmalı; sorgu hatası/erişilememe davranışı henüz netleştirilmedi.

### Tekrarlı çalışma, hata ve izlenebilirlik

- Eski akış, Birfatura başarılı cevabından sonra siparişleri faturalanmış işaretliyor. API gönderiminden sonra uygulama/DB hatası olursa aynı siparişin ikinci kez gönderilme riski var.
- `order_invoice_status` mevcut akışta başarı/başarısız ayrımını ve kilit/işleniyor durumunu tek başına ifade etmiyor.
- Cron eklenmeden önce aynı siparişin paralel çalışmalarda iki kez alınmasını engelleyen claim/idempotency tasarımı ve API sonucu belirsiz kaldığında uzlaştırma yolu tanımlanmalı.
- Başarısız alıcı verisi, servis hatası ve belirsiz API cevabı; hata nedeni, deneme zamanı ve yeniden deneme kararıyla görünür olmalı. Başarısızlık başarılı fatura gibi işaretlenmemeli.
- Eski `Test::fatura_kesim` uygulaması, vergi/fatura hesaplaması ve müşteri eşleme davranışlarında farklılık gösterebiliyor. Otomatik iş için admin `Fatura` ile ayrı test kopyaları değil, tek bir ortak fatura oluşturma kuralı kullanılmalı.

## 6. Önerilen geliştirme sırası (cron en son)

1. **Fatura kurallarını sabitle:** Bir önceki Türkiye takvim gününü işleme, sipariş gününü fatura tarihi kullanma, aynı kullanıcı/gün/alıcı snapshot'ı ile gruplama ve bundle'ın tüm satırlarını bekleme kuralları onaylandı. Yabancı müşteri alanları ve geçerli belge tipi ayrıca doğrulanmalı.
2. **Alıcı veri sözleşmesini belirle:** `order_billing_address` snapshot’ının alanları, telefon/e-posta kaynağı ve eksik veri aksiyonu açıkça tanımlansın. Fatura alıcı verisi sipariş anında sabitlenmiş olsun.
3. **Snapshot uyarlamasını tasarla:** Snapshot alanları doğrulansın ve Birfatura UBL’nin beklediği alıcı alanlarına açıkça eşlensin. `full_name`/`tax_number` gibi alanların eski `users` anahtarlarıyla karıştırılması engellensin.
4. **İdempotent fatura kaydını hazırla:** Fatura denemesi, kalıcı sonuç, hata ve yeniden deneme bilgisi saklanabilsin. API başarılı olduktan sonra DB güncellemesi başarısız olursa çift fatura üretmeyecek bir uzlaştırma yaklaşımı belirlenmiş olsun.
5. **Ortak kesim servisini uygula:** Aday seçimi, doğrulama, fatura kalemi hesaplama, Birfatura çağrısı ve sonuç kaydı tek bir tekrar kullanılabilir akış olsun. Manuel admin aracı ve otomasyon aynı hesaplama/alıcılık kurallarını kullansın.
6. **Cron olmadan doğrula:** Önizleme/dry-run ve kontrollü manuel tetikleme ile gerçek sipariş snapshot’ları üzerinde alan eşlemesi, toplamlar, vergi türleri, servis başarı/hata yanıtı ve tekrar çalıştırma test edilsin. Üretim faturası göndermeyen test modu tercih edilsin.
7. **Son kullanıcı görünürlüğünü tamamla:** Fatura durumu, başarılı faturanın numara/tarih/döküman erişimi ve eksik bilgi/hata halinde kullanıcıya gösterilecek mesaj belirlenip uygulanmalı.
8. **Cron’u en son aç:** Önce sınırlı kapsamda, kilitli ve izlenebilir çalıştır; sonuçlar doğrulandıktan sonra genel otomasyona geç. Cron sıklığı, batch limiti, paralellik ve uyarı/operasyon sahibi bu aşamada belirlenmeli.

## 6.1. Onaylanan iş kuralları

İlk karar turunda aşağıdaki kurallar seçildi:

- Fatura adayı, teslimat tamamlandığında (`order_status = 1`) oluşur.
- Aynı kullanıcının aynı Türkiye takvim günündeki siparişleri gruplama adayıdır (`Europe/Istanbul`).
- Günlük grupta yalnızca aynı fatura alıcısı/snapshot alanlarına sahip siparişler birleştirilir; farklı alıcı bilgileri ayrı fatura grubunda kalır.
- Bir bundle/çok satırlı satın alım ancak tüm satırları tamamlandığında faturalanır.
- Her günlük çalıştırma bir önceki Türkiye takvim gününün siparişlerini tarar; yalnızca uygun ve tamamlanmış siparişler faturalanır.
- Fatura belge tarihi, siparişin oluşturulduğu Türkiye yerel takvim günüdür. Örneğin 29 Ekim çalıştırması, 28 Ekim siparişleri için 28 Ekim tarihli fatura üretir.
- Bundle'ın tüm satırları sonraki güne sarkarsa, grup tamamlanana kadar bekler; tamamlandığı sonraki çalıştırmada işlenir ve belge tarihi sipariş günü olarak kalır.
- Kullanıcı, hiç fatura adresi tanımlamamış hesaplar için aşağıdaki muhtelif alıcı fatura kuralını onayladı.

## 6.2. Fatura adresi olmayan kullanıcı kuralı

Sipariş snapshot'ında hiç fatura adresi bulunmayan veya Auth tarafından otomatik oluşturulmuş, kullanıcı tarafından girilmemiş varsayılan adres bulunan kullanıcılar için fatura alıcısı şu şekilde hazırlanır:

- Ad soyad: kullanıcının hesap ad ve soyadı.
- Açık adres: ``.
- İlçe/il: `Kadıköy / İstanbul`.
- TCKN: `11111111111`.
- Fatura tipi: bireysel, ülke: Türkiye.
- Kullanıcı tarafından doldurulmuş gerçek fatura adresi varsa bu kural uygulanmaz; mevcut snapshot alanları kullanılır ve olağan doğrulamalar devam eder.
- Fallback yalnız snapshot hiç yoksa/boşsa veya Auth'un otomatik `Fatura Adresi` varsayılan imzası tanınıyorsa uygulanır. Kısmi ve farklı bir kullanıcı adresi varsayılan değerlerle üzerine yazılmaz; doğrulamadan geçmiyorsa bekletilir.

## 6.3. Günlük fatura adayları

Admin `/admin/fatura/fatura-kesim` ekranında **Günlük Fatura Adayları** önizlemesi bulunur:

- Varsayılan gün, `Europe/Istanbul` saat diliminde bir önceki gündür; geçmiş gün seçerek önizleme testi de yapılabilir.
- Yalnız site siparişleri (`order_type = 3`), seçilen yerel gün, teslimat tamam (`order_status = 1`) ve faturasız (`order_invoice_status = 0`) koşullarıyla listelenir.
- Siparişler kullanıcı ve normalize edilmiş fatura alıcısı alanlarına göre gruplandırılır; `snapshot_at`, adres başlığı ve adres ID'si alıcı kimliğinin parçası sayılmaz.
- Yerli bireysel/kurumsal için alan varlığı ve temel numara uzunluğu kontrol edilir. Yurt dışı alıcılar, onaylı ayrı fatura kuralı olmadığı için bekletilir. Bu ön kontrol, kimlik numarasının resmi/geçerli olduğunu doğrulamaz.
- Hiç fatura adresi tanımlamayan kullanıcılar, 6.2'deki onaylı muhtelif alıcı verileriyle önizlenir ve kesime uygunluk kontrollerinden geçirilir.
- Çok satırlı bundle grubu ancak tüm satırlar tamamlanmış, faturasız, aynı sipariş gününde ve aynı alıcı snapshot'ında ise ön kontrolden geçer.
- Günlük liste 1000 siparişi aşarsa kısmi/yanıltıcı sonuç yerine hata gösterilir.
- Bu ekran yalnız aday ön kontrolüdür: API çağrısı, fatura kaydı veya cron çalıştırması yapmaz.
- Eski reseller/tarih seçerek toplu kesim formu ve handler'ı kaldırılmıştır. Aday satırlarında fatura önizleme ve kullanıcı/snapshot grubuna özel manuel kesim bulunur; seçilen günün kesilmiş faturaları aynı sayfada görüntülenebilir.

## 6.4. Müşteri bazlı önizleme ve kontrollü manuel kesim

Müşteri listesinde her kayıt için fatura sayısı/son fatura bilgisi ve geçmiş faturaları kullanıcı detayında açan bağlantı bulunur. Günlük fatura ekranındaki aday satırlarında ayrıca **Fatura Önizle**, **Faturayı Elle Kes** ve **Kesilmiş Faturalar / Faturayı Gör** akışı sunulur.

- Günlük ekranın seçilen günü bugünden önce olmalıdır. Önizleme ve kesim kullanıcıya ve alıcı snapshot grubuna özeldir; yalnız `order_type = 3`, tamamlanmış ve faturalanmamış siparişleri alır.
- Önizleme, gerçek kesimle aynı alıcı snapshot doğrulamasını, bundle kontrolünü ve fatura kalemi/tutar hesaplamasını çalıştırır; alıcı, siparişler, ürün kalemleri, KDV ve genel toplamları gösterir. Belge göndermez.
- Manuel kesim POST + admin CSRF doğrulaması kullanır; kullanıcı/gün bazlı DB kilidi alır ve sipariş uygunluğunu sunucuda yeniden kontrol eder. Ekrandaki onay gerçek Birfatura gönderimini açıkça belirtir. Başarılı işlemden sonra aynı seçili gün yenilenerek kesilmiş faturalar listelenir.
- Birden fazla alıcı snapshot'ı ayrı fatura gruplarıdır. Eksik/uygunsuz gruplar bekletilir. Başarı, hata ve kısmi başarı ayrı bildirilir.
- Birfatura başarılı olduğu halde DB sipariş güncellemesi başarısız olursa işlem durur ve otomatik tekrar yapılmaz; fatura numarası/UUID ile entegratör panelinden uzlaştırma gerekir. Şema değişikliği yapılmadığı için bu durum için kalıcı claim/retry tablosu yoktur.
- Fatura geçmişindeki PDF bağlantısı snapshot'taki e-Fatura/e-Arşiv türünü önce dener, sonuç alamazsa diğer belge türüyle de sorgular.
- Bu adımda cron eklenmemiştir ve gerçek fatura gönderimi doğrulama amacıyla çalıştırılmamıştır.

## 7. Cron öncesi kabul kontrol listesi

- [ ] Fatura tetikleyicisinin teslimat tamamlanınca (`order_status = 1`) çalıştığı test edildi.
- [ ] İptal, iade, fraud incelemesi ve kısmi teslimat dahil sipariş statülerinin etkisi test edildi.
- [ ] Aynı kullanıcı/gün/alıcı snapshot'ı için gruplama sınırı test edildi.
- [ ] Günlük aday taramasının önceki `Europe/Istanbul` takvim gününü kapsadığı ve fatura belge tarihinin sipariş günü olduğu doğrulandı.
- [ ] Gün sınırında oluşturulan siparişler ve tamamlanması sonraki güne sarkan bundle'lar test edildi.
- [ ] Alıcı her zaman sipariş snapshot’ından kuruluyor; güncel profil değişikliği eski sipariş faturasını değiştirmiyor.
- [ ] Bireysel/kurumsal ve Türkiye/yurt dışı kombinasyonları gerçekçi test verileriyle doğrulandı.
- [ ] Onaylı alıcı veri kurallarına göre eksik/geçersiz bilgi faturayı bekletiyor ve nedeni görünür.
- [ ] Snapshot-Birfatura alan eşlemesi ve UBL alıcı çıktısı doğrulandı.
- [ ] Müşteri bazlı fatura önizlemesinin aynı fatura kalemi/toplamlarını kullandığı ve Birfatura'ya belge göndermediği doğrulandı.
- [ ] E-Pin, hizmet bedeli, tam KDV’li ürün, yabancı müşteri, çok ürün ve yuvarlama toplamları karşılaştırıldı.
- [ ] Başarısız, zaman aşımına uğramış ve belirsiz servis cevaplarında sipariş/fatura kaydı tutarlı kalıyor.
- [ ] Paralel manuel istekler DB kilidiyle engelleniyor; belirsiz Birfatura/DB sonucunda tekrar kesim öncesi uzlaştırma yapılıyor.
- [ ] Admin manuel kesimiyle otomatik kesim aynı ortak kuralları kullanıyor.
- [ ] Başarılı ve bekleyen fatura kullanıcı tarafından görülebiliyor; sorunlar admin tarafından teşhis edilebiliyor.
- [ ] Üretim cron’u ancak dry-run ve kontrollü kesim sonuçları onaylandıktan sonra etkinleştiriliyor.

## 8. Sonraki uygulama adımları

Mevcut kod adımı, müşteri bazlı önizleme ve kullanıcı tarafından onaylanan manuel kesimdir. Sonraki adımda gerçek test ortamında kontrollü örnek siparişler üzerinden snapshot-alıcı eşlemesi, fatura toplamları, Birfatura olumlu/olumsuz cevapları ve e-Fatura/e-Arşiv PDF erişimi doğrulanmalıdır. API başarılı olduktan sonra DB kaydının başarısız olması için şema gerektirmeyen güvenli uzlaştırma yöntemi belirlenmeden cron açılmamalıdır. Kalıcı idempotency, izleme ve hata/yeniden deneme kaydı çözüldükten sonra cron en son bağlanır.
