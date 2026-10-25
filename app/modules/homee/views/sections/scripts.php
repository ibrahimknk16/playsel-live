  <script>
    // ===== Coupons Modal Logic =====
    const couponsModal = document.getElementById('coupons-modal');
    const btnViewAllCoupons = document.getElementById('btn-view-all-coupons');
    const closeCouponsModal = document.getElementById('close-coupons-modal');
    if (btnViewAllCoupons && couponsModal && closeCouponsModal) {
      btnViewAllCoupons.addEventListener('click', () => {
        couponsModal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
      });
      closeCouponsModal.addEventListener('click', () => {
        couponsModal.classList.remove('show');
        document.body.style.overflow = '';
      });
      // Close on clicking outside
      couponsModal.addEventListener('click', (e) => {
        if (e.target === couponsModal) {
          couponsModal.classList.remove('show');
          document.body.style.overflow = '';
        }
      });
    }
    (function() {
      'use strict';

      const slideDataMap = <?= json_encode($jsData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const categoryKeys = <?= json_encode(array_values(array_map('strval', array_keys($jsData)))) ?>;
      if (!categoryKeys.length) return;

      let currentCategoryIndex = 0;
      let currentSubSlideIndex = 0;
      const slideDuration = 6000; // 6 Saniye
      let animFrameId = null;
      let animStartTime = null;
      let pausedElapsedTime = 0;
      let isPaused = false;

      function getCurrentCategory() {
        const catKey = categoryKeys[currentCategoryIndex];
        return slideDataMap[catKey] || null;
      }

      function getCurrentSlide() {
        const cat = getCurrentCategory();
        if (!cat || !cat.slides || !cat.slides.length) return null;
        return cat.slides[currentSubSlideIndex] || cat.slides[0];
      }

      /**
       * Akıcı Yavaşlama Eğrisi (Smooth Deceleration Ease-Out Curve)
       * Başlangıçta tempolu dolar, sona doğru doğal ve kesintisiz biçimde yavaşlar.
       */
      function calculateFluidProgress(timeRatio) {
        const t = Math.max(0, Math.min(1.0, timeRatio));
        const eased = 1 - Math.pow(1 - t, 2.25);
        return eased * 100;
      }

      function updateProgressBar(val) {
        const bar = document.getElementById('heroProgressBar');
        if (bar) {
          bar.style.width = val.toFixed(2) + '%';
        }
      }

      function restartProgress() {
        if (animFrameId) cancelAnimationFrame(animFrameId);
        animFrameId = null;
        animStartTime = null;
        pausedElapsedTime = 0;
        updateProgressBar(0);
        if (!isPaused) {
          animFrameId = requestAnimationFrame(stepProgress);
        }
      }

      function stepProgress(timestamp) {
        if (isPaused) return;

        if (!animStartTime) animStartTime = timestamp;

        const totalElapsed = (timestamp - animStartTime) + pausedElapsedTime;
        const timeRatio = Math.min(1.0, totalElapsed / slideDuration);
        const visualProgress = calculateFluidProgress(timeRatio);
        updateProgressBar(visualProgress);

        if (totalElapsed >= slideDuration) {
          nextSlide();
          return;
        }

        animFrameId = requestAnimationFrame(stepProgress);
      }

      function pauseAutoplay() {
        if (!isPaused) {
          isPaused = true;
          if (animStartTime !== null) {
            pausedElapsedTime = Math.min(slideDuration, pausedElapsedTime + (performance.now() - animStartTime));
          }
          if (animFrameId) {
            cancelAnimationFrame(animFrameId);
            animFrameId = null;
          }
          animStartTime = null;
        }
      }

      function resumeAutoplay() {
        if (isPaused) {
          isPaused = false;
          animStartTime = null;
          if (!animFrameId) {
            animFrameId = requestAnimationFrame(stepProgress);
          }
        }
      }

      function renderPagination() {
        const cat = getCurrentCategory();
        const container = document.getElementById('heroPaginationDots');
        const pill = document.querySelector('.hero-pagination-pill');
        if (!container || !cat || !cat.slides) return;

        container.innerHTML = '';
        const slidesCount = cat.slides.length;

        if (pill) {
          pill.style.display = slidesCount > 1 ? 'inline-flex' : 'none';
        }

        for (let i = 0; i < slidesCount; i++) {
          const dotBtn = document.createElement('button');
          dotBtn.type = 'button';
          dotBtn.className = 'hero-dot-btn';
          dotBtn.setAttribute('aria-label', 'Slayt ' + (i + 1));
          dotBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            goToSubSlide(i);
          };

          const indicator = document.createElement('span');
          indicator.className = 'hero-dot-indicator' + (i === currentSubSlideIndex ? ' active' : '');
          dotBtn.appendChild(indicator);
          container.appendChild(dotBtn);
        }
      }

      function renderActiveSlide() {
        const cat = getCurrentCategory();
        const slide = getCurrentSlide();
        if (!cat || !slide) return;

        // 1. Arka Plan Ambient Görselini Güncelle (Playsel Ambient Yansıma)
        const heroSection = document.getElementById('hero-section');
        if (heroSection && slide.image) {
          heroSection.style.setProperty('--ps-hero-bg', 'url("' + slide.image.replace(/"/g, '%22') + '")');
        }

        // 2. Ana Slayt Görseli & Mobil Görsel (Picture Tag)
        const mobileSource = document.getElementById('heroSlideSourceMobile');
        const slideImg = document.getElementById('heroSlideImg');
        const targetMobileImg = slide.mobile_image || slide.image;

        if (mobileSource && targetMobileImg) {
          mobileSource.srcset = targetMobileImg;
        }

        if (slideImg && slide.image) {
          // Ken Burns animasyonunu yeniden tetikle
          slideImg.classList.remove('ken-burns-anim');
          void slideImg.offsetWidth; // DOM reflow
          slideImg.src = slide.image;
          slideImg.alt = slide.title || cat.name;
          slideImg.classList.add('ken-burns-anim');
        }

        // 3. Link ve Metin Alanları
        const slideLink = document.getElementById('heroSlideLink');
        if (slideLink) {
          slideLink.href = slide.url || cat.catUrl || '#';
        }

        const badgeEl = document.getElementById('heroCategoryBadge');
        if (badgeEl) {
          badgeEl.textContent = cat.name || '';
        }

        const titleEl = document.getElementById('heroSlideTitle');
        if (titleEl) {
          titleEl.textContent = slide.title || cat.name || '';
        }

        const subEl = document.getElementById('heroSlideSubtitle');
        if (subEl) {
          subEl.textContent = slide.content_2 || slide.content_1 || '';
        }

        // 4. Tab Menüsü Aktif Durumu
        categoryKeys.forEach(function(key, idx) {
          const tabBtn = document.getElementById('tab-btn-' + key);
          if (tabBtn) {
            const isActive = (idx === currentCategoryIndex);
            tabBtn.classList.toggle('active', isActive);
            tabBtn.setAttribute('aria-selected', isActive ? 'true' : 'false');
          }
        });

        // Aktif tab'ı görünür alana kaydır
        const activeTabKey = categoryKeys[currentCategoryIndex];
        const activeTabBtn = document.getElementById('tab-btn-' + activeTabKey);
        const tabsContainer = document.getElementById('heroTabsList');
        if (activeTabBtn && tabsContainer && tabsContainer.scrollWidth > tabsContainer.clientWidth) {
          const targetScroll = activeTabBtn.offsetLeft - (tabsContainer.clientWidth / 2) + (activeTabBtn.clientWidth / 2);
          tabsContainer.scrollTo({ left: Math.max(0, targetScroll), behavior: 'smooth' });
        }

        renderPagination();
        restartProgress();
      }

      function goToCategory(index) {
        if (index === currentCategoryIndex && currentSubSlideIndex === 0) return;
        currentCategoryIndex = index;
        currentSubSlideIndex = 0;
        renderActiveSlide();
      }

      function goToSubSlide(subIndex) {
        if (subIndex === currentSubSlideIndex) return;
        currentSubSlideIndex = subIndex;
        renderActiveSlide();
      }

      function nextSlide() {
        const cat = getCurrentCategory();
        if (cat && cat.slides && currentSubSlideIndex < cat.slides.length - 1) {
          currentSubSlideIndex++;
        } else {
          currentCategoryIndex = (currentCategoryIndex + 1) % categoryKeys.length;
          currentSubSlideIndex = 0;
        }
        renderActiveSlide();
      }

      function prevSlide() {
        if (currentSubSlideIndex > 0) {
          currentSubSlideIndex--;
        } else {
          currentCategoryIndex = (currentCategoryIndex - 1 + categoryKeys.length) % categoryKeys.length;
          const prevCat = getCurrentCategory();
          currentSubSlideIndex = (prevCat && prevCat.slides) ? Math.max(0, prevCat.slides.length - 1) : 0;
        }
        renderActiveSlide();
      }

      function nextSubSlide() {
        const cat = getCurrentCategory();
        if (cat && cat.slides && cat.slides.length > 1) {
          currentSubSlideIndex = (currentSubSlideIndex + 1) % cat.slides.length;
          renderActiveSlide();
        } else {
          nextSlide();
        }
      }

      function prevSubSlide() {
        const cat = getCurrentCategory();
        if (cat && cat.slides && cat.slides.length > 1) {
          currentSubSlideIndex = (currentSubSlideIndex - 1 + cat.slides.length) % cat.slides.length;
          renderActiveSlide();
        } else {
          prevSlide();
        }
      }

      // Tab Butonları Dinleyicileri
      document.querySelectorAll('.hero-tab-btn').forEach(function(btn, idx) {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const gameId = btn.getAttribute('data-game');
          const targetIndex = categoryKeys.indexOf(String(gameId));
          if (targetIndex !== -1) {
            goToCategory(targetIndex);
          } else {
            goToCategory(idx);
          }
        });
      });

      // Kart Sağ & Sol Navigasyon Okları
      const btnSidePrev = document.getElementById('heroSidePrev');
      const btnSideNext = document.getElementById('heroSideNext');
      if (btnSidePrev) {
        btnSidePrev.addEventListener('click', function(e) {
          e.preventDefault();
          prevSlide();
        });
      }
      if (btnSideNext) {
        btnSideNext.addEventListener('click', function(e) {
          e.preventDefault();
          nextSlide();
        });
      }

      // 2. Görseldeki Slayt Kontrol Hapı Okları (heroPillPrev & heroPillNext)
      const btnPillPrev = document.getElementById('heroPillPrev');
      const btnPillNext = document.getElementById('heroPillNext');
      if (btnPillPrev) {
        btnPillPrev.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          prevSubSlide();
        });
      }
      if (btnPillNext) {
        btnPillNext.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          nextSubSlide();
        });
      }

      // Slider Alanı Mouse Hover Duraklatma
      const sliderArea = document.getElementById('heroSliderArea');
      if (sliderArea) {
        sliderArea.addEventListener('mouseenter', pauseAutoplay);
        sliderArea.addEventListener('mouseleave', resumeAutoplay);
      }

      // Mobil & Tablet Touch Swipe
      if (sliderArea) {
        let touchStartX = 0;
        let touchStartY = 0;
        let touchEndX = 0;
        let touchEndY = 0;
        let isSwiping = false;

        sliderArea.addEventListener('touchstart', function(e) {
          touchStartX = e.changedTouches[0].clientX;
          touchStartY = e.changedTouches[0].clientY;
          isSwiping = true;
          pauseAutoplay();
        }, { passive: true });

        sliderArea.addEventListener('touchmove', function(e) {
          if (!isSwiping) return;
          const currentX = e.changedTouches[0].clientX;
          const currentY = e.changedTouches[0].clientY;
          const diffX = Math.abs(currentX - touchStartX);
          const diffY = Math.abs(currentY - touchStartY);

          if (diffX > diffY && diffX > 10) {
            if (e.cancelable) e.preventDefault();
          }
        }, { passive: false });

        sliderArea.addEventListener('touchend', function(e) {
          if (!isSwiping) return;
          isSwiping = false;
          touchEndX = e.changedTouches[0].clientX;
          touchEndY = e.changedTouches[0].clientY;

          const deltaX = touchEndX - touchStartX;
          const deltaY = touchEndY - touchStartY;
          const threshold = 35;

          if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > threshold) {
            if (deltaX < 0) {
              nextSlide();
            } else {
              prevSlide();
            }
          }
          resumeAutoplay();
        }, { passive: true });

        sliderArea.addEventListener('touchcancel', function() {
          isSwiping = false;
          resumeAutoplay();
        });
      }

      // KIVILCIM / KÖZ PARÇACIK MOTORU (Ultra Hafif HTML5 Canvas)
      function setupEmberCanvas(canvasId, count) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        let particles = [];

        function resize() {
          if (!canvas.offsetWidth || !canvas.offsetHeight) return;
          canvas.width = canvas.offsetWidth;
          canvas.height = canvas.offsetHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        for (let i = 0; i < count; i++) {
          particles.push({
            x: Math.random() * (canvas.width || 800) * 0.6 + (canvas.width || 800) * 0.4,
            y: Math.random() * (canvas.height || 400),
            radius: Math.random() * 1.8 + 0.6,
            speedY: Math.random() * 0.6 + 0.3,
            speedX: (Math.random() - 0.5) * 0.4,
            opacity: Math.random() * 0.7 + 0.2,
            color: Math.random() > 0.4 ? '#ff5c16' : '#f59e0b'
          });
        }

        function animate() {
          if (!canvas.width || !canvas.height) {
            resize();
          }
          if (canvas.width && canvas.height) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(function(p) {
              ctx.beginPath();
              ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
              ctx.fillStyle = p.color;
              ctx.globalAlpha = p.opacity;
              ctx.fill();

              p.y -= p.speedY;
              p.x += p.speedX;

              if (p.y < 0) {
                p.y = canvas.height + 10;
                p.x = Math.random() * canvas.width * 0.6 + canvas.width * 0.4;
              }
            });
          }
          requestAnimationFrame(animate);
        }
        animate();
      }

      // Başlatma
      renderActiveSlide();
      setupEmberCanvas('emberCanvas', 24);

      // Header alt çizgisine tam yapışma hizalaması
      function alignHeroToHeader() {
        const nav = document.getElementById('navbar');
        const hero = document.getElementById('hero-section');
        if (!nav || !hero) return;
        const delta = hero.getBoundingClientRect().top - nav.getBoundingClientRect().bottom;
        if (delta > 0.5 && delta < 180) {
          hero.style.setProperty('margin-top', (-delta) + 'px', 'important');
        }
      }
      alignHeroToHeader();
      window.addEventListener('resize', alignHeroToHeader);
    })();

    (function() {
      // ===== Countdown =====
      let ts = 2 * 3600 + 33 * 60 + 48;

      function updateCD() {
        const h = Math.floor(ts / 3600),
          m = Math.floor((ts % 3600) / 60),
          s = ts % 60;
        document.getElementById('cd-hours').textContent = String(h).padStart(2, '0');
        document.getElementById('cd-minutes').textContent = String(m).padStart(2, '0');
        document.getElementById('cd-seconds').textContent = String(s).padStart(2, '0');
        if (ts > 0) ts--;
        else ts = 2 * 3600 + 33 * 60 + 48;
      }
      updateCD();
      setInterval(updateCD, 1000);

      // ===== Scroll animations =====
      const obs = new IntersectionObserver(e => e.forEach(en => {
        if (en.isIntersecting) {
          en.target.classList.add('visible');
          obs.unobserve(en.target);
        }
      }), {
        threshold: 0.1
      });
      document.querySelectorAll('.animate-on-scroll').forEach(el => obs.observe(el));

      // ===== Navbar scroll =====
      window.addEventListener('scroll', () => document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 80));

      // ===== Coupon copy =====
      document.querySelectorAll('.btn-coupon-use').forEach(b => {
        b.addEventListener('click', () => {
          const code = b.closest('.coupon-top').querySelector('.coupon-code').textContent;
          navigator.clipboard.writeText(code).then(() => {
            const o = b.textContent;
            b.textContent = 'Kopyalandı!';
            b.style.background = '#FFC800';
            setTimeout(() => {
              b.textContent = o;
              b.style.background = '';
            }, 2000);
          });
        });
      });

      // ===== Category filters (Dynamic Text & Bulletproof Hide) =====
      document.querySelectorAll('.cat-filter-tab').forEach(btn => {
        btn.addEventListener('click', () => {
          document.querySelectorAll('.cat-filter-tab').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');

          const categoryName = btn.querySelector('span').textContent;
          document.getElementById('dynamic-cat-title').textContent = categoryName;

          const filter = btn.dataset.filter;
          document.querySelectorAll('.category-card').forEach(card => {
            const cardCats = (card.dataset.category || '').split(' ');
            if (filter === 'all' || cardCats.includes(filter)) {
              card.classList.remove('d-none');
            } else {
              card.classList.add('d-none');
            }
          });
        });
      });
    })();
  </script>