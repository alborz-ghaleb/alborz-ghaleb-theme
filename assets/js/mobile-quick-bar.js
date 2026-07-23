/** Mobile Quick Action Bar — virtual-keyboard guard. */
(function () {
    'use strict';

    function init() {
        var bar = document.getElementById('glassMobileQuickbar');
        if (!bar) return;

        // Prevent the fixed bar from covering focused fields above a mobile keyboard.
        if (window.visualViewport) {
            var baselineHeight = Math.max(window.innerHeight, window.visualViewport.height);
            var syncKeyboard = function () {
                var current = window.visualViewport.height;
                if (current > baselineHeight) baselineHeight = current;
                bar.classList.toggle('gmqb-keyboard-open', current < baselineHeight * 0.72);
            };
            window.visualViewport.addEventListener('resize', syncKeyboard, { passive: true });
            window.addEventListener('orientationchange', function () {
                window.setTimeout(function () {
                    baselineHeight = Math.max(window.innerHeight, window.visualViewport.height);
                    syncKeyboard();
                }, 250);
            }, { passive: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
