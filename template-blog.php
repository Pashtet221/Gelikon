<?php
/*
Template Name: Блог
*/

defined('ABSPATH') || exit;

get_header();

$paged = max(1, get_query_var('paged'), get_query_var('page'));

if (!function_exists('gelikon_blog_trim_excerpt')) {
	function gelikon_blog_trim_excerpt($text = '', $length = 20) {
		$text = wp_strip_all_tags($text);
		$text = preg_replace('/\s+/', ' ', $text);
		return wp_trim_words($text, $length, '...');
	}
}

$blog_query = new WP_Query([
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 9,
	'paged'               => $paged,
	'ignore_sticky_posts' => true,
]);
?>

<main id="primary" class="site-main gl-blog-page">
	<div class="gl-container">

		<?php echo do_shortcode('[gelikon_breadcrumbs]'); ?>

		<header class="gl-blog-head">
			<h1><?php the_title(); ?></h1>
			<p>
				Полезные статьи, обзоры оборудования и советы по выбору медицинских решений.
			</p>
		</header>

		<?php if ($blog_query->have_posts()) : ?>
			<div class="gl-blog-grid">
				<?php while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
					<article <?php post_class('gl-blog-card'); ?>>

						<a class="gl-blog-card__image" href="<?php the_permalink(); ?>">
							<?php if (has_post_thumbnail()) : ?>
								<?php the_post_thumbnail('medium_large'); ?>
							<?php else : ?>
								<div class="gl-blog-card__placeholder">
									<span>Gelikon</span>
								</div>
							<?php endif; ?>
						</a>

						<div class="gl-blog-card__body">
							<?php $card_categories = get_the_category(); ?>

							<div class="gl-blog-card__meta">
								<?php if (!empty($card_categories)) : ?>
									<span><?php echo esc_html($card_categories[0]->name); ?></span>
									<i></i>
								<?php endif; ?>

								<span><?php echo esc_html(get_the_date('d.m.Y')); ?></span>
							</div>

							<h2 class="gl-blog-card__title">
								<a href="<?php the_permalink(); ?>">
									<?php the_title(); ?>
								</a>
							</h2>

							<div class="gl-blog-card__excerpt">
								<?php echo esc_html(gelikon_blog_trim_excerpt(get_the_excerpt(), 20)); ?>
							</div>

							<a class="gl-blog-card__more" href="<?php the_permalink(); ?>">
								Читать подробнее
							</a>
						</div>

					</article>
				<?php endwhile; ?>
			</div>

			<?php
			$pagination = paginate_links([
				'total'     => $blog_query->max_num_pages,
				'current'   => $paged,
				'type'      => 'list',
				'prev_text' => '←',
				'next_text' => '→',
			]);

			if ($pagination) :
				?>
				<div class="gl-blog-pagination">
					<?php echo wp_kses_post($pagination); ?>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<div class="gl-blog-empty">
				Пока статей нет.
			</div>
		<?php endif; ?>

		<?php wp_reset_postdata(); ?>

	</div>
</main>

<style>
.gl-blog-page {
	padding: 28px 0 76px;
}

.gl-blog-page .woocommerce-breadcrumb {
	margin: 0 0 24px;
	font-size: 14px;
	color: #8a8f97;
}

.gl-blog-page .woocommerce-breadcrumb a {
	color: inherit;
	text-decoration: none;
}

.gl-blog-head {
	margin: 0 0 34px;
}

.gl-blog-head h1 {
	margin: 0 0 14px;
	color: #171d2a;
	font-size: clamp(34px, 4vw, 56px);
	line-height: 1;
	letter-spacing: -0.045em;
	font-weight: 800;
}

.gl-blog-head p {
	max-width: 720px;
	margin: 0;
	color: #646b73;
	font-size: 17px;
	line-height: 1.65;
}

.gl-blog-grid {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 20px;
}

.gl-blog-card {
	display: flex;
	flex-direction: column;
	min-height: 100%;
	overflow: hidden;
	border-radius: 22px;
	background: #fff;
	border: 1px solid #e2e6e1;
	box-shadow: 0 14px 36px rgba(23, 29, 42, 0.045);
}

.gl-blog-card__image {
	display: block;
	aspect-ratio: 1.35 / .85;
	background: #fff;
	overflow: hidden;
	text-decoration: none;
}

.gl-blog-card__image img {
	display: block;
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.gl-blog-card__placeholder {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	height: 100%;
	background: linear-gradient(135deg, #eef2ed 0%, #dfe7df 100%);
	color: var(--gl-color-accent, #2f8f5b);
	font-size: 18px;
	font-weight: 800;
	letter-spacing: -0.02em;
}

.gl-blog-card__body {
	display: flex;
	flex-direction: column;
	flex: 1;
	padding: 20px;
}

.gl-blog-card__meta {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
	margin: 0 0 12px;
	color: #8a8f97;
	font-size: 13px;
	line-height: 1.35;
	font-weight: 600;
}

.gl-blog-card__meta span:first-child {
	color: var(--gl-color-accent, #2f8f5b);
	font-weight: 800;
}

.gl-blog-card__meta i {
	width: 4px;
	height: 4px;
	border-radius: 50%;
	background: #c4cac3;
}

.gl-blog-card__title {
	margin: 0 0 12px;
	font-size: 22px;
	line-height: 1.18;
	letter-spacing: -0.025em;
	font-weight: 800;
}

.gl-blog-card__title a {
	color: #171d2a;
	text-decoration: none;
}

.gl-blog-card__title a:hover {
	color: var(--gl-color-accent, #2f8f5b);
}

.gl-blog-card__excerpt {
	margin: 0 0 18px;
	color: #646b73;
	font-size: 15px;
	line-height: 1.6;
}

.gl-blog-card__more {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: fit-content;
	min-height: 42px;
	margin-top: auto;
	padding: 0 17px;
	border-radius: 999px;
	background: rgba(47, 143, 91, .10);
	color: var(--gl-color-accent, #2f8f5b);
	text-decoration: none;
	font-size: 14px;
	font-weight: 800;
}

.gl-blog-card__more:hover {
	background: var(--gl-color-accent, #2f8f5b);
	color: #fff;
}

.gl-blog-pagination {
	margin-top: 34px;
}

.gl-blog-pagination .page-numbers {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	padding: 0;
	margin: 0;
	list-style: none;
}

.gl-blog-pagination .page-numbers a,
.gl-blog-pagination .page-numbers span {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 44px;
	height: 44px;
	padding: 0 14px;
	border-radius: 999px;
	background: #fff;
	border: 1px solid #e2e6e1;
	color: #171d2a;
	text-decoration: none;
	font-size: 14px;
	font-weight: 700;
}

.gl-blog-pagination .page-numbers .current,
.gl-blog-pagination .page-numbers a:hover {
	background: var(--gl-color-accent, #2f8f5b);
	border-color: var(--gl-color-accent, #2f8f5b);
	color: #fff;
}

.gl-blog-empty {
	padding: 26px;
	border-radius: 20px;
	background: #fff;
	border: 1px solid #e2e6e1;
	color: #646b73;
	font-size: 16px;
}

@media (max-width: 1199px) {
	.gl-blog-grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 767px) {
	.gl-blog-page {
		padding: 18px 0 54px;
	}

	.gl-blog-page .woocommerce-breadcrumb {
		margin-bottom: 16px;
		font-size: 12px;
	}

	.gl-blog-head {
		margin-bottom: 24px;
	}

	.gl-blog-head h1 {
		font-size: 36px;
	}

	.gl-blog-head p {
		font-size: 15px;
		line-height: 1.6;
	}

	.gl-blog-grid {
		grid-template-columns: 1fr;
		gap: 16px;
	}

	.gl-blog-card {
		border-radius: 20px;
	}

	.gl-blog-card__body {
		padding: 17px;
	}

	.gl-blog-card__title {
		font-size: 20px;
	}
}
</style>

<?php
get_footer();