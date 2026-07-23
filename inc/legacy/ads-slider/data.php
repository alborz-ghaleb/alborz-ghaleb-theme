<?php
/** Ads Slider — Slides Collection Function (shared between shortcode + widget) */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   4. جمع‌آوری اسلایدها (مشترک بین شورت‌کد و ویجت)
   ════════════════════════════════════════ */
/**
 * بازگرداندن آرایهٔ اسلایدهای منتشرشده.
 *
 * @param int    $count تعداد (-1 = همه).
 * @param string $group اسلاگ گروه اسلایدر (خالی = همه).
 * @return array
 */
function glass_ads_get_slides( $count = -1, $group = '' ) {
	$args = [
		'post_type'      => 'glass_ad',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $count,
		'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
		'no_found_rows'  => true,
	];

	// فیلتر بر اساس گروه (در صورت تعیین)
	if ( '' !== trim( (string) $group ) ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'glass_ad_group',
				'field'    => 'slug',
				'terms'    => array_map( 'trim', explode( ',', $group ) ),
			],
		];
	}

	$query = new WP_Query( $args );

	$slides = [];
	while ( $query->have_posts() ) {
		$query->the_post();
		$id = get_the_ID();

		// تصویر: متای اختصاصی → سپس Featured Image
		$img_id = (int) get_post_meta( $id, '_glass_ad_image_id', true );
		if ( ! $img_id && has_post_thumbnail( $id ) ) {
			$img_id = (int) get_post_thumbnail_id( $id );
		}
		if ( ! $img_id ) {
			continue; // اسلاید بدون تصویر را رد کن
		}
		$img_src = wp_get_attachment_image_url( $img_id, 'full' );
		if ( ! $img_src ) {
			continue;
		}

		$show_ct = '1' === get_post_meta( $id, '_glass_ad_show_content', true );
		$slides[] = [
			'img'       => $img_src,
			'alt'       => get_the_title(),
			'link'      => get_post_meta( $id, '_glass_ad_link', true ),
			'new_tab'   => ( '1' === get_post_meta( $id, '_glass_ad_new_tab', true ) ),
			'show'      => $show_ct,
			'title'     => $show_ct ? get_the_title() : '',
			'subtitle'  => $show_ct ? get_post_meta( $id, '_glass_ad_subtitle', true ) : '',
			'desc'      => $show_ct ? get_post_meta( $id, '_glass_ad_desc', true ) : '',
			'btn_text'  => $show_ct ? get_post_meta( $id, '_glass_ad_btn_text', true ) : '',
			'position'  => get_post_meta( $id, '_glass_ad_position', true ) ?: 'center',
			'color'     => get_post_meta( $id, '_glass_ad_text_color', true ) ?: '#ffffff',
			'overlay'   => (int) ( get_post_meta( $id, '_glass_ad_overlay', true ) ?: 0 ),
		];
	}
	wp_reset_postdata();
	return $slides;
}

