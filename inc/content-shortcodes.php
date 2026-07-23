<?php
/**
 * Content Shortcodes — جدول شیشه‌ای + سوالات متداول (FAQ)
 *
 * شورت‌کدها:
 *   [glass_table]            جدول شیشه‌ای (با سینتکس خط | یا جدول HTML آماده)
 *   [glass_faq]              باکس سوالات متداول (آکاردئونی + اسکیما FAQPage)
 *   [glass_faq_item q="?"]   هر سوال/جواب داخل باکس FAQ
 *   [glass_heading]          تیتر استایل‌دار (خط کناری / زیرخط / بج شیشه‌ای)
 *   [glass_p]                پاراگراف استایل‌دار (lead / باکس شیشه‌ای / نکته)
 *   [glass_button]           دکمه شیشه‌ای داخل متن (primary / accent / ghost)
 *
 * @package Alborz_Ghaleb
 * @since   5.16.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   ۱) استایل مشترک
   ════════════════════════════════════════
   از نسخه 5.16.0، تمام استایل‌های کلاس‌های glass-* از طریق
   فایل assets/css/glass-content-classes.css به‌صورت سراسری لود
   می‌شوند (enqueue.php). دیگر نیازی به چاپ inline CSS نیست.
   تابع glass_cs_print_styles() حذف شد — CSS یکتا از فایل سراسری.
   ════════════════════════════════════════ */

/* ════════════════════════════════════════
   ۲) شورت‌کد جدول  [glass_table]
   ────────────────────────────────────────
   حالت ۱ — سینتکس ساده با خط عمودی:
   [glass_table]
   محصول | قیمت | وضعیت
   قالب فلزی | ۲,۵۰۰,۰۰۰ | موجود
   جک سقفی | ۸۰۰,۰۰۰ | ناموجود
   [/glass_table]

   حالت ۲ — جدول HTML آماده (گوتنبرگ): فقط دورش بپیچید تا استایل بگیرد.

   پارامترها:
   striped="1|0"  ردیف‌های یک‌درمیان رنگی (پیش‌فرض 1)
   hover="1|0"    هایلایت ردیف با موس (پیش‌فرض 1)
   align="center" وسط‌چین کردن سلول‌ها
   header="0"     اگر نمی‌خواهید ردیف اول، هدر باشد
   ════════════════════════════════════════ */
add_shortcode( 'glass_table', 'glass_cs_table_shortcode' );
function glass_cs_table_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( [
		'striped' => '1',
		'hover'   => '1',
		'align'   => '',
		'header'  => '1',
	], $atts, 'glass_table' );

	$classes = 'glass-table';
	if ( '1' === $atts['striped'] )      { $classes .= ' glass-table--striped'; }
	if ( '1' === $atts['hover'] )        { $classes .= ' glass-table--hover'; }
	if ( 'center' === $atts['align'] )   { $classes .= ' glass-table--center'; }

	$content = trim( (string) $content );

	/* حالت ۲: جدول HTML آماده داخل شورت‌کد */
	if ( false !== stripos( $content, '<table' ) ) {
		$content = preg_replace( '/<table\b([^>]*)class="([^"]*)"/i', '<table$1class="$2 ' . esc_attr( $classes ) . '"', $content, 1, $replaced );
		if ( empty( $replaced ) ) {
			$content = preg_replace( '/<table\b/i', '<table class="' . esc_attr( $classes ) . '"', $content, 1 );
		}
		return '<div class="glass-table-wrap">' . wp_kses_post( $content ) . '</div>';
	}

	/* حالت ۱: سینتکس خط عمودی */
	$content = str_replace( [ '<br />', '<br/>', '<br>', '</p>' ], "\n", $content );
	$content = wp_strip_all_tags( $content );
	$lines   = array_values( array_filter( array_map( 'trim', explode( "\n", $content ) ), 'strlen' ) );
	if ( empty( $lines ) ) { return ''; }

	$rows = [];
	foreach ( $lines as $line ) {
		$rows[] = array_map( 'trim', explode( '|', $line ) );
	}

	$html = '<table class="' . esc_attr( $classes ) . '">';
	if ( '1' === $atts['header'] && count( $rows ) > 1 ) {
		$head = array_shift( $rows );
		$html .= '<thead><tr>';
		foreach ( $head as $cell ) { $html .= '<th>' . esc_html( $cell ) . '</th>'; }
		$html .= '</tr></thead>';
	}
	$html .= '<tbody>';
	foreach ( $rows as $row ) {
		$html .= '<tr>';
		foreach ( $row as $cell ) { $html .= '<td>' . esc_html( $cell ) . '</td>'; }
		$html .= '</tr>';
	}
	$html .= '</tbody></table>';

	return '<div class="glass-table-wrap">' . $html . '</div>';
}

/* ════════════════════════════════════════
   ۳) شورت‌کد سوالات متداول  [glass_faq] + [glass_faq_item]
   ────────────────────────────────────────
   [glass_faq title="سوالات متداول"]
     [glass_faq_item q="هزینه ثبت آگهی چقدر است؟" open="1"]
       ثبت آگهی عادی رایگان است.
     [/glass_faq_item]
     [glass_faq_item q="آگهی من کی تایید می‌شود؟"]
       حداکثر تا ۲۴ ساعت کاری.
     [/glass_faq_item]
   [/glass_faq]

   پارامترها:
   title=""   عنوان باکس (خالی = بدون عنوان)
   schema="0" غیرفعال‌کردن اسکیما FAQPage (پیش‌فرض فعال)
   open="1"   روی هر آیتم: به‌صورت باز نمایش داده شود
   ════════════════════════════════════════ */

/** جمع‌آوری سوال/جواب‌ها برای اسکیما */
function glass_cs_faq_registry( $q = null, $a = null ) {
	static $items = [];
	if ( null !== $q && null !== $a ) {
		$items[] = [ 'q' => $q, 'a' => $a ];
	}
	return $items;
}

add_shortcode( 'glass_faq_item', 'glass_cs_faq_item_shortcode' );
function glass_cs_faq_item_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( [
		'q'    => '',
		'open' => '0',
	], $atts, 'glass_faq_item' );

	$question = trim( (string) $atts['q'] );
	$answer   = do_shortcode( wpautop( trim( (string) $content ) ) );
	if ( '' === $question ) { return ''; }

	// ثبت برای اسکیما
	glass_cs_faq_registry( $question, trim( wp_strip_all_tags( $answer ) ) );

	$open = ( '1' === $atts['open'] ) ? ' open' : '';

	return '<details class="glass-faq-item"' . $open . '>'
		. '<summary class="glass-faq-q"><span>' . esc_html( $question ) . '</span>'
		. '<span class="glass-faq-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>'
		. '</summary>'
		. '<div class="glass-faq-a">' . wp_kses_post( $answer ) . '</div>'
		. '</details>';
}

add_shortcode( 'glass_faq', 'glass_cs_faq_shortcode' );
function glass_cs_faq_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( [
		'title'  => '',
		'schema' => '1',
	], $atts, 'glass_faq' );

	$inner = do_shortcode( (string) $content );

	$title_html = '';
	if ( '' !== trim( (string) $atts['title'] ) ) {
		$title_html = '<h2 class="glass-faq-title">'
			. '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
			. esc_html( $atts['title'] ) . '</h2>';
	}

	/* اسکیما FAQPage برای سئو — فقط اگر فعال باشد و سوالی ثبت شده باشد */
	$schema_html = '';
	if ( '1' === $atts['schema'] ) {
		$items = glass_cs_faq_registry();
		if ( $items ) {
			$main = [];
			foreach ( $items as $it ) {
				$main[] = [
					'@type'          => 'Question',
					'name'           => $it['q'],
					'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $it['a'] ],
				];
			}
			$schema = [
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'@id'        => get_permalink() ? get_permalink() . '#faq' : '#faq',
				'mainEntity' => $main,
			];
			if ( function_exists( 'glass_pro_schema_in_language' ) ) {
				$schema['inLanguage'] = glass_pro_schema_in_language();
			}
				$schema_nonce = '';
				if ( function_exists( 'glass_pro_csp_nonce' ) && apply_filters( 'glass_pro/csp/enabled', false ) ) {
					$schema_nonce = ' nonce="' . esc_attr( glass_pro_csp_nonce() ) . '"';
				}
				$schema_html = '<script type="application/ld+json"' . $schema_nonce . '>'
					. wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
					. '</script>';
		}
	}

	return '<div class="glass-faq" id="faq">' . $title_html . $inner . '</div>'
		. $schema_html;
}

/* ════════════════════════════════════════
   ۴) شورت‌کد تیتر  [glass_heading]
   ────────────────────────────────────────
   [glass_heading]تیتر با خط کناری گرادیانی[/glass_heading]
   [glass_heading style="underline" tag="h3"]تیتر با زیرخط زیتونی[/glass_heading]
   [glass_heading style="badge" align="center"]تیتر بج شیشه‌ای وسط‌چین[/glass_heading]

   پارامترها:
   tag="h2|h3|h4"                 تگ تیتر (پیش‌فرض h2)
   style="bar|underline|badge"    سبک تیتر (پیش‌فرض bar = خط کناری)
   align="center"                 وسط‌چین
   id=""                          آی‌دی دلخواه (برای لینک‌دهی/فهرست مطالب)
   ════════════════════════════════════════ */
add_shortcode( 'glass_heading', 'glass_cs_heading_shortcode' );
function glass_cs_heading_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( [
		'tag'   => 'h2',
		'style' => 'bar',
		'align' => '',
		'id'    => '',
	], $atts, 'glass_heading' );

	$tag = in_array( strtolower( $atts['tag'] ), [ 'h2', 'h3', 'h4' ], true ) ? strtolower( $atts['tag'] ) : 'h2';
	$style = in_array( $atts['style'], [ 'bar', 'underline', 'badge' ], true ) ? $atts['style'] : 'bar';

	$classes = 'glass-heading glass-heading--' . $tag . ' glass-heading--' . $style;
	if ( 'center' === $atts['align'] ) { $classes .= ' glass-heading--center'; }

	$id_attr = '' !== trim( (string) $atts['id'] ) ? ' id="' . esc_attr( sanitize_title( $atts['id'] ) ) . '"' : '';

	$wrap_open  = '';
	$wrap_close = '';
	if ( 'badge' === $style && 'center' === $atts['align'] ) {
		$wrap_open  = '<div style="text-align:center;">';
		$wrap_close = '</div>';
	}

	return $wrap_open
		. '<' . $tag . ' class="' . esc_attr( $classes ) . '"' . $id_attr . '>'
		. wp_kses_post( do_shortcode( trim( (string) $content ) ) )
		. '</' . $tag . '>' . $wrap_close;
}

/* ════════════════════════════════════════
   ۵) شورت‌کد پاراگراف  [glass_p]
   ────────────────────────────────────────
   [glass_p]پاراگراف معمولی با تایپوگرافی قالب[/glass_p]
   [glass_p style="lead"]پاراگراف معرفی (درشت‌تر)[/glass_p]
   [glass_p style="box"]پاراگراف داخل کارت شیشه‌ای[/glass_p]
   [glass_p style="note"]نکته با خط زیتونی[/glass_p]
   [glass_p style="info"]اطلاعات با خط آبی[/glass_p]
   [glass_p style="warning"]هشدار با خط نارنجی[/glass_p]

   پارامترها:
   style="lead|box|note|info|warning|small"   سبک پاراگراف
   align="center"                             وسط‌چین
   ════════════════════════════════════════ */
add_shortcode( 'glass_p', 'glass_cs_paragraph_shortcode' );
function glass_cs_paragraph_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( [
		'style' => '',
		'align' => '',
	], $atts, 'glass_p' );

	$allowed = [ 'lead', 'box', 'note', 'info', 'warning', 'small' ];
	$classes = 'glass-p';
	if ( in_array( $atts['style'], $allowed, true ) ) { $classes .= ' glass-p--' . $atts['style']; }
	if ( 'center' === $atts['align'] ) { $classes .= ' glass-p--center'; }

	$icons = [
		'note'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#7C8B00" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-inline-end:6px;"><path d="M12 2a7 7 0 0 0-4 12.7V17a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-2.3A7 7 0 0 0 12 2z"/><line x1="9" y1="21" x2="15" y2="21"/></svg>',
		'info'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#2D5F93" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-inline-end:6px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
		'warning' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#E8A33D" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-inline-end:6px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
	];
	$icon = isset( $icons[ $atts['style'] ] ) ? $icons[ $atts['style'] ] : '';

	return '<p class="' . esc_attr( $classes ) . '">' . $icon
		. wp_kses_post( do_shortcode( trim( (string) $content ) ) )
		. '</p>';
}

/* ════════════════════════════════════════
   ۶) شورت‌کد دکمه  [glass_button]
   ────────────────────────────────────────
   [glass_button url="/submit/"]ثبت آگهی رایگان[/glass_button]
   [glass_button url="/contact/" style="accent" size="lg"]تماس با ما[/glass_button]
   [glass_button url="https://t.me/..." style="ghost" target="_blank"]کانال تلگرام[/glass_button]
   [glass_button url="/submit/" block="1" align="center"]دکمه تمام‌عرض[/glass_button]

   پارامترها:
   url=""                        لینک دکمه (الزامی)
   style="primary|accent|ghost"  رنگ دکمه (پیش‌فرض primary آبی)
   size="sm|lg"                  اندازه (خالی = معمولی)
   target="_blank"               باز شدن در تب جدید (rel امن خودکار)
   block="1"                     تمام‌عرض
   align="center"                وسط‌چین
   icon="arrow|phone|download|telegram|whatsapp"  آیکن داخل دکمه
   ════════════════════════════════════════ */
add_shortcode( 'glass_button', 'glass_cs_button_shortcode' );
function glass_cs_button_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( [
		'url'    => '#',
		'style'  => 'primary',
		'size'   => '',
		'target' => '',
		'block'  => '0',
		'align'  => '',
		'icon'   => '',
	], $atts, 'glass_button' );

	$style   = in_array( $atts['style'], [ 'primary', 'accent', 'ghost' ], true ) ? $atts['style'] : 'primary';
	$classes = 'glass-btn glass-btn--' . $style;
	if ( in_array( $atts['size'], [ 'sm', 'lg' ], true ) ) { $classes .= ' glass-btn--' . $atts['size']; }
	if ( '1' === $atts['block'] ) { $classes .= ' glass-btn--block'; }

	$target_attr = '';
	if ( '_blank' === $atts['target'] ) {
		$target_attr = ' target="_blank" rel="noopener noreferrer"';
	}

	$icons = [
		'arrow'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="transform:scaleX(-1);"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
		'phone'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
		'download' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
		'telegram' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M21.94 4.04 18.6 19.77c-.25 1.11-.91 1.39-1.84.86l-5.09-3.75-2.46 2.36c-.27.27-.5.5-1.02.5l.37-5.18L18 6.06c.41-.37-.09-.57-.64-.21L5.72 13.21l-5.01-1.57c-1.09-.34-1.11-1.09.23-1.61L20.53 2.4c.91-.34 1.7.21 1.41 1.64z"/></svg>',
		'whatsapp' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.26-.47-2.39-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.91-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.87 1.21 3.07c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.04 2C6.56 2 2.1 6.45 2.1 11.93c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.92 9.92 0 0 0 4.79 1.22c5.48 0 9.93-4.45 9.93-9.93 0-2.65-1.03-5.14-2.91-7.01A9.86 9.86 0 0 0 12.04 2z"/></svg>',
	];
	$icon_html = isset( $icons[ $atts['icon'] ] ) ? $icons[ $atts['icon'] ] : '';

	$btn = '<a href="' . esc_url( $atts['url'] ) . '" class="' . esc_attr( $classes ) . '"' . $target_attr . '>'
		. $icon_html
		. '<span>' . wp_kses_post( do_shortcode( trim( (string) $content ) ) ) . '</span>'
		. '</a>';

	if ( 'center' === $atts['align'] ) {
		$btn = '<div class="glass-btn-wrap--center">' . $btn . '</div>';
	}

	return $btn;
}
