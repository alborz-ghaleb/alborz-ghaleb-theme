<?php
/**
 * Polylang Integration — String Translations
 *
 * این ماژول رشته‌های قابل‌ترجمه‌ی قالب را با Polylang هماهنگ می‌کند:
 *
 *  ۱) همه‌ی کلیدهای بانک glass_t در «زبان‌ها → ترجمهٔ رشته‌ها» ثبت می‌شوند
 *     (pll_register_string) تا مدیر بتواند از پنل ویرایش کند.
 *  ۲) ترجمه‌های آماده‌ی بانک (en/ar/tr/ru/hy) به‌صورت خودکار «یک‌بار» وارد
 *     جدول ترجمه‌های Polylang می‌شوند (auto-import) تا از روز اول پر باشند.
 *  ۳) خروجی glass_t در صورت وجود ترجمهٔ Polylang، از آن استفاده می‌کند
 *     (اولویت با ترجمهٔ مدیر در پنل).
 *
 * اگر Polylang نصب نباشد، این فایل عملاً بی‌اثر است و بانک داخلی glass_t
 * مثل قبل کار می‌کند.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* فقط در صورت فعال بودن Polylang ادامه بده. */
if ( ! function_exists( 'pll_register_string' ) ) {
	return;
}

const GLASS_PRO_PLL_GROUP = 'Alborz Ghaleb';

/* ────────────────────────────────────────
   ۱) ثبت رشته‌ها در Polylang Strings
   ──────────────────────────────────────── */
add_action( 'init', 'glass_pro_pll_register_strings', 20 );
/**
 * ثبت همه‌ی کلیدهای بانک glass_t (با متن فارسی به‌عنوان مرجع) در Polylang.
 *
 * @return void
 */
function glass_pro_pll_register_strings() {
	if ( ! function_exists( 'glass_ads_strings' ) ) {
		return;
	}
	$bank = glass_ads_strings();
	$ref  = isset( $bank['fa'] ) ? $bank['fa'] : ( isset( $bank['en'] ) ? $bank['en'] : [] );

	foreach ( $ref as $key => $text ) {
		// نام رشته = کلید (برای شناسایی آسان در پنل)؛ مقدار = متن فارسی مرجع.
		$len       = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
		$multiline = ( $len > 80 || false !== strpos( $text, "\n" ) );
		pll_register_string( 'glass_' . $key, $text, GLASS_PRO_PLL_GROUP, $multiline );
	}
}

/* ────────────────────────────────────────
   ۲) Auto-import ترجمه‌های بانک به جدول Polylang (یک‌بار)
   ──────────────────────────────────────── */
add_action( 'admin_init', 'glass_pro_pll_autoimport_translations' );
/**
 * پر کردن خودکار ترجمه‌های Polylang از روی بانک داخلی قالب.
 *
 * ⚙️ روش درست: Polylang ترجمهٔ رشته‌ها را در دیتابیس (پست‌نوع polylang_mo)
 * نگه می‌دارد، نه در فایل .mo داخل uploads. بنابراین از کلاس داخلی PLL_MO
 * استفاده می‌کنیم تا ترجمه‌ها در همان جدول Polylang نوشته شوند و در پنل
 * «زبان‌ها → ترجمهٔ رشته‌ها» پر و قابل‌ویرایش دیده شوند.
 *
 * فقط خانه‌های «خالی» پر می‌شوند؛ اگر مدیر قبلاً چیزی نوشته باشد، دست‌نمی‌خورد.
 * با هر نسخهٔ جدید بانک، دوباره اجرا می‌شود تا کلیدهای جدید هم پر شوند.
 *
 * @return void
 */
function glass_pro_pll_autoimport_translations() {

	$flag = 'glass_pro_pll_imported_' . GLASS_PRO_VERSION;
	if ( get_option( $flag ) ) {
		return;
	}
	if ( ! function_exists( 'glass_ads_strings' ) || ! function_exists( 'pll_languages_list' ) ) {
		return;
	}
	// کلاس‌های داخلی Polylang لازم‌اند.
	if ( ! class_exists( 'PLL_MO' ) || ! class_exists( 'PLL_Language' ) ) {
		return;
	}

	$bank  = glass_ads_strings();
	$langs = pll_languages_list( [ 'fields' => '' ] ); // آرایهٔ آبجکت‌های PLL_Language
	if ( empty( $langs ) ) {
		return;
	}

	$ref = isset( $bank['fa'] ) ? $bank['fa'] : ( isset( $bank['en'] ) ? $bank['en'] : [] );
	if ( empty( $ref ) ) {
		return;
	}

	$done = false;

	foreach ( $langs as $language ) {

		// از هر شکل ورودی، آبجکت زبان را به‌دست بیاور.
		if ( is_string( $language ) && function_exists( 'PLL' ) ) {
			$language = PLL()->model->get_language( $language );
		}
		if ( ! is_object( $language ) || empty( $language->slug ) ) {
			continue;
		}

		$slug      = $language->slug;
		$bank_lang = substr( $slug, 0, 2 ); // fa_IR → fa
		if ( empty( $bank[ $bank_lang ] ) ) {
			continue;
		}

		// MO مخصوص این زبان را از دیتابیس بخوان.
		$mo = new PLL_MO();
		$mo->import_from_db( $language );

		foreach ( $ref as $key => $source ) {
			if ( '' === (string) $source ) {
				continue;
			}
			$translation = isset( $bank[ $bank_lang ][ $key ] ) ? $bank[ $bank_lang ][ $key ] : '';
			if ( '' === (string) $translation ) {
				continue;
			}

			// فقط اگر خالی است پر کن (ترجمهٔ دستیِ مدیر را بازنویسی نکن).
			$current = $mo->translate( $source );
			if ( $current === $source || '' === $current ) {
				if ( method_exists( $mo, 'make_entry' ) ) {
					$entry = $mo->make_entry( $source, $translation );
				} else {
					$entry = new Translation_Entry( [
						'singular'     => $source,
						'translations' => [ $translation ],
					] );
				}
				$mo->add_entry( $entry );
			}
		}

		$mo->export_to_db( $language );
		$done = true;
	}

	if ( $done ) {
		update_option( $flag, time() );
	}
}

/* ────────────────────────────────────────
   ۳) Override خروجی glass_t با ترجمهٔ Polylang
   ──────────────────────────────────────── */
add_filter( 'glass_t', 'glass_pro_pll_filter_glass_t', 10, 3 );
/**
 * در صورت وجود ترجمهٔ Polylang برای رشته، آن را اولویت بده.
 *
 * @param string $value متن نهایی فعلی (از بانک).
 * @param string $key   کلید رشته.
 * @param string $lang  زبان جاری.
 * @return string
 */
function glass_pro_pll_filter_glass_t( $value, $key, $lang ) {
	if ( ! function_exists( 'pll__' ) || ! function_exists( 'glass_ads_strings' ) ) {
		return $value;
	}
	$bank = glass_ads_strings();
	// متن مرجع (همان چیزی که pll_register_string ثبت کرده) = نسخهٔ فارسی کلید.
	$ref = isset( $bank['fa'][ $key ] ) ? $bank['fa'][ $key ] : null;
	if ( null === $ref ) {
		return $value;
	}
	$translated = pll__( $ref );
	// اگر Polylang ترجمه‌ای داشت (و با مرجع فرق داشت)، آن را استفاده کن.
	return ( $translated && $translated !== $ref ) ? $translated : $value;
}

/* ────────────────────────────────────────
   ۴) دکمهٔ دستی «همگام‌سازی ترجمه‌های قالب»
   در صفحهٔ «زبان‌ها → ترجمهٔ رشته‌ها» یک اعلان با دکمه نشان می‌دهد
   تا مدیر بتواند هر زمان خانه‌های خالی را دوباره پر کند
   (مثلاً پس از افزودن زبان جدید).
   ──────────────────────────────────────── */
add_action( 'admin_notices', 'glass_pro_pll_sync_notice' );
/**
 * نمایش اعلان همگام‌سازی در صفحهٔ ترجمهٔ رشته‌های Polylang.
 *
 * @return void
 */
function glass_pro_pll_sync_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$is_pll_strings = $screen && isset( $_GET['page'] ) && 'mlang_strings' === $_GET['page'];
	if ( ! $is_pll_strings ) {
		return;
	}

	if ( isset( $_GET['glass_pll_synced'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>✅ ' .
			esc_html__( 'ترجمه‌های قالب با موفقیت همگام‌سازی شدند (خانه‌های خالی پر شدند).', 'glassmorphism-child-pro' ) .
			'</p></div>';
	}

	$url = wp_nonce_url(
		add_query_arg( [ 'glass_pll_sync' => '1' ] ),
		'glass_pll_sync',
		'glass_pll_nonce'
	);
	echo '<div class="notice notice-info"><p>🌐 <strong>Alborz Ghaleb:</strong> ' .
		esc_html__( 'برای پر کردن خودکار ترجمه‌های خالیِ رشته‌های قالب در همهٔ زبان‌ها روی دکمهٔ زیر بزنید.', 'glassmorphism-child-pro' ) .
		' <a href="' . esc_url( $url ) . '" class="button button-secondary">' .
		esc_html__( 'همگام‌سازی ترجمه‌های قالب', 'glassmorphism-child-pro' ) .
		'</a></p></div>';
}

add_action( 'admin_init', 'glass_pro_pll_handle_manual_sync' );
/**
 * اجرای دستیِ همگام‌سازی پس از کلیک روی دکمه.
 *
 * @return void
 */
function glass_pro_pll_handle_manual_sync() {
	if ( empty( $_GET['glass_pll_sync'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_GET['glass_pll_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['glass_pll_nonce'] ) ), 'glass_pll_sync' ) ) {
		return;
	}

	// flag را پاک کن تا auto-import دوباره اجرا شود.
	delete_option( 'glass_pro_pll_imported_' . GLASS_PRO_VERSION );
	// رشته‌ها باید ثبت شده باشند.
	if ( function_exists( 'glass_pro_pll_register_strings' ) ) {
		glass_pro_pll_register_strings();
	}
	glass_pro_pll_autoimport_translations();

	wp_safe_redirect( add_query_arg(
		[ 'glass_pll_synced' => '1' ],
		remove_query_arg( [ 'glass_pll_sync', 'glass_pll_nonce' ] )
	) );
	exit;
}

/* ────────────────────────────────────────
   ۵) پشتیبانی Polylang از CPT آگهی و دسته‌بندی‌های آن
   ──────────────────────────────────────── */
add_filter( 'pll_get_post_types', 'glass_pro_pll_cpt_support', 10, 2 );
function glass_pro_pll_cpt_support( $post_types, $is_settings ) {
	$post_types['portfolio'] = 'portfolio';
	return $post_types;
}

add_filter( 'pll_get_taxonomies', 'glass_pro_pll_tax_support', 10, 2 );
function glass_pro_pll_tax_support( $taxonomies, $is_settings ) {
	$taxonomies['themsah_theme_type'] = 'themsah_theme_type';
	return $taxonomies;
}

/* ────────────────────────────────────────
   ۶) رفع مشکل پنهان شدن آگهی‌های قبلی (تضمینی)
   ──────────────────────────────────────── */
add_action( 'admin_init', 'glass_pro_pll_fix_missing_languages' );
function glass_pro_pll_fix_missing_languages() {
	if ( get_option( 'glass_pro_pll_cpt_fixed_v2' ) ) {
		return;
	}
	if ( ! function_exists( 'pll_default_language' ) || ! function_exists( 'pll_set_post_language' ) ) {
		return;
	}

	$default_lang = pll_default_language( 'slug' );
	if ( ! $default_lang ) $default_lang = 'fa';

	/*
	 * انتساب زبان پیش‌فرض به تمام آگهی‌های بدون زبان.
	 * استفاده از WP API به‌جای کوئری خام: ایمن‌تر، cache دار، و standards-compliant.
	 * برای دور زدن فیلترهای Polylang از suppress_filters استفاده می‌کنیم.
	 */
	$post_ids = get_posts( [
		'post_type'        => 'portfolio',
		'post_status'      => [ 'publish', 'draft', 'pending', 'private' ],
		'numberposts'      => -1,
		'fields'           => 'ids',
		'suppress_filters' => true,
		'no_found_rows'    => true,
	] );

	foreach ( $post_ids as $pid ) {
		if ( ! pll_get_post_language( $pid ) ) {
			pll_set_post_language( $pid, $default_lang );
		}
	}

	// انتساب زبان پیش‌فرض به دسته‌بندی‌های بدون زبان.
	$term_ids = get_terms( [
		'taxonomy'   => 'themsah_theme_type',
		'hide_empty' => false,
		'fields'     => 'ids',
	] );
	if ( is_wp_error( $term_ids ) ) {
		$term_ids = [];
	}
	foreach ( $term_ids as $tid ) {
		if ( ! pll_get_term_language( $tid ) ) {
			pll_set_term_language( $tid, $default_lang );
		}
	}

	update_option( 'glass_pro_pll_cpt_fixed_v2', time() );
}

/* ────────────────────────────────────────
   ۷) پشتیبانی از ترجمه کلمه "دست دوم" در منوها و تایتل‌ها برای آرشیو
   ──────────────────────────────────────── */

// ثابت برای جلوگیری از hardcode تکراری و آسان‌سازی تغییر آینده.
if ( ! defined( 'GLASS_PRO_PORTFOLIO_LABEL' ) ) {
	define( 'GLASS_PRO_PORTFOLIO_LABEL', 'دست دوم' );
}

/**
 * ثبت رشته‌ی قابل‌ترجمه برای عنوان آرشیو portfolio.
 * این کار باید روی init انجام شود (نه داخل فیلتر رندر)، چون pll_register_string
 * در زمان رندر ممکن است late باشد و باعث INSERT بی‌فایده در هر page-view شود.
 */
add_action( 'init', 'glass_pro_register_portfolio_archive_string', 25 );
function glass_pro_register_portfolio_archive_string() {
	if ( function_exists( 'pll_register_string' ) ) {
		pll_register_string( 'portfolio_archive_title', GLASS_PRO_PORTFOLIO_LABEL, 'Alborz Ghaleb' );
	}
}

/**
 * ترجمه‌ی عنوان آرشیو CPT portfolio.
 *
 * @param string $title
 * @param string $post_type
 * @return string
 */
add_filter( 'post_type_archive_title', 'glass_pro_translate_archive_title', 10, 2 );
function glass_pro_translate_archive_title( $title, $post_type ) {
	if ( 'portfolio' === $post_type && function_exists( 'pll__' ) ) {
		return pll__( GLASS_PRO_PORTFOLIO_LABEL );
	}
	return $title;
}

/**
 * ترجمه‌ی عنوان آیتم منو در صورتی که به آرشیو CPT portfolio لینک شده باشد.
 * بهینه‌شده با شرط‌های null-safe و is_object برای جلوگیری از Fatal Error
 * در زمانی که فیلتر با آبجکت غیرمنتظره صدا زده می‌شود.
 *
 * @param string $title
 * @param mixed  $item
 * @return string
 */
add_filter( 'nav_menu_item_title', 'glass_pro_translate_menu_title', 10, 2 );
function glass_pro_translate_menu_title( $title, $item ) {
	if ( ! is_object( $item ) || ! isset( $item->object, $item->type ) ) {
		return $title;
	}
	if ( 'portfolio' === $item->object
	     && 'post_type_archive' === $item->type
	     && function_exists( 'pll__' ) ) {
		return pll__( GLASS_PRO_PORTFOLIO_LABEL );
	}
	return $title;
}
