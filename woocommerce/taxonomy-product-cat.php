<?php
defined('ABSPATH') || exit;

get_header('shop');

$current_term = get_queried_object();

if (!$current_term || is_wp_error($current_term)) {
	get_footer('shop');
	return;
}

$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$per_page = 12;

/**
 * Доступные атрибуты WooCommerce
 */
$attribute_taxonomies = function_exists('wc_get_attribute_taxonomies') ? wc_get_attribute_taxonomies() : [];

/**
 * Текущая сортировка
 */
$orderby_selected = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'menu_order';

$orderby_args = [
	'orderby' => 'menu_order title',
	'order'   => 'ASC',
];

switch ($orderby_selected) {
	case 'date_desc':
		$orderby_args = [
			'orderby' => 'date',
			'order'   => 'DESC',
		];
		break;

	case 'price_asc':
		$orderby_args = [
			'meta_key' => '_price',
			'orderby'  => 'meta_value_num',
			'order'    => 'ASC',
		];
		break;

	case 'price_desc':
		$orderby_args = [
			'meta_key' => '_price',
			'orderby'  => 'meta_value_num',
			'order'    => 'DESC',
		];
		break;

	case 'title_asc':
		$orderby_args = [
			'orderby' => 'title',
			'order'   => 'ASC',
		];
		break;
}

/**
 * Активные фильтры из GET
 */
$selected_filters = [];

if (!empty($attribute_taxonomies)) {
	foreach ($attribute_taxonomies as $attribute_tax) {
		$taxonomy  = wc_attribute_taxonomy_name($attribute_tax->attribute_name);
		$query_key = 'filter_' . $taxonomy;

		if (!empty($_GET[$query_key])) {
			$raw = wp_unslash($_GET[$query_key]);
			$values = array_filter(array_map('sanitize_title', explode(',', $raw)));

			if (!empty($values)) {
				$selected_filters[$taxonomy] = $values;
			}
		}
	}
}

$min_price_selected = isset($_GET['min_price']) ? (int) $_GET['min_price'] : 0;
$max_price_selected = isset($_GET['max_price']) ? (int) $_GET['max_price'] : 0;

/**
 * Базовый tax_query
 */
$tax_query = [
	'relation' => 'AND',
	[
		'taxonomy' => 'product_cat',
		'field'    => 'term_id',
		'terms'    => [$current_term->term_id],
	],
];

foreach ($selected_filters as $taxonomy => $terms) {
	$tax_query[] = [
		'taxonomy' => $taxonomy,
		'field'    => 'slug',
		'terms'    => $terms,
		'operator' => 'IN',
	];
}

/**
 * Все товары текущей категории — чтобы вычислить доступные фильтры и диапазон цен
 */
$product_ids_for_filters = get_posts([
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'tax_query'      => [
		[
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => [$current_term->term_id],
		],
	],
]);

/**
 * Доступные фильтры по атрибутам
 */
$available_filters = [];

if (!empty($product_ids_for_filters) && !empty($attribute_taxonomies)) {
	foreach ($attribute_taxonomies as $attribute_tax) {
		$taxonomy = wc_attribute_taxonomy_name($attribute_tax->attribute_name);

		if (!taxonomy_exists($taxonomy)) {
			continue;
		}

		$terms = get_terms([
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		]);

		if (is_wp_error($terms) || empty($terms)) {
			continue;
		}

		$filtered_terms = [];

		foreach ($terms as $term) {
			$matching_products = get_posts([
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'post__in'       => $product_ids_for_filters,
				'tax_query'      => [
					[
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => [$current_term->term_id],
					],
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => [$term->term_id],
					],
				],
			]);

			if (!empty($matching_products)) {
				$filtered_terms[] = $term;
			}
		}

		if (!empty($filtered_terms)) {
			$available_filters[] = [
				'taxonomy' => $taxonomy,
				'label'    => $attribute_tax->attribute_label ?: $attribute_tax->attribute_name,
				'terms'    => $filtered_terms,
			];
		}
	}
}

/**
 * Диапазон цен по текущей категории
 */
$price_min = 0;
$price_max = 0;

if (!empty($product_ids_for_filters)) {
	global $wpdb;

	$ids_sql = implode(',', array_map('intval', $product_ids_for_filters));

	if ($ids_sql) {
		$row = $wpdb->get_row("
			SELECT 
				MIN(CAST(pm.meta_value AS DECIMAL(10,2))) AS min_price,
				MAX(CAST(pm.meta_value AS DECIMAL(10,2))) AS max_price
			FROM {$wpdb->postmeta} pm
			WHERE pm.post_id IN ($ids_sql)
			  AND pm.meta_key = '_price'
		");

		if ($row) {
			$price_min = (int) floor((float) $row->min_price);
			$price_max = (int) ceil((float) $row->max_price);
		}
	}
}

if ($min_price_selected <= 0) {
	$min_price_selected = $price_min;
}

if ($max_price_selected <= 0) {
	$max_price_selected = $price_max;
}

/**
 * Основной запрос товаров
 */
$query_args = [
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'paged'          => $paged,
	'posts_per_page' => $per_page,
	'tax_query'      => $tax_query,
];

if ($min_price_selected > 0 || $max_price_selected > 0) {
	$query_args['meta_query'] = [
		[
			'key'     => '_price',
			'type'    => 'NUMERIC',
			'compare' => 'BETWEEN',
			'value'   => [
				max(0, $min_price_selected),
				$max_price_selected > 0 ? $max_price_selected : 999999999,
			],
		],
	];
}

$query_args = array_merge($query_args, $orderby_args);

$products_query = new WP_Query($query_args);
?>

<main id="primary" class="site-main gl-catalog-page">
	<div class="gl-container">

		<?php echo do_shortcode('[gelikon_breadcrumbs]'); ?>

		<header class="gl-catalog-page__head gl-home-section">
			<div class="gl-catalog-page__title-wrap">
				<h1 class="gl-catalog-page__title"><?php single_term_title(); ?></h1>

				<?php $term_description = term_description($current_term->term_id, 'product_cat'); ?>
				<?php if ($term_description) : ?>
					<div class="gl-catalog-page__description">
						<?php echo wp_kses_post($term_description); ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="gl-catalog-page__toolbar">
				<div class="gl-catalog-page__meta" id="gl-catalog-count">
					<?php printf(esc_html__('%d товаров', 'gelikon'), (int) $products_query->found_posts); ?>
				</div>

				<div class="gl-catalog-sort">
					<label class="gl-catalog-sort__label" for="gl-catalog-sort">
						<?php esc_html_e('Сортировка', 'gelikon'); ?>
					</label>

					<select id="gl-catalog-sort" class="gl-catalog-sort__select">
						<option value="menu_order" <?php selected($orderby_selected, 'menu_order'); ?>>По умолчанию</option>
						<option value="date_desc" <?php selected($orderby_selected, 'date_desc'); ?>>Сначала новые</option>
						<option value="price_asc" <?php selected($orderby_selected, 'price_asc'); ?>>Сначала дешевле</option>
						<option value="price_desc" <?php selected($orderby_selected, 'price_desc'); ?>>Сначала дороже</option>
						<option value="title_asc" <?php selected($orderby_selected, 'title_asc'); ?>>По названию</option>
					</select>
				</div>
			</div>
		</header>

		<div class="gl-catalog-mobile-bar">
			<button type="button" class="gl-catalog-mobile-bar__button" id="gl-open-filters">
				<?php esc_html_e('Показать фильтры', 'gelikon'); ?>
			</button>
		</div>

		<div
			class="gl-catalog-layout"
			data-term-id="<?php echo esc_attr($current_term->term_id); ?>"
			data-per-page="<?php echo esc_attr($per_page); ?>"
		>
			<div class="gl-catalog-overlay" id="gl-catalog-overlay"></div>

			<aside class="gl-catalog-sidebar" id="gl-catalog-sidebar">
				<div class="gl-catalog-sidebar__inner gl-card">
					<div class="gl-catalog-sidebar__head">
						<h2><?php esc_html_e('Фильтры', 'gelikon'); ?></h2>

						<div class="gl-catalog-sidebar__head-actions">
							<button type="button" class="gl-catalog-sidebar__reset" id="gl-catalog-reset">
								<?php esc_html_e('Сбросить', 'gelikon'); ?>
							</button>

							<button type="button" class="gl-catalog-sidebar__close" id="gl-close-filters" aria-label="<?php esc_attr_e('Закрыть фильтры', 'gelikon'); ?>">
								×
							</button>
						</div>
					</div>

					<div class="gl-catalog-filters" id="gl-catalog-filters">

						<?php if ($price_max > 0) : ?>
							<div class="gl-catalog-filter" data-filter-block>
								<button type="button" class="gl-catalog-filter__toggle" data-filter-toggle>
									<span><?php esc_html_e('Цена', 'gelikon'); ?></span>
									<span class="gl-catalog-filter__arrow" aria-hidden="true">
										<svg viewBox="0 0 12 12" width="12" height="12">
											<path d="M2.5 7.5L6 4L9.5 7.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</span>
								</button>

								<div class="gl-catalog-filter__body" data-filter-body>
									<div class="gl-price-filter">
										<div class="gl-price-filter__values">
											<span><?php esc_html_e('От', 'gelikon'); ?> <strong id="gl-price-min-value"><?php echo esc_html($min_price_selected); ?></strong></span>
											<span><?php esc_html_e('До', 'gelikon'); ?> <strong id="gl-price-max-value"><?php echo esc_html($max_price_selected); ?></strong></span>
										</div>

										<div
											class="gl-price-slider"
											id="gl-price-slider"
											data-min="<?php echo esc_attr($price_min); ?>"
											data-max="<?php echo esc_attr($price_max); ?>"
										>
											<div class="gl-price-slider__track"></div>
											<div class="gl-price-slider__range" id="gl-price-slider-range"></div>

											<button type="button" class="gl-price-slider__thumb gl-price-slider__thumb--min" id="gl-price-thumb-min" aria-label="<?php esc_attr_e('Минимальная цена', 'gelikon'); ?>"></button>
											<button type="button" class="gl-price-slider__thumb gl-price-slider__thumb--max" id="gl-price-thumb-max" aria-label="<?php esc_attr_e('Максимальная цена', 'gelikon'); ?>"></button>
										</div>

										<input type="hidden" id="gl-price-min" value="<?php echo esc_attr($min_price_selected); ?>">
										<input type="hidden" id="gl-price-max" value="<?php echo esc_attr($max_price_selected); ?>">
									</div>
								</div>
							</div>
						<?php endif; ?>

						<?php foreach ($available_filters as $filter_group) : ?>
							<div class="gl-catalog-filter is-collapsed" data-filter-block>
								<button type="button" class="gl-catalog-filter__toggle" data-filter-toggle>
									<span><?php echo esc_html($filter_group['label']); ?></span>
									<span class="gl-catalog-filter__arrow" aria-hidden="true">
										<svg viewBox="0 0 12 12" width="12" height="12">
											<path d="M2.5 7.5L6 4L9.5 7.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</span>
								</button>

								<div class="gl-catalog-filter__body" data-filter-body hidden>
									<div class="gl-catalog-filter__list">
										<?php foreach ($filter_group['terms'] as $term) :
											$is_active = !empty($selected_filters[$filter_group['taxonomy']]) && in_array($term->slug, $selected_filters[$filter_group['taxonomy']], true);
											?>
											<label class="gl-catalog-filter__item <?php echo $is_active ? 'is-active' : ''; ?>">
												<input
													type="checkbox"
													class="gl-filter-checkbox"
													data-taxonomy="<?php echo esc_attr($filter_group['taxonomy']); ?>"
													value="<?php echo esc_attr($term->slug); ?>"
													<?php checked($is_active); ?>
												>
												<span class="gl-catalog-filter__check"></span>
												<span class="gl-catalog-filter__name"><?php echo esc_html($term->name); ?></span>
												<span class="gl-catalog-filter__count"><?php echo (int) $term->count; ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>

					</div>
				</div>
			</aside>

			<section class="gl-catalog-products">
				<div id="gl-catalog-products-wrap">
					<?php if ($products_query->have_posts()) : ?>
						<ul class="products columns-3 gl-catalog-products__grid">
							<?php while ($products_query->have_posts()) : $products_query->the_post(); ?>
								<?php
								$GLOBALS['product'] = wc_get_product(get_the_ID());

								if (!$GLOBALS['product'] || !$GLOBALS['product']->is_visible()) {
									continue;
								}

								wc_get_template_part('content', 'product');
								?>
							<?php endwhile; ?>
						</ul>

						<div class="gl-catalog-pagination">
							<?php
							echo paginate_links([
								'total'     => $products_query->max_num_pages,
								'current'   => $paged,
								'prev_text' => '←',
								'next_text' => '→',
							]);
							?>
						</div>
					<?php else : ?>
						<div class="gl-card gl-catalog-products__empty">
							<h2><?php esc_html_e('Товары не найдены', 'gelikon'); ?></h2>
							<p><?php esc_html_e('Попробуйте изменить фильтры.', 'gelikon'); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</section>
		</div>

	</div>
</main>


<style>
	/* =========================
   Product category page
========================= */

.gl-catalog-page {
	padding: 28px 0 72px;
}

.gl-catalog-page .woocommerce-breadcrumb {
	margin: 0 0 20px;
	font-size: 14px;
	color: var(--gl-text-muted, #7e838b);
}

.gl-catalog-page .woocommerce-breadcrumb a {
	color: inherit;
	text-decoration: none;
}

.gl-catalog-page__head {
	display: flex;
	align-items: flex-end;
	justify-content: space-between;
	gap: 24px;
	margin-bottom: 24px;
}

.gl-catalog-page__title {
	margin: 0 0 10px;
	font-size: clamp(30px, 3vw, 52px);
	line-height: 1.02;
	letter-spacing: -0.04em;
	color: #171d2a;
}

.gl-catalog-page__description {
	max-width: 760px;
	font-size: 16px;
	line-height: 1.7;
	color: #67707a;
}

.gl-catalog-page__description p {
	margin: 0;
}

.gl-catalog-page__meta {
	font-size: 15px;
	color: #7c838c;
	white-space: nowrap;
}

.gl-catalog-layout {
	display: grid;
	grid-template-columns: 300px minmax(0, 1fr);
	gap: 28px;
	align-items: start;
}

.gl-catalog-sidebar {
	min-width: 0;
}

.gl-catalog-sidebar__inner {
	position: sticky;
	top: 96px;
	padding: 22px;
	border-radius: 24px;
	background: #fff;
	border: 1px solid #e5ebe7;
}

.gl-catalog-sidebar__head {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	margin-bottom: 18px;
}

.gl-catalog-sidebar__head h2 {
	margin: 0;
	font-size: 20px;
	line-height: 1.2;
	color: #171d2a;
}

.gl-catalog-sidebar__reset {
	font-size: 14px;
	line-height: 1.2;
	color: var(--gl-color-accent);
	text-decoration: none;
}

.gl-catalog-filters {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.gl-catalog-filter__title {
	margin: 0 0 12px;
	font-size: 15px;
	line-height: 1.3;
	font-weight: 700;
	color: #171d2a;
}

.gl-catalog-filter__list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.gl-catalog-filter__item {
	display: grid;
	grid-template-columns: 18px 1fr auto;
	align-items: center;
	gap: 10px;
	min-height: 38px;
	padding: 8px 10px;
	border-radius: 12px;
	text-decoration: none;
	color: #171d2a;
	transition: background-color .2s ease, color .2s ease;
}

.gl-catalog-filter__item:hover {
	background: #f5f7f6;
}

.gl-catalog-filter__item.is-active {
	background: rgba(34, 197, 94, 0.08);
}

.gl-catalog-filter__check {
	width: 18px;
	height: 18px;
	border-radius: 6px;
	border: 1px solid #d9e1db;
	background: #fff;
	position: relative;
}

.gl-catalog-filter__item.is-active .gl-catalog-filter__check {
	border-color: var(--gl-color-accent);
	background: var(--gl-color-accent);
}

.gl-catalog-filter__item.is-active .gl-catalog-filter__check::after {
	content: "";
	position: absolute;
	left: 5px;
	top: 2px;
	width: 5px;
	height: 9px;
	border: solid #fff;
	border-width: 0 2px 2px 0;
	transform: rotate(45deg);
}

.gl-catalog-filter__name {
	font-size: 14px;
	line-height: 1.35;
}

.gl-catalog-filter__count {
	font-size: 13px;
	color: #8a9199;
}

.gl-catalog-sidebar__empty {
	font-size: 14px;
	line-height: 1.5;
	color: #7c838c;
}

.gl-catalog-products {
	min-width: 0;
}

.gl-catalog-products__grid {
	display: grid !important;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 20px;
	margin: 0;
	padding: 0;
	list-style: none;
}

.gl-catalog-products__grid li.product {
	width: auto !important;
	float: none !important;
	margin: 0 !important;
}

.gl-catalog-products__empty {
	padding: 28px;
	border-radius: 24px;
}

.gl-catalog-products__empty h2 {
	margin: 0 0 10px;
	font-size: 24px;
	color: #171d2a;
}

.gl-catalog-products__empty p {
	margin: 0;
	font-size: 15px;
	line-height: 1.6;
	color: #6b737d;
}

.gl-catalog-pagination {
	margin-top: 28px;
}

.gl-catalog-pagination .page-numbers {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 42px;
	height: 42px;
	margin-right: 8px;
	padding: 0 12px;
	border-radius: 999px;
	background: #fff;
	border: 1px solid #e1e6e3;
	color: #171d2a;
	text-decoration: none;
	font-size: 14px;
	font-weight: 500;
}

.gl-catalog-pagination .page-numbers.current {
	background: var(--gl-color-accent);
	border-color: var(--gl-color-accent);
	color: #fff;
}
	
.gl-price-slider button{
	min-height: 0;
}

@media (max-width: 1199px) {
	.gl-catalog-layout {
		grid-template-columns: 260px minmax(0, 1fr);
		gap: 22px;
	}

	.gl-catalog-products__grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 991px) {
	.gl-catalog-page__head {
		flex-direction: column;
		align-items: flex-start;
	}

	.gl-catalog-layout {
		grid-template-columns: 1fr;
	}

	.gl-catalog-sidebar__inner {
		position: static;
		top: auto;
	}

	.gl-catalog-products__grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 767px) {
	.gl-catalog-page {
		padding: 18px 0 48px;
	}

	.gl-catalog-page__title {
		font-size: 30px;
	}

	.gl-catalog-page__description {
		font-size: 15px;
	}

	.gl-catalog-sidebar__inner {
		padding: 18px;
		border-radius: 20px;
	}

	.gl-catalog-products__grid {
		grid-template-columns: 1fr;
		gap: 16px;
	}
}
	
	

/* Accordion filters */
.gl-catalog-filter {
	border-top: 1px solid #eef2ef;
	padding-top: 14px;
}

.gl-catalog-filter:first-child {
	border-top: 0;
	padding-top: 0;
}

.gl-catalog-filter__toggle {
	width: 100%;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	padding: 0;
	border: 0;
	background: transparent;
	cursor: pointer;
	text-align: left;
	font-size: 15px;
	font-weight: 700;
	line-height: 1.3;
	color: #171d2a;
}

.gl-catalog-filter__arrow {
	font-size: 18px;
	line-height: 1;
	transition: transform .2s ease;
	color: #7a828c;
}

.gl-catalog-filter:not(.is-collapsed) .gl-catalog-filter__arrow {
	transform: rotate(180deg);
}

.gl-catalog-filter__body {
	padding-top: 12px;
}

/* checkbox rows */
.gl-catalog-filter__item input[type="checkbox"] {
	display: none;
}

/* price */
.gl-price-filter__values {
	display: flex;
	justify-content: space-between;
	gap: 12px;
	margin-bottom: 14px;
	font-size: 14px;
	color: #67707a;
}

.gl-price-filter__ranges {
	display: grid;
	gap: 12px;
}

.gl-price-filter__ranges input[type="range"] {
	width: 100%;
	appearance: none;
	height: 6px;
	border-radius: 999px;
	background: #e7ece8;
	outline: none;
}

.gl-price-filter__ranges input[type="range"]::-webkit-slider-thumb {
	appearance: none;
	width: 18px;
	height: 18px;
	border-radius: 50%;
	background: var(--gl-color-accent);
	cursor: pointer;
	border: 0;
	box-shadow: 0 2px 8px rgba(0,0,0,.15);
}

.gl-price-filter__ranges input[type="range"]::-moz-range-thumb {
	width: 18px;
	height: 18px;
	border-radius: 50%;
	background: var(--gl-color-accent);
	cursor: pointer;
	border: 0;
	box-shadow: 0 2px 8px rgba(0,0,0,.15);
}

/* loading */
#gl-catalog-products-wrap {
	position: relative;
	transition: opacity .2s ease;
}

#gl-catalog-products-wrap.is-loading {
	opacity: .45;
	pointer-events: none;
}
	
	
	
	
	
	
	
/* =========================
   Product category page
========================= */

.gl-catalog-page {
	padding: 28px 0 72px;
}

.gl-catalog-page .woocommerce-breadcrumb {
	margin: 0 0 20px;
	font-size: 14px;
	color: var(--gl-text-muted, #7e838b);
}

.gl-catalog-page .woocommerce-breadcrumb a {
	color: inherit;
	text-decoration: none;
}

.gl-catalog-page__head {
	display: flex;
	align-items: flex-end;
	justify-content: space-between;
	gap: 24px;
	margin-bottom: 24px;
}

.gl-catalog-page__title {
	margin: 0 0 10px;
	font-size: clamp(30px, 3vw, 52px);
	line-height: 1.02;
	letter-spacing: -0.04em;
	color: #171d2a;
}

.gl-catalog-page__description {
	max-width: 760px;
	font-size: 16px;
	line-height: 1.7;
	color: #67707a;
}

.gl-catalog-page__description p {
	margin: 0;
}

.gl-catalog-page__meta {
	font-size: 15px;
	color: #7c838c;
	white-space: nowrap;
}

.gl-catalog-layout {
	display: grid;
	grid-template-columns: 300px minmax(0, 1fr);
	gap: 28px;
	align-items: start;
}

.gl-catalog-sidebar__inner {
	position: sticky;
	top: 96px;
	padding: 22px;
	border-radius: 24px;
	background: #fff;
	border: 1px solid #e5ebe7;
}

.gl-catalog-sidebar__head {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	margin-bottom: 18px;
}

.gl-catalog-sidebar__head h2 {
	margin: 0;
	font-size: 20px;
	line-height: 1.2;
	color: #171d2a;
}

.gl-catalog-sidebar__reset {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 38px;
	padding: 0 16px;
	border: 0;
	border-radius: 999px;
	background: #ecefed;
	color: var(--gl-color-accent);
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
}

.gl-catalog-filters {
	display: flex;
	flex-direction: column;
	gap: 18px;
}

/* Accordion */
.gl-catalog-filter {
	border-top: 1px solid #eef2ef;
	padding-top: 16px;
}

.gl-catalog-filter:first-child {
	border-top: 0;
	padding-top: 0;
}

.gl-catalog-filter__toggle {
	width: 100%;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	padding: 0;
	border: 0;
	background: transparent;
	cursor: pointer;
	text-align: left;
	font-size: 15px;
	font-weight: 700;
	line-height: 1.3;
	color: #171d2a;
}

.gl-catalog-filter__arrow {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 18px;
	height: 18px;
	color: #7a828c;
	transition: transform .2s ease;
	flex: 0 0 18px;
}

.gl-catalog-filter__arrow svg {
	display: block;
	width: 100%;
	height: 100%;
}

.gl-catalog-filter.is-collapsed .gl-catalog-filter__arrow {
	transform: rotate(180deg);
}

.gl-catalog-filter__body {
	padding-top: 14px;
}

/* checkbox */
.gl-catalog-filter__list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.gl-catalog-filter__item {
	display: grid;
	grid-template-columns: 18px 1fr auto;
	align-items: center;
	gap: 10px;
	min-height: 42px;
	padding: 8px 10px;
	border-radius: 12px;
	cursor: pointer;
	transition: background-color .2s ease, color .2s ease;
	position: relative;
}

.gl-catalog-filter__item:hover {
	background: #f5f7f6;
}

.gl-catalog-filter__item.is-active {
	background: rgba(34, 197, 94, 0.08);
}

.gl-catalog-filter__item input[type="checkbox"] {
	position: absolute;
	opacity: 0;
	pointer-events: none;
}

.gl-catalog-filter__check {
	width: 18px;
	height: 18px;
	border-radius: 6px;
	border: 1px solid #d9e1db;
	background: #fff;
	position: relative;
	transition: background-color .2s ease, border-color .2s ease;
}

.gl-catalog-filter__item.is-active .gl-catalog-filter__check {
	border-color: var(--gl-color-accent);
	background: var(--gl-color-accent);
}

.gl-catalog-filter__item.is-active .gl-catalog-filter__check::after {
	content: "";
	position: absolute;
	left: 5px;
	top: 2px;
	width: 5px;
	height: 9px;
	border: solid #fff;
	border-width: 0 2px 2px 0;
	transform: rotate(45deg);
}

.gl-catalog-filter__name {
	font-size: 14px;
	line-height: 1.35;
	color: #171d2a;
}

.gl-catalog-filter__count {
	font-size: 13px;
	color: #8a9199;
}

/* Price slider */
.gl-price-filter__values {
	display: flex;
	justify-content: space-between;
	gap: 12px;
	margin-bottom: 18px;
	font-size: 14px;
	color: #67707a;
}

.gl-price-filter__values strong {
	color: #171d2a;
	font-size: 15px;
}

.gl-price-slider {
	position: relative;
	height: 32px;
	margin: 10px 2px 0;
}

.gl-price-slider__track,
.gl-price-slider__range {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	height: 6px;
	border-radius: 999px;
}

.gl-price-slider__track {
	left: 0;
	right: 0;
	background: #e7ece8;
}

.gl-price-slider__range {
	background: var(--gl-color-accent);
}

.gl-price-slider__thumb {
	position: absolute;
	top: 50%;
	transform: translate(-50%, -50%);
	width: 22px;
	height: 22px;
	border-radius: 50%;
	border: 0;
	background: var(--gl-color-accent);
	box-shadow: 0 2px 8px rgba(0,0,0,.16);
	cursor: pointer;
	padding: 0;
}

.gl-catalog-products__grid {
	display: grid !important;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 20px;
	margin: 0;
	padding: 0;
	list-style: none;
}

.gl-catalog-products__grid li.product {
	width: auto !important;
	float: none !important;
	margin: 0 !important;
}

.gl-catalog-products__empty {
	padding: 28px;
	border-radius: 24px;
}

.gl-catalog-pagination {
	margin-top: 28px;
}

.gl-catalog-pagination .page-numbers {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 42px;
	height: 42px;
	margin-right: 8px;
	padding: 0 12px;
	border-radius: 999px;
	background: #fff;
	border: 1px solid #e1e6e3;
	color: #171d2a;
	text-decoration: none;
	font-size: 14px;
	font-weight: 500;
}

.gl-catalog-pagination .page-numbers.current {
	background: var(--gl-color-accent);
	border-color: var(--gl-color-accent);
	color: #fff;
}

#gl-catalog-products-wrap {
	position: relative;
	transition: opacity .2s ease;
}

#gl-catalog-products-wrap.is-loading {
	opacity: .45;
	pointer-events: none;
}

@media (max-width: 1199px) {
	.gl-catalog-layout {
		grid-template-columns: 260px minmax(0, 1fr);
		gap: 22px;
	}

	.gl-catalog-products__grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 991px) {
	.gl-catalog-page__head {
		flex-direction: column;
		align-items: flex-start;
	}

	.gl-catalog-layout {
		grid-template-columns: 1fr;
	}

	.gl-catalog-sidebar__inner {
		position: static;
		top: auto;
	}

	.gl-catalog-products__grid {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 767px) {
	.gl-catalog-page {
		padding: 18px 0 48px;
	}

	.gl-catalog-page__title {
		font-size: 30px;
	}

	.gl-catalog-page__description {
		font-size: 15px;
	}

	.gl-catalog-sidebar__inner {
		padding: 18px;
		border-radius: 20px;
	}

	.gl-catalog-products__grid {
		grid-template-columns: 1fr;
		gap: 16px;
	}
}
	
	
	
/* Mobile filters button */
.gl-catalog-mobile-bar {
	display: none;
	margin-bottom: 18px;
}

.gl-catalog-mobile-bar__button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 44px;
	padding: 0 18px;
	border: 1px solid #d3dbd6;
	border-radius: 999px;
	background: #fff;
	color: #2f3a35;
	font-size: 14px;
	font-weight: 500;
	cursor: pointer;
	transition: background-color .2s ease, border-color .2s ease;
}

.gl-catalog-mobile-bar__button:hover {
	border-color: #c4cec8;
	background: #f8faf9;
}

/* Overlay */
.gl-catalog-overlay {
	display: none;
}

.gl-catalog-sidebar__head-actions {
	display: flex;
	align-items: center;
	gap: 10px;
}

.gl-catalog-sidebar__close {
	display: none;
	width: 38px;
	height: 38px;
	border: 0;
	border-radius: 50%;
	background: #f2f4f3;
	color: #171d2a;
	font-size: 24px;
	line-height: 1;
	cursor: pointer;
}

/* Smaller price thumbs */
.gl-price-slider {
	position: relative;
	height: 26px;
	margin: 10px 2px 0;
}

.gl-price-slider__track,
.gl-price-slider__range {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	height: 6px;
	border-radius: 999px;
}

.gl-price-slider__track {
	left: 0;
	right: 0;
	background: #e7ece8;
}

.gl-price-slider__range {
	background: var(--gl-color-accent);
}

.gl-price-slider__thumb {
	position: absolute;
	top: 50%;
	transform: translate(-50%, -50%);
	width: 16px;
	height: 16px;
	border-radius: 50%;
	border: 0;
	background: var(--gl-color-accent);
	box-shadow: 0 2px 6px rgba(0,0,0,.14);
	cursor: pointer;
	padding: 0;
}

.gl-price-slider__thumb:hover {
	filter: brightness(.97);
}

/* mobile drawer */
@media (max-width: 991px) {
	.gl-catalog-mobile-bar {
		display: block;
	}

	.gl-catalog-overlay.is-visible {
		display: block;
		position: fixed;
		inset: 0;
		background: rgba(0, 0, 0, 0.35);
		z-index: 999;
	}

	.gl-catalog-sidebar {
		position: fixed;
		top: 0;
		left: 0;
		width: min(380px, 90vw);
		height: 100vh;
		z-index: 1000;
		transform: translateX(-100%);
		transition: transform .25s ease;
		padding: 0;
	}

	.gl-catalog-sidebar.is-open {
		transform: translateX(0);
	}

	.gl-catalog-sidebar__inner {
		position: relative;
		top: 0;
		height: 100%;
		overflow-y: auto;
		border-radius: 0 24px 24px 0;
		padding: 18px;
	}

	.gl-catalog-sidebar__close {
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	body.gl-filters-open {
		overflow: hidden;
	}
}
	
	
	
	
	
/* Скролл внутри длинных списков значений */
.gl-catalog-filter__list {
	display: flex;
	flex-direction: column;
	gap: 8px;
	max-height: 220px;
	overflow-y: auto;
	padding-right: 6px;
	scrollbar-width: thin;
	scrollbar-color: #cfd8d2 transparent;
}

.gl-catalog-filter__list::-webkit-scrollbar {
	width: 6px;
}

.gl-catalog-filter__list::-webkit-scrollbar-track {
	background: transparent;
}

.gl-catalog-filter__list::-webkit-scrollbar-thumb {
	background: #cfd8d2;
	border-radius: 999px;
}

/* Чтобы список не прилипал к правому краю при скролле */
.gl-catalog-filter__body {
	padding-top: 14px;
	min-width: 0;
}
</style>


<?php
wp_reset_postdata();
get_footer('shop');
