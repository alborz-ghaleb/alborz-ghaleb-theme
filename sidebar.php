<?php
/**
 * Sidebar Template — قابل widgetize.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'glass-pro-sidebar' ) ) {
	return;
}
?>
<aside id="secondary" class="glass-sidebar widget-area" role="complementary" aria-label="<?php esc_attr_e( 'سایدبار', 'glassmorphism-child-pro' ); ?>">
	<?php dynamic_sidebar( 'glass-pro-sidebar' ); ?>
</aside>
