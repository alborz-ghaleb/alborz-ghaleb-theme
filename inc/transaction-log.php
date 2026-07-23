<?php
/**
 * Transaction Log Infrastructure
 *
 * Lightweight payment audit table for Zarinpal/crypto flows. This is intentionally
 * inside the theme for backward compatibility; it can be moved to a companion
 * plugin later without changing the public helper API.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return string
 */
function glass_pro_transaction_table(): string {
	global $wpdb;
	return $wpdb->prefix . 'glass_pro_transactions';
}

add_action( 'after_switch_theme', 'glass_pro_transaction_install' );
add_action( 'admin_init', 'glass_pro_transaction_install_maybe' );

/**
 * @return void
 */
function glass_pro_transaction_install_maybe(): void {
	if ( '1' === get_option( 'glass_pro_transactions_installed' ) ) {
		return;
	}
	glass_pro_transaction_install();
}

/**
 * @return void
 */
function glass_pro_transaction_install(): void {
	global $wpdb;

	$table = glass_pro_transaction_table();
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		post_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
		user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
		gateway VARCHAR(40) NOT NULL DEFAULT '',
		type VARCHAR(60) NOT NULL DEFAULT '',
		status VARCHAR(60) NOT NULL DEFAULT '',
		amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
		currency VARCHAR(20) NOT NULL DEFAULT 'IRR',
		authority VARCHAR(191) NOT NULL DEFAULT '',
		ref_id VARCHAR(191) NOT NULL DEFAULT '',
		payload LONGTEXT NULL,
		created_at DATETIME NOT NULL,
		PRIMARY KEY  (id),
		KEY post_id (post_id),
		KEY user_id (user_id),
		KEY gateway_status (gateway, status),
		KEY authority (authority)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'glass_pro_transactions_installed', '1', false );
}

/**
 * ذخیره یک رخداد تراکنش.
 *
 * @param array $args
 * @return int Insert ID or 0
 */
function glass_pro_transaction_log( array $args ): int {
	// Phase 3: در صورت فعال بودن Core، منبع اصلی لاگ تراکنش پلاگین Core است.
	if ( function_exists( 'glass_core_transaction_log' ) ) {
		return glass_core_transaction_log( $args );
	}

	global $wpdb;
	glass_pro_transaction_install_maybe();

	$defaults = [
		'post_id'   => 0,
		'user_id'   => get_current_user_id(),
		'gateway'   => '',
		'type'      => '',
		'status'    => '',
		'amount'    => 0,
		'currency'  => 'IRR',
		'authority' => '',
		'ref_id'    => '',
		'payload'   => null,
	];
	$args = wp_parse_args( $args, $defaults );

	$payload = $args['payload'];
	if ( null !== $payload && ! is_string( $payload ) ) {
		$payload = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}

	$inserted = $wpdb->insert(
		glass_pro_transaction_table(),
		[
			'post_id'    => absint( $args['post_id'] ),
			'user_id'    => absint( $args['user_id'] ),
			'gateway'    => sanitize_key( $args['gateway'] ),
			'type'       => sanitize_key( $args['type'] ),
			'status'     => sanitize_key( $args['status'] ),
			'amount'     => absint( $args['amount'] ),
			'currency'   => sanitize_text_field( $args['currency'] ),
			'authority'  => sanitize_text_field( $args['authority'] ),
			'ref_id'     => sanitize_text_field( $args['ref_id'] ),
			'payload'    => $payload,
			'created_at' => current_time( 'mysql' ),
		],
		[ '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' ]
	);

	return $inserted ? (int) $wpdb->insert_id : 0;
}

add_action( 'admin_menu', 'glass_pro_transactions_admin_menu' );
/**
 * @return void
 */
function glass_pro_transactions_admin_menu(): void {
	add_management_page(
		__( 'تراکنش‌های Glassmorphism', 'glassmorphism-child-pro' ),
		__( 'تراکنش‌های Glassmorphism', 'glassmorphism-child-pro' ),
		'manage_options',
		'glass-pro-transactions',
		'glass_pro_transactions_page'
	);
}

/**
 * @return void
 */
function glass_pro_transactions_page(): void {
	global $wpdb;
	$table = glass_pro_transaction_table();
	$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 100" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'آخرین تراکنش‌های Glassmorphism', 'glassmorphism-child-pro' ); ?></h1>
		<table class="widefat striped">
			<thead><tr><th>ID</th><th>Post</th><th>Gateway</th><th>Type</th><th>Status</th><th>Amount</th><th>Authority</th><th>Ref</th><th>Date</th></tr></thead>
			<tbody>
			<?php if ( $rows ) : foreach ( $rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row->id ); ?></td>
					<td><?php echo esc_html( $row->post_id ); ?></td>
					<td><?php echo esc_html( $row->gateway ); ?></td>
					<td><?php echo esc_html( $row->type ); ?></td>
					<td><?php echo esc_html( $row->status ); ?></td>
					<td><?php echo esc_html( number_format_i18n( (int) $row->amount ) ); ?> <?php echo esc_html( $row->currency ); ?></td>
					<td><code><?php echo esc_html( $row->authority ); ?></code></td>
					<td><code><?php echo esc_html( $row->ref_id ); ?></code></td>
					<td><?php echo esc_html( $row->created_at ); ?></td>
				</tr>
			<?php endforeach; else : ?>
				<tr><td colspan="9"><?php esc_html_e( 'هنوز تراکنشی ثبت نشده است.', 'glassmorphism-child-pro' ); ?></td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
