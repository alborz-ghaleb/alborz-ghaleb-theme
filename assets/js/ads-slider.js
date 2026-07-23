/**
 * Glass Ads Slider PRO
 *
 * امکانات:
 *  - افکت slide یا fade
 *  - فلش چپ/راست + نقطه‌ها + نوار پیشرفت (progress)
 *  - autoplay با pause-on-hover و pause روی تب پنهان
 *  - loop بی‌نهایت (در حالت slide با clone) یا متوقف‌شونده
 *  - swipe/drag لمسی و ماوس
 *  - کنترل با کیبورد (فلش چپ/راست، Home/End)
 *  - تعداد نمایش responsive (desktop/tablet/mobile)
 *  - RTL سازگار + prefers-reduced-motion
 *
 * بدون وابستگی (Vanilla JS).
 */
(function () {
	'use strict';

	var TABLET_BP = 1024;
	var MOBILE_BP = 768;
	var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function parseConfig(root) {
		var raw = root.getAttribute('data-config');
		var def = {
			autoplay: 5000, loop: 1, effect: 'slide', arrows: 1, dots: 1,
			progress: 1, pauseHover: 1, perDesktop: 2, perTablet: 1, perMobile: 1, gap: 24
		};
		if (!raw) return def;
		try { return Object.assign(def, JSON.parse(raw)); } catch (e) { return def; }
	}

	function initSlider(root) {
		if (root.__glassInit) return;
		root.__glassInit = true;

		var cfg      = parseConfig(root);
		var viewport = root.querySelector('.glass-ads-viewport');
		var track    = root.querySelector('.glass-ads-track');
		var dotsWrap = root.querySelector('.glass-ads-dots');
		var prevBtn  = root.querySelector('.glass-ads-prev');
		var nextBtn  = root.querySelector('.glass-ads-next');
		var progBar  = root.querySelector('.glass-ads-progress-bar');
		var isFade   = cfg.effect === 'fade';

		var realSlides = Array.prototype.slice.call(track.querySelectorAll('.glass-ads-slide'));
		var total = realSlides.length;
		if (!track || total === 0) return;

		var index = 0;          // index صفحه (در fade = index اسلاید)
		var timer = null;
		var rafProg = null;
		var progStart = 0;

		function perView() {
			if (isFade) return 1;
			var w = window.innerWidth;
			if (w <= MOBILE_BP) return Math.max(1, cfg.perMobile);
			if (w <= TABLET_BP) return Math.max(1, cfg.perTablet);
			return Math.max(1, cfg.perDesktop);
		}

		function pageCount() {
			if (isFade) return total;
			return Math.max(1, Math.ceil(total / perView()));
		}

		/* ---------- FADE mode ---------- */
		function applyFade() {
			realSlides.forEach(function (s, i) {
				s.classList.toggle('is-active', i === index);
			});
		}

		/* ---------- SLIDE mode ---------- */
		function slideWidth() {
			var pv = perView();
			var gap = cfg.gap || 0;
			return (viewport.offsetWidth - gap * (pv - 1)) / pv;
		}

		function layoutSlides() {
			if (isFade) return;
			var w = slideWidth();
			realSlides.forEach(function (s) {
				s.style.flex = '0 0 ' + w + 'px';
				s.style.width = w + 'px';
			});
		}

		function applySlide(animate) {
			if (isFade) { applyFade(); return; }
			var pv = perView();
			var w = slideWidth();
			var gap = cfg.gap || 0;
			var first = index * pv;
			// محدودسازی تا آخرین صفحه از سرریز جلوگیری کند
			first = Math.min(first, Math.max(0, total - pv));
			var offset = first * (w + gap);
			var rtl = getComputedStyle(root).direction === 'rtl';
			track.style.transition = (animate && !REDUCED) ? '' : 'none';
			track.style.transform = 'translateX(' + (rtl ? offset : -offset) + 'px)';
			if (!animate) { void track.offsetWidth; track.style.transition = ''; }
		}

		function render(animate) {
			if (isFade) applyFade(); else applySlide(animate !== false);
			updateDots();
			updateArrows();
			restartProgress();
		}

		/* ---------- Dots ---------- */
		function buildDots() {
			if (!dotsWrap) return;
			dotsWrap.innerHTML = '';
			var pages = pageCount();
			if (pages <= 1) { dotsWrap.style.display = 'none'; return; }
			dotsWrap.style.display = '';
			for (var i = 0; i < pages; i++) {
				var dot = document.createElement('button');
				dot.type = 'button';
				dot.className = 'glass-ads-dot' + (i === index ? ' is-active' : '');
				dot.setAttribute('role', 'tab');
				dot.setAttribute('aria-label', 'اسلاید ' + (i + 1));
				(function (p) {
					dot.addEventListener('click', function () { goTo(p); userInteract(); });
				})(i);
				dotsWrap.appendChild(dot);
			}
		}
		function updateDots() {
			if (!dotsWrap) return;
			var dots = dotsWrap.querySelectorAll('.glass-ads-dot');
			for (var i = 0; i < dots.length; i++) {
				var on = i === index;
				dots[i].classList.toggle('is-active', on);
				dots[i].setAttribute('aria-selected', on ? 'true' : 'false');
			}
		}

		/* ---------- Arrows ---------- */
		function updateArrows() {
			if (cfg.loop) return;
			if (prevBtn) prevBtn.disabled = (index <= 0);
			if (nextBtn) nextBtn.disabled = (index >= pageCount() - 1);
		}

		/* ---------- Navigation ---------- */
		function goTo(p) {
			var pages = pageCount();
			if (cfg.loop) {
				index = ((p % pages) + pages) % pages;
			} else {
				index = Math.max(0, Math.min(p, pages - 1));
			}
			render(true);
		}
		function next() { goTo(index + 1); }
		function prev() { goTo(index - 1); }

		/* ---------- Autoplay ---------- */
		function canAutoplay() { return cfg.autoplay > 0 && pageCount() > 1; }
		function start() {
			if (!canAutoplay()) return;
			stop();
			timer = setInterval(next, cfg.autoplay);
			restartProgress();
		}
		function stop() {
			if (timer) { clearInterval(timer); timer = null; }
			if (rafProg) { cancelAnimationFrame(rafProg); rafProg = null; }
			if (progBar) progBar.style.width = '0%';
		}
		function userInteract() { if (canAutoplay()) start(); }

		/* ---------- Progress bar ---------- */
		function restartProgress() {
			if (!progBar || !canAutoplay() || !timer) { if (progBar) progBar.style.width = '0%'; return; }
			if (rafProg) cancelAnimationFrame(rafProg);
			progStart = performance.now();
			var dur = cfg.autoplay;
			function step(now) {
				var pct = Math.min(100, ((now - progStart) / dur) * 100);
				progBar.style.width = pct + '%';
				if (pct < 100 && timer) rafProg = requestAnimationFrame(step);
			}
			rafProg = requestAnimationFrame(step);
		}

		/* ---------- Swipe / Drag ---------- */
		function bindSwipe() {
			var startX = 0, dx = 0, dragging = false;
			function down(x) { dragging = true; startX = x; dx = 0; stop(); track.style.transition = 'none'; }
			function move(x) {
				if (!dragging) return;
				dx = x - startX;
			}
			function up() {
				if (!dragging) return;
				dragging = false;
				track.style.transition = '';
				var threshold = viewport.offsetWidth * 0.12;
				var rtl = getComputedStyle(root).direction === 'rtl';
				if (Math.abs(dx) > threshold) {
					var forward = rtl ? dx > 0 : dx < 0;
					forward ? next() : prev();
				} else {
					render(true);
				}
				userInteract();
			}
			// Touch
			viewport.addEventListener('touchstart', function (e) { down(e.touches[0].clientX); }, { passive: true });
			viewport.addEventListener('touchmove', function (e) { move(e.touches[0].clientX); }, { passive: true });
			viewport.addEventListener('touchend', up);
			// Mouse drag
			viewport.addEventListener('mousedown', function (e) { e.preventDefault(); down(e.clientX); });
			window.addEventListener('mousemove', function (e) { move(e.clientX); });
			window.addEventListener('mouseup', up);
		}

		/* ---------- Keyboard ---------- */
		root.setAttribute('tabindex', '0');
		root.addEventListener('keydown', function (e) {
			var rtl = getComputedStyle(root).direction === 'rtl';
			if (e.key === 'ArrowLeft')  { rtl ? next() : prev(); userInteract(); }
			else if (e.key === 'ArrowRight') { rtl ? prev() : next(); userInteract(); }
			else if (e.key === 'Home') { goTo(0); userInteract(); }
			else if (e.key === 'End')  { goTo(pageCount() - 1); userInteract(); }
		});

		/* ---------- Hover & visibility ---------- */
		if (cfg.pauseHover) {
			root.addEventListener('mouseenter', stop);
			root.addEventListener('mouseleave', start);
			root.addEventListener('focusin', stop);
			root.addEventListener('focusout', start);
		}
		document.addEventListener('visibilitychange', function () {
			if (document.hidden) stop(); else start();
		});

		/* ---------- Arrow buttons ---------- */
		if (prevBtn) prevBtn.addEventListener('click', function () { prev(); userInteract(); });
		if (nextBtn) nextBtn.addEventListener('click', function () { next(); userInteract(); });

		/* ---------- Resize ---------- */
		var rt;
		window.addEventListener('resize', function () {
			clearTimeout(rt);
			rt = setTimeout(function () {
				layoutSlides();
				buildDots();
				goTo(Math.min(index, pageCount() - 1));
			}, 180);
		});

		/* ---------- Init ---------- */
		bindSwipe();
		layoutSlides();
		buildDots();
		// تاخیر کوتاه تا ابعاد محاسبه شوند
		setTimeout(function () {
			layoutSlides();
			render(false);
			start();
		}, 60);
	}

	function init() {
		var nodes = document.querySelectorAll('.glass-ads-slider');
		for (var i = 0; i < nodes.length; i++) initSlider(nodes[i]);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// برای Elementor (پیش‌نمایش زنده در ویرایشگر)
	if (window.elementorFrontend) {
		window.addEventListener('elementor/frontend/init', function () {
			if (window.elementorFrontend.hooks) {
				window.elementorFrontend.hooks.addAction('frontend/element_ready/glass_pro_ads_slider.default', function ($scope) {
					var el = $scope[0] ? $scope[0].querySelector('.glass-ads-slider') : null;
					if (el) initSlider(el);
				});
			}
		});
	}
})();
