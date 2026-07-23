<?php
/**
 * SEO Enhancements / Rank Math Compatibility
 *
 * Adds non-duplicating schema enrichments for Rank Math,
 * image alt improvements and conservative heading normalization. URLs/canonicals are not changed.
 * Avoids adding invalid inLanguage to Organization/Person/ImageObject nodes.
 * Completes ImageObject metadata (license, creator, acquireLicensePage) for Google Images.
 * Completes Product AggregateOffer fields (lowPrice, highPrice, offerCount) for Product snippets.
 *
 * @package Alborz_Ghaleb
 * @since 5.12.17
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }


function glass_pro_schema_lang_slug(): string {
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language( 'slug' );
		if ( $lang ) { return (string) $lang; }
	}
	return substr( (string) get_locale(), 0, 2 ) ?: 'fa';
}

function glass_pro_schema_in_language(): string {
	if ( function_exists( 'pll_current_language' ) ) {
		$locale = pll_current_language( 'locale' );
		if ( $locale ) { return str_replace( '_', '-', (string) $locale ); }
	}
	return str_replace( '_', '-', get_locale() );
}

function glass_pro_schema_home_url_for_lang(): string {
	$lang = glass_pro_schema_lang_slug();
	if ( function_exists( 'pll_home_url' ) && $lang ) {
		$url = pll_home_url( $lang );
		if ( $url ) { return $url; }
	}
	return home_url( '/' );
}

function glass_pro_schema_site_name_for_lang(): string {
	$name = get_bloginfo( 'name' );
	if ( function_exists( 'pll__' ) ) {
		$translated = pll__( $name );
		if ( $translated ) { return (string) $translated; }
	}
	return (string) $name;
}

/* ────────────────────────────────────────
   Rank Math schema enrichment — no duplicate output
   ──────────────────────────────────────── */
add_filter( 'rank_math/json_ld', 'glass_pro_rankmath_schema_enrich', 20, 2 );
function glass_pro_rankmath_schema_enrich( $data, $jsonld = null ) {
	if ( ! is_array( $data ) ) { return $data; }

	// Add inLanguage only to Schema.org types that support it, to avoid validator warnings.
	$data = glass_pro_rankmath_schema_add_selective_language( $data );


	if ( is_singular( 'portfolio' ) ) {
		$post_id = get_the_ID();
		if ( $post_id && ! glass_pro_schema_has_type( $data, 'Product' ) ) {
			$product = glass_pro_schema_product_for_portfolio( $post_id );
			if ( $product ) {
				$data['glass_pro_product_' . $post_id] = $product;
			}
		}
	}

	if ( is_front_page() && ! glass_pro_schema_has_type( $data, 'LocalBusiness' ) && ! glass_pro_schema_has_type( $data, 'HomeAndConstructionBusiness' ) ) {
		$data['glass_pro_local_business'] = glass_pro_schema_local_business();
	}

		$faq = glass_pro_schema_faq_from_content();
		if ( $faq && ! glass_pro_schema_has_type( $data, 'FAQPage' ) ) {
			$data['glass_pro_faq'] = $faq;
		}

		// Rank Math may add inLanguage to nested Organization/Person/ImageObject/BreadcrumbList nodes.
		// Remove it from unsupported types to keep Schema.org validation clean.
		$data = glass_pro_schema_sanitize_inlanguage_recursive( $data );

		// Complete ImageObject metadata for Google Search Console Image Metadata warnings.
		$data = glass_pro_schema_enrich_image_objects( $data );

		// Complete AggregateOffer fields for Product snippets (lowPrice/highPrice/offerCount).
		$data = glass_pro_schema_enrich_product_offers( $data );

		// Complete incomplete PostalAddress nodes (GSC LocalBusiness optional fields).
		$data = glass_pro_schema_enrich_postal_addresses( $data );

		return $data;
}





function glass_pro_rankmath_schema_add_selective_language( array $data ): array {
	$in_language = glass_pro_schema_in_language();
	$allowed_types = (array) apply_filters( 'glass_pro/seo/inlanguage_allowed_types', [
		'WebPage',
		'Article',
		'BlogPosting',
		'NewsArticle',
		'WebSite',
		'FAQPage',
		'CollectionPage',
		'SearchResultsPage',
	] );

	foreach ( $data as $key => $node ) {
		if ( ! is_array( $node ) || isset( $node['inLanguage'] ) || empty( $node['@type'] ) ) {
			continue;
		}
		$types = (array) $node['@type'];
		if ( array_intersect( $types, $allowed_types ) ) {
			$data[ $key ]['inLanguage'] = $in_language;
		}
	}
	return $data;
}



function glass_pro_schema_sanitize_inlanguage_recursive( $node ) {
	$allowed_types = (array) apply_filters( 'glass_pro/seo/inlanguage_allowed_types', [
		'WebPage',
		'Article',
		'BlogPosting',
		'NewsArticle',
		'WebSite',
		'FAQPage',
		'CollectionPage',
		'SearchResultsPage',
	] );

	if ( ! is_array( $node ) ) {
		return $node;
	}

	if ( isset( $node['@type'] ) && isset( $node['inLanguage'] ) ) {
		$types = (array) $node['@type'];
		if ( ! array_intersect( $types, $allowed_types ) ) {
			unset( $node['inLanguage'] );
		}
	}

	foreach ( $node as $key => $value ) {
		if ( is_array( $value ) ) {
			$node[ $key ] = glass_pro_schema_sanitize_inlanguage_recursive( $value );
		}
	}

	return $node;
}

/* ────────────────────────────────────────
   Image Metadata (Google Images / Search Console)
   Completes missing license, creator, acquireLicensePage
   on every ImageObject emitted by Rank Math.
   ──────────────────────────────────────── */

/**
 * Default ImageObject license metadata for site-owned media.
 *
 * @return array{license:string,acquireLicensePage:string,creator:array,creditText:string,copyrightNotice:string}
 */
function glass_pro_schema_image_license_defaults(): array {
	$site_name = glass_pro_schema_site_name_for_lang();
	$year      = (string) gmdate( 'Y' );

	// Prefer real legal pages when they exist; fall back to contact.
	$license_url = glass_pro_schema_first_existing_url( [
		'/سیاست-حفظ-حریم-خصوصی/',
		'/privacy-policy/',
		'/privacy/',
		'/درباره-ما/',
		'/about/',
		'/contact/',
		'/contact-us/',
	] );

	$acquire_url = glass_pro_schema_first_existing_url( [
		'/contact/',
		'/contact-us/',
		'/تماس-با-ما/',
		'/درباره-ما/',
		'/about/',
	] );

	$defaults = [
		'license'            => $license_url,
		'acquireLicensePage' => $acquire_url,
		'creator'            => [
			'@type' => 'Organization',
			'name'  => $site_name,
			'url'   => glass_pro_schema_home_url_for_lang(),
		],
		'creditText'         => $site_name,
		'copyrightNotice'    => sprintf( '© %s %s', $year, $site_name ),
	];

	/**
	 * Filter default ImageObject license metadata.
	 *
	 * @param array $defaults Keys: license, acquireLicensePage, creator, creditText, copyrightNotice.
	 */
	return (array) apply_filters( 'glass_pro/schema/image_license_defaults', $defaults );
}

/**
 * Return the first path that resolves to a published page/post URL, else home.
 *
 * @param string[] $paths Absolute path candidates (with leading slash).
 */
function glass_pro_schema_first_existing_url( array $paths ): string {
	static $cache = [];

	foreach ( $paths as $path ) {
		$path = '/' . ltrim( (string) $path, '/' );
		if ( isset( $cache[ $path ] ) ) {
			if ( $cache[ $path ] ) {
				return $cache[ $path ];
			}
			continue;
		}

		$resolved = '';

		// 1) Page slug / hierarchical path.
		$page = get_page_by_path( trim( $path, '/' ) );
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			$permalink = get_permalink( $page );
			$resolved  = $permalink ? (string) $permalink : '';
		}

		// 2) Resolve via pretty-permalink → post ID (works better for non-ASCII slugs).
		if ( ! $resolved ) {
			$candidate = home_url( $path );
			$post_id   = function_exists( 'url_to_postid' ) ? (int) url_to_postid( $candidate ) : 0;
			if ( $post_id > 0 ) {
				$permalink = get_permalink( $post_id );
				$resolved  = $permalink ? (string) $permalink : '';
			}
		}

		// 3) Last internal fallback only for well-known English contact paths.
		if ( ! $resolved ) {
			$slug = trim( $path, '/' );
			if ( in_array( $slug, [ 'contact', 'contact-us' ], true ) ) {
				$resolved = (string) home_url( $path );
			}
		}

		$cache[ $path ] = $resolved;
		if ( $resolved ) {
			return $resolved;
		}
	}

	// Absolute last resort.
	return glass_pro_schema_home_url_for_lang();
}

/**
 * Whether a schema node is (or includes) ImageObject.
 *
 * @param array $node Schema node.
 */
function glass_pro_schema_node_is_image_object( array $node ): bool {
	if ( empty( $node['@type'] ) ) {
		return false;
	}
	$types = (array) $node['@type'];
	return in_array( 'ImageObject', $types, true );
}

/**
 * Whether a URL is clearly third-party (do not claim site license).
 *
 * @param string $url Image URL.
 */
function glass_pro_schema_is_external_image_url( string $url ): bool {
	if ( '' === $url ) {
		return false;
	}
	// Gravatar / social CDNs etc.
	if ( preg_match( '#//(secure\.)?gravatar\.com/|#i', $url ) ) {
		return true;
	}
	$host = wp_parse_url( $url, PHP_URL_HOST );
	$site = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	if ( ! $host || ! $site ) {
		return false;
	}
	$host = strtolower( preg_replace( '/^www\./', '', (string) $host ) );
	$site = strtolower( preg_replace( '/^www\./', '', (string) $site ) );
	return $host !== $site;
}

/**
 * Enrich a single ImageObject node with license metadata when missing.
 *
 * @param array $node ImageObject node.
 * @return array
 */
function glass_pro_schema_complete_image_object( array $node ): array {
	if ( ! glass_pro_schema_node_is_image_object( $node ) ) {
		return $node;
	}

	// Never claim ownership of external avatars/CDNs.
	$image_url = '';
	foreach ( [ 'contentUrl', 'url' ] as $key ) {
		if ( ! empty( $node[ $key ] ) && is_string( $node[ $key ] ) ) {
			$image_url = $node[ $key ];
			break;
		}
	}
	if ( glass_pro_schema_is_external_image_url( $image_url ) ) {
		return $node;
	}

	$meta = glass_pro_schema_image_license_defaults();

	// contentUrl is preferred by Google Images; mirror url when missing.
	if ( empty( $node['contentUrl'] ) && ! empty( $node['url'] ) && is_string( $node['url'] ) ) {
		$node['contentUrl'] = $node['url'];
	}

	if ( empty( $node['license'] ) && ! empty( $meta['license'] ) ) {
		$node['license'] = esc_url_raw( (string) $meta['license'] );
	}
	if ( empty( $node['acquireLicensePage'] ) && ! empty( $meta['acquireLicensePage'] ) ) {
		$node['acquireLicensePage'] = esc_url_raw( (string) $meta['acquireLicensePage'] );
	}
	if ( empty( $node['creator'] ) && ! empty( $meta['creator'] ) && is_array( $meta['creator'] ) ) {
		$node['creator'] = $meta['creator'];
	}
	if ( empty( $node['creditText'] ) && ! empty( $meta['creditText'] ) ) {
		$node['creditText'] = sanitize_text_field( (string) $meta['creditText'] );
	}
	if ( empty( $node['copyrightNotice'] ) && ! empty( $meta['copyrightNotice'] ) ) {
		$node['copyrightNotice'] = sanitize_text_field( (string) $meta['copyrightNotice'] );
	}

	/**
	 * Filter a completed ImageObject node.
	 *
	 * @param array $node Completed node.
	 * @param array $meta Defaults used.
	 */
	return (array) apply_filters( 'glass_pro/schema/image_object', $node, $meta );
}

/**
 * Walk Rank Math JSON-LD graph and complete every ImageObject.
 *
 * @param mixed $data Rank Math schema data.
 * @return mixed
 */
function glass_pro_schema_enrich_image_objects( $data ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}

	// Top-level associative map of nodes (Rank Math style).
	foreach ( $data as $key => $node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}
		if ( glass_pro_schema_node_is_image_object( $node ) ) {
			$data[ $key ] = glass_pro_schema_complete_image_object( $node );
			continue;
		}
		// Nested ImageObject values (e.g. Organization.logo, Person.image, primaryImageOfPage).
		$data[ $key ] = glass_pro_schema_enrich_image_objects_nested( $node );
	}

	return $data;
}

/**
 * Recursively enrich nested ImageObject nodes inside a schema branch.
 *
 * @param array $node Schema branch.
 * @return array
 */
function glass_pro_schema_enrich_image_objects_nested( array $node ): array {
	if ( glass_pro_schema_node_is_image_object( $node ) ) {
		return glass_pro_schema_complete_image_object( $node );
	}

	foreach ( $node as $key => $value ) {
		if ( ! is_array( $value ) ) {
			continue;
		}
		// List of nodes.
		if ( array_is_list( $value ) ) {
			foreach ( $value as $i => $child ) {
				if ( is_array( $child ) ) {
					$node[ $key ][ $i ] = glass_pro_schema_enrich_image_objects_nested( $child );
				}
			}
			continue;
		}
		$node[ $key ] = glass_pro_schema_enrich_image_objects_nested( $value );
	}

	return $node;
}

/* ────────────────────────────────────────
   Product / AggregateOffer (Google Product snippets)
   Completes missing lowPrice, highPrice, offerCount.
   Prefer converting incomplete AggregateOffer → Offer
   when only a single price is known.
   ──────────────────────────────────────── */

/**
 * Default price band used when AggregateOffer has no prices.
 * Overridable via filter or post meta:
 *   glass_pro_agg_low_price, glass_pro_agg_high_price, glass_pro_agg_offer_count
 *   glass_pro_product_price (single Offer price fallback)
 *
 * @return array{lowPrice:?string,highPrice:?string,offerCount:?string,price:?string,priceCurrency:string}
 */
function glass_pro_schema_aggregate_offer_defaults(): array {
	$post_id = get_queried_object_id();
	$lang    = glass_pro_schema_lang_slug();
	$currency = ( 'fa' === substr( (string) $lang, 0, 2 ) ) ? 'IRR' : 'USD';

	$low   = $post_id ? get_post_meta( $post_id, 'glass_pro_agg_low_price', true ) : '';
	$high  = $post_id ? get_post_meta( $post_id, 'glass_pro_agg_high_price', true ) : '';
	$count = $post_id ? get_post_meta( $post_id, 'glass_pro_agg_offer_count', true ) : '';
	$price = $post_id ? get_post_meta( $post_id, 'glass_pro_product_price', true ) : '';

	// Also accept Rank Math / common custom fields if present.
	if ( $post_id && '' === (string) $price ) {
		foreach ( [ 'rank_math_rich_snippet_product_price', 'product_price', '_price', 'price' ] as $meta_key ) {
			$maybe = get_post_meta( $post_id, $meta_key, true );
			if ( '' !== (string) $maybe && is_numeric( preg_replace( '/[^\d.]/', '', (string) $maybe ) ) ) {
				$price = $maybe;
				break;
			}
		}
	}

	$normalize = static function ( $value ): string {
		$value = is_string( $value ) || is_numeric( $value ) ? (string) $value : '';
		$value = preg_replace( '/[^\d.]/', '', $value );
		return $value ? $value : '';
	};

	$low   = $normalize( $low );
	$high  = $normalize( $high );
	$price = $normalize( $price );
	$count = $normalize( $count );

	// If only a single product price exists, use it as both low and high.
	if ( $price && ! $low && ! $high ) {
		$low  = $price;
		$high = $price;
		if ( ! $count ) {
			$count = '1';
		}
	}

	$defaults = [
		'lowPrice'      => $low ?: null,
		'highPrice'     => $high ?: null,
		'offerCount'    => $count ?: null,
		'price'         => $price ?: null,
		'priceCurrency' => $currency,
	];

	/**
	 * Filter AggregateOffer / Product price defaults for schema completion.
	 *
	 * @param array $defaults lowPrice, highPrice, offerCount, price, priceCurrency.
	 * @param int   $post_id  Current post ID (0 if none).
	 */
	return (array) apply_filters( 'glass_pro/schema/aggregate_offer_defaults', $defaults, (int) $post_id );
}

/**
 * Whether node @type is (or includes) $type.
 *
 * @param array  $node Schema node.
 * @param string $type Type name.
 */
function glass_pro_schema_node_has_type( array $node, string $type ): bool {
	if ( empty( $node['@type'] ) ) {
		return false;
	}
	$types = (array) $node['@type'];
	return in_array( $type, $types, true );
}

/**
 * Complete a single AggregateOffer / Offer node.
 *
 * @param array $offer Offer node.
 * @return array
 */
function glass_pro_schema_complete_offer_node( array $offer ): array {
	$defaults = glass_pro_schema_aggregate_offer_defaults();
	$is_agg   = glass_pro_schema_node_has_type( $offer, 'AggregateOffer' );
	$is_offer = glass_pro_schema_node_has_type( $offer, 'Offer' );

	if ( ! $is_agg && ! $is_offer ) {
		return $offer;
	}

	if ( empty( $offer['priceCurrency'] ) && ! empty( $defaults['priceCurrency'] ) ) {
		$offer['priceCurrency'] = (string) $defaults['priceCurrency'];
	}

	// Incomplete AggregateOffer with a known single price → convert to Offer.
	if ( $is_agg ) {
		$has_band = ! empty( $offer['lowPrice'] ) || ! empty( $offer['highPrice'] );
		$single   = ! empty( $offer['price'] ) ? (string) $offer['price'] : (string) ( $defaults['price'] ?? '' );

		if ( ! $has_band && $single ) {
			$offer['@type'] = 'Offer';
			$offer['price'] = preg_replace( '/[^\d.]/', '', $single );
			unset( $offer['lowPrice'], $offer['highPrice'], $offer['offerCount'] );
			$is_agg   = false;
			$is_offer = true;
		}
	}

	if ( $is_agg ) {
		if ( empty( $offer['lowPrice'] ) && ! empty( $defaults['lowPrice'] ) ) {
			$offer['lowPrice'] = (string) $defaults['lowPrice'];
		}
		if ( empty( $offer['highPrice'] ) && ! empty( $defaults['highPrice'] ) ) {
			$offer['highPrice'] = (string) $defaults['highPrice'];
		}
		// If only one bound exists, mirror it.
		if ( ! empty( $offer['lowPrice'] ) && empty( $offer['highPrice'] ) ) {
			$offer['highPrice'] = (string) $offer['lowPrice'];
		}
		if ( ! empty( $offer['highPrice'] ) && empty( $offer['lowPrice'] ) ) {
			$offer['lowPrice'] = (string) $offer['highPrice'];
		}
		if ( empty( $offer['offerCount'] ) ) {
			if ( ! empty( $defaults['offerCount'] ) ) {
				$offer['offerCount'] = (string) $defaults['offerCount'];
			} elseif ( ! empty( $offer['lowPrice'] ) || ! empty( $offer['highPrice'] ) ) {
				$offer['offerCount'] = '1';
			}
		}

		// Still incomplete and no trustworthy prices → drop AggregateOffer type to plain Offer without fake prices.
		// Google prefers missing optional Product rich result over invalid/fake AggregateOffer.
		if ( empty( $offer['lowPrice'] ) && empty( $offer['highPrice'] ) ) {
			// Convert to Offer without price (valid for non-critical; avoids fake prices).
			// Keep availability/url/seller. Remove AggregateOffer-only expectation fields.
			$offer['@type'] = 'Offer';
			unset( $offer['lowPrice'], $offer['highPrice'], $offer['offerCount'] );
			$is_agg   = false;
			$is_offer = true;
		}
	}

	if ( $is_offer && empty( $offer['price'] ) && ! empty( $defaults['price'] ) ) {
		$offer['price'] = (string) $defaults['price'];
	}

	/**
	 * Filter a completed Offer / AggregateOffer node.
	 *
	 * @param array $offer    Completed node.
	 * @param array $defaults Defaults used.
	 */
	return (array) apply_filters( 'glass_pro/schema/offer_node', $offer, $defaults );
}

/**
 * Recursively enrich Product.offers and AggregateOffer nodes.
 *
 * @param mixed $data Rank Math schema data.
 * @return mixed
 */
function glass_pro_schema_enrich_product_offers( $data ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}

	foreach ( $data as $key => $node ) {
		if ( is_array( $node ) ) {
			$data[ $key ] = glass_pro_schema_enrich_product_offers_nested( $node );
		}
	}

	return $data;
}

/**
 * Nested walker for Product offers enrichment.
 *
 * @param array $node Schema branch.
 * @return array
 */
function glass_pro_schema_enrich_product_offers_nested( array $node ): array {
	// Complete this node if it is an offer type.
	if ( glass_pro_schema_node_has_type( $node, 'AggregateOffer' ) || glass_pro_schema_node_has_type( $node, 'Offer' ) ) {
		// Only auto-complete when it looks like a product offer (has priceCurrency/availability/url/seller or parent will call via offers key).
		$node = glass_pro_schema_complete_offer_node( $node );
	}

	// Explicit Product.offers handling (string URL offers are left alone).
	if ( glass_pro_schema_node_has_type( $node, 'Product' ) && isset( $node['offers'] ) && is_array( $node['offers'] ) ) {
		if ( array_is_list( $node['offers'] ) ) {
			foreach ( $node['offers'] as $i => $offer ) {
				if ( is_array( $offer ) ) {
					$node['offers'][ $i ] = glass_pro_schema_complete_offer_node( $offer );
				}
			}
		} else {
			$node['offers'] = glass_pro_schema_complete_offer_node( $node['offers'] );
		}
	}

	foreach ( $node as $key => $value ) {
		if ( ! is_array( $value ) ) {
			continue;
		}
		// Product.offers already normalized above; still walk children of other keys.
		if ( 'offers' === $key && glass_pro_schema_node_has_type( $node, 'Product' ) ) {
			continue;
		}
		if ( array_is_list( $value ) ) {
			foreach ( $value as $i => $child ) {
				if ( is_array( $child ) ) {
					$node[ $key ][ $i ] = glass_pro_schema_enrich_product_offers_nested( $child );
				}
			}
			continue;
		}
		$node[ $key ] = glass_pro_schema_enrich_product_offers_nested( $value );
	}

	return $node;
}

/* ────────────────────────────────────────
   Catch Product / AggregateOffer JSON-LD outside Rank Math graph
   (custom schema in content, extra <script> blocks, etc.)
   ──────────────────────────────────────── */
add_action( 'template_redirect', 'glass_pro_schema_product_offers_buffer_start', 0 );
function glass_pro_schema_product_offers_buffer_start(): void {
	if ( is_admin() || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}
	if ( ! apply_filters( 'glass_pro/seo/buffer_fix_product_offers', true ) ) {
		return;
	}
	ob_start( 'glass_pro_schema_product_offers_buffer_callback' );
}

/**
 * Rewrite incomplete AggregateOffer nodes inside ld+json script tags.
 *
 * @param string $html Full page HTML.
 * @return string
 */
function glass_pro_schema_product_offers_buffer_callback( string $html ): string {
	if ( '' === $html ) {
		return $html;
	}

	$needs_offer_fix   = false !== stripos( $html, 'AggregateOffer' );
	$needs_address_fix = false !== stripos( $html, 'PostalAddress' );
	if ( ! $needs_offer_fix && ! $needs_address_fix ) {
		return $html;
	}

	return (string) preg_replace_callback(
		'/<script\b([^>]*type=(["\'])application\/ld\+json\2[^>]*)>(.*?)<\/script>/is',
		static function ( array $m ) use ( $needs_offer_fix, $needs_address_fix ) {
			$raw = trim( $m[3] );
			if ( '' === $raw ) {
				return $m[0];
			}
			$touch = ( $needs_offer_fix && false !== stripos( $raw, 'AggregateOffer' ) )
				|| ( $needs_address_fix && false !== stripos( $raw, 'PostalAddress' ) );
			if ( ! $touch ) {
				return $m[0];
			}
			$data = json_decode( $raw, true );
			if ( ! is_array( $data ) ) {
				return $m[0];
			}

			$fix_tree = static function ( $node ) {
				if ( ! is_array( $node ) ) {
					return $node;
				}
				if ( array_is_list( $node ) ) {
					$out = [];
					foreach ( $node as $item ) {
						$out[] = is_array( $item )
							? glass_pro_schema_enrich_postal_addresses(
								glass_pro_schema_enrich_product_offers_nested( $item )
							)
							: $item;
					}
					return $out;
				}
				$node = glass_pro_schema_enrich_product_offers_nested( $node );
				$node = glass_pro_schema_enrich_postal_addresses( $node );
				return $node;
			};

			$fixed = $fix_tree( $data );
			$json  = wp_json_encode( $fixed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			if ( ! $json ) {
				return $m[0];
			}
			return '<script' . $m[1] . '>' . $json . '</script>';
		},
		$html
	);
}

function glass_pro_schema_has_type( array $data, string $type ): bool {
	foreach ( $data as $node ) {
		if ( ! is_array( $node ) || empty( $node['@type'] ) ) { continue; }
		$node_type = $node['@type'];
		if ( is_array( $node_type ) && in_array( $type, $node_type, true ) ) { return true; }
		if ( $node_type === $type ) { return true; }
	}
	return false;
}

function glass_pro_schema_product_for_portfolio( int $post_id ): array {
	$title = get_the_title( $post_id );
	$desc  = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 35, '…' );
	$image = get_the_post_thumbnail_url( $post_id, 'large' );
	$price = absint( get_post_meta( $post_id, 'portfolio_price', true ) );
	$lang  = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post_id ) : glass_pro_schema_lang_slug();
	$currency = ( 'fa' === substr( (string) $lang, 0, 2 ) ) ? 'IRR' : 'USD';
	$availability = 'https://schema.org/InStock';
	$state = get_post_meta( $post_id, 'portfolio_ad_state', true );
	if ( 'sold' === $state ) { $availability = 'https://schema.org/SoldOut'; }
	if ( 'expired' === $state ) { $availability = 'https://schema.org/Discontinued'; }

	$old_price = absint( get_post_meta( $post_id, 'portfolio_old_price', true ) );

	// Single price → Offer; price range (old > current) → AggregateOffer with required fields.
	if ( $price > 0 && $old_price > $price ) {
		$offers = [
			'@type'         => 'AggregateOffer',
			'url'           => get_permalink( $post_id ),
			'priceCurrency' => $currency,
			'lowPrice'      => (string) $price,
			'highPrice'     => (string) $old_price,
			'offerCount'    => '1',
			'availability'  => $availability,
			'itemCondition' => 'https://schema.org/UsedCondition',
			'seller'        => [ '@type' => 'Organization', 'name' => glass_pro_schema_site_name_for_lang() ],
		];
	} else {
		$offers = [
			'@type'         => 'Offer',
			'url'           => get_permalink( $post_id ),
			'priceCurrency' => $currency,
			'availability'  => $availability,
			'itemCondition' => 'https://schema.org/UsedCondition',
			'seller'        => [ '@type' => 'Organization', 'name' => glass_pro_schema_site_name_for_lang() ],
		];
		if ( $price > 0 ) {
			$offers['price'] = (string) $price;
		}
	}

	$product = [
		'@type'       => 'Product',
		'@id'         => get_permalink( $post_id ) . '#product',
		'name'        => wp_strip_all_tags( $title ),
		'description' => wp_strip_all_tags( $desc ),
		'url'         => get_permalink( $post_id ),
		'category'    => glass_pro_primary_term_name( $post_id, 'themsah_theme_type' ),
		'brand'       => [ '@type' => 'Brand', 'name' => glass_pro_schema_site_name_for_lang() ],
		'sku'         => 'portfolio-' . $post_id,
		'offers'      => $offers,
	];
	if ( $image ) { $product['image'] = [ $image ]; }
	return array_filter( $product );
}

function glass_pro_primary_term_name( int $post_id, string $taxonomy ): string {
	$terms = get_the_terms( $post_id, $taxonomy );
	return ( $terms && ! is_wp_error( $terms ) ) ? (string) $terms[0]->name : '';
}

/**
 * Canonical business postal address for schema.
 * Prefer structured theme_mod / options; fall back to known site defaults.
 *
 * @return array PostalAddress node (without empty values).
 */
function glass_pro_schema_postal_address(): array {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}

	$footer_raw = function_exists( 'glass_footer_theme_mod_i18n' )
		? (string) glass_footer_theme_mod_i18n( 'fl_footer_address', '' )
		: (string) get_theme_mod( 'fl_footer_address', '' );

	// Optional structured customizer/options (if set later).
	$street   = (string) get_theme_mod( 'glass_pro_address_street', get_option( 'glass_pro_address_street', '' ) );
	$locality = (string) get_theme_mod( 'glass_pro_address_locality', get_option( 'glass_pro_address_locality', '' ) );
	$region   = (string) get_theme_mod( 'glass_pro_address_region', get_option( 'glass_pro_address_region', '' ) );
	$postal  = (string) get_theme_mod( 'glass_pro_address_postal', get_option( 'glass_pro_address_postal', '' ) );
	$country  = (string) get_theme_mod( 'glass_pro_address_country', get_option( 'glass_pro_address_country', 'IR' ) );

	// Site defaults matching the complete LocalBusiness block already used on the homepage.
	$defaults = [
		'streetAddress'   => 'بلوار بهشت سکینه، چهارم شرقی، پلاک ۸۰، کمالشهر',
		'addressLocality' => 'کرج',
		'addressRegion'   => 'البرز',
		'postalCode'      => '3197963457',
		'addressCountry'  => 'IR',
	];

	if ( '' === $street && $footer_raw ) {
		// If footer is a single-line blob, keep it as street; structured fields still come from defaults/options.
		$street = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $footer_raw ) ) );
	}

	$address = [
		'@type'           => 'PostalAddress',
		'streetAddress'   => $street !== '' ? $street : $defaults['streetAddress'],
		'addressLocality' => $locality !== '' ? $locality : $defaults['addressLocality'],
		'addressRegion'   => $region !== '' ? $region : $defaults['addressRegion'],
		'postalCode'      => $postal !== '' ? $postal : $defaults['postalCode'],
		'addressCountry'  => $country !== '' ? $country : $defaults['addressCountry'],
	];

	// Normalize country to ISO-ish code when possible.
	$cc = strtoupper( trim( (string) $address['addressCountry'] ) );
	if ( in_array( $cc, [ 'IRAN', 'IRN', 'ایران' ], true ) || false !== strpos( (string) $address['addressCountry'], 'ایران' ) ) {
		$address['addressCountry'] = 'IR';
	}

	/**
	 * Filter the canonical PostalAddress used across schema enrichments.
	 *
	 * @param array $address PostalAddress node.
	 */
	$cached = (array) apply_filters( 'glass_pro/schema/postal_address', $address );
	return $cached;
}

/**
 * Fill missing fields on a PostalAddress node.
 *
 * @param array $node PostalAddress or empty address shell.
 * @return array
 */
function glass_pro_schema_complete_postal_address_node( array $node ): array {
	$defaults = glass_pro_schema_postal_address();
	if ( empty( $node['@type'] ) ) {
		$node['@type'] = 'PostalAddress';
	}

	foreach ( [ 'streetAddress', 'addressLocality', 'addressRegion', 'postalCode', 'addressCountry' ] as $field ) {
		$val = isset( $node[ $field ] ) ? trim( (string) $node[ $field ] ) : '';
		if ( '' === $val && ! empty( $defaults[ $field ] ) ) {
			$node[ $field ] = $defaults[ $field ];
		}
	}

	// If street is a long blob that already contains city/region, still keep structured fields for GSC.
	return $node;
}

/**
 * Recursively complete PostalAddress nodes in Rank Math / schema graphs.
 *
 * @param mixed $data Schema data.
 * @return mixed
 */
function glass_pro_schema_enrich_postal_addresses( $data ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}

	if ( glass_pro_schema_node_has_type( $data, 'PostalAddress' ) ) {
		return glass_pro_schema_complete_postal_address_node( $data );
	}

	foreach ( $data as $key => $value ) {
		if ( ! is_array( $value ) ) {
			continue;
		}
		// Common key "address" that is an empty/partial PostalAddress.
		if ( 'address' === $key ) {
			if ( array_is_list( $value ) ) {
				foreach ( $value as $i => $child ) {
					if ( is_array( $child ) ) {
						$data[ $key ][ $i ] = glass_pro_schema_enrich_postal_addresses( $child );
					}
				}
			} else {
				// Empty object {"@type":"PostalAddress"} or partial fields.
				if ( empty( $value ) || glass_pro_schema_node_has_type( $value, 'PostalAddress' ) || ! isset( $value['@type'] ) ) {
					$base = $value;
					if ( empty( $base['@type'] ) ) {
						$base['@type'] = 'PostalAddress';
					}
					$data[ $key ] = glass_pro_schema_complete_postal_address_node( $base );
				} else {
					$data[ $key ] = glass_pro_schema_enrich_postal_addresses( $value );
				}
			}
			continue;
		}

		if ( array_is_list( $value ) ) {
			foreach ( $value as $i => $child ) {
				if ( is_array( $child ) ) {
					$data[ $key ][ $i ] = glass_pro_schema_enrich_postal_addresses( $child );
				}
			}
			continue;
		}

		$data[ $key ] = glass_pro_schema_enrich_postal_addresses( $value );
	}

	return $data;
}

function glass_pro_schema_local_business(): array {
	$logo_id = get_theme_mod( 'custom_logo' );
	$logo = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	$phone = function_exists( 'glass_pro_contact' ) ? glass_pro_contact( 'phone_sales_1' ) : ( function_exists( 'glass_footer_theme_mod_i18n' ) ? glass_footer_theme_mod_i18n( 'fl_footer_phone', '' ) : get_theme_mod( 'fl_footer_phone', '' ) );
	$same_as = [];
	if ( function_exists( 'glass_pro_contact' ) ) {
		foreach ( [ 'telegram_url', 'instagram_url', 'whatsapp_catalog' ] as $key ) {
			$url = glass_pro_contact( $key );
			if ( $url ) { $same_as[] = esc_url_raw( $url ); }
		}
	}
	$node = [
		'@type'      => 'HomeAndConstructionBusiness',
		'@id'        => trailingslashit( glass_pro_schema_home_url_for_lang() ) . '#localbusiness',
		'name'       => glass_pro_schema_site_name_for_lang(),
		'url'        => glass_pro_schema_home_url_for_lang(),
		'telephone'  => $phone,
		'priceRange' => '$$-$$$',
		'areaServed' => [ '@type' => 'Country', 'name' => 'Iran' ],
		'sameAs'     => array_values( array_unique( array_filter( $same_as ) ) ),
		'address'    => glass_pro_schema_postal_address(),
	];
	if ( $logo ) { $node['image'] = $logo; $node['logo'] = $logo; }
	return array_filter( $node );
}

function glass_pro_schema_faq_from_content(): ?array {
	if ( ! is_singular() || ! apply_filters( 'glass_pro/seo/auto_faq_schema', false ) ) { return null; }
	$content = get_post_field( 'post_content', get_the_ID() );
	if ( false === stripos( $content, 'glass-faq' ) && false === stripos( $content, 'schema-faq' ) ) { return null; }
	if ( ! preg_match_all( '/<h[2-4][^>]*>(.*?)<\/h[2-4]>\s*<p[^>]*>(.*?)<\/p>/is', $content, $m, PREG_SET_ORDER ) ) { return null; }
	$items = [];
	foreach ( $m as $row ) {
		$q = trim( wp_strip_all_tags( $row[1] ) );
		$a = trim( wp_strip_all_tags( $row[2] ) );
		if ( $q && $a && ( str_contains( $q, '?' ) || str_contains( $q, '؟' ) ) ) {
			$items[] = [ '@type' => 'Question', 'name' => $q, 'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $a ] ];
		}
	}
	return $items ? [ '@type' => 'FAQPage', '@id' => get_permalink() . '#faq', 'inLanguage' => glass_pro_schema_in_language(), 'mainEntity' => $items ] : null;
}

/* ────────────────────────────────────────
   Image SEO: alt text fallback for all WP images + content images
   ──────────────────────────────────────── */
add_filter( 'wp_get_attachment_image_attributes', 'glass_pro_seo_image_alt_attributes', 20, 3 );
function glass_pro_seo_image_alt_attributes( array $attr, $attachment, $size ): array {
	if ( empty( $attr['alt'] ) ) {
		$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
		if ( ! $alt ) { $alt = get_the_title( $attachment->ID ); }
		if ( ! $alt && is_singular() ) { $alt = get_the_title(); }
		$attr['alt'] = glass_pro_seo_alt_text( (string) $alt );
	}
	return $attr;
}

add_filter( 'the_content', 'glass_pro_seo_content_image_alt', 130 );
function glass_pro_seo_content_image_alt( string $content ): string {
	if ( is_admin() || false === strpos( $content, '<img' ) ) { return $content; }
	return preg_replace_callback( '/<img\b([^>]*)>/i', static function( array $m ) {
		$tag = $m[0];
		if ( preg_match( '/\balt=["\']([^"\']*)["\']/i', $tag, $am ) && trim( $am[1] ) !== '' ) {
			return $tag;
		}
		$alt = glass_pro_seo_alt_text( get_the_title() );
		if ( preg_match( '/\balt=["\'][^"\']*["\']/i', $tag ) ) {
			return preg_replace( '/\balt=["\'][^"\']*["\']/i', 'alt="' . esc_attr( $alt ) . '"', $tag );
		}
		return preg_replace( '/<img\b/i', '<img alt="' . esc_attr( $alt ) . '"', $tag, 1 );
	}, $content );
}

function glass_pro_seo_alt_text( string $base ): string {
	$base = trim( wp_strip_all_tags( $base ) );
	$site = get_bloginfo( 'name' );
	if ( '' === $base ) { $base = $site; }
	// Keep short and descriptive; avoid stuffing.
	return mb_substr( $base, 0, 120 );
}

/* ────────────────────────────────────────
   Conservative heading normalization for known template/content classes
   ──────────────────────────────────────── */
add_filter( 'the_content', 'glass_pro_seo_normalize_known_headings', 140 );
function glass_pro_seo_normalize_known_headings( string $content ): string {
	if ( is_admin() || ! apply_filters( 'glass_pro/seo/normalize_headings', true ) ) { return $content; }
	// These h4 classes were reported by Lighthouse as skipped heading levels in Elementor content.
	$content = preg_replace_callback(
		'/<h4([^>]*class=["\'][^"\']*(?:gh-product-title|gh-glow-title|gh-blog-title|gh-contact-title)[^"\']*["\'][^>]*)>(.*?)<\/h4>/is',
		static function ( array $m ) {
			return '<h3' . $m[1] . '>' . $m[2] . '</h3>';
		},
		$content
	);
	// Avoid duplicate H1 inside content when the theme template already prints one.
	if ( is_singular() ) {
		$content = preg_replace_callback(
			'/<h1\b([^>]*)>(.*?)<\/h1>/is',
			static function ( array $m ) {
				return '<h2' . $m[1] . '>' . $m[2] . '</h2>';
			},
			$content
		);
	}
	return $content;
}

/* ════════════════════════════════════════════════════════════════
   [SEO CANONICAL FIX v5.15.4] حل خطاهای ابزار Seobility
   ۱. جلوگیری از چاپ تگ کنونیکال تکراری (More than one canonical link)
   ۲. اصلاح مبنای صفحه‌بندی فارسی از /page/ به /صفحه/ در کنونیکال Rank Math
   ۳. ریدایرکت ۳۰۱ آدرس‌های /page/ به /صفحه/ جهت رفع خطای 404 (Problematic status code)
   ════════════════════════════════════════════════════════════════ */

// ۱. جلوگیری از تداخل و تکرار تگ کنونیکال بین وردپرس و افزونه‌های سئو
add_action( 'wp_head', 'glass_pro_prevent_duplicate_canonical', 1 );
function glass_pro_prevent_duplicate_canonical(): void {
	if ( class_exists( 'RankMath' ) || defined( 'WPSEO_VERSION' ) ) {
		remove_action( 'wp_head', 'rel_canonical' );
	}
}

// ۲. اصلاح کلمه /page/ به /صفحه/ در خروجی کنونیکال افزونه Rank Math و وردپرس
add_filter( 'rank_math/frontend/canonical', 'glass_pro_normalize_pagination_canonical' );
add_filter( 'get_canonical_url', 'glass_pro_normalize_pagination_canonical' );
function glass_pro_normalize_pagination_canonical( $canonical ) {
	if ( empty( $canonical ) || ! is_string( $canonical ) ) {
		return $canonical;
	}
	// Category routing is managed separately; never mutate its canonical base.
	if ( function_exists( 'glass_category_route_is_request' ) && glass_category_route_is_request() ) {
		return $canonical;
	}
	if ( false !== strpos( $canonical, '/page/' ) ) {
		$canonical = preg_replace( '#/page/([0-9]+)/?#', '/صفحه/$1/', $canonical );
	}
	return $canonical;
}

// ۳. ریدایرکت ۳۰۱ خودکار درخواست‌های /page/ به /صفحه/ جهت حل خطای 404 خزنده‌ها
add_action( 'template_redirect', 'glass_pro_redirect_page_to_safhe', 1 );
function glass_pro_redirect_page_to_safhe(): void {
	if ( is_admin() || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) ) {
		return;
	}
	$uri = $_SERVER['REQUEST_URI'] ?? '';
	if ( function_exists( 'glass_category_route_is_request' ) && glass_category_route_is_request( (string) $uri ) ) {
		return;
	}
	if ( false !== strpos( $uri, '/page/' ) && ! is_page() ) {
		$new_uri = preg_replace( '#/page/([0-9]+)#', '/صفحه/$1', $uri );
		if ( $new_uri !== $uri ) {
			wp_safe_redirect( home_url( $new_uri ), 301 );
			exit;
		}
	}
}


/* ────────────────────────────────────────
   [NEW v8.6.8] Stage 49 REDO CLEAN V2 - Double-Encoding Fix Only - SAFE FOR CATEGORY
   فقط %2520 → %20 و %20 → - برای SEO، بدون www/https، بدون loop
   کاملا Skip برای هر URL شامل بلاگ یا دسته-بندی تا 404 نشه
   ──────────────────────────────────────── */
add_action( 'template_redirect', 'glass_pro_fix_double_encoding_clean_v2', 0 );
function glass_pro_fix_double_encoding_clean_v2(): void {
	if ( is_admin() || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return; }
	$request_uri = $_SERVER['REQUEST_URI'] ?? '';
	if ( '' === $request_uri ) { return; }
	// Only post-category routes are protected from this legacy global normalizer.
	if ( function_exists( 'glass_category_route_is_request' ) && glass_category_route_is_request( (string) $request_uri ) ) {
		return;
	}
	// کاملا Skip برای بلاگ و دسته-بندی — هیچ ریدایرکتی برای این URLها انجام نده تا 404 نشه
	$decoded = rawurldecode( $request_uri );
	if ( false !== strpos( $decoded, 'بلاگ' ) || false !== strpos( $decoded, 'دسته-بندی' ) || false !== strpos( $decoded, 'دسته بندی' ) ) {
		return;
	}
	if ( false !== strpos( $request_uri, 'بلاگ' ) || false !== strpos( $request_uri, 'دسته-بندی' ) ) {
		return;
	}
	// Skip search
	if ( false !== strpos( $request_uri, '?s=' ) || false !== strpos( $request_uri, '&s=' ) ) { return; }
	$has_double = false !== strpos( $request_uri, '%25' );
	$has_space_encoded = false !== strpos( $request_uri, '%20' );
	if ( ! $has_double && ! $has_space_encoded ) { return; }
	$new_uri = $request_uri;
	$prev=''; $it=0;
	while ( false !== strpos( $new_uri, '%25' ) && $new_uri !== $prev && $it < 3 ) {
		$prev=$new_uri; $new_uri=rawurldecode($new_uri); $it++;
	}
	$parsed = wp_parse_url( $new_uri );
	$path = $parsed['path'] ?? $new_uri;
	$query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
	if ( false !== strpos($path,' ') || false !== strpos($path,'%20') ) {
		$path = str_replace(['%20',' '],'-',$path);
		$path = preg_replace('#-+#','-',$path);
		$new_uri = $path . $query;
	}
	if ( $new_uri === $request_uri ) { return; }
	$norm_new = strtolower( trailingslashit( rawurldecode($new_uri) ) );
	$norm_cur = strtolower( trailingslashit( rawurldecode($request_uri) ) );
	if ( $norm_new === $norm_cur ) { return; }
	wp_safe_redirect( home_url( $new_uri ), 301 );
	exit;
}

