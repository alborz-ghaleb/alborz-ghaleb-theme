<?php
/**
 * Elementor Widgets: Glass Content Elements
 *
 * ۵ ویجت محتوایی شیشه‌ای برای المنتور:
 *   - تیتر شیشه‌ای (Glass Heading)
 *   - پاراگراف / باکس نکته (Glass Paragraph)
 *   - دکمه شیشه‌ای (Glass Button)
 *   - جدول شیشه‌ای (Glass Table)
 *   - سوالات متداول (Glass FAQ + اسکیما)
 *
 * رندر هر ویجت از توابع شورت‌کد inc/content-shortcodes.php استفاده می‌کند.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\Elementor\Widget_Base' ) ) { return; }

/* ════════════════════════════════════════
   ۱) تیتر شیشه‌ای
   ════════════════════════════════════════ */
if ( ! class_exists( 'Glass_CS_Widget_Heading' ) ) {
class Glass_CS_Widget_Heading extends \Elementor\Widget_Base {
	public function get_name() { return 'glass_cs_heading'; }
	public function get_title() { return __( 'تیتر شیشه‌ای', 'glassmorphism-child-pro' ); }
	public function get_icon() { return 'eicon-heading'; }
	public function get_categories() { return [ 'glassmorphism-child-pro', 'general' ]; }
	public function get_keywords() { return [ 'heading', 'title', 'تیتر', 'عنوان', 'شیشه' ]; }

	protected function register_controls() {
		$this->start_controls_section( 'sec', [ 'label' => __( 'محتوا', 'glassmorphism-child-pro' ) ] );
		$this->add_control( 'text', [
			'label'   => __( 'متن تیتر', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'تیتر نمونه', 'glassmorphism-child-pro' ),
		] );
		$this->add_control( 'style', [
			'label'   => __( 'سبک', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'bar',
			'options' => [
				'bar'       => __( 'خط کناری گرادیانی', 'glassmorphism-child-pro' ),
				'underline' => __( 'زیرخط زیتونی', 'glassmorphism-child-pro' ),
				'badge'     => __( 'بج شیشه‌ای', 'glassmorphism-child-pro' ),
			],
		] );
		$this->add_control( 'tag', [
			'label'   => __( 'تگ HTML', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'h2',
			'options' => [ 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4' ],
		] );
		$this->add_control( 'center', [
			'label' => __( 'وسط‌چین', 'glassmorphism-child-pro' ),
			'type'  => \Elementor\Controls_Manager::SWITCHER,
		] );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! function_exists( 'glass_cs_heading_shortcode' ) ) { return; }
		$s = $this->get_settings_for_display();
		echo glass_cs_heading_shortcode( [
			'tag'   => $s['tag'],
			'style' => $s['style'],
			'align' => ( 'yes' === $s['center'] ) ? 'center' : '',
		], $s['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
}

/* ════════════════════════════════════════
   ۲) پاراگراف / باکس نکته
   ════════════════════════════════════════ */
if ( ! class_exists( 'Glass_CS_Widget_Paragraph' ) ) {
class Glass_CS_Widget_Paragraph extends \Elementor\Widget_Base {
	public function get_name() { return 'glass_cs_paragraph'; }
	public function get_title() { return __( 'پاراگراف شیشه‌ای', 'glassmorphism-child-pro' ); }
	public function get_icon() { return 'eicon-text'; }
	public function get_categories() { return [ 'glassmorphism-child-pro', 'general' ]; }
	public function get_keywords() { return [ 'paragraph', 'text', 'note', 'پاراگراف', 'نکته', 'هشدار' ]; }

	protected function register_controls() {
		$this->start_controls_section( 'sec', [ 'label' => __( 'محتوا', 'glassmorphism-child-pro' ) ] );
		$this->add_control( 'text', [
			'label'   => __( 'متن', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::WYSIWYG,
			'default' => __( 'متن پاراگراف اینجا قرار می‌گیرد.', 'glassmorphism-child-pro' ),
		] );
		$this->add_control( 'style', [
			'label'   => __( 'سبک', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => [
				''        => __( 'معمولی', 'glassmorphism-child-pro' ),
				'lead'    => __( 'لید (مقدمه درشت)', 'glassmorphism-child-pro' ),
				'box'     => __( 'کارت شیشه‌ای', 'glassmorphism-child-pro' ),
				'note'    => __( 'نکته 💡 (زیتونی)', 'glassmorphism-child-pro' ),
				'info'    => __( 'اطلاعات ℹ️ (آبی)', 'glassmorphism-child-pro' ),
				'warning' => __( 'هشدار ⚠️ (نارنجی)', 'glassmorphism-child-pro' ),
				'small'   => __( 'متن ریز (پانوشت)', 'glassmorphism-child-pro' ),
			],
		] );
		$this->add_control( 'center', [
			'label' => __( 'وسط‌چین', 'glassmorphism-child-pro' ),
			'type'  => \Elementor\Controls_Manager::SWITCHER,
		] );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! function_exists( 'glass_cs_paragraph_shortcode' ) ) { return; }
		$s = $this->get_settings_for_display();
		echo glass_cs_paragraph_shortcode( [
			'style' => $s['style'],
			'align' => ( 'yes' === $s['center'] ) ? 'center' : '',
		], $s['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
}

/* ════════════════════════════════════════
   ۳) دکمه شیشه‌ای
   ════════════════════════════════════════ */
if ( ! class_exists( 'Glass_CS_Widget_Button' ) ) {
class Glass_CS_Widget_Button extends \Elementor\Widget_Base {
	public function get_name() { return 'glass_cs_button'; }
	public function get_title() { return __( 'دکمه شیشه‌ای', 'glassmorphism-child-pro' ); }
	public function get_icon() { return 'eicon-button'; }
	public function get_categories() { return [ 'glassmorphism-child-pro', 'general' ]; }
	public function get_keywords() { return [ 'button', 'cta', 'دکمه', 'شیشه' ]; }

	protected function register_controls() {
		$this->start_controls_section( 'sec', [ 'label' => __( 'محتوا', 'glassmorphism-child-pro' ) ] );
		$this->add_control( 'text', [
			'label'   => __( 'متن دکمه', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'کلیک کنید', 'glassmorphism-child-pro' ),
		] );
		$this->add_control( 'link', [
			'label'   => __( 'لینک', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => [ 'url' => '#' ],
		] );
		$this->add_control( 'style', [
			'label'   => __( 'رنگ', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'primary',
			'options' => [
				'primary' => __( 'آبی گرادیانی', 'glassmorphism-child-pro' ),
				'accent'  => __( 'زیتونی', 'glassmorphism-child-pro' ),
				'ghost'   => __( 'شیشه‌ای شفاف', 'glassmorphism-child-pro' ),
			],
		] );
		$this->add_control( 'size', [
			'label'   => __( 'اندازه', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => [
				''   => __( 'معمولی', 'glassmorphism-child-pro' ),
				'sm' => __( 'کوچک', 'glassmorphism-child-pro' ),
				'lg' => __( 'بزرگ', 'glassmorphism-child-pro' ),
			],
		] );
		$this->add_control( 'icon', [
			'label'   => __( 'آیکن', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => [
				''         => __( 'بدون آیکن', 'glassmorphism-child-pro' ),
				'arrow'    => __( 'فلش', 'glassmorphism-child-pro' ),
				'phone'    => __( 'تلفن', 'glassmorphism-child-pro' ),
				'download' => __( 'دانلود', 'glassmorphism-child-pro' ),
				'telegram' => __( 'تلگرام', 'glassmorphism-child-pro' ),
				'whatsapp' => __( 'واتساپ', 'glassmorphism-child-pro' ),
			],
		] );
		$this->add_control( 'block', [
			'label' => __( 'تمام‌عرض', 'glassmorphism-child-pro' ),
			'type'  => \Elementor\Controls_Manager::SWITCHER,
		] );
		$this->add_control( 'center', [
			'label' => __( 'وسط‌چین', 'glassmorphism-child-pro' ),
			'type'  => \Elementor\Controls_Manager::SWITCHER,
		] );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! function_exists( 'glass_cs_button_shortcode' ) ) { return; }
		$s    = $this->get_settings_for_display();
		$link = is_array( $s['link'] ) ? $s['link'] : [ 'url' => '#' ];
		echo glass_cs_button_shortcode( [
			'url'    => ! empty( $link['url'] ) ? $link['url'] : '#',
			'style'  => $s['style'],
			'size'   => $s['size'],
			'icon'   => $s['icon'],
			'target' => ! empty( $link['is_external'] ) ? '_blank' : '',
			'block'  => ( 'yes' === $s['block'] ) ? '1' : '0',
			'align'  => ( 'yes' === $s['center'] ) ? 'center' : '',
		], $s['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
}

/* ════════════════════════════════════════
   ۴) جدول شیشه‌ای
   ════════════════════════════════════════ */
if ( ! class_exists( 'Glass_CS_Widget_Table' ) ) {
class Glass_CS_Widget_Table extends \Elementor\Widget_Base {
	public function get_name() { return 'glass_cs_table'; }
	public function get_title() { return __( 'جدول شیشه‌ای', 'glassmorphism-child-pro' ); }
	public function get_icon() { return 'eicon-table'; }
	public function get_categories() { return [ 'glassmorphism-child-pro', 'general' ]; }
	public function get_keywords() { return [ 'table', 'جدول', 'قیمت' ]; }

	protected function register_controls() {
		$this->start_controls_section( 'sec', [ 'label' => __( 'محتوا', 'glassmorphism-child-pro' ) ] );
		$this->add_control( 'data', [
			'label'       => __( 'داده‌های جدول', 'glassmorphism-child-pro' ),
			'type'        => \Elementor\Controls_Manager::TEXTAREA,
			'rows'        => 10,
			'description' => __( 'هر خط = یک ردیف. ستون‌ها را با | جدا کنید. خط اول هدر جدول است.', 'glassmorphism-child-pro' ),
			'default'     => "محصول | قیمت (تومان) | وضعیت\nقالب فلزی دست دوم | ۲,۵۰۰,۰۰۰ | موجود\nجک سقفی صلیبی | ۸۰۰,۰۰۰ | موجود",
		] );
		$this->add_control( 'header', [
			'label'   => __( 'ردیف اول هدر باشد', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );
		$this->add_control( 'striped', [
			'label'   => __( 'ردیف‌های یک‌درمیان رنگی', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );
		$this->add_control( 'hover', [
			'label'   => __( 'هایلایت ردیف با موس', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );
		$this->add_control( 'center', [
			'label' => __( 'وسط‌چین سلول‌ها', 'glassmorphism-child-pro' ),
			'type'  => \Elementor\Controls_Manager::SWITCHER,
		] );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! function_exists( 'glass_cs_table_shortcode' ) ) { return; }
		$s = $this->get_settings_for_display();
		echo glass_cs_table_shortcode( [
			'striped' => ( 'yes' === $s['striped'] ) ? '1' : '0',
			'hover'   => ( 'yes' === $s['hover'] ) ? '1' : '0',
			'align'   => ( 'yes' === $s['center'] ) ? 'center' : '',
			'header'  => ( 'yes' === $s['header'] ) ? '1' : '0',
		], $s['data'] ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
}

/* ════════════════════════════════════════
   ۵) سوالات متداول (FAQ)
   ════════════════════════════════════════ */
if ( ! class_exists( 'Glass_CS_Widget_FAQ' ) ) {
class Glass_CS_Widget_FAQ extends \Elementor\Widget_Base {
	public function get_name() { return 'glass_cs_faq'; }
	public function get_title() { return __( 'سوالات متداول شیشه‌ای', 'glassmorphism-child-pro' ); }
	public function get_icon() { return 'eicon-toggle'; }
	public function get_categories() { return [ 'glassmorphism-child-pro', 'general' ]; }
	public function get_keywords() { return [ 'faq', 'accordion', 'سوال', 'آکاردئون' ]; }

	protected function register_controls() {
		$this->start_controls_section( 'sec', [ 'label' => __( 'محتوا', 'glassmorphism-child-pro' ) ] );
		$this->add_control( 'title', [
			'label'   => __( 'عنوان باکس', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'سوالات متداول', 'glassmorphism-child-pro' ),
		] );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'q', [
			'label'   => __( 'سوال', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'سوال شما؟', 'glassmorphism-child-pro' ),
		] );
		$repeater->add_control( 'a', [
			'label'   => __( 'جواب', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::WYSIWYG,
			'default' => __( 'جواب اینجا قرار می‌گیرد.', 'glassmorphism-child-pro' ),
		] );
		$repeater->add_control( 'open', [
			'label' => __( 'پیش‌فرض باز باشد', 'glassmorphism-child-pro' ),
			'type'  => \Elementor\Controls_Manager::SWITCHER,
		] );

		$this->add_control( 'items', [
			'label'       => __( 'سوال‌ها', 'glassmorphism-child-pro' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ q }}}',
			'default'     => [
				[ 'q' => __( 'هزینه ثبت آگهی چقدر است؟', 'glassmorphism-child-pro' ), 'a' => __( 'ثبت آگهی عادی رایگان است.', 'glassmorphism-child-pro' ), 'open' => 'yes' ],
				[ 'q' => __( 'آگهی من کی تایید می‌شود؟', 'glassmorphism-child-pro' ), 'a' => __( 'حداکثر تا ۲۴ ساعت کاری.', 'glassmorphism-child-pro' ) ],
			],
		] );
		$this->add_control( 'schema', [
			'label'       => __( 'اسکیمای FAQ برای سئو', 'glassmorphism-child-pro' ),
			'type'        => \Elementor\Controls_Manager::SWITCHER,
			'default'     => 'yes',
			'description' => __( 'شانس نمایش سوالات زیر نتیجه گوگل', 'glassmorphism-child-pro' ),
		] );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! function_exists( 'glass_cs_faq_shortcode' ) || ! function_exists( 'glass_cs_faq_item_shortcode' ) ) { return; }
		$s     = $this->get_settings_for_display();
		$inner = '';
		foreach ( (array) $s['items'] as $item ) {
			$inner .= glass_cs_faq_item_shortcode( [
				'q'    => isset( $item['q'] ) ? $item['q'] : '',
				'open' => ( isset( $item['open'] ) && 'yes' === $item['open'] ) ? '1' : '0',
			], isset( $item['a'] ) ? $item['a'] : '' );
		}
		echo glass_cs_faq_shortcode( [
			'title'  => $s['title'],
			'schema' => ( 'yes' === $s['schema'] ) ? '1' : '0',
		], $inner ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
}
