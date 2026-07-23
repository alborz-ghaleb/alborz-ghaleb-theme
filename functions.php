<?php
/**
 * Alborz Ghaleb — Bootstrapper
 *
 * این فایل فقط نقش راه‌انداز (bootstrapper) را دارد.
 * تمام منطق در فایل‌های پوشه‌ی /inc تفکیک شده است.
 *
 * ساختار:
 *   /inc/setup.php                 → theme supports, menus, image sizes, textdomain
 *   /inc/enqueue.php               → بارگذاری assets
 *   /inc/helpers.php               → توابع کمکی + پاکسازی کش
 *   /inc/post-types.php            → CPT/Taxonomy/Rewrite (عیناً از نسخه قبل)
 *   /inc/security.php              → سخت‌سازی امنیتی
 *   /inc/performance.php           → preload/defer/حذف اضافات
 *   /inc/seo.php                   → Schema/OG شرطی (فقط در نبود Rank Math)
 *   /inc/rtl-persian.php           → فونت فارسی + اعداد + RTL
 *   /inc/accessibility.php         → skip-link + a11y
 *   /inc/elementor-integration.php → ویجت‌های سفارشی
 *   /inc/legacy/*                  → ماژول‌های پایدار نسخه قبل (پرداخت، پنل، فرم، ...)
 *
 * نکته: این قالب کاملاً مستقل (Standalone) است و هیچ وابستگی به
 * قالب مادر (Hello Elementor یا هر قالب دیگر) ندارد.
 *
 * @package Alborz_Ghaleb
 * @version 8.9.2
 * @author  M.Dasht Abadi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   ثابت‌های تم
   ──────────────────────────────────────── */
define( 'GLASS_PRO_VERSION', '8.9.2' );

/*
 * فایل‌های PHP و assetهای اصلی متعلق به همین Theme/Parent هستند.
 *
 * در حالت Standalone این دو تابع همان مسیر را برمی‌گردانند. اگر روزی روی
 * این قالب Child Theme فعال شود، get_stylesheet_directory() به پوشه Child
 * اشاره می‌کند و includeهای /inc را می‌شکند؛ بنابراین برای منابع اصلی از
 * template directory استفاده می‌کنیم. Child Theme همچنان می‌تواند style.css
 * خود را از طریق get_stylesheet_uri() در enqueue.php ارائه کند.
 */
define( 'GLASS_PRO_DIR',     get_template_directory() );
define( 'GLASS_PRO_URI',     get_template_directory_uri() );

/* سازگاری عقب‌رو: ماژول‌های legacy از این ثابت‌ها استفاده می‌کنند. */
if ( ! defined( 'GLASS_VERSION' ) ) define( 'GLASS_VERSION', GLASS_PRO_VERSION );
if ( ! defined( 'GLASS_DIR' ) )     define( 'GLASS_DIR',     GLASS_PRO_DIR );
if ( ! defined( 'GLASS_URI' ) )     define( 'GLASS_URI',     GLASS_PRO_URI );

/* ────────────────────────────────────────
   ۱) ماژول‌های زیرساختِ جدید (PRO)
   ──────────────────────────────────────── */
$glass_pro_core = [
	'/inc/helpers.php',
		'/inc/cache-helper.php',
	'/inc/setup.php',
	'/inc/hooks.php',
	'/inc/core-plugin-compat.php',
	'/inc/ui-translations.php',
	'/inc/contact-customizer.php', 
	'/inc/zarinpal-helper.php',
	'/inc/email-verification.php',
	'/inc/bot-detection.php',
	'/inc/comment-spam-guard.php', // محدودیت ۲ دیدگاه/۲۴ ساعت + ضداسپم
	'/inc/portfolio-upload-api.php',
	'/inc/csp.php',
	'/inc/critical-css.php',
	'/inc/pwa.php',
	'/inc/webp-support.php',
	'/inc/welcome-wizard.php',
	'/inc/health-check.php',
	'/inc/admin-settings.php',
	'/inc/transaction-log.php',
	'/inc/emails.php',
	'/inc/moderation.php',
	'/inc/naming-aliases.php',
	'/inc/settings-export.php',
	'/inc/post-types.php',
	'/inc/enqueue.php',
	'/inc/security.php',
	'/inc/performance.php',
	'/inc/performance-lighthouse.php',
	'/inc/layout-optimizations.php', // یک درخواست CSS برای همه ماژول‌های چیدمان
	'/inc/mobile-stage-4.php', // فقط مرحله ۴: کاهش درخواست فونت
	'/inc/mobile-quick-bar.php', // نوار اقدام سریع موبایل؛ مستقل و قابل حذف
	'/inc/mobile-content-spacing.php', // کاهش گاتر بیرونی محتوا در موبایل
	'/inc/mobile-inner-spacing.php', // کاهش پدینگ داخلی کارت‌های محتوا
	'/inc/mobile-home-aw.php', // چیدمان اختصاصی موبایل صفحه اصلی agh-aw
	'/inc/desktop-inner-spacing.php', // کاهش پدینگ داخلی دسکتاپ
	'/inc/desktop-top-spacing.php', // کاهش فاصله زیر هدر تا اولین آیتم دسکتاپ
	'/inc/rtl-persian.php',
	'/inc/accessibility.php',
	'/inc/seo.php',
	'/inc/seo-enhancements.php',
	'/inc/category-routing-fix.php', // رفع 404 و ریدایرکت اشتباه دسته‌بندی‌ها
	'/inc/content-shortcodes.php',
	'/inc/elementor-integration.php',
	'/inc/polylang.php',
];

/* ────────────────────────────────────────
   ۲) ماژول‌های پایدارِ منتقل‌شده از نسخه قبل (legacy)
   ترتیب مهم است: i18n قبل از بقیه.
   ──────────────────────────────────────── */
$glass_pro_legacy = [
	'/inc/legacy/functions-i18n-ads.php',
	'/inc/legacy/functions-header.php',
	'/inc/legacy/functions-breadcrumb.php',
	'/inc/legacy/functions-footer.php',
	'/inc/legacy/functions-floating.php',
	'/inc/legacy/functions-blog.php',
	'/inc/legacy/functions-portfolio.php',
	'/inc/legacy/functions-portfolio-submit.php',
	'/inc/legacy/functions-search.php',
	'/inc/legacy/functions-comments.php',
	'/inc/legacy/functions-ads-slider.php',
	'/inc/legacy/functions-user-panel.php',
];

// [PERF v5.9.1] ماژول‌های صرفاً مدیریتی را در فرانت‌اند لود نکنیم.
// این کار بدون حذف قابلیت‌ها، تعداد hook/functionهای لودشده در هر page-view را کم می‌کند.
$is_api_or_cron = ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() );
if ( ! is_admin() && ! $is_api_or_cron ) {
	$glass_pro_admin_only = [
		'/inc/welcome-wizard.php',
		'/inc/health-check.php',
		'/inc/settings-export.php',
		'/inc/moderation.php',
	];

	// [PERF v5.10.2] شبیه رفتار سبک نسخه 5.0.2: ماژول‌های payment/mail/log فقط روی درخواست‌های مرتبط لود شوند.
	$glass_pro_is_payment_request = isset( $_POST['glass_pf_submit'] )
		|| isset( $_POST['glass_ad_action'] )
		|| isset( $_GET['glass_zp_callback'] )
		|| isset( $_GET['glass_renew_callback'] )
		|| isset( $_GET['glass_feature_callback'] );

	$glass_pro_email_verify_enabled = (bool) get_option( 'glass_pro_require_email_verification', false );
	$glass_pro_needs_email_verify   = $glass_pro_email_verify_enabled || isset( $_GET['glass_pro_verify'] );

	$glass_pro_lazy_front_modules = [];
	if ( ! $glass_pro_is_payment_request ) {
		$glass_pro_lazy_front_modules[] = '/inc/zarinpal-helper.php';
		$glass_pro_lazy_front_modules[] = '/inc/transaction-log.php';
		$glass_pro_lazy_front_modules[] = '/inc/emails.php';
	}
	if ( ! $glass_pro_needs_email_verify ) {
		$glass_pro_lazy_front_modules[] = '/inc/email-verification.php';
	}

	$glass_pro_core = array_values( array_diff( $glass_pro_core, array_merge( $glass_pro_admin_only, $glass_pro_lazy_front_modules ) ) );
}

foreach ( array_merge( $glass_pro_core, $glass_pro_legacy ) as $glass_pro_file ) {
	$glass_pro_path = GLASS_PRO_DIR . $glass_pro_file;
	if ( is_readable( $glass_pro_path ) ) {
		require_once $glass_pro_path; // بدون @ : خطاهای واقعی فایل پنهان نمی‌شوند (DX بهتر)
	}
}
