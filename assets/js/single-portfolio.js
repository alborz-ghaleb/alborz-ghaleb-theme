// --- Extracted Script Block 1 ---

(function(){
    'use strict';

    /* ═══════════════════════════════
       CATEGORIES SLIDER AUTOPLAY (Mobile only)
       تک‌تک هر 3 ثانیه — توقف هنگام دست‌کاری کاربر
       ═══════════════════════════════ */
    (function initCatsAutoplay() {
        var track = document.getElementById('flSpCatsTrack');
        if (!track) return;

        var INTERVAL_MS = 3000;
        var BREAKPOINT  = 768;
        var autoTimer   = null;
        var pausedByUser = false;
        var resumeTimer  = null;

        function isMobile() { return window.innerWidth <= BREAKPOINT; }

        function next() {
            if (!isMobile() || pausedByUser) return;
            var cards = track.querySelectorAll('.fl-sp-cat-card');
            if (!cards.length) return;

            var cardWidth = cards[0].getBoundingClientRect().width;
            var maxScroll = track.scrollWidth - track.clientWidth;
            var nextLeft  = track.scrollLeft + cardWidth;

            // در RTL با scrollLeft منفی هم کار می‌کنه؛ بر اساس direction:
            var isRtl = getComputedStyle(track).direction === 'rtl';
            if (isRtl) {
                // در RTL: scrollLeft از 0 شروع و منفی می‌ره
                if (track.scrollLeft - cardWidth <= -maxScroll) {
                    track.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    track.scrollBy({ left: -cardWidth, behavior: 'smooth' });
                }
            } else {
                if (nextLeft >= maxScroll - 2) {
                    track.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    track.scrollBy({ left: cardWidth, behavior: 'smooth' });
                }
            }
        }

        function start() {
            stop();
            if (!isMobile()) return;
            autoTimer = setInterval(next, INTERVAL_MS);
        }

        function stop() {
            if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
        }

        // وقتی کاربر دست می‌زنه (swipe/touch/scroll) موقتاً متوقف بشه
        function pauseTemporarily() {
            pausedByUser = true;
            if (resumeTimer) clearTimeout(resumeTimer);
            resumeTimer = setTimeout(function() { pausedByUser = false; }, 5000);
        }

        track.addEventListener('touchstart', pauseTemporarily, { passive: true });
        track.addEventListener('mousedown',  pauseTemporarily);
        track.addEventListener('wheel',      pauseTemporarily, { passive: true });

        // وقتی tab از دید خارج می‌شه autoplay رو متوقف کن (صرفه‌جویی باتری)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) stop(); else start();
        });

        window.addEventListener('resize', function() {
            if (isMobile()) start(); else stop();
        });

        start();
    })();

    /* ═══════════════════════════════
       GALLERY
       ═══════════════════════════════ */
    var galleryImages = window.glassSinglePortfolioImages || [];
    var currentIndex = 0;

    window.flSpSelectImage = function(el, index) {
        currentIndex = index;
        var mainImg = document.getElementById('flSpMainImage');
        var counter = document.getElementById('flSpCounterText');
        if (mainImg) mainImg.src = el.getAttribute('data-full');
        if (counter) counter.textContent = (index + 1) + ' / ' + galleryImages.length;

        document.querySelectorAll('.fl-sp-gallery-thumb').forEach(function(t) { t.classList.remove('active'); });
        el.classList.add('active');
    };

    /* ═══════════════════════════════
       LIGHTBOX
       ═══════════════════════════════ */
    window.flSpOpenLightbox = function(index) {
        if (!galleryImages.length) return;
        currentIndex = index;
        var lb = document.getElementById('flSpLightbox');
        var img = document.getElementById('flSpLbImage');
        var counter = document.getElementById('flSpLbCounter');
        if (!lb) return;
        img.src = galleryImages[currentIndex];
        counter.textContent = (currentIndex + 1) + ' / ' + galleryImages.length;
        lb.classList.add('fl-on');
        document.body.style.overflow = 'hidden';
    };

    window.flSpCloseLightbox = function() {
        var lb = document.getElementById('flSpLightbox');
        if (lb) lb.classList.remove('fl-on');
        document.body.style.overflow = '';
    };

    window.flSpLbNav = function(dir) {
        currentIndex += dir;
        if (currentIndex < 0) currentIndex = galleryImages.length - 1;
        if (currentIndex >= galleryImages.length) currentIndex = 0;
        var img = document.getElementById('flSpLbImage');
        var counter = document.getElementById('flSpLbCounter');
        if (img) img.src = galleryImages[currentIndex];
        if (counter) counter.textContent = (currentIndex + 1) + ' / ' + galleryImages.length;
    };

    // ESC + Keyboard
    document.addEventListener('keydown', function(e) {
        var lb = document.getElementById('flSpLightbox');
        if (!lb || !lb.classList.contains('fl-on')) return;
        if (e.key === 'Escape') flSpCloseLightbox();
        if (e.key === 'ArrowRight') flSpLbNav(-1);
        if (e.key === 'ArrowLeft') flSpLbNav(1);
    });

    /* Contact form removed - VIP box used instead */

    /* ═══════════════════════════════
       MOBILE DRAWER
       ═══════════════════════════════ */
    function initMobileDrawer() {
        var btn = document.querySelector('.fl-pf-mob-btn');
        var drawer = document.querySelector('.fl-pf-drawer');
        var overlay = document.querySelector('.fl-pf-overlay');
        var closeX = document.querySelector('.fl-pf-drawer-x');
        var searchInput = document.querySelector('.fl-pf-city-search-input');
        var drawerList = document.querySelector('.fl-drawer-list');
        var emptySearch = document.querySelector('.fl-pf-drawer-empty-search');
        var headerGroup = document.getElementById('flHeaderGroup');
        var headerMain = document.getElementById('flHeader');
        var infoBar = headerGroup ? headerGroup.querySelector('.fl-info') : null;
        if (!btn) return;

        function updateBtnPos() {
            if (window.innerWidth > 768) { document.documentElement.style.removeProperty('--fl-city-btn-top'); return; }
            var gap = 10, top = 88;
            if (headerGroup) {
                var isStuck = headerGroup.classList.contains('stuck');
                var isHidden = headerMain && headerMain.classList.contains('h-hidden');
                if (isStuck && isHidden) { top = infoBar ? Math.round(infoBar.getBoundingClientRect().bottom + gap) : 52 + gap; }
                else { top = Math.round(headerGroup.getBoundingClientRect().bottom + gap); }
            }
            if (top < 50) top = 50; if (top > 160) top = 160;
            document.documentElement.style.setProperty('--fl-city-btn-top', top + 'px');
        }

        var raf = null;
        function reqUpdate() { if (raf) cancelAnimationFrame(raf); raf = requestAnimationFrame(updateBtnPos); }

        function resetCitySearch() {
            if (!drawerList) return;
            drawerList.querySelectorAll('li').forEach(function(li) { li.style.display = ''; });
            if (searchInput) searchInput.value = '';
            if (emptySearch) emptySearch.classList.remove('show');
        }

        function filterCityMenu(keyword) {
            if (!drawerList) return;
            var items = drawerList.children; var vc = 0; var q = (keyword || '').trim().toLowerCase();
            for (var i = 0; i < items.length; i++) {
                var item = items[i]; if (!item || item.tagName.toLowerCase() !== 'li') continue;
                var text = (item.textContent || '').trim().toLowerCase();
                if (!q) { item.style.display = ''; vc++; }
                else if (text.indexOf(q) !== -1) { item.style.display = ''; vc++; }
                else { item.style.display = 'none'; }
            }
            if (emptySearch) { emptySearch.classList.toggle('show', q && vc === 0); }
        }

        function openDrawer() {
            if (!drawer || !overlay) return;
            overlay.classList.add('fl-on'); drawer.classList.add('fl-on');
            document.body.style.overflow = 'hidden'; document.documentElement.style.overflow = 'hidden';
            setTimeout(function() { if (searchInput) searchInput.focus(); }, 220);
        }

        function closeDrawer() {
            if (drawer) drawer.classList.remove('fl-on');
            if (overlay) overlay.classList.remove('fl-on');
            document.body.style.overflow = ''; document.documentElement.style.overflow = '';
            resetCitySearch();
        }

        updateBtnPos(); setTimeout(updateBtnPos, 150); setTimeout(updateBtnPos, 500);
        window.addEventListener('load', updateBtnPos);
        window.addEventListener('resize', function() { reqUpdate(); if (window.innerWidth > 768) closeDrawer(); });
        window.addEventListener('scroll', reqUpdate, { passive: true });

        if (headerGroup) {
            var mo = new MutationObserver(reqUpdate);
            mo.observe(headerGroup, { attributes: true, attributeFilter: ['class', 'style'] });
            if (headerMain) mo.observe(headerMain, { attributes: true, attributeFilter: ['class', 'style'] });
            if (infoBar) mo.observe(infoBar, { attributes: true, attributeFilter: ['class', 'style'] });
        }

        btn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); openDrawer(); });

        // تابع گلوبال برای دکمه شهر روی تصویر
        window.flSpOpenCityDrawer = function() { openDrawer(); };

        if (closeX) closeX.addEventListener('click', closeDrawer);
        if (overlay) overlay.addEventListener('click', closeDrawer);

        var sy = 0, cy = 0;
        if (drawer) {
            drawer.addEventListener('touchstart', function(e) { sy = e.touches[0].clientY; }, { passive: true });
            drawer.addEventListener('touchmove', function(e) { cy = e.touches[0].clientY; }, { passive: true });
            drawer.addEventListener('touchend', function() { if (cy - sy > 80) closeDrawer(); sy = 0; cy = 0; });
        }

        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDrawer(); });
        if (searchInput) searchInput.addEventListener('input', function() { filterCityMenu(this.value); });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileDrawer);
    } else {
        initMobileDrawer();
    }
})();