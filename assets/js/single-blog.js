// --- Extracted Script Block 1 ---

/* ═══════════════════════════════
   CATEGORIES SLIDER AUTOPLAY (Mobile only)
   تک‌تک هر 3 ثانیه — توقف هنگام دست‌کاری کاربر
   مشابه single-portfolio.php
   ═══════════════════════════════ */
(function initSbCatsAutoplay() {
    var track = document.getElementById('flSbCatsTrack');
    if (!track) return;

    var INTERVAL_MS = 3000;
    var BREAKPOINT  = 768;
    var autoTimer   = null;
    var pausedByUser = false;
    var resumeTimer  = null;

    function isMobile() { return window.innerWidth <= BREAKPOINT; }

    function next() {
        if (!isMobile() || pausedByUser) return;
        var cards = track.querySelectorAll('.fl-sb-cat-card');
        if (!cards.length) return;

        var cardWidth = cards[0].getBoundingClientRect().width;
        var maxScroll = track.scrollWidth - track.clientWidth;
        var nextLeft  = track.scrollLeft + cardWidth;

        var isRtl = getComputedStyle(track).direction === 'rtl';
        if (isRtl) {
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

    function pauseTemporarily() {
        pausedByUser = true;
        if (resumeTimer) clearTimeout(resumeTimer);
        resumeTimer = setTimeout(function() { pausedByUser = false; }, 5000);
    }

    track.addEventListener('touchstart', pauseTemporarily, { passive: true });
    track.addEventListener('mousedown',  pauseTemporarily);
    track.addEventListener('wheel',      pauseTemporarily, { passive: true });

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) stop(); else start();
    });

    window.addEventListener('resize', function() {
        if (isMobile()) start(); else stop();
    });

    start();
})();

// --- Extracted Script Block 2 ---

function flSbChangeImg(el){
                        var src=el.getAttribute('data-src');
                        var main=document.getElementById('flSbMainImg');
                        if(main)main.src=src;
                        document.querySelectorAll('.fl-sb-gallery-thumb').forEach(function(t){t.classList.remove('active')});
                        el.classList.add('active');
                    }

/* ═══════════════════════════════
   TABLE OF CONTENTS (فهرست مطالب)
   مشابه پارس‌پک — ساخت خودکار از h1/h2/h3
   دسکتاپ: چسبان | موبایل: آکاردئونی
   ═══════════════════════════════ */
(function initSbToc() {
    var tocList = document.getElementById('flSbTocList');
    if (!tocList) return;

    // جستجوی عنوان‌ها: اول در .fl-sb-content، بعد در .fl-sb-main-col، بعد در کل صفحه
    var content = document.querySelector('.fl-sb-content');
    if (!content) content = document.querySelector('.gl-page-content');
    if (!content) content = document.querySelector('.fl-sb-main-col');
    if (!content) content = document.querySelector('.fl-sp-blog');
    if (!content) content = document.querySelector('.site-main');
    if (!content) return;

    var headings = content.querySelectorAll('h1, h2, h3');
    if (headings.length < 2) {
        var tocAside = document.getElementById('flSbToc');
        if (tocAside) tocAside.style.display = 'none';
        var layout = document.querySelector('.fl-sb-content-layout');
        if (layout) layout.classList.add('fl-sb-no-toc');
        return;
    }

    var counter = 0;
    headings.forEach(function(h) {
        counter++;
        var id = 'toc-heading-' + counter;
        h.id = id;

        var li = document.createElement('li');
        li.className = 'fl-sb-toc-item' + (h.tagName === 'H3' ? ' fl-sb-toc-sub' : '') + (h.tagName === 'H1' ? ' fl-sb-toc-h1' : '');

        var a = document.createElement('a');
        a.href = '#' + id;
        a.textContent = h.textContent;
        a.className = 'fl-sb-toc-link';
        a.addEventListener('click', function(e) {
            e.preventDefault();
            var target = document.getElementById(id);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            // موبایل: بستن آکاردئون بعد از کلیک
            if (window.innerWidth <= 768) {
                var inner = document.querySelector('.fl-sb-toc-inner');
                if (inner) inner.classList.remove('fl-sb-toc-open');
            }
        });

        li.appendChild(a);
        tocList.appendChild(li);
    });

    // موبایل: آکاردئون — کلیک روی هدر = باز/بسته
    var tocHeader = document.querySelector('.fl-sb-toc-header');
    if (tocHeader) {
        tocHeader.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                var inner = document.querySelector('.fl-sb-toc-inner');
                if (inner) inner.classList.toggle('fl-sb-toc-open');
            }
        });
    }

    // اسکرول اسپای — هایلایت آیتم فعال
    var tocLinks = tocList.querySelectorAll('.fl-sb-toc-link');
    function updateActiveToc() {
        var scrollY = window.scrollY || window.pageYOffset;
        var activeId = '';
        headings.forEach(function(h) {
            var rect = h.getBoundingClientRect();
            if (rect.top <= 120) {
                activeId = h.id;
            }
        });
        tocLinks.forEach(function(link) {
            link.classList.remove('fl-sb-toc-active');
            if (link.getAttribute('href') === '#' + activeId) {
                link.classList.add('fl-sb-toc-active');
            }
        });
    }

    var tocRafId = null;
    window.addEventListener('scroll', function() {
        if (tocRafId) return;
        tocRafId = requestAnimationFrame(function() {
            updateActiveToc();
            tocRafId = null;
        });
    }, { passive: true });

    updateActiveToc();
})();