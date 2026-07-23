<?php
/**
 * SEO — Structured Data (JSON-LD) & Social Meta
 *
 * 🚨 قانون قرمز: کل این ماژول فقط زمانی فعال می‌شود که هیچ افزونه‌ی SEO
 * (Rank Math / Yoast / SEOPress / AIOSEO) فعال نباشد — تا Schema/Meta
 * تکراری تولید نشود. هیچ canonical / redirect / meta-robots تولید نمی‌کنیم.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'glass_pro_seo_output', 5 );
/**
 * چاپ JSON-LD و متاهای اجتماعی در صورت نبود افزونه‌ی SEO.
 *
 * @return void
 */
function glass_pro_seo_output(): void {

	// 🚨 اگر افزونه‌ی SEO فعال است، هیچ خروجی نده.
	if ( glass_pro_has_seo_plugin() ) {
		return;
	}

	glass_pro_seo_open_graph();
	glass_pro_seo_json_ld();
}

/* ────────────────────────────────────────
   Open Graph + Twitter Cards
   ──────────────────────────────────────── */
/**
 * چاپ متاهای Open Graph و Twitter.
 *
 * @return void
 */
function glass_pro_seo_open_graph(): void {

	$site_name = get_bloginfo( 'name' );
	$locale    = get_locale();

	if ( is_singular() ) {
		$title = get_the_title();
		$url   = get_permalink();
		$desc  = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 30, '…' );
		$type  = 'article';
		$image = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'glass-featured' ) : '';
	} else {
		$title = wp_get_document_title();
		$url   = home_url( add_query_arg( null, null ) );
		$desc  = get_bloginfo( 'description' );
		$type  = 'website';
		$image = '';
	}

	$tags = [
		'og:locale'      => $locale,
		'og:type'        => $type,
		'og:title'       => $title,
		'og:description' => $desc,
		'og:url'         => $url,
		'og:site_name'   => $site_name,
	];
	foreach ( $tags as $prop => $val ) {
		if ( $val ) {
			printf(
				'<meta property="%s" content="%s">' . "\n",
				esc_attr( $prop ),
				esc_attr( wp_strip_all_tags( $val ) )
			);
		}
	}
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
	}

	// Twitter
	printf( '<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary' );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $title ) ) );
	if ( $desc ) {
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $desc ) ) );
	}
	if ( $image ) {
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
	}
}

/* ────────────────────────────────────────
   JSON-LD: Organization + WebSite + Breadcrumb + Article/Product
   ──────────────────────────────────────── */
/**
 * چاپ گراف JSON-LD ساختاریافته.
 *
 * @return void
 */
function glass_pro_seo_json_ld(): void {

	$graph = [];

	/* Organization (با امکان override) */
	$org = apply_filters( 'glass_pro/schema/organization', [
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	] );
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $logo_url ) {
			$logo_node = [
				'@type'      => 'ImageObject',
				'url'        => $logo_url,
				'contentUrl' => $logo_url,
			];
			// Complete license/creator metadata when helper is available.
			if ( function_exists( 'glass_pro_schema_complete_image_object' ) ) {
				$logo_node = glass_pro_schema_complete_image_object( $logo_node );
			}
			$org['logo'] = $logo_node;
		}
	}
	$graph[] = $org;

	/* WebSite + SearchAction */
	$graph[] = [
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'description'     => get_bloginfo( 'description' ),
		'inLanguage'      => str_replace( '_', '-', get_locale() ),
		'publisher'       => [ '@id' => home_url( '/#organization' ) ],
		'potentialAction' => [
			'@type'       => 'SearchAction',
			'target'      => [
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			],
			'query-input' => 'required name=search_term_string',
		],
	];

	/* BreadcrumbList */
	$crumbs = glass_pro_get_breadcrumb_items();
	if ( count( $crumbs ) > 1 ) {
		$items = [];
		foreach ( $crumbs as $i => $c ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $c['name'],
				'item'     => $c['url'],
			];
		}
		$graph[] = [
			'@type'           => 'BreadcrumbList',
			'@id'             => ( is_singular() ? get_permalink() : home_url() ) . '#breadcrumb',
			'itemListElement' => $items,
		];
	}

	/* Article / Product برای محتوای تکی */
	if ( is_singular( [ 'post', 'portfolio' ] ) ) {
		$is_ad = is_singular( 'portfolio' );
		$node  = [
			'@type'         => $is_ad ? 'Product' : 'Article',
			'@id'           => get_permalink() . '#main',
			'name'          => get_the_title(),
			'headline'      => get_the_title(),
			'url'           => get_permalink(),
			'datePublished' => get_the_date( 'c' ),
			'dateModified'  => get_the_modified_date( 'c' ),
			'inLanguage'    => str_replace( '_', '-', get_locale() ),
		];
		if ( has_post_thumbnail() ) {
			$thumb = get_the_post_thumbnail_url( null, 'glass-featured' );
			if ( $thumb ) {
				$image_node = [
					'@type'      => 'ImageObject',
					'url'        => $thumb,
					'contentUrl' => $thumb,
				];
				if ( function_exists( 'glass_pro_schema_complete_image_object' ) ) {
					$image_node = glass_pro_schema_complete_image_object( $image_node );
				}
				$node['image'] = $image_node;
			}
		}
		if ( $is_ad ) {
			$node['description'] = wp_strip_all_tags( get_the_excerpt() );
		} else {
			$author              = get_the_author();
			$node['author']      = [ '@type' => 'Person', 'name' => $author ? $author : get_bloginfo( 'name' ) ];
			$node['publisher']   = [ '@id' => home_url( '/#organization' ) ];
		}
		$graph[] = $node;
	}

	$json = [
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	];

	$nonce_attr = function_exists( 'glass_pro_csp_nonce' ) && apply_filters( 'glass_pro/csp/enabled', false )
		? ' nonce="' . esc_attr( glass_pro_csp_nonce() ) . '"'
		: '';

	echo '<script type="application/ld+json"' . $nonce_attr . '>'
		. wp_json_encode( $json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		. '</script>' . "\n";
}

/* ────────────────────────────────────────
   استخراج آیتم‌های بردکرامب برای Schema
   (مستقل از خروجی HTML بردکرامب موجود؛ فقط داده.)
   ──────────────────────────────────────── */
/**
 * بازگرداندن آرایه‌ی بردکرامب [ ['name'=>..,'url'=>..], ... ].
 *
 * @return array
 */
function glass_pro_get_breadcrumb_items(): array {
	$items = [ [ 'name' => __( 'خانه', 'glassmorphism-child-pro' ), 'url' => home_url( '/' ) ] ];

	if ( is_singular() ) {
		$post_type = get_post_type();
		if ( 'portfolio' === $post_type ) {
			$terms = get_the_terms( get_the_ID(), 'themsah_theme_type' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$term    = $terms[0];
				$items[] = [ 'name' => $term->name, 'url' => get_term_link( $term ) ];
			}
		} elseif ( 'post' === $post_type ) {
			$cats = get_the_category();
			if ( $cats ) {
				$items[] = [ 'name' => $cats[0]->name, 'url' => get_category_link( $cats[0]->term_id ) ];
			}
		}
		$items[] = [ 'name' => get_the_title(), 'url' => get_permalink() ];
	} elseif ( is_category() || is_tax() || is_tag() ) {
		$obj = get_queried_object();
		if ( $obj && ! empty( $obj->name ) ) {
			$items[] = [ 'name' => $obj->name, 'url' => get_term_link( $obj ) ];
		}
	} elseif ( is_search() ) {
		$items[] = [ 'name' => __( 'نتایج جستجو', 'glassmorphism-child-pro' ), 'url' => home_url( '/?s=' . rawurlencode( get_search_query() ) ) ];
	}

	return $items;
}
