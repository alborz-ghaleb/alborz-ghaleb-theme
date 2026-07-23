/**
 * Lightweight Scroll Animations — DISABLED
 * انیمیشن‌های لود بخش‌ها غیرفعال شد.
 * تمام المان‌ها بلافاصله نمایش داده می‌شوند.
 *
 * @package Alborz_Ghaleb
 */
(function () {
    'use strict';
    // تمام المان‌های gl-reveal و gl-stagger بلافاصله نمایش داده شوند
    function showAll() {
        document.querySelectorAll('.gl-reveal, .gl-stagger').forEach(function (el) {
            el.style.opacity = '1';
            el.style.transform = 'none';
            el.classList.add('is-visible');
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showAll);
    } else {
        showAll();
    }
})();
