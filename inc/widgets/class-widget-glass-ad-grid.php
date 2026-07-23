<?php
/**
 * Elementor Widget: Glass Ad Grid
 *
 * نمایش گرید آخرین آگهی‌های دست‌دوم (CPT portfolio) با طراحی شیشه‌ای.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Glass_Pro_Widget_Ad_Grid
 */
class Glass_Pro_Widget_Ad_Grid extends \Elementor\Widget_Base {

	/**
	 * نام یکتا.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'glass_pro_ad_grid';
	}

	/**
	 * عنوان.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'گرید آگهی‌ها', 'glassmorphism-child-pro' );
	}

	/**
	 * آیکون.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * دسته‌بندی.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'glassmorphism-child-pro' ];
	}

	/**
	 * کلیدواژه‌ها.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'ad', 'portfolio', 'grid', 'آگهی', 'دست دوم' ];
	}

	/**
	 * ثبت کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'query_section',
			[
				'label' => __( 'تنظیمات', 'glassmorphism-child-pro' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'count',
			[
				'label'   => __( 'تعداد آگهی', 'glassmorphism-child-pro' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 24,
				'default' => 6,
			]
		);

		$this->add_control(
			'columns',
			[
				'label'   => __( 'تعداد ستون', 'glassmorphism-child-pro' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '3',
				'options' => [ '2' => '۲', '3' => '۳', '4' => '۴' ],
			]
		);

		$this->add_control(
			'category',
			[
				'label'   => __( 'دسته‌بندی', 'glassmorphism-child-pro' ),
				'type'    => \Elementor\Controls_Manager::SELECT2,
				'options' => $this->get_categories_options(),
				'default' => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * گزینه‌های دسته‌بندی برای کنترل SELECT2.
	 *
	 * @return array
	 */
	protected function get_categories_options() {
		$options = [ '' => __( 'همه دسته‌ها', 'glassmorphism-child-pro' ) ];
		if ( ! taxonomy_exists( 'themsah_theme_type' ) ) {
			return $options;
		}
		$terms = get_terms( [ 'taxonomy' => 'themsah_theme_type', 'hide_empty' => false ] );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$options[ $t->term_id ] = $t->name;
			}
		}
		return $options;
	}

	/**
	 * رندر فرانت‌اند.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$count    = max( 1, (int) $settings['count'] );
		$cols     = max( 2, (int) $settings['columns'] );

		$args = [
			'post_type'           => 'portfolio',
			'posts_per_page'      => $count,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		];

		if ( ! empty( $settings['category'] ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'themsah_theme_type',
					'field'    => 'term_id',
					'terms'    => (int) $settings['category'],
				],
			];
		}

		$q = new WP_Query( $args );

		if ( ! $q->have_posts() ) {
			echo '<p>' . esc_html__( 'آگهی‌ای یافت نشد.', 'glassmorphism-child-pro' ) . '</p>';
			return;
		}
		?>
		<div class="glass-pro-ad-grid" style="display:grid;grid-template-columns:repeat(<?php echo esc_attr( $cols ); ?>,1fr);gap:18px;">
			<?php
			while ( $q->have_posts() ) :
				$q->the_post();
				?>
					<?php if ( has_post_thumbnail() ) : ?>
						<div style="aspect-ratio:4/3;overflow:hidden;">
							<?php the_post_thumbnail( 'glass-card', [ 'style' => 'width:100%;height:100%;object-fit:cover;', 'loading' => 'lazy' ] ); ?>
						</div>
					<?php endif; ?>
					<div style="padding:14px 16px;">
						<h3 style="margin:0;font-size:1rem;font-weight:700;color:var(--fl-text,#17212B);line-height:1.5;"><?php the_title(); ?></h3>
					</div>
				</a>
				<?php
			endwhile;
			?>
		</div>
		<?php
		wp_reset_postdata();
	}
}
