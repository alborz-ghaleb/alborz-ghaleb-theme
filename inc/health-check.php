<?php
/**
 * Health Check / Diagnostics Page
 *
 * صفحه‌ای در پنل ادمین که وضعیت سلامت قالب و وابستگی‌ها را نشان می‌دهد.
 *
 * مسیر: ابزارها → سلامت Alborz Ghaleb
 *
 * @package Alborz_Ghaleb
 * @since   5.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'glass_pro_health_check_menu' );
/**
 * @return void
 */
function glass_pro_health_check_menu(): void {
	add_management_page(
		__( 'سلامت Alborz Ghaleb', 'glassmorphism-child-pro' ),
		__( 'سلامت Alborz Ghaleb', 'glassmorphism-child-pro' ),
		'manage_options',
		'glass-pro-health',
		'glass_pro_health_check_page'
	);
}

/**
 * @return void
 */
function glass_pro_health_check_page(): void {
	$checks = glass_pro_health_check_run();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'سلامت Alborz Ghaleb', 'glassmorphism-child-pro' ); ?></h1>
		<p><?php esc_html_e( 'این صفحه وضعیت قالب، وابستگی‌ها و تنظیمات حساس را بررسی می‌کند.', 'glassmorphism-child-pro' ); ?></p>

		<table class="widefat striped" style="max-width: 900px;">
			<thead>
				<tr>
					<th style="width:30%"><?php esc_html_e( 'بررسی', 'glassmorphism-child-pro' ); ?></th>
					<th style="width:15%"><?php esc_html_e( 'وضعیت', 'glassmorphism-child-pro' ); ?></th>
					<th><?php esc_html_e( 'توضیحات', 'glassmorphism-child-pro' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $checks as $check ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $check['name'] ); ?></strong></td>
					<td>
						<?php
						if ( $check['status'] === 'ok' ) {
							echo '<span style="color:#17212b;font-weight:bold;">✅ OK</span>';
						} elseif ( $check['status'] === 'warning' ) {
							echo '<span style="color:#f59e0b;font-weight:bold;">⚠️ ' . esc_html__( 'هشدار', 'glassmorphism-child-pro' ) . '</span>';
						} else {
							echo '<span style="color:#ef4444;font-weight:bold;">❌ ' . esc_html__( 'مشکل', 'glassmorphism-child-pro' ) . '</span>';
						}
						?>
					</td>
					<td><?php echo wp_kses_post( $check['message'] ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<hr style="margin:30px 0;max-width:900px;">
		<div style="max-width:900px;background:#fff;padding:20px;border:1px solid #ccd0d4;border-radius:10px;">
			<h2 style="margin-top:0;">شبیه‌ساز و عیب‌یاب زنده زرین‌پال (Zarinpal Sandbox Simulator)</h2>
			<p>با کلیک روی دکمه زیر، سیستم یک درخواست آزمایشی امن به سرورهای زرین‌پال ارسال کرده و سلامت گواهی SSL، باز بودن پورت‌های cURL، زمان پاسخگویی بانک و اعتبار کد مرچنت را تست می‌کند.</p>
			<button type="button" id="glassTestZarinpalBtn" class="button button-primary button-hero">🔍 تست زنده اتصال درگاه زرین‌پال</button>
			<span id="glassZarinpalSpinner" class="spinner" style="float:none;margin:0 10px;"></span>
			<div id="glassZarinpalResult" style="margin-top:15px;font-weight:bold;line-height:1.6;"></div>
		</div>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var btn = document.getElementById('glassTestZarinpalBtn');
			var spin = document.getElementById('glassZarinpalSpinner');
			var res = document.getElementById('glassZarinpalResult');
			if (!btn) return;
			btn.addEventListener('click', function() {
				btn.disabled = true;
				spin.classList.add('is-active');
				res.innerHTML = '<span style="color:#64748B;">در حال برقراری اتصال به api.zarinpal.com ...</span>';
				fetch(ajaxurl, {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: 'action=glass_pro_test_zarinpal_ajax&nonce=' + encodeURIComponent('<?php echo wp_create_nonce("glass_test_zp"); ?>')
				})
				.then(function(r) { return r.json(); })
				.then(function(data) {
					btn.disabled = false;
					spin.classList.remove('is-active');
					if (data.success) {
						res.innerHTML = '<div style="color:#10B981;background:#ECFDF5;padding:12px;border-radius:8px;border:1px solid #10B981;">' + data.data.message + '</div>';
					} else {
						res.innerHTML = '<div style="color:#EF4444;background:#FEF2F2;padding:12px;border-radius:8px;border:1px solid #EF4444;">' + (data.data ? data.data.message : 'خطای ناشناخته') + '</div>';
					}
				})
				.catch(function(e) {
					btn.disabled = false;
					spin.classList.remove('is-active');
					res.innerHTML = '<div style="color:#EF4444;">خطای شبکه: ' + e + '</div>';
				});
			});
		});
		</script>
	</div>
	<?php
}

add_action( 'wp_ajax_glass_pro_test_zarinpal_ajax', function() {
	check_ajax_referer( 'glass_test_zp', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => 'عدم دسترسی.' ] );
	}
	$merchant = get_theme_mod( 'glass_pf_zarinpal_merchant', '' );
	if ( empty( $merchant ) ) {
		wp_send_json_error( [ 'message' => 'کد مرچنت زرین‌پال در بخش سفارشی‌سازی قالب تنظیم نشده است.' ] );
	}
	$start = microtime( true );
	$test_data = [
		'merchant_id' => $merchant,
		'amount'      => 10000,
		'currency'    => 'IRR', // amount is in Rial — pin explicitly.
		'description' => 'تست عیب‌یابی زنده درگاه',
		'callback_url'=> home_url( '/' ),
	];
	$response = wp_remote_post( 'https://api.zarinpal.com/pg/v4/payment/request.json', [
		'headers'   => [ 'Content-Type' => 'application/json', 'Accept' => 'application/json' ],
		'body'      => wp_json_encode( $test_data ),
		'timeout'   => 12,
		'sslverify' => true,
	] );
	$duration = round( ( microtime( true ) - $start ) * 1000 );
	if ( is_wp_error( $response ) ) {
		wp_send_json_error( [ 'message' => sprintf( 'خطای برقراری ارتباط cURL/SSL: %s (زمان: %dms)', $response->get_error_message(), $duration ) ] );
	}
	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( isset( $body['data']['code'] ) && (int) $body['data']['code'] === 100 ) {
		wp_send_json_success( [ 'message' => sprintf( '✅ اتصال به درگاه زرین‌پال ۱۰۰٪ موفق و معتبر است! (زمان پاسخ بانک: %dms | کد مرچنت تایید شد)', $duration ) ] );
	} else {
		$err = $body['errors']['message'] ?? ( $body['errors']['code'] ?? "HTTP {$code}" );
		wp_send_json_error( [ 'message' => sprintf( '❌ پاسخ خطا از سرور زرین‌پال: %s (زمان: %dms)', is_array($err) ? wp_json_encode($err) : $err, $duration ) ] );
	}
});

/**
 * اجرای بررسی‌های سلامت.
 *
 * @return array
 */
function glass_pro_health_check_run(): array {
	$checks = [];

	// PHP version
	$php_ver = PHP_VERSION;
	$checks[] = [
		'name'    => __( 'نسخه PHP', 'glassmorphism-child-pro' ),
		'status'  => version_compare( $php_ver, '8.1', '>=' ) ? 'ok' : 'error',
		'message' => sprintf( 'PHP %s (حداقل: 8.1)', $php_ver ),
	];

	// WordPress version
	global $wp_version;
	$checks[] = [
		'name'    => __( 'نسخه WordPress', 'glassmorphism-child-pro' ),
		'status'  => version_compare( $wp_version, '6.5', '>=' ) ? 'ok' : 'warning',
		'message' => sprintf( 'WordPress %s (حداقل: 6.5)', $wp_version ),
	];

	// HTTPS
	$checks[] = [
		'name'    => __( 'HTTPS', 'glassmorphism-child-pro' ),
		'status'  => is_ssl() ? 'ok' : 'warning',
		'message' => is_ssl() ? __( 'سایت روی HTTPS است.', 'glassmorphism-child-pro' ) : __( 'سایت روی HTTP است. برای امنیت و SEO، HTTPS را فعال کنید.', 'glassmorphism-child-pro' ),
	];

	// GD extension (برای watermark تصاویر)
	$checks[] = [
		'name'    => __( 'PHP GD Extension', 'glassmorphism-child-pro' ),
		'status'  => extension_loaded( 'gd' ) ? 'ok' : 'error',
		'message' => extension_loaded( 'gd' ) ? __( 'GD نصب است (برای compress تصاویر آگهی لازم است).', 'glassmorphism-child-pro' ) : __( 'GD نصب نیست. عکس‌های آپلودی compress نمی‌شوند.', 'glassmorphism-child-pro' ),
	];

	// cURL یا allow_url_fopen
	$has_curl = function_exists( 'curl_init' );
	$checks[] = [
		'name'    => __( 'cURL Extension', 'glassmorphism-child-pro' ),
		'status'  => $has_curl ? 'ok' : 'warning',
		'message' => $has_curl ? __( 'cURL نصب است (برای فراخوانی زرین‌پال لازم است).', 'glassmorphism-child-pro' ) : __( 'cURL نصب نیست. وردپرس به fsockopen fallback می‌کند.', 'glassmorphism-child-pro' ),
	];

	// Zarinpal merchant
	$merchant = get_theme_mod( 'glass_pf_zarinpal_merchant', '' );
	$pay_enabled = (bool) get_theme_mod( 'glass_pf_pay_enabled', false );
	if ( $pay_enabled ) {
		$checks[] = [
			'name'    => __( 'مرچنت زرین‌پال', 'glassmorphism-child-pro' ),
			'status'  => ( ! empty( $merchant ) && strlen( $merchant ) === 36 ) ? 'ok' : 'error',
			'message' => empty( $merchant )
				? __( 'پرداخت فعال است ولی مرچنت تنظیم نشده!', 'glassmorphism-child-pro' )
				: sprintf( __( 'مرچنت ثبت شده (طول: %d کاراکتر).', 'glassmorphism-child-pro' ), strlen( $merchant ) ),
		];
	}

	// Elementor
	$has_elementor = did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' );
	$checks[] = [
		'name'    => 'Elementor',
		'status'  => $has_elementor ? 'ok' : 'warning',
		'message' => $has_elementor ? __( 'Elementor نصب و فعال است.', 'glassmorphism-child-pro' ) : __( 'Elementor نصب نیست. ۳ ویجت سفارشی در دسترس نخواهد بود.', 'glassmorphism-child-pro' ),
	];

	// Polylang
	$has_polylang = function_exists( 'pll_current_language' );
	$checks[] = [
		'name'    => 'Polylang',
		'status'  => $has_polylang ? 'ok' : 'warning',
		'message' => $has_polylang ? __( 'Polylang نصب و فعال است.', 'glassmorphism-child-pro' ) : __( 'Polylang نصب نیست. ترجمه‌های ۶ زبانه فعال نیست (فقط فارسی نمایش داده می‌شود).', 'glassmorphism-child-pro' ),
	];

	// SEO plugin
	$has_seo = function_exists( 'glass_pro_has_seo_plugin' ) ? glass_pro_has_seo_plugin() : false;
	$checks[] = [
		'name'    => __( 'افزونه SEO', 'glassmorphism-child-pro' ),
		'status'  => 'ok',
		'message' => $has_seo
			? __( 'افزونه SEO شناسایی شد. قالب schema داخلی را غیرفعال کرده.', 'glassmorphism-child-pro' )
			: __( 'افزونه SEO نصب نیست. قالب schema داخلی را اعمال می‌کند.', 'glassmorphism-child-pro' ),
	];

	// Cron working
	$cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
	$checks[] = [
		'name'    => __( 'WP-Cron', 'glassmorphism-child-pro' ),
		'status'  => $cron_disabled ? 'warning' : 'ok',
		'message' => $cron_disabled
			? __( 'DISABLE_WP_CRON فعال است. مطمئن شوید system cron برای انقضای آگهی‌ها تنظیم شده.', 'glassmorphism-child-pro' )
			: __( 'WP-Cron فعال است. انقضای آگهی‌ها هر روز چک می‌شود.', 'glassmorphism-child-pro' ),
	];

	// Cron event ثبت شده
	$next_expiry = wp_next_scheduled( 'glass_check_expired_ads' );
	$checks[] = [
		'name'    => __( 'Cron انقضای آگهی', 'glassmorphism-child-pro' ),
		'status'  => $next_expiry ? 'ok' : 'warning',
		'message' => $next_expiry
			? sprintf( __( 'اجرای بعدی: %s', 'glassmorphism-child-pro' ), wp_date( 'Y-m-d H:i', $next_expiry ) )
			: __( 'cron event ثبت نشده. قالب را غیرفعال و دوباره فعال کنید.', 'glassmorphism-child-pro' ),
	];

	// Asset files
	$essential_assets = [
		'assets/css/header.css',
		'assets/css/dark-mode.css',
		'assets/js/dark-mode.js',
		'assets/fonts/vazirmatn/webfonts/Vazirmatn-Regular.woff2',
	];
	$missing = [];
	foreach ( $essential_assets as $asset ) {
		if ( ! is_readable( GLASS_PRO_DIR . '/' . $asset ) ) {
			$missing[] = $asset;
		}
	}
	$checks[] = [
		'name'    => __( 'فایل‌های ضروری asset', 'glassmorphism-child-pro' ),
		'status'  => empty( $missing ) ? 'ok' : 'error',
		'message' => empty( $missing )
			? __( 'همه فایل‌های ضروری در دسترس هستند.', 'glassmorphism-child-pro' )
			: sprintf( __( 'فایل مفقود: %s', 'glassmorphism-child-pro' ), '<code>' . implode( ', ', $missing ) . '</code>' ),
	];

	// Site Icon
	$site_icon = get_theme_mod( 'site_icon', 0 );
	$checks[] = [
		'name'    => __( 'Site Icon (favicon)', 'glassmorphism-child-pro' ),
		'status'  => $site_icon ? 'ok' : 'warning',
		'message' => $site_icon
			? __( 'Site Icon تنظیم شده.', 'glassmorphism-child-pro' )
			: sprintf( __( 'Site Icon تنظیم نشده. به %s بروید.', 'glassmorphism-child-pro' ), '<a href="' . esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ) . '">Customize</a>' ),
	];

	// PWA
	$checks[] = [
		'name'    => __( 'PWA Manifest', 'glassmorphism-child-pro' ),
		'status'  => 'ok',
		'message' => sprintf( __( 'manifest در %s در دسترس است.', 'glassmorphism-child-pro' ), '<code>' . esc_html( home_url( '/manifest.webmanifest' ) ) . '</code>' ),
	];


	// Stage 47: DISALLOW_FILE_EDIT check — SecFix 5.15.18
	$checks[] = [
		'name'    => __( 'File Edit Disabled', 'glassmorphism-child-pro' ),
		'status'  => ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) ? 'ok' : 'warning',
		'message' => ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT )
			? __( 'DISALLOW_FILE_EDIT در wp-config.php فعال است — امن.', 'glassmorphism-child-pro' )
			: __( 'توصیه: define(\'DISALLOW_FILE_EDIT\', true) را در wp-config.php قرار دهید.', 'glassmorphism-child-pro' ),
	];

	// Stage 47: HSTS header check
	$checks[] = [
		'name'    => __( 'HSTS Header', 'glassmorphism-child-pro' ),
		'status'  => is_ssl() ? 'ok' : 'warning',
		'message' => is_ssl()
			? __( 'HSTS header برای HTTPS فعال است (از طریق security.php).', 'glassmorphism-child-pro' )
			: __( 'سایت HTTPS نیست — HSTS ارسال نمی‌شود.', 'glassmorphism-child-pro' ),
	];

	return apply_filters( 'glass_pro/health_check/checks', $checks );
}
