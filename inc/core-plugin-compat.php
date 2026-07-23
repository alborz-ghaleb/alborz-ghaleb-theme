<?php
/**
 * Alborz Ghaleb Core compatibility layer.
 *
 * پل ارتباطی بین قالب و افزونه کور (Alborz Ghaleb Core).
 * اگر افزونه کور فعال باشد، از توابع آن استفاده می‌شود.
 * در غیر این صورت، قالب مستقل عمل می‌کند.
 *
 * @package Alborz_Ghaleb
 * @since   5.17.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * آیا افزونه کور فعال است؟
 */
function glass_pro_core_plugin_active(): bool {
	return defined( 'GLASS_CORE_VERSION' ) && function_exists( 'glass_core_transaction_log' );
}

/**
 * آیا قابلیت ویژه‌سازی آگهی فعال است؟
 */
add_filter( 'glass_pro/feature_ad/enabled', static function ( $enabled ) {
    if ( function_exists( 'glass_core_feature_enabled' ) ) {
        return glass_core_feature_enabled();
    }
    return $enabled;
} );

/**
 * مبلغ ویژه‌سازی آگهی
 */
add_filter( 'glass_pro/feature_ad/amount', static function ( $amount ) {
    if ( function_exists( 'glass_core_feature_amount' ) ) {
        return glass_core_feature_amount();
    }
    return $amount;
} );

// Mirror theme transaction logs to the Core plugin when both are available.
add_action( 'glass_pro/payment/paid', 'glass_pro_core_bridge_paid_transaction', 20, 4 );
function glass_pro_core_bridge_paid_transaction( int $post_id, string $ref_id, int $amount, $result = null ): void {
	if ( ! function_exists( 'glass_core_transaction_log' ) ) {
		return;
	}
	glass_core_transaction_log( [
		'post_id'  => $post_id,
		'gateway'  => 'zarinpal',
		'type'     => 'theme_paid_bridge',
		'status'   => 'paid',
		'amount'   => $amount,
		'ref_id'   => $ref_id,
		'payload'  => $result,
	] );
}
