<?php
/**
 * Portfolio Submit — Helpers (link/HTML detection، image compress + watermark)
 *
 * @package Alborz_Ghaleb
 * @since   5.0.3
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   ۲. تابع تشخیص و بلاک کردن هرگونه لینک، دامنه یا کدهای HTML و اسکریپت
   ════════════════════════════════════════ */
function glass_contains_links_or_html( $text ) {
    if ( empty( $text ) ) return false;
    if ( preg_match( '/<[^>]*>/', $text ) ) return true;
    if ( preg_match( '/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', $text ) ) return true;
    if ( preg_match( '/\bwww\.[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', $text ) ) return true;
    if ( preg_match( '/[a-zA-Z0-9.-]+\.(com|ir|net|org|co|info|me|online|site|website|xyz|link|club|shop|app|su|biz)\b/i', $text ) ) return true;
    return false;
}

/* ════════════════════════════════════════
   ۳. تابع کمکی پردازش، کاهش حجم و واترمرک تصویر با کتابخانه GD
   ════════════════════════════════════════ */
function glass_process_compress_and_watermark_image( $file_path, $mime_type ) {
    // Prefer GD for the existing watermark path. If the required GD loader is
    // unavailable, use WordPress's configured image editor (Imagick/GD).
    // Never return the raw upload path: callers treat a non-false result as a
    // re-encoded JPEG and would otherwise store raw bytes with the wrong MIME.
    $loader = ( 'image/png' === $mime_type ) ? 'imagecreatefrompng' : 'imagecreatefromjpeg';
    if ( ! function_exists( $loader ) || ! function_exists( 'imagejpeg' ) ) {
        if ( ! function_exists( 'wp_get_image_editor' ) ) {
            return false;
        }

        $editor = wp_get_image_editor( $file_path );
        if ( is_wp_error( $editor ) ) {
            return false;
        }

        $upload_dir = wp_upload_dir();
        if ( ! empty( $upload_dir['error'] ) || empty( $upload_dir['path'] ) ) {
            return false;
        }

        $temp_file = tempnam( $upload_dir['path'], 'glass_opt_' );
        if ( ! $temp_file ) {
            return false;
        }

        $saved = $editor->save( $temp_file, 'image/jpeg' );
        if ( is_wp_error( $saved ) || empty( $saved['path'] ) || ! is_readable( $saved['path'] ) ) {
            @unlink( $temp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
            return false;
        }

        if ( $saved['path'] !== $temp_file && file_exists( $temp_file ) ) {
            @unlink( $temp_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
        }
        return $saved['path'];
    }

    $source = @call_user_func( $loader, $file_path );
    if ( ! $source ) {
        return false;
    }

    $source_w = imagesx( $source );
    $source_h = imagesy( $source );

    $logo_id = get_theme_mod( 'custom_logo' );
    if ( $logo_id ) {
        $logo_path = get_attached_file( $logo_id );
        if ( $logo_path && file_exists( $logo_path ) ) {
            $logo_info = getimagesize( $logo_path );
            $logo_mime = $logo_info['mime'] ?? '';
            
            if ( $logo_mime === 'image/png' ) {
                $watermark = @imagecreatefrompng( $logo_path );
            } elseif ( $logo_mime === 'image/jpeg' ) {
                $watermark = @imagecreatefromjpeg( $logo_path );
            } else {
                $watermark = false;
            }

            if ( $watermark ) {
                imagealphablending( $source, true );
                imagealphablending( $watermark, true );

                $logo_w = imagesx( $watermark );
                $logo_h = imagesy( $watermark );

                $dest_logo_w = round( $source_w * 0.15 );
                $dest_logo_h = round( $logo_h * ( $dest_logo_w / $logo_w ) );

                if ( $dest_logo_w < 60 ) {
                    $dest_logo_w = 60;
                    $dest_logo_h = round( $logo_h * ( $dest_logo_w / $logo_w ) );
                }

                $padding = 20;
                $dest_x  = $padding;
                $dest_y  = $source_h - $dest_logo_h - $padding;

                if ( $dest_y < 0 ) $dest_y = 10;

                imagecopyresampled(
                    $source, $watermark,
                    $dest_x, $dest_y, 0, 0,
                    $dest_logo_w, $dest_logo_h, $logo_w, $logo_h
                );

                imagedestroy( $watermark );
            }
        }
    }

    $upload_dir = wp_upload_dir();
    $temp_file = tempnam( $upload_dir['path'], 'glass_opt_' );
    if ( imagejpeg( $source, $temp_file, 75 ) ) {
        imagedestroy( $source );
        return $temp_file;
    }

    imagedestroy( $source );
    return false;
}

