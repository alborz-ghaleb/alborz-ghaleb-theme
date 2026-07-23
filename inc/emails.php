<?php
/**
 * Email Notification Layer
 *
 * @package Alborz_Ghaleb
 * @since   5.0.8
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function glass_pro_mail_headers(): array {
	$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );
	$from_email = apply_filters( 'glass_pro/mail/from_address', 'noreply@' . preg_replace( '/^www\./i', '', (string) $site_domain ) );
	return [
		'Content-Type: text/html; charset=UTF-8',
		sprintf( 'From: %s <%s>', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), sanitize_email( $from_email ) ),
	];
}

function glass_pro_mail_template( string $title, string $body ): string {
	return '<div style="font-family:Tahoma,Arial,sans-serif;direction:rtl;line-height:1.8;color:#17212B">'
		. '<h2 style="color:#2D5F93">' . esc_html( $title ) . '</h2>'
		. wp_kses_post( wpautop( $body ) )
		. '<hr><p style="font-size:12px;color:#17212b;">'
		. '</p></div>';
}

add_action( 'glass_pro/payment/paid', 'glass_pro_email_payment_paid', 10, 3 );
function glass_pro_email_payment_paid( int $post_id, string $ref_id, int $amount ): void {
	if ( ! get_option( 'glass_pro_notify_admin_payments', true ) ) {
		return;
	}
	$subject = sprintf( __( 'پرداخت موفق آگهی #%d', 'glassmorphism-child-pro' ), $post_id );
	$body = sprintf(
		__( 'پرداخت آگهی با موفقیت تأیید شد.<br>آگهی: %1$s<br>مبلغ: %2$s تومان<br>کد رهگیری: %3$s', 'glassmorphism-child-pro' ),
		'<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a>',
		esc_html( number_format_i18n( $amount ) ),
		esc_html( $ref_id )
	);
	wp_mail( get_option( 'admin_email' ), $subject, glass_pro_mail_template( $subject, $body ), glass_pro_mail_headers() );
}

add_action( 'transition_post_status', 'glass_pro_email_new_portfolio_pending', 10, 3 );
function glass_pro_email_new_portfolio_pending( string $new_status, string $old_status, WP_Post $post ): void {
	if ( 'portfolio' !== $post->post_type || 'pending' !== $new_status || 'pending' === $old_status ) {
		return;
	}
	if ( ! get_option( 'glass_pro_notify_admin_ads', true ) ) {
		return;
	}
	$subject = sprintf( __( 'آگهی جدید در انتظار بررسی: %s', 'glassmorphism-child-pro' ), get_the_title( $post ) );
	$body = sprintf(
		__( 'یک آگهی جدید در انتظار بررسی است.<br><a href="%s">مشاهده در پیشخوان</a>', 'glassmorphism-child-pro' ),
		esc_url( get_edit_post_link( $post->ID ) )
	);
	wp_mail( get_option( 'admin_email' ), $subject, glass_pro_mail_template( $subject, $body ), glass_pro_mail_headers() );
}
