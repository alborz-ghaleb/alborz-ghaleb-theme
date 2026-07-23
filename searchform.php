<?php
if (!defined('ABSPATH')) {
	exit;
}

$search_action = function_exists('glass_search_action_url') ? glass_search_action_url() : home_url('/');
$current_lang  = function_exists('glass_search_current_lang') ? glass_search_current_lang() : 'fa';
$field_id      = 'glass-s-' . wp_unique_id();
$search_label  = function_exists( 'glass_search_t' ) ? glass_search_t( 'placeholder' ) : __( 'Search', 'glassmorphism-child-pro' );
$search_button = function_exists( 'glass_search_t' ) ? glass_search_t( 'search_btn' ) : __( 'Search', 'glassmorphism-child-pro' );
?>

<form role="search" aria-label="<?php echo esc_attr( function_exists('glass_search_t') ? glass_search_t('search_form_label') : __( 'Search form', 'glassmorphism-child-pro' ) ); ?>" method="get" class="gs-form" action="<?php echo esc_url($search_action); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr($field_id); ?>">
		<?php echo esc_html( $search_label ); ?>
	</label>

	<input
		type="search"
		id="<?php echo esc_attr($field_id); ?>"
		class="gs-form__input"
		placeholder="<?php echo esc_attr( $search_label ); ?>"
		value="<?php echo esc_attr(get_search_query()); ?>"
		name="s"
	/>

	<input type="hidden" name="lang" value="<?php echo esc_attr($current_lang); ?>" />

	<button type="submit" class="gs-form__button" aria-label="<?php echo esc_attr( $search_button ); ?>">
		<svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="11" cy="11" r="8"></circle>
			<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
		</svg>
		<?php echo esc_html( $search_button ); ?>
	</button>
</form>