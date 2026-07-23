<?php
/**
 * Elementor Widget: Glass Card
 *
 * یک کارت شیشه‌ای (Alborz Ghaleb) با عنوان، متن، آیکون و دکمه.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Glass_Pro_Widget_Card
 */
class Glass_Pro_Widget_Card extends \Elementor\Widget_Base {

	/**
	 * نام یکتای ویجت.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'glass_pro_card';
	}

	/**
	 * عنوان قابل‌نمایش.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'کارت شیشه‌ای', 'glassmorphism-child-pro' );
	}

	/**
	 * آیکون ویجت.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-info-box';
	}

	/**
	 * دسته‌بندی‌ها.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'glassmorphism-child-pro' ];
	}

	/**
	 * کلیدواژه‌های جستجو.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'glassmorphism-child-pro', 'card', 'box', 'شیشه', 'کارت' ];
	}

	/**
	 * ثبت کنترل‌های ویجت.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'محتوا', 'glassmorphism-child-pro' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label'   => __( 'عنوان', 'glassmorphism-child-pro' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'عنوان کارت', 'glassmorphism-child-pro' ),
			]
		);

		$this->add_control(
			'description',
			[
				'label'   => __( 'متن', 'glassmorphism-child-pro' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'توضیح کوتاه کارت اینجا قرار می‌گیرد.', 'glassmorphism-child-pro' ),
			]
		);

		$this->add_control(
			'icon',
			[
				'label'   => __( 'آیکون', 'glassmorphism-child-pro' ),
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => [ 'value' => 'fas fa-star', 'library' => 'fa-solid' ],
			]
		);

		$this->add_control(
			'link',
			[
				'label'       => __( 'لینک', 'glassmorphism-child-pro' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://example.com',
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'   => __( 'متن دکمه', 'glassmorphism-child-pro' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'بیشتر بخوانید', 'glassmorphism-child-pro' ),
			]
		);

		$this->end_controls_section();

		/* استایل */
		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'استایل', 'glassmorphism-child-pro' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => __( 'رنگ عنوان', 'glassmorphism-child-pro' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .glass-pro-card__title' => 'color: {{VALUE}}' ],
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label'     => __( 'رنگ آیکون', 'glassmorphism-child-pro' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .glass-pro-card__icon' => 'color: #17212b}}' ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * رندر خروجی فرانت‌اند.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$has_link = ! empty( $settings['link']['url'] );

		if ( $has_link ) {
			$this->add_link_attributes( 'link', $settings['link'] );
		}
		?>
			<?php if ( ! empty( $settings['icon']['value'] ) ) : ?>
				<div class="glass-pro-card__icon" style="font-size:2.2rem;margin-bottom:14px;color:var(--fl-primary,#2D5F93);">
					<?php \Elementor\Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $settings['title'] ) : ?>
				<h3 class="glass-pro-card__title" style="margin:0 0 10px;font-size:1.2rem;font-weight:700;color:var(--fl-text,#17212B);"><?php echo esc_html( $settings['title'] ); ?></h3>
			<?php endif; ?>

			<?php if ( $settings['description'] ) : ?>
				<p class="glass-pro-card__desc" style="margin:0 0 18px;color:var(--fl-text-light,#64748B);line-height:1.8;"><?php echo esc_html( $settings['description'] ); ?></p>
			<?php endif; ?>

			<?php if ( $has_link && $settings['button_text'] ) : ?>
				<a <?php $this->print_render_attribute_string( 'link' ); ?> class="glass-cta-btn glass-cta-btn--primary">
					<?php echo esc_html( $settings['button_text'] ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}
}
