# Playsel Live — İyileştirme ve Takip Planı

> Bu plan, depo incelemesine dayalı başlangıç planıdır; canlı ortam, veritabanı, müşteri metrikleri ve ekip süreçleri ayrıca doğrulanmalıdır. Kutular ilerleme oldukça güncellenir.

> Yönerge kontrolü: Depoda ve kontrol edilen üst klasörlerde `AGENTS.md`/`agents.md` bulunamadı.

## Hedef

Kullanıcı yönetimi ve ilişkili admin operasyonlarını güvenli, test edilebilir, dağıtımı tekrarlanabilir ve sürdürülebilir hale getirmek; sonrasında proje önceliklerini ölçülebilir biçimde takip etmek.

## Tahmin ve planlama varsayımları

- **Ön tahmin:** yaklaşık **12–22 aktif mühendislik iş günü**.
- **Takvim tahmini:** ekip incelemeleri, staging doğrulaması ve onaylarla yaklaşık **3–5 hafta**.
- İş kalemleri kısmen örtüşebilir; tahmin, ilk teknik doğrulama sonrasında revize edilmelidir.
- Üretim erişimi, veritabanı şeması, ödeme/bildirim sağlayıcıları ve iş kuralları için yetkili ekip geri bildirimi gerekebilir.
- Aktif geliştirme süresi ile paydaş onayı ve bekleme süresi birbirinden ayrıdır.

## Aşama 0 — Kapsamı ve mevcut durumu doğrula

**Tahmin: 1–2 iş günü**

- [x] PHP sürümlerini kaydet: yerel CLI 8.1.10; kullanıcının bildirdiğine göre staging ve production 8.3.
- [x] `composer.json` PHP gereksinimlerini kodun kullandığı özelliklerle karşılaştır; desteklenen sürüm aralığı tutarsızlığını kaydet. Manifestler `>=5.3.7` diyor; kod en az PHP 7.1 özellikleri kullanıyor.
- [x] Admin yetki tablolarının bulunduğu kullanıcı tarafından teyit edildi; uygulama bu DB'ye doğrudan bağlı olmadığı için bağımsız ortam doğrulaması yapılmadı.
- [x] Yetki tabloları bulunamadığında kodun erişime izin verdiği doğrulandı (`enforceAdminPermission()` fail-open davranıyor). Bunun ortamda fiilen tetiklenip tetiklenmediği henüz doğrulanmadı.
- [ ] Yetki istisnaları ayrıca doğrulanmalı: login/dashboard yanında bazı `admins.*` rotaları whitelist'te; bu rotaların rol tablosu yokken davranışı ve erişim gerekçesi gözden geçirilmeli.
- [x] Rol ve admin oluşturma kodu incelendi: `admin_power = 2` rolü `Super Admin`; `Users::addUser()` admin kaydını sabit `2` ile oluşturuyor, `Admins::addUser()` ise rol seçimini alıyor. Kullanıcı kararı: yeni adminleri yalnızca Super Admin oluşturabilir.
- [x] Admin CSRF yapılandırması ve form/AJAX kullanımı tarandı. CSRF kapalı; 56 PHP dosyasında 91 form/helper eşleşmesi ve 37 dosyada 160 AJAX/POST eşleşmesi bulundu; taramada açık CSRF tokenı veya `form_open()` kullanımı bulunmadı. Envanter eşleşmeleri elle endpoint doğrulaması gerektirir.
- [ ] `customer_restrictions` ve ilgili tabloların varlığı kullanıcı tarafından teyit edildi. DB değişikliklerini kullanıcı phpMyAdmin ile manuel uyguluyor; uygulama kodu SFTP ile yükleniyor. Depoda görünen app migration'ı `support_and_transfer_expiry`; admin migration dizini ve uygulamanın referans verdiği `migration/operations_requests.sql` bu incelemede bulunamadı. Sürümleme/yedekleme/rollback akışını doğrula.
- [ ] Bakiye, ödeme, sipariş, şifre sıfırlama ve impersonation için iş sahibi/karar vericileri belirle.
- [ ] Her bulguya bir sorumlu, önem derecesi ve hedef tarih ata.

**Çıkış kriteri**

- [ ] Ortamlar arası PHP ve veritabanı şema farkları belgelenmiş.
- [ ] Erişim kontrolü, CSRF ve admin oluşturma akışlarının mevcut davranışı doğrulanmış.
- [ ] İlk aşamadaki bulgular için sorumlu ve öncelik belirlenmiş.

## Aşama 1 — Yetki ve CSRF risklerini azalt

**Tahmin: 2–4 iş günü**  
**Bağımlılık:** Aşama 0 bulguları ve iş sahibi onayı.

- [x] Rol tablosu bulunmadığında whitelist dışı, rol kontrolüne tabi istekler fail-closed hale getirildi; Super Admin erişimi korunuyor, diğer istekler loglanıp 503/uyarı ile reddediliyor.
- [ ] Her admin rolü için kullanıcı yönetimi ekranı ve durum değiştiren eylemleri kapsayan yetki matrisi oluştur.
- [x] Admin oluşturma akışında rol politikasını belirle: yeni adminleri yalnızca Super Admin oluşturabilir.
- [x] Her iki admin oluşturma ucuna Super Admin kontrolü eklendi; rol girdisi `Admins::addUser()` içinde 2/3/4 ile sınırlandı.
- [x] Kısa vadeli karar: yüzlerce form/AJAX akışı nedeniyle global CSRF açılımı şu an yapılmayacak. Bu, riskin sıfır olduğu değil; kontrollü biçimde ertelendiği anlamına gelir.
- [ ] Cloudflare Zero Trust'ın admin origin'ini gerçekten koruduğunu ve doğrudan origin erişiminin kapalı olduğunu doğrula. Zero Trust oturumlu tarayıcıdan kaynaklanabilecek CSRF'yi tek başına engellemez.
- [ ] Admin session cookie ayarlarını incele: yapılandırmada Secure ve HttpOnly açık, SameSite Lax, oturum süresi 7200 saniye ve IP eşleştirme kapalı. Canlı yanıt başlıklarında ve gerçek tarayıcıda doğrula; Lax ek azaltımdır, mutlak CSRF koruması değildir.
- [ ] Durum değiştiren admin rotalarını tara; hiçbir değişiklik GET ile yapılmamalı. Yüksek etkili işlemlerin (admin/rol, bakiye, parola, hesap durumu, impersonation) listesini çıkar.
- [ ] Mevcut 2FA'nın yalnızca girişte mi, yoksa hassas işlem öncesinde yeniden doğrulama olarak da mı kullanıldığını doğrula; gerekirse yalnızca yüksek etkili işlemlere step-up doğrulama ekle.
- [ ] Yüksek etkili uçlar için hedefli token veya eşdeğer istek doğrulaması seçeneğini staging'de dene; modül modül ilerle, tüm formlar uyumlu olmadan global korumayı açma.
- [ ] CSRF'yi geçici olarak erteleme kararı için kalan risk, telafi kontrolleri, sorumlu ve yeniden değerlendirme tarihi kaydet.
- [ ] Özel impersonation CSRF kontrolünü koru ve ortak CSRF yaklaşımıyla tutarlı çalıştığını doğrula.
- [ ] Erişim reddi ve kritik admin işlemlerini hassas veri içermeyecek şekilde logla.
- [ ] Yetkisiz ve geçersiz isteklerde açık hata yanıtı ve doğru HTTP durum kodu sağla.

**Çıkış kriteri**

- [ ] Yetki tabloları yokken rol kontrolüne tabi istekler reddediliyor; whitelist rotalarının istisna davranışı gerekçelendirilip test edildi.
- [ ] Yetkisiz roller kullanıcı ve admin işlemlerini gerçekleştiremiyor.
- [ ] CSRF yaklaşımı ve kalan risk belgelenmiş; seçilen hedefli korumalar test edilmiş veya telafi kontrolleriyle geçici risk kabulinin sorumlusu ve gözden geçirme tarihi atanmış.
- [ ] Kritik rol ve erişim kararları için otomatik test mevcut.

## Aşama 2 — Veritabanı değişikliklerini güvenilir hale getir

**Tahmin: 1–3 iş günü**  
**Bağımlılık:** Aşama 0 şema envanteri.

- [ ] `customer_restrictions` için uygulamanın referans verdiği migration/SQL kaynağını depoda ve dağıtım sürecinde doğrula.
- [ ] Eksik şema değişikliklerini proje standardına uygun, sürümlü ve tekrarlanabilir migration olarak yönet.
- [ ] Migration'ın yeni kurulum ve mevcut ortam senaryolarındaki davranışını doğrula.
- [ ] Migration öncesi yedekleme, uygulama sırası, hata halinde geri dönüş ve doğrulama adımlarını tanımla.
- [ ] Geliştirme, staging ve production migration sürümlerinin nasıl karşılaştırılacağını belirle.
- [ ] Başarısız migration'ın dağıtımı durdurduğunu ve operatöre açık hata verdiğini doğrula.

**Çıkış kriteri**

- [ ] Gerekli şema, belgelenmiş tek bir dağıtım yoluyla kurulabiliyor.
- [ ] Ortam farkı ve geri dönüş yaklaşımı yazılı.
- [ ] Kısıtlama özelliği eksik tablo durumunda yanıltıcı başarı göstermiyor.

## Aşama 3 — Kritik akışlar için test ve yayın güvencesi

**Tahmin: 3–5 iş günü**  
**Bağımlılık:** Aşama 1 ve 2 ile paralel başlatılabilir.

- [ ] Projenin desteklediği PHP sürümlerinde çalışacak test komutunu ve test altyapısını doğrula.
- [ ] Kullanıcı bilgisi güncelleme: geçerli veri, geçersiz e-posta, yinelenen e-posta ve değişiklik yok senaryolarını test et.
- [ ] Bakiye ekleme/çıkarma: geçerli tutar, geçersiz tutar ve işlem başarısızlığı senaryolarını test et.
- [ ] Şifre sıfırlama: bildirim seçimi, parola güncellemesi, oturum geçersizleştirme ve e-posta/SMS başarısızlığı senaryolarını test et.
- [ ] Kısıtlama oluşturma/kaldırma: yetki, süre, durum ve veritabanı hatası senaryolarını test et.
- [ ] Kullanıcı durumu, kategori erişimi ve varsayılan fatura adresi değişikliklerini test et.
- [ ] Admin rolü, CSRF ve impersonation erişim kontrolleri için regresyon testleri ekle.
- [ ] Listeleme, filtreleme, sayfalama ve CSV export için çıktı ve sınır durumlarını test et.
- [ ] Testleri mevcut CI/yayın hattına ekle; test başarısızlığında yayın yapılmamasını sağlayacak kuralı belirle.
- [ ] Kritik akışlar için yayın öncesi smoke test listesi hazırla.

**Çıkış kriteri**

- [ ] Kritik akışların başarılı ve başarısız durumları otomatik testlerle doğrulanıyor.
- [ ] Test komutu belgeli ve yayın sürecinde çalıştırılıyor.
- [ ] Hata yanıtı, kayıt sonucu ve kullanıcıya gösterilen mesaj birbiriyle tutarlı.

## Aşama 4 — Kullanıcı yönetimi modülünü sürdürülebilir hale getir

**Tahmin: 4–8 iş günü**  
**Bağımlılık:** Kritik testlerin eklenmesi; küçük ve ölçülebilir adımlarla ilerlenmeli.

- [ ] `Users` controller içindeki metotları iş alanlarına göre sınıflandır: profil, bakiye, sipariş/ödeme, fraud/kısıtlama, adres ve referral.
- [ ] İlk çıkarılacak iş alanını test kapsamı ve değişiklik riski en düşük olacak şekilde seç.
- [ ] Seçilen alanı mevcut davranışı koruyarak uygun controller/model/service yapısına taşı.
- [ ] Yetki kontrolü, girdi doğrulaması, transaction ve hata yanıtı kalıplarını tutarlı hale getir.
- [ ] Kullanıcıdan gelen verilerin HTML/CSV çıktılarında güvenli biçimde işlendiğini doğrula.
- [ ] DB transaction gereken çok adımlı adres ve kısıtlama değişikliklerinde kısmi güncelleme riskini gider.
- [ ] Taşıma sonrasında ilgili regresyon testlerini ve kod incelemesini tamamla.
- [ ] Sonraki iş alanları için ayrı kapsam ve tahmin oluştur; tek seferde büyük yeniden yazım yapma.

**Çıkış kriteri**

- [ ] İlk iş alanı eski ve yeni akışlar arasında işlevsel olarak eşdeğer.
- [ ] İlgili testler geçiyor; yetki ve hata davranışı değişiklikleri belgeli.
- [ ] Refactor, kullanıcıya görünür davranışı onaysız değiştirmiyor.

## Aşama 5 — Operasyon, dokümantasyon ve ölçüm

**Tahmin: 1–2 iş günü**  
**Bağımlılık:** Ürün ve operasyon sahiplerinin kararları.

- [ ] Ürün hedefi, öncelikli kullanıcı problemleri, kapsam dışı maddeler ve yol haritasını belirle.
- [ ] Bakiye, şifre sıfırlama, kısıtlama, fraud belgesi ve impersonation süreçleri için rol/onay/geri alma kurallarını yaz.
- [ ] Kritik işlemlerde “kim, neyi, ne zaman, hangi gerekçeyle yaptı?” bilgisinin kaydedildiğini doğrula.
- [ ] KVKK ve veri saklama kararlarını (adres, IP, fraud belgeleri, erişim logları) veri sorumlularıyla netleştir.
- [ ] Ödeme, e-posta/SMS ve dosya depolama sağlayıcılarında hata durumunda operasyon sahibini ve eskalasyon yolunu belirle.
- [ ] Yayın, migration, rollback ve staging doğrulama kontrol listelerini hazırla.
- [ ] Başlangıç değerlerini çıkarıp ekip onaylı hedeflerle takip et: sipariş başarı oranı, teslimat süresi, ödeme hataları, destek çözüm süresi, fraud inceleme süresi ve yayın geri alma oranı.

**Çıkış kriteri**

- [ ] Operasyonel süreçlerin sahibi, eskalasyon yolu ve geri alma adımları belli.
- [ ] KPI tanımları ve ölçüm kaynakları ekip tarafından onaylı.
- [ ] Yayın ve olay müdahale kontrol listeleri erişilebilir.

## Aşama 6 — Son doğrulama ve yayına hazırlık

**Tahmin: 1–3 iş günü**

- [ ] Test, statik kontrol ve uygulama build/başlangıç kontrollerini çalıştır.
- [ ] Migration'ı staging'de uygula; kısıtlama ve kullanıcı yönetimi akışlarını doğrula.
- [ ] Yetkisiz erişim, CSRF, işlem hatası ve bildirim sağlayıcısı arızası smoke testlerini tamamla.
- [ ] Loglarda parola, token, kimlik veya fraud belgesi gibi hassas değerlerin bulunmadığını kontrol et.
- [ ] Yayın onayı, sorumlu kişi, geri dönüş tetikleyicisi ve iletişim planını belirle.
- [ ] Production sonrası temel sağlık kontrollerini ve ilk dönem izleme sorumlusunu belirle.

**Çıkış kriteri**

- [ ] Staging doğrulaması ve yayın onayı kayıtlı.
- [ ] Geri dönüş planı uygulanabilir ve sorumlusu atanmış.
- [ ] Yayın sonrası izleme ve olay eskalasyonu hazır.

## Karar ve bağımlılık bekleyen maddeler

- [x] `admin_power = 2` rolü `Super Admin`; yeni adminleri yalnızca Super Admin oluşturmalı (kullanıcı kararı).
- [x] Staging ve production PHP sürümleri kullanıcı tarafından 8.3 olarak bildirildi; Composer PHP gereksinim bildirimi için uyumluluk kararı verilmeli.
- [x] CSRF duruşu kararı: şu an global açılım yapılmayacak; önce maruziyet ve telafi kontrolleri doğrulanıp yüksek etkili uçlar risk bazlı ele alınacak.
- [x] Mevcut dağıtım yöntemi kullanıcı tarafından bildirildi: DB SQL phpMyAdmin ile manuel, kod SFTP ile yükleniyor; otomatik migration/CI hattı yok.
- [ ] Kullanıcı yönetimi işlemlerinde hangi rollerin hangi alanları değiştirebileceği onaylandı.
- [ ] KPI tanımları, veri kaynağı ve hedefler ürün/operasyon sahiplerince onaylandı.

## İlerleme özeti

| Aşama | Durum | Tahmin |
|---|---|---:|
| 0 — Mevcut durumu doğrula | Devam ediyor — PHP, rol politikası, tablo varlığı ve manuel dağıtım bilgileri kullanıcı tarafından teyit edildi; rollback/yedekleme ve iş sahipleri bekliyor | 1–2 iş günü |
| 1 — Yetki ve CSRF | Devam ediyor — admin oluşturma uçları ve izin-denetimli rotalarda fail-closed eklendi; global CSRF kısa vadede ertelendi, origin/cookie/GET ve yüksek etkili işlem kontrolleri bekliyor | 2–4 iş günü |
| 2 — Migration ve şema | Devam ediyor — mevcut manuel phpMyAdmin/SFTP akışı belgelendi; sürümleme, yedekleme ve rollback standardı bekliyor | 1–3 iş günü |
| 3 — Test ve yayın güvencesi | Başlamadı | 3–5 iş günü |
| 4 — Modül sürdürülebilirliği | Başlamadı | 4–8 iş günü |
| 5 — Operasyon ve ölçüm | Başlamadı | 1–2 iş günü |
| 6 — Son doğrulama | Başlamadı | 1–3 iş günü |

> Aşama tahminleri örtüşebilir. Toplam ilk planlama aralığı **12–22 aktif iş günü**; Aşama 0 tamamlanınca risk ve bağımlılıklara göre yeniden tahmin edilmelidir.
