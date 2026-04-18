<?php
if (!defined('ABSPATH')) {
    exit;
}

define('GELIKON_VERSION', '1.0.0');
define('GELIKON_DIR', get_template_directory());
define('GELIKON_URI', get_template_directory_uri());

require_once GELIKON_DIR . '/inc/setup.php';
require_once GELIKON_DIR . '/inc/enqueue.php';
require_once GELIKON_DIR . '/inc/customizer.php';
require_once GELIKON_DIR . '/inc/template-tags.php';
require_once GELIKON_DIR . '/inc/woocommerce.php';



add_action('wp_enqueue_scripts', 'gelikon_enqueue_manrope_font', 5);
function gelikon_enqueue_manrope_font() {
	wp_enqueue_style(
		'gelikon-manrope-font',
		'https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap',
		[],
		null
	);
}



add_filter('upload_mimes', function ($mimes) {
	$mimes['mp4'] = 'video/mp4';
	return $mimes;
});




add_action('wp_enqueue_scripts', function () {
	if (is_admin()) {
		return;
	}

	wp_enqueue_style(
		'swiper',
		'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
		[],
		'11.0.7'
	);

	wp_enqueue_script(
		'swiper',
		'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
		[],
		'11.0.7',
		true
	);

	wp_enqueue_script(
		'gelikon-home-products-slider',
		get_template_directory_uri() . '/assets/js/home-products-slider.js',
		['swiper'],
		file_exists(get_template_directory() . '/assets/js/home-products-slider.js')
			? filemtime(get_template_directory() . '/assets/js/home-products-slider.js')
			: wp_get_theme()->get('Version'),
		true
	);
}, 30);






add_action('after_setup_theme', function () {
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}, 20);









add_action('init', function () {
	register_post_type('gelikon_review', [
		'labels' => [
			'name'               => 'Отзывы',
			'singular_name'      => 'Отзыв',
			'add_new'            => 'Добавить отзыв',
			'add_new_item'       => 'Добавить отзыв',
			'edit_item'          => 'Редактировать отзыв',
			'new_item'           => 'Новый отзыв',
			'view_item'          => 'Просмотреть отзыв',
			'search_items'       => 'Найти отзыв',
			'not_found'          => 'Отзывы не найдены',
			'not_found_in_trash' => 'В корзине отзывов нет',
			'menu_name'          => 'Отзывы',
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 25,
		'menu_icon'           => 'dashicons-format-quote',
		'supports'            => ['title'],
		'has_archive'         => false,
		'rewrite'             => false,
		'show_in_rest'        => true,
	]);
});





add_action('wp_enqueue_scripts', function () {
	if (is_admin()) {
		return;
	}

	wp_enqueue_style(
		'swiper',
		'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
		[],
		'11.0.7'
	);

	wp_enqueue_script(
		'swiper',
		'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
		[],
		'11.0.7',
		true
	);

	wp_enqueue_script(
		'gelikon-reviews-slider',
		get_template_directory_uri() . '/assets/js/reviews-slider.js',
		['swiper'],
		wp_get_theme()->get('Version'),
		true
	);
}, 30);






add_action('after_setup_theme', function () {
    register_nav_menus([
        'footer_categories' => __('Footer Categories', 'gelikon'),
        'footer_info'       => __('Footer Info', 'gelikon'),
    ]);
});










/**
 * Gelikon Catalog Dropdown for WooCommerce product categories
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Миниатюра категории WooCommerce
 */
if (!function_exists('gelikon_get_term_thumbnail_url')) {
	function gelikon_get_term_thumbnail_url($term_id, $size = 'thumbnail') {
		$thumb_id = get_term_meta($term_id, 'thumbnail_id', true);

		if (!$thumb_id) {
			return '';
		}

		$image = wp_get_attachment_image_src((int) $thumb_id, $size);

		return !empty($image[0]) ? $image[0] : '';
	}
}

/**
 * Дерево категорий WooCommerce
 */
if (!function_exists('gelikon_get_product_cat_tree')) {
	function gelikon_get_product_cat_tree() {
		$top_terms = get_terms([
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'orderby'    => 'menu_order',
			'order'      => 'ASC',
		]);

		if (is_wp_error($top_terms) || empty($top_terms)) {
			return [];
		}

		$tree = [];

		foreach ($top_terms as $top_term) {
			if ($top_term->slug === 'misc') {
				continue;
			}

			$children = get_terms([
				'taxonomy'   => 'product_cat',
				'parent'     => $top_term->term_id,
				'hide_empty' => true,
				'orderby'    => 'menu_order',
				'order'      => 'ASC',
			]);

			if (is_wp_error($children)) {
				$children = [];
			}

			$children = array_values(array_filter($children, function ($child) {
				return $child->slug !== 'misc';
			}));

			$tree[] = [
				'term'     => $top_term,
				'children' => $children,
			];
		}

		return $tree;
	}
}

/**
 * Рендер каталога
 */
if (!function_exists('gelikon_render_catalog_dropdown')) {
	function gelikon_render_catalog_dropdown($args = []) {
		if (!class_exists('WooCommerce')) {
			return '';
		}

		$args = wp_parse_args($args, [
			'title' => 'Каталог',
		]);

		$tree = gelikon_get_product_cat_tree();

		if (empty($tree)) {
			return '';
		}

		$instance_id = 'gl-catalog-' . wp_generate_uuid4();

		ob_start();
		?>
		<div class="gl-catalog-dropdown" id="<?php echo esc_attr($instance_id); ?>">
			<button
				class="gl-catalog-dropdown__toggle"
				type="button"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr($instance_id); ?>-panel"
			>
				<span class="gl-catalog-dropdown__toggle-icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect x="2" y="3" width="12" height="1.5" rx="0.75" fill="currentColor"/>
						<rect x="2" y="7" width="12" height="1.5" rx="0.75" fill="currentColor"/>
						<rect x="2" y="11" width="12" height="1.5" rx="0.75" fill="currentColor"/>
					</svg>
				</span>

				<span class="gl-catalog-dropdown__toggle-text">
					<?php echo esc_html($args['title']); ?>
				</span>

				<span class="gl-catalog-dropdown__toggle-arrow" aria-hidden="true">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M3 5.5L7 9.5L11 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
			</button>

			<div class="gl-catalog-dropdown__panel" id="<?php echo esc_attr($instance_id); ?>-panel" hidden>
				<div class="gl-catalog-dropdown__grid">

					<div class="gl-catalog-dropdown__sidebar">
						<ul class="gl-catalog-dropdown__parents" role="tablist">
							<?php foreach ($tree as $index => $item) :
								$term      = $item['term'];
								$panel_id  = 'cat-' . $term->term_id;
								$is_active = $index === 0;
								?>
								<li class="gl-catalog-dropdown__parent-item">
									<div
										class="gl-catalog-dropdown__parent-row <?php echo $is_active ? 'is-active' : ''; ?>"
										data-target="<?php echo esc_attr($panel_id); ?>"
										role="tab"
										aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
										tabindex="0"
									>
										<a class="gl-catalog-dropdown__parent-link-main" href="<?php echo esc_url(get_term_link($term)); ?>">
											<span class="gl-catalog-dropdown__parent-name">
												<?php echo esc_html($term->name); ?>
											</span>
										</a>

										<span class="gl-catalog-dropdown__parent-meta">
											<?php echo (int) $term->count; ?>
										</span>

										<span class="gl-catalog-dropdown__parent-arrow" aria-hidden="true">›</span>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="gl-catalog-dropdown__content">
						<?php foreach ($tree as $index => $item) :
							$term      = $item['term'];
							$children  = $item['children'];
							$panel_id  = 'cat-' . $term->term_id;
							$is_active = $index === 0;
							?>
							<div
								class="gl-catalog-dropdown__children-panel <?php echo $is_active ? 'is-active' : ''; ?>"
								data-panel="<?php echo esc_attr($panel_id); ?>"
								<?php echo $is_active ? '' : 'hidden'; ?>
							>
								<div class="gl-catalog-dropdown__children-head">
									<a class="gl-catalog-dropdown__parent-link" href="<?php echo esc_url(get_term_link($term)); ?>">
										<?php echo esc_html($term->name); ?>
									</a>
								</div>

								<?php if (!empty($children)) : ?>
									<div class="gl-catalog-dropdown__children-list">
										<?php foreach ($children as $child) :
											$thumb_url = gelikon_get_term_thumbnail_url($child->term_id, 'thumbnail');
											?>
											<a class="gl-catalog-dropdown__child-link" href="<?php echo esc_url(get_term_link($child)); ?>">
												<?php if ($thumb_url) : ?>
													<span class="gl-catalog-dropdown__child-thumb">
														<img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($child->name); ?>" loading="lazy">
													</span>
												<?php endif; ?>

												<span class="gl-catalog-dropdown__child-name">
													<?php echo esc_html($child->name); ?>
												</span>

												<span class="gl-catalog-dropdown__child-count">
													<?php echo (int) $child->count; ?>
												</span>
											</a>
										<?php endforeach; ?>
									</div>
								<?php else : ?>
									<div class="gl-catalog-dropdown__empty">
										<a href="<?php echo esc_url(get_term_link($term)); ?>">
											<?php esc_html_e('Перейти в категорию', 'gelikon'); ?>
										</a>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>

				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}

/**
 * Шорткод
 */
if (!function_exists('gelikon_catalog_dropdown_shortcode')) {
	function gelikon_catalog_dropdown_shortcode($atts = []) {
		$atts = shortcode_atts([
			'title' => 'Каталог',
		], $atts, 'gelikon_catalog_dropdown');

		return gelikon_render_catalog_dropdown($atts);
	}
}
add_shortcode('gelikon_catalog_dropdown', 'gelikon_catalog_dropdown_shortcode');

/**
 * Скрипт
 */
add_action('wp_enqueue_scripts', function () {
	wp_enqueue_script(
		'gelikon-catalog-dropdown',
		get_template_directory_uri() . '/assets/js/catalog-dropdown.js',
		[],
		file_exists(get_template_directory() . '/assets/js/catalog-dropdown.js')
			? filemtime(get_template_directory() . '/assets/js/catalog-dropdown.js')
			: wp_get_theme()->get('Version'),
		true
	);
}, 30);

/**
 * Стили каталога
 */
add_action('wp_head', function () {
	?>
	<style id="gelikon-catalog-dropdown-styles">
		.gl-catalog-dropdown {
			position: relative;
			z-index: 80;
		}

		.gl-catalog-dropdown__toggle {
			display: inline-flex;
			align-items: center;
			gap: 10px;
			min-height: 42px;
			padding: 0 8px 0 4px;
			border: 0;
			border-radius: 8px;
			background: transparent;
			color: #171d2a;
			cursor: pointer;
			font-size: 16px;
			font-weight: 500;
			line-height: 1;
			box-shadow: none;
		}

		.gl-catalog-dropdown__toggle:hover {
			background: rgba(23, 29, 42, 0.04);
		}

		.gl-catalog-dropdown__toggle-icon {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 16px;
			height: 16px;
			color: #7d828a;
			flex: 0 0 16px;
		}

		.gl-catalog-dropdown__toggle-icon svg,
		.gl-catalog-dropdown__toggle-arrow svg {
			display: block;
			width: 100%;
			height: 100%;
		}

		.gl-catalog-dropdown__toggle-text {
			flex: 0 0 auto;
			text-align: left;
			white-space: nowrap;
		}

		.gl-catalog-dropdown__toggle-arrow {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 14px;
			height: 14px;
			color: #7d828a;
			transition: transform .2s ease;
			flex: 0 0 14px;
		}

		.gl-catalog-dropdown.is-open .gl-catalog-dropdown__toggle-arrow {
			transform: rotate(180deg);
		}

		.gl-catalog-dropdown__panel {
			position: absolute;
			top: calc(100% + 8px);
			left: 0;
			width: min(900px, 90vw);
			background: #fff;
			border-radius: 18px;
			box-shadow: 0 24px 60px rgba(0, 0, 0, 0.12);
			overflow: hidden;
		}

		.gl-catalog-dropdown__grid {
			display: grid;
			grid-template-columns: 280px 1fr;
			min-height: 360px;
		}

		.gl-catalog-dropdown__sidebar {
			background: #fafafa;
			border-right: 1px solid #eceff1;
		}

		.gl-catalog-dropdown__parents {
			margin: 0;
			padding: 10px;
			list-style: none;
		}

		.gl-catalog-dropdown__parent-item {
			margin: 0;
		}

		.gl-catalog-dropdown__parent-item + .gl-catalog-dropdown__parent-item {
			margin-top: 6px;
		}

		.gl-catalog-dropdown__parent-row {
			display: grid;
			grid-template-columns: 1fr auto auto;
			align-items: center;
			gap: 8px;
			min-height: 50px;
			padding: 10px 12px;
			border-radius: 14px;
			cursor: pointer;
			transition: background-color .2s ease, color .2s ease;
		}

		.gl-catalog-dropdown__parent-row.is-active {
			background: #fff;
		}

		.gl-catalog-dropdown__parent-link-main {
			text-decoration: none;
			color: #171d2a;
			min-width: 0;
		}

		.gl-catalog-dropdown__parent-row.is-active .gl-catalog-dropdown__parent-link-main {
			color: #2aa5f5;
		}

		.gl-catalog-dropdown__parent-name {
			display: block;
			font-size: 15px;
			line-height: 1.2;
			font-weight: 600;
		}

		.gl-catalog-dropdown__parent-meta {
			font-size: 14px;
			color: #8b9199;
		}

		.gl-catalog-dropdown__parent-arrow {
			font-size: 24px;
			line-height: 1;
			color: #222;
		}

		.gl-catalog-dropdown__content {
			padding: 20px 22px;
		}

		.gl-catalog-dropdown__children-head {
			margin-bottom: 16px;
		}

		.gl-catalog-dropdown__parent-link {
			font-size: 17px;
			font-weight: 700;
			line-height: 1.2;
			color: #171d2a;
			text-decoration: none;
		}

		.gl-catalog-dropdown__children-list {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 12px 22px;
		}

		.gl-catalog-dropdown__child-link {
			display: grid;
			grid-template-columns: auto 1fr auto;
			align-items: center;
			gap: 10px;
			min-height: 38px;
			text-decoration: none;
			color: #171d2a;
			font-size: 14px;
			font-weight: 500;
			line-height: 1.3;
			padding: 4px 0;
		}

		.gl-catalog-dropdown__child-thumb {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 30px;
			height: 30px;
			border-radius: 8px;
			overflow: hidden;
			background: #f6f8f8;
		}

		.gl-catalog-dropdown__child-thumb img {
			display: block;
			width: 100%;
			height: 100%;
			object-fit: contain;
		}

		.gl-catalog-dropdown__child-name {
			min-width: 0;
		}

		.gl-catalog-dropdown__child-count {
			font-size: 13px;
			color: #8b9199;
		}

		.gl-catalog-dropdown__empty a {
			font-size: 14px;
			color: #2aa5f5;
			text-decoration: none;
		}

		@media (max-width: 991px) {
			.gl-catalog-dropdown__toggle {
				min-width: auto;
			}

			.gl-catalog-dropdown__toggle-icon{
				display: none;
			}

			.gl-catalog-dropdown__panel {
				position: static;
				width: 100%;
				margin-top: 8px;
				border-radius: 18px;
				box-shadow: 0 18px 40px rgba(0, 0, 0, 0.08);
			}

			.gl-catalog-dropdown__grid {
				grid-template-columns: 1fr;
				min-height: auto;
			}

			.gl-catalog-dropdown__sidebar {
				border-right: 0;
				border-bottom: 1px solid #eceff1;
			}

			.gl-catalog-dropdown__content {
				padding: 18px;
			}

			.gl-catalog-dropdown__children-list {
				grid-template-columns: 1fr;
			}
		}

		@media (max-width: 767px) {
			.gl-catalog-dropdown__toggle {
				min-height: 38px;
				padding: 0 6px 0 2px;
				font-size: 15px;
				border-radius: 8px;
			}

			.gl-catalog-dropdown__parents {
				padding: 8px;
			}

			.gl-catalog-dropdown__parent-row {
				min-height: 46px;
				padding: 9px 10px;
				border-radius: 12px;
			}

			.gl-catalog-dropdown__parent-name {
				font-size: 14px;
			}

			.gl-catalog-dropdown__parent-link {
				font-size: 16px;
			}

			.gl-catalog-dropdown__child-link {
				font-size: 13px;
			}
		}
	</style>
	<?php
}, 99);











add_action('wp_ajax_gelikon_filter_products', 'gelikon_filter_products_ajax');
add_action('wp_ajax_nopriv_gelikon_filter_products', 'gelikon_filter_products_ajax');

function gelikon_filter_products_ajax() {
	if (!class_exists('WooCommerce')) {
		wp_send_json_error(['message' => 'WooCommerce not active']);
	}

	$term_id  = isset($_POST['term_id']) ? (int) $_POST['term_id'] : 0;
	$page     = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;
	$per_page = isset($_POST['per_page']) ? max(1, (int) $_POST['per_page']) : 12;

	$filters = isset($_POST['filters']) && is_array($_POST['filters']) ? $_POST['filters'] : [];
	$min_price = isset($_POST['min_price']) ? (int) $_POST['min_price'] : 0;
	$max_price = isset($_POST['max_price']) ? (int) $_POST['max_price'] : 0;
	$orderby_selected = isset($_POST['orderby']) ? sanitize_key($_POST['orderby']) : 'menu_order';

	$tax_query = [
		'relation' => 'AND',
	];

	/**
	 * Если term_id > 0 — это страница категории
	 * Если term_id = 0 — это общий каталог, не фильтруем по product_cat
	 */
	if ($term_id > 0) {
		$tax_query[] = [
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => [$term_id],
		];
	}

	foreach ($filters as $taxonomy => $terms) {
		$taxonomy = sanitize_key($taxonomy);

		if (!taxonomy_exists($taxonomy) || empty($terms) || !is_array($terms)) {
			continue;
		}

		$clean_terms = array_filter(array_map('sanitize_title', $terms));

		if (empty($clean_terms)) {
			continue;
		}

		$tax_query[] = [
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => $clean_terms,
			'operator' => 'IN',
		];
	}

	$args = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'paged'          => $page,
		'posts_per_page' => $per_page,
	];

	if (count($tax_query) > 1) {
		$args['tax_query'] = $tax_query;
	}

	if ($min_price > 0 || $max_price > 0) {
		$args['meta_query'] = [
			[
				'key'     => '_price',
				'type'    => 'NUMERIC',
				'compare' => 'BETWEEN',
				'value'   => [
					max(0, $min_price),
					$max_price > 0 ? $max_price : 999999999,
				],
			]
		];
	}

	switch ($orderby_selected) {
		case 'date_desc':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;

		case 'price_asc':
			$args['meta_key'] = '_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
			break;

		case 'price_desc':
			$args['meta_key'] = '_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;

		case 'title_asc':
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
			break;

		case 'menu_order':
		default:
			$args['orderby'] = 'menu_order title';
			$args['order']   = 'ASC';
			break;
	}

	$query = new WP_Query($args);

	ob_start();

	if ($query->have_posts()) {
		echo '<ul class="products columns-3 gl-catalog-products__grid">';

		while ($query->have_posts()) {
			$query->the_post();

			$GLOBALS['product'] = wc_get_product(get_the_ID());

			if (!$GLOBALS['product'] || !$GLOBALS['product']->is_visible()) {
				continue;
			}

			wc_get_template_part('content', 'product');
		}

		echo '</ul>';

		echo '<div class="gl-catalog-pagination">';
		echo paginate_links([
			'total'     => $query->max_num_pages,
			'current'   => $page,
			'prev_text' => '←',
			'next_text' => '→',
		]);
		echo '</div>';
	} else {
		echo '<div class="gl-card gl-catalog-products__empty">';
		echo '<h2>Товары не найдены</h2>';
		echo '<p>Попробуйте изменить фильтры.</p>';
		echo '</div>';
	}

	wp_reset_postdata();

	wp_send_json_success([
		'html'  => ob_get_clean(),
		'count' => (int) $query->found_posts,
	]);
}

add_action('wp_enqueue_scripts', function () {
	if (!is_tax('product_cat') && !is_shop() && !is_post_type_archive('product')) {
		return;
	}

	wp_enqueue_script(
		'gelikon-taxonomy-filters',
		get_template_directory_uri() . '/assets/js/gelikon-taxonomy-filters.js',
		[],
		file_exists(get_template_directory() . '/assets/js/gelikon-taxonomy-filters.js')
			? filemtime(get_template_directory() . '/assets/js/gelikon-taxonomy-filters.js')
			: wp_get_theme()->get('Version'),
		true
	);

	wp_localize_script('gelikon-taxonomy-filters', 'gelikonCatalogAjax', [
		'ajaxurl' => admin_url('admin-ajax.php'),
		'i18n'    => [
			'countSuffix' => 'товаров',
		],
	]);
}, 30);








// Отзывы
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Тип комментария товара:
 * review | question
 */
function gelikon_get_product_comment_type($comment_id) {
	$type = get_comment_meta($comment_id, 'ds_product_comment_type', true);

	if (!$type) {
		$rating = get_comment_meta($comment_id, 'rating', true);
		$type   = $rating ? 'review' : 'review';
	}

	return $type;
}

/**
 * Кол-во вопросов по товару
 */
function gelikon_get_product_questions_count($product_id) {
	$comments = get_comments([
		'post_id' => $product_id,
		'status'  => 'approve',
		'type'    => 'comment',
		'meta_key'   => 'ds_product_comment_type',
		'meta_value' => 'question',
		'count'   => true,
	]);

	return (int) $comments;
}

/**
 * Получить вопросы по товару
 */
function gelikon_get_product_questions($product_id) {
	return get_comments([
		'post_id' => $product_id,
		'status'  => 'approve',
		'type'    => 'comment',
		'meta_key'   => 'ds_product_comment_type',
		'meta_value' => 'question',
		'orderby' => 'comment_date_gmt',
		'order'   => 'DESC',
	]);
}

/**
 * Получить отзывы по товару
 */
function gelikon_get_product_reviews($product_id) {
	return get_comments([
		'post_id' => $product_id,
		'status'  => 'approve',
		'type'    => 'review',
		'orderby' => 'comment_date_gmt',
		'order'   => 'DESC',
	]);
}

/**
 * Обработка отправки вопроса
 */
add_action('init', function () {
	if (
		!isset($_POST['gelikon_submit_product_question']) ||
		!isset($_POST['gelikon_question_nonce'])
	) {
		return;
	}

	if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gelikon_question_nonce'])), 'gelikon_product_question')) {
		return;
	}

	$product_id = isset($_POST['comment_post_ID']) ? absint($_POST['comment_post_ID']) : 0;
	$author     = isset($_POST['author']) ? sanitize_text_field(wp_unslash($_POST['author'])) : '';
	$email      = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
	$content    = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';

	if (!$product_id || get_post_type($product_id) !== 'product' || empty($content)) {
		return;
	}

	$user = wp_get_current_user();

	$commentdata = [
		'comment_post_ID'      => $product_id,
		'comment_content'      => $content,
		'comment_type'         => 'comment',
		'comment_parent'       => 0,
		'user_id'              => get_current_user_id(),
		'comment_author'       => $user->exists() ? $user->display_name : $author,
		'comment_author_email' => $user->exists() ? $user->user_email : $email,
		'comment_approved'     => 0,
	];

	$comment_id = wp_insert_comment(wp_filter_comment($commentdata));

	if ($comment_id) {
		add_comment_meta($comment_id, 'ds_product_comment_type', 'question', true);
	}

	$redirect = get_permalink($product_id);
	if ($redirect) {
		wp_safe_redirect(add_query_arg('question_sent', '1', $redirect . '#gelikon-product-popup'));
		exit;
	}
});



/**
 * ============================================
 * Фото к отзывам WooCommerce
 * - множественная загрузка
 * - drag & drop
 * - превью до отправки
 * - lightbox в отзыве
 * - фото в админке
 * ============================================
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Проверка: это отзыв к товару, а не вопрос
 */
function gelikon_is_product_review_comment($comment_id) {
	$comment = get_comment($comment_id);

	if (!$comment || empty($comment->comment_post_ID)) {
		return false;
	}

	if (get_post_type($comment->comment_post_ID) !== 'product') {
		return false;
	}

	$comment_type_meta = get_comment_meta($comment_id, 'ds_product_comment_type', true);

	if ($comment_type_meta === 'question') {
		return false;
	}

	return true;
}

/**
 * Гарантируем enctype для формы комментариев
 */
add_filter('comment_form_defaults', function ($defaults) {
	if (is_product()) {
		$defaults['enctype'] = 'multipart/form-data';
	}
	return $defaults;
});

/**
 * Поле загрузки фото в форме отзыва
 */
function gelikon_review_images_field() {
	if (!is_product()) {
		return;
	}

	echo '
	<div class="gelikon-review-upload">
		<label class="gelikon-review-upload__label" for="gelikon-review-images">Фото к отзыву</label>

		<div class="gelikon-review-upload__dropzone" id="gelikon-review-dropzone">
			<input
				type="file"
				id="gelikon-review-images"
				name="gelikon_review_images[]"
				accept="image/jpeg,image/png,image/webp"
				multiple
			>

			<div class="gelikon-review-upload__inner">
				<div class="gelikon-review-upload__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="24" height="24" fill="none">
						<path d="M12 16V8M12 8L8.5 11.5M12 8L15.5 11.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
						<path d="M20 16.5C20 18.433 18.433 20 16.5 20H7.5C5.567 20 4 18.433 4 16.5C4 14.818 5.187 13.414 6.771 13.079C7.231 10.746 9.289 9 11.75 9C14.211 9 16.269 10.746 16.729 13.079C18.313 13.414 19.5 14.818 19.5 16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
					</svg>
				</div>

				<div class="gelikon-review-upload__text">
					<div class="gelikon-review-upload__title">Перетащите фото сюда</div>
					<div class="gelikon-review-upload__subtitle">или нажмите, чтобы выбрать изображения</div>
				</div>

				<div class="gelikon-review-upload__button">Выбрать файлы</div>
				<div class="gelikon-review-upload__note">До 5 фото · PNG, JPG, WEBP · до 5 МБ за файл</div>
			</div>
		</div>

		<div class="gelikon-review-preview" id="gelikon-review-preview"></div>
	</div>';
}
add_action('comment_form_logged_in_after', 'gelikon_review_images_field');
add_action('comment_form_after_fields', 'gelikon_review_images_field');

/**
 * Стили на фронте
 */
add_action('wp_head', function () {
	if (!is_product()) {
		return;
	}
	?>
	<style id="gelikon-review-images-style">
		.gelikon-review-upload {
			margin-top: 22px !important;
		}

		.gelikon-review-upload__label {
			display: block !important;
			margin: 0 0 12px !important;
			font-size: 16px !important;
			font-weight: 700 !important;
			line-height: 1.3 !important;
			color: #171d2a !important;
		}

		.gelikon-review-upload__dropzone {
			position: relative !important;
			border: 1px solid #e3e9e5 !important;
			border-radius: 20px !important;
			background: #f8faf9 !important;
			transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease !important;
			overflow: hidden !important;
		}

		.gelikon-review-upload__dropzone:hover {
			border-color: #cfd9d2 !important;
			background: #fbfcfc !important;
		}

		.gelikon-review-upload__dropzone.is-dragover {
			border-color: var(--gl-color-accent) !important;
			background: rgba(34, 197, 94, 0.06) !important;
			box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.08) !important;
		}

		.gelikon-review-upload__dropzone input[type="file"] {
			position: absolute !important;
			inset: 0 !important;
			width: 100% !important;
			height: 100% !important;
			opacity: 0 !important;
			cursor: pointer !important;
			z-index: 5 !important;
			display: block !important;
			font-size: 0 !important;
		}

		.gelikon-review-upload__dropzone input[type="file"]::-webkit-file-upload-button {
			visibility: hidden !important;
			display: none !important;
		}

		.gelikon-review-upload__dropzone input[type="file"]::file-selector-button {
			visibility: hidden !important;
			display: none !important;
		}

		.gelikon-review-upload__inner {
			display: flex !important;
			flex-direction: column !important;
			align-items: center !important;
			justify-content: center !important;
			text-align: center !important;
			padding: 30px 24px !important;
			min-height: 200px !important;
		}

		.gelikon-review-upload__icon {
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			width: 58px !important;
			height: 58px !important;
			margin-bottom: 14px !important;
			border-radius: 16px !important;
			background: rgba(34, 197, 94, 0.10) !important;
			color: var(--gl-color-accent) !important;
		}

		.gelikon-review-upload__text {
			margin-bottom: 16px !important;
		}

		.gelikon-review-upload__title {
			margin: 0 0 6px !important;
			font-size: 22px !important;
			font-weight: 700 !important;
			line-height: 1.2 !important;
			color: #171d2a !important;
		}

		.gelikon-review-upload__subtitle {
			margin: 0 !important;
			font-size: 15px !important;
			line-height: 1.5 !important;
			color: #67707a !important;
		}

		.gelikon-review-upload__button {
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			min-height: 44px !important;
			padding: 0 18px !important;
			border-radius: 999px !important;
			background: var(--gl-color-accent) !important;
			color: #fff !important;
			font-size: 14px !important;
			font-weight: 600 !important;
			line-height: 1 !important;
			box-shadow: 0 8px 18px rgba(34, 197, 94, 0.18) !important;
			pointer-events: none !important;
		}

		.gelikon-review-upload__note {
			margin-top: 12px !important;
			font-size: 13px !important;
			line-height: 1.5 !important;
			color: #8a9199 !important;
		}

		.gelikon-review-preview {
			display: grid !important;
			grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)) !important;
			gap: 12px !important;
			margin-top: 14px !important;
		}

		.gelikon-review-preview__item {
			position: relative !important;
			aspect-ratio: 1 / 1 !important;
			border-radius: 16px !important;
			overflow: hidden !important;
			background: #f3f5f4 !important;
			border: 1px solid #e6ebe8 !important;
			box-shadow: 0 4px 12px rgba(17, 24, 39, 0.04) !important;
		}

		.gelikon-review-preview__item img {
			display: block !important;
			width: 100% !important;
			height: 100% !important;
			object-fit: cover !important;
		}

		.gelikon-review-preview__remove {
			position: absolute !important;
			top: 8px !important;
			right: 8px !important;
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			width: 28px !important;
			height: 28px !important;
			border: 0 !important;
			border-radius: 50% !important;
			background: rgba(23, 29, 42, 0.78) !important;
			color: #fff !important;
			font-size: 18px !important;
			line-height: 1 !important;
			cursor: pointer !important;
			padding: 0 !important;
		}

		.gelikon-review-gallery {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
			gap: 10px;
			margin-top: 14px;
			max-width: 460px;
		}

		.gelikon-review-gallery__link {
			display: block;
			border-radius: 14px;
			overflow: hidden;
			border: 1px solid #e6ebe8;
			background: #f5f7f6;
			transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
		}

		.gelikon-review-gallery__link:hover {
			transform: translateY(-2px);
			border-color: #d4ddd7;
			box-shadow: 0 8px 18px rgba(17, 24, 39, 0.08);
		}

		.gelikon-review-gallery__link img {
			display: block;
			width: 100%;
			height: 88px;
			object-fit: cover;
		}

		.gelikon-lightbox {
			position: fixed;
			inset: 0;
			z-index: 999999;
			display: none;
			align-items: center;
			justify-content: center;
			padding: 24px;
			background: rgba(10, 14, 20, 0.9);
			backdrop-filter: blur(4px);
		}

		.gelikon-lightbox.is-open {
			display: flex;
		}

		.gelikon-lightbox__inner {
			position: relative;
			max-width: min(1120px, 95vw);
			max-height: 90vh;
		}

		.gelikon-lightbox__img {
			display: block;
			max-width: 100%;
			max-height: 90vh;
			border-radius: 18px;
			box-shadow: 0 18px 40px rgba(0,0,0,.35);
		}

		.gelikon-lightbox__close {
			position: absolute;
			top: -14px;
			right: -14px;
			width: 42px;
			height: 42px;
			border: 0;
			border-radius: 50%;
			background: #fff;
			color: #171d2a;
			font-size: 24px;
			line-height: 1;
			cursor: pointer;
			box-shadow: 0 6px 18px rgba(0,0,0,.18);
		}

		@media (max-width: 767px) {
			.gelikon-review-upload__inner {
				min-height: 160px !important;
				padding: 22px 16px !important;
			}

			.gelikon-review-upload__title {
				font-size: 18px !important;
			}

			.gelikon-review-upload__subtitle {
				font-size: 14px !important;
			}

			.gelikon-review-upload__button {
				min-height: 40px !important;
				padding: 0 16px !important;
				font-size: 13px !important;
			}

			.gelikon-review-preview {
				grid-template-columns: repeat(auto-fill, minmax(84px, 1fr)) !important;
			}
		}
	</style>
	<?php
}, 30);

/**
 * JS на фронте
 */
add_action('wp_footer', function () {
	if (!is_product()) {
		return;
	}
	?>
	<script id="gelikon-review-images-script">
	document.addEventListener('DOMContentLoaded', function () {
		const form = document.getElementById('commentform');
		const input = document.getElementById('gelikon-review-images');
		const dropzone = document.getElementById('gelikon-review-dropzone');
		const preview = document.getElementById('gelikon-review-preview');

		if (form) {
			form.setAttribute('enctype', 'multipart/form-data');
		}

		if (form && input && dropzone && preview) {
			let dt = new DataTransfer();
			const MAX_FILES = 5;

			function syncInputFiles() {
				input.files = dt.files;
			}

			function renderPreview() {
				preview.innerHTML = '';

				Array.from(dt.files).forEach(function (file, index) {
					if (!file.type.startsWith('image/')) return;

					const reader = new FileReader();

					reader.onload = function (e) {
						const item = document.createElement('div');
						item.className = 'gelikon-review-preview__item';

						const img = document.createElement('img');
						img.src = e.target.result;
						img.alt = file.name;

						const removeBtn = document.createElement('button');
						removeBtn.type = 'button';
						removeBtn.className = 'gelikon-review-preview__remove';
						removeBtn.innerHTML = '&times;';

						removeBtn.addEventListener('click', function () {
							const newDt = new DataTransfer();

							Array.from(dt.files).forEach(function (f, i) {
								if (i !== index) {
									newDt.items.add(f);
								}
							});

							dt = newDt;
							syncInputFiles();
							renderPreview();
						});

						item.appendChild(img);
						item.appendChild(removeBtn);
						preview.appendChild(item);
					};

					reader.readAsDataURL(file);
				});
			}

			function addFiles(files) {
				Array.from(files).forEach(function (file) {
					if (!file.type.startsWith('image/')) return;
					if (dt.files.length >= MAX_FILES) return;

					const exists = Array.from(dt.files).some(function (f) {
						return f.name === file.name && f.size === file.size && f.lastModified === file.lastModified;
					});

					if (!exists) {
						dt.items.add(file);
					}
				});

				syncInputFiles();
				renderPreview();
			}

			input.addEventListener('change', function () {
				addFiles(input.files);
			});

			['dragenter', 'dragover'].forEach(function (eventName) {
				dropzone.addEventListener(eventName, function (e) {
					e.preventDefault();
					e.stopPropagation();
					dropzone.classList.add('is-dragover');
				});
			});

			['dragleave', 'drop'].forEach(function (eventName) {
				dropzone.addEventListener(eventName, function (e) {
					e.preventDefault();
					e.stopPropagation();
					dropzone.classList.remove('is-dragover');
				});
			});

			dropzone.addEventListener('drop', function (e) {
				if (e.dataTransfer && e.dataTransfer.files) {
					addFiles(e.dataTransfer.files);
				}
			});
		}

		const galleryLinks = document.querySelectorAll('.gelikon-review-gallery__link');

		if (galleryLinks.length) {
			let lightbox = document.querySelector('.gelikon-lightbox');

			if (!lightbox) {
				lightbox = document.createElement('div');
				lightbox.className = 'gelikon-lightbox';
				lightbox.innerHTML = `
					<div class="gelikon-lightbox__inner">
						<button type="button" class="gelikon-lightbox__close" aria-label="Закрыть">&times;</button>
						<img class="gelikon-lightbox__img" src="" alt="">
					</div>
				`;
				document.body.appendChild(lightbox);
			}

			const img = lightbox.querySelector('.gelikon-lightbox__img');
			const closeBtn = lightbox.querySelector('.gelikon-lightbox__close');

			galleryLinks.forEach(function (link) {
				link.addEventListener('click', function (e) {
					e.preventDefault();
					const href = link.getAttribute('href');
					if (!href) return;

					img.src = href;
					lightbox.classList.add('is-open');
					document.body.style.overflow = 'hidden';
				});
			});

			function closeLightbox() {
				lightbox.classList.remove('is-open');
				img.src = '';
				document.body.style.overflow = '';
			}

			closeBtn.addEventListener('click', closeLightbox);

			lightbox.addEventListener('click', function (e) {
				if (e.target === lightbox) {
					closeLightbox();
				}
			});

			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && lightbox.classList.contains('is-open')) {
					closeLightbox();
				}
			});
		}
	});
	</script>
	<?php
}, 99);

/**
 * Ограничение размера файлов
 */
add_filter('wp_handle_upload_prefilter', function ($file) {
	if (!is_array($file) || empty($file['name'])) {
		return $file;
	}

	if (!empty($file['size']) && (int) $file['size'] > 5 * 1024 * 1024) {
		$file['error'] = 'Каждое изображение должно быть не больше 5 МБ.';
	}

	return $file;
});

/**
 * Сохраняем фото к отзыву
 */
add_action('comment_post', function ($comment_id, $comment_approved, $commentdata) {
	if (!gelikon_is_product_review_comment($comment_id)) {
		return;
	}

	if (
		empty($_FILES['gelikon_review_images']) ||
		empty($_FILES['gelikon_review_images']['name']) ||
		!is_array($_FILES['gelikon_review_images']['name'])
	) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$files = $_FILES['gelikon_review_images'];
	$attachment_ids = [];
	$max_files = 5;
	$allowed = ['image/jpeg', 'image/png', 'image/webp'];

	foreach ($files['name'] as $index => $name) {
		if (count($attachment_ids) >= $max_files) {
			break;
		}

		if (empty($name)) {
			continue;
		}

		$file = [
			'name'     => $files['name'][$index],
			'type'     => $files['type'][$index],
			'tmp_name' => $files['tmp_name'][$index],
			'error'    => $files['error'][$index],
			'size'     => $files['size'][$index],
		];

		if (!empty($file['error'])) {
			continue;
		}

		if (!in_array($file['type'], $allowed, true)) {
			continue;
		}

		$_FILES['gelikon_single_review_image'] = $file;

		$attachment_id = media_handle_upload('gelikon_single_review_image', 0);

		if (!is_wp_error($attachment_id) && $attachment_id) {
			$attachment_ids[] = (int) $attachment_id;
		}
	}

	unset($_FILES['gelikon_single_review_image']);

	if (!empty($attachment_ids)) {
		update_comment_meta($comment_id, 'gelikon_review_image_ids', $attachment_ids);
	}
}, 10, 3);

/**
 * Галерея на фронте
 */
add_action('woocommerce_review_after_comment_text', function ($comment) {
	if (!$comment || empty($comment->comment_ID)) {
		return;
	}

	$image_ids = get_comment_meta($comment->comment_ID, 'gelikon_review_image_ids', true);

	if (empty($image_ids) || !is_array($image_ids)) {
		return;
	}

	echo '<div class="gelikon-review-gallery">';

	foreach ($image_ids as $attachment_id) {
		$full  = wp_get_attachment_image_url($attachment_id, 'full');
		$thumb = wp_get_attachment_image_url($attachment_id, 'woocommerce_thumbnail');

		if (!$full) {
			continue;
		}

		if (!$thumb) {
			$thumb = $full;
		}

		echo '<a href="' . esc_url($full) . '" class="gelikon-review-gallery__link">';
		echo '<img src="' . esc_url($thumb) . '" alt="">';
		echo '</a>';
	}

	echo '</div>';
});

/**
 * Колонка с фото в списке комментариев
 */
add_filter('manage_edit-comments_columns', function ($columns) {
	$new_columns = [];

	foreach ($columns as $key => $label) {
		$new_columns[$key] = $label;

		if ($key === 'comment') {
			$new_columns['gelikon_review_images'] = 'Фото';
		}
	}

	return $new_columns;
});

add_action('manage_comments_custom_column', function ($column, $comment_ID) {
	if ($column !== 'gelikon_review_images') {
		return;
	}

	$comment = get_comment($comment_ID);

	if (!$comment || get_post_type($comment->comment_post_ID) !== 'product') {
		echo '—';
		return;
	}

	$image_ids = get_comment_meta($comment_ID, 'gelikon_review_image_ids', true);

	if (empty($image_ids) || !is_array($image_ids)) {
		echo '—';
		return;
	}

	echo '<div class="gelikon-admin-review-images">';

	foreach ($image_ids as $attachment_id) {
		$thumb = wp_get_attachment_image_url($attachment_id, 'thumbnail');
		$full  = wp_get_attachment_image_url($attachment_id, 'full');

		if (!$thumb) {
			continue;
		}

		echo '<a href="' . esc_url($full ? $full : $thumb) . '" target="_blank" rel="noopener noreferrer">';
		echo '<img src="' . esc_url($thumb) . '" alt="">';
		echo '</a>';
	}

	echo '</div>';
}, 10, 2);

/**
 * Метабокс на странице редактирования комментария
 */
add_action('add_meta_boxes_comment', function () {
	add_meta_box(
		'gelikon-review-images-meta-box',
		'Фото к отзыву',
		'gelikon_render_review_images_metabox',
		'comment',
		'normal',
		'default'
	);
});

function gelikon_render_review_images_metabox($comment) {
	if (!$comment || empty($comment->comment_ID)) {
		echo '<p>Нет данных.</p>';
		return;
	}

	if (!gelikon_is_product_review_comment($comment->comment_ID)) {
		echo '<p>У этого комментария нет фото отзыва.</p>';
		return;
	}

	$image_ids = get_comment_meta($comment->comment_ID, 'gelikon_review_image_ids', true);

	if (empty($image_ids) || !is_array($image_ids)) {
		echo '<p>Фото не прикреплены.</p>';
		return;
	}

	echo '<div class="gelikon-admin-review-images">';

	foreach ($image_ids as $attachment_id) {
		$thumb = wp_get_attachment_image_url($attachment_id, 'medium');
		$full  = wp_get_attachment_image_url($attachment_id, 'full');

		if (!$thumb) {
			continue;
		}

		echo '<a href="' . esc_url($full ? $full : $thumb) . '" target="_blank" rel="noopener noreferrer">';
		echo '<img src="' . esc_url($thumb) . '" alt="">';
		echo '</a>';
	}

	echo '</div>';
}

/**
 * Стили в админке
 */
add_action('admin_head-edit-comments.php', function () {
	?>
	<style>
		.gelikon-admin-review-images {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			margin-top: 6px;
		}

		.gelikon-admin-review-images a {
			display: inline-block;
			border: 1px solid #dcdcde;
			border-radius: 8px;
			overflow: hidden;
			background: #fff;
		}

		.gelikon-admin-review-images img {
			display: block;
			width: 60px;
			height: 60px;
			object-fit: cover;
		}
	</style>
	<?php
});

add_action('admin_head-comment.php', function () {
	?>
	<style>
		.gelikon-admin-review-images {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			margin-top: 6px;
		}

		.gelikon-admin-review-images a {
			display: inline-block;
			border: 1px solid #dcdcde;
			border-radius: 8px;
			overflow: hidden;
			background: #fff;
		}

		.gelikon-admin-review-images img {
			display: block;
			width: 120px;
			height: 120px;
			object-fit: cover;
		}
	</style>
	<?php
});













if (!defined('ABSPATH')) {
	exit;
}

/**
 * Gelikon breadcrumbs
 * Шорткод: [gelikon_breadcrumbs]
 */
add_shortcode('gelikon_breadcrumbs', 'gelikon_breadcrumbs_shortcode');

function gelikon_breadcrumbs_shortcode($atts = []) {
	if (is_front_page()) {
		return '';
	}

	$items = gelikon_get_breadcrumb_items();

	if (empty($items) || !is_array($items)) {
		return '';
	}

	ob_start();
	?>
	<nav class="gl-breadcrumbs" aria-label="Хлебные крошки">
		<ol class="gl-breadcrumbs__list">
			<?php foreach ($items as $index => $item) : ?>
				<?php
				$is_last = ($index === array_key_last($items));
				$title   = isset($item['title']) ? wp_strip_all_tags($item['title']) : '';
				$url     = isset($item['url']) ? $item['url'] : '';
				$home    = !empty($item['home']);
				?>
				<li class="gl-breadcrumbs__item<?php echo $is_last ? ' is-current' : ''; ?>">
					<?php if (!$is_last && !empty($url)) : ?>
						<a class="gl-breadcrumbs__link<?php echo $home ? ' is-home' : ''; ?>" href="<?php echo esc_url($url); ?>">
							<?php if ($home) : ?>
								<span class="gl-breadcrumbs__home-icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path d="M10.94 3.94a1.5 1.5 0 0 1 2.12 0l7 7A1.5 1.5 0 0 1 19 13.5h-.5V19A1.5 1.5 0 0 1 17 20.5h-3.5a.5.5 0 0 1-.5-.5v-4a1 1 0 0 0-1-1h0a1 1 0 0 0-1 1v4a.5.5 0 0 1-.5.5H7A1.5 1.5 0 0 1 5.5 19v-5.5H5a1.5 1.5 0 0 1-1.06-2.56l7-7Z"/>
									</svg>
								</span>
							<?php endif; ?>
							<span class="gl-breadcrumbs__text"><?php echo esc_html($title); ?></span>
						</a>
					<?php else : ?>
						<span class="gl-breadcrumbs__current">
							<?php echo esc_html($title); ?>
						</span>
					<?php endif; ?>

					<?php if (!$is_last) : ?>
						<span class="gl-breadcrumbs__sep" aria-hidden="true">›</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php

	return ob_get_clean();
}

/**
 * Собираем элементы хлебных крошек
 */
function gelikon_get_breadcrumb_items() {
	$items = [];

	$items[] = [
		'title' => 'Главная',
		'url'   => home_url('/'),
		'home'  => true,
	];

	$shop_page_id    = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;
	$shop_page_title = $shop_page_id && $shop_page_id > 0 ? get_the_title($shop_page_id) : 'Каталог';
	$shop_page_url   = $shop_page_id && $shop_page_id > 0 ? get_permalink($shop_page_id) : home_url('/shop/');

	if (function_exists('is_shop') && is_shop()) {
		$items[] = [
			'title' => $shop_page_title ?: 'Каталог',
			'url'   => '',
		];
		return $items;
	}

	if (function_exists('is_product_category') && is_product_category()) {
		$items[] = [
			'title' => $shop_page_title ?: 'Каталог',
			'url'   => $shop_page_url,
		];

		$term = get_queried_object();
		if ($term instanceof WP_Term) {
			$ancestors = array_reverse(get_ancestors($term->term_id, 'product_cat'));

			foreach ($ancestors as $ancestor_id) {
				$ancestor = get_term($ancestor_id, 'product_cat');
				if ($ancestor && !is_wp_error($ancestor)) {
					$items[] = [
						'title' => $ancestor->name,
						'url'   => get_term_link($ancestor),
					];
				}
			}

			$items[] = [
				'title' => $term->name,
				'url'   => '',
			];
		}

		return $items;
	}

	if (function_exists('is_product') && is_product()) {
		$items[] = [
			'title' => $shop_page_title ?: 'Каталог',
			'url'   => $shop_page_url,
		];

		$product_id = get_the_ID();
		$terms      = get_the_terms($product_id, 'product_cat');

		if ($terms && !is_wp_error($terms)) {
			$deepest_term = gelikon_get_deepest_term($terms, 'product_cat');

			if ($deepest_term) {
				$ancestors = array_reverse(get_ancestors($deepest_term->term_id, 'product_cat'));

				foreach ($ancestors as $ancestor_id) {
					$ancestor = get_term($ancestor_id, 'product_cat');
					if ($ancestor && !is_wp_error($ancestor)) {
						$items[] = [
							'title' => $ancestor->name,
							'url'   => get_term_link($ancestor),
						];
					}
				}

				$items[] = [
					'title' => $deepest_term->name,
					'url'   => get_term_link($deepest_term),
				];
			}
		}

		// Название товара убрано
		return $items;
	}

	if (is_singular('page') && !is_front_page()) {
		$page_id   = get_the_ID();
		$ancestors = array_reverse(get_post_ancestors($page_id));

		foreach ($ancestors as $ancestor_id) {
			$items[] = [
				'title' => get_the_title($ancestor_id),
				'url'   => get_permalink($ancestor_id),
			];
		}

		$items[] = [
			'title' => get_the_title($page_id),
			'url'   => '',
		];

		return $items;
	}

	if (is_singular('post')) {
		// Название статьи убрано
		return $items;
	}

	if (is_singular()) {
		$items[] = [
			'title' => get_the_title(),
			'url'   => '',
		];
		return $items;
	}

	if (is_category()) {
		$term = get_queried_object();
		if ($term instanceof WP_Term) {
			$ancestors = array_reverse(get_ancestors($term->term_id, 'category'));

			foreach ($ancestors as $ancestor_id) {
				$ancestor = get_term($ancestor_id, 'category');
				if ($ancestor && !is_wp_error($ancestor)) {
					$items[] = [
						'title' => $ancestor->name,
						'url'   => get_term_link($ancestor),
					];
				}
			}

			$items[] = [
				'title' => $term->name,
				'url'   => '',
			];
		}
		return $items;
	}

	if (is_search()) {
		$search_query = get_search_query();

		// Добавляем "Каталог" перед результатами поиска
		if (function_exists('is_woocommerce') || isset($_GET['post_type']) && $_GET['post_type'] === 'product') {
			$items[] = [
				'title' => $shop_page_title ?: 'Каталог',
				'url'   => $shop_page_url,
			];
		}

		$items[] = [
			'title' => $search_query
				? 'Результаты поиска: ' . $search_query
				: 'Результаты поиска',
			'url'   => '',
		];

		return $items;
	}

	if (is_404()) {
		$items[] = [
			'title' => 'Страница не найдена',
			'url'   => '',
		];
		return $items;
	}

	$items[] = [
		'title' => wp_get_document_title(),
		'url'   => '',
	];

	return $items;
}

/**
 * Самый глубокий термин
 */
function gelikon_get_deepest_term($terms, $taxonomy) {
	if (empty($terms) || !is_array($terms)) {
		return null;
	}

	$deepest = null;
	$max_depth = -1;

	foreach ($terms as $term) {
		if (!$term instanceof WP_Term) {
			continue;
		}

		$depth = count(get_ancestors($term->term_id, $taxonomy));

		if ($depth > $max_depth) {
			$max_depth = $depth;
			$deepest   = $term;
		}
	}

	return $deepest;
}

/**
 * Подключаем стили
 */
add_action('wp_head', 'gelikon_breadcrumbs_inline_styles', 99);


function gelikon_breadcrumbs_inline_styles() {
	?>
	<style>
		.gl-breadcrumbs{
			margin: 0 0 24px;
		}

		.gl-breadcrumbs__list{
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 8px 10px;
			margin: 0;
			padding: 0;
			list-style: none;
		}

		.gl-breadcrumbs__item{
			display: inline-flex;
			align-items: center;
			gap: 8px;
			min-width: 0;
			font-size: 16px;
			line-height: 1.35;
			font-weight: 600;
		}

		.gl-breadcrumbs__link{
			display: inline-flex;
			align-items: center;
			gap: 8px;
			color: var(--gl-color-helper);
			text-decoration: none;
			transition: color .2s ease, opacity .2s ease;
			min-width: 0;
		}

		.gl-breadcrumbs__link:hover{
			color: var(--gl-color-text);
		}

		.gl-breadcrumbs__link.is-home{
			gap: 10px;
		}

		.gl-breadcrumbs__home-icon{
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 22px;
			height: 22px;
			color: #c4c4c7;
			flex: 0 0 auto;
		}

		.gl-breadcrumbs__home-icon svg{
			display: block;
			width: 100%;
			height: 100%;
		}

		.gl-breadcrumbs__text,
		.gl-breadcrumbs__current{
			display: inline-block;
			white-space: normal;
			word-break: break-word;
		}

		.gl-breadcrumbs__current{
			color: var(--gl-color-helper);
			font-weight: 500;
		}

		.gl-breadcrumbs__sep{
			display: inline-flex;
			align-items: center;
			justify-content: center;
			color: #c4c4c7;
			font-size: 22px;
			line-height: 1;
			transform: translateY(-1px);
			flex: 0 0 auto;
		}

		@media (max-width: 767px){
			.gl-breadcrumbs{
				margin: 0 0 18px;
			}

			.gl-breadcrumbs__list{
				gap: 6px 8px;
			}

			.gl-breadcrumbs__item{
				font-size: 14px;
				line-height: 1.3;
			}

			.gl-breadcrumbs__sep{
				font-size: 18px;
			}

			.gl-breadcrumbs__home-icon{
				width: 18px;
				height: 18px;
			}
		}
	</style>
	<?php
}






// Бейджи в карточках и на странице товара

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Тексты плашек
 */
function gelikon_get_product_badge_label_map() {
	return [
		'hit'        => 'Хит',
		'best_price' => 'Лучшая цена',
		'new'        => 'Новинка',
		'sale'       => 'Скидка',
		'top'        => 'Топ',
	];
}

/**
 * CSS-классы цветов
 */
function gelikon_get_product_badge_color_class($color) {
	$allowed = [
		'green',
		'blue',
		'orange',
		'red',
		'dark',
		'white',
	];

	$color = sanitize_key((string) $color);

	if (!in_array($color, $allowed, true)) {
		$color = 'green';
	}

	return 'gl-badge--' . $color;
}

/**
 * Собираем плашки товара из ACF
 */
function gelikon_get_product_badges($product_id) {
	if (!$product_id || !function_exists('get_field')) {
		return [];
	}

	$rows = get_field('product_badges', $product_id);
	if (empty($rows) || !is_array($rows)) {
		return [];
	}

	$label_map = gelikon_get_product_badge_label_map();
	$badges    = [];

	foreach ($rows as $row) {
		$enabled = !empty($row['enabled']);
		$key     = isset($row['label']) ? sanitize_key($row['label']) : '';
		$color   = isset($row['color']) ? sanitize_key($row['color']) : 'green';

		if (!$enabled || !$key) {
			continue;
		}

		$text = isset($label_map[$key]) ? $label_map[$key] : $key;

		$badges[] = [
			'key'   => $key,
			'text'  => $text,
			'color' => $color,
			'class' => gelikon_get_product_badge_color_class($color),
		];
	}

	return $badges;
}

/**
 * HTML плашек
 */
function gelikon_render_product_badges($product_id, $context = 'card') {
	$badges = gelikon_get_product_badges($product_id);

	if (empty($badges)) {
		return '';
	}

	$context_class = $context === 'single'
		? 'gl-product-badges gl-product-badges--single'
		: 'gl-product-badges gl-product-badges--card';

	ob_start();
	?>
	<div class="<?php echo esc_attr($context_class); ?>">
		<?php foreach ($badges as $badge) : ?>
			<span class="gl-product-badge <?php echo esc_attr($badge['class']); ?>">
				<?php echo esc_html($badge['text']); ?>
			</span>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Стили плашек
 */
add_action('wp_head', function () {
	?>
	<style>
		.gl-product-badges{
			position: absolute;
			left: 16px;
			top: 16px;
			z-index: 6;
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			gap: 8px;
			pointer-events: none;
			max-width: calc(100% - 32px);
		}

		.gl-product-badges--single{
			left: 18px;
			top: 18px;
			gap: 10px;
			max-width: calc(100% - 36px);
		}

		.gl-product-badge{
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-height: 32px;
			padding: 0 14px;
			border-radius: 999px;
			font-size: 14px;
			line-height: 1;
			font-weight: 700;
			white-space: nowrap;
			box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
		}

		.gl-product-badges--single .gl-product-badge{
			min-height: 36px;
			padding: 0 16px;
			font-size: 15px;
		}

		.gl-badge--green{
			background: #eafaf0;
			color: var(--gl-color-accent);
			border: 1px solid rgba(34, 197, 94, 0.45);
		}

		.gl-badge--blue{
			background: #eef9ff;
			color: #38bdf8;
			border: 1px solid rgba(56, 189, 248, 0.45);
		}

		.gl-badge--orange{
			background: #fff4e8;
			color: #f97316;
			border: 1px solid rgba(249, 115, 22, 0.42);
		}

		.gl-badge--red{
			background: #fff1f2;
			color: #ef4444;
			border: 1px solid rgba(239, 68, 68, 0.42);
		}

		.gl-badge--dark{
			background: #1f2937;
			color: #ffffff;
			border: 1px solid #1f2937;
		}

		.gl-badge--white{
			background: rgba(255,255,255,0.96);
			color: #1f2937;
			border: 1px solid #e5e7eb;
		}

		/* Чтобы плашки нормально сидели на карточке */
		.gl-product-card__media{
			position: relative;
		}

		/* Чтобы плашки нормально сидели на галерее товара */
		.gl-product-gallery{
			position: relative;
		}

		.gl-product-gallery .woocommerce-product-gallery{
			position: relative;
		}

		@media (max-width: 767px){
			.gl-product-badges{
				left: 12px;
				top: 12px;
				gap: 6px;
				max-width: calc(100% - 24px);
			}

			.gl-product-badge{
				min-height: 28px;
				padding: 0 12px;
				font-size: 13px;
			}

			.gl-product-badges--single{
				left: 12px;
				top: 12px;
			}

			.gl-product-badges--single .gl-product-badge{
				min-height: 32px;
				padding: 0 13px;
				font-size: 13px;
			}
		}
	</style>
	<?php
}, 99);














if (!defined('ABSPATH')) {
	exit;
}

/**
 * Шорткод: [gelikon_cookie_notice]
 */
if (!function_exists('gelikon_cookie_notice_shortcode')) {
	function gelikon_cookie_notice_shortcode($atts = []) {
		$atts = shortcode_atts([
			'text'        => 'Мы используем cookies, чтобы сайт работал корректно и был удобнее для вас.',
			'more_text'   => 'Подробнее',
			'accept_text' => 'Принять',
			'more_url'    => home_url('/cookies/'),
		], $atts, 'gelikon_cookie_notice');

		ob_start();
		?>
		<div class="gl-cookie-notice" id="gl-cookie-notice" hidden>
			<div class="gl-cookie-notice__inner">
				<div class="gl-cookie-notice__text">
					<?php echo esc_html($atts['text']); ?>
				</div>

				<div class="gl-cookie-notice__actions">
					<a
						class="gl-cookie-notice__button gl-cookie-notice__button--ghost"
						href="<?php echo esc_url($atts['more_url']); ?>"
					>
						<?php echo esc_html($atts['more_text']); ?>
					</a>

					<button
						type="button"
						class="gl-cookie-notice__button gl-cookie-notice__button--accent"
						id="gl-cookie-notice-accept"
					>
						<?php echo esc_html($atts['accept_text']); ?>
					</button>
				</div>
			</div>
		</div>

		<style>
			.gl-cookie-notice{
				position: fixed;
				left: 20px;
				right: 20px;
				bottom: 20px;
				z-index: 99999;
				display: flex;
				justify-content: center;
				pointer-events: none;
			}

			.gl-cookie-notice__inner{
				width: 100%;
				max-width: 920px;
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 20px;
				padding: 18px 22px;
				border-radius: 20px;
				background: rgba(23, 29, 42, 0.96);
				color: #fff;
				box-shadow: 0 18px 50px rgba(0,0,0,.22);
				pointer-events: auto;
			}

			.gl-cookie-notice__text{
				font-size: 15px;
				line-height: 1.5;
				color: rgba(255,255,255,.92);
			}

			.gl-cookie-notice__actions{
				display: flex;
				align-items: center;
				gap: 12px;
				flex: 0 0 auto;
			}

			.gl-cookie-notice__button{
				display: inline-flex;
				align-items: center;
				justify-content: center;
				min-height: 44px;
				padding: 0 18px;
				border-radius: 999px;
				border: 1px solid transparent;
				text-decoration: none;
				font-size: 14px;
				font-weight: 600;
				cursor: pointer;
				transition: .2s ease;
			}

			.gl-cookie-notice__button--ghost{
				background: transparent;
				border-color: rgba(255,255,255,.22);
				color: #fff;
			}

			.gl-cookie-notice__button--ghost:hover{
				background: rgba(255,255,255,.08);
				color: #fff;
			}

			.gl-cookie-notice__button--accent{
				background: var(--gl-color-accent);
				border-color: var(--gl-color-accent);
				color: #fff;
			}

			.gl-cookie-notice__button--accent:hover{
				filter: brightness(.96);
			}

			@media (max-width: 767px){
				.gl-cookie-notice{
					left: 12px;
					right: 12px;
					bottom: 12px;
				}

				.gl-cookie-notice__inner{
					flex-direction: column;
					align-items: stretch;
					padding: 16px;
					border-radius: 18px;
				}

				.gl-cookie-notice__actions{
					width: 100%;
					flex-direction: column;
				}

				.gl-cookie-notice__button{
					width: 100%;
				}
			}
		</style>

		<script>
			(function () {
				function getCookie(name) {
					var matches = document.cookie.match(
						new RegExp("(?:^|; )" + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + "=([^;]*)")
					);
					return matches ? decodeURIComponent(matches[1]) : null;
				}

				function setCookie(name, value, days) {
					var expires = "";
					if (days) {
						var date = new Date();
						date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
						expires = "; expires=" + date.toUTCString();
					}
					document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/; SameSite=Lax";
				}

				document.addEventListener('DOMContentLoaded', function () {
					var notice = document.getElementById('gl-cookie-notice');
					var acceptBtn = document.getElementById('gl-cookie-notice-accept');
					var cookieName = 'gelikon_cookie_notice_accepted';

					if (!notice || !acceptBtn) {
						return;
					}

					if (getCookie(cookieName) === '1') {
						notice.remove();
						return;
					}

					notice.hidden = false;

					acceptBtn.addEventListener('click', function () {
						setCookie(cookieName, '1', 365);
						notice.remove();
					}, { once: true });
				});
			})();
		</script>
		<?php
		return ob_get_clean();
	}
}
add_shortcode('gelikon_cookie_notice', 'gelikon_cookie_notice_shortcode');

add_action('wp_footer', function () {
	echo do_shortcode('[gelikon_cookie_notice]');
}, 50);












/**
 * Кастомная галерея товара Gelikon:
 * - видео как элемент галереи
 * - фото товара
 * - миниатюры
 * - переключение main/thumb
 */

if (!defined('ABSPATH')) {
	exit;
}

function gelikon_get_product_media_items($product_id) {
	$items = [];
	$product_id = absint($product_id);

	if (!$product_id) {
		return $items;
	}

	$product = wc_get_product($product_id);
	if (!$product) {
		return $items;
	}

	/**
	 * 1. Видео товара
	 * Добавляем только если:
	 * - meta существует
	 * - attachment существует
	 * - mime video/*
	 * - есть URL
	 */
	$video_id = (int) get_post_meta($product_id, '_gelikon_product_video_id', true);

	if ($video_id > 0) {
		$video_post = get_post($video_id);
		$video_url  = wp_get_attachment_url($video_id);
		$mime_type  = get_post_mime_type($video_id);

		if (
			$video_post &&
			$video_post->post_type === 'attachment' &&
			$video_url &&
			$mime_type &&
			strpos($mime_type, 'video/') === 0
		) {
			$video_thumb = wp_get_attachment_image_url($video_id, 'woocommerce_thumbnail');

			$items[] = [
				'type'     => 'video',
				'id'       => $video_id,
				'full'     => $video_url,
				'thumb'    => $video_thumb ? $video_thumb : '',
				'alt'      => get_the_title($product_id) . ' — видео',
				'is_video' => true,
			];
		}
	}

	/**
	 * 2. Главное изображение товара
	 */
	$featured_id = (int) $product->get_image_id();

	if ($featured_id > 0) {
		$featured_full  = wp_get_attachment_image_url($featured_id, 'full');
		$featured_thumb = wp_get_attachment_image_url($featured_id, 'woocommerce_thumbnail');

		if ($featured_full) {
			$items[] = [
				'type'     => 'image',
				'id'       => $featured_id,
				'full'     => $featured_full,
				'thumb'    => $featured_thumb ? $featured_thumb : $featured_full,
				'alt'      => get_post_meta($featured_id, '_wp_attachment_image_alt', true) ?: get_the_title($product_id),
				'is_video' => false,
			];
		}
	}

	/**
	 * 3. Остальные изображения галереи
	 */
	$gallery_ids = $product->get_gallery_image_ids();

	if (!empty($gallery_ids) && is_array($gallery_ids)) {
		foreach ($gallery_ids as $image_id) {
			$image_id = (int) $image_id;

			if ($image_id <= 0) {
				continue;
			}

			if ($image_id === $featured_id) {
				continue;
			}

			$image_full  = wp_get_attachment_image_url($image_id, 'full');
			$image_thumb = wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail');

			if (!$image_full) {
				continue;
			}

			$items[] = [
				'type'     => 'image',
				'id'       => $image_id,
				'full'     => $image_full,
				'thumb'    => $image_thumb ? $image_thumb : $image_full,
				'alt'      => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: get_the_title($product_id),
				'is_video' => false,
			];
		}
	}

	return $items;
}

function gelikon_render_product_media_gallery($product_id) {
	$product_id = absint($product_id);
	$items = gelikon_get_product_media_items($product_id);

	if (empty($items)) {
		return wc_placeholder_img('full');
	}

	ob_start();
	?>
	<div class="gl-product-media-gallery" data-gl-product-gallery>
		<div class="gl-product-media-gallery__main" data-gl-product-gallery-main>
			<?php foreach ($items as $index => $item) : ?>
				<div
					class="gl-product-media-gallery__slide <?php echo $index === 0 ? 'is-active' : ''; ?>"
					data-gl-product-gallery-slide="<?php echo esc_attr($index); ?>"
					hidden
				>
					<?php if ($item['type'] === 'video') : ?>
						<div class="gl-product-media-gallery__video-wrap">
							<video
								class="gl-product-media-gallery__video"
								controls
								playsinline
								preload="metadata"
							>
								<source src="<?php echo esc_url($item['full']); ?>" type="video/mp4">
								Ваш браузер не поддерживает видео.
							</video>
						</div>
					<?php else : ?>
						<a
							href="<?php echo esc_url($item['full']); ?>"
							class="gl-product-media-gallery__image-link"
							data-gl-product-lightbox="1"
						>
							<img
								class="gl-product-media-gallery__image"
								src="<?php echo esc_url($item['full']); ?>"
								alt="<?php echo esc_attr($item['alt']); ?>"
								loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
							>
						</a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if (count($items) > 1) : ?>
			<div class="gl-product-media-gallery__thumbs" data-gl-product-gallery-thumbs>
				<?php foreach ($items as $index => $item) : ?>
					<button
						type="button"
						class="gl-product-media-gallery__thumb <?php echo $index === 0 ? 'is-active' : ''; ?> <?php echo $item['type'] === 'video' ? 'is-video' : ''; ?>"
						data-gl-product-gallery-thumb="<?php echo esc_attr($index); ?>"
						<?php if ($item['type'] === 'video') : ?>
							data-video-src="<?php echo esc_url($item['full']); ?>"
						<?php endif; ?>
						aria-label="<?php echo esc_attr($item['type'] === 'video' ? 'Показать видео' : 'Показать изображение'); ?>"
					>
						<?php if ($item['type'] === 'video') : ?>
							<span class="gl-product-media-gallery__thumb-video-preview">
								<?php if (!empty($item['thumb'])) : ?>
									<img src="<?php echo esc_url($item['thumb']); ?>" alt="" loading="lazy">
								<?php endif; ?>
							</span>
							<span class="gl-product-media-gallery__thumb-play">▶</span>
						<?php else : ?>
							<img src="<?php echo esc_url($item['thumb']); ?>" alt="" loading="lazy">
						<?php endif; ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php

	return ob_get_clean();
}


add_action('wp_head', function () {
	if (!is_product()) {
		return;
	}
	?>
	<style>
		.gl-product-media-gallery {
			display: flex;
			flex-direction: column;
			gap: 16px;
		}

		.gl-product-media-gallery__main {
			position: relative;
			border-radius: 24px;
			overflow: hidden;
			background: #f6f7f7;
			min-height: 520px;
		}

		.gl-product-media-gallery__slide {
			position: absolute;
			inset: 0;
			opacity: 0;
			visibility: hidden;
			pointer-events: none;
			transition: opacity .32s ease, visibility .32s ease;
		}

		.gl-product-media-gallery__slide.is-active {
			position: relative;
			opacity: 1;
			visibility: visible;
			pointer-events: auto;
			z-index: 2;
		}

		.gl-product-media-gallery__slide[hidden] {
			display: block !important;
		}

		.gl-product-media-gallery__image-link,
		.gl-product-media-gallery__video-wrap {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 100%;
			min-height: 520px;
			background: #f6f7f7;
		}

		.gl-product-media-gallery__image,
		.gl-product-media-gallery__video {
			display: block;
			width: 100%;
			max-height: 720px;
			object-fit: contain;
			background: #f6f7f7;
		}

		.gl-product-media-gallery__thumbs {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
		}

		.gl-product-media-gallery__thumb {
			position: relative;
			width: 92px;
			height: 92px;
			padding: 0;
			border: 2px solid #dfe5e2;
			border-radius: 16px;
			overflow: hidden;
			background: #f6f7f7;
			cursor: pointer;
			transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
			flex: 0 0 92px;
		}

		.gl-product-media-gallery__thumb:hover {
			transform: translateY(-2px);
			border-color: #cfd8d2;
			box-shadow: 0 8px 18px rgba(23, 29, 42, 0.08);
		}

		.gl-product-media-gallery__thumb.is-active {
			border-color: var(--gl-color-accent);
			box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.10);
		}

		.gl-product-media-gallery__thumb img,
		.gl-product-media-gallery__thumb-video-preview img,
		.gl-product-media-gallery__thumb-video-preview canvas {
			display: block;
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.gl-product-media-gallery__thumb-video-preview {
			display: block;
			width: 100%;
			height: 100%;
			background: linear-gradient(180deg, #f8faf9 0%, #eef3f0 100%);
		}

		.gl-product-media-gallery__thumb.is-video::before {
			content: "";
			position: absolute;
			inset: 0;
			background: linear-gradient(to top, rgba(0,0,0,.18), rgba(0,0,0,0));
			pointer-events: none;
			z-index: 1;
		}

		.gl-product-media-gallery__thumb-play {
			position: absolute;
			left: 50%;
			top: 50%;
			transform: translate(-50%, -50%);
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 34px;
			height: 34px;
			border-radius: 50%;
			background: rgba(23, 29, 42, 0.82);
			color: #fff;
			font-size: 13px;
			line-height: 1;
			pointer-events: none;
			z-index: 2;
		}

		.gl-product-lightbox {
			position: fixed;
			inset: 0;
			z-index: 999999;
			display: none;
			align-items: center;
			justify-content: center;
			padding: 24px;
			background: rgba(10, 14, 20, 0.92);
			backdrop-filter: blur(4px);
		}

		.gl-product-lightbox.is-open {
			display: flex;
		}

		.gl-product-lightbox__inner {
			position: relative;
			max-width: min(1200px, 95vw);
			max-height: 92vh;
		}

		.gl-product-lightbox__img {
			display: block;
			max-width: 100%;
			max-height: 92vh;
			border-radius: 18px;
			box-shadow: 0 18px 40px rgba(0,0,0,.35);
		}

		.gl-product-lightbox__close {
			position: absolute;
			top: -14px;
			right: -14px;
			width: 42px;
			height: 42px;
			border: 0;
			border-radius: 50%;
			background: #fff;
			color: #171d2a;
			font-size: 24px;
			line-height: 1;
			cursor: pointer;
			box-shadow: 0 6px 18px rgba(0,0,0,.18);
		}

		@media (max-width: 991px) {
			.gl-product-media-gallery__main,
			.gl-product-media-gallery__image-link,
			.gl-product-media-gallery__video-wrap {
				min-height: 360px;
			}

			.gl-product-media-gallery__thumb {
				width: 76px;
				height: 76px;
				flex-basis: 76px;
				border-radius: 14px;
			}

			.gl-product-media-gallery__thumb-play {
				width: 30px;
				height: 30px;
				font-size: 12px;
			}
		}

		@media (max-width: 767px) {
			.gl-product-media-gallery__thumbs {
				gap: 8px;
			}

			.gl-product-media-gallery__thumb {
				width: 64px;
				height: 64px;
				flex-basis: 64px;
				border-radius: 12px;
			}
		}
	</style>
	<?php
}, 30);

/**
 * JS
 */
add_action('wp_footer', function () {
	if (!is_product()) {
		return;
	}
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-gl-product-gallery]').forEach(function (gallery) {
			const slides = Array.from(gallery.querySelectorAll('[data-gl-product-gallery-slide]'));
			const thumbs = Array.from(gallery.querySelectorAll('[data-gl-product-gallery-thumb]'));

			if (!slides.length || !thumbs.length) return;

			function activate(index) {
				slides.forEach(function (slide, i) {
					const isActive = i === index;
					slide.classList.toggle('is-active', isActive);
					slide.hidden = false;

					if (!isActive) {
						const video = slide.querySelector('video');
						if (video) {
							video.pause();
						}
					}
				});

				thumbs.forEach(function (thumb, i) {
					thumb.classList.toggle('is-active', i === index);
				});
			}

			thumbs.forEach(function (thumb, index) {
				thumb.addEventListener('click', function () {
					activate(index);
				});
			});

			activate(0);

			// Генерация превью для видео из первого кадра
			thumbs.forEach(function (thumb) {
				const videoSrc = thumb.getAttribute('data-video-src');
				const previewWrap = thumb.querySelector('.gl-product-media-gallery__thumb-video-preview');

				if (!videoSrc || !previewWrap) return;
				if (previewWrap.querySelector('img, canvas')) return;

				const video = document.createElement('video');
				video.src = videoSrc;
				video.muted = true;
				video.playsInline = true;
				video.preload = 'metadata';
				video.crossOrigin = 'anonymous';

				video.addEventListener('loadeddata', function () {
					try {
						const canvas = document.createElement('canvas');
						const width = video.videoWidth || 320;
						const height = video.videoHeight || 180;

						canvas.width = width;
						canvas.height = height;

						const ctx = canvas.getContext('2d');
						ctx.drawImage(video, 0, 0, width, height);

						previewWrap.innerHTML = '';
						previewWrap.appendChild(canvas);
					} catch (e) {
						previewWrap.innerHTML = '<span class="gl-product-media-gallery__thumb-video-fallback" style="display:flex;width:100%;height:100%;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#171d2a;">Видео</span>';
					}
				});

				video.addEventListener('error', function () {
					previewWrap.innerHTML = '<span class="gl-product-media-gallery__thumb-video-fallback" style="display:flex;width:100%;height:100%;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#171d2a;">Видео</span>';
				});
			});
		});

		const lightboxLinks = Array.from(document.querySelectorAll('[data-gl-product-lightbox="1"]'));
		if (!lightboxLinks.length) return;

		let lightbox = document.querySelector('.gl-product-lightbox');

		if (!lightbox) {
			lightbox = document.createElement('div');
			lightbox.className = 'gl-product-lightbox';
			lightbox.innerHTML = `
				<div class="gl-product-lightbox__inner">
					<button type="button" class="gl-product-lightbox__close" aria-label="Закрыть">&times;</button>
					<img class="gl-product-lightbox__img" src="" alt="">
				</div>
			`;
			document.body.appendChild(lightbox);
		}

		const img = lightbox.querySelector('.gl-product-lightbox__img');
		const closeBtn = lightbox.querySelector('.gl-product-lightbox__close');

		lightboxLinks.forEach(function (link) {
			link.addEventListener('click', function (e) {
				e.preventDefault();
				const href = link.getAttribute('href');
				if (!href) return;

				img.src = href;
				lightbox.classList.add('is-open');
				document.body.style.overflow = 'hidden';
			});
		});

		function closeLightbox() {
			lightbox.classList.remove('is-open');
			img.src = '';
			document.body.style.overflow = '';
		}

		closeBtn.addEventListener('click', closeLightbox);

		lightbox.addEventListener('click', function (e) {
			if (e.target === lightbox) {
				closeLightbox();
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && lightbox.classList.contains('is-open')) {
				closeLightbox();
			}
		});
	});
	</script>
	<?php
}, 99);

















if (!defined('ABSPATH')) {
	exit;
}

/**
 * Шорткод поиска по товарам WooCommerce с popup-окном
 * Использование: [gelikon_product_search]
 */

/* =========================
 * 1. Шорткод
 * ========================= */
add_shortcode('gelikon_product_search', 'gelikon_product_search_shortcode');

function gelikon_product_search_shortcode($atts) {
	if (!class_exists('WooCommerce')) {
		return '';
	}

	$atts = shortcode_atts([
		'placeholder' => 'Поиск по каталогу',
	], $atts, 'gelikon_product_search');

	$popup_id = 'gl-search-popup-' . wp_rand(1000, 9999);

	ob_start();
	?>
	<div class="gl-header-search-trigger-wrap gl-product-search" data-search-root>
		<button
			type="button"
			class="gl-header-search-trigger"
			aria-label="<?php esc_attr_e('Открыть поиск', 'gelikon'); ?>"
			data-search-open
			data-search-target="<?php echo esc_attr($popup_id); ?>"
		>
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none">
				<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
				<path d="M20 20L16.65 16.65" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
			</svg>
		</button>

		<div class="gl-search-popup" id="<?php echo esc_attr($popup_id); ?>" hidden>
			<div class="gl-search-popup__overlay" data-search-close></div>

			<div class="gl-search-popup__dialog" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Поиск по товарам', 'gelikon'); ?>">
				<button
					type="button"
					class="gl-search-popup__close"
					aria-label="<?php esc_attr_e('Закрыть поиск', 'gelikon'); ?>"
					data-search-close
				>
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none">
						<path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</button>

				<div class="gl-search-popup__head">
					<div class="gl-search-popup__title">Поиск по каталогу</div>
				</div>

				<form
					role="search"
					method="get"
					class="gl-search-form gl-product-search__form gl-search-popup__form"
					action="<?php echo esc_url(home_url('/')); ?>"
					autocomplete="off"
				>
					<span class="gl-search-form__icon gl-search-popup__icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none">
							<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
							<path d="M20 20L16.65 16.65" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
						</svg>
					</span>

					<input
						type="search"
						class="gl-search-form__input gl-product-search__input gl-search-popup__input"
						placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
						value=""
						name="s"
						aria-label="<?php esc_attr_e('Поиск по товарам', 'gelikon'); ?>"
						data-search-input
					>

					<input type="hidden" name="post_type" value="product">

					<button
						type="submit"
						class="gl-product-search__submit gl-search-popup__submit"
						aria-label="<?php esc_attr_e('Найти', 'gelikon'); ?>"
					>
						Найти
					</button>
				</form>

				<div class="gl-product-search__dropdown gl-search-popup__results" data-search-dropdown hidden></div>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/* =========================
 * 2. AJAX: поиск товаров
 * ========================= */
add_action('wp_ajax_gelikon_product_search', 'gelikon_ajax_product_search');
add_action('wp_ajax_nopriv_gelikon_product_search', 'gelikon_ajax_product_search');

function gelikon_ajax_product_search() {
	if (!class_exists('WooCommerce')) {
		wp_send_json_error(['message' => 'WooCommerce не активен.'], 400);
	}

	$query = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
	$query = trim($query);

	if (mb_strlen($query) < 3) {
		wp_send_json_success([
			'items' => [],
		]);
	}

	$args = [
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => 8,
		'ignore_sticky_posts' => true,
		's'                   => $query,
		'orderby'             => 'title',
		'order'               => 'ASC',
		'fields'              => 'ids',
	];

	$product_ids = get_posts($args);
	$items = [];

	if (!empty($product_ids)) {
		foreach ($product_ids as $product_id) {
			$product = wc_get_product($product_id);

			if (!$product) {
				continue;
			}

			$image = get_the_post_thumbnail_url($product_id, 'woocommerce_thumbnail');
			if (!$image) {
				$image = wc_placeholder_img_src('woocommerce_thumbnail');
			}

			$items[] = [
				'id'    => $product_id,
				'title' => get_the_title($product_id),
				'url'   => get_permalink($product_id),
				'price' => $product->get_price_html(),
				'image' => $image,
			];
		}
	}

	wp_send_json_success([
		'items'      => $items,
		'search_url' => add_query_arg([
			's'         => $query,
			'post_type' => 'product',
		], home_url('/')),
	]);
}

/* =========================
 * 3. Подключение JS/CSS
 * ========================= */
add_action('wp_enqueue_scripts', 'gelikon_product_search_assets');

function gelikon_product_search_assets() {
	if (!class_exists('WooCommerce')) {
		return;
	}

	wp_register_script(
		'gelikon-product-search',
		false,
		[],
		null,
		true
	);

	wp_enqueue_script('gelikon-product-search');

	wp_localize_script('gelikon-product-search', 'gelikonProductSearch', [
		'ajaxUrl'      => admin_url('admin-ajax.php'),
		'minChars'     => 3,
		'noResults'    => 'Ничего не найдено',
		'searchText'   => 'Показать все результаты',
		'placeholder'  => wc_placeholder_img_src('woocommerce_thumbnail'),
	]);

	$inline_js = <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
	const roots = document.querySelectorAll('[data-search-root]');
	if (!roots.length) return;

	roots.forEach(function (root) {
		const openBtn = root.querySelector('[data-search-open]');
		const popupId = openBtn ? openBtn.getAttribute('data-search-target') : '';
		const popup = popupId ? document.getElementById(popupId) : null;
		const input = popup ? popup.querySelector('[data-search-input]') : null;
		const dropdown = popup ? popup.querySelector('[data-search-dropdown]') : null;
		const form = popup ? popup.querySelector('form') : null;
		const closeBtns = popup ? popup.querySelectorAll('[data-search-close]') : [];

		if (!openBtn || !popup || !input || !dropdown || !form) return;

		let timer = null;
		let controller = null;
		let activeIndex = -1;

		function lockScroll() {
			document.documentElement.classList.add('gl-search-lock');
			document.body.classList.add('gl-search-lock');
		}

		function unlockScroll() {
			document.documentElement.classList.remove('gl-search-lock');
			document.body.classList.remove('gl-search-lock');
		}

		function openPopup() {
			popup.hidden = false;
			requestAnimationFrame(function () {
				popup.classList.add('is-visible');
				lockScroll();
				setTimeout(function () {
					input.focus();
				}, 60);
			});
		}

		function closePopup() {
			popup.classList.remove('is-visible');
			closeDropdown();
			unlockScroll();

			setTimeout(function () {
				popup.hidden = true;
			}, 220);
		}

		function closeDropdown() {
			dropdown.hidden = true;
			dropdown.innerHTML = '';
			activeIndex = -1;
		}

		function openDropdown() {
			dropdown.hidden = false;
		}

		function escapeHtml(str) {
			if (typeof str !== 'string') return '';
			return str
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#039;');
		}

		function renderItems(items, query) {
			if (!items.length) {
				dropdown.innerHTML = '<div class="gl-product-search__empty">' + gelikonProductSearch.noResults + '</div>';
				openDropdown();
				return;
			}

			let html = '<div class="gl-product-search__list">';

			items.forEach(function (item, index) {
				const title = escapeHtml(item.title || '');
				const image = item.image || gelikonProductSearch.placeholder || '';

				html += `
					<a href="${item.url}" class="gl-product-search__item" data-search-item data-index="${index}">
						<span class="gl-product-search__thumb">
							<img src="${image}" alt="${title}">
						</span>
						<span class="gl-product-search__content">
							<span class="gl-product-search__title">${title}</span>
							<span class="gl-product-search__price">${item.price || ''}</span>
						</span>
					</a>
				`;
			});

			html += `
				<button type="button" class="gl-product-search__all" data-search-submit>
					${gelikonProductSearch.searchText}
				</button>
			`;

			html += '</div>';

			dropdown.innerHTML = html;
			openDropdown();
		}

		function fetchResults(query) {
			if (controller) {
				controller.abort();
			}

			controller = new AbortController();

			const url = new URL(gelikonProductSearch.ajaxUrl, window.location.origin);
			url.searchParams.set('action', 'gelikon_product_search');
			url.searchParams.set('q', query);

			fetch(url.toString(), {
				method: 'GET',
				signal: controller.signal,
				credentials: 'same-origin'
			})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (!data || !data.success || !data.data) {
					closeDropdown();
					return;
				}

				renderItems(data.data.items || [], query);
			})
			.catch(function (error) {
				if (error.name !== 'AbortError') {
					closeDropdown();
				}
			});
		}

		function updateActive(items) {
			items.forEach(function (item, index) {
				item.classList.toggle('is-active', index === activeIndex);
			});

			if (items[activeIndex]) {
				items[activeIndex].scrollIntoView({
					block: 'nearest'
				});
			}
		}

		openBtn.addEventListener('click', function () {
			openPopup();
		});

		closeBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				closePopup();
			});
		});

		input.addEventListener('input', function () {
			const query = input.value.trim();

			clearTimeout(timer);

			if (query.length < Number(gelikonProductSearch.minChars || 3)) {
				closeDropdown();
				return;
			}

			timer = setTimeout(function () {
				fetchResults(query);
			}, 180);
		});

		input.addEventListener('keydown', function (e) {
			const items = dropdown.querySelectorAll('[data-search-item]');

			if (e.key === 'Escape') {
				e.preventDefault();
				closePopup();
				return;
			}

			if (dropdown.hidden || !items.length) {
				return;
			}

			if (e.key === 'ArrowDown') {
				e.preventDefault();
				activeIndex = activeIndex < items.length - 1 ? activeIndex + 1 : 0;
				updateActive(items);
			}

			if (e.key === 'ArrowUp') {
				e.preventDefault();
				activeIndex = activeIndex > 0 ? activeIndex - 1 : items.length - 1;
				updateActive(items);
			}

			if (e.key === 'Enter' && activeIndex >= 0 && items[activeIndex]) {
				e.preventDefault();
				window.location.href = items[activeIndex].getAttribute('href');
			}
		});

		dropdown.addEventListener('click', function (e) {
			const submitBtn = e.target.closest('[data-search-submit]');
			if (submitBtn) {
				form.submit();
			}
		});

		form.addEventListener('submit', function () {
			closePopup();
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !popup.hidden) {
				closePopup();
			}
		});
	});
});
JS;

	wp_add_inline_script('gelikon-product-search', $inline_js);

	wp_register_style(
		'gelikon-product-search',
		false,
		[],
		null
	);

	wp_enqueue_style('gelikon-product-search');

	$inline_css = <<<'CSS'
.gl-search-lock {
	overflow: hidden;
}

.gl-header-search-trigger-wrap {
	position: relative;
}

.gl-header-search-trigger {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 48px;
	height: 48px;
	padding: 0;
	border: 1px solid #dde4dd;
	border-radius: 999px;
	background: #ffffff;
	color: #1d232c;
	cursor: pointer;
	transition: .25s ease;
	box-shadow: 0 4px 18px rgba(16, 22, 31, 0.04);
}

.gl-header-search-trigger:hover {
	background: #f7f8f7;
	border-color: #cfd8cf;
	color: #10161f;
	transform: translateY(-1px);
}

.gl-search-popup {
	position: fixed;
	inset: 0;
	z-index: 9999;
	display: flex;
	align-items: flex-start;
	justify-content: center;
	padding: 70px 20px 20px;
	opacity: 0;
	visibility: hidden;
	pointer-events: none;
	transition: opacity .22s ease, visibility .22s ease;
}

.gl-search-popup.is-visible {
	opacity: 1;
	visibility: visible;
	pointer-events: auto;
}

.gl-search-popup__overlay {
	position: absolute;
	inset: 0;
	background: rgba(16, 22, 31, 0.42);
	backdrop-filter: blur(4px);
}

.gl-search-popup__dialog {
	position: relative;
	width: 100%;
	max-width: 860px;
	background: #ffffff;
	border-radius: 28px;
	border: 1px solid #e6ece6;
	box-shadow: 0 24px 80px rgba(16, 22, 31, 0.16);
	padding: 28px;
	transform: translateY(14px) scale(.98);
	transition: transform .22s ease;
}

.gl-search-popup.is-visible .gl-search-popup__dialog {
	transform: translateY(0) scale(1);
}

.gl-search-popup__close {
	position: absolute;
	top: 18px;
	right: 18px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 44px;
	height: 44px;
	padding: 0;
	border: 1px solid #e4e9e4;
	border-radius: 999px;
	background: #fff;
	color: #6b7280;
	cursor: pointer;
	transition: .2s ease;
}

.gl-search-popup__close:hover {
	background: #f6f8f6;
	color: #1a1a1a;
	border-color: #d6ddd6;
}

.gl-search-popup__head {
	padding-right: 56px;
	margin-bottom: 18px;
}

.gl-search-popup__title {
	font-size: 28px;
	line-height: 1.15;
	font-weight: 700;
	color: #1a1a1a;
	letter-spacing: -0.02em;
}

.gl-search-popup__form {
	position: relative;
	display: flex;
	align-items: center;
	min-height: 68px;
	padding: 8px 8px 8px 56px;
	border-radius: 999px;
}

.gl-search-popup__icon,
.gl-search-form__icon.gl-search-popup__icon {
	position: absolute;
	left: 22px;
	top: 50%;
	transform: translateY(-50%);
	display: inline-flex;
	align-items: center;
	justify-content: center;
	color: #9ca3af;
	pointer-events: none;
}

.gl-search-popup__input,
.gl-search-form__input.gl-search-popup__input {
	width: 100%;
	min-width: 0;
	height: 46px;
	padding: 0 18px 0 0;
	border: 0;
	outline: 0;
	background: transparent;
	font-size: 18px;
	line-height: 1.4;
	color: #1a1a1a;
	box-shadow: none;
}

.gl-search-popup__input::placeholder {
	color: #9ca3af;
}

.gl-search-popup__submit,
.gl-product-search__submit.gl-search-popup__submit {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	height: 46px;
	padding: 0 24px;
	border: 0;
	border-radius: 999px;
	background: #10161f;
	color: #ffffff;
	font-size: 15px;
	font-weight: 700;
	cursor: pointer;
	transition: .2s ease;
	white-space: nowrap;
}

.gl-search-popup__submit:hover {
	background: #1b2533;
	color: #ffffff;
}

.gl-search-popup__results,
.gl-product-search__dropdown {
	position: static;
	margin-top: 14px;
	z-index: auto;
	background: #fff;
	border: 1px solid #e5ebf0;
	border-radius: 22px;
	box-shadow: none;
	padding: 8px;
	max-height: 420px;
	overflow: auto;
}

.gl-product-search__list {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.gl-product-search__item {
	display: flex;
	align-items: center;
	gap: 14px;
	padding: 12px;
	border-radius: 16px;
	text-decoration: none;
	color: #1b2230;
	transition: background .2s ease, transform .2s ease;
}

.gl-product-search__item:hover,
.gl-product-search__item.is-active {
	background: #f5f8f5;
}

.gl-product-search__thumb {
	width: 58px;
	height: 58px;
	border-radius: 12px;
	overflow: hidden;
	background: #f4f7fa;
	flex: 0 0 auto;
}

.gl-product-search__thumb img {
	display: block;
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.gl-product-search__content {
	display: flex;
	flex-direction: column;
	gap: 4px;
	min-width: 0;
}

.gl-product-search__title {
	font-size: 15px;
	line-height: 1.35;
	font-weight: 600;
	color: #1a1a1a;
}

.gl-product-search__price {
	font-size: 13px;
	line-height: 1.3;
	color: #6b7280;
}

.gl-product-search__price .amount {
	color: #1a1a1a;
	font-weight: 700;
}

.gl-product-search__all {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	min-height: 48px;
	margin-top: 6px;
	border: 0;
	border-radius: 16px;
	background: #f3f6f3;
	color: #1a1a1a;
	font-size: 14px;
	font-weight: 700;
	cursor: pointer;
	transition: .2s ease;
}

.gl-product-search__all:hover {
	background: #eaf0ea;
}

.gl-product-search__empty {
	padding: 18px 14px;
	font-size: 14px;
	color: #6b7280;
}

@media (max-width: 991px) {
	.gl-header-search-trigger {
		width: 44px;
		height: 44px;
	}

	.gl-search-popup {
		padding: 14px;
		align-items: flex-start;
	}

	.gl-search-popup__dialog {
		max-width: 100%;
		border-radius: 22px;
		padding: 18px;
	}

	.gl-search-popup__close {
		top: 14px;
		right: 14px;
		width: 40px;
		height: 40px;
	}

	.gl-search-popup__head {
		margin-bottom: 14px;
		padding-right: 50px;
	}

	.gl-search-popup__title {
		font-size: 22px;
	}

	.gl-search-popup__form {
		min-height: 60px;
		padding: 6px 6px 6px 48px;
		border-radius: 18px;
		flex-wrap: wrap;
	}

	.gl-search-popup__icon {
		left: 18px;
	}

	.gl-search-popup__input {
		height: 46px;
		font-size: 16px;
		padding-right: 0;
	}

	.gl-search-popup__submit {
		width: 100%;
		margin-top: 8px;
		height: 46px;
		border-radius: 14px;
	}

	.gl-search-popup__results {
		margin-top: 12px;
		border-radius: 16px;
		padding: 6px;
		max-height: 55vh;
	}

	.gl-product-search__item {
		padding: 10px;
		border-radius: 12px;
	}

	.gl-product-search__thumb {
		width: 46px;
		height: 46px;
	}
}
CSS;

	wp_add_inline_style('gelikon-product-search', $inline_css);
}










if (!defined('ABSPATH')) exit;

/**
 * Options Page
 */
add_action('acf/init', function () {
	if (!function_exists('acf_add_options_page')) return;

	acf_add_options_page([
		'page_title' => 'Контакты',
		'menu_title' => 'Контакты',
		'menu_slug'  => 'gelikon-contacts',
		'capability' => 'edit_posts',
		'redirect'   => false,
	]);
});

/**
 * Fields
 */
add_action('acf/init', function () {
	if (!function_exists('acf_add_local_field_group')) return;

	acf_add_local_field_group([
		'key' => 'group_gelikon_contacts',
		'title' => 'Контакты (модалка)',
		'fields' => [
			[
				'key' => 'field_contacts_repeater',
				'label' => 'Контакты',
				'name' => 'contacts',
				'type' => 'repeater',
				'layout' => 'block',
				'button_label' => 'Добавить контакт',
				'sub_fields' => [

					[
						'key' => 'field_contact_icon',
						'label' => 'Иконка (SVG/PNG)',
						'name' => 'icon',
						'type' => 'image',
						'return_format' => 'array',
						'preview_size' => 'thumbnail',
					],

					[
						'key' => 'field_contact_title',
						'label' => 'Заголовок',
						'name' => 'title',
						'type' => 'text',
					],

					[
						'key' => 'field_contact_text',
						'label' => 'Подпись',
						'name' => 'text',
						'type' => 'text',
					],

					[
						'key' => 'field_contact_link',
						'label' => 'Ссылка',
						'name' => 'link',
						'type' => 'url',
					],

					[
						'key' => 'field_contact_style',
						'label' => 'Класс (tg / wa / кастом)',
						'name' => 'style',
						'type' => 'text',
						'instructions' => 'Например: tg, wa или свой класс',
					],
				],
			],
		],
		'location' => [
			[
				[
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'gelikon-contacts',
				],
			],
		],
	]);
});











if (!defined('ABSPATH')) {
	exit;
}

/**
 * Рендер статуса наличия товара
 */
function gelikon_get_stock_status_html($product_id = 0) {
	if (!function_exists('wc_get_product')) {
		return '';
	}

	if (!$product_id) {
		$product_id = get_the_ID();
	}

	if (!$product_id) {
		return '';
	}

	$product = wc_get_product($product_id);

	if (!$product || !is_a($product, 'WC_Product')) {
		return '';
	}

	$text  = '';
	$class = '';

	$backorders = $product->get_backorders();

	if ($product->is_in_stock()) {
		if ($backorders && $backorders !== 'no') {
			$text  = 'Предзаказ';
			$class = 'is-preorder';
		} else {
			$text  = 'В наличии';
			$class = 'is-instock';
		}
	} else {
		$text  = 'Нет в наличии';
		$class = 'is-outofstock';
	}

	ob_start();
	?>

	<div class="gl-write-btn" style="cursor: initial; color: #0f9f57; font-weight: 400; text-decoration: none;">
		<span class="gl-write-btn__text"><?php echo esc_html($text); ?></span>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Шорткод
 * [gelikon_stock_status]
 * [gelikon_stock_status id="123"]
 */
add_shortcode('gelikon_stock_status', function($atts = []) {
	$product_id = get_the_ID();

	if (!empty($atts['id'])) {
		$product_id = (int) $atts['id'];
	}

	return gelikon_get_stock_status_html($product_id);
});














/**
 * Галерея товара:
 * - отключить magnify/zoom
 * - включить lightbox
 * - оставить слайдер галереи
 */
add_action('after_setup_theme', function () {
	// На всякий случай убираем все старые поддержки
	remove_theme_support('wc-product-gallery-zoom');
	remove_theme_support('wc-product-gallery-lightbox');
	remove_theme_support('wc-product-gallery-slider');

	// Включаем только то, что нужно
	add_theme_support('wc-product-gallery-lightbox');
	add_theme_support('wc-product-gallery-slider');
}, 100);

/**
 * Иногда тема или плагин всё равно включает zoom через фильтр.
 * Принудительно отключаем.
 */
add_filter('woocommerce_single_product_zoom_enabled', '__return_false', 999);




add_filter('woocommerce_product_single_add_to_cart_text', function($text) {
	return 'Купить';
});