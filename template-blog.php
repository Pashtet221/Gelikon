<?php
/*
Template Name: Блог
*/
defined('ABSPATH') || exit;

get_header();

$paged = max(1, get_query_var('paged'), get_query_var('page'));

$blog_query = new WP_Query([
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 9,
	'paged'               => $paged,
	'ignore_sticky_posts' => true,
]);

$featured_post = null;
$featured_args = [
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 1,
	'ignore_sticky_posts' => true,
];

$featured_query = new WP_Query($featured_args);

if ($featured_query->have_posts()) {
	$featured_query->the_post();
	$featured_post = get_post();
}
wp_reset_postdata();

$categories = get_categories([
	'taxonomy'   => 'category',
	'hide_empty' => true,
	'orderby'    => 'count',
	'order'      => 'DESC',
	'number'     => 8,
]);

function gelikon_blog_trim_excerpt($text = '', $length = 22) {
	$text = wp_strip_all_tags($text);
	$text = preg_replace('/\s+/', ' ', $text);
	return wp_trim_words($text, $length, '...');
}
?>

<main id="primary" class="site-main gl-blog-page">
	<div class="gl-container">

		<?php if (function_exists('do_shortcode')) : ?>
			<?php echo do_shortcode('[gelikon_breadcrumbs]'); ?>
		<?php endif; ?>

		<section class="gl-blog-hero gl-home-section">
			<div class="gl-blog-hero__content">
				<div class="gl-blog-hero__text">
					<div class="gl-blog-hero__eyebrow">Gelikon Blog</div>
					<h1 class="gl-blog-hero__title"><?php the_title(); ?></h1>
					<div class="gl-blog-hero__desc">
						Полезные статьи, обзоры оборудования, советы по выбору и материалы о здоровье, диагностике и современных медицинских решениях.
					</div>

					<?php if (!empty($categories)) : ?>
						<div class="gl-blog-hero__cats">
							<?php foreach ($categories as $cat) : ?>
								<a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="gl-blog-chip">
									<?php echo esc_html($cat->name); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($featured_post) : ?>
					<a class="gl-card gl-blog-hero-card" href="<?php echo esc_url(get_permalink($featured_post->ID)); ?>">
						<div class="gl-blog-hero-card__image">
							<?php if (has_post_thumbnail($featured_post->ID)) : ?>
								<?php echo get_the_post_thumbnail($featured_post->ID, 'large'); ?>
							<?php else : ?>
								<div class="gl-blog-hero-card__placeholder"></div>
							<?php endif; ?>
						</div>

						<div class="gl-blog-hero-card__body">
							<?php
							$post_categories = get_the_category($featured_post->ID);
							if (!empty($post_categories)) :
							?>
								<div class="gl-blog-card__meta">
									<span class="gl-blog-card__cat"><?php echo esc_html($post_categories[0]->name); ?></span>
									<span class="gl-blog-card__dot">•</span>
									<span class="gl-blog-card__date"><?php echo esc_html(get_the_date('d.m.Y', $featured_post->ID)); ?></span>
								</div>
							<?php endif; ?>

							<h2 class="gl-blog-hero-card__title">
								<?php echo esc_html(get_the_title($featured_post->ID)); ?>
							</h2>

							<div class="gl-blog-hero-card__excerpt">
								<?php echo esc_html(gelikon_blog_trim_excerpt(get_the_excerpt($featured_post->ID), 26)); ?>
							</div>

							<div class="gl-blog-hero-card__link">Читать статью</div>
						</div>
					</a>
				<?php endif; ?>
			</div>
		</section>

		<section class="gl-home-section">
			<div class="gl-section-head gl-section-head--between gl-blog-section-head">
				<h2>Последние статьи</h2>
			</div>

			<?php if ($blog_query->have_posts()) : ?>
				<div class="gl-blog-grid">
					<?php while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
						<article <?php post_class('gl-card gl-blog-card'); ?>>
							<a class="gl-blog-card__image" href="<?php the_permalink(); ?>">
								<?php if (has_post_thumbnail()) : ?>
									<?php the_post_thumbnail('medium_large'); ?>
								<?php else : ?>
									<div class="gl-blog-card__placeholder"></div>
								<?php endif; ?>
							</a>

							<div class="gl-blog-card__body">
								<?php $card_categories = get_the_category(); ?>
								<div class="gl-blog-card__meta">
									<?php if (!empty($card_categories)) : ?>
										<span class="gl-blog-card__cat"><?php echo esc_html($card_categories[0]->name); ?></span>
										<span class="gl-blog-card__dot">•</span>
									<?php endif; ?>
									<span class="gl-blog-card__date"><?php echo esc_html(get_the_date('d.m.Y')); ?></span>
								</div>

								<h3 class="gl-blog-card__title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>

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
				<div class="gl-card gl-blog-empty">
					Пока статей нет.
				</div>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>
		</section>

	</div>
</main>

<style>
.gl-blog-page {
	padding: 28px 0 80px;
	background: #f3f4f2;
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

.gl-blog-hero {
	margin-bottom: 28px;
}

.gl-blog-hero__content {
	display: grid;
	grid-template-columns: minmax(0, 1fr) minmax(360px, 520px);
	gap: 28px;
	align-items: stretch;
}

.gl-blog-hero__text {
	padding: 18px 0 8px;
}

.gl-blog-hero__eyebrow {
	display: inline-flex;
	align-items: center;
	min-height: 34px;
	padding: 0 14px;
	margin: 0 0 18px;
	border-radius: 999px;
	background: rgba(34, 197, 94, 0.10);
	color: var(--gl-color-accent);
	font-size: 14px;
	font-weight: 700;
}

.gl-blog-hero__title {
	margin: 0 0 18px;
	font-size: clamp(34px, 4vw, 64px);
	line-height: 0.96;
	letter-spacing: -0.05em;
	color: #171d2a;
}

.gl-blog-hero__desc {
	max-width: 760px;
	font-size: 18px;
	line-height: 1.7;
	color: #656b73;
}

.gl-blog-hero__cats {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	margin-top: 26px;
}

.gl-blog-chip {
	display: inline-flex;
	align-items: center;
	min-height: 42px;
	padding: 0 16px;
	border-radius: 999px;
	background: #fff;
	border: 1px solid #e6e9e7;
	text-decoration: none;
	font-size: 14px;
	font-weight: 600;
	color: #20242a;
	transition: .2s ease;
}

.gl-blog-chip:hover {
	transform: translateY(-1px);
	border-color: var(--gl-color-accent);
	color: var(--gl-color-accent);
}

.gl-blog-hero-card {
	display: flex;
	flex-direction: column;
	overflow: hidden;
	border-radius: 28px;
	background: #fff;
	border: 1px solid #e6e9e7;
	text-decoration: none;
}

.gl-blog-hero-card__image {
	aspect-ratio: 1.12 / 1;
	background: #e9ece8;
	overflow: hidden;
}

.gl-blog-hero-card__image img,
.gl-blog-card__image img {
	display: block;
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.gl-blog-hero-card__placeholder,
.gl-blog-card__placeholder {
	width: 100%;
	height: 100%;
	background: linear-gradient(135deg, #eef2ef 0%, #dfe7e1 100%);
}

.gl-blog-hero-card__body {
	padding: 22px 22px 24px;
}

.gl-blog-hero-card__title {
	margin: 0 0 14px;
	font-size: 28px;
	line-height: 1.08;
	letter-spacing: -0.03em;
	color: #171d2a;
}

.gl-blog-hero-card__excerpt {
	margin: 0 0 18px;
	font-size: 15px;
	line-height: 1.65;
	color: #656b73;
}

.gl-blog-hero-card__link {
	display: inline-flex;
	align-items: center;
	min-height: 48px;
	padding: 0 20px;
	border-radius: 999px;
	background: var(--gl-color-accent);
	color: #fff;
	font-size: 15px;
	font-weight: 700;
}

.gl-blog-section-head h2 {
	margin: 0 0 18px;
	font-size: clamp(28px, 2.8vw, 50px);
	line-height: 1.03;
	letter-spacing: -0.04em;
	color: #171d2a;
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
	border-radius: 24px;
	background: #fff;
	border: 1px solid #e6e9e7;
}

.gl-blog-card__image {
	display: block;
	aspect-ratio: 1.18 / 0.78;
	background: #e9ece8;
	overflow: hidden;
}

.gl-blog-card__body {
	display: flex;
	flex-direction: column;
	flex: 1 1 auto;
	padding: 20px 20px 22px;
}

.gl-blog-card__meta {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 8px;
	margin: 0 0 14px;
	font-size: 13px;
	line-height: 1.3;
	color: #8a8f97;
}

.gl-blog-card__cat {
	color: var(--gl-color-accent);
	font-weight: 700;
}

.gl-blog-card__dot {
	color: #b4babf;
}

.gl-blog-card__title {
	margin: 0 0 12px;
	font-size: 22px;
	line-height: 1.15;
	letter-spacing: -0.02em;
}

.gl-blog-card__title a {
	color: #171d2a;
	text-decoration: none;
	transition: color .2s ease;
}

.gl-blog-card__title a:hover {
	color: var(--gl-color-accent);
}

.gl-blog-card__excerpt {
	margin: 0 0 18px;
	font-size: 15px;
	line-height: 1.65;
	color: #656b73;
}

.gl-blog-card__more {
	display: inline-flex;
	align-items: center;
	min-height: 46px;
	padding: 0 18px;
	margin-top: auto;
	border-radius: 999px;
	background: rgba(34, 197, 94, 0.10);
	color: var(--gl-color-accent);
	text-decoration: none;
	font-size: 14px;
	font-weight: 700;
	transition: .2s ease;
}

.gl-blog-card__more:hover {
	transform: translateY(-1px);
	background: var(--gl-color-accent);
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

.gl-blog-pagination .page-numbers li {
	margin: 0;
}

.gl-blog-pagination .page-numbers a,
.gl-blog-pagination .page-numbers span {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 46px;
	height: 46px;
	padding: 0 14px;
	border-radius: 999px;
	background: #fff;
	border: 1px solid #e6e9e7;
	color: #171d2a;
	text-decoration: none;
	font-weight: 600;
}

.gl-blog-pagination .page-numbers .current {
	background: var(--gl-color-accent);
	border-color: var(--gl-color-accent);
	color: #fff;
}

.gl-blog-empty {
	padding: 28px;
	border-radius: 24px;
	background: #fff;
	border: 1px solid #e6e9e7;
	font-size: 16px;
	color: #656b73;
}

.gl-home-section {
	margin: 50px 0;
}

@media (max-width: 1199px) {
	.gl-blog-hero__content {
		grid-template-columns: 1fr;
	}

	.gl-blog-grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 767px) {
	.gl-blog-page {
		padding: 16px 0 52px;
	}

	.gl-blog-page .woocommerce-breadcrumb {
		font-size: 12px;
		margin-bottom: 14px;
	}

	.gl-blog-hero__title {
		font-size: 36px;
		line-height: 1.02;
	}

	.gl-blog-hero__desc {
		font-size: 15px;
		line-height: 1.65;
	}

	.gl-blog-hero-card,
	.gl-blog-card {
		border-radius: 20px;
	}

	.gl-blog-hero-card__body,
	.gl-blog-card__body {
		padding: 16px;
	}

	.gl-blog-hero-card__title {
		font-size: 22px;
	}

	.gl-blog-card__title {
		font-size: 20px;
	}

	.gl-blog-grid {
		grid-template-columns: 1fr;
		gap: 16px;
	}

	.gl-home-section {
		margin: 32px 0;
	}
}
</style>

<?php
get_footer();