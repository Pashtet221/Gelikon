<?php
defined('ABSPATH') || exit;

get_header();

while (have_posts()) :
	the_post();

	$post_id = get_the_ID();
	$post_categories = get_the_category($post_id);
	$author_id = get_post_field('post_author', $post_id);

	$related_args = [
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 3,
		'post__not_in'        => [$post_id],
		'ignore_sticky_posts' => true,
	];

	if (!empty($post_categories)) {
		$related_args['category__in'] = wp_list_pluck($post_categories, 'term_id');
	}

	$related_query = new WP_Query($related_args);

	$recent_posts = new WP_Query([
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 4,
		'post__not_in'        => [$post_id],
		'ignore_sticky_posts' => true,
	]);

	$sidebar_categories = get_categories([
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 8,
	]);

	function gelikon_blog_trim_text($text = '', $length = 16) {
		$text = wp_strip_all_tags($text);
		$text = preg_replace('/\s+/', ' ', $text);
		return wp_trim_words($text, $length, '...');
	}
	?>

	<main id="primary" class="site-main gl-single-blog-page">
		<div class="gl-container">

			<?php if (function_exists('do_shortcode')) : ?>
				<?php echo do_shortcode('[gelikon_breadcrumbs]'); ?>
			<?php endif; ?>

			<section class="gl-single-blog-hero gl-home-section">
				<div class="gl-single-blog-hero__inner">
					<div class="gl-single-blog-hero__content">
						<?php if (!empty($post_categories)) : ?>
							<div class="gl-single-blog-hero__cats">
								<?php foreach ($post_categories as $cat) : ?>
									<a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="gl-blog-chip">
										<?php echo esc_html($cat->name); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<h1 class="gl-single-blog-hero__title"><?php the_title(); ?></h1>

						<div class="gl-single-blog-hero__meta">
							<span><?php echo esc_html(get_the_date('d.m.Y')); ?></span>
							<span class="gl-single-blog-hero__dot">•</span>
							<span><?php echo esc_html(get_the_author_meta('display_name', $author_id)); ?></span>
						</div>

						<?php if (has_excerpt()) : ?>
							<div class="gl-single-blog-hero__excerpt">
								<?php echo esc_html(get_the_excerpt()); ?>
							</div>
						<?php endif; ?>
					</div>

					<?php if (has_post_thumbnail()) : ?>
						<div class="gl-card gl-single-blog-hero__image">
							<?php the_post_thumbnail('full'); ?>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<section class="gl-home-section">
				<div class="gl-single-blog-layout">

					<article <?php post_class('gl-single-blog-article'); ?>>
						<div class="gl-card gl-single-blog-article__inner">
							<div class="gl-single-blog-content">
								<?php the_content(); ?>
							</div>
						</div>
					</article>

					<aside class="gl-single-blog-sidebar">

						<?php if (!empty($sidebar_categories)) : ?>
							<div class="gl-card gl-single-blog-widget">
								<h3 class="gl-single-blog-widget__title">Категории</h3>

								<div class="gl-single-blog-widget__cats">
									<?php foreach ($sidebar_categories as $cat) : ?>
										<a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" class="gl-blog-chip">
											<?php echo esc_html($cat->name); ?>
										</a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($recent_posts->have_posts()) : ?>
							<div class="gl-card gl-single-blog-widget">
								<h3 class="gl-single-blog-widget__title">Последние статьи</h3>

								<div class="gl-single-blog-recent">
									<?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
										<a href="<?php the_permalink(); ?>" class="gl-single-blog-recent__item">
											<div class="gl-single-blog-recent__thumb">
												<?php if (has_post_thumbnail()) : ?>
													<?php the_post_thumbnail('thumbnail'); ?>
												<?php else : ?>
													<div class="gl-single-blog-recent__placeholder"></div>
												<?php endif; ?>
											</div>

											<div class="gl-single-blog-recent__content">
												<div class="gl-single-blog-recent__date">
													<?php echo esc_html(get_the_date('d.m.Y')); ?>
												</div>
												<div class="gl-single-blog-recent__title">
													<?php the_title(); ?>
												</div>
											</div>
										</a>
									<?php endwhile; ?>
									<?php wp_reset_postdata(); ?>
								</div>
							</div>
						<?php endif; ?>

						<div class="gl-card gl-single-blog-cta">
							<div class="gl-single-blog-cta__label">Консультация</div>
							<h3 class="gl-single-blog-cta__title">Нужна помощь с выбором оборудования?</h3>
							<div class="gl-single-blog-cta__text">
								Подскажем, какое решение подойдёт именно под ваши задачи, бюджет и требования.
							</div>
							<a href="/contacts/" class="gl-single-blog-cta__button">Связаться с нами</a>
						</div>

					</aside>
				</div>
			</section>

			<?php if ($related_query->have_posts()) : ?>
				<section class="gl-home-section">
					<div class="gl-section-head gl-section-head--between gl-blog-section-head">
						<h2>Похожие статьи</h2>
						<a class="gl-section-link" href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/blog/')); ?>">
							Все статьи
						</a>
					</div>

					<div class="gl-blog-grid">
						<?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
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
										<?php echo esc_html(gelikon_blog_trim_text(get_the_excerpt(), 18)); ?>
									</div>

									<a class="gl-blog-card__more" href="<?php the_permalink(); ?>">
										Читать подробнее
									</a>
								</div>
							</article>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					</div>
				</section>
			<?php endif; ?>

		</div>
	</main>

	<style>
	.gl-single-blog-page {
		padding: 28px 0 80px;
		background: #f3f4f2;
	}

	.gl-single-blog-page .woocommerce-breadcrumb {
		margin: 0 0 24px;
		font-size: 14px;
		color: #8a8f97;
	}

	.gl-single-blog-page .woocommerce-breadcrumb a {
		color: inherit;
		text-decoration: none;
	}

	.gl-single-blog-hero {
		margin-bottom: 20px;
	}

	.gl-single-blog-hero__inner {
		display: grid;
		grid-template-columns: minmax(0, 1fr);
		gap: 22px;
	}

	.gl-single-blog-hero__content {
		max-width: 920px;
	}

	.gl-single-blog-hero__cats {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		margin-bottom: 18px;
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

	.gl-single-blog-hero__title {
		margin: 0 0 18px;
		font-size: clamp(34px, 4vw, 64px);
		line-height: 0.98;
		letter-spacing: -0.05em;
		color: #171d2a;
	}

	.gl-single-blog-hero__meta {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 10px;
		margin-bottom: 18px;
		font-size: 15px;
		line-height: 1.4;
		color: #8a8f97;
	}

	.gl-single-blog-hero__dot {
		color: #b4babf;
	}

	.gl-single-blog-hero__excerpt {
		max-width: 840px;
		font-size: 18px;
		line-height: 1.7;
		color: #656b73;
	}

	.gl-single-blog-hero__image {
		overflow: hidden;
		border-radius: 30px;
		background: #fff;
		border: 1px solid #e6e9e7;
	}

	.gl-single-blog-hero__image img {
		display: block;
		width: 100%;
		height: auto;
	}

	.gl-single-blog-layout {
		display: grid;
		grid-template-columns: minmax(0, 1fr) 340px;
		gap: 28px;
		align-items: start;
	}

	.gl-single-blog-article__inner,
	.gl-single-blog-widget,
	.gl-single-blog-cta {
		background: #fff;
		border: 1px solid #e6e9e7;
		border-radius: 28px;
	}

	.gl-single-blog-article__inner {
		padding: 34px;
	}

	.gl-single-blog-content {
		font-size: 17px;
		line-height: 1.8;
		color: #2b3138;
	}

	.gl-single-blog-content > *:first-child {
		margin-top: 0;
	}

	.gl-single-blog-content > *:last-child {
		margin-bottom: 0;
	}

	.gl-single-blog-content p {
		margin: 0 0 18px;
	}

	.gl-single-blog-content h2,
	.gl-single-blog-content h3,
	.gl-single-blog-content h4 {
		margin: 34px 0 16px;
		line-height: 1.18;
		letter-spacing: -0.03em;
		color: #171d2a;
	}

	.gl-single-blog-content h2 {
		font-size: clamp(28px, 2.3vw, 40px);
	}

	.gl-single-blog-content h3 {
		font-size: 28px;
	}

	.gl-single-blog-content h4 {
		font-size: 22px;
	}

	.gl-single-blog-content ul,
	.gl-single-blog-content ol {
		margin: 0 0 20px 22px;
		padding: 0;
	}

	.gl-single-blog-content li {
		margin-bottom: 10px;
	}

	.gl-single-blog-content img {
		display: block;
		max-width: 100%;
		height: auto;
		border-radius: 22px;
		margin: 24px 0;
	}

	.gl-single-blog-content blockquote {
		margin: 24px 0;
		padding: 22px 24px;
		border-left: 4px solid var(--gl-color-accent);
		background: #f8faf8;
		border-radius: 18px;
		color: #20242a;
		font-size: 18px;
		line-height: 1.7;
	}

	.gl-single-blog-content a {
		color: var(--gl-color-accent);
		text-decoration: underline;
		text-underline-offset: 3px;
	}

	.gl-single-blog-sidebar {
		display: flex;
		flex-direction: column;
		gap: 18px;
		position: sticky;
		top: 24px;
	}

	.gl-single-blog-widget {
		padding: 22px;
	}

	.gl-single-blog-widget__title {
		margin: 0 0 16px;
		font-size: 22px;
		line-height: 1.15;
		color: #171d2a;
	}

	.gl-single-blog-widget__cats {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
	}

	.gl-single-blog-recent {
		display: grid;
		gap: 14px;
	}

	.gl-single-blog-recent__item {
		display: grid;
		grid-template-columns: 78px minmax(0, 1fr);
		gap: 12px;
		align-items: center;
		text-decoration: none;
	}

	.gl-single-blog-recent__thumb {
		width: 78px;
		height: 78px;
		border-radius: 16px;
		overflow: hidden;
		background: #eef2ef;
		flex: 0 0 78px;
	}

	.gl-single-blog-recent__thumb img,
	.gl-single-blog-recent__placeholder {
		display: block;
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.gl-single-blog-recent__placeholder {
		background: linear-gradient(135deg, #eef2ef 0%, #dfe7e1 100%);
	}

	.gl-single-blog-recent__date {
		margin-bottom: 6px;
		font-size: 13px;
		line-height: 1.3;
		color: #8a8f97;
	}

	.gl-single-blog-recent__title {
		font-size: 15px;
		line-height: 1.45;
		font-weight: 600;
		color: #171d2a;
		transition: color .2s ease;
	}

	.gl-single-blog-recent__item:hover .gl-single-blog-recent__title {
		color: var(--gl-color-accent);
	}

	.gl-single-blog-cta {
		padding: 24px;
		background: linear-gradient(180deg, #ffffff 0%, #f7fbf7 100%);
	}

	.gl-single-blog-cta__label {
		display: inline-flex;
		align-items: center;
		min-height: 34px;
		padding: 0 12px;
		margin: 0 0 14px;
		border-radius: 999px;
		background: rgba(34, 197, 94, 0.10);
		color: var(--gl-color-accent);
		font-size: 13px;
		font-weight: 700;
	}

	.gl-single-blog-cta__title {
		margin: 0 0 12px;
		font-size: 24px;
		line-height: 1.1;
		letter-spacing: -0.03em;
		color: #171d2a;
	}

	.gl-single-blog-cta__text {
		margin: 0 0 18px;
		font-size: 15px;
		line-height: 1.65;
		color: #656b73;
	}

	.gl-single-blog-cta__button {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-height: 50px;
		padding: 0 22px;
		border-radius: 999px;
		background: var(--gl-color-accent);
		color: #fff;
		font-size: 15px;
		font-weight: 700;
		text-decoration: none;
		transition: .2s ease;
	}

	.gl-single-blog-cta__button:hover {
		transform: translateY(-1px);
		filter: brightness(.96);
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

	.gl-blog-card__image img {
		display: block;
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.gl-blog-card__placeholder {
		width: 100%;
		height: 100%;
		background: linear-gradient(135deg, #eef2ef 0%, #dfe7e1 100%);
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

	.gl-home-section {
		margin: 50px 0;
	}

	@media (max-width: 1199px) {
		.gl-single-blog-layout {
			grid-template-columns: 1fr;
		}

		.gl-single-blog-sidebar {
			position: static;
			top: auto;
		}

		.gl-blog-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 767px) {
		.gl-single-blog-page {
			padding: 16px 0 52px;
		}

		.gl-single-blog-page .woocommerce-breadcrumb {
			font-size: 12px;
			margin-bottom: 14px;
		}

		.gl-single-blog-hero__title {
			font-size: 34px;
			line-height: 1.02;
		}

		.gl-single-blog-hero__excerpt {
			font-size: 15px;
			line-height: 1.65;
		}

		.gl-single-blog-article__inner,
		.gl-single-blog-widget,
		.gl-single-blog-cta,
		.gl-single-blog-hero__image {
			border-radius: 20px;
		}

		.gl-single-blog-article__inner {
			padding: 18px 16px;
		}

		.gl-single-blog-content {
			font-size: 15px;
			line-height: 1.75;
		}

		.gl-single-blog-content h2 {
			font-size: 28px;
		}

		.gl-single-blog-content h3 {
			font-size: 24px;
		}

		.gl-single-blog-content h4 {
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
endwhile;

get_footer();