<?php
/**
 * Elementor Widget: Glass Ads Slider PRO
 *
 * نمایش اسلایدر تبلیغی (CPT glass_ad) با همهٔ تنظیمات حرفه‌ای از داخل Elementor.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Glass_Pro_Widget_Ads_Slider
 */
class Glass_Pro_Widget_Ads_Slider extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'glass_pro_ads_slider';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'اسلایدر تبلیغی PRO', 'glassmorphism-child-pro' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-slider-push';
	}

	/**
	 * @return array
	 */
	public function get_categories() {
		return [ 'glassmorphism-child-pro' ];
	}

	/**
	 * @return array
	 */
	public function get_keywords() {
		return [ 'slider', 'ads', 'carousel', 'اسلایدر', 'تبلیغ', 'بنر' ];
	}

	/**
	 * اسکریپت/استایل لازم برای ویجت.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'glass-ads-slider' ];
	}

	/**
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'glass-ads-slider' ];
	}

	/**
	 * گزینه‌های گروه اسلایدر برای کنترل SELECT.
	 *
	 * @return array
	 */
	protected function get_group_options() {
		$options = [ '' => __( 'همهٔ اسلایدها', 'glassmorphism-child-pro' ) ];
		if ( ! taxonomy_exists( 'glass_ad_group' ) ) {
			return $options;
		}
		$terms = get_terms( [ 'taxonomy' => 'glass_ad_group', 'hide_empty' => false ] );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$options[ $t->slug ] = $t->name;
			}
		}
		return $options;
	}

	/**
	 * ثبت کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->start_controls_section( 'sec_general', [
			'label' => __( 'تنظیمات اسلایدر', 'glassmorphism-child-pro' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'group', [
			'label'   => __( 'گروه اسلایدر', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => $this->get_group_options(),
		] );

		$this->add_control( 'count', [
			'label'   => __( 'تعداد اسلاید (-1 = همه)', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => -1,
		] );

		$this->add_control( 'effect', [
			'label'   => __( 'افکت', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'slide',
			'options' => [
				'slide' => __( 'لغزشی (Slide)', 'glassmorphism-child-pro' ),
				'fade'  => __( 'محوشونده (Fade)', 'glassmorphism-child-pro' ),
			],
		] );

		$this->add_control( 'autoplay', [
			'label'   => __( 'پخش خودکار (میلی‌ثانیه، 0 = خاموش)', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 5000,
		] );

		$this->add_control( 'loop', [
			'label'        => __( 'حلقهٔ بی‌نهایت', 'glassmorphism-child-pro' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => '1',
		] );

		$this->add_control( 'arrows', [
			'label'        => __( 'فلش‌ها', 'glassmorphism-child-pro' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => '1',
		] );

		$this->add_control( 'dots', [
			'label'        => __( 'نقطه‌ها', 'glassmorphism-child-pro' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => '1',
		] );

		$this->add_control( 'progress', [
			'label'        => __( 'نوار پیشرفت', 'glassmorphism-child-pro' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => '1',
		] );

		$this->add_control( 'pause_hover', [
			'label'        => __( 'توقف هنگام Hover', 'glassmorphism-child-pro' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => '1',
		] );

		$this->end_controls_section();

		/* ── چیدمان ── */
		$this->start_controls_section( 'sec_layout', [
			'label' => __( 'چیدمان', 'glassmorphism-child-pro' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'per_desktop', [
			'label'   => __( 'تعداد در دسکتاپ', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '2',
			'options' => [ '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴' ],
		] );
		$this->add_control( 'per_tablet', [
			'label'   => __( 'تعداد در تبلت', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '1',
			'options' => [ '1' => '۱', '2' => '۲', '3' => '۳' ],
		] );
		$this->add_control( 'per_mobile', [
			'label'   => __( 'تعداد در موبایل', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '1',
			'options' => [ '1' => '۱', '2' => '۲' ],
		] );

		$this->add_control( 'gap', [
			'label'   => __( 'فاصلهٔ بین اسلایدها (px)', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 24,
		] );

		$this->add_control( 'ratio', [
			'label'   => __( 'نسبت ابعاد (عرض:ارتفاع)', 'glassmorphism-child-pro' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '2.5',
			'options' => [
				'2.5'  => '2.5 : 1 (بنر)',
				'2'    => '2 : 1',
				'1.77' => '16 : 9',
				'3'    => '3 : 1 (باریک)',
				'1'    => '1 : 1 (مربع)',
			],
		] );

		$this->end_controls_section();
	}

	/**
	 * رندر فرانت‌اند.
	 *
	 * @return void
	 */
	protected function render() {
		if ( ! function_exists( 'glass_ads_render' ) ) {
			return;
		}
		$s = $this->get_settings_for_display();

		echo glass_ads_render( [
			'count'       => isset( $s['count'] ) ? (int) $s['count'] : -1,
			'group'       => $s['group'] ?? '',
			'autoplay'    => isset( $s['autoplay'] ) ? (int) $s['autoplay'] : 5000,
			'effect'      => $s['effect'] ?? 'slide',
			'loop'        => ( '1' === ( $s['loop'] ?? '1' ) ) ? 1 : 0,
			'arrows'      => ( '1' === ( $s['arrows'] ?? '1' ) ) ? 1 : 0,
			'dots'        => ( '1' === ( $s['dots'] ?? '1' ) ) ? 1 : 0,
			'progress'    => ( '1' === ( $s['progress'] ?? '1' ) ) ? 1 : 0,
			'pause_hover' => ( '1' === ( $s['pause_hover'] ?? '1' ) ) ? 1 : 0,
			'per_desktop' => (int) ( $s['per_desktop'] ?? 2 ),
			'per_tablet'  => (int) ( $s['per_tablet'] ?? 1 ),
			'per_mobile'  => (int) ( $s['per_mobile'] ?? 1 ),
			'gap'         => (int) ( $s['gap'] ?? 24 ),
			'ratio'       => $s['ratio'] ?? '2.5',
		] );
	}
}
