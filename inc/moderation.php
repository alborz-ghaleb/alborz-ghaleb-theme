<?php
/**
 * Portfolio Moderation Helpers
 *
 * Adds a small admin meta box for moderation state/rejection note without
 * replacing the existing legacy approval workflow.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.8
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'add_meta_boxes', 'glass_pro_moderation_meta_box' );
function glass_pro_moderation_meta_box(): void {
	add_meta_box(
		'glass_pro_moderation',
		__( 'وضعیت بررسی آگهی', 'glassmorphism-child-pro' ),
		'glass_pro_moderation_meta_box_render',
		'portfolio',
		'side',
		'high'
	);
}

function glass_pro_moderation_meta_box_render( WP_Post $post ): void {
	wp_nonce_field( 'glass_pro_moderation_save', 'glass_pro_moderation_nonce' );
	$state = get_post_meta( $post->ID, 'portfolio_moderation_state', true ) ?: 'pending_review';
	$note  = get_post_meta( $post->ID, 'portfolio_moderation_note', true );
	$states = [
		'pending_review' => __( 'در انتظار بررسی', 'glassmorphism-child-pro' ),
		'approved'       => __( 'تأیید شده', 'glassmorphism-child-pro' ),
		'rejected'       => __( 'رد شده', 'glassmorphism-child-pro' ),
		'spam'           => __( 'اسپم', 'glassmorphism-child-pro' ),
	];
	?>
	<p><label for="glass_pro_moderation_state"><?php esc_html_e( 'وضعیت', 'glassmorphism-child-pro' ); ?></label></p>
	<select name="glass_pro_moderation_state" id="glass_pro_moderation_state" style="width:100%">
		<?php foreach ( $states as $key => $label ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $state, $key ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<p><label for="glass_pro_moderation_note"><?php esc_html_e( 'یادداشت/دلیل رد', 'glassmorphism-child-pro' ); ?></label></p>
	<textarea name="glass_pro_moderation_note" id="glass_pro_moderation_note" rows="4" style="width:100%"><?php echo esc_textarea( $note ); ?></textarea>
	<?php
}

add_action( 'save_post_portfolio', 'glass_pro_moderation_save' );
function glass_pro_moderation_save( int $post_id ): void {
	if ( ! isset( $_POST['glass_pro_moderation_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glass_pro_moderation_nonce'] ) ), 'glass_pro_moderation_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$allowed = [ 'pending_review', 'approved', 'rejected', 'spam' ];
	$state = isset( $_POST['glass_pro_moderation_state'] ) ? sanitize_key( wp_unslash( $_POST['glass_pro_moderation_state'] ) ) : 'pending_review';
	if ( ! in_array( $state, $allowed, true ) ) {
		$state = 'pending_review';
	}
	$note = isset( $_POST['glass_pro_moderation_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['glass_pro_moderation_note'] ) ) : '';
	update_post_meta( $post_id, 'portfolio_moderation_state', $state );
	update_post_meta( $post_id, 'portfolio_moderation_note', $note );
}
