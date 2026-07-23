<?php
if (!defined('ABSPATH')) {
	exit;
}

get_header();

global $wp_query;

$search_term = get_search_query();
$lang        = function_exists('glass_search_current_lang') ? glass_search_current_lang() : 'fa';
$lang_data   = function_exists('glass_search_current_language_data') ? glass_search_current_language_data() : array();
$dir         = function_exists('glass_search_dir') ? glass_search_dir() : 'ltr';
$found_posts = (int) $wp_query->found_posts;
$glass_search_translate = static function ( string $key, string $fallback = '' ): string {
    return function_exists( 'glass_search_t' ) ? (string) glass_search_t( $key ) : __( $fallback ?: $key, 'glassmorphism-child-pro' );
};
?>

<main id="main-content" class="gs-page" dir="<?php echo esc_attr($dir); ?>">
	<div class="gs-container">

		<section class="gs-hero">
			<span class="gs-hero__eyebrow">
				<?php echo esc_html( $glass_search_translate( 'eyebrow', 'جستجوی سایت' ) ); ?>
			</span>

			<h1 class="gs-hero__title">
				<?php echo esc_html( $glass_search_translate( 'results_for', 'نتایج جستجو برای' ) ); ?>
				<span><?php echo esc_html($search_term); ?></span>
			</h1>

			<div class="gs-hero__meta">
				<p class="gs-hero__count">
					<?php
					$tpl = ( $found_posts === 1 )
                            ? $glass_search_translate( 'result_s', '%s نتیجه یافت شد' )
                            : $glass_search_translate( 'results_p', '%s نتیجه یافت شد' );
					printf(esc_html($tpl), esc_html(number_format_i18n($found_posts)));
					?>
				</p>

				<?php if (!empty($lang_data['name'])) : ?>
					<span class="gs-hero__lang">
						<span><?php echo esc_html($lang_data['flag']); ?></span>
						<?php echo esc_html($lang_data['name']); ?>
					</span>
				<?php endif; ?>
			</div>
		</section>

		<?php if (have_posts()) : ?>

			<div class="gs-grid">
				<?php while (have_posts()) : the_post(); ?>

					<article <?php post_class('gs-card'); ?>>
						<a class="gs-card__link" href="<?php the_permalink(); ?>">

							<div class="gs-card__img-wrap">
								<?php if (has_post_thumbnail()) : ?>
									<?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
								<?php else : ?>
									<div class="gs-card__img-placeholder">
										<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
											<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
											<circle cx="8.5" cy="8.5" r="1.5"></circle>
											<polyline points="21 15 16 10 5 21"></polyline>
										</svg>
									</div>
								<?php endif; ?>

								<div class="gs-card__overlay">
									<span class="gs-card__view-btn">
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
											<circle cx="12" cy="12" r="3"></circle>
										</svg>
										<?php echo esc_html( $glass_search_translate( 'view', 'مشاهده' ) ); ?>
									</span>
								</div>
							</div>

							<div class="gs-card__body">
								<p class="gs-card__title" style="font-weight:700;"><?php the_title(); ?></p>
							</div>

						</a>
					</article>

				<?php endwhile; ?>
			</div>

			<?php
			$pagination = paginate_links(array(
				'type'      => 'list',
				'current'   => max(1, get_query_var('paged')),
				'total'     => (int) $wp_query->max_num_pages,
				'prev_text' => '‹',
				'next_text' => '›',
				'add_args'  => array(
					's'    => $search_term,
					'lang' => $lang,
				),
			));

			if ($pagination) :
				?>
				<nav class="gs-pagination" aria-label="<?php echo esc_attr( $glass_search_translate( 'page_label', 'صفحه‌بندی نتایج جستجو' ) ); ?>">
					<?php echo wp_kses_post($pagination); ?>
				</nav>
			<?php endif; ?>

		<?php else : ?>

			<div class="gs-empty">
				<div class="gs-empty__icon">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="11" cy="11" r="8"></circle>
						<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
						<line x1="8" y1="11" x2="14" y2="11"></line>
					</svg>
				</div>
				<p style="font-weight:700;font-size:1.2rem;"><?php echo esc_html( $glass_search_translate( 'no_results', 'نتیجه‌ای یافت نشد' ) ); ?></p>
				<p><?php echo esc_html( $glass_search_translate( 'no_desc', 'لطفاً عبارت دیگری را امتحان کنید یا از منوهای سایت استفاده کنید.' ) ); ?></p>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php
wp_reset_postdata();
get_footer();