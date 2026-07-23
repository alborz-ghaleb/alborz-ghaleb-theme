/**
 * Glass User Panel — جابجایی تب‌های ورود/ثبت‌نام/فراموشی رمز
 */
(function () {
    'use strict';

    function init() {
        var wrap = document.querySelector('.glass-auth-card');
        if (!wrap) return;

        var tabs   = wrap.querySelectorAll('.glass-auth-tab');
        var links  = wrap.querySelectorAll('.glass-auth-link');
        var panels = wrap.querySelectorAll('.glass-auth-form');

        function show(name) {
            panels.forEach(function (p) {
                p.style.display = (p.getAttribute('data-panel') === name) ? 'block' : 'none';
            });
            tabs.forEach(function (t) {
                t.classList.toggle('is-active', t.getAttribute('data-tab') === name);
            });
        }

        tabs.forEach(function (t) {
            t.addEventListener('click', function () {
                show(t.getAttribute('data-tab'));
            });
        });

        links.forEach(function (l) {
            l.addEventListener('click', function () {
                show(l.getAttribute('data-tab'));
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
