/**
 * Flavor Floating Buttons - LIGHT v7.1
 */
(function () {
    'use strict';

    function init() {
        var socialWrap = document.getElementById('fl-fab-social');
        var socialBtn  = document.getElementById('fl-fab-social-btn');

        if (socialBtn && socialWrap) {

            socialBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                socialWrap.classList.toggle('is-open');
            });

            document.addEventListener('click', function (e) {
                if (!socialWrap.contains(e.target)) {
                    socialWrap.classList.remove('is-open');
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    socialWrap.classList.remove('is-open');
                }
            });
        }

        /* تشخیص باز بودن منوی موبایل */
        function checkMobileMenu() {
            var body = document.body;
            var html = document.documentElement;

            var isMenuOpen = (
                body.style.overflow === 'hidden' ||
                html.style.overflow === 'hidden' ||
                body.classList.contains('menu-open') ||
                body.classList.contains('mobile-menu-open') ||
                body.classList.contains('offcanvas-open') ||
                body.classList.contains('no-scroll') ||
                body.classList.contains('overflow-hidden') ||
                document.querySelector('.fl-drawer.on') ||
                document.querySelector('.mobile-menu.active') ||
                document.querySelector('.offcanvas.show')
            );

            if (isMenuOpen) {
                body.classList.add('fl-hide-fab');
                if (socialWrap) socialWrap.classList.remove('is-open');
            } else {
                body.classList.remove('fl-hide-fab');
            }
        }

        setInterval(checkMobileMenu, 500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();