<?php
/**
 * Email Verification System
 *
 * توابع برای تایید ایمیل کاربر پس از ثبت‌نام.
 *
 * فعال‌سازی:
 *   add_filter( 'glass_pro/register/require_email_verification', '__return_true' );
 *
 * @package Alborz_Ghaleb
 * @since   5.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'glass_pro_generate_verification_token' ) ) :
	/**
	 * تولید توکن امضاشده برای تایید ایمیل.
	 *
	 * @param int $user_id
	 * @return string فرمت: timestamp:hash
	 */
	function glass_pro_generate_verification_token( int $user_id ): string {
		$timestamp = time();
		$secret    = wp_hash( $user_id . '|' . $timestamp . '|glass_pro_verify' );
		return $timestamp . ':' . $secret;
	}
endif;

if ( ! function_exists( 'glass_pro_verify_token' ) ) :
	/**
	 * تأیید توکن (با محدودیت زمانی ۷۲ ساعت).
	 *
	 * @param int    $user_id
	 * @param string $token
	 * @return bool
	 */
	function glass_pro_verify_token( int $user_id, string $token ): bool {
		if ( strpos( $token, ':' ) === false ) {
			return false;
		}
		list( $timestamp, $hash ) = explode( ':', $token, 2 );
		$timestamp = (int) $timestamp;

		// Stage 44: انقضا از ۷۲ ساعت به ۲۴ ساعت برای امنیت بیشتر — قابل override via filter
		$max_age = (int) apply_filters( 'glass_pro/verify/max_age', 24 * HOUR_IN_SECONDS );
		if ( ( time() - $timestamp ) > $max_age ) {
			return false;
		}

		$expected = wp_hash( $user_id . '|' . $timestamp . '|glass_pro_verify' );
		return hash_equals( $expected, $hash );
	}
endif;

if ( ! function_exists( 'glass_pro_send_verification_email' ) ) :
	/**
	 * ارسال ایمیل تایید به کاربر.
	 *
	 * @param int $user_id
	 * @return bool true در صورت موفقیت
	 */
	function glass_pro_send_verification_email( int $user_id ): bool {
		$user = get_userdata( $user_id );
		if ( ! $user || empty( $user->user_email ) ) {
			return false;
		}

		$token       = glass_pro_generate_verification_token( $user_id );
		$verify_url  = add_query_arg( [
			'glass_pro_verify' => 1,
			'uid'              => $user_id,
			'token'            => $token,
		], home_url( '/' ) );

		$site_name   = get_bloginfo( 'name' );
		$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );

		/* translators: 1: site name, 2: verify URL */
		$message = sprintf(
			__( "سلام %1\$s،\n\nخوش آمدید به %2\$s.\nبرای فعال‌سازی حساب کاربری، روی لینک زیر کلیک کنید (تا ۲۴ ساعت معتبر):\n\n%3\$s\n\nاگر این درخواست از طرف شما نبود، این ایمیل را نادیده بگیرید.", 'glassmorphism-child-pro' ),
			$user->display_name ?: $user->user_login,
			$site_name,
			$verify_url
		);

		/* translators: %s: site name */
		$subject = sprintf( __( 'فعال‌سازی حساب کاربری — %s', 'glassmorphism-child-pro' ), $site_name );

		$from_email = apply_filters(
			'glass_pro/mail/from_address',
			'noreply@' . preg_replace( '/^www\./i', '', (string) $site_domain )
		);
		$headers = [
			'Content-Type: text/plain; charset=UTF-8',
			sprintf( 'From: %s <%s>', $site_name, $from_email ),
		];

		return wp_mail( $user->user_email, $subject, $message, $headers );
	}
endif;

/* ────────────────────────────────────────
   شنود لینک تایید
   ──────────────────────────────────────── */
add_action( 'template_redirect', 'glass_pro_handle_email_verification', 5 );
/**
 * پردازش کلیک کاربر روی لینک تایید ایمیل.
 *
 * @return void
 */
function glass_pro_handle_email_verification() {
	if ( ! isset( $_GET['glass_pro_verify'], $_GET['uid'], $_GET['token'] ) ) {
		return;
	}

	$user_id = absint( wp_unslash( $_GET['uid'] ) );
	$token   = sanitize_text_field( wp_unslash( $_GET['token'] ) );

	if ( ! $user_id || ! $token ) {
		return;
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		wp_safe_redirect( add_query_arg( 'glass_verify', 'invalid', home_url( '/' ) ) );
		exit;
	}

	// اگر قبلاً تایید شده
	if ( (int) get_user_meta( $user_id, 'glass_pro_email_verified', true ) === 1 ) {
		wp_safe_redirect( add_query_arg( 'glass_verify', 'already', home_url( '/' ) ) );
		exit;
	}

	if ( ! glass_pro_verify_token( $user_id, $token ) ) {
		wp_safe_redirect( add_query_arg( 'glass_verify', 'expired', home_url( '/' ) ) );
		exit;
	}

	// تأیید شد
	update_user_meta( $user_id, 'glass_pro_email_verified', 1 );

	/**
	 * Action: glass_pro/user/email_verified
	 *
	 * @param int $user_id
	 */
	do_action( 'glass_pro/user/email_verified', $user_id );

	// auto-login
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	$dash = function_exists( 'glass_get_dashboard_url' )
			? glass_get_dashboard_url()
			: home_url( '/' );
	wp_safe_redirect( add_query_arg( 'glass_verify', 'success', $dash ) );
	exit;
}
