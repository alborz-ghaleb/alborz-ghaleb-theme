<?php
/**
 * Theme Setup
 *
 * theme supports، منوها، سایزهای تصویر، textdomain.
 * (تجمیع بخش‌های ELEMENTOR SUPPORT + THEME SUPPORT + IMAGE SIZES نسخه قبل.)
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   Textdomainها
   - دامنه‌ی جدید PRO برای فایل‌های جدید.
   - دامنه‌ی قدیمی «glass» برای ماژول‌های legacy (سازگاری ترجمه‌های موجود).
   ──────────────────────────────────────── */
add_action( 'after_setup_theme', 'glass_pro_load_textdomains' );
/**
 * بارگذاری فایل‌های ترجمه.
 *
 * @return void
 */
function glass_pro_load_textdomains(): void {
	load_theme_textdomain( 'glassmorphism-child-pro', GLASS_PRO_DIR . '/languages' );
}

/* ────────────────────────────────────────
   Theme Supports
   ──────────────────────────────────────── */
add_action( 'after_setup_theme', 'glass_pro_theme_support' );
/**
 * فعال‌سازی قابلیت‌های تم.
 *
 * @return void
 */
function glass_pro_theme_support(): void {

	// Elementor — فقط اگر افزونه واقعاً بارگذاری شده باشد.
	if ( did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) ) {
		add_theme_support( 'elementor' );
		add_theme_support( 'elementor-header-footer' );
	}
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );

	// عمومی
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_theme_support( 'custom-logo', [
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	] );

	add_theme_support( 'html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	] );

	// 🚫 WooCommerce: این سایت فروشگاهی نیست؛ هیچ پشتیبانی، استایل یا hook
	//     مربوط به WooCommerce در این قالب وجود ندارد.

	// 🚨 توجه: منوهای ناوبری (primary_*, footer_*) توسط ماژول‌های legacy
	//     (functions-header.php و functions-footer.php) ثبت می‌شوند تا
	//     مکان‌های منوی فعلی سایت بدون تغییر باقی بمانند. اینجا تکرار نمی‌کنیم.
}

/* ────────────────────────────────────────
   Body class — نشانه‌گذاری Elementor
   ──────────────────────────────────────── */
add_filter( 'body_class', 'glass_pro_body_class' );
/**
 * افزودن کلاس elementor-active در صورت بارگذاری Elementor.
 *
 * @param array $classes کلاس‌های body.
 * @return array
 */
function glass_pro_body_class( $classes ): array {
	if ( did_action( 'elementor/loaded' ) ) {
		$classes[] = 'elementor-active';
	}

	// [i18n-safe] اگر زبان جاری فارسی نیست، کلاس محافظ چیدمان اضافه شود
	// تا متن‌های بلندِ ترجمه‌شده قالب را بهم نریزند. (ظاهر فارسی دست‌نخورده می‌ماند.)
	$lang = function_exists( 'glass_current_lang' ) ? glass_current_lang() : substr( get_locale(), 0, 2 );
	if ( 0 !== strpos( (string) $lang, 'fa' ) ) {
		$classes[] = 'glass-i18n-nonfa';
	}

	return $classes;
}

/* ────────────────────────────────────────
   سایزهای تصویر سفارشی (حفظ‌شده عیناً)
   ──────────────────────────────────────── */
add_action( 'after_setup_theme', 'glass_pro_image_sizes' );
/**
 * ثبت سایزهای تصویر اختصاصی تم.
 *
 * @return void
 */
function glass_pro_image_sizes(): void {
	add_image_size( 'glass-card', 400, 280, true );
	add_image_size( 'glass-featured', 900, 500, true );
	add_image_size( 'glass-thumb', 200, 200, true );
}

/* ────────────────────────────────────────
   استایل ادیتور (editor.css)
   ──────────────────────────────────────── */
add_action( 'after_setup_theme', 'glass_pro_editor_styles' );
/**
 * افزودن استایل به ویرایشگر گوتنبرگ/کلاسیک.
 *
 * @return void
 */
function glass_pro_editor_styles(): void {
	add_theme_support( 'editor-styles' );
	if ( is_readable( GLASS_PRO_DIR . '/assets/css/editor.css' ) ) {
		add_editor_style( 'assets/css/editor.css' );
	}
}

/* ────────────────────────────────────────
   راهنمای نصب Elementor (اختیاری)
   ──────────────────────────────────────── */
add_action( 'admin_notices', 'glass_pro_elementor_admin_notice' );
/**
 * نمایش admin notice در صورت عدم نصب Elementor.
 * فقط برای کاربر دارای دسترسی فعال‌سازی افزونه‌ها و فقط در صفحات قالب نمایش داده می‌شود.
 *
 * @return void
 */
function glass_pro_elementor_admin_notice(): void {
	if ( did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) ) {
		return;
	}
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	// نمایش فقط در داشبورد و صفحه پوسته‌ها (نه در همه صفحات ادمین).
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && ! in_array( $screen->id, [ 'dashboard', 'themes', 'appearance_page_glass-pro' ], true ) ) {
		return;
	}
	// امکان dismiss شدن.
	$dismissed_key = 'glass_pro_elementor_notice_dismissed';
	if ( get_user_meta( get_current_user_id(), $dismissed_key, true ) ) {
		return;
	}
	?>
	<div class="notice notice-info is-dismissible" data-glass-pro-notice="elementor">
		<p>
			<strong><?php esc_html_e( 'Alborz Ghaleb', 'glassmorphism-child-pro' ); ?>:</strong>
			<?php esc_html_e( 'برای استفاده از تمام ویجت‌ها و قالب‌های آماده، افزونه‌ی Elementor را نصب کنید.', 'glassmorphism-child-pro' ); ?>
			<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ); ?>" class="button button-primary" style="margin-right:8px;">
				<?php esc_html_e( 'نصب Elementor', 'glassmorphism-child-pro' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/* ────────────────────────────────────────
   Widget Areas (sidebar + footer)
   ──────────────────────────────────────── */
add_action( 'widgets_init', 'glass_pro_register_widget_areas' );
/**
 * ثبت widget area های قالب.
 *
 * @return void
 */
function glass_pro_register_widget_areas(): void {

	register_sidebar( [
		'name'          => __( 'سایدبار اصلی', 'glassmorphism-child-pro' ),
		'id'            => 'glass-pro-sidebar',
		'description'   => __( 'ویجت‌های نمایش‌داده‌شده در سایدبار اصلی قالب.', 'glassmorphism-child-pro' ),
		'before_widget' => '<section id="%1$s" class="widget glass-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title glass-widget-title">',
		'after_title'   => '</h3>',
	] );

	register_sidebar( [
		'name'          => __( 'فوتر — ستون ۱', 'glassmorphism-child-pro' ),
		'id'            => 'glass-pro-footer-1',
		'description'   => __( 'اولین ستون ویجت در فوتر.', 'glassmorphism-child-pro' ),
		'before_widget' => '<section id="%1$s" class="widget glass-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	] );

	register_sidebar( [
		'name'          => __( 'فوتر — ستون ۲', 'glassmorphism-child-pro' ),
		'id'            => 'glass-pro-footer-2',
		'description'   => __( 'دومین ستون ویجت در فوتر.', 'glassmorphism-child-pro' ),
		'before_widget' => '<section id="%1$s" class="widget glass-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	] );

	register_sidebar( [
		'name'          => __( 'فوتر — ستون ۳', 'glassmorphism-child-pro' ),
		'id'            => 'glass-pro-footer-3',
		'description'   => __( 'سومین ستون ویجت در فوتر.', 'glassmorphism-child-pro' ),
		'before_widget' => '<section id="%1$s" class="widget glass-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	] );
}

/* ────────────────────────────────────────
   [FIX v5.15.11] اتصال بومی تنظیمات تم از پیشخوان ادمین به مرورگر کاربران
   ──────────────────────────────────────── */
add_action( 'wp_head', 'glass_pro_print_theme_mode_settings', 1 );
function glass_pro_print_theme_mode_settings(): void {
	$hub_mode = get_option( 'glass_pro_default_theme_mode', '' );
	if ( empty( $hub_mode ) || 'system' === $hub_mode ) {
		$tm_default = get_theme_mod( 'glass_dark_mode_default', false );
		$tm_respect = get_theme_mod( 'glass_dark_mode_respect_system', true );
		if ( $tm_default ) {
			$hub_mode = 'dark';
		} elseif ( ! $tm_respect ) {
			$hub_mode = 'light';
		} else {
			$hub_mode = $hub_mode ?: 'system';
		}
	}
	$is_dark        = 'dark' === $hub_mode;
	$respect_system = 'system' === $hub_mode;
	?>
	<script <?php if ( function_exists( 'glass_pro_csp_nonce_attr' ) ) { glass_pro_csp_nonce_attr(); } ?>>
	window.glassSettings = window.glassSettings || {};
	window.glassSettings.darkModeDefault = <?php echo $is_dark ? 'true' : 'false'; ?>;
	window.glassSettings.respectSystemPreference = <?php echo $respect_system ? 'true' : 'false'; ?>;
	</script>
	<?php
}
