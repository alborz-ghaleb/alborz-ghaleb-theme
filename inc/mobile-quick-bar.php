<?php
/**
 * Mobile Quick Action Bar — independently removable.
 *
 * Rollback: remove '/inc/mobile-quick-bar.php' from functions.php.
 * No template file or existing floating component is modified.
 *
 * @package Alborz_Ghaleb
 * @since   8.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'body_class', 'glass_mobile_quick_bar_body_class' );
function glass_mobile_quick_bar_body_class( array $classes ): array {
	if ( apply_filters( 'glass_pro/mobile_quick_bar/enabled', true ) ) {
		$classes[] = 'glass-feature-mobile-quickbar';
	}
	return $classes;
}

/* Attach the tiny behavior to the existing bundle: no extra JS request. */
add_action( 'wp_enqueue_scripts', 'glass_mobile_quick_bar_inline_script', 38 );
function glass_mobile_quick_bar_inline_script(): void {
	if ( ! apply_filters( 'glass_pro/mobile_quick_bar/enabled', true ) ) {
		return;
	}
	$path = GLASS_PRO_DIR . '/assets/js/mobile-quick-bar.js';
	if ( is_readable( $path ) ) {
		$script = (string) file_get_contents( $path );
		if ( '' !== $script ) {
			wp_add_inline_script( 'glass-bundle', $script, 'after' );
		}
	}
}

/**
 * Small internal label bank; avoids adding database options and supports the
 * six languages already shipped by the theme.
 */
function glass_mobile_quick_bar_labels(): array {
	$lang = function_exists( 'glass_current_lang' ) ? glass_current_lang() : substr( get_locale(), 0, 2 );
	$bank = [
		'fa' => [ 'bar' => 'ارتباط سریع', 'call' => 'تماس', 'whatsapp' => 'واتساپ', 'telegram' => 'تلگرام', 'instagram' => 'اینستا' ],
		'en' => [ 'bar' => 'Quick contact', 'call' => 'Call', 'whatsapp' => 'WhatsApp', 'telegram' => 'Telegram', 'instagram' => 'Instagram' ],
		'ar' => [ 'bar' => 'اتصال سريع', 'call' => 'اتصال', 'whatsapp' => 'واتساب', 'telegram' => 'تيليجرام', 'instagram' => 'إنستغرام' ],
		'tr' => [ 'bar' => 'Hızlı iletişim', 'call' => 'Ara', 'whatsapp' => 'WhatsApp', 'telegram' => 'Telegram', 'instagram' => 'Instagram' ],
		'ru' => [ 'bar' => 'Быстрая связь', 'call' => 'Звонок', 'whatsapp' => 'WhatsApp', 'telegram' => 'Telegram', 'instagram' => 'Instagram' ],
		'hy' => [ 'bar' => 'Արագ կապ', 'call' => 'Զանգ', 'whatsapp' => 'WhatsApp', 'telegram' => 'Telegram', 'instagram' => 'Instagram' ],
	];
	$lang = strtolower( substr( (string) $lang, 0, 2 ) );
	return $bank[ $lang ] ?? $bank['en'];
}

add_action( 'glass_pro/after_page', 'glass_mobile_quick_bar_render', 20 );
function glass_mobile_quick_bar_render(): void {
	if ( is_admin() || ! apply_filters( 'glass_pro/mobile_quick_bar/enabled', true ) ) {
		return;
	}

	$phone = function_exists( 'glass_pro_contact' )
		? glass_pro_contact( 'phone_sales_1_tel', '09121390683' )
		: get_theme_mod( 'fl_fab_phone', get_theme_mod( 'fl_footer_phone', '09121390683' ) );
	$phone = preg_replace( '/[^0-9+]/', '', (string) $phone );

	$wa_number = function_exists( 'glass_pro_contact' )
		? glass_pro_contact( 'whatsapp_number', '989121390683' )
		: '';
	$wa_number = preg_replace( '/[^0-9]/', '', (string) $wa_number );
	$wa_url    = $wa_number ? 'https://wa.me/' . $wa_number : get_theme_mod( 'fl_fab_whatsapp', '' );
	// Prefer explicitly saved new settings, then the site's established FAB
	// settings, and only then use a safe theme fallback.
	$telegram_url = (string) get_theme_mod( 'glass_pro_contact_telegram_url', '' );
	if ( '' === $telegram_url ) {
		$telegram_url = (string) get_theme_mod( 'fl_fab_telegram', '' );
	}
	if ( '' === $telegram_url && function_exists( 'glass_pro_contact' ) ) {
		$telegram_url = glass_pro_contact( 'telegram_url', 'https://t.me/alborzghaleb' );
	}

	$instagram_url = (string) get_theme_mod( 'glass_pro_contact_instagram_url', '' );
	if ( '' === $instagram_url ) {
		$instagram_url = (string) get_theme_mod( 'fl_fab_instagram', '' );
	}
	if ( '' === $instagram_url && function_exists( 'glass_pro_contact' ) ) {
		$instagram_url = glass_pro_contact( 'instagram_url', 'https://instagram.com/alborz_ghaleb' );
	}

	$labels = glass_mobile_quick_bar_labels();
	?>
	<div class="gmqb-page-spacer" aria-hidden="true"></div>
	<nav class="glass-mobile-quickbar" id="glassMobileQuickbar" aria-label="<?php echo esc_attr( $labels['bar'] ); ?>">
		<?php if ( $phone ) : ?>
		<a class="gmqb-item gmqb-call" href="tel:<?php echo esc_attr( $phone ); ?>" aria-label="<?php echo esc_attr( $labels['call'] ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8.09 9.73a16 16 0 0 0 6.18 6.18l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92z"/></svg>
			<span><?php echo esc_html( $labels['call'] ); ?></span>
		</a>
		<?php endif; ?>

		<?php if ( $wa_url ) : ?>
		<a class="gmqb-item gmqb-whatsapp" href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php echo esc_attr( $labels['whatsapp'] ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.26-.46-2.39-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.07c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35zM12.04 2a9.84 9.84 0 0 0-8.4 14.96L2.05 22l5.18-1.52A9.95 9.95 0 1 0 12.04 2z"/></svg>
			<span><?php echo esc_html( $labels['whatsapp'] ); ?></span>
		</a>
		<?php endif; ?>

		<?php if ( $telegram_url ) : ?>
		<a class="gmqb-item gmqb-telegram" href="<?php echo esc_url( $telegram_url ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php echo esc_attr( $labels['telegram'] ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.94 3.35 18.9 20.1c-.23 1.18-.84 1.47-1.7.92l-4.63-3.42-2.24 2.15c-.25.25-.46.46-.94.46l.33-4.72 8.59-7.76c.37-.33-.08-.52-.58-.19L7.12 14.22l-4.57-1.43c-.99-.31-1.01-.99.21-1.47L20.62 4.4c.83-.3 1.55.2 1.32-1.05z"/></svg>
			<span><?php echo esc_html( $labels['telegram'] ); ?></span>
		</a>
		<?php endif; ?>

		<?php if ( $instagram_url ) : ?>
		<a class="gmqb-item gmqb-instagram" href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php echo esc_attr( $labels['instagram'] ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" class="gmqb-ig-dot"/></svg>
			<span><?php echo esc_html( $labels['instagram'] ); ?></span>
		</a>
		<?php endif; ?>
	</nav>
	<?php
}
