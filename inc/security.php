<?php
/**
 * Security Hardening
 *
 * هدرهای امنیتی، حذف نسخه‌ی وردپرس، غیرفعال‌سازی xmlrpc،
 * محدودسازی ساده‌ی تلاش‌های ورود، غیرفعال‌سازی ویرایش فایل از پنل.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   هدرهای امنیتی (حفظ‌شده + تکمیل)
   ──────────────────────────────────────── */
add_action( 'send_headers', 'glass_pro_security_headers' );
/**
 * ارسال هدرهای امنیتی پایه.
 *
 * @return void
 */
function glass_pro_security_headers(): void {
	if ( headers_sent() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );

	// HSTS (Strict-Transport-Security) — فقط روی HTTPS ارسال شود
	if ( is_ssl() ) {
		$hsts_max_age = (int) apply_filters( 'glass_pro/hsts/max_age', 31536000 ); // پیش‌فرض ۱ سال
		$hsts_include_subdomains = (bool) apply_filters( 'glass_pro/hsts/include_subdomains', true );
		$hsts_preload = (bool) apply_filters( 'glass_pro/hsts/preload', false );

		$hsts_value = 'max-age=' . $hsts_max_age;
		if ( $hsts_include_subdomains ) {
			$hsts_value .= '; includeSubDomains';
		}
		if ( $hsts_preload ) {
			$hsts_value .= '; preload';
		}
		header( 'Strict-Transport-Security: ' . $hsts_value );
	}
}

/* ────────────────────────────────────────
   پاکسازی <head> (حفظ‌شده)
   ──────────────────────────────────────── */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

/* حذف نسخه از لینک assetها (جلوگیری از افشای نسخه‌ی WP/افزونه‌ها) */
add_filter( 'style_loader_src', 'glass_pro_remove_wp_ver', 9999 );
add_filter( 'script_loader_src', 'glass_pro_remove_wp_ver', 9999 );
/**
 * حذف پارامتر ?ver= هنگامی که مقدارش دقیقاً نسخه‌ی هسته‌ی وردپرس است.
 *
 * @param string $src آدرس asset.
 * @return string
 */
function glass_pro_remove_wp_ver( $src ): string {
	global $wp_version;
	if ( $src && false !== strpos( $src, 'ver=' . $wp_version ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}

/* ────────────────────────────────────────
   غیرفعال‌سازی xmlrpc (حفظ‌شده)
   ──────────────────────────────────────── */
add_filter( 'xmlrpc_enabled', '__return_false' );

/* ────────────────────────────────────────
   غیرفعال‌سازی ویرایش فایل از پیشخوان
   [SEC-FIX v5.15.21] حذف define از runtime تم – باید در wp-config.php باشد
   ──────────────────────────────────────── */
add_action( 'admin_notices', 'glass_pro_file_edit_security_notice' );
/**
 * هشدار اگر DISALLOW_FILE_EDIT فعال نیست.
 */
function glass_pro_file_edit_security_notice(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
		return;
	}
	echo '<div class="notice notice-warning"><p>';
	echo esc_html__( 'توصیه امنیتی Alborz Ghaleb: لطفاً define(\'DISALLOW_FILE_EDIT\', true) را در wp-config.php قرار دهید.', 'glassmorphism-child-pro' );
	echo '</p></div>';
}

// Health check integration
add_filter( 'site_status_tests', function( $tests ) {
	$tests['direct']['glass_pro_file_editor'] = [
		'label' => __( 'Glass PRO – File Editor', 'glassmorphism-child-pro' ),
		'test'  => 'glass_pro_health_file_editor_test',
	];
	return $tests;
});
function glass_pro_health_file_editor_test() {
	$result = [
		'label'       => __( 'ویرایشگر فایل غیرفعال است', 'glassmorphism-child-pro' ),
		'status'      => 'good',
		'badge'       => [ 'label' => __( 'امنیت', 'glassmorphism-child-pro' ), 'color' => 'green' ],
		'description' => '',
		'actions'     => '',
		'test'        => 'glass_pro_file_editor',
	];
	if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
		$result['status'] = 'recommended';
		$result['label']  = __( 'ویرایشگر فایل در پیشخوان فعال است', 'glassmorphism-child-pro' );
		$result['description'] = '<p>' . esc_html__( 'برای امنیت بیشتر، ویرایش فایل‌ها از پیشخوان را در wp-config.php غیرفعال کنید.', 'glassmorphism-child-pro' ) . '</p>';
	}
	return $result;
}

/* ────────────────────────────────────────
   محدودسازی ساده‌ی تلاش‌های ورود (Brute-force mitigation)
   - شمارش بر اساس IP در Transient.
   - پس از سقف مجاز، ورود موقتاً مسدود می‌شود.
   ──────────────────────────────────────── */

/**
 * دریافت IP کاربر جاری به‌صورت ایمن (در پشت پروکسی هم درست عمل می‌کند).
 * [SEC-FIX v5.15.21] Trusted proxy hardening
 *
 * @return string
 */
function glass_pro_get_client_ip(): string {
	$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

	// Trusted proxy check – فقط اگر REMOTE_ADDR در لیست trusted باشد، هدرهای forwarded قابل اعتمادند
	$trusted_proxies = (array) apply_filters( 'glass_pro/trusted_proxies', [] );
	$is_trusted = empty( $trusted_proxies ) ? false : in_array( $remote_addr, $trusted_proxies, true );

	// Cloudflare / proxy headers – فقط وقتی trusted
	$ip = $remote_addr;
	if ( $is_trusted ) {
		$candidates = [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_REAL_IP',
			'HTTP_X_FORWARDED_FOR',
		];
		foreach ( $candidates as $hdr ) {
			if ( ! empty( $_SERVER[ $hdr ] ) ) {
				// X-Forwarded-For ممکن است لیست باشد: client, proxy1, proxy2
				$raw = sanitize_text_field( wp_unslash( $_SERVER[ $hdr ] ) );
				$first = trim( explode( ',', $raw )[0] );
				if ( filter_var( $first, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) || filter_var( $first, FILTER_VALIDATE_IP ) ) {
					$ip = $first;
					break;
				}
			}
		}
	}

	/**
	 * فیلتر برای override کردن IP (مثلاً پشت Cloudflare از CF-Connecting-IP).
	 *
	 * @param string $ip
	 * @param string $remote_addr
	 * @param bool   $is_trusted
	 */
	$filtered_ip = (string) apply_filters( 'glass_pro/client_ip', $ip, $remote_addr, $is_trusted );
	return filter_var( $filtered_ip, FILTER_VALIDATE_IP ) ? $filtered_ip : $ip;
}

/**
 * تولید کلید Transient امن برای rate-limiting بر اساس IP و نوع عملیات.
 *
 * @param string $action نوع عمل (login, register, lostpass, ...).
 * @return string
 */
function glass_pro_rate_limit_key( string $action = 'login' ): string {
	$ip   = glass_pro_get_client_ip();
	$slug = preg_replace( '/[^a-z0-9_]/i', '', (string) $action );
	// hash( 'sha256', ... ) جایگزین md5() — هم استاندارد، هم WPCS clean.
	return 'glass_pro_rl_' . $slug . '_' . substr( hash( 'sha256', $ip ), 0, 32 );
}

/**
 * کلید Transient شمارنده‌ی تلاش‌های ورود برای IP جاری (سازگاری عقب‌رو).
 *
 * @return string
 */
function glass_pro_login_attempts_key(): string {
	return glass_pro_rate_limit_key( 'login' );
}

/**
 * بررسی اینکه آیا IP جاری از سقف تلاش‌ها برای یک عمل خاص عبور کرده یا نه.
 *
 * @param string $action     نوع عمل (login, register, lostpass).
 * @param int    $max        حداکثر تلاش مجاز.
 * @param int    $window_sec پنجره‌ی زمانی (ثانیه).
 * @return bool true یعنی مسدود است.
 */
function glass_pro_rate_limit_is_blocked( string $action, int $max = 5, int $window_sec = 900 ): bool {
	$max = (int) apply_filters( 'glass_pro/rate_limit_max_' . $action, $max );
	if ( $max <= 0 ) {
		return false;
	}
	$attempts = (int) get_transient( glass_pro_rate_limit_key( $action ) );
	return $attempts >= $max;
}

/**
 * افزایش شمارنده‌ی تلاش‌های یک عمل خاص برای IP جاری.
 *
 * @param string $action     نوع عمل.
 * @param int    $window_sec پنجره‌ی زمانی (ثانیه).
 * @return void
 */
function glass_pro_rate_limit_hit( string $action, int $window_sec = 900 ): void {
	$key      = glass_pro_rate_limit_key( $action );
	$attempts = (int) get_transient( $key );
	set_transient( $key, $attempts + 1, (int) $window_sec );
}

/**
 * پاکسازی شمارنده‌ی یک عمل (پس از موفقیت).
 *
 * @param string $action نوع عمل.
 * @return void
 */
function glass_pro_rate_limit_clear( string $action ): void {
	delete_transient( glass_pro_rate_limit_key( $action ) );
}

add_filter( 'authenticate', 'glass_pro_check_login_attempts', 30, 1 );
/**
 * مسدودسازی ورود در صورت عبور از سقف تلاش‌ها.
 *
 * @param WP_User|WP_Error|null $user نتیجه‌ی احراز هویت تا این مرحله.
 * @return WP_User|WP_Error|null
 */
function glass_pro_check_login_attempts( $user ) {
	$max = (int) apply_filters( 'glass_pro/login_max_attempts', 8 );
	if ( $max <= 0 ) {
		return $user;
	}
	$attempts = (int) get_transient( glass_pro_login_attempts_key() );
	if ( $attempts >= $max ) {
		return new WP_Error(
			'glass_pro_too_many',
			__( 'تعداد تلاش‌های ناموفق بیش از حد مجاز است. لطفاً چند دقیقه بعد دوباره تلاش کنید.', 'glassmorphism-child-pro' )
		);
	}
	return $user;
}

add_action( 'wp_login_failed', 'glass_pro_increment_login_attempts' );
/**
 * افزایش شمارنده پس از ورود ناموفق.
 *
 * @return void
 */
function glass_pro_increment_login_attempts(): void {
	glass_pro_rate_limit_hit( 'login', 15 * MINUTE_IN_SECONDS );
}

add_action( 'wp_login', 'glass_pro_clear_login_attempts' );
/**
 * پاکسازی شمارنده پس از ورود موفق.
 *
 * @return void
 */
function glass_pro_clear_login_attempts(): void {
	glass_pro_rate_limit_clear( 'login' );
}
