<?php
/**
 * Welcome / Onboarding Wizard
 *
 * یک صفحه راهنما در ادمین که پس از فعال‌سازی قالب نمایش داده می‌شود
 * و کاربر را به تنظیمات اصلی هدایت می‌کند.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * در فعال‌سازی قالب، یک transient ست می‌کنیم تا welcome page redirect شود.
 */
add_action( 'after_switch_theme', 'glass_pro_welcome_activate' );
/**
 * @return void
 */
function glass_pro_welcome_activate(): void {
	set_transient( 'glass_pro_welcome_redirect', true, 30 );
}

/**
 * هدایت به welcome page بعد از فعال‌سازی.
 */
add_action( 'admin_init', 'glass_pro_welcome_redirect' );
/**
 * @return void
 */
function glass_pro_welcome_redirect(): void {
	if ( ! get_transient( 'glass_pro_welcome_redirect' ) ) {
		return;
	}
	if ( wp_doing_ajax() || is_network_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_transient( 'glass_pro_welcome_redirect' );
	wp_safe_redirect( admin_url( 'themes.php?page=glass-pro-welcome' ) );
	exit;
}

/**
 * افزودن صفحه welcome به منوی Appearance.
 */
add_action( 'admin_menu', 'glass_pro_welcome_menu' );
/**
 * @return void
 */
function glass_pro_welcome_menu(): void {
	add_theme_page(
		__( 'خوش‌آمدید به Alborz Ghaleb', 'glassmorphism-child-pro' ),
		__( 'راهنمای Alborz Ghaleb', 'glassmorphism-child-pro' ),
		'manage_options',
		'glass-pro-welcome',
		'glass_pro_welcome_page_render'
	);
}

/**
 * رندر صفحه welcome.
 *
 * @return void
 */
function glass_pro_welcome_page_render(): void {
	$theme = wp_get_theme();
	?>
	<div class="wrap glass-pro-welcome">
		<h1 style="font-size: 2em; margin: 1em 0;">
			🎨 <?php echo esc_html( $theme->get( 'Name' ) ); ?>
			<span style="font-size: 0.5em; color: #64748B; font-weight: normal;">v<?php echo esc_html( $theme->get( 'Version' ) ); ?></span>
		</h1>

		<p style="font-size: 1.1em; line-height: 1.7; max-width: 800px;">
			<?php esc_html_e( 'تشکر از انتخاب قالب! این راهنما به شما کمک می‌کند با چند کلیک ساده، قالب را برای سایت خود سفارشی کنید.', 'glassmorphism-child-pro' ); ?>
		</p>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 30px; max-width: 1200px;">

			<div class="glass-welcome-card" style="background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
				<div style="font-size: 2.5em; margin-bottom: 12px;">📞</div>
				<h2 style="margin-top: 0;"><?php esc_html_e( '۱. اطلاعات تماس', 'glassmorphism-child-pro' ); ?></h2>
				<p><?php esc_html_e( 'شماره تماس، شبکه‌های اجتماعی و اطلاعات برند را تنظیم کنید.', 'glassmorphism-child-pro' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=glass_pro_contact' ) ); ?>" class="button button-primary"><?php esc_html_e( 'تنظیم اطلاعات', 'glassmorphism-child-pro' ); ?></a>
			</div>

			<div class="glass-welcome-card" style="background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
				<div style="font-size: 2.5em; margin-bottom: 12px;">💳</div>
				<h2 style="margin-top: 0;"><?php esc_html_e( '۲. درگاه پرداخت', 'glassmorphism-child-pro' ); ?></h2>
				<p><?php esc_html_e( 'مرچنت زرین‌پال و قیمت آگهی‌ها را تنظیم کنید.', 'glassmorphism-child-pro' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=glass_pf_payment_section' ) ); ?>" class="button button-primary"><?php esc_html_e( 'تنظیم پرداخت', 'glassmorphism-child-pro' ); ?></a>
			</div>

			<div class="glass-welcome-card" style="background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
				<div style="font-size: 2.5em; margin-bottom: 12px;">👤</div>
				<h2 style="margin-top: 0;"><?php esc_html_e( '۳. پنل کاربری', 'glassmorphism-child-pro' ); ?></h2>
				<p><?php esc_html_e( 'مدت اعتبار آگهی، تمدید و سایر تنظیمات کاربر.', 'glassmorphism-child-pro' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=glass_user_panel_section' ) ); ?>" class="button button-primary"><?php esc_html_e( 'تنظیم پنل', 'glassmorphism-child-pro' ); ?></a>
			</div>

			<div class="glass-welcome-card" style="background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
				<div style="font-size: 2.5em; margin-bottom: 12px;">🔗</div>
				<h2 style="margin-top: 0;"><?php esc_html_e( '۴. منوها', 'glassmorphism-child-pro' ); ?></h2>
				<p><?php esc_html_e( 'منوهای اصلی، فوتر و فارسی/انگلیسی را تنظیم کنید.', 'glassmorphism-child-pro' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="button button-primary"><?php esc_html_e( 'مدیریت منوها', 'glassmorphism-child-pro' ); ?></a>
			</div>

			<div class="glass-welcome-card" style="background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
				<div style="font-size: 2.5em; margin-bottom: 12px;">🧩</div>
				<h2 style="margin-top: 0;"><?php esc_html_e( '۵. ویجت‌ها', 'glassmorphism-child-pro' ); ?></h2>
				<p><?php esc_html_e( 'سایدبار و ۳ ستون فوتر را با ویجت‌ها پر کنید.', 'glassmorphism-child-pro' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>" class="button button-primary"><?php esc_html_e( 'مدیریت ویجت‌ها', 'glassmorphism-child-pro' ); ?></a>
			</div>

			<div class="glass-welcome-card" style="background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
				<div style="font-size: 2.5em; margin-bottom: 12px;">⚙️</div>
				<h2 style="margin-top: 0;"><?php esc_html_e( '۶. پیوندهای یکتا', 'glassmorphism-child-pro' ); ?></h2>
				<p><?php esc_html_e( 'حتماً یک بار به این صفحه بروید و ذخیره کنید (rewrite refresh).', 'glassmorphism-child-pro' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>" class="button button-primary"><?php esc_html_e( 'تنظیم پیوندها', 'glassmorphism-child-pro' ); ?></a>
			</div>

		</div>

		<hr style="margin: 40px 0;">

		<h2><?php esc_html_e( 'افزونه‌های پیشنهادی (اختیاری)', 'glassmorphism-child-pro' ); ?></h2>
		<ul style="line-height: 1.8; font-size: 1.05em;">
			<li><strong>Elementor</strong> — برای ویرایش بصری صفحات + ۳ ویجت شیشه‌ای سفارشی</li>
			<li><strong>Polylang</strong> — برای پشتیبانی چندزبانه (۶ زبان آماده)</li>
			<li><strong>Rank Math</strong> یا <strong>Yoast SEO</strong> — برای SEO پیشرفته (قالب schema را خودکار غیرفعال می‌کند)</li>
		</ul>

		<hr style="margin: 40px 0;">

		<h2><?php esc_html_e( 'ویژگی‌های فعال', 'glassmorphism-child-pro' ); ?></h2>
		<table class="widefat striped" style="max-width: 800px;">
			<tbody>
				<tr><td>✅ Mobile Performance Optimization</td><td><?php esc_html_e( 'فعال', 'glassmorphism-child-pro' ); ?></td></tr>
				<tr><td>✅ Dark Mode (بدون FOUC)</td><td><?php esc_html_e( 'فعال', 'glassmorphism-child-pro' ); ?></td></tr>
				<tr><td>✅ Bot Detection برای شمارش بازدید</td><td><?php esc_html_e( 'فعال', 'glassmorphism-child-pro' ); ?></td></tr>
				<tr><td>✅ Rate-limit (لاگین/ثبت‌نام/فراموشی)</td><td><?php esc_html_e( 'فعال', 'glassmorphism-child-pro' ); ?></td></tr>
				<tr><td>✅ Captcha + Honeypot</td><td><?php esc_html_e( 'فعال', 'glassmorphism-child-pro' ); ?></td></tr>
				<tr><td>✅ Zarinpal با SSL Verify</td><td><?php esc_html_e( 'فعال (نیاز به مرچنت)', 'glassmorphism-child-pro' ); ?></td></tr>
				<tr><td>✅ PWA Manifest</td><td><?php esc_html_e( 'فعال', 'glassmorphism-child-pro' ); ?></td></tr>
				<tr><td>✅ WebP/AVIF Support</td><td><?php esc_html_e( 'فعال', 'glassmorphism-child-pro' ); ?></td></tr>
				<tr><td>⚙️ Email Verification</td><td><?php esc_html_e( 'با فیلتر فعال می‌شود', 'glassmorphism-child-pro' ); ?></td></tr>
				<tr><td>⚙️ CSP Header</td><td><?php esc_html_e( 'با فیلتر فعال می‌شود', 'glassmorphism-child-pro' ); ?></td></tr>
			</tbody>
		</table>

		<p style="margin-top: 40px; padding: 16px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; color: #92400e;">
			💡 <?php esc_html_e( 'این صفحه را می‌توانید هر زمان از طریق نمایش → راهنمای Alborz Ghaleb دوباره ببینید.', 'glassmorphism-child-pro' ); ?>
		</p>
	</div>
	<?php
}
