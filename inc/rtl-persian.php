<?php
/**
 * RTL & Persian Helpers
 *
 * تبدیل اعداد انگلیسی به فارسی (اختیاری/فیلترپذیر)،
 * font-display:swap برای فونت، helperهای RTL.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   تبدیل ارقام لاتین به فارسی در محتوا/عنوان
   - به‌صورت پیش‌فرض فقط روی سایت فارسی فعال است.
   - با فیلتر glass_pro/enable_persian_numbers قابل خاموش‌کردن.
   - ارقام داخل تگ‌ها/ویژگی‌ها دست‌نمی‌خورند چون فقط روی متن نمایشی اعمال می‌شود.
   ──────────────────────────────────────── */

/**
 * آیا تبدیل اعداد فارسی فعال است؟
 *
 * @return bool
 */
function glass_pro_persian_numbers_enabled(): bool {
	$is_fa = ( 0 === strpos( get_locale(), 'fa' ) );
	return (bool) apply_filters( 'glass_pro/enable_persian_numbers', $is_fa );
}

/**
 * تبدیل ارقام 0-9 لاتین به معادل فارسی.
 *
 * @param string $string رشته‌ی ورودی.
 * @return string
 */
function glass_pro_to_persian_digits( $string ): string {
	if ( '' === $string || null === $string ) {
		return $string;
	}
	// نادیده گرفتن تگ‌های HTML و entityها (مثل &#8211; که خط فاصله است) تا ساختار آنها خراب نشود
	return preg_replace_callback( '/<[^>]+>|&#?[a-zA-Z0-9]+;|[0-9]+/', function( $matches ) {
		$match = $matches[0];
		if ( ctype_digit( $match ) ) {
			$latin   = [ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ];
			$persian = [ '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ];
			return str_replace( $latin, $persian, $match );
		}
		return $match;
	}, $string );
}

add_filter( 'the_title', 'glass_pro_maybe_persian_digits', 20 );
/**
 * اعمال تبدیل اعداد روی عنوان (در فرانت‌اند و در صورت فعال بودن).
 *
 * @param string $title عنوان.
 * @return string
 */
function glass_pro_maybe_persian_digits( $title ) {  // Cannot type-hint: filter می‌تونه null هم پاس بده
	if ( is_admin() || ! glass_pro_persian_numbers_enabled() ) {
		return $title;
	}
	return glass_pro_to_persian_digits( $title );
}

/* ────────────────────────────────────────
   بهبود نمایش فونت: تزریق font-display:swap
   (در صورتی که CSS فونت CDN آن را نداشته باشد)
   ──────────────────────────────────────── */
add_action( 'wp_head', 'glass_pro_font_display_swap', 2 );
/**
 * تضمین font-display:swap برای خانواده‌ی Vazirmatn (کاهش CLS/LCP).
 *
 * @return void
 */
function glass_pro_font_display_swap(): void {
	echo '<style id="glass-pro-font-display">@font-face{font-family:"Vazirmatn";font-display:swap;}</style>' . "\n";
}

/* ────────────────────────────────────────
   helper: آیا جهت جاری RTL است؟ (برای استفاده در قالب‌ها)
   ──────────────────────────────────────── */
if ( ! function_exists( 'glass_pro_is_rtl' ) ) {
	/**
	 * آیا جهت سایت/زبان جاری راست‌به‌چپ است؟
	 *
	 * @return bool
	 */
	function glass_pro_is_rtl() {
		if ( function_exists( 'glass_lang_is_rtl' ) ) {
			return glass_lang_is_rtl();
		}
		return is_rtl();
	}
}
