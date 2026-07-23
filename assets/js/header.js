/**
 * Flavor Header JS v3.1
 * Small Lang Dropdown | Sticky Info | Hidden Header
 */
(function () {
    'use strict';

    var group      = document.getElementById('flHeaderGroup');
    var header     = document.getElementById('flHeader');
    var drawer     = document.getElementById('flDrawer');
    var overlay    = document.querySelector('.fl-overlay');
    var burger     = document.querySelector('.fl-burger');
    var closeBtn   = document.querySelector('.fl-drawer-close');
    var searchBtn  = document.querySelector('.fl-search-toggle-btn');
    var searchPop  = document.querySelector('.fl-search-pop');

    // Language dropdown (small)
    var langWrap   = document.getElementById('flLangWrap');
    var langToggle = document.getElementById('flLangToggle');
    var langDrop   = document.getElementById('flLangDrop');

    if (!group) return;


    /* ═══════════════════════════════
       1. SCROLL
       ═══════════════════════════════ */
    if (header) {
        var lastY   = window.pageYOffset || 0;
        var ticking = false;
        var delta   = 5;

        function onScroll() {
            if (drawer && drawer.classList.contains('on')) {
                ticking = false;
                return;
            }

            var y    = window.pageYOffset || 0;
            var diff = y - lastY;

            if (y <= 10) {
                header.classList.remove('h-hidden');
                group.classList.remove('stuck');
            } else if (diff > delta) {
                header.classList.add('h-hidden');
                group.classList.add('stuck');
                closeLang();
                closeAllDrops();
                if (searchPop) searchPop.classList.remove('open');
            } else if (diff < -delta) {
                header.classList.remove('h-hidden');
                group.classList.remove('stuck');
            }

            lastY   = y;
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(onScroll);
                ticking = true;
            }
        }, { passive: true });

        onScroll();
    }


    /* ═══════════════════════════════
       2. LANGUAGE DROPDOWN (Small)
       ═══════════════════════════════ */
    var langOpen = false;

    function openLang() {
        if (!langDrop) return;
        langDrop.classList.add('is-open');
        langDrop.setAttribute('aria-hidden', 'false');
        if (langToggle) langToggle.setAttribute('aria-expanded', 'true');
        langOpen = true;
    }

    function closeLang() {
        if (!langDrop) return;
        langDrop.classList.remove('is-open');
        langDrop.setAttribute('aria-hidden', 'true');
        if (langToggle) langToggle.setAttribute('aria-expanded', 'false');
        langOpen = false;
    }

    if (langToggle) {
        langToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            langOpen ? closeLang() : openLang();
        });
    }

    document.addEventListener('click', function (e) {
        if (langOpen && langWrap && !langWrap.contains(e.target)) {
            closeLang();
        }
    });


    /* ═══════════════════════════════
       3. DRAWER
       ═══════════════════════════════ */
    function openDrawer() {
        if (!drawer || !overlay || !burger) return;
        drawer.classList.add('on');
        drawer.setAttribute('aria-hidden', 'false');
        drawer.removeAttribute('inert');
        overlay.classList.add('on');
        burger.classList.add('on');
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        if (drawer) {
            drawer.classList.remove('on');
            drawer.setAttribute('aria-hidden', 'true');
            drawer.setAttribute('inert', '');
        }
        if (overlay) overlay.classList.remove('on');
        if (burger) burger.classList.remove('on');
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }

    if (burger) {
        burger.addEventListener('click', function () {
            drawer && drawer.classList.contains('on') ? closeDrawer() : openDrawer();
        });
    }

    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay) overlay.addEventListener('click', closeDrawer);


    /* ═══════════════════════════════
       4. DRAWER ACCORDION
       ═══════════════════════════════ */
    var allAccordions = document.querySelectorAll('.fl-drawer-acc');

    function toggleAccordion(acc) {
        var wasOpen = acc.classList.contains('on');
        allAccordions.forEach(function (a) { a.classList.remove('on'); });
        if (!wasOpen) acc.classList.add('on');
    }

    allAccordions.forEach(function (accordion) {
        var trigger = accordion.querySelector('.fl-drawer-acc-trigger');
        var link    = accordion.querySelector('.fl-drawer-acc-link');
        var toggle  = accordion.querySelector('.fl-drawer-acc-toggle');

        if (!trigger) return;

        if (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleAccordion(accordion);
            });
        }

        trigger.addEventListener('click', function (e) {
            if (link && (e.target === link || link.contains(e.target))) return;
            if (toggle && (e.target === toggle || toggle.contains(e.target))) return;
            e.preventDefault();
            e.stopPropagation();
            toggleAccordion(accordion);
        });
    });


    /* ═══════════════════════════════
       5. DRAWER LANGUAGE
       ═══════════════════════════════ */
    var drawerLang       = document.querySelector('.fl-drawer-lang');
    var drawerLangToggle = document.querySelector('.fl-drawer-lang-toggle');

    if (drawerLangToggle && drawerLang) {
        drawerLangToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = drawerLang.classList.contains('is-open');
            if (isOpen) {
                drawerLang.classList.remove('is-open');
                drawerLangToggle.setAttribute('aria-expanded', 'false');
            } else {
                drawerLang.classList.add('is-open');
                drawerLangToggle.setAttribute('aria-expanded', 'true');
            }
        });
    }


    /* ═══════════════════════════════
       6. DESKTOP MEGA MENU
       ═══════════════════════════════ */
    var drops      = document.querySelectorAll('.fl-drop[data-drop]');
    var closeTimer = null;

    function closeAllDrops() {
        drops.forEach(function (d) { d.classList.remove('mega-open'); });
    }

    drops.forEach(function (drop) {
        var btn = drop.querySelector('[data-drop-toggle]');

        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = drop.classList.contains('mega-open');
                closeAllDrops();
                if (!isOpen) drop.classList.add('mega-open');
            });
        }

        drop.addEventListener('mouseenter', function () {
            if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
        });

        drop.addEventListener('mouseleave', function () {
            closeTimer = setTimeout(function () {
                drop.classList.remove('mega-open');
            }, 200);
        });
    });

    document.addEventListener('click', function (e) {
        var inside = false;
        drops.forEach(function (d) { if (d.contains(e.target)) inside = true; });
        if (!inside) closeAllDrops();
    });


    /* ═══════════════════════════════
       7. SEARCH POPUP
       ═══════════════════════════════ */
    if (searchBtn && searchPop) {
        searchBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            searchPop.classList.toggle('open');

            if (searchPop.classList.contains('open')) {
                var inp = searchPop.querySelector('input');
                if (inp) setTimeout(function () { inp.focus(); }, 120);
            }
        });

        document.addEventListener('click', function (e) {
            if (!searchPop.contains(e.target) && !searchBtn.contains(e.target)) {
                searchPop.classList.remove('open');
            }
        });
    }


    /* ═══════════════════════════════
       8. ESC
       ═══════════════════════════════ */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllDrops();
            closeLang();
            if (drawer && drawer.classList.contains('on')) closeDrawer();
            if (searchPop) searchPop.classList.remove('open');
        }
    });


    /* ═══════════════════════════════
       9. RESIZE
       ═══════════════════════════════ */
    var rt;
    window.addEventListener('resize', function () {
        clearTimeout(rt);
        rt = setTimeout(function () {
            if (window.innerWidth > 1100 && drawer && drawer.classList.contains('on')) {
                closeDrawer();
            }
        }, 200);
    });

})();
