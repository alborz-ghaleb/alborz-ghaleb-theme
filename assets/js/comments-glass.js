/**
 * بخش دیدگاه — افکت‌های تعاملی
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        /* ─── افکت فوکوس فیلدها ─── */
        var inputs = document.querySelectorAll(
            '.comment-form input[type="text"],' +
            '.comment-form input[type="email"],' +
            '.comment-form input[type="url"],' +
            '.comment-form textarea'
        );

        inputs.forEach(function (el) {
            el.addEventListener('focus', function () {
                if (this.parentElement) {
                    this.parentElement.style.transform = 'translateY(-2px)';
                    this.parentElement.style.transition = 'transform .3s ease';
                }
            });
            el.addEventListener('blur', function () {
                if (this.parentElement) {
                    this.parentElement.style.transform = 'translateY(0)';
                }
            });
        });

        /* ─── اسکرول نرم به فرم ریپلای ─── */
        document.querySelectorAll('.comment-reply-link').forEach(function (link) {
            link.addEventListener('click', function () {
                setTimeout(function () {
                    var respond = document.getElementById('respond');
                    if (respond) {
                        respond.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 150);
            });
        });

    });
})();