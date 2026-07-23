<?php
/**
 * Assets Enqueue
 *
 * بارگذاری CSS/JS با dependency و نسخه‌گذاری filemtime.
 * [REFACTOR v5.14.0] JS Bundling — 10 فایل مجزا → 2 باندل بهینه
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * بررسی وجود فایل با cache per-request — جلوگیری از تکرار stat() system call.
 *
 * @param string $rel_path مسیر نسبی از ریشه‌ی تم.
 * @return bool
 */
function glass_pro_asset_exists( string $rel_path ): bool {
	static $cache = [];
	if ( isset( $cache[ $rel_path ] ) ) {
		return $cache[ $rel_path ];
	}
	$cache[ $rel_path ] = is_readable( GLASS_PRO_DIR . '/' . ltrim( $rel_path, '/' ) );
	return $cache[ $rel_path ];
}

/**
 * نسخه‌ی فایل برای cache-busting.
 *
 * @param string $rel_path مسیر نسبی از ریشه‌ی تم.
 * @return string
 */
function glass_pro_asset_ver( string $rel_path ): string {
	if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
		return GLASS_PRO_VERSION;
	}
	static $cache = [];
	if ( isset( $cache[ $rel_path ] ) ) {
		return $cache[ $rel_path ];
	}
	$full = GLASS_PRO_DIR . '/' . ltrim( $rel_path, '/' );
	$cache[ $rel_path ] = is_readable( $full ) ? (string) filemtime( $full ) : GLASS_PRO_VERSION;
	return $cache[ $rel_path ];
}

/**
 * تشخیص برگه تماس در همه زبان‌ها (Polylang) برای بارگذاری استایل اختصاصی.
 */
function glass_pro_is_contact_page(): bool {
	if ( is_page_template( 'page-contact.php' ) ) {
		return true;
	}

	$contact_slugs = (array) apply_filters(
		'glass_pro/contact_page_slugs',
		[ 'contact', 'contact-us', 'تماس-با-ما' ]
	);
	if ( is_page( $contact_slugs ) ) {
		return true;
	}

	$contact = get_page_by_path( 'contact', OBJECT, 'page' );
	if ( $contact && function_exists( 'pll_get_post_translations' ) ) {
		$ids = array_map( 'intval', array_values( pll_get_post_translations( $contact->ID ) ) );
		return in_array( (int) get_queried_object_id(), $ids, true );
	}

	return false;
}

add_action( 'wp_enqueue_scripts', 'glass_pro_enqueue_assets', 20 );
/**
 * صف‌بندی استایل‌ها و اسکریپت‌های فرانت‌اند.
 *
 * @return void
 */
function glass_pro_enqueue_assets(): void {

	/* ── فونت Vazirmatn (self-hosted) ── */
	$font_url = apply_filters( 'glass_pro/font_url', GLASS_PRO_URI . '/assets/fonts/vazirmatn/vazirmatn.css' );
	if ( $font_url ) {
		$inline_font_css = (bool) apply_filters( 'glass_pro/font_inline_css', true );
		if ( $inline_font_css && false !== strpos( (string) $font_url, '/assets/fonts/vazirmatn/vazirmatn.css' ) ) {
			wp_register_style( 'glass-vazirmatn', false, [], GLASS_PRO_VERSION );
			wp_enqueue_style( 'glass-vazirmatn' );
			$font_base = trailingslashit( GLASS_PRO_URI ) . 'assets/fonts/vazirmatn/webfonts/';
			$font_css  = '';
			// Extension point for the independently removable font optimization.
			// Without a filter, the original four files remain exactly unchanged.
			$font_weights = (array) apply_filters( 'glass_pro/font_weights', [
				400 => 'Regular',
				500 => 'Medium',
				600 => 'SemiBold',
				700 => 'Bold',
			] );
			$allowed_font_files = [ 'Regular', 'Medium', 'SemiBold', 'Bold' ];
			foreach ( $font_weights as $weight => $file_weight ) {
				$file_weight = (string) $file_weight;
				if ( ! in_array( $file_weight, $allowed_font_files, true ) ) {
					continue;
				}
				$font_css .= "@font-face{font-family:'Vazirmatn';src:url('" . esc_url_raw( $font_base . 'Vazirmatn-' . $file_weight . '.woff2' ) . "') format('woff2');font-weight:" . (int) $weight . ";font-style:normal;font-display:swap;}";
			}
			wp_add_inline_style( 'glass-vazirmatn', $font_css );
		} else {
			wp_enqueue_style( 'glass-vazirmatn', $font_url, [], GLASS_PRO_VERSION );
		}
	}

	/* ── استایل اصلی تم؛ نسخه minified در حالت Standalone، با حفظ Child Theme ── */
	$main_style_path = 'assets/css/style.min.css';
	$main_style_url  = get_stylesheet_uri();
	$main_style_ver  = GLASS_PRO_VERSION;
	if ( get_stylesheet_directory() === get_template_directory() && glass_pro_asset_exists( $main_style_path ) ) {
		$main_style_url = GLASS_PRO_URI . '/' . $main_style_path;
		$main_style_ver = glass_pro_asset_ver( $main_style_path );
	}
	wp_enqueue_style( 'glass-style', $main_style_url, [ 'glass-vazirmatn' ], $main_style_ver );

	/* ── باندل CSS سراسری: چهار درخواست به یک درخواست کاهش یافته است ── */
	if ( glass_pro_asset_exists( 'assets/css/core.css' ) ) {
		wp_enqueue_style( 'glass-core', GLASS_PRO_URI . '/assets/css/core.css', [ 'glass-style' ], glass_pro_asset_ver( 'assets/css/core.css' ) );

		// aliasهای بدون خروجی برای سازگاری افزونه‌ها/کدهایی که به handleهای قدیمی وابسته‌اند.
		foreach ( [ 'glass-header', 'glass-footer', 'glass-floating', 'glass-content-classes' ] as $legacy_handle ) {
			wp_register_style( $legacy_handle, false, [ 'glass-core' ], GLASS_PRO_VERSION );
			wp_enqueue_style( $legacy_handle );
		}
	}

	/* ── استایل دارک مود (بعد از core) ── */
	if ( glass_pro_asset_exists( 'assets/css/dark-mode.css' ) ) {
		wp_enqueue_style( 'glass-dark-mode', GLASS_PRO_URI . '/assets/css/dark-mode.css', [ 'glass-core' ], glass_pro_asset_ver( 'assets/css/dark-mode.css' ) );
	}
	/* فیکس‌های نهایی داخل dark-mode.css ادغام شده‌اند تا یک درخواست CSS حذف شود. */

	/* ── استایل RTL ── */
	if ( is_rtl() && glass_pro_asset_exists( 'assets/css/i18n-safe.css' ) ) {
		wp_enqueue_style( 'glass-i18n-safe', GLASS_PRO_URI . '/assets/css/i18n-safe.css', [ 'glass-style' ], glass_pro_asset_ver( 'assets/css/i18n-safe.css' ) );
	}

	/* ── Dark mode — در head برای جلوگیری از FOUC و اجرای تک‌نمونه ── */
	if ( glass_pro_asset_exists( 'assets/js/dark-mode.js' ) ) {
		wp_enqueue_script( 'glass-dark-mode-js', GLASS_PRO_URI . '/assets/js/dark-mode.js', [], glass_pro_asset_ver( 'assets/js/dark-mode.js' ), false );
	}

	/* ────────────────────────────────────────
	   [PERF v5.14.0] JS Bundling
	   - glass-bundle.js: header + footer + floating — همه صفحات
	   - glass-portfolio-bundle.js: single-portfolio + ads-slider + comments — فقط صفحات آگهی
	   - سایر فایل‌های JS سبک: شرطی بر اساس صفحه
	   ──────────────────────────────────────── */

	// Bundle اصلی (همه صفحات)
	if ( glass_pro_asset_exists( 'assets/js/glass-bundle.js' ) ) {
		wp_enqueue_script( 'glass-bundle', GLASS_PRO_URI . '/assets/js/glass-bundle.js', [], glass_pro_asset_ver( 'assets/js/glass-bundle.js' ), true );

		// Localize data
		wp_localize_script( 'glass-bundle', 'glassProData', [
			'ajaxUrl'                 => admin_url( 'admin-ajax.php' ),
			'nonce'                   => wp_create_nonce( 'glass_nonce' ),
			'isRtl'                   => is_rtl(),
		] );
	}

	// انیمیشن reveal عمداً غیرفعال است؛ عناصر از ابتدا قابل مشاهده‌اند و
	// فایل JavaScript اضافی برای آن دانلود نمی‌شود.

	/* ── Bundle اختصاصی آگهی‌ها و استایل‌ها ── */
	if ( is_singular( 'portfolio' ) ) {
		if ( glass_pro_asset_exists( 'assets/css/single-portfolio.css' ) ) {
			wp_enqueue_style( 'glass-single-portfolio', GLASS_PRO_URI . '/assets/css/single-portfolio.css', [ 'glass-style' ], glass_pro_asset_ver( 'assets/css/single-portfolio.css' ) );
		}
		if ( glass_pro_asset_exists( 'assets/js/glass-portfolio-bundle.js' ) ) {
			wp_enqueue_script( 'glass-portfolio-bundle', GLASS_PRO_URI . '/assets/js/glass-portfolio-bundle.js', [ 'glass-bundle' ], glass_pro_asset_ver( 'assets/js/glass-portfolio-bundle.js' ), true );
		}
	}

	/* ── استایل و JS بخش نظرات (دیدگاه‌ها) ── */
	if ( is_singular() && ( comments_open() || get_comments_number() ) ) {
		if ( glass_pro_asset_exists( 'assets/css/comments-glass.css' ) ) {
			wp_enqueue_style( 'glass-comments', GLASS_PRO_URI . '/assets/css/comments-glass.css', [ 'glass-style' ], glass_pro_asset_ver( 'assets/css/comments-glass.css' ) );
		}
		if ( ! is_singular( 'portfolio' ) && glass_pro_asset_exists( 'assets/js/comments-glass.js' ) ) {
			wp_enqueue_script( 'glass-comments-js', GLASS_PRO_URI . '/assets/js/comments-glass.js', [ 'glass-bundle' ], glass_pro_asset_ver( 'assets/js/comments-glass.js' ), true );
		}
	}

	/* ── استایل/JS مخصوص بلاگ و برگه‌ها (فهرست مطالب) ── */
	if ( is_singular( 'post' ) || is_page() ) {
		glass_pro_enqueue_conditional( 'glass-single-blog', 'single-blog', [ 'glass-style', 'glass-dark-mode' ] );
	}

	/* CSS قالب برگه از HTML خارج شده تا توسط مرورگر cache شود. */
	if ( is_page() && glass_pro_asset_exists( 'assets/css/page.css' ) ) {
		wp_enqueue_style( 'glass-page', GLASS_PRO_URI . '/assets/css/page.css', [ 'glass-single-blog', 'glass-dark-mode' ], glass_pro_asset_ver( 'assets/css/page.css' ) );
	}

	/* Breadcrumb در همه صفحات داخلی؛ فایل خارجی و cacheable. */
	if ( ! is_front_page() && ! is_home() && glass_pro_asset_exists( 'assets/css/breadcrumb.css' ) ) {
		wp_enqueue_style( 'glass-breadcrumb', GLASS_PRO_URI . '/assets/css/breadcrumb.css', [ 'glass-dark-mode' ], glass_pro_asset_ver( 'assets/css/breadcrumb.css' ) );
	}

	/* ── استایل مخصوص صفحه تماس ── */
	if ( glass_pro_is_contact_page() ) {
		glass_pro_enqueue_conditional( 'glass-contact-page', 'contact-page', [ 'glass-style', 'glass-dark-mode' ] );
	}

	/* ── استایل مخصوص 404 ── */
	if ( is_404() ) {
		wp_enqueue_style( 'glass-page-404', GLASS_PRO_URI . '/assets/css/page-404.css', [ 'glass-style' ], glass_pro_asset_ver( 'assets/css/page-404.css' ) );
	}

	/* ── استایل آرشیوها؛ جلوگیری از بارگذاری CSS بلااستفاده روی بلاگ legacy ── */
	$glass_legacy_blog = is_home() || is_category() || is_tag();
	if ( $glass_legacy_blog && glass_pro_asset_exists( 'assets/css/legacy-blog.css' ) ) {
		wp_enqueue_style( 'glass-legacy-blog', GLASS_PRO_URI . '/assets/css/legacy-blog.css', [ 'glass-dark-mode' ], glass_pro_asset_ver( 'assets/css/legacy-blog.css' ) );
	} elseif ( is_archive() ) {
		glass_pro_enqueue_conditional( 'glass-blog-archive', 'blog-archive', [ 'glass-style' ] );
	}

	/* ── پنل کاربری ── */
	if ( function_exists( 'glass_is_dashboard_page' ) && glass_is_dashboard_page() ) {
		if ( glass_pro_asset_exists( 'assets/css/user-panel.css' ) ) {
			wp_enqueue_style( 'glass-user-panel', GLASS_PRO_URI . '/assets/css/user-panel.css', [ 'glass-style' ], glass_pro_asset_ver( 'assets/css/user-panel.css' ) );
		}
		if ( glass_pro_asset_exists( 'assets/js/user-panel.js' ) ) {
			wp_enqueue_script( 'glass-user-panel', GLASS_PRO_URI . '/assets/js/user-panel.js', [ 'glass-bundle' ], glass_pro_asset_ver( 'assets/js/user-panel.js' ), true );
		}
	}
}

/**
 * بارگذاری شرطی یک جفت CSS/JS هم‌نام.
 *
 * @param string $handle  هندل پایه.
 * @param string $slug    نام فایل بدون پسوند (در assets/css و assets/js).
 * @param array  $css_deps وابستگی‌های CSS.
 * @return void
 */
function glass_pro_enqueue_conditional( string $handle, string $slug, array $css_deps = [] ): void {
	$css = 'assets/css/' . $slug . '.css';
	$js  = 'assets/js/' . $slug . '.js';

	if ( glass_pro_asset_exists( $css ) ) {
		wp_enqueue_style( $handle, GLASS_PRO_URI . '/' . $css, $css_deps, glass_pro_asset_ver( $css ) );
	}
	if ( glass_pro_asset_exists( $js ) ) {
		wp_enqueue_script( $handle . '-js', GLASS_PRO_URI . '/' . $js, [ 'glass-bundle' ], glass_pro_asset_ver( $js ), true );
	}
}

/* ── defer روی اسکریپت‌های غیرحیاتی ── */
add_filter( 'script_loader_tag', 'glass_pro_defer_scripts', 10, 2 );
function glass_pro_defer_scripts( string $tag, string $handle ): string {
	$defer = [ 'glass-bundle', 'glass-portfolio-bundle', 'glass-user-panel' ];
	if ( in_array( $handle, $defer, true ) && false === strpos( $tag, ' defer' ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}
	return $tag;
}

/* ── بارگذاری CSS محتوای شیشه‌ای در ادیتور المنتور ── */
add_action( 'elementor/editor/after_enqueue_styles', 'glass_pro_elementor_editor_styles' );
/**
 * لود استایل‌های کلاس‌های glass-* در پیش‌نمایش ادیتور المنتور.
 *
 * @return void
 */
function glass_pro_elementor_editor_styles(): void {
	$css = 'assets/css/glass-content-classes.css';
	if ( glass_pro_asset_exists( $css ) ) {
		wp_enqueue_style( 'glass-content-classes-editor', GLASS_PRO_URI . '/' . $css, [], glass_pro_asset_ver( $css ) );
	}
}


