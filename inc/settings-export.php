<?php
/**
 * Customizer Settings Export/Import
 *
 * صفحه‌ای در ابزارها → Export/Import Glass Settings که اجازه می‌دهد
 * تمام تنظیمات قالب را در یک فایل JSON export و در سایت دیگر import کنید.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'glass_pro_settings_export_menu' );
/**
 * @return void
 */
function glass_pro_settings_export_menu(): void {
	add_management_page(
		__( 'Export/Import تنظیمات Glassmorphism', 'glassmorphism-child-pro' ),
		__( 'Export Glassmorphism', 'glassmorphism-child-pro' ),
		'manage_options',
		'glass-pro-export',
		'glass_pro_settings_export_page'
	);
}

/**
 * هندل export.
 */
add_action( 'admin_init', 'glass_pro_settings_export_handler' );
/**
 * @return void
 */
function glass_pro_settings_export_handler(): void {
	if ( ! isset( $_GET['glass_pro_export'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'glass_pro_export' ) ) {
		wp_die( esc_html__( 'خطای امنیتی.', 'glassmorphism-child-pro' ) );
	}

	$theme_mods = get_theme_mods();
	$export = [
		'theme'    => 'Alborz Ghaleb',
		'version'  => defined( 'GLASS_PRO_VERSION' ) ? GLASS_PRO_VERSION : 'unknown',
		'exported' => gmdate( 'Y-m-d H:i:s' ),
		'mods'     => $theme_mods,
	];

	$filename = 'glassmorphism-settings-' . gmdate( 'Y-m-d-His' ) . '.json';
	header( 'Content-Type: application/json; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Cache-Control: no-store' );
	echo wp_json_encode( $export, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	exit;
}

/**
 * هندل import.
 */
add_action( 'admin_init', 'glass_pro_settings_import_handler' );
/**
 * @return void
 */
function glass_pro_settings_import_handler(): void {
	if ( ! isset( $_POST['glass_pro_import'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['glass_pro_import_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glass_pro_import_nonce'] ) ), 'glass_pro_import' ) ) {
		wp_die( esc_html__( 'خطای امنیتی.', 'glassmorphism-child-pro' ) );
	}

	if ( empty( $_FILES['glass_import_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['glass_import_file']['tmp_name'] ) ) {
		add_settings_error( 'glass_pro_import', 'no_file', __( 'فایلی انتخاب نشد.', 'glassmorphism-child-pro' ) );
		return;
	}

	$file_size = isset( $_FILES['glass_import_file']['size'] ) ? absint( $_FILES['glass_import_file']['size'] ) : 0;
	if ( $file_size <= 0 || $file_size > 1024 * 1024 ) {
		add_settings_error( 'glass_pro_import', 'bad_size', __( 'حجم فایل تنظیمات نامعتبر است. حداکثر ۱ مگابایت مجاز است.', 'glassmorphism-child-pro' ) );
		return;
	}

	$file_name = isset( $_FILES['glass_import_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['glass_import_file']['name'] ) ) : '';
	if ( ! function_exists( 'wp_check_filetype_and_ext' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	$checked   = wp_check_filetype_and_ext( $_FILES['glass_import_file']['tmp_name'], $file_name, [ 'json' => 'application/json' ] );
	if ( 'json' !== ( $checked['ext'] ?? '' ) ) {
		add_settings_error( 'glass_pro_import', 'bad_type', __( 'فقط فایل JSON معتبر قابل import است.', 'glassmorphism-child-pro' ) );
		return;
	}

	$file_content = file_get_contents( $_FILES['glass_import_file']['tmp_name'] );
	if ( false === $file_content ) {
		add_settings_error( 'glass_pro_import', 'read_fail', __( 'خواندن فایل ناموفق بود.', 'glassmorphism-child-pro' ) );
		return;
	}

	$data = json_decode( $file_content, true );
	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) || empty( $data['mods'] ) || ! is_array( $data['mods'] ) ) {
		add_settings_error( 'glass_pro_import', 'bad_json', __( 'فایل JSON نامعتبر است.', 'glassmorphism-child-pro' ) );
		return;
	}

	$allowed_prefixes = (array) apply_filters( 'glass_pro/settings_import/allowed_prefixes', [ 'glass_', 'glass_pro_', 'fl_', 'custom_logo', 'site_icon', 'nav_menu_locations' ] );
	$count = 0;
	foreach ( $data['mods'] as $key => $value ) {
		$key = sanitize_key( (string) $key );
		$allowed = false;
		foreach ( $allowed_prefixes as $prefix ) {
			if ( 0 === strpos( $key, (string) $prefix ) ) {
				$allowed = true;
				break;
			}
		}
		if ( ! $allowed || is_object( $value ) ) {
			continue;
		}
		set_theme_mod( $key, $value );
		$count++;
	}

	add_settings_error(
		'glass_pro_import',
		'success',
		sprintf( __( '%d تنظیم با موفقیت import شد.', 'glassmorphism-child-pro' ), $count ),
		'success'
	);
}

/**
 * @return void
 */
function glass_pro_settings_export_page(): void {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Export/Import تنظیمات Alborz Ghaleb', 'glassmorphism-child-pro' ); ?></h1>

		<?php settings_errors( 'glass_pro_import' ); ?>

		<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">

			<div class="card" style="padding: 24px; background: #fff; border: 1px solid #c3c4c7;">
				<h2>📥 <?php esc_html_e( 'دانلود تنظیمات (Export)', 'glassmorphism-child-pro' ); ?></h2>
				<p><?php esc_html_e( 'یک فایل JSON شامل تمام Customizer settings قالب را دانلود کنید. این فایل را می‌توانید در سایت دیگری upload کنید یا به‌عنوان backup نگه دارید.', 'glassmorphism-child-pro' ); ?></p>
				<p>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'tools.php?page=glass-pro-export&glass_pro_export=1' ), 'glass_pro_export' ) ); ?>" class="button button-primary button-large">
						<?php esc_html_e( '💾 دانلود فایل تنظیمات', 'glassmorphism-child-pro' ); ?>
					</a>
				</p>
			</div>

			<div class="card" style="padding: 24px; background: #fff; border: 1px solid #c3c4c7;">
				<h2>📤 <?php esc_html_e( 'بارگذاری تنظیمات (Import)', 'glassmorphism-child-pro' ); ?></h2>
				<p><?php esc_html_e( 'یک فایل JSON تنظیمات که قبلاً export شده را بارگذاری کنید. تنظیمات فعلی override می‌شود.', 'glassmorphism-child-pro' ); ?></p>
				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'glass_pro_import', 'glass_pro_import_nonce' ); ?>
					<input type="file" name="glass_import_file" accept="application/json,.json" required>
					<p>
						<button type="submit" name="glass_pro_import" value="1" class="button button-primary button-large" onclick="return confirm('<?php esc_attr_e( 'مطمئنید؟ تنظیمات فعلی override خواهد شد.', 'glassmorphism-child-pro' ); ?>');">
							<?php esc_html_e( '⬆️ بارگذاری و اعمال', 'glassmorphism-child-pro' ); ?>
						</button>
					</p>
				</form>
			</div>

		</div>

		<div style="margin-top: 30px; padding: 16px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; color: #92400e;">
			⚠️ <?php esc_html_e( 'نکته: قبل از import حتماً یک export از تنظیمات فعلی بگیرید تا در صورت نیاز بتوانید برگردید.', 'glassmorphism-child-pro' ); ?>
		</div>
	</div>
	<?php
}
