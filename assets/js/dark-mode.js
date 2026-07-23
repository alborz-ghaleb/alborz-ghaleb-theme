/**
 * Dark Mode — Single Source of Truth
 * - اجرای سریع در head برای جلوگیری از FOUC
 * - احترام به localStorage، Customizer default و prefers-color-scheme
 * - گارد window.__glassDarkModeInit برای جلوگیری از اجرای چندنمونه‌ای
 */
(function () {
    'use strict';

    if (window.__glassDarkModeInit) return;
    window.__glassDarkModeInit = true;

    var STORAGE_KEY = 'glass_dark_mode';
    var DARK_CLASS  = 'dark-mode';
    var settings    = window.glassSettings || {};

    function toBool(value, fallback) {
        if (value === true || value === 'true' || value === '1' || value === 1) return true;
        if (value === false || value === 'false' || value === '0' || value === 0) return false;
        return fallback;
    }

    function getSaved() {
        try { return localStorage.getItem(STORAGE_KEY); } catch (e) { return null; }
    }

    function setSaved(mode) {
        try { localStorage.setItem(STORAGE_KEY, mode); } catch (e) {}
    }

    function systemDark() {
        return !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
    }

    function shouldDark() {
        var saved = getSaved();
        if (saved === 'dark') return true;
        if (saved === 'light') return false;

        if (toBool(settings.darkModeDefault, false)) return true;
        if (toBool(settings.respectSystemPreference, true) && systemDark()) return true;

        return false;
    }

    function apply(isDark) {
        document.documentElement.classList.toggle(DARK_CLASS, !!isDark);
        if (document.body) document.body.classList.toggle(DARK_CLASS, !!isDark);
    }

    function updateToggle(toggle, isDark) {
        if (!toggle) return;
        toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        toggle.setAttribute('aria-label', isDark ? 'خروج از حالت تاریک' : 'فعال کردن حالت تاریک');
    }

    function bind() {
        var toggle = document.getElementById('flDarkToggle');
        var isDark = document.documentElement.classList.contains(DARK_CLASS);
        updateToggle(toggle, isDark);

        if (toggle) {
            toggle.addEventListener('click', function () {
                var next = !document.documentElement.classList.contains(DARK_CLASS);
                apply(next);
                setSaved(next ? 'dark' : 'light');
                updateToggle(toggle, next);
                try {
                    window.dispatchEvent(new CustomEvent('glassDarkModeChange', { detail: { isDark: next } }));
                } catch (e) {}
            });
        }

        if (toBool(settings.respectSystemPreference, true) && window.matchMedia) {
            var mq = window.matchMedia('(prefers-color-scheme: dark)');
            var listener = function (e) {
                if (getSaved() !== null) return;
                var next = toBool(settings.darkModeDefault, false) ? true : !!e.matches;
                apply(next);
                updateToggle(toggle, next);
            };
            if (typeof mq.addEventListener === 'function') mq.addEventListener('change', listener);
            else if (typeof mq.addListener === 'function') mq.addListener(listener);
        }
    }

    // اعمال فوری در head برای جلوگیری از فلش روشن/تاریک
    apply(shouldDark());

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
