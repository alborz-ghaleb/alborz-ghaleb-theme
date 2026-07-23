/**
 * Flavor Footer JS - LIGHT v7.1
 */
(function () {
    'use strict';

    function init() {
        var backTop = document.getElementById('flBackTop');
        if (!backTop) return;

        backTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();