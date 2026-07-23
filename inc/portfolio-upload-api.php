<?php
/**
 * AJAX image upload API for portfolio submit form.
 *
 * @package Alborz_Ghaleb
 * @since 5.12.16
 * @updated 5.15.0 — DoS Rate-Limiting & Garbage Collection
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_ajax_glass_pf_upload_images', 'glass_pf_ajax_upload_images' );
add_action( 'wp_ajax_nopriv_glass_pf_upload_images', 'glass_pf_ajax_upload_images' );

/**
 * [SECURITY] Drop PHP-execution guard files into wp-content/uploads.
 *
 * This is defence-in-depth behind the per-file MIME/extension validation: even
 * if a crafted file were ever stored, Apache (.htaccess) and IIS (web.config)
 * are told never to execute server-side scripts from the uploads tree. The
 * files are written once and then left alone (cached via a transient so we do
 * not stat/write on every request). Nginx ignores these files, so production
 * Nginx hosts should add an equivalent `location` rule at the server level.
 *
 * @return void
 */
function glass_pro_harden_uploads_dir(): void {
	if ( get_transient( 'glass_pro_uploads_hardened' ) ) {
		return;
	}

	$uploads = wp_get_upload_dir();
	if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
		return;
	}
	$basedir = trailingslashit( $uploads['basedir'] );

	$htaccess = $basedir . '.htaccess';
	if ( ! file_exists( $htaccess ) ) {
		$rules = "# Added by Alborz Ghaleb — block script execution in uploads.\n"
			. "<FilesMatch \"\\.(?i:php|php3|php4|php5|php7|phtml|pht|phar|cgi|pl|py|asp|aspx|jsp|sh)$\">\n"
			. "\tRequire all denied\n"
			. "</FilesMatch>\n"
			. "<IfModule mod_php.c>\n\tphp_flag engine off\n</IfModule>\n"
			. "<IfModule mod_php7.c>\n\tphp_flag engine off\n</IfModule>\n";
		@file_put_contents( $htaccess, $rules ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	}

	$webconfig = $basedir . 'web.config';
	if ( ! file_exists( $webconfig ) ) {
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
			. "<configuration>\n  <system.webServer>\n    <handlers accessPolicy=\"Read\" />\n"
			. "  </system.webServer>\n</configuration>\n";
		@file_put_contents( $webconfig, $xml ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	}

	set_transient( 'glass_pro_uploads_hardened', 1, WEEK_IN_SECONDS );
}

function glass_pf_ajax_upload_images(): void {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'glass_pf_upload_images' ) ) {
		wp_send_json_error( [ 'message' => __( 'خطای امنیتی آپلود. صفحه را رفرش کنید.', 'glassmorphism-child-pro' ) ], 403 );
	}

	/*
	 * [SECURITY] By default, require an authenticated user before accepting
	 * file uploads into the media library. The endpoint was registered for
	 * both `wp_ajax_` and `wp_ajax_nopriv_`, which let logged-out visitors
	 * push attachments into wp-content/uploads (author_id = 0). Even with
	 * MIME sniffing this is a spam / storage-exhaustion vector and leaves
	 * orphaned, unowned attachments. Sites that genuinely want anonymous
	 * submissions can re-enable it via the filter below.
	 */
	$allow_guest_upload = (bool) apply_filters( 'glass_pro/portfolio/allow_guest_upload', false );
	if ( ! is_user_logged_in() && ! $allow_guest_upload ) {
		wp_send_json_error( [ 'message' => __( 'برای آپلود تصویر باید وارد حساب کاربری شوید.', 'glassmorphism-child-pro' ) ], 401 );
	}

		if ( empty( $_FILES['pf_images_ajax'] ) || empty( $_FILES['pf_images_ajax']['name'][0] ) ) {
			wp_send_json_error( [ 'message' => __( 'هیچ تصویری برای آپلود انتخاب نشده است.', 'glassmorphism-child-pro' ) ], 400 );
		}

		/*
		 * [SECURITY] Apply an upload quota to authenticated users as well as
		 * guests. A nonce prevents CSRF but does not stop a logged-in account
		 * from sending unlimited requests and filling the uploads directory.
		 * The defaults are deliberately generous and filterable for sites with
		 * a different business policy.
		 */
		$rate_limit_files = max( 1, (int) apply_filters( 'glass_pro/portfolio/max_upload_files_per_hour', 60 ) );
		$rate_limit_bytes = max( 1, (int) apply_filters( 'glass_pro/portfolio/max_upload_bytes_per_hour', 256 * 1024 * 1024 ) );
		$incoming_count   = 0;
		$incoming_bytes   = 0;
		$incoming_names   = (array) ( $_FILES['pf_images_ajax']['name'] ?? [] );
		$incoming_sizes   = (array) ( $_FILES['pf_images_ajax']['size'] ?? [] );
		foreach ( $incoming_names as $file_index => $incoming_name ) {
			if ( '' === (string) $incoming_name ) {
				continue;
			}
			$incoming_count++;
			$incoming_bytes += absint( $incoming_sizes[ $file_index ] ?? 0 );
		}

		if ( is_user_logged_in() ) {
			$rate_key = 'glass_pf_upload_user_rl_' . get_current_user_id();
			$rate     = get_transient( $rate_key );
			$rate     = is_array( $rate ) ? $rate : [ 'files' => 0, 'bytes' => 0 ];
			$used_files = absint( $rate['files'] ?? 0 );
			$used_bytes = absint( $rate['bytes'] ?? 0 );
			if ( ( $used_files + $incoming_count ) > $rate_limit_files || ( $used_bytes + $incoming_bytes ) > $rate_limit_bytes ) {
				wp_send_json_error( [ 'message' => __( 'سقف آپلود ساعتی حساب شما تکمیل شده است. لطفاً بعداً دوباره تلاش کنید.', 'glassmorphism-child-pro' ) ], 429 );
			}
			set_transient(
				$rate_key,
				[ 'files' => $used_files + $incoming_count, 'bytes' => $used_bytes + $incoming_bytes ],
				HOUR_IN_SECONDS
			);
		} else {
			// Guest uploads are disabled by default; keep the legacy IP limit
			// for installations that explicitly re-enable them with the filter.
			$ip = preg_replace( '/[^0-9a-fA-F:.]/', '', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
			$rl_key = 'glass_pf_upload_rl_' . md5( $ip );
			$attempts = (int) get_transient( $rl_key );
			if ( $attempts >= 15 ) {
				wp_send_json_error( [ 'message' => __( 'تعداد آپلودهای شما در این ساعت بیش از حد مجاز است. لطفاً ساعتی دیگر تلاش کنید یا وارد حساب خود شوید.', 'glassmorphism-child-pro' ) ], 429 );
			}
			set_transient( $rl_key, $attempts + 1, HOUR_IN_SECONDS );
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	// [SECURITY] Defence-in-depth: ensure PHP cannot be executed from the
	// uploads directory, in case a malicious file ever slips past validation.
	glass_pro_harden_uploads_dir();

	$files = $_FILES['pf_images_ajax'];
	$limit = min( 5, count( (array) $files['name'] ) );
	$uploaded = [];
	$errors = [];
	$max_upload_size = (int) apply_filters( 'glass_pro/portfolio/max_upload_size', 8 * 1024 * 1024 );
	$max_pixels = (int) apply_filters( 'glass_pro/portfolio/max_image_pixels', 24_000_000 );

	for ( $i = 0; $i < $limit; $i++ ) {
		if ( (int) $files['error'][ $i ] !== UPLOAD_ERR_OK ) {
			$errors[] = sprintf( __( 'آپلود فایل %s ناموفق بود.', 'glassmorphism-child-pro' ), sanitize_file_name( $files['name'][ $i ] ?? '' ) );
			continue;
		}

		$file_name = sanitize_file_name( wp_unslash( $files['name'][ $i ] ) );
		$file_tmp  = $files['tmp_name'][ $i ];
		$file_size = (int) $files['size'][ $i ];

		if ( $file_size <= 0 || $file_size > $max_upload_size ) {
			$errors[] = sprintf( __( 'فایل %1$s بزرگتر از حد مجاز است.', 'glassmorphism-child-pro' ), esc_html( $file_name ) );
			continue;
		}

		$allowed_mimes = [
			'jpg|jpeg' => 'image/jpeg',
			'png'      => 'image/png',
		];
		$checked = wp_check_filetype_and_ext( $file_tmp, $file_name, $allowed_mimes );

		// [SECURITY] Reject extension/content mismatches outright. When
		// wp_check_filetype_and_ext detects that the real content does not
		// match the claimed extension it returns a `proper_filename`; that is
		// the classic polyglot / double-extension ("shell.php.jpg") signal.
		if ( ! empty( $checked['proper_filename'] ) ) {
			$errors[] = sprintf( __( 'فرمت فایل %s با پسوند آن همخوانی ندارد.', 'glassmorphism-child-pro' ), esc_html( $file_name ) );
			continue;
		}

		$real_mime = ! empty( $checked['type'] ) ? $checked['type'] : '';

		// Cross-check with the actual decoded image header. Both signals must
		// agree on an allowed image type; a file that is not a real image
		// (getimagesize fails) is rejected.
		$img_info = @getimagesize( $file_tmp );
		$gd_mime  = ( false !== $img_info && ! empty( $img_info['mime'] ) ) ? $img_info['mime'] : '';

		if ( '' === $gd_mime ) {
			$real_mime = '';
		} elseif ( '' === $real_mime ) {
			$real_mime = $gd_mime;
		} elseif ( $real_mime !== $gd_mime ) {
			// Two trusted sources disagree — treat as suspicious.
			$real_mime = '';
		}

		if ( ! in_array( $real_mime, [ 'image/jpeg', 'image/png' ], true ) ) {
			$errors[] = sprintf( __( 'فرمت فایل %s معتبر نیست. فقط JPG و PNG مجاز است.', 'glassmorphism-child-pro' ), esc_html( $file_name ) );
			continue;
		}

		if ( ! empty( $img_info[0] ) && ! empty( $img_info[1] ) && ( (int) $img_info[0] * (int) $img_info[1] ) > $max_pixels ) {
			$errors[] = sprintf( __( 'ابعاد فایل %s بیش از حد مجاز است.', 'glassmorphism-child-pro' ), esc_html( $file_name ) );
			continue;
		}

		$processed_file = function_exists( 'glass_process_compress_and_watermark_image' )
			? glass_process_compress_and_watermark_image( $file_tmp, $real_mime )
			: false;

		$upload_dir = wp_upload_dir();
		if ( ! empty( $processed_file ) && is_readable( $processed_file ) ) {
			$unique_name = wp_unique_filename( $upload_dir['path'], 'ad_ajax_' . time() . '_' . $i . '.jpg' );
			$new_file_path = trailingslashit( $upload_dir['path'] ) . $unique_name;
			if ( ! @rename( $processed_file, $new_file_path ) ) {
				$errors[] = sprintf( __( 'ذخیره فایل %s ناموفق بود.', 'glassmorphism-child-pro' ), esc_html( $file_name ) );
				continue;
			}
			$mime = 'image/jpeg';
		} else {
			$single = [
				'name'     => $file_name,
				'tmp_name' => $file_tmp,
				'type'     => $real_mime,
				'error'    => 0,
				'size'     => $file_size,
			];
			$moved = wp_handle_sideload( $single, [ 'test_form' => false, 'mimes' => [ 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png' ] ] );
			if ( isset( $moved['error'] ) ) {
				$errors[] = $moved['error'];
				continue;
			}
			$new_file_path = $moved['file'];
			$mime = $moved['type'];
			$unique_name = basename( $new_file_path );

			/*
			 * [SECURITY] Server-independent hardening: when the GD re-encode
			 * path above did NOT run (GD missing, decode failed, etc.) the raw
			 * uploaded bytes are now on disk. Re-encode the image through
			 * WordPress's own image editor (Imagick/GD) so the file is rebuilt
			 * from decoded pixels — this destroys any embedded PHP/polyglot
			 * payload and strips metadata. This protects hosts where we cannot
			 * add an Nginx/.htaccess "no PHP in uploads" rule.
			 */
			$editor = wp_get_image_editor( $new_file_path );
			if ( ! is_wp_error( $editor ) ) {
				$saved = $editor->save( $new_file_path, $mime );
				if ( ! is_wp_error( $saved ) && ! empty( $saved['path'] ) && $saved['path'] !== $new_file_path ) {
					// Editor may have changed the extension/path; keep things in sync.
					@unlink( $new_file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
					$new_file_path = $saved['path'];
					$mime          = ! empty( $saved['mime-type'] ) ? $saved['mime-type'] : $mime;
					$unique_name   = basename( $new_file_path );
				}
			} else {
				// No usable image editor AND no GD re-encode happened: refuse to
				// store an un-sanitised raw upload rather than risk it.
				@unlink( $new_file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
				$errors[] = sprintf( __( 'پردازش تصویر %s ممکن نشد. لطفاً تصویر دیگری امتحان کنید.', 'glassmorphism-child-pro' ), esc_html( $file_name ) );
				continue;
			}
		}

		$attachment = [
			'guid'           => trailingslashit( $upload_dir['url'] ) . $unique_name,
			'post_mime_type' => $mime,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $unique_name ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_author'    => get_current_user_id(),
			'post_parent'    => 0,
		];
		$attach_id = wp_insert_attachment( $attachment, $new_file_path, 0 );
		if ( is_wp_error( $attach_id ) || ! $attach_id ) {
			$errors[] = sprintf( __( 'ثبت فایل %s در رسانه ناموفق بود.', 'glassmorphism-child-pro' ), esc_html( $file_name ) );
			continue;
		}
		$attach_data = wp_generate_attachment_metadata( $attach_id, $new_file_path );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		update_post_meta( $attach_id, '_glass_pending_portfolio_upload', (string) time() );
		update_post_meta( $attach_id, '_glass_pending_upload_user', get_current_user_id() );

		$uploaded[] = [
			'id'    => (int) $attach_id,
			'url'   => wp_get_attachment_image_url( $attach_id, 'thumbnail' ),
			'name'  => $file_name,
			'size'  => $file_size,
		];
	}

	if ( empty( $uploaded ) ) {
		wp_send_json_error( [ 'message' => implode( ' ', $errors ) ?: __( 'آپلود تصاویر ناموفق بود.', 'glassmorphism-child-pro' ) ], 400 );
	}

	wp_send_json_success( [ 'images' => $uploaded, 'errors' => $errors ] );
}

/* ────────────────────────────────────────
   [SECURITY v5.15.0] Garbage Collector پیوست‌های رها شده
   ──────────────────────────────────────── */
add_action( 'glass_pro_upload_gc_cron', 'glass_pro_run_upload_gc' );
function glass_pro_run_upload_gc(): int {
	$args = [
		'post_type'      => 'attachment',
		'post_status'    => 'any',
		'posts_per_page' => 50,
		'fields'         => 'ids',
		'meta_query'     => [
			[
				'key'     => '_glass_pending_portfolio_upload',
				'compare' => 'EXISTS',
			],
		],
	];
	$query = new WP_Query( $args );
	$deleted = 0;
	$now = time();
	if ( ! empty( $query->posts ) ) {
		foreach ( $query->posts as $attachment_id ) {
			$upload_time = (int) get_post_meta( $attachment_id, '_glass_pending_portfolio_upload', true );
			// اگر بیش از ۱۲ ساعت از آپلود گذشته و به آگهی وصل نشده
			if ( $upload_time <= 0 || ( $now - $upload_time ) > ( 12 * HOUR_IN_SECONDS ) ) {
				if ( wp_delete_attachment( (int) $attachment_id, true ) ) {
					$deleted++;
				}
			}
		}
	}
	return $deleted;
}

add_action( 'init', function() {
	if ( ! wp_next_scheduled( 'glass_pro_upload_gc_cron' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'glass_pro_upload_gc_cron' );
	}
});
