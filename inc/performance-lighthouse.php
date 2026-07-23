<?php
/**
 * Lighthouse-targeted optimizations.
 *
 * @package Alborz_Ghaleb
 * @since   5.9.2
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'script_loader_tag', 'glass_pro_defer_render_blocking_scripts', 20, 3 );
/**
 * Optional defer for scripts that commonly appear in Lighthouse as render-blocking.
 * Disabled by default because Elementor/jQuery deferring can worsen real LCP on some pages.
 */
function glass_pro_defer_render_blocking_scripts( string $tag, string $handle, string $src ): string {
	if ( is_admin() || false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, ' async' ) ) {
		return $tag;
	}
	if ( ! apply_filters( 'glass_pro/perf/defer_blocking_scripts', false ) ) {
		return $tag;
	}

	$defer_handles = (array) apply_filters( 'glass_pro/perf/defer_handles', [
		'jquery-core',
		'jquery',
		'elementor-webpack-runtime',
		'elementor-frontend-modules',
		'elementor-frontend',
		'elementor-pro-webpack-runtime',
		'elementor-pro-frontend',
		'elementor-frontend-js',
		'elementor-pro-frontend-js',
		'persian-elementor-datepicker',
		'persian-calendar-standalone',
		'datepicker-init',
	] );

	$match = in_array( $handle, $defer_handles, true );
	if ( ! $match && ( false !== strpos( $src, '/elementor/' ) || false !== strpos( $src, '/elementor-pro/' ) || false !== strpos( $src, '/persian-elementor/' ) ) ) {
		$match = true;
	}
	if ( ! $match ) {
		return $tag;
	}

	return str_replace( ' src', ' defer src', $tag );
}

add_filter( 'style_loader_tag', 'glass_pro_async_small_elementor_post_css', 20, 4 );
/**
 * Optional async loading for Elementor per-post CSS.
 * Disabled by default because delaying Elementor page CSS can increase LCP/CLS. Enable only after visual testing:
 */
function glass_pro_async_small_elementor_post_css( string $html, string $handle, string $href, string $media ): string {
	if ( is_admin() || ! apply_filters( 'glass_pro/perf/async_elementor_post_css', false ) ) {
		return $html;
	}
	$is_elementor_post_css = ( 0 === strpos( $handle, 'elementor-post-' ) ) || false !== strpos( $href, '/uploads/elementor/css/post-' );
	if ( ! $is_elementor_post_css || false !== strpos( $html, "media='print'" ) || false !== strpos( $html, 'media="print"' ) ) {
		return $html;
	}
	$async = str_replace( "rel='stylesheet'", "rel='stylesheet' media='print' onload=\"this.media='all';this.onload=null;\"", $html );
	if ( $async === $html ) {
		$async = str_replace( 'rel="stylesheet"', "rel=\"stylesheet\" media=\"print\" onload=\"this.media='all';this.onload=null;\"", $html );
	}
	return $async . '<noscript>' . $html . '</noscript>';
}

add_filter( 'the_content', 'glass_pro_lighthouse_image_attributes', 120 );
/**
 * Add missing width/height to content images where possible and prioritize the
 * first feature image for LCP. This targets Elementor/custom content images too.
 */
function glass_pro_lighthouse_image_attributes( string $content ): string {
	if ( is_admin() || false === strpos( $content, '<img' ) ) {
		return $content;
	}

	$seen_feature = false;
	$count = 0;
	return preg_replace_callback( '/<img\b[^>]*>/i', static function ( array $m ) use ( &$seen_feature, &$count ) {
		$img = $m[0];
		$count++;
		if ( $count > (int) apply_filters( 'glass_pro/perf/max_images_to_dimension', 20 ) ) {
			return $img;
		}

		$is_feature = ( false !== strpos( $img, 'gh-feature-img' ) || false !== strpos( $img, 'wp-post-image' ) );
		if ( $is_feature && ! $seen_feature ) {
			$seen_feature = true;
			if ( false === stripos( $img, 'fetchpriority=' ) ) {
				$img = preg_replace( '/<img\b/i', '<img fetchpriority="high"', $img, 1 );
			}
			if ( false === stripos( $img, 'loading=' ) ) {
				$img = preg_replace( '/<img\b/i', '<img loading="eager"', $img, 1 );
			}
		}

		if ( false !== stripos( $img, ' width=' ) && false !== stripos( $img, ' height=' ) ) {
			return $img;
		}
		if ( ! preg_match( '/\bsrc=["\']([^"\']+)["\']/i', $img, $src_match ) ) {
			return $img;
		}
		$src = html_entity_decode( $src_match[1] );
		$dim = glass_pro_get_image_dimensions_from_url( $src );
		if ( ! $dim ) {
			return $img;
		}
		if ( false === stripos( $img, ' width=' ) ) {
			$img = preg_replace( '/<img\b/i', '<img width="' . (int) $dim[0] . '"', $img, 1 );
		}
		if ( false === stripos( $img, ' height=' ) ) {
			$img = preg_replace( '/<img\b/i', '<img height="' . (int) $dim[1] . '"', $img, 1 );
		}
		return $img;
	}, $content );
}

function glass_pro_get_image_dimensions_from_url( string $url ): ?array {
	static $cache = [];
	if ( isset( $cache[ $url ] ) ) {
		return $cache[ $url ];
	}

	$cache[ $url ] = null;
	$clean_url = preg_replace( '/\.webp$/i', '', $url );
	if ( preg_match( '/-(\d{2,5})x(\d{2,5})\.(?:jpe?g|png|webp|avif)$/i', $clean_url, $m ) ) {
		$cache[ $url ] = [ (int) $m[1], (int) $m[2] ];
		return $cache[ $url ];
	}

	$attachment_id = attachment_url_to_postid( $clean_url );
	if ( $attachment_id ) {
		$meta = wp_get_attachment_metadata( $attachment_id );
		if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
			$cache[ $url ] = [ (int) $meta['width'], (int) $meta['height'] ];
		}
	}
	return $cache[ $url ];
}

/* ────────────────────────────────────────
   [PERF v5.10.3] Optional delay 3rd-party analytics scripts
   ──────────────────────────────────────── */
add_action( 'template_redirect', 'glass_pro_delay_third_party_buffer_start', 0 );
function glass_pro_delay_third_party_buffer_start(): void {
	if ( is_admin() || wp_doing_ajax() || is_feed() || ! apply_filters( 'glass_pro/perf/delay_third_party', false ) ) {
		return;
	}
	ob_start( 'glass_pro_delay_third_party_rewrite_html' );
}

function glass_pro_delay_third_party_rewrite_html( string $html ): string {
	if ( false === stripos( $html, '<script' ) ) {
		return $html;
	}
	$pattern = '#<script\b([^>]*)\bsrc=(?:"|\')([^"\']*(?:googletagmanager\.com/gtag/js|google-analytics\.com/analytics\.js|scripts\.clarity\.ms/|www\.clarity\.ms/tag/)[^"\']*)(?:"|\')([^>]*)>\s*</script>#i';
	return preg_replace_callback( $pattern, static function ( array $m ) {
		$src = esc_url( html_entity_decode( $m[2] ) );
		if ( ! $src ) {
			return $m[0];
		}
		return '<script type="text/plain" data-glass-delay="third-party" data-glass-delay-src="' . $src . '"></script>';
	}, $html );
}

add_action( 'wp_footer', 'glass_pro_delay_third_party_loader', 99 );
function glass_pro_delay_third_party_loader(): void {
	if ( is_admin() || ! apply_filters( 'glass_pro/perf/delay_third_party', false ) ) {
		return;
	}
	$timeout = (int) apply_filters( 'glass_pro/perf/delay_third_party_timeout', 4500 );
	?>
	<script <?php if ( function_exists( 'glass_pro_csp_nonce_attr' ) ) { glass_pro_csp_nonce_attr(); } ?>>
	(function(){
		'use strict';
		var loaded=false;
		function loadDelayed(){
			if(loaded){return;} loaded=true;
			var nodes=document.querySelectorAll('script[data-glass-delay="third-party"][data-glass-delay-src]');
			for(var i=0;i<nodes.length;i++){
				var s=document.createElement('script');
				s.src=nodes[i].getAttribute('data-glass-delay-src');
				s.async=true;
				document.head.appendChild(s);
			}
		}
		['click','scroll','keydown','touchstart','mousemove'].forEach(function(ev){window.addEventListener(ev,loadDelayed,{once:true,passive:true});});
		window.setTimeout(loadDelayed, <?php echo (int) $timeout; ?>);
	})();
	</script>
	<?php
}

/* ────────────────────────────────────────
   [PERF v5.10.5] Optional preload likely LCP image from current page content
   ──────────────────────────────────────── */
add_action( 'wp_head', 'glass_pro_preload_likely_lcp_image', 1 );
function glass_pro_preload_likely_lcp_image(): void {
	if ( is_admin() || ! apply_filters( 'glass_pro/perf/preload_lcp_content_image', false ) ) {
		return;
	}
	$post_id = get_queried_object_id();
	if ( ! $post_id || ! is_singular() ) {
		return;
	}
	$content = (string) get_post_field( 'post_content', $post_id );
	if ( false === stripos( $content, '<img' ) ) {
		return;
	}

	$src = '';
	if ( preg_match( '/<img\b(?=[^>]*\bclass=["\'][^"\']*(?:gh-feature-img|wp-post-image)[^"\']*["\'])[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i', $content, $m ) ) {
		$src = html_entity_decode( $m[1] );
	} elseif ( preg_match( '/<img\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i', $content, $m ) ) {
		$src = html_entity_decode( $m[1] );
	}
	if ( ! $src || ! preg_match( '#^https?://#i', $src ) ) {
		return;
	}
	printf( '<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n", esc_url( $src ) );
}

/* ────────────────────────────────────────
   [A11Y/BP v5.12.5] Protect WordPress i18n runtime from cache-plugin delay
   ──────────────────────────────────────── */
add_filter( 'script_loader_tag', 'glass_pro_no_delay_wp_i18n_runtime', 5, 3 );
function glass_pro_no_delay_wp_i18n_runtime( string $tag, string $handle, string $src ): string {
	$protected = [ 'wp-i18n', 'wp-hooks', 'wp-dom-ready' ];
	if ( in_array( $handle, $protected, true ) && false === strpos( $tag, 'data-no-defer' ) ) {
		$tag = str_replace( ' src', ' data-no-defer="1" data-no-delay="1" src', $tag );
	}
	return $tag;
}
