<?php
/**
 * Hooks Registry — مرکز همه فیلترها و اکشن‌های قابل‌توسعه‌ی قالب.
 *
 * این فایل صرفاً نقش رجیستری/مستندساز دارد. توابع `glass_pro_apply_*` و
 * `glass_pro_do_*` در زیر، شیر‌های توسعه‌پذیری هستند که از داخل قالب صدا
 * زده می‌شوند و توسعه‌دهنده با add_filter/add_action می‌تواند تغییرشان دهد.
 *
 * فهرست hookها (تمام‌نما):
 *
 *   FILTERS
 *   -------
 *   glass_pro/font_url                    → URL فونت اصلی
 *   glass_pro/enable_persian_numbers      → فعال/غیرفعال تبدیل اعداد فارسی
 *   glass_pro/schema/organization         → بازنویسی Schema Organization
 *   glass_pro/login_max_attempts          → سقف تلاش‌های ورود
 *   glass_pro/rate_limit_max_login        → سقف rate-limit ورود
 *   glass_pro/rate_limit_max_register     → سقف rate-limit ثبت‌نام
 *   glass_pro/rate_limit_max_lostpass     → سقف rate-limit فراموشی رمز
 *   glass_pro/client_ip                   → override IP کاربر (Cloudflare, ...)
 *   glass_t                               → فیلتر سراسری ترجمه‌ی کلیدها
 *   glass_pro/portfolio/excerpt_length    → طول خلاصه‌ی کارت پورتفولیو
 *   glass_pro/portfolio/per_page          → تعداد آگهی در آرشیو
 *   glass_pro/blog/excerpt_length         → طول خلاصه‌ی کارت بلاگ
 *   glass_pro/header/menu_args            → آرگومان‌های wp_nav_menu هدر
 *   glass_pro/footer/menu_args            → آرگومان‌های wp_nav_menu فوتر
 *   glass_pro/security/headers            → آرایه‌ی هدرهای امنیتی
 *   glass_pro/seo/schema_enabled          → فعال/غیرفعال JSON-LD
 *   glass_pro/captcha/enabled             → فعال/غیرفعال captcha
 *
 *   ACTIONS
 *   -------
 *   glass_pro/before_page                 → پس از <body>
 *   glass_pro/after_page                  → قبل از </body>
 *   glass_pro/before_header                → قبل از header sticky
 *   glass_pro/after_header                 → بعد از header sticky
 *   glass_pro/before_main                  → ابتدای main
 *   glass_pro/after_main                   → انتهای main
 *   glass_pro/single/before_content       → قبل از محتوای single
 *   glass_pro/single/after_content        → بعد از محتوای single
 *   glass_pro/portfolio/before_card       → قبل از هر کارت پورتفولیو
 *   glass_pro/portfolio/after_card        → بعد از هر کارت پورتفولیو
 *   glass_pro/portfolio/submitted         → پس از ثبت موفق آگهی (post_id, user_id)
 *   glass_pro/user/registered             → پس از ثبت‌نام موفق (user_id)
 *
 * @package Alborz_Ghaleb
 * @since   5.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   API توابع کمکی برای استفاده‌ی توسعه‌دهنده
   ──────────────────────────────────────── */

if ( ! function_exists( 'glass_pro_get_setting' ) ) :
	/**
	 * دریافت یک تنظیم قالب با امکان override توسط فیلتر.
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	function glass_pro_get_setting( $key, $default = null ) {
		$value = get_theme_mod( 'glass_pro_' . $key, $default );
		/**
		 * فیلتر مقدار یک تنظیم قالب.
		 *
		 * @param mixed  $value
		 * @param string $key
		 * @param mixed  $default
		 */
		return apply_filters( 'glass_pro/setting/' . $key, $value, $key, $default );
	}
endif;

if ( ! function_exists( 'glass_pro_portfolio_per_page' ) ) :
	/**
	 * تعداد آگهی در صفحه آرشیو (قابل override با فیلتر).
	 *
	 * @return int
	 */
	function glass_pro_portfolio_per_page(): int {
		return (int) apply_filters( 'glass_pro/portfolio/per_page', 12 );
	}
endif;

if ( ! function_exists( 'glass_pro_blog_excerpt_length' ) ) :
	/**
	 * طول خلاصه‌ی بلاگ.
	 *
	 * @return int
	 */
	function glass_pro_blog_excerpt_length(): int {
		return (int) apply_filters( 'glass_pro/blog/excerpt_length', 28 );
	}
endif;

/* ────────────────────────────────────────
   اعمال فیلتر طول excerpt
   ──────────────────────────────────────── */
add_filter( 'excerpt_length', 'glass_pro_filter_excerpt_length', 999 );
/**
 * اعمال طول excerpt مطابق فیلتر glass_pro/blog/excerpt_length.
 *
 * @param int $length
 * @return int
 */
function glass_pro_filter_excerpt_length( int $length ): int {
	if ( is_singular() ) {
		return $length;
	}
	return glass_pro_blog_excerpt_length();
}

/* ────────────────────────────────────────
   [FIX] Wrap bare <table> tags for horizontal scroll on mobile
   جدول‌های بدون wrapper را در .glass-table-wrap قرار می‌دهد
   تا در موبایل اسکرول افقی داشته باشند (مثل برگه‌ها)
   ──────────────────────────────────────── */
add_filter( 'the_content', 'glass_pro_wrap_bare_tables', 50 );
function glass_pro_wrap_bare_tables( $content ) {
    if ( empty( $content ) ) {
        return $content;
    }
    // Skip if already inside a glass-table-wrap or wp-block-table
    // Wrap bare <table>...</table> that are NOT already inside a wrapper
    $content = preg_replace(
        '#(?<!<div class="glass-table-wrap">)\s*(<table\b[^>]*>.*?</table>)\s*(?!</div>)#is',
        '<div class="glass-table-wrap">$1</div>',
        $content
    );
    return $content;
}
