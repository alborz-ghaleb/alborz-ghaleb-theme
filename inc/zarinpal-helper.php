<?php
/**
 * Zarinpal HTTP helper — جایگزین امن curl_init برای فراخوانی API زرین‌پال.
 *
 * این فایل از v5.0.5 اضافه شده تا تمام مشکلات امنیتی pattern قبلی رفع شود:
 *   - SSL/TLS verify (پیش‌فرض WP_HTTP)
 *   - timeout مناسب (جلوگیری از hang و DoS)
 *   - استفاده از wp_remote_post (سازگار با proxy های وردپرس و filter ها)
 *   - چک null برای json_decode
 *   - error logging
 *
 * @package Alborz_Ghaleb
 * @since   5.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'glass_pro_zarinpal_request' ) ) :
	/**
	 * فراخوانی امن API زرین‌پال.
	 *
	 * @param string $endpoint 'request' یا 'verify'
	 * @param array  $data     داده‌های POST (merchant_id، amount، authority، ...)
	 * @return array|WP_Error  ['data' => [...], 'errors' => [...]] یا WP_Error
	 */
	function glass_pro_zarinpal_request( string $endpoint, array $data ) {
		// Phase 3: اگر پلاگین Alborz Ghaleb Core فعال باشد، لایه پرداخت مرکزی Core استفاده می‌شود.
		if ( function_exists( 'glass_core_zarinpal_request' ) ) {
			return glass_core_zarinpal_request( $endpoint, $data );
		}

		$endpoints = [
			'request' => 'https://api.zarinpal.com/pg/v4/payment/request.json',
			'verify'  => 'https://api.zarinpal.com/pg/v4/payment/verify.json',
		];

		if ( ! isset( $endpoints[ $endpoint ] ) ) {
			return new WP_Error( 'glass_pro_zp_invalid_endpoint', __( 'Endpoint نامعتبر است.', 'glassmorphism-child-pro' ) );
		}

		$url = $endpoints[ $endpoint ];

		// timeout قابل تنظیم با فیلتر
		$timeout = (int) apply_filters( 'glass_pro/zarinpal/timeout', 15 );

		$site_host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		$headers = [
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
				'User-Agent'   => 'Alborz-Ghaleb/' . GLASS_PRO_VERSION . ' (' . $site_host . '; WordPress; ZarinPal v4)',
				'Referer'      => home_url( '/' ),
			];
			/**
			 * Filter: glass_pro/zarinpal/headers — اجازه تغییر هدرها
			 */
		$headers = (array) apply_filters( 'glass_pro/zarinpal/headers', $headers );

		$args = [
			'method'      => 'POST',
			'timeout'     => $timeout,
			'redirection' => 5,
			'httpversion' => '1.1',
			'sslverify'   => true,
			'blocking'    => true,
			'headers'     => $headers,
			'body'        => wp_json_encode( $data ),
		];

		$response = wp_remote_post( $url, $args );

		// خطای HTTP
		if ( is_wp_error( $response ) ) {
			error_log( sprintf(
				'[Glass Pro][Zarinpal][%s] HTTP error: %s',
				$endpoint,
				$response->get_error_message()
			) );
			return $response;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		$body      = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			error_log( sprintf(
				'[Glass Pro][Zarinpal][%s] Empty response body. HTTP code: %d',
				$endpoint,
				$http_code
			) );
			return new WP_Error( 'glass_pro_zp_empty', __( 'پاسخ خالی از درگاه دریافت شد.', 'glassmorphism-child-pro' ) );
		}

		$decoded = json_decode( $body, true );

		if ( null === $decoded ) {
			error_log( sprintf(
				'[Glass Pro][Zarinpal][%s] Invalid JSON. Code: %d, Body: %s',
				$endpoint,
				$http_code,
				substr( $body, 0, 500 )
			) );
			return new WP_Error( 'glass_pro_zp_json', __( 'پاسخ نامعتبر از درگاه.', 'glassmorphism-child-pro' ) );
		}

		// لاگ کردن خطاهای زرین‌پال
		if ( ! empty( $decoded['errors'] ) ) {
			error_log( sprintf(
				'[Glass Pro][Zarinpal][%s] API errors: %s',
				$endpoint,
				wp_json_encode( $decoded['errors'] )
			) );
		}

		return $decoded;
	}
endif;

if ( ! function_exists( 'glass_pro_zarinpal_is_success' ) ) :
	/**
	 * بررسی موفقیت پاسخ زرین‌پال (با strict comparison).
	 *
	 * @param array|mixed $response پاسخ decode شده
	 * @return bool
	 */
	function glass_pro_zarinpal_is_success( $response ): bool {
		if ( function_exists( 'glass_core_zarinpal_is_success' ) ) {
			return glass_core_zarinpal_is_success( $response );
		}
		if ( ! is_array( $response ) || empty( $response['data']['code'] ) ) {
			return false;
		}
		$code = (int) $response['data']['code'];
		return ( 100 === $code || 101 === $code );
	}
endif;

if ( ! function_exists( 'glass_pro_zarinpal_get_authority' ) ) :
	/**
	 * استخراج authority از پاسخ request.
	 *
	 * @param array|mixed $response
	 * @return string  خالی اگر authority نباشد
	 */
	function glass_pro_zarinpal_get_authority( $response ): string {
		if ( function_exists( 'glass_core_zarinpal_get_authority' ) ) {
			return glass_core_zarinpal_get_authority( $response );
		}
		if ( ! is_array( $response ) || empty( $response['data']['authority'] ) ) {
			return '';
		}
		return (string) $response['data']['authority'];
	}
endif;
