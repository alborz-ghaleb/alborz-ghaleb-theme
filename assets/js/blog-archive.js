/**
 * Blog Archive JavaScript
 * Only view toggle + smooth scroll on filter
 * No entry animations - No counter animations
 *
 * @package developer-jeison
 * @version 2.1.0
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initViewToggle();
        initSmoothScrollOnFilter();
    });

    /* ═══════════════════════════════════════════════════
       1. GRID / LIST VIEW TOGGLE
       ═══════════════════════════════════════════════════ */

    function initViewToggle() {
        var toggleBtns = document.querySelectorAll('.blog-view-btn');
        var grid = document.getElementById('blog-posts-grid');

        if (!toggleBtns.length || !grid) return;

        // Restore saved preference
        var savedView = null;
        try {
            savedView = localStorage.getItem('jeison_blog_view');
        } catch (e) {}

        if (savedView === 'list') {
            grid.classList.add('list-view');
            toggleBtns.forEach(function (btn) {
                btn.classList.toggle('active', btn.getAttribute('data-view') === 'list');
            });
        }

        toggleBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var view = this.getAttribute('data-view');

                toggleBtns.forEach(function (b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');

                grid.style.opacity = '0';

                setTimeout(function () {
                    if (view === 'list') {
                        grid.classList.add('list-view');
                    } else {
                        grid.classList.remove('list-view');
                    }
                    grid.style.opacity = '1';
                }, 200);

                try {
                    localStorage.setItem('jeison_blog_view', view);
                } catch (e) {}
            });
        });
    }

    /* ═══════════════════════════════════════════════════
       2. SMOOTH SCROLL WHEN FILTER IS APPLIED
       ═══════════════════════════════════════════════════ */

    function initSmoothScrollOnFilter() {
        var urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('blog_tag')) {
            var postsSection = document.querySelector('.blog-posts-section');
            if (postsSection) {
                setTimeout(function () {
                    var offset = postsSection.getBoundingClientRect().top + window.pageYOffset - 100;
                    window.scrollTo({
                        top: offset,
                        behavior: 'smooth',
                    });
                }, 400);
            }
        }
    }
})();