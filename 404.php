<?php
/**
 * 404 — Not Found Template (Standalone, Alborz Ghaleb)
 *
 * قالب مستقل برای صفحه‌ی «یافت نشد». بدون هیچ وابستگی به قالب مادر.
 *
 * @package Alborz_Ghaleb
 * @version 5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php do_action( 'glass_pro/before_main', '404' ); ?>
<main id="main-content" class="gl-404-wrap">
	<div class="gl-404-card">

		<div class="gl-404-badge" aria-hidden="true">
			<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="10"></circle>
				<line x1="12" y1="8" x2="12" y2="12"></line>
				<line x1="12" y1="16" x2="12.01" y2="16"></line>
			</svg>
		</div>

		<h1 class="gl-404-code">404</h1>
		<p class="gl-404-title"><?php esc_html_e( 'صفحه‌ای که دنبالش بودید پیدا نشد.', 'glassmorphism-child-pro' ); ?></p>
		<p class="gl-404-desc"><?php esc_html_e( 'ممکن است آدرس اشتباه باشد یا صفحه حذف شده باشد. می‌توانید جستجو کنید یا به صفحه‌ی اصلی برگردید.', 'glassmorphism-child-pro' ); ?></p>

		<div class="gl-404-search">
			<?php get_search_form(); ?>
		</div>

		<a class="glass-cta-btn glass-cta-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
			<span><?php esc_html_e( 'بازگشت به صفحه اصلی', 'glassmorphism-child-pro' ); ?></span>
		</a>

	</div>
</main>
<?php do_action( 'glass_pro/after_main', '404' ); ?>

<?php
get_footer();
