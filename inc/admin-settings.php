<?php
/**
 * Admin Settings Hub
 *
 * Central landing/settings page for operational features that do not belong in
 * the visual Customizer.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.8
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', 'glass_pro_settings_hub_menu' );
function glass_pro_settings_hub_menu(): void {
	add_menu_page(
		__( 'Alborz Ghaleb', 'glassmorphism-child-pro' ),
		__( 'Alborz Ghaleb', 'glassmorphism-child-pro' ),
		'manage_options',
		'glass-pro-settings',
		'glass_pro_settings_hub_page',
		'dashicons-admin-customizer',
		58
	);
}

add_action( 'admin_init', 'glass_pro_register_operational_settings' );
function glass_pro_register_operational_settings(): void {
	register_setting( 'glass_pro_operational', 'glass_pro_require_email_verification', [ 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false ] );
	register_setting( 'glass_pro_operational', 'glass_pro_enable_csp', [ 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false ] );
	register_setting( 'glass_pro_operational', 'glass_pro_notify_admin_payments', [ 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ] );
	register_setting( 'glass_pro_operational', 'glass_pro_notify_admin_ads', [ 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ] );
	register_setting( 'glass_pro_operational', 'glass_pro_default_theme_mode', [ 'type' => 'string', 'sanitize_callback' => 'sanitize_key', 'default' => 'system' ] );
	register_setting( 'glass_pro_operational', 'glass_pro_show_header_dark_toggle', [ 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ] );
	register_setting( 'glass_pro_operational', 'glass_pro_toc_enabled', [ 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ] );
	register_setting( 'glass_pro_operational', 'glass_pro_toc_on_posts', [ 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ] );
	register_setting( 'glass_pro_operational', 'glass_pro_toc_on_pages', [ 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false ] );
}

add_filter( 'theme_mod_glass_dark_mode_toggle_show', static function ( $val ) {
	$opt = get_option( 'glass_pro_show_header_dark_toggle', null );
	return null !== $opt && '' !== $opt ? (bool) $opt : $val;
} );

add_filter( 'glass_pro/register/require_email_verification', static function ( $enabled ) {
	return (bool) get_option( 'glass_pro_require_email_verification', $enabled );
} );
add_filter( 'glass_pro/csp/enabled', static function ( $enabled ) {
	return (bool) get_option( 'glass_pro_enable_csp', $enabled );
} );

function glass_pro_settings_hub_page(): void {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Alborz Ghaleb — تنظیمات عملیاتی', 'glassmorphism-child-pro' ); ?></h1>
		<p><?php esc_html_e( 'این صفحه برای تنظیمات امنیتی/عملیاتی است. تنظیمات ظاهری همچنان از Customizer انجام می‌شود.', 'glassmorphism-child-pro' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'glass_pro_operational' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'تأیید ایمیل ثبت‌نام', 'glassmorphism-child-pro' ); ?></th>
					<td><label><input type="checkbox" name="glass_pro_require_email_verification" value="1" <?php checked( get_option( 'glass_pro_require_email_verification', false ) ); ?>> <?php esc_html_e( 'کاربر قبل از ورود باید ایمیل خود را تأیید کند.', 'glassmorphism-child-pro' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Content Security Policy', 'glassmorphism-child-pro' ); ?></th>
					<td><label><input type="checkbox" name="glass_pro_enable_csp" value="1" <?php checked( get_option( 'glass_pro_enable_csp', false ) ); ?>> <?php esc_html_e( 'هدر CSP سازگار با کدهای legacy فعال شود.', 'glassmorphism-child-pro' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'اعلان پرداخت‌ها', 'glassmorphism-child-pro' ); ?></th>
					<td><label><input type="checkbox" name="glass_pro_notify_admin_payments" value="1" <?php checked( get_option( 'glass_pro_notify_admin_payments', true ) ); ?>> <?php esc_html_e( 'پس از پرداخت موفق به مدیر ایمیل ارسال شود.', 'glassmorphism-child-pro' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'اعلان آگهی جدید', 'glassmorphism-child-pro' ); ?></th>
					<td><label><input type="checkbox" name="glass_pro_notify_admin_ads" value="1" <?php checked( get_option( 'glass_pro_notify_admin_ads', true ) ); ?>> <?php esc_html_e( 'پس از ثبت آگهی جدید به مدیر ایمیل ارسال شود.', 'glassmorphism-child-pro' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'حالت پیش‌فرض نمایش قالب', 'glassmorphism-child-pro' ); ?></th>
					<td>
						<fieldset>
							<label style="margin-inline-end: 20px;"><input type="radio" name="glass_pro_default_theme_mode" value="system" <?php checked( get_option( 'glass_pro_default_theme_mode', 'system' ), 'system' ); ?>> 💻 <?php esc_html_e( 'خودکار هماهنگ با سیستم کاربر (OS Auto)', 'glassmorphism-child-pro' ); ?></label><br><br>
							<label style="margin-inline-end: 20px;"><input type="radio" name="glass_pro_default_theme_mode" value="dark" <?php checked( get_option( 'glass_pro_default_theme_mode', 'system' ), 'dark' ); ?>> 🌙 <?php esc_html_e( 'همیشه تاریک (Dark Mode)', 'glassmorphism-child-pro' ); ?></label><br><br>
							<label><input type="radio" name="glass_pro_default_theme_mode" value="light" <?php checked( get_option( 'glass_pro_default_theme_mode', 'system' ), 'light' ); ?>> ☀️ <?php esc_html_e( 'همیشه روشن (Light Mode)', 'glassmorphism-child-pro' ); ?></label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'دکمه هدر حالت نمایش', 'glassmorphism-child-pro' ); ?></th>
					<td><label><input type="checkbox" name="glass_pro_show_header_dark_toggle" value="1" <?php checked( get_option( 'glass_pro_show_header_dark_toggle', true ) ); ?>> <?php esc_html_e( 'دکمه‌ی سوئیچ خورشید/ماه در هدر سایت نمایش داده شود.', 'glassmorphism-child-pro' ); ?></label></td>
				</tr>
				<tr><td colspan="2"><hr><h2><?php esc_html_e( 'فهرست مطالب (TOC)', 'glassmorphism-child-pro' ); ?></h2></td></tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'فعال‌سازی فهرست مطالب', 'glassmorphism-child-pro' ); ?></th>
					<td><label><input type="checkbox" name="glass_pro_toc_enabled" value="1" <?php checked( get_option( 'glass_pro_toc_enabled', true ) ); ?>> <?php esc_html_e( 'فهرست مطالب در صفحات پشتیبانی‌شده نمایش داده شود.', 'glassmorphism-child-pro' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'نمایش در نوشته‌ها', 'glassmorphism-child-pro' ); ?></th>
					<td><label><input type="checkbox" name="glass_pro_toc_on_posts" value="1" <?php checked( get_option( 'glass_pro_toc_on_posts', true ) ); ?>> <?php esc_html_e( 'فهرست مطالب در صفحه تکی نوشته‌ها نمایش داده شود.', 'glassmorphism-child-pro' ); ?></label></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'نمایش در برگه‌ها', 'glassmorphism-child-pro' ); ?></th>
					<td><label><input type="checkbox" name="glass_pro_toc_on_pages" value="1" <?php checked( get_option( 'glass_pro_toc_on_pages', false ) ); ?>> <?php esc_html_e( 'فهرست مطالب در برگه‌های سفارشی نمایش داده شود.', 'glassmorphism-child-pro' ); ?></label></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<hr>
		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'themes.php?page=glass-pro-welcome' ) ); ?>"><?php esc_html_e( 'راهنما', 'glassmorphism-child-pro' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'tools.php?page=glass-pro-health' ) ); ?>"><?php esc_html_e( 'سلامت سیستم', 'glassmorphism-child-pro' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'tools.php?page=glass-pro-transactions' ) ); ?>"><?php esc_html_e( 'تراکنش‌ها', 'glassmorphism-child-pro' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'tools.php?page=glass-pro-export' ) ); ?>"><?php esc_html_e( 'Export/Import', 'glassmorphism-child-pro' ); ?></a>
		</p>
	</div>
	<?php
}
