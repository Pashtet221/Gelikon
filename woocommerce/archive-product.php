<?php
defined('ABSPATH') || exit;

get_header('shop');

$paged    = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$per_page = 12;

/**
 * Страница магазина WooCommerce
 */
$shop_page_id      = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;
$shop_title        = $shop_page_id && $shop_page_id > 0 ? get_the_title($shop_page_id) : __('Каталог', 'gelikon');
$shop_description  = '';
$search_query      = trim((string) get_search_query());
$is_product_search = is_search() && !empty($search_query) && (
	(isset($_GET['post_type']) && sanitize_key(wp_unslash($_GET['post_type'])) === 'product') ||
	(function_exists('is_shop') && is_shop()) ||
	is_post_type_archive('product')
);

if ($shop_page_id && $shop_page_id > 0) {
	$shop_page = get_post($shop_page_id);

	if ($shop_page && !is_wp_error($shop_page)) {
		$shop_description = apply_filters('the_content', $shop_page->post_content);
	}
}

/**
 * Доступные атрибуты WooCommerce
 */
$attribute_taxonomies = function_exists('wc_get_attribute_taxonomies') ? wc_get_attribute_taxonomies() : [];

/**
 * Текущая сортировка
 */
$orderby_selected = function_exists('gelikon_get_catalog_orderby_from_request') ? gelikon_get_catalog_orderby_from_request($_GET) : (isset($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : 'menu_order');
$orderby_args     = function_exists('gelikon_get_catalog_orderby_args') ? gelikon_get_catalog_orderby_args($orderby_selected) : [
	'meta_key' => '_price',
	'orderby'  => 'meta_value_num',
	'order'    => 'DESC',
];

/**
 * Активные фильтры из GET
 */
$selected_filters = [];

if (!empty($attribute_taxonomies)) {
	foreach ($attribute_taxonomies as $attribute_tax) {
		$taxonomy  = wc_attribute_taxonomy_name($attribute_tax->attribute_name);
		$query_key = 'filter_' . $taxonomy;

		if (!empty($_GET[$query_key])) {
			$raw    = wp_unslash($_GET[$query_key]);
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
];

foreach ($selected_filters as $taxonomy => $terms) {
	$tax_query[] = [
		'taxonomy' => $taxonomy,
		'field'    => 'slug',
		'terms'    => $terms,
		// Multiple values in one attribute narrow the result instead of expanding it.
		'operator' => 'AND',
	];
}

/**
 * ==========================================
 * БАЗОВЫЙ ЗАПРОС ДЛЯ РАСЧЁТА ФИЛЬТРОВ И ЦЕНЫ
 * Без учета цены, чтобы диапазон был честным для текущего поиска/атрибутов
 * ==========================================
 */
$filter_base_args = [
	'post_type'              => 'product',
	'post_status'            => 'publish',
	'posts_per_page'         => -1,
	'fields'                 => 'ids',
	'ignore_sticky_posts'    => true,
	'no_found_rows'          => true,
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
];

if ($is_product_search) {
	$filter_base_args['s'] = $search_query;
}

if (count($tax_query) > 1) {
	$filter_base_args['tax_query'] = $tax_query;
}

/**
 * Все товары текущей выборки (поиск + атрибуты, но без ценового ограничения)
 * Нужны для:
 * - списка доступных фильтров
 * - диапазона цен
 */
$product_ids_for_filters = get_posts($filter_base_args);

/**
 * ==========================================
 * ДОСТУПНЫЕ ФИЛЬТРЫ ПО АТРИБУТАМ
 * Считаются только по текущей выборке
 * ==========================================
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
				'post_type'              => 'product',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'post__in'               => $product_ids_for_filters,
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'tax_query'              => [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => [$term->term_id],
					],
				],
			]);

			if (!empty($matching_products)) {
				$term_for_display = clone $term;
				$term_for_display->gelikon_filter_count = count($matching_products);
				$filtered_terms[] = $term_for_display;
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
 * ==========================================
 * ДИАПАЗОН ЦЕН ПО ТЕКУЩЕЙ ВЫБОРКЕ
 * Поиск + атрибуты, но без текущего price filter
 * ==========================================
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

/**
 * Если цена не выбрана — ставим диапазон текущей выборки
 */
if ($min_price_selected <= 0) {
	$min_price_selected = $price_min;
}

if ($max_price_selected <= 0) {
	$max_price_selected = $price_max;
}

/**
 * ==========================================
 * ОСНОВНОЙ ЗАПРОС ТОВАРОВ
 * Учитывает:
 * - поиск
 * - атрибуты
 * - цену
 * - сортировку
 * ==========================================
 */
$query_args = [
	'post_type'           => 'product',
	'post_status'         => 'publish',
	'paged'               => $paged,
	'posts_per_page'      => $per_page,
	'ignore_sticky_posts' => true,
];

if ($is_product_search) {
	$query_args['s'] = $search_query;
}

if (count($tax_query) > 1) {
	$query_args['tax_query'] = $tax_query;
}

$meta_query = [];

/**
 * Фильтр по цене
 */
if ($min_price_selected > 0 || $max_price_selected > 0) {
	$meta_query[] = [
		'key'     => '_price',
		'type'    => 'NUMERIC',
		'compare' => 'BETWEEN',
		'value'   => [
			max(0, $min_price_selected),
			$max_price_selected > 0 ? $max_price_selected : 999999999,
		],
	];
}

if (!empty($meta_query)) {
	$query_args['meta_query'] = $meta_query;
}

$query_args = array_merge($query_args, $orderby_args);

$products_query = new WP_Query($query_args);
?>

<main id="primary" class="site-main gl-catalog-page">
	<div class="gl-container">

		<?php echo do_shortcode('[gelikon_breadcrumbs]'); ?>

		<header class="gl-catalog-page__head gl-home-section">
			<div class="gl-catalog-page__title-wrap">
				<?php if ($is_product_search) : ?>
					<h1 class="gl-catalog-page__title">
						<?php printf('Результаты поиска: %s', esc_html($search_query)); ?>
					</h1>
				<?php else : ?>
					<h1 class="gl-catalog-page__title"><?php echo esc_html($shop_title); ?></h1>
				<?php endif; ?>

				<?php if ($shop_description && !$is_product_search) : ?>
					<div class="gl-catalog-page__description">
						<?php echo wp_kses_post($shop_description); ?>
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
						<?php foreach (gelikon_catalog_sorting_options() as $orderby_value => $orderby_label) : ?>
							<option value="<?php echo esc_attr($orderby_value); ?>" <?php selected($orderby_selected, $orderby_value); ?>><?php echo esc_html($orderby_label); ?></option>
						<?php endforeach; ?>
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
			data-term-id="0"
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
							<div class="gl-catalog-filter gl-catalog-filter--choices" data-filter-block>
								<button type="button" class="gl-catalog-filter__heading gl-catalog-filter-heading" data-filter-toggle aria-expanded="true">
									<span><?php echo esc_html($filter_group['label']); ?></span>
									<span class="gl-catalog-filter__arrow" aria-hidden="true">
										<svg viewBox="0 0 12 12" width="12" height="12">
											<path d="M2.5 7.5L6 4L9.5 7.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</span>
								</button>

								<div class="gl-catalog-filter__body" data-filter-body>
									<div class="gl-catalog-filter__list">
										<?php foreach ($filter_group['terms'] as $term) : ?>
											<?php
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
												<span class="gl-catalog-filter__name"><?php echo esc_html($term->name); ?></span>
												<span class="gl-catalog-filter__count"><?php echo esc_html(isset($term->gelikon_filter_count) ? (int) $term->gelikon_filter_count : (int) $term->count); ?></span>
												<span class="gl-catalog-filter__check" aria-hidden="true"></span>
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
							<?php if ($is_product_search) : ?>
								<h2><?php esc_html_e('Ничего не найдено', 'gelikon'); ?></h2>
								<p><?php esc_html_e('Попробуйте изменить поисковый запрос или фильтры.', 'gelikon'); ?></p>
							<?php else : ?>
								<h2><?php esc_html_e('Товары не найдены', 'gelikon'); ?></h2>
								<p><?php esc_html_e('Попробуйте изменить фильтры.', 'gelikon'); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php wp_reset_postdata(); ?>
				</div>
			</section>
		</div>

	</div>
</main>





<script>
document.addEventListener('DOMContentLoaded', function () {
	const sortSelect = document.getElementById('gl-catalog-sort');

	if (!sortSelect) {
		return;
	}

	sortSelect.addEventListener('change', function () {
		const url = new URL(window.location.href);

		url.searchParams.set('orderby', this.value);
		url.searchParams.delete('paged');

		window.location.href = url.toString();
	});
});
</script>

<?php
wp_reset_postdata();
get_footer('shop');
