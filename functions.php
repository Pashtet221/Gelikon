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



/**
 * Возвращает URL страницы политики конфиденциальности.
 */
function gelikon_get_privacy_policy_url() {
	$privacy_url = function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : '';

	if (!$privacy_url) {
		$page = get_page_by_path('privacy-policy');
		$privacy_url = $page ? get_permalink($page->ID) : home_url('/privacy-policy/');
	}

	return $privacy_url;
}

/**
 * Проверяет согласие на обработку персональных данных.
 */
function gelikon_is_personal_data_consent_given($field_name = 'gelikon_personal_data_consent') {
	return !empty($_POST[$field_name]); // phpcs:ignore WordPress.Security.NonceVerification.Missing
}

/**
 * Единый чекбокс согласия на обработку персональных данных для форм.
 */
function gelikon_personal_data_consent_markup($field_id = '', $field_name = 'gelikon_personal_data_consent', $class_name = 'gl-personal-data-consent') {
	if (!$field_id) {
		$field_id = $field_name . '_' . wp_rand(1000, 9999);
	}

	$privacy_url = gelikon_get_privacy_policy_url();

	ob_start();
	?>
	<p class="<?php echo esc_attr($class_name); ?>">
		<label for="<?php echo esc_attr($field_id); ?>">
			<input
				type="checkbox"
				id="<?php echo esc_attr($field_id); ?>"
				name="<?php echo esc_attr($field_name); ?>"
				value="1"
				required
			>
			<span>
				<?php esc_html_e('Я даю согласие на обработку персональных данных и принимаю условия', 'gelikon'); ?>
				<a href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e('политики конфиденциальности', 'gelikon'); ?>
				</a>.
			</span>
		</label>
	</p>
	<?php
	return ob_get_clean();
}

/**
 * Вывод согласия перед кнопкой оформления заказа.
 */
add_action('woocommerce_review_order_before_submit', function () {
	echo gelikon_personal_data_consent_markup('gelikon_checkout_personal_data_consent', 'gelikon_personal_data_consent', 'gl-personal-data-consent gl-personal-data-consent--checkout'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 8);

/**
 * Валидация согласия в оформлении заказа.
 */
add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
	if (!gelikon_is_personal_data_consent_given()) {
		$errors->add('personal_data_consent', __('Подтвердите согласие на обработку персональных данных.', 'gelikon'));
	}
}, 10, 2);

/**
 * Валидация согласия для формы отзыва о товаре.
 */
add_filter('preprocess_comment', function ($commentdata) {
	$post_id = isset($commentdata['comment_post_ID']) ? absint($commentdata['comment_post_ID']) : 0;

	if ($post_id && 'product' === get_post_type($post_id) && empty($_POST['gelikon_submit_product_question']) && !gelikon_is_personal_data_consent_given()) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		wp_die(
			esc_html__('Подтвердите согласие на обработку персональных данных.', 'gelikon'),
			esc_html__('Необходимо согласие', 'gelikon'),
			['response' => 400, 'back_link' => true]
		);
	}

	return $commentdata;
});


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
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
						<rect x="2" y="3" width="12" height="1.5" rx="0.75" fill="currentColor"/>
						<rect x="2" y="7" width="12" height="1.5" rx="0.75" fill="currentColor"/>
						<rect x="2" y="11" width="12" height="1.5" rx="0.75" fill="currentColor"/>
					</svg>
				</span>

				<span class="gl-catalog-dropdown__toggle-text">
					<?php echo esc_html($args['title']); ?>
				</span>

				<span class="gl-catalog-dropdown__toggle-arrow" aria-hidden="true">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
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

						<button class="gl-catalog-dropdown__back" type="button">
							<span>‹</span>
							Назад
						</button>

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
 * Стили и скрипт каталога
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

		.gl-catalog-dropdown__toggle-icon,
		.gl-catalog-dropdown__toggle-arrow {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			color: #7d828a;
			flex: 0 0 auto;
		}

		.gl-catalog-dropdown__toggle-icon {
			width: 16px;
			height: 16px;
		}

		.gl-catalog-dropdown__toggle-arrow {
			width: 14px;
			height: 14px;
			transition: transform .2s ease;
		}

		.gl-catalog-dropdown__toggle-icon svg,
		.gl-catalog-dropdown__toggle-arrow svg {
			display: block;
			width: 100%;
			height: 100%;
		}

		.gl-catalog-dropdown__toggle-text {
			white-space: nowrap;
			font-weight: 700;
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
			background: #fff;
		}

		.gl-catalog-dropdown__back {
			display: none;
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
			.gl-catalog-dropdown__parent-arrow {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 38px;
		height: 38px;
		margin: -8px -8px -8px 0;
		cursor: pointer;
	}
			
			.gl-catalog-dropdown {
				position: relative;
				width: 100%;
			}

			.gl-catalog-dropdown__toggle {
				min-height: 38px;
				padding: 0 6px 0 2px;
				font-size: 15px;
				border-radius: 8px;
			}

			.gl-catalog-dropdown__toggle-icon {
				display: none;
			}

			.gl-catalog-dropdown__panel {
				position: absolute;
				top: calc(100% + 8px);
				left: 0;
				width: min(420px, 92vw);
				max-width: 92vw;
				border-radius: 18px;
				box-shadow: 0 18px 40px rgba(0, 0, 0, 0.12);
				overflow: hidden;
			}

			.gl-catalog-dropdown__grid {
				position: relative;
				display: block;
				min-height: 360px;
				overflow: hidden;
				background: #fff;
			}

			.gl-catalog-dropdown__sidebar,
			.gl-catalog-dropdown__content {
				width: 100%;
				min-height: 360px;
				transition: transform .28s ease;
			}

			.gl-catalog-dropdown__sidebar {
				position: relative;
				z-index: 1;
				border-right: 0;
				border-bottom: 0;
				background: #fafafa;
				transform: translateX(0);
			}

			.gl-catalog-dropdown__content {
				position: absolute;
				top: 0;
				left: 0;
				z-index: 2;
				padding: 18px;
				transform: translateX(100%);
			}

			.gl-catalog-dropdown.is-mobile-submenu .gl-catalog-dropdown__sidebar {
				transform: translateX(-100%);
			}

			.gl-catalog-dropdown.is-mobile-submenu .gl-catalog-dropdown__content {
				transform: translateX(0);
			}

			.gl-catalog-dropdown__parents {
				padding: 8px;
			}

			.gl-catalog-dropdown__parent-row {
				min-height: 46px;
				padding: 9px 10px;
				border-radius: 12px;
			}

			.gl-catalog-dropdown__parent-row.is-active {
				background: transparent;
			}

			.gl-catalog-dropdown__parent-row.is-active .gl-catalog-dropdown__parent-link-main {
				color: #171d2a;
			}

			.gl-catalog-dropdown__parent-name {
				font-size: 14px;
			}

			.gl-catalog-dropdown__children-list {
				grid-template-columns: 1fr;
				gap: 10px;
			}

			.gl-catalog-dropdown__parent-link {
				font-size: 16px;
			}

			.gl-catalog-dropdown__child-link {
				font-size: 13px;
			}

			.gl-catalog-dropdown__back {
				display: inline-flex;
				align-items: center;
				gap: 6px;
				margin: 0 0 18px;
				padding: 0;
				border: 0;
				background: transparent;
				color: #171d2a;
				font-size: 15px;
				font-weight: 700;
				cursor: pointer;
			}

			.gl-catalog-dropdown__back span {
				font-size: 26px;
				line-height: 1;
			}

			.gl-catalog-dropdown__parent-row:hover,
			.gl-catalog-dropdown__child-link:hover,
			.gl-catalog-dropdown__parent-link:hover,
			.gl-catalog-dropdown__parent-link-main:hover {
				background: inherit;
				color: inherit;
				text-decoration: none;
			}
		}
	</style>

<script id="gelikon-catalog-dropdown-script">
	document.addEventListener('DOMContentLoaded', function () {
		const dropdowns = document.querySelectorAll('.gl-catalog-dropdown');

		if (!dropdowns.length) return;

		const isMobile = () => window.matchMedia('(max-width: 991px)').matches;

		dropdowns.forEach(function (dropdown) {
			const toggle = dropdown.querySelector('.gl-catalog-dropdown__toggle');
			const panel = dropdown.querySelector('.gl-catalog-dropdown__panel');
			const parentRows = dropdown.querySelectorAll('.gl-catalog-dropdown__parent-row');
			const childrenPanels = dropdown.querySelectorAll('.gl-catalog-dropdown__children-panel');
			const backButton = dropdown.querySelector('.gl-catalog-dropdown__back');

			if (!toggle || !panel) return;

			function closeDropdown() {
				dropdown.classList.remove('is-open', 'is-mobile-submenu');
				toggle.setAttribute('aria-expanded', 'false');
				panel.hidden = true;
			}

			function openDropdown() {
				dropdown.classList.add('is-open');
				toggle.setAttribute('aria-expanded', 'true');
				panel.hidden = false;
			}

			function setActivePanel(target) {
				parentRows.forEach(function (item) {
					const isCurrent = item.dataset.target === target;

					item.classList.toggle('is-active', isCurrent);
					item.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
				});

				childrenPanels.forEach(function (panelItem) {
					const isCurrent = panelItem.dataset.panel === target;

					panelItem.classList.toggle('is-active', isCurrent);
					panelItem.hidden = !isCurrent;
				});
			}

			toggle.addEventListener('click', function () {
				if (dropdown.classList.contains('is-open')) {
					closeDropdown();
				} else {
					openDropdown();
				}
			});

			parentRows.forEach(function (row) {
				const arrow = row.querySelector('.gl-catalog-dropdown__parent-arrow');

				row.addEventListener('click', function () {
					if (isMobile()) return;

					const target = row.dataset.target;

					if (!target) return;

					setActivePanel(target);
				});

				row.addEventListener('mouseenter', function () {
					if (isMobile()) return;

					const target = row.dataset.target;

					if (!target) return;

					setActivePanel(target);
				});

				if (arrow) {
					arrow.addEventListener('click', function (event) {
						if (!isMobile()) return;

						event.preventDefault();
						event.stopPropagation();

						const target = row.dataset.target;

						if (!target) return;

						setActivePanel(target);
						dropdown.classList.add('is-mobile-submenu');
					});
				}
			});

			if (backButton) {
				backButton.addEventListener('click', function () {
					dropdown.classList.remove('is-mobile-submenu');
				});
			}

			document.addEventListener('click', function (event) {
				if (dropdown.contains(event.target)) return;

				closeDropdown();
			});

			window.addEventListener('resize', function () {
				dropdown.classList.remove('is-mobile-submenu');
			});
		});
	});
</script>
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


add_action('wp_head', function () {
	if (!is_tax('product_cat') && !is_shop() && !is_post_type_archive('product')) {
		return;
	}
	?>
	<style id="gelikon-catalog-critical-styles">
		.gl-catalog-layout{display:grid;grid-template-columns:300px minmax(0,1fr);gap:28px;align-items:start}.gl-catalog-sidebar{min-width:0}.gl-catalog-sidebar__inner{position:sticky;top:96px;padding:22px;border-radius:24px;background:#fff;border:1px solid #e5ebe7}.gl-catalog-mobile-bar,.gl-catalog-overlay{display:none}.gl-catalog-products{min-width:0}.gl-catalog-products__grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;margin:0;padding:0;list-style:none}.gl-catalog-products__grid li.product{width:auto!important;float:none!important;margin:0!important}@media (max-width:1199px){.gl-catalog-layout{grid-template-columns:260px minmax(0,1fr);gap:22px}.gl-catalog-products__grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:991px){.gl-catalog-mobile-bar{display:block}.gl-catalog-layout{grid-template-columns:1fr}.gl-catalog-sidebar{position:fixed;top:0;left:0;width:min(380px,90vw);height:100vh;z-index:1000;transform:translateX(-100%);padding:0}.gl-catalog-sidebar.is-open{transform:translateX(0)}.gl-catalog-sidebar__inner{position:relative;top:0;height:100%;overflow-y:auto;border-radius:0 24px 24px 0;padding:18px}.gl-catalog-overlay.is-visible{display:block;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:999}}@media (max-width:767px){.gl-catalog-products__grid{grid-template-columns:1fr;gap:16px}}
	</style>
	<?php
}, 5);

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

	if (!gelikon_is_personal_data_consent_given()) {
		wp_die(
			esc_html__('Подтвердите согласие на обработку персональных данных.', 'gelikon'),
			esc_html__('Необходимо согласие', 'gelikon'),
			['response' => 400, 'back_link' => true]
		);
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

				if ($is_last && empty($url)) {
					$url = gelikon_get_current_breadcrumb_url();
				}
				?>

				<li class="gl-breadcrumbs__item<?php echo $is_last ? ' is-current' : ''; ?>">
					<?php if (!empty($url) && !is_wp_error($url)) : ?>
						<a class="gl-breadcrumbs__link<?php echo $home ? ' is-home' : ''; ?><?php echo $is_last ? ' is-current' : ''; ?>" href="<?php echo esc_url($url); ?>">
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
 * URL текущей страницы для последней крошки
 */
function gelikon_get_current_breadcrumb_url() {
	if (function_exists('is_shop') && is_shop()) {
		$shop_page_id = wc_get_page_id('shop');

		if ($shop_page_id && $shop_page_id > 0) {
			return get_permalink($shop_page_id);
		}

		return home_url('/shop/');
	}

	if (is_singular()) {
		return get_permalink();
	}

	if (is_category() || is_tag() || (function_exists('is_product_category') && is_product_category())) {
		$term = get_queried_object();

		if ($term instanceof WP_Term) {
			$link = get_term_link($term);

			if (!is_wp_error($link)) {
				return $link;
			}
		}
	}

	if (is_search()) {
		return get_search_link();
	}

	if (is_404()) {
		return home_url(add_query_arg([], $GLOBALS['wp']->request));
	}

	return home_url(add_query_arg([], $GLOBALS['wp']->request));
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
			'url'   => $shop_page_url,
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
				'url'   => get_term_link($term),
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
			'url'   => get_permalink($page_id),
		];

		return $items;
	}

	if (is_singular('post')) {
		$categories = get_the_category();

		if (!empty($categories)) {
			$category = $categories[0];

			$ancestors = array_reverse(get_ancestors($category->term_id, 'category'));

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
				'title' => $category->name,
				'url'   => get_category_link($category->term_id),
			];
		}

		return $items;
	}

	if (is_singular()) {
		$items[] = [
			'title' => get_the_title(),
			'url'   => get_permalink(),
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
				'url'   => get_term_link($term),
			];
		}

		return $items;
	}

	if (is_search()) {
		$search_query = get_search_query();

		if (isset($_GET['post_type']) && $_GET['post_type'] === 'product') {
			$items[] = [
				'title' => $shop_page_title ?: 'Каталог',
				'url'   => $shop_page_url,
			];
		}

		$items[] = [
			'title' => $search_query
				? 'Результаты поиска: ' . $search_query
				: 'Результаты поиска',
			'url'   => get_search_link(),
		];

		return $items;
	}

	if (is_404()) {
		$items[] = [
			'title' => 'Страница не найдена',
			'url'   => home_url('/'),
		];

		return $items;
	}

	$items[] = [
		'title' => wp_get_document_title(),
		'url'   => gelikon_get_current_breadcrumb_url(),
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

	$deepest   = null;
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
		.gl-breadcrumbs {
			margin: 0 0 24px;
		}

		.gl-breadcrumbs__list {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 8px 10px;
			margin: 0;
			padding: 0;
			list-style: none;
		}

		.gl-breadcrumbs__item {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			min-width: 0;
			font-size: 16px;
			line-height: 1.35;
			font-weight: 600;
		}

		.gl-breadcrumbs__link {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			color: var(--gl-color-helper);
			text-decoration: none;
			transition: color .2s ease, opacity .2s ease;
			min-width: 0;
		}

		.gl-breadcrumbs__link:hover {
			color: var(--gl-color-text);
		}

		.gl-breadcrumbs__link.is-current {
			color: var(--gl-color-helper);
			font-weight: 500;
			cursor: pointer;
		}

		.gl-breadcrumbs__link.is-current:hover {
			color: var(--gl-color-text);
		}

		.gl-breadcrumbs__link.is-home {
			gap: 10px;
		}

		.gl-breadcrumbs__home-icon {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 22px;
			height: 22px;
			color: #c4c4c7;
			flex: 0 0 auto;
		}

		.gl-breadcrumbs__home-icon svg {
			display: block;
			width: 100%;
			height: 100%;
		}

		.gl-breadcrumbs__text,
		.gl-breadcrumbs__current {
			display: inline-block;
			white-space: normal;
			word-break: break-word;
		}

		.gl-breadcrumbs__current {
			color: var(--gl-color-helper);
			font-weight: 500;
		}

		.gl-breadcrumbs__sep {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			color: #c4c4c7;
			font-size: 22px;
			line-height: 1;
			transform: translateY(-1px);
			flex: 0 0 auto;
		}

		@media (max-width: 767px) {
			.gl-breadcrumbs {
				margin: 0 0 18px;
			}

			.gl-breadcrumbs__list {
				gap: 6px 8px;
			}

			.gl-breadcrumbs__item {
				font-size: 14px;
				line-height: 1.3;
			}

			.gl-breadcrumbs__sep {
				font-size: 18px;
			}

			.gl-breadcrumbs__home-icon {
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


add_action('acf/init', function () {
	if (!function_exists('acf_add_options_page')) return;

	acf_add_options_page([
		'page_title' => 'Общие настройки',
		'menu_title' => 'Общие настройки',
		'menu_slug'  => 'gelikon-general',
		'capability' => 'edit_posts',
		'redirect'   => false,
	]);
});


/**
 * Fields
 */

add_action('acf/init', function () {
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group([
		'key' => 'group_gelikon_general_product',
		'title' => 'Общие настройки — карточка товара',
		'fields' => [
			[
				'key' => 'field_product_global_benefits',
				'label' => 'Преимущества в карточке товара',
				'name' => 'product_global_benefits',
				'type' => 'repeater',
				'layout' => 'row',
				'min' => 0,
				'button_label' => 'Добавить преимущество',
				'sub_fields' => [
					[
						'key' => 'field_product_global_benefit_icon',
						'label' => 'Иконка',
						'name' => 'icon',
						'type' => 'image',
						'return_format' => 'array',
						'preview_size' => 'thumbnail',
						'library' => 'all',
					],
					[
						'key' => 'field_product_global_benefit_text',
						'label' => 'Текст',
						'name' => 'text',
						'type' => 'text',
					],
					[
						'key' => 'field_product_global_benefit_link',
						'label' => 'Ссылка',
						'name' => 'link',
						'type' => 'url',
						'placeholder' => 'https://site.ru/page/',
					],
				],
			],
		],
		'location' => [
			[
				[
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'gelikon-general',
				],
			],
		],
	]);
});



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
 * Product sale status and purchase note helpers.
 */
function gelikon_product_meta_enabled($product_id, $meta_key) {
	$value = get_post_meta((int) $product_id, $meta_key, true);
	return in_array($value, ['1', 1, 'yes', 'on', true], true);
}

function gelikon_is_product_preorder($product_id = 0) {
	$product_id = $product_id ? (int) $product_id : get_the_ID();
	return $product_id ? gelikon_product_meta_enabled($product_id, '_gelikon_preorder_enabled') : false;
}

function gelikon_is_product_discontinued($product_id = 0) {
	$product_id = $product_id ? (int) $product_id : get_the_ID();
	return $product_id ? gelikon_product_meta_enabled($product_id, '_gelikon_discontinued') : false;
}

function gelikon_get_product_purchase_note($product_id = 0) {
	$product_id = $product_id ? (int) $product_id : get_the_ID();

	if (!$product_id) {
		return '';
	}

	return trim((string) get_post_meta($product_id, '_gelikon_purchase_note', true));
}

function gelikon_product_can_be_purchased($product = null) {
	if (!$product && function_exists('wc_get_product')) {
		$product = wc_get_product(get_the_ID());
	}

	if (!$product || !is_a($product, 'WC_Product')) {
		return false;
	}

	$product_id = $product->get_id();

	if (gelikon_is_product_discontinued($product_id)) {
		return false;
	}

	return $product->is_in_stock() || gelikon_is_product_preorder($product_id);
}

function gelikon_render_product_purchase_note($product_id = 0, $class = '') {
	$note = gelikon_get_product_purchase_note($product_id);

	if ($note === '') {
		return '';
	}

	$class = trim('gl-product-purchase-note ' . $class);

	return '<div class="' . esc_attr($class) . '">' . wp_kses_post(wpautop($note)) . '</div>';
}

/**
 * Gelikon product sale status fields in admin.
 */
add_action('add_meta_boxes_product', function () {
	add_meta_box(
		'gelikon_product_sale_status',
		'Статус покупки Gelikon',
		'gelikon_product_sale_status_metabox',
		'product',
		'side',
		'default'
	);
});

function gelikon_product_sale_status_metabox($post) {
	wp_nonce_field('gelikon_save_product_sale_status', 'gelikon_product_sale_status_nonce');

	$preorder_enabled = gelikon_is_product_preorder($post->ID);
	$discontinued     = gelikon_is_product_discontinued($post->ID);
	$purchase_note    = gelikon_get_product_purchase_note($post->ID);
	?>
	<p>
		<label>
			<input type="checkbox" name="gelikon_preorder_enabled" value="1" <?php checked($preorder_enabled); ?>>
			Включить статус предзаказа
		</label>
	</p>
	<p style="color:#666;margin-top:-6px;">Предзаказ показывается на сайте и разрешает покупку товара.</p>

	<p>
		<label>
			<input type="checkbox" name="gelikon_discontinued" value="1" <?php checked($discontinued); ?>>
			Снят с продажи
		</label>
	</p>
	<p style="color:#666;margin-top:-6px;">Товар остаётся на сайте, но покупка блокируется.</p>

	<p>
		<label for="gelikon_purchase_note"><strong>Текст рядом с кнопкой покупки</strong></label>
	</p>
	<textarea
		id="gelikon_purchase_note"
		name="gelikon_purchase_note"
		rows="4"
		style="width:100%;"
		placeholder="Например: Доступно для предзаказа. Менеджер уточнит сроки доставки."
	><?php echo esc_textarea($purchase_note); ?></textarea>
	<?php
}

add_action('save_post_product', function ($post_id) {
	if (
		(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		|| wp_is_post_revision($post_id)
		|| !current_user_can('edit_product', $post_id)
	) {
		return;
	}

	if (
		empty($_POST['gelikon_product_sale_status_nonce'])
		|| !wp_verify_nonce(
			sanitize_text_field(wp_unslash($_POST['gelikon_product_sale_status_nonce'])),
			'gelikon_save_product_sale_status'
		)
	) {
		return;
	}

	update_post_meta($post_id, '_gelikon_preorder_enabled', isset($_POST['gelikon_preorder_enabled']) ? '1' : '0');
	update_post_meta($post_id, '_gelikon_discontinued', isset($_POST['gelikon_discontinued']) ? '1' : '0');

	$purchase_note = isset($_POST['gelikon_purchase_note']) ? wp_kses_post(wp_unslash($_POST['gelikon_purchase_note'])) : '';
	update_post_meta($post_id, '_gelikon_purchase_note', trim($purchase_note));
}, 30);

/**
 * Keep unavailable products visible in catalog, but block buying where needed.
 */
add_filter('option_woocommerce_hide_out_of_stock_items', function () {
	return 'no';
}, 20);

add_filter('woocommerce_product_is_in_stock', function ($is_in_stock, $product) {
	if ($product && is_a($product, 'WC_Product')) {
		$product_id = $product->get_id();

		if (gelikon_is_product_discontinued($product_id)) {
			return false;
		}

		if (gelikon_is_product_preorder($product_id)) {
			return true;
		}
	}

	return $is_in_stock;
}, 20, 2);

add_filter('woocommerce_product_get_stock_status', function ($stock_status, $product) {
	if ($product && is_a($product, 'WC_Product')) {
		$product_id = $product->get_id();

		if (gelikon_is_product_discontinued($product_id)) {
			return 'outofstock';
		}

		if (gelikon_is_product_preorder($product_id)) {
			return 'onbackorder';
		}
	}

	return $stock_status;
}, 20, 2);

add_filter('woocommerce_product_get_backorders', function ($backorders, $product) {
	if ($product && is_a($product, 'WC_Product') && gelikon_is_product_preorder($product->get_id())) {
		return 'yes';
	}

	return $backorders;
}, 20, 2);

add_filter('woocommerce_product_backorders_allowed', function ($allowed, $product_id, $product) {
	if ($product && is_a($product, 'WC_Product') && gelikon_is_product_preorder($product->get_id())) {
		return true;
	}

	return $allowed;
}, 20, 3);

add_filter('woocommerce_product_is_purchasable', function ($is_purchasable, $product) {
	if ($product && is_a($product, 'WC_Product') && gelikon_is_product_discontinued($product->get_id())) {
		return false;
	}

	return $is_purchasable;
}, 20, 2);

add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id) {
	if (gelikon_is_product_discontinued($product_id)) {
		wc_add_notice('Товар снят с продажи и недоступен для покупки.', 'error');
		return false;
	}

	return $passed;
}, 20, 2);

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

	if (gelikon_is_product_discontinued($product_id)) {
		$text  = 'Снят с продажи';
		$class = 'is-discontinued';
	} elseif (gelikon_is_product_preorder($product_id) || $product->get_stock_status() === 'onbackorder') {
		$text  = 'Предзаказ';
		$class = 'is-preorder';
	} elseif ($product->is_in_stock()) {
		$text  = 'В наличии';
		$class = 'is-instock';
	} else {
		$text  = 'Нет в наличии';
		$class = 'is-outofstock';
	}

	ob_start();
	?>

	<div class="gl-write-btn gl-product-stock-status <?php echo esc_attr($class); ?>">
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










add_action('acf/init', function () {
	if (!function_exists('acf_add_local_field_group')) return;

	acf_add_local_field_group([
		'key' => 'group_gelikon_contacts_page',
		'title' => 'Страница Контакты',
		'fields' => [
			['key' => 'field_company_address','label' => 'Адрес','name' => 'company_address','type' => 'textarea','rows' => 3],
			['key' => 'field_company_phones','label' => 'Телефон','name' => 'company_phones','type' => 'textarea','rows' => 2],
			['key' => 'field_company_email','label' => 'Email','name' => 'company_email','type' => 'email'],
			['key' => 'field_company_work_hours','label' => 'График работы','name' => 'company_work_hours','type' => 'text'],
			['key' => 'field_company_parking_note','label' => 'Дополнительно (парковка)','name' => 'company_parking_note','type' => 'text'],
			[
				'key' => 'field_company_location_group',
				'label' => 'Локация компании',
				'name' => 'company_location',
				'type' => 'group',
				'layout' => 'block',
				'sub_fields' => [
					['key' => 'field_company_location_latitude','label' => 'Широта','name' => 'latitude','type' => 'text'],
					['key' => 'field_company_location_longitude','label' => 'Долгота','name' => 'longitude','type' => 'text'],
					['key' => 'field_company_location_link','label' => 'Ссылка на Яндекс.Карты','name' => 'yandex_maps_link','type' => 'url'],
					['key' => 'field_company_location_image','label' => 'Изображение схемы проезда','name' => 'location_scheme_image','type' => 'image','return_format' => 'array','preview_size' => 'medium'],
				],
			],
			['key' => 'field_company_name','label' => 'Название компании','name' => 'company_name','type' => 'text'],
			['key' => 'field_company_inn','label' => 'ИНН','name' => 'company_inn','type' => 'text'],
			['key' => 'field_company_ogrn','label' => 'ОГРН','name' => 'company_ogrn','type' => 'text'],
			['key' => 'field_company_kpp','label' => 'КПП','name' => 'company_kpp','type' => 'text'],
			['key' => 'field_company_legal_address','label' => 'Юридический адрес','name' => 'company_legal_address','type' => 'textarea','rows' => 2],
			['key' => 'field_company_actual_address','label' => 'Фактический адрес','name' => 'company_actual_address','type' => 'textarea','rows' => 2],
			['key' => 'field_company_bank_details','label' => 'Банковские реквизиты','name' => 'company_bank_details','type' => 'textarea','rows' => 3],
			['key' => 'field_company_director','label' => 'Генеральный директор','name' => 'company_director','type' => 'text'],
			['key' => 'field_company_details_pdf','label' => 'PDF с реквизитами','name' => 'company_details_pdf','type' => 'file','return_format' => 'array','mime_types' => 'pdf'],
		],
		'location' => [
			[
				[
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'page-contacts.php',
				],
			],
		],
	]);
});







add_action('acf/init', function () {
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group([
		'key' => 'group_gelikon_about_page',
		'title' => 'О компании — контент страницы',
		'fields' => [
			['key' => 'field_about_hero_title', 'label' => 'Hero title', 'name' => 'hero_title', 'type' => 'text', 'default_value' => 'Gelikon Line'],
			['key' => 'field_about_hero_subtitle', 'label' => 'Hero subtitle', 'name' => 'hero_subtitle', 'type' => 'text', 'default_value' => 'Технологии для жизни и здоровья'],
			['key' => 'field_about_hero_background', 'label' => 'Hero background', 'name' => 'hero_background', 'type' => 'file', 'return_format' => 'array', 'mime_types' => 'jpg,jpeg,png,webp,mp4'],
			['key' => 'field_about_intro_text', 'label' => 'Вводный текст', 'name' => 'intro_text', 'type' => 'wysiwyg'],
			['key' => 'field_about_history_title', 'label' => 'History title', 'name' => 'history_title', 'type' => 'text', 'default_value' => 'История и опыт'],
			['key' => 'field_about_history_text', 'label' => 'History text', 'name' => 'history_text', 'type' => 'wysiwyg'],
			['key' => 'field_about_history_image', 'label' => 'History image', 'name' => 'history_image', 'type' => 'image', 'return_format' => 'array'],
			[
				'key' => 'field_about_approach_items',
				'label' => 'Подход к продукции',
				'name' => 'approach_items',
				'type' => 'repeater',
				'min' => 0,
				'max' => 4,
				'layout' => 'row',
				'button_label' => 'Добавить пункт',
				'sub_fields' => [
					['key' => 'field_about_approach_icon', 'label' => 'Icon', 'name' => 'icon', 'type' => 'image', 'return_format' => 'array'],
					['key' => 'field_about_approach_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text'],
					['key' => 'field_about_approach_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea'],
				],
			],
			['key' => 'field_about_production_text', 'label' => 'Production text', 'name' => 'production_text', 'type' => 'wysiwyg'],
			['key' => 'field_about_production_image', 'label' => 'Production image', 'name' => 'production_image', 'type' => 'image', 'return_format' => 'array'],
			['key' => 'field_about_clients_text', 'label' => 'Clients text', 'name' => 'clients_text', 'type' => 'wysiwyg'],
			['key' => 'field_about_clients_logos', 'label' => 'Clients logos', 'name' => 'clients_logos', 'type' => 'gallery', 'return_format' => 'array', 'preview_size' => 'medium'],
			['key' => 'field_about_warranty_text', 'label' => 'Warranty text', 'name' => 'warranty_text', 'type' => 'wysiwyg'],
			['key' => 'field_about_brand_text', 'label' => 'Brand text', 'name' => 'brand_text', 'type' => 'wysiwyg'],
		],
		'location' => [[['param' => 'page_template', 'operator' => '==', 'value' => 'page-about-company.php']]],
	]);
});










add_action('acf/init', function () {
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group([
		'key' => 'group_gelikon_warranty_page',
		'title' => 'Гарантия и возврат — контент страницы',
		'fields' => [
			[
				'key' => 'field_warranty_hero_title',
				'label' => 'Hero title',
				'name' => 'warranty_hero_title',
				'type' => 'text',
				'default_value' => 'Гарантия и возврат',
			],
			[
				'key' => 'field_warranty_hero_subtitle',
				'label' => 'Hero subtitle',
				'name' => 'warranty_hero_subtitle',
				'type' => 'text',
				'default_value' => 'Прозрачные условия обслуживания и возврата продукции',
			],
			[
				'key' => 'field_warranty_benefits',
				'label' => 'Основные преимущества',
				'name' => 'warranty_benefits',
				'type' => 'repeater',
				'layout' => 'row',
				'button_label' => 'Добавить пункт',
				'sub_fields' => [
					[
						'key' => 'field_warranty_benefits_icon',
						'label' => 'Icon',
						'name' => 'icon',
						'type' => 'image',
						'return_format' => 'array',
						'preview_size' => 'thumbnail',
						'library' => 'all',
					],
					[
						'key' => 'field_warranty_benefits_title',
						'label' => 'Title',
						'name' => 'title',
						'type' => 'text',
					],
					[
						'key' => 'field_warranty_benefits_text',
						'label' => 'Text',
						'name' => 'text',
						'type' => 'text',
					],
				],
			],
			[
				'key' => 'field_warranty_main_text',
				'label' => 'Текст блока «Гарантия»',
				'name' => 'warranty_main_text',
				'type' => 'wysiwyg',
			],
			[
				'key' => 'field_warranty_return_conditions',
				'label' => 'Условия возврата',
				'name' => 'warranty_return_conditions',
				'type' => 'textarea',
				'rows' => 6,
			],
			[
				'key' => 'field_warranty_exclusions',
				'label' => 'Когда гарантия не действует',
				'name' => 'warranty_exclusions',
				'type' => 'textarea',
				'rows' => 6,
			],
			[
				'key' => 'field_warranty_return_steps',
				'label' => 'Как оформить возврат',
				'name' => 'warranty_return_steps',
				'type' => 'textarea',
				'rows' => 6,
			],
			[
				'key' => 'field_warranty_refund_text',
				'label' => 'Возврат денежных средств',
				'name' => 'warranty_refund_text',
				'type' => 'wysiwyg',
			],
			[
				'key' => 'field_warranty_defect_text',
				'label' => 'Товары ненадлежащего качества',
				'name' => 'warranty_defect_text',
				'type' => 'textarea',
				'rows' => 6,
			],
			[
				'key' => 'field_warranty_return_address',
				'label' => 'Адрес для возврата',
				'name' => 'warranty_return_address',
				'type' => 'textarea',
				'rows' => 3,
			],
			[
				'key' => 'field_warranty_return_schedule',
				'label' => 'График работы',
				'name' => 'warranty_return_schedule',
				'type' => 'text',
			],
			[
				'key' => 'field_warranty_phones',
				'label' => 'Телефоны',
				'name' => 'warranty_phones',
				'type' => 'textarea',
				'rows' => 2,
			],
			[
				'key' => 'field_warranty_email',
				'label' => 'Email',
				'name' => 'warranty_email',
				'type' => 'email',
			],
		],
		'location' => [
			[
				[
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'page-warranty-returns.php',
				],
			],
		],
	]);
});













/**
 * Gelikon header cart counter fragment.
 */
add_filter('woocommerce_add_to_cart_fragments', 'gelikon_header_cart_count_fragment');

function gelikon_header_cart_count_fragment($fragments) {
	$count = (class_exists('WooCommerce') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;

	$fragments['span.gl-cart-count'] = '<span class="gl-cart-count">' . esc_html($count) . '</span>';
	$fragments['.gl-cart-count']      = '<span class="gl-cart-count">' . esc_html($count) . '</span>';

	return $fragments;
}


/**
 * Gelikon AJAX remove cart item.
 */
add_action('wp_ajax_gelikon_remove_cart_item', 'gelikon_remove_cart_item_ajax');
add_action('wp_ajax_nopriv_gelikon_remove_cart_item', 'gelikon_remove_cart_item_ajax');

function gelikon_remove_cart_item_ajax() {
	if (!class_exists('WooCommerce') || !WC()->cart) {
		wp_send_json_error();
	}

	$cart_item_key = isset($_POST['cart_item_key'])
		? sanitize_text_field(wp_unslash($_POST['cart_item_key']))
		: '';

	if (!$cart_item_key) {
		wp_send_json_error();
	}

	WC()->cart->remove_cart_item($cart_item_key);
	WC()->cart->calculate_totals();

	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	$count = WC()->cart->get_cart_contents_count();

	wp_send_json_success([
		'count'     => $count,
		'fragments' => [
			'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
			'.gl-cart-count'                  => '<span class="gl-cart-count">' . esc_html($count) . '</span>',
			'span.gl-cart-count'              => '<span class="gl-cart-count">' . esc_html($count) . '</span>',
		],
	]);
}


/**
 * Gelikon AJAX update cart item quantity.
 */
add_action('wp_ajax_gelikon_update_cart_item_qty', 'gelikon_update_cart_item_qty_ajax');
add_action('wp_ajax_nopriv_gelikon_update_cart_item_qty', 'gelikon_update_cart_item_qty_ajax');

function gelikon_update_cart_item_qty_ajax() {
	if (!class_exists('WooCommerce') || !WC()->cart) {
		wp_send_json_error();
	}

	$cart_item_key = isset($_POST['cart_item_key'])
		? sanitize_text_field(wp_unslash($_POST['cart_item_key']))
		: '';

	$qty = isset($_POST['qty'])
		? max(0, (int) $_POST['qty'])
		: 1;

	if (!$cart_item_key) {
		wp_send_json_error();
	}

	if ($qty <= 0) {
		WC()->cart->remove_cart_item($cart_item_key);
	} else {
		WC()->cart->set_quantity($cart_item_key, $qty, false);
	}

	WC()->cart->calculate_totals();

	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	$count = WC()->cart->get_cart_contents_count();

	wp_send_json_success([
		'count'     => $count,
		'fragments' => [
			'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
			'.gl-cart-count'                  => '<span class="gl-cart-count">' . esc_html($count) . '</span>',
			'span.gl-cart-count'              => '<span class="gl-cart-count">' . esc_html($count) . '</span>',
		],
	]);
}


/**
 * Gelikon mini cart + checkout UX.
 */
add_action('wp_footer', function () {
	if (is_admin() || !class_exists('WooCommerce')) {
		return;
	}

	$checkout_url = wc_get_checkout_url();
	$ajax_url     = admin_url('admin-ajax.php');
	?>
	<style id="gelikon-mini-cart-style">
		.gl-product-card__actions .added_to_cart.wc-forward {
			display: none !important;
		}

		a.gl-product-card__button.is-in-cart,
		a.gl-product-card__button.added,
		.gl-product-card__button.is-in-cart,
		.gl-product-card__button.added,
		.single_add_to_cart_button.is-in-cart {
			background: #12D457 !important;
			border-color: #12D457 !important;
			color: #fff !important;
		}

		a.gl-product-card__button.is-in-cart:hover,
		a.gl-product-card__button.added:hover {
			color: #fff !important;
		}

		.gl-full-mini-cart {
			position: fixed;
			top: 92px;
			right: 72px;
			z-index: 99999;
			width: 430px;
			max-width: calc(100vw - 24px);
			background: #fff;
			border-radius: 22px;
			box-shadow: 0 18px 55px rgba(23, 29, 42, .18);
			opacity: 0;
			visibility: hidden;
			pointer-events: none;
			transform: translateY(-8px);
			transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
			overflow: hidden;
		}

		.gl-full-mini-cart.is-visible {
			opacity: 1;
			visibility: visible;
			pointer-events: auto;
			transform: translateY(0);
		}

		.gl-full-mini-cart__head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			padding: 18px 18px 12px;
			border-bottom: 1px solid #eef1f3;
		}

		.gl-full-mini-cart__title {
			margin: 0;
			font-size: 17px;
			font-weight: 800;
			color: #171d2a;
		}

		.gl-full-mini-cart__close {
			width: 32px;
			height: 32px;
			border: 0;
			border-radius: 50%;
			background: #f4f6f8;
			cursor: pointer;
			font-size: 20px;
			line-height: 1;
			color: #7d8490;
		}

		.gl-full-mini-cart__items {
			max-height: 360px;
			overflow-y: auto;
			padding: 6px 18px;
		}

		.gl-full-mini-cart__item {
			position: relative;
			display: grid;
			grid-template-columns: 74px 1fr;
			gap: 14px;
			padding: 14px 28px 14px 0;
			border-bottom: 1px solid #eef1f3;
		}

		.gl-full-mini-cart__item:last-child {
			border-bottom: 0;
		}

		.gl-full-mini-cart__image {
			width: 74px;
			height: 74px;
			border-radius: 14px;
			background: #f6f8f9;
			object-fit: contain;
		}

		.gl-full-mini-cart__name {
			display: block;
			margin: 0 0 6px;
			font-size: 14px;
			font-weight: 600;
			line-height: 1.35;
			color: #15191f !important;
			text-decoration: none !important;
		}

		.gl-full-mini-cart__meta {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			flex-wrap: wrap;
		}

		.gl-full-mini-cart__qty-control {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			margin-top: 8px;
			padding: 4px;
			border-radius: 999px;
			background: #f4f6f8;
		}

		.gl-full-mini-cart__qty-btn {
			width: 28px;
			height: 28px;
			border: 0;
			border-radius: 50%;
			background: #fff;
			color: #171d2a;
			font-size: 18px;
			font-weight: 700;
			line-height: 1;
			cursor: pointer;
			box-shadow: 0 3px 10px rgba(23, 29, 42, .08);
			transition: .18s ease;
		}

		.gl-full-mini-cart__qty-btn:hover {
			background: #12D457;
			color: #fff;
		}

		.gl-full-mini-cart__qty-value {
			min-width: 24px;
			text-align: center;
			font-size: 14px;
			font-weight: 700;
			color: #171d2a;
		}

		.gl-full-mini-cart__qty-control.is-loading {
			opacity: .45;
			pointer-events: none;
		}

		.gl-full-mini-cart__remove {
			position: absolute;
			top: 14px;
			right: 0;
			width: 24px;
			height: 24px;
			border-radius: 50%;
			background: #f4f6f8;
			color: #9aa2ad !important;
			text-decoration: none !important;
			font-size: 20px;
			line-height: 22px;
			text-align: center;
			font-weight: 400;
			transition: .2s ease;
		}

		.gl-full-mini-cart__remove:hover {
			background: #ffecec;
			color: #ff3b30 !important;
		}

		.gl-full-mini-cart__footer {
			padding: 14px 18px 18px;
			border-top: 1px solid #eef1f3;
			background: #fff;
		}

		.gl-full-mini-cart__total {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 14px;
			font-size: 16px;
			font-weight: 800;
			color: #171d2a;
		}

		.gl-full-mini-cart__checkout {
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 46px;
			border-radius: 999px;
			font-size: 14px;
			font-weight: 700;
			text-decoration: none !important;
			background: #12D457;
			color: #fff !important;
			border: 1px solid #12D457;
		}

		.gl-full-mini-cart__checkout:hover {
			background: #10bf4f;
			border-color: #10bf4f;
			color: #fff !important;
		}

		.gl-full-mini-cart__empty {
			padding: 28px 18px;
			text-align: center;
			color: #7d8490;
			font-size: 15px;
		}

		.gl-full-mini-cart__loader {
			padding: 18px;
		}

		.gl-full-mini-cart__skeleton {
			display: grid;
			grid-template-columns: 74px 1fr;
			gap: 14px;
			padding: 12px 0;
		}

		.gl-full-mini-cart__skeleton-img,
		.gl-full-mini-cart__skeleton-line {
			background: linear-gradient(90deg, #f1f3f5 25%, #e8ecef 37%, #f1f3f5 63%);
			background-size: 400% 100%;
			animation: glSkeleton 1.2s ease infinite;
		}

		.gl-full-mini-cart__skeleton-img {
			width: 74px;
			height: 74px;
			border-radius: 14px;
		}

		.gl-full-mini-cart__skeleton-line {
			height: 14px;
			border-radius: 999px;
			margin-bottom: 10px;
		}

		.gl-full-mini-cart__skeleton-line:nth-child(1) {
			width: 90%;
		}

		.gl-full-mini-cart__skeleton-line:nth-child(2) {
			width: 55%;
		}

		@keyframes glSkeleton {
			0% { background-position: 100% 0; }
			100% { background-position: 0 0; }
		}

		.gl-full-mini-cart.is-loading .gl-full-mini-cart__items {
			pointer-events: none;
		}

		body.woocommerce-checkout .woocommerce-checkout-review-order-table thead,
		body.woocommerce-checkout .woocommerce-checkout-review-order-table tbody {
			display: none !important;
		}

		body.woocommerce-checkout .woocommerce-checkout-review-order-table {
			margin-top: 0 !important;
		}

		body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot tr.cart-subtotal th,
		body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot tr.cart-subtotal td {
			border-top: 0 !important;
		}

		@media (max-width: 767px) {
			.gl-full-mini-cart {
				top: auto !important;
				right: 12px !important;
				left: 12px;
				bottom: 16px;
				width: auto;
				border-radius: 20px;
			}

			.gl-full-mini-cart__items {
				max-height: 300px;
			}
		}
	</style>

	<script id="gelikon-mini-cart-script">
	(function(){
		if (window.glFullMiniCartInitialized) return;
		window.glFullMiniCartInitialized = true;

		const MINI_CART_ID = 'gl-full-mini-cart';
		const checkoutUrl = <?php echo wp_json_encode($checkout_url); ?>;
		const ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;

		let lastCartTrigger = null;
		let refreshRequest = null;
		let refreshSequence = 0;
		const qtyTimers = {};
		const qtyRequests = {};
		const qtyDelay = 250;

		function ensureMiniCart() {
			let panel = document.getElementById(MINI_CART_ID);

			if (panel) return panel;

			panel = document.createElement('div');
			panel.id = MINI_CART_ID;
			panel.className = 'gl-full-mini-cart';

			panel.innerHTML = `
				<div class="gl-full-mini-cart__head">
					<p class="gl-full-mini-cart__title">Корзина</p>
					<button type="button" class="gl-full-mini-cart__close" aria-label="Закрыть">×</button>
				</div>

				<div class="gl-full-mini-cart__body">
					<div class="gl-full-mini-cart__items"></div>
				</div>

				<div class="gl-full-mini-cart__footer" style="display:none;">
					<div class="gl-full-mini-cart__total">
						<span>Итого</span>
						<strong>0 ₽</strong>
					</div>

					<div class="gl-full-mini-cart__actions">
						<a class="gl-full-mini-cart__checkout" href="${checkoutUrl}">Оформить заказ</a>
					</div>
				</div>
			`;

			document.body.appendChild(panel);

			panel.querySelector('.gl-full-mini-cart__close').addEventListener('click', hideMiniCart);

			return panel;
		}

		function setMiniCartLoading() {
			const panel = ensureMiniCart();
			const itemsNode = panel.querySelector('.gl-full-mini-cart__items');

			panel.classList.add('is-loading');

			itemsNode.innerHTML = `
				<div class="gl-full-mini-cart__loader">
					<div class="gl-full-mini-cart__skeleton">
						<div class="gl-full-mini-cart__skeleton-img"></div>
						<div>
							<div class="gl-full-mini-cart__skeleton-line"></div>
							<div class="gl-full-mini-cart__skeleton-line"></div>
						</div>
					</div>

					<div class="gl-full-mini-cart__skeleton">
						<div class="gl-full-mini-cart__skeleton-img"></div>
						<div>
							<div class="gl-full-mini-cart__skeleton-line"></div>
							<div class="gl-full-mini-cart__skeleton-line"></div>
						</div>
					</div>
				</div>
			`;
		}

		function setCartCount(count) {
			const safeCount = Math.max(parseInt(count, 10) || 0, 0);

			document.querySelectorAll('.gl-cart-count').forEach(function(node){
				node.textContent = String(safeCount);
			});
		}

		function showMiniCart() {
			const panel = ensureMiniCart();

			positionMiniCart(panel);
			panel.classList.add('is-visible');

		}

		function hideMiniCart() {
			const panel = ensureMiniCart();
			panel.classList.remove('is-visible');
		}

		function positionMiniCart(panel) {
			const cartLink = document.querySelector('.gl-cart-link');

			if (!cartLink || window.innerWidth <= 767) {
				panel.style.top = '';
				panel.style.right = '';
				return;
			}

			const rect = cartLink.getBoundingClientRect();
			const panelWidth = 430;
			const gap = 14;

			let right = window.innerWidth - rect.right - 8;

			if (right < 20) {
				right = 20;
			}

			panel.style.top = (rect.bottom + gap) + 'px';
			panel.style.right = right + 'px';

			const leftEdge = window.innerWidth - right - panelWidth;

			if (leftEdge < 12) {
				panel.style.right = '12px';
			}
		}

		function setButtonInCartState(button) {
			if (!button) return;

			button.classList.add('is-in-cart', 'added');
			button.textContent = 'В корзине';
			button.style.color = '#fff';
			button.setAttribute('aria-label', 'Товар уже в корзине');
		}

		function stripHtml(html) {
			const temp = document.createElement('div');
			temp.innerHTML = html || '';
			return temp.textContent || '';
		}

		function updateCartCountFromFragments(fragments) {
			if (!fragments || typeof fragments !== 'object') return false;

			const html = fragments['.gl-cart-count'] || fragments['span.gl-cart-count'];

			if (!html) return false;

			const temp = document.createElement('div');
			temp.innerHTML = html;

			const incoming = temp.querySelector('.gl-cart-count') || temp.firstElementChild;

			if (!incoming) return false;

			document.querySelectorAll('.gl-cart-count').forEach(function(node){
				node.textContent = incoming.textContent;
			});

			return true;
		}

		function incrementCartCountFallback() {
			document.querySelectorAll('.gl-cart-count').forEach(function(node){
				const current = parseInt((node.textContent || '').replace(/\D+/g, ''), 10);
				node.textContent = String((isNaN(current) ? 0 : current) + 1);
			});
		}

		function renderMiniCartFromFragments(fragments) {
			const panel = ensureMiniCart();
			const itemsNode = panel.querySelector('.gl-full-mini-cart__items');
			const totalNode = panel.querySelector('.gl-full-mini-cart__total strong');
			const footerNode = panel.querySelector('.gl-full-mini-cart__footer');

			let miniCartHtml = '';

			if (fragments && fragments['div.widget_shopping_cart_content']) {
				miniCartHtml = fragments['div.widget_shopping_cart_content'];
			}

			if (!miniCartHtml && window.jQuery) {
				const widgetContent = window.jQuery('.widget_shopping_cart_content').first();

				if (widgetContent.length) {
					miniCartHtml = widgetContent.html();
				}
			}

			if (!miniCartHtml) {
				return;
			}

			const temp = document.createElement('div');
			temp.innerHTML = miniCartHtml;

			const wooItems = temp.querySelectorAll('.woocommerce-mini-cart-item, .mini_cart_item');
			const wooTotal = temp.querySelector('.woocommerce-mini-cart__total .amount, .woocommerce-mini-cart__total bdi, .total .amount, .total bdi');

			if (!wooItems.length) {
				itemsNode.innerHTML = '<div class="gl-full-mini-cart__empty">Корзина пуста</div>';
				totalNode.textContent = '0 ₽';

				if (footerNode) {
					footerNode.style.display = 'none';
				}

				panel.classList.remove('is-loading');
				return;
			}

			if (footerNode) {
				footerNode.style.display = '';
			}

			let html = '';

			wooItems.forEach(function(item){
				const img = item.querySelector('img');
				const remove = item.querySelector('.remove, .remove_from_cart_button');
				const qtyNode = item.querySelector('.quantity');

				const removeHref = remove ? remove.href : '#';
				const removeKey = remove ? remove.getAttribute('data-cart_item_key') : '';
				const removeProductId = remove ? remove.getAttribute('data-product_id') : '';

				const qtyText = qtyNode ? qtyNode.textContent.replace(/\s+/g, ' ').trim() : '';
				const qtyNumberMatch = qtyText.match(/(\d+)/);
				const qtyNumber = qtyNumberMatch ? parseInt(qtyNumberMatch[1], 10) : 1;

				if (qtyNode) {
					qtyNode.remove();
				}

				if (remove) {
					remove.remove();
				}

				const link = item.querySelector('a:not(.remove)');
				const name = link ? stripHtml(link.innerHTML).replace(/\s+/g, ' ').trim() : stripHtml(item.innerHTML).trim();
				const href = link ? link.href : '#';
				const imgSrc = img ? img.src : '';

				html += `
					<div class="gl-full-mini-cart__item">
						<a
							class="gl-full-mini-cart__remove"
							href="${removeHref}"
							data-cart_item_key="${removeKey || ''}"
							data-product_id="${removeProductId || ''}"
							aria-label="Удалить товар"
						>×</a>

						${imgSrc ? `<img class="gl-full-mini-cart__image" src="${imgSrc}" alt="">` : `<div class="gl-full-mini-cart__image"></div>`}

						<div>
							<a class="gl-full-mini-cart__name" href="${href}">${name}</a>

							<div class="gl-full-mini-cart__meta">
								<div class="gl-full-mini-cart__qty-control" data-cart_item_key="${removeKey || ''}" data-qty="${qtyNumber}">
									<button type="button" class="gl-full-mini-cart__qty-btn" data-qty-action="minus" aria-label="Уменьшить количество">−</button>
									<span class="gl-full-mini-cart__qty-value">${qtyNumber}</span>
									<button type="button" class="gl-full-mini-cart__qty-btn" data-qty-action="plus" aria-label="Увеличить количество">+</button>
								</div>
							</div>
						</div>
					</div>
				`;
			});

			itemsNode.innerHTML = html;
			totalNode.textContent = wooTotal ? wooTotal.textContent.replace(/\s+/g, ' ').trim() : '';

			panel.classList.remove('is-loading');
		}

		function refreshMiniCartAjax(callback, options) {
			if (!window.wc_cart_fragments_params || !window.jQuery) return;

			const settings = options || {};
			const sequence = ++refreshSequence;

			if (settings.showLoading !== false) {
				setMiniCartLoading();
			}

			if (refreshRequest) {
				refreshRequest.abort();
			}

			refreshRequest = window.jQuery.ajax({
				url: window.wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
				type: 'POST',
				success: function(data) {
					if (sequence !== refreshSequence) {
						return;
					}

					if (data && data.fragments) {
						renderMiniCartFromFragments(data.fragments);
						updateCartCountFromFragments(data.fragments);

						if (typeof callback === 'function') {
							callback(data);
						}
					}
				},
				complete: function(_xhr, status) {
					if (status !== 'abort') {
						ensureMiniCart().classList.remove('is-loading');
						refreshRequest = null;
					}
				}
			});
		}

		function removeMiniCartItem(removeButton) {
			if (!removeButton || !window.jQuery) {
				return;
			}

			const cartItemKey = removeButton.getAttribute('data-cart_item_key');
			const item = removeButton.closest('.gl-full-mini-cart__item');

			if (!cartItemKey) {
				return;
			}

			removeButton.classList.add('is-loading');

			if (item) {
				item.style.opacity = '.45';
				item.style.pointerEvents = 'none';
			}

			window.jQuery.ajax({
				type: 'POST',
				url: ajaxUrl,
				data: {
					action: 'gelikon_remove_cart_item',
					cart_item_key: cartItemKey
				},
				success: function(response) {
					if (!response || !response.success || !response.data) {
						refreshMiniCartAjax();
						return;
					}

					if (response.data.fragments) {
						renderMiniCartFromFragments(response.data.fragments);
					}

					if (typeof response.data.count !== 'undefined') {
						setCartCount(response.data.count);
					}

					showMiniCart();
				},
				error: function() {
					refreshMiniCartAjax();
				}
			});
		}

		function updateMiniCartItemQty(button) {
			if (!button || !window.jQuery) {
				return;
			}

			const control = button.closest('.gl-full-mini-cart__qty-control');

			if (!control) {
				return;
			}

			const cartItemKey = control.getAttribute('data-cart_item_key');
			const valueNode = control.querySelector('.gl-full-mini-cart__qty-value');
			const action = button.getAttribute('data-qty-action');
			let currentQty = parseInt(control.getAttribute('data-pending-qty'), 10);

			if (Number.isNaN(currentQty)) {
				currentQty = valueNode ? parseInt(valueNode.textContent, 10) : parseInt(control.getAttribute('data-qty'), 10);
			}

			if (Number.isNaN(currentQty) || currentQty < 1) {
				currentQty = 1;
			}

			let newQty = currentQty;

			if (action === 'plus') {
				newQty = currentQty + 1;
			}

			if (action === 'minus') {
				newQty = currentQty - 1;
			}

			if (!cartItemKey || newQty < 0) {
				return;
			}

			control.classList.remove('is-loading');
			control.setAttribute('data-pending-qty', String(newQty));
			control.setAttribute('data-qty', String(newQty));

			if (valueNode) {
				valueNode.textContent = String(newQty);
			}

			const item = control.closest('.gl-full-mini-cart__item');

			if (item) {
				item.style.opacity = newQty === 0 ? '.45' : '';
				item.style.pointerEvents = '';
			}

			if (qtyTimers[cartItemKey]) {
				window.clearTimeout(qtyTimers[cartItemKey]);
			}

			qtyTimers[cartItemKey] = window.setTimeout(function(){
				const finalQty = parseInt(control.getAttribute('data-pending-qty'), 10);

				control.classList.add('is-loading');

				if (qtyRequests[cartItemKey]) {
					qtyRequests[cartItemKey].abort();
				}

				qtyRequests[cartItemKey] = window.jQuery.ajax({
					type: 'POST',
					url: ajaxUrl,
					data: {
						action: 'gelikon_update_cart_item_qty',
						cart_item_key: cartItemKey,
						qty: finalQty
					},
					success: function(response) {
						if (!response || !response.success || !response.data) {
							refreshMiniCartAjax();
							return;
						}

						if (response.data.fragments) {
							renderMiniCartFromFragments(response.data.fragments);
						}

						if (typeof response.data.count !== 'undefined') {
							setCartCount(response.data.count);
						}

						showMiniCart();
					},
					error: function(xhr, status) {
						if (status !== 'abort') {
							refreshMiniCartAjax();
						}
					},
					complete: function() {
						qtyRequests[cartItemKey] = null;
					}
				});
			}, qtyDelay);
		}

		function removePostponedNotice() {
			document.querySelectorAll('.woocommerce-message, .woocommerce-info, .woocommerce-error li, .woocommerce-notices-wrapper > *').forEach(function(notice){
				const text = (notice.textContent || '').toLowerCase();

				if (
					text.includes('вы отложили товар') ||
					text.includes('отложили товар')
				) {
					notice.remove();
				}
			});
		}

		document.querySelectorAll('.gl-product-card__button.added, .single_add_to_cart_button.added').forEach(setButtonInCartState);

		document.body.addEventListener('click', function(event){
			const qtyButton = event.target.closest('.gl-full-mini-cart__qty-btn');

			if (qtyButton) {
				event.preventDefault();
				updateMiniCartItemQty(qtyButton);
				return;
			}

			const removeButton = event.target.closest('.gl-full-mini-cart__remove');

			if (removeButton) {
				event.preventDefault();
				removeMiniCartItem(removeButton);
				return;
			}

			const btn = event.target.closest('.ajax_add_to_cart, .single_add_to_cart_button');

			if (btn) {
				lastCartTrigger = btn;
			}
		});

		document.addEventListener('mouseenter', function(event){
			const cartLink = event.target.closest('.gl-cart-link');

			if (!cartLink) return;

			showMiniCart();
			refreshMiniCartAjax();
		}, true);

		window.addEventListener('resize', function(){
			const panel = ensureMiniCart();

			if (panel.classList.contains('is-visible')) {
				positionMiniCart(panel);
			}
		});

		if (window.jQuery) {
			window.jQuery(document.body).on('adding_to_cart', function(_e, button){
				if (button && button[0]) {
					lastCartTrigger = button[0];
				}

				showMiniCart();
				setMiniCartLoading();
			});

			window.jQuery(document.body).on('added_to_cart', function(_e, fragments, _hash, button){
				const target = (button && button[0]) || lastCartTrigger;

				if (target) {
					setButtonInCartState(target);
				}

				renderMiniCartFromFragments(fragments);

				if (!updateCartCountFromFragments(fragments)) {
					incrementCartCountFallback();
				}

				showMiniCart();
				refreshMiniCartAjax(null, { showLoading: false });
			});
		}

		document.body.addEventListener('submit', function(event){
			const form = event.target.closest('form.cart');

			if (!form) return;

			const submitButton = form.querySelector('.single_add_to_cart_button');

			if (!submitButton || submitButton.disabled) return;

			lastCartTrigger = submitButton;

			showMiniCart();
			setMiniCartLoading();
		});

		removePostponedNotice();

		const noticeObserver = new MutationObserver(removePostponedNotice);
		noticeObserver.observe(document.body, {
			childList: true,
			subtree: true
		});
	})();
	</script>
	<?php
}, 99);







/**
 * Checkout: RU compact flow.
 */
add_filter('woocommerce_checkout_fields', function ($fields) {
	if (isset($fields['billing'])) {
		$fields['billing']['billing_first_name']['label'] = 'ФИО';
		$fields['billing']['billing_first_name']['placeholder'] = 'Иванов Иван Иванович';
		$fields['billing']['billing_first_name']['required'] = true;
		$fields['billing']['billing_first_name']['priority'] = 10;

		unset($fields['billing']['billing_last_name'], $fields['billing']['billing_company'], $fields['billing']['billing_country'], $fields['billing']['billing_state'], $fields['billing']['billing_postcode'], $fields['billing']['billing_city'], $fields['billing']['billing_address_1'], $fields['billing']['billing_address_2']);

		$fields['billing']['billing_phone']['label'] = 'Телефон';
		$fields['billing']['billing_phone']['placeholder'] = '+7 (___) ___-__-__';
		$fields['billing']['billing_phone']['required'] = true;
		$fields['billing']['billing_phone']['priority'] = 20;

		$fields['billing']['billing_email']['label'] = 'Email';
		$fields['billing']['billing_email']['placeholder'] = 'example@mail.ru';
		$fields['billing']['billing_email']['required'] = false;
		$fields['billing']['billing_email']['priority'] = 30;
	}

	if (isset($fields['shipping'])) {
		unset($fields['shipping']['shipping_first_name'], $fields['shipping']['shipping_last_name'], $fields['shipping']['shipping_company'], $fields['shipping']['shipping_country'], $fields['shipping']['shipping_state'], $fields['shipping']['shipping_postcode'], $fields['shipping']['shipping_address_2']);
		$fields['shipping']['shipping_city']['label'] = 'Город';
		$fields['shipping']['shipping_city']['placeholder'] = 'Москва';
		$fields['shipping']['shipping_city']['priority'] = 10;
		$fields['shipping']['shipping_address_1']['label'] = 'Улица и дом';
		$fields['shipping']['shipping_address_1']['placeholder'] = 'Ленинский проспект, 10';
		$fields['shipping']['shipping_address_1']['priority'] = 20;
	}

	if (isset($fields['order']['order_comments'])) {
		$fields['order']['order_comments']['label'] = 'Комментарий к заказу';
	}

	return $fields;
}, 20);

add_filter('gettext', function ($translated, $text, $domain) {
	if ('woocommerce' !== $domain) {
		return $translated;
	}
	if ('Have a coupon? %s' === $text) {
		return 'Есть купон? %s';
	}
	if ('Click here to enter your code' === $text) {
		return 'Показать поле';
	}
	return $translated;
}, 10, 3);

add_action('wp', function () {
	if (function_exists('is_checkout') && is_checkout()) {
		remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
	}
});


/**
 * Checkout: RU compact flow + sync shipping address to billing for CDEK.
 */
add_filter('woocommerce_checkout_fields', function ($fields) {
	if (isset($fields['billing'])) {
		$fields['billing']['billing_first_name']['label'] = 'ФИО';
		$fields['billing']['billing_first_name']['placeholder'] = 'Иванов Иван Иванович';
		$fields['billing']['billing_first_name']['required'] = true;
		$fields['billing']['billing_first_name']['priority'] = 10;

		unset($fields['billing']['billing_last_name'], $fields['billing']['billing_company'], $fields['billing']['billing_country'], $fields['billing']['billing_state'], $fields['billing']['billing_postcode'], $fields['billing']['billing_city'], $fields['billing']['billing_address_1'], $fields['billing']['billing_address_2']);

		$fields['billing']['billing_phone']['label'] = 'Телефон';
		$fields['billing']['billing_phone']['placeholder'] = '+7 (___) ___-__-__';
		$fields['billing']['billing_phone']['required'] = true;
		$fields['billing']['billing_phone']['priority'] = 20;

		$fields['billing']['billing_email']['label'] = 'Email';
		$fields['billing']['billing_email']['placeholder'] = 'example@mail.ru';
		$fields['billing']['billing_email']['required'] = false;
		$fields['billing']['billing_email']['priority'] = 30;
	}

	if (isset($fields['shipping'])) {
		unset($fields['shipping']['shipping_first_name'], $fields['shipping']['shipping_last_name'], $fields['shipping']['shipping_company'], $fields['shipping']['shipping_country'], $fields['shipping']['shipping_state'], $fields['shipping']['shipping_postcode'], $fields['shipping']['shipping_address_2']);

		$fields['shipping']['shipping_city']['label'] = 'Город';
		$fields['shipping']['shipping_city']['placeholder'] = 'Москва';
		$fields['shipping']['shipping_city']['priority'] = 10;

		$fields['shipping']['shipping_address_1']['label'] = 'Улица и дом';
		$fields['shipping']['shipping_address_1']['placeholder'] = 'Ленинский проспект, 10';
		$fields['shipping']['shipping_address_1']['priority'] = 20;
	}

	if (isset($fields['order']['order_comments'])) {
		$fields['order']['order_comments']['label'] = 'Комментарий к заказу';
	}

	return $fields;
}, 20);


/**
 * During WooCommerce AJAX checkout update:
 * copy shipping city/address into billing customer data for CDEK.
 */
add_action('woocommerce_checkout_update_order_review', function ($post_data) {
	parse_str($post_data, $data);

	$city    = !empty($data['shipping_city']) ? wc_clean(wp_unslash($data['shipping_city'])) : '';
	$address = !empty($data['shipping_address_1']) ? wc_clean(wp_unslash($data['shipping_address_1'])) : '';

	WC()->customer->set_billing_country('RU');
	WC()->customer->set_shipping_country('RU');

	if ($city) {
		WC()->customer->set_billing_city($city);
		WC()->customer->set_shipping_city($city);
		WC()->customer->set_billing_state($city);
		WC()->customer->set_shipping_state($city);
	}

	if ($address) {
		WC()->customer->set_billing_address_1($address);
		WC()->customer->set_shipping_address_1($address);
	}

	WC()->customer->save();
});


/**
 * On order creation:
 * save shipping city/address into billing fields too.
 */
add_action('woocommerce_checkout_create_order', function ($order, $data) {
	$city    = !empty($data['shipping_city']) ? wc_clean($data['shipping_city']) : '';
	$address = !empty($data['shipping_address_1']) ? wc_clean($data['shipping_address_1']) : '';

	$order->set_billing_country('RU');
	$order->set_shipping_country('RU');

	if ($city) {
		$order->set_billing_city($city);
		$order->set_shipping_city($city);
		$order->set_billing_state($city);
		$order->set_shipping_state($city);
	}

	if ($address) {
		$order->set_billing_address_1($address);
		$order->set_shipping_address_1($address);
	}
}, 20, 2);


/**
 * Frontend checkout JS:
 * create hidden billing fields and sync values on input, before CDEK/WooCommerce recalculation.
 */
add_action('wp_footer', function () {
	if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
		return;
	}
	?>
	<script>
		jQuery(function ($) {
			var glLastCheckoutCity = '';
			var glLastCheckoutAddress = '';

			function glCreateBillingField(name) {
				var $field = $('[name="' + name + '"]');

				if (!$field.length) {
					$field = $('<input>', {
						type: 'hidden',
						name: name,
						id: name,
						value: ''
					});

					$('form.checkout').append($field);
				}

				return $field;
			}

			function glNormalizeCheckoutValue(value) {
				return $.trim(value || '').replace(/\s+/g, ' ');
			}

			function glGetShippingCity() {
				return glNormalizeCheckoutValue($('[name="shipping_city"]').val());
			}

			function glGetShippingAddress() {
				return glNormalizeCheckoutValue($('[name="shipping_address_1"]').val());
			}

			function glSyncShippingToBilling() {
				var shippingCity = glGetShippingCity();
				var shippingAddress = glGetShippingAddress();

				glCreateBillingField('billing_country').val('RU');
				glCreateBillingField('billing_city').val(shippingCity);
				glCreateBillingField('billing_address_1').val(shippingAddress);
				glCreateBillingField('billing_state').val(shippingCity);
				glCreateBillingField('billing_postcode').val('');
			}

			function glTriggerCheckoutUpdate() {
				glLastCheckoutCity = glGetShippingCity();
				glLastCheckoutAddress = glGetShippingAddress();
				$(document.body).trigger('update_checkout');
			}

			function glSetDetectedCheckoutCity(city) {
				city = glNormalizeCheckoutValue(city);

				if (!city || glGetShippingCity()) {
					return;
				}

				$('[name="shipping_city"]').val(city).trigger('change');
				glSyncShippingToBilling();
				glScheduleCheckoutUpdate(100);
			}

			function glDetectCheckoutCity() {
				if (glGetShippingCity() || !navigator.geolocation) {
					return;
				}

				navigator.geolocation.getCurrentPosition(function(position) {
					var coords = [position.coords.latitude, position.coords.longitude];

					if (window.ymaps && typeof window.ymaps.geocode === 'function') {
						window.ymaps.geocode(coords).then(function(response) {
							var first = response.geoObjects && response.geoObjects.get(0);

							if (!first) {
								return;
							}

							var props = first.properties || null;
							var meta = props && props.get ? props.get('metaDataProperty') : null;
							var address = meta && meta.GeocoderMetaData && meta.GeocoderMetaData.Address ? meta.GeocoderMetaData.Address.Components : [];
							var city = '';

							(address || []).some(function(component) {
								if (component.kind === 'locality' || component.kind === 'province') {
									city = component.name;
									return true;
								}

								return false;
							});

							glSetDetectedCheckoutCity(city);
						});
					}
				}, function() {}, { enableHighAccuracy: false, timeout: 8000, maximumAge: 600000 });
			}

			function glScheduleCheckoutUpdate(delay) {
				clearTimeout(window.glCdekUpdateTimer);
				window.glCdekUpdateTimer = setTimeout(glTriggerCheckoutUpdate, delay || 900);
			}

			$(document.body).on('input', '[name="shipping_city"], [name="shipping_address_1"]', function () {
				glSyncShippingToBilling();
			});

			$(document.body).on('input', '[name="shipping_city"]', function () {
				var shippingCity = glGetShippingCity();

				if (shippingCity.length >= 2 && shippingCity !== glLastCheckoutCity) {
					glScheduleCheckoutUpdate(900);
				}
			});

			$(document.body).on('change blur', '[name="shipping_city"]', function () {
				glSyncShippingToBilling();

				if (glGetShippingCity() !== glLastCheckoutCity) {
					glScheduleCheckoutUpdate(100);
				}
			});

			$(document.body).on('change blur', '[name="shipping_address_1"]', function () {
				glSyncShippingToBilling();

				if (glGetShippingAddress() !== glLastCheckoutAddress) {
					glScheduleCheckoutUpdate(250);
				}
			});

			$(document.body).on('update_checkout checkout_place_order', function () {
				glSyncShippingToBilling();
			});

			$('form.checkout').on('submit', function () {
				glSyncShippingToBilling();
			});

			glSyncShippingToBilling();
			glLastCheckoutCity = glGetShippingCity();
			glLastCheckoutAddress = glGetShippingAddress();
			glDetectCheckoutCity();
		});
	</script>
	<?php
}, 99);










/**
 * Product description fields: plain textarea instead of Gutenberg/visual editors.
 */

/**
 * Отключаем Gutenberg для товаров.
 */
add_filter('use_block_editor_for_post_type', function ($use_block_editor, $post_type) {
	return $post_type === 'product' ? false : $use_block_editor;
}, 10, 2);


/**
 * Убираем стандартный редактор описания товара.
 */
add_action('init', function () {
	remove_post_type_support('product', 'editor');
}, 100);


/**
 * Добавляем обычное textarea для полного описания товара.
 */
add_action('add_meta_boxes_product', function () {
	add_meta_box(
		'gelikon_plain_product_description',
		'Описание товара',
		'gelikon_plain_product_description_metabox',
		'product',
		'normal',
		'high'
	);
});

function gelikon_plain_product_description_metabox($post) {
	wp_nonce_field('gelikon_save_plain_product_description', 'gelikon_plain_product_description_nonce');

	$content = get_post_field('post_content', $post->ID);

	echo '<p style="margin-top:0;color:#666;">Вставляйте обычный текст. Лишние стили, шрифты и HTML будут удалены при сохранении.</p>';

	echo '<textarea name="gelikon_product_description" style="width:100%;min-height:260px;font-family:monospace;font-size:14px;line-height:1.5;">' . esc_textarea($content) . '</textarea>';
}


/**
 * Заменяем краткое описание товара на обычное textarea.
 */
add_action('add_meta_boxes_product', function () {
	remove_meta_box('postexcerpt', 'product', 'normal');

	add_meta_box(
		'gelikon_plain_product_excerpt',
		'Краткое описание товара',
		'gelikon_plain_product_excerpt_metabox',
		'product',
		'normal',
		'high'
	);
}, 99);

function gelikon_plain_product_excerpt_metabox($post) {
	$excerpt = get_post_field('post_excerpt', $post->ID);

	echo '<p style="margin-top:0;color:#666;">Краткий текст рядом с ценой/кнопкой покупки. Без сторонних стилей и шрифтов.</p>';

	echo '<textarea name="gelikon_product_excerpt" style="width:100%;min-height:160px;font-family:monospace;font-size:14px;line-height:1.5;">' . esc_textarea($excerpt) . '</textarea>';
}


/**
 * Сохраняем оба поля.
 */
add_action('save_post_product', 'gelikon_save_plain_product_fields', 20);

function gelikon_save_plain_product_fields($post_id) {
	if (
		defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
		|| wp_is_post_revision($post_id)
		|| !current_user_can('edit_product', $post_id)
	) {
		return;
	}

	if (
		empty($_POST['gelikon_plain_product_description_nonce'])
		|| !wp_verify_nonce(
			sanitize_text_field(wp_unslash($_POST['gelikon_plain_product_description_nonce'])),
			'gelikon_save_plain_product_description'
		)
	) {
		return;
	}

	remove_action('save_post_product', 'gelikon_save_plain_product_fields', 20);

	if (isset($_POST['gelikon_product_description'])) {
		$description = gelikon_clean_plain_product_text(wp_unslash($_POST['gelikon_product_description']));

		wp_update_post([
			'ID'           => $post_id,
			'post_content' => $description,
		]);
	}

	if (isset($_POST['gelikon_product_excerpt'])) {
		$excerpt = gelikon_clean_plain_product_text(wp_unslash($_POST['gelikon_product_excerpt']));

		wp_update_post([
			'ID'           => $post_id,
			'post_excerpt' => $excerpt,
		]);
	}

	add_action('save_post_product', 'gelikon_save_plain_product_fields', 20);
}


/**
 * Чистка текста от мусора Word / Google Docs / inline-стилей.
 */
function gelikon_clean_plain_product_text($content) {
	$content = trim($content);

	$content = preg_replace('/<!--(.|\s)*?-->/', '', $content);
	$content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content);
	$content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
	$content = preg_replace('/<font\b[^>]*>(.*?)<\/font>/is', '$1', $content);
	$content = preg_replace('/<span\b[^>]*>(.*?)<\/span>/is', '$1', $content);

	// WP All Import can wrap plain product descriptions in heading tags.
	// Keep the imported text, but drop H4/H5/H6 wrappers from descriptions.
	$content = preg_replace('/<h[4-6]\b[^>]*>(.*?)<\/h[4-6]>/is', '$1', $content);

	$content = preg_replace('/\s(style|class|id|face|font-family|lang|width|height)="[^"]*"/i', '', $content);
	$content = preg_replace("/\s(style|class|id|face|font-family|lang|width|height)='[^']*'/i", '', $content);

	$content = wp_kses($content, [
		'p'      => [],
		'br'     => [],
		'strong' => [],
		'b'      => [],
		'em'     => [],
		'i'      => [],
		'ul'     => [],
		'ol'     => [],
		'li'     => [],
		'h2'     => [],
		'h3'     => [],
		'a'      => [
			'href'   => [],
			'title'  => [],
			'target' => [],
			'rel'    => [],
		],
	]);

	$content = wpautop($content);

	return $content;
}

/**
 * Clean imported/saved product descriptions even when they bypass the custom textarea.
 */
add_filter('wp_insert_post_data', function ($data, $postarr) {
	if (($data['post_type'] ?? '') !== 'product') {
		return $data;
	}

	if (!empty($data['post_content'])) {
		$data['post_content'] = gelikon_clean_plain_product_text($data['post_content']);
	}

	if (!empty($data['post_excerpt'])) {
		$data['post_excerpt'] = gelikon_clean_plain_product_text($data['post_excerpt']);
	}

	return $data;
}, 20, 2);













/**
 * Разрешить загрузку SVG в медиабиблиотеку.
 */

/**
 * Добавляем SVG в список разрешённых MIME.
 */
add_filter('upload_mimes', function ($mimes) {
	if (current_user_can('manage_options')) {
		$mimes['svg'] = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	}

	return $mimes;
});


/**
 * Исправляем проверку типа файла для SVG.
 */
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
	$filetype = wp_check_filetype($filename, $mimes);

	if (isset($filetype['ext']) && in_array($filetype['ext'], ['svg', 'svgz'], true)) {
		$data['ext']  = $filetype['ext'];
		$data['type'] = 'image/svg+xml';
	}

	return $data;
}, 10, 4);


/**
 * Показываем SVG-превью в медиабиблиотеке.
 */
add_action('admin_head', function () {
	echo '<style>
		.attachment-266x266, 
		.thumbnail img[src$=".svg"] {
			width: 100% !important;
			height: auto !important;
		}

		.media-icon img[src$=".svg"],
		img[src$=".svg"].attachment-post-thumbnail {
			width: 100% !important;
			height: auto !important;
		}
	</style>';
});










/**
 * Gelikon: чистим НДС и строки доставки в заказе / письмах.
 */
function gelikon_clean_includes_tax_text($html) {
	if (!is_string($html)) {
		return $html;
	}

	$html = preg_replace(
		'~<small\b([^>]*)>\(\s*включая\s*(.*?)\s*НДС\s*\)</small>~su',
		'<small$1>$2 НДС</small>',
		$html
	);

	$html = preg_replace_callback(
		'~<small\b([^>]*)>(.*?)</small>~su',
		function($matches) {
			$attrs = $matches[1];
			$content = $matches[2];

			if (strpos($content, 'НДС') === false) {
				return $matches[0];
			}

			if (strpos($attrs, 'style=') !== false) {
				$attrs = preg_replace(
					'~style="([^"]*)"~',
					'style="$1;font-weight:normal;font-size:14px;line-height:1.3;white-space:nowrap;"',
					$attrs
				);
			} else {
				$attrs .= ' style="font-weight:normal;font-size:14px;line-height:1.3;white-space:nowrap;"';
			}

			return '<small' . $attrs . '>' . $content . '</small>';
		},
		$html
	);

	return $html;
}

/**
 * Checkout / Cart total.
 */
add_filter('woocommerce_cart_totals_order_total_html', 'gelikon_clean_includes_tax_text', 20);

/**
 * Thank you page / My account / Emails totals.
 */
add_filter('woocommerce_get_order_item_totals', function($total_rows, $order, $tax_display) {

	foreach ($total_rows as $key => $row) {

		/**
		 * Убираем дубль доставки:
		 * было: Доставка: Доставка - Бесплатно / Доставка - Бесплатно
		 * будет: Доставка: / Бесплатно
		 */
		if ($key === 'shipping') {
			$total_rows[$key]['label'] = 'Доставка:';
			$total_rows[$key]['value'] = 'Бесплатно';
		}

		if (!empty($total_rows[$key]['value'])) {
			$total_rows[$key]['value'] = gelikon_clean_includes_tax_text($total_rows[$key]['value']);
		}
	}

	return $total_rows;

}, 20, 3);

/**
 * Formatted order total.
 */
add_filter('woocommerce_get_formatted_order_total', 'gelikon_clean_includes_tax_text', 20);

/**
 * Email items table.
 */
add_filter('woocommerce_email_order_items_table', 'gelikon_clean_includes_tax_text', 20);










/**
 * Gelikon: добавляем настройку скидки в метод оплаты Т-Банк.
 */
add_filter('woocommerce_settings_api_form_fields_tbank', 'gelikon_add_tbank_discount_setting');

function gelikon_add_tbank_discount_setting($fields) {

	$fields['gelikon_discount_percent'] = array(
		'title'       => 'Скидка за онлайн-оплату (%)',
		'type'        => 'number',
		'description' => 'Укажите процент скидки при выборе оплаты через Т-Банк. Например: 5',
		'default'     => '5',
		'desc_tip'    => true,
		'custom_attributes' => array(
			'min'  => '0',
			'max'  => '100',
			'step' => '0.1',
		),
	);

	return $fields;
}

/**
 * Gelikon: скидка при выборе онлайн-оплаты Т-Банк.
 */
add_action('woocommerce_cart_calculate_fees', 'gelikon_tbank_payment_discount', 20);

function gelikon_tbank_payment_discount($cart) {
	if (is_admin() && !defined('DOING_AJAX')) {
		return;
	}

	if (!$cart || $cart->is_empty()) {
		return;
	}

	$chosen_payment_method = WC()->session ? WC()->session->get('chosen_payment_method') : '';

	if ($chosen_payment_method !== 'tbank') {
		return;
	}

	$tbank_settings = get_option('woocommerce_tbank_settings', array());

	$discount_percent = isset($tbank_settings['gelikon_discount_percent'])
		? (float) str_replace(',', '.', $tbank_settings['gelikon_discount_percent'])
		: 0;

	if ($discount_percent <= 0) {
		return;
	}

	$discount = $cart->get_subtotal() * ($discount_percent / 100);

	if ($discount <= 0) {
		return;
	}

	$cart->add_fee('Скидка', -$discount, false);
}

/**
 * Обновляем checkout при выборе способа оплаты.
 */
add_action('wp_footer', 'gelikon_update_checkout_on_payment_change');

function gelikon_update_checkout_on_payment_change() {
	if (!is_checkout() || is_order_received_page()) {
		return;
	}
	?>
	<script>
		jQuery(function($) {
			$(document.body).on('change', 'input[name="payment_method"]', function() {
				$(document.body).trigger('update_checkout');
			});
		});
	</script>
	<?php
}





/**
 * Gelikon: принудительно выбираем Т-Банк по умолчанию на checkout.
 */
add_action('template_redirect', 'gelikon_set_default_checkout_payment_method');

function gelikon_set_default_checkout_payment_method() {
	if (!function_exists('WC') || !WC()->session) {
		return;
	}

	if (!is_checkout() || is_order_received_page()) {
		return;
	}

	$default_gateway = 'tbank';

	$chosen_gateway = WC()->session->get('chosen_payment_method');

	/**
	 * Если ничего не выбрано или выбран Яндекс Сплит — ставим Т-Банк.
	 */
	if (empty($chosen_gateway) || strpos($chosen_gateway, 'split') !== false || strpos($chosen_gateway, 'yandex') !== false) {
		WC()->session->set('chosen_payment_method', $default_gateway);
	}
}
















add_action('wp_ajax_gelikon_checkout_remove_cart_item', 'gelikon_checkout_remove_cart_item');
add_action('wp_ajax_nopriv_gelikon_checkout_remove_cart_item', 'gelikon_checkout_remove_cart_item');

function gelikon_checkout_remove_cart_item() {
	if (
		empty($_POST['nonce']) ||
		! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'update-order-review')
	) {
		wp_send_json_error();
	}

	if (! class_exists('WooCommerce') || ! WC()->cart) {
		wp_send_json_error();
	}

	$cart_item_key = isset($_POST['cart_item_key'])
		? sanitize_text_field(wp_unslash($_POST['cart_item_key']))
		: '';

	if (! $cart_item_key || ! WC()->cart->get_cart_item($cart_item_key)) {
		wp_send_json_error();
	}

	WC()->cart->remove_cart_item($cart_item_key);
	WC()->cart->calculate_totals();

	wp_send_json_success([
		'count' => WC()->cart->get_cart_contents_count(),
	]);
}










add_action('wp_footer', 'gelikon_checkout_phone_mask_script', 100);

function gelikon_checkout_phone_mask_script() {
	if (! is_checkout() || is_order_received_page()) {
		return;
	}
	?>
	<script>
		jQuery(function($) {
			function gelikonInitPhoneMask() {
				const phone = $('#billing_phone');

				if (!phone.length || typeof Inputmask === 'undefined') {
					return;
				}

				Inputmask({
					mask: '+7 (999) 999-99-99',
					showMaskOnHover: false,
					clearIncomplete: false
				}).mask(phone);
			}

			gelikonInitPhoneMask();

			$(document.body).on('updated_checkout', function() {
				gelikonInitPhoneMask();
			});
		});
	</script>
	<?php
}

add_action('wp_enqueue_scripts', 'gelikon_enqueue_inputmask');

function gelikon_enqueue_inputmask() {
	if (! is_checkout() || is_order_received_page()) {
		return;
	}

	wp_enqueue_script(
		'inputmask',
		'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js',
		array('jquery'),
		'5.0.9',
		true
	);
}






/**
 * Текст кнопки оформления заказа только для T-Bank.
 */
add_filter('woocommerce_available_payment_gateways', function($gateways) {
	if (is_admin() && !wp_doing_ajax()) {
		return $gateways;
	}

	if (isset($gateways['tbank'])) {
		$gateways['tbank']->order_button_text = 'Оплатить';
	}

	return $gateways;
});






/**
 * Gelikon — стилизация WooCommerce уведомлений.
 */
add_action('wp_head', function () {
	?>
	<style>
		.woocommerce-notices-wrapper,
		.woocommerce-NoticeGroup,
		.woocommerce-NoticeGroup-checkout {
			margin: 0 0 22px;
		}

		.woocommerce-error,
		.woocommerce-info,
		.woocommerce-message {
			position: relative;
			margin: 0 0 18px !important;
			padding: 18px 22px 18px 56px !important;
			border: 1px solid #E5EBE7 !important;
			border-left: 5px solid #12D457 !important;
			border-radius: 22px !important;
			background: #fff !important;
			color: #171D2A !important;
			box-shadow: 0 14px 34px rgba(23, 29, 42, 0.08) !important;
			font-size: 15px;
			line-height: 1.45;
			list-style: none !important;
		}

		.woocommerce-error {
			border-left-color: #EF4444 !important;
			background: #FFF7F7 !important;
		}

		.woocommerce-info {
			border-left-color: #3B82F6 !important;
			background: #F7FBFF !important;
		}

		.woocommerce-message {
			border-left-color: #12D457 !important;
			background: #F7FFF9 !important;
		}

		.woocommerce-error::before,
		.woocommerce-info::before,
		.woocommerce-message::before {
			position: absolute;
			top: 18px;
			left: 22px;
			width: 22px;
			height: 22px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			border-radius: 50%;
			font-size: 13px;
			font-weight: 700;
			line-height: 1;
		}

		.woocommerce-error::before {
			content: "!";
			background: #EF4444;
			color: #fff;
		}

		.woocommerce-info::before {
			content: "i";
			background: #3B82F6;
			color: #fff;
		}

		.woocommerce-message::before {
			content: "✓";
			background: #12D457;
			color: #fff;
		}

		.woocommerce-error li,
		.woocommerce-info li,
		.woocommerce-message li {
			margin: 0 0 8px !important;
			padding: 0 !important;
			list-style: none !important;
		}

		.woocommerce-error li:last-child,
		.woocommerce-info li:last-child,
		.woocommerce-message li:last-child {
			margin-bottom: 0 !important;
		}

		.woocommerce-error a,
		.woocommerce-info a,
		.woocommerce-message a {
			color: inherit !important;
			text-decoration: none !important;
			font-weight: 500;
		}

		.woocommerce-error strong,
		.woocommerce-info strong,
		.woocommerce-message strong {
			font-weight: 700;
			color: #171D2A;
		}

		.woocommerce-message .button,
		.woocommerce-info .button,
		.woocommerce-error .button {
			float: none !important;
			display: inline-flex !important;
			align-items: center;
			justify-content: center;
			margin: 10px 0 0 !important;
			padding: 10px 18px !important;
			border-radius: 999px !important;
			background: #12D457 !important;
			color: #fff !important;
			font-size: 14px !important;
			font-weight: 600 !important;
			line-height: 1 !important;
			text-decoration: none !important;
		}

		.woocommerce-error .button {
			background: #EF4444 !important;
		}

		.woocommerce-info .button {
			background: #3B82F6 !important;
		}

		@media (max-width: 767px) {
			.woocommerce-error,
			.woocommerce-info,
			.woocommerce-message {
				padding: 16px 18px 16px 50px !important;
				border-radius: 18px !important;
				font-size: 14px;
			}

			.woocommerce-error::before,
			.woocommerce-info::before,
			.woocommerce-message::before {
				top: 16px;
				left: 18px;
			}
		}
	</style>
	<?php
}, 99);


/**
 * Gelikon — отключаем inline-ошибки под полями checkout.
 */
add_action('wp_head', function () {
	if (!is_checkout()) {
		return;
	}
	?>
	<style>
		.checkout-inline-error-message,
		p.checkout-inline-error-message,
		.woocommerce-checkout .checkout-inline-error-message,
		.woocommerce-checkout [id$="_description"].checkout-inline-error-message {
			display: none !important;
		}
	</style>
	<?php
}, 100);




add_filter('woocommerce_thankyou_order_received_text', function($text, $order) {
	return 'Благодарим за заказ';
}, 10, 2);

add_filter('woocommerce_email_heading_customer_processing_order', function($heading, $order, $email) {
	return 'Благодарим за заказ';
}, 10, 3);

add_filter('woocommerce_email_heading_customer_completed_order', function($heading, $order, $email) {
	return 'Благодарим за заказ';
}, 10, 3);







/**
 * Thank you page: сохраняем адрес доставки из shipping_city / shipping_address_1.
 */
add_action('woocommerce_checkout_create_order', function($order, $data) {

	if (!empty($_POST['shipping_city'])) {
		$order->set_shipping_city(sanitize_text_field(wp_unslash($_POST['shipping_city'])));
	}

	if (!empty($_POST['shipping_address_1'])) {
		$order->set_shipping_address_1(sanitize_text_field(wp_unslash($_POST['shipping_address_1'])));
	}

	// Если доставка не заполнена, подставляем платежный адрес, чтобы не было Н/Д.
	if (!$order->get_shipping_city() && $order->get_billing_city()) {
		$order->set_shipping_city($order->get_billing_city());
	}

	if (!$order->get_shipping_address_1() && $order->get_billing_address_1()) {
		$order->set_shipping_address_1($order->get_billing_address_1());
	}

}, 20, 2);


add_filter('woocommerce_order_get_formatted_shipping_address', function($address, $order) {

	if (!$order instanceof WC_Order) {
		return $address;
	}

	$city      = $order->get_shipping_city();
	$address_1 = $order->get_shipping_address_1();

	if (!$city && !$address_1) {
		return $address;
	}

	$parts = array_filter([
		$address_1,
		$city,
	]);

	return esc_html(implode(', ', $parts));

}, 20, 2);








/**
 * Убираем сообщение "Товар добавлен в корзину" на странице оформления заказа
 */
add_action('template_redirect', function () {
	if (function_exists('is_checkout') && is_checkout() && !is_order_received_page()) {
		wc_clear_notices();
	}
});




/**
 * AJAX добавление товара в корзину на странице товара без перезагрузки
 */
add_action('wp_footer', function () {
	if (!is_product()) {
		return;
	}
	?>
	<script>
	jQuery(function ($) {
		$(document).on('submit', 'form.cart', function (e) {
			e.preventDefault();

			const $form = $(this);
			const $button = $form.find('.single_add_to_cart_button').first();

			if (!$button.length || $button.hasClass('disabled') || $button.hasClass('loading') || $button.data('gelikonProcessing')) {
				return;
			}

			const productId = $form.find('[name="add-to-cart"]').val() || $form.find('[name="product_id"]').val() || $button.val();

			if (!productId) {
				return;
			}

			const data = {};

			$.each($form.serializeArray(), function (_index, field) {
				data[field.name] = field.value;
			});

			data.product_id = data.product_id || productId;
			data.quantity = data.quantity || $form.find('input.qty').val() || 1;

			// The sticky product bars use a hidden add-to-cart input for non-JS fallback.
			// When sent to the WooCommerce AJAX endpoint, that field can also trigger
			// the regular form handler and add the same product a second time.
			delete data['add-to-cart'];

			$button.data('gelikonProcessing', true).addClass('loading').prop('disabled', true);
			$(document.body).trigger('adding_to_cart', [$button, data]);

			$.ajax({
				type: 'POST',
				url: wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', 'add_to_cart'),
				data: data,
				success: function (response) {
					if (!response) {
						return;
					}

					if (response.error && response.product_url) {
						window.location = response.product_url;
						return;
					}

					$(document.body).trigger('added_to_cart', [
						response.fragments,
						response.cart_hash,
						$button
					]);

					$button.removeClass('loading').addClass('added');
				},
				complete: function () {
					$button.data('gelikonProcessing', false).prop('disabled', false).removeClass('loading');
				}
			});
		});

	});
	</script>
	<?php
});

add_action('wp_enqueue_scripts', function () {
	if (is_product()) {
		wp_enqueue_script('wc-add-to-cart');
		wp_enqueue_script('wc-cart-fragments');
	}
});














/**
 * Gelikon checkout update cart item quantity.
 */
add_action('wp_ajax_gelikon_checkout_update_cart_item_qty', 'gelikon_checkout_update_cart_item_qty');
add_action('wp_ajax_nopriv_gelikon_checkout_update_cart_item_qty', 'gelikon_checkout_update_cart_item_qty');

function gelikon_checkout_update_cart_item_qty() {
	if (
		empty($_POST['nonce']) ||
		! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'update-order-review')
	) {
		wp_send_json_error();
	}

	if (! class_exists('WooCommerce') || ! WC()->cart) {
		wp_send_json_error();
	}

	$cart_item_key = isset($_POST['cart_item_key'])
		? sanitize_text_field(wp_unslash($_POST['cart_item_key']))
		: '';

	$quantity = isset($_POST['quantity'])
		? max(0, (int) $_POST['quantity'])
		: 1;

	if (! $cart_item_key || ! WC()->cart->get_cart_item($cart_item_key)) {
		wp_send_json_error();
	}

	if ($quantity <= 0) {
		WC()->cart->remove_cart_item($cart_item_key);
	} else {
		WC()->cart->set_quantity($cart_item_key, $quantity, false);
	}

	WC()->cart->calculate_totals();

	wp_send_json_success([
		'count' => WC()->cart->get_cart_contents_count(),
	]);
}



add_filter('woocommerce_checkout_fields', function ($fields) {

    unset($fields['shipping']['shipping_phone']);

    return $fields;
});



/**
 * Checkout shipping: keep CDEK delivery choices visible after the free-delivery threshold.
 *
 * Orders from 3000 ₽ should still let the customer choose CDEK courier or pickup point;
 * only the calculated delivery price is changed to 0 ₽.
 */
add_filter('woocommerce_package_rates', 'gelikon_checkout_zero_cdek_rates_above_threshold', 100, 2);
add_filter('woocommerce_shipping_chosen_method', 'gelikon_checkout_choose_first_cdek_rate', 100, 3);
add_filter('woocommerce_cart_shipping_method_full_label', 'gelikon_checkout_shipping_method_label', 100, 2);

function gelikon_checkout_free_shipping_threshold() {
	return 3000;
}

function gelikon_checkout_cart_reaches_free_shipping_threshold() {
	if (! function_exists('WC') || ! WC()->cart) {
		return false;
	}

	return (float) WC()->cart->get_displayed_subtotal() >= gelikon_checkout_free_shipping_threshold();
}

function gelikon_checkout_is_cdek_shipping_rate($rate) {
	if (! $rate instanceof WC_Shipping_Rate) {
		return false;
	}

	$haystack = $rate->get_id() . ' ' . $rate->get_method_id() . ' ' . $rate->get_label();
	$haystack = function_exists('mb_strtolower') ? mb_strtolower($haystack, 'UTF-8') : strtolower($haystack);

	return strpos($haystack, 'cdek') !== false || strpos($haystack, 'сдэк') !== false;
}

function gelikon_checkout_zero_shipping_rate($rate) {
	if (! $rate instanceof WC_Shipping_Rate) {
		return;
	}

	$rate->set_cost(0);
	$rate->set_taxes(array_map(static function () {
		return 0;
	}, (array) $rate->get_taxes()));
}

function gelikon_checkout_zero_cdek_rates_above_threshold($rates, $package) {
	if (! gelikon_checkout_cart_reaches_free_shipping_threshold()) {
		return $rates;
	}

	$has_cdek_rates = false;

	foreach ($rates as $rate) {
		if (gelikon_checkout_is_cdek_shipping_rate($rate)) {
			$has_cdek_rates = true;
			break;
		}
	}

	foreach ($rates as $rate_id => $rate) {
		if ($rate instanceof WC_Shipping_Rate && $rate->get_method_id() === 'free_shipping' && $has_cdek_rates) {
			unset($rates[$rate_id]);
			continue;
		}

		if (! $has_cdek_rates || gelikon_checkout_is_cdek_shipping_rate($rate)) {
			gelikon_checkout_zero_shipping_rate($rate);
		}
	}

	return $rates;
}

function gelikon_checkout_choose_first_cdek_rate($chosen_method, $available_methods, $package) {
	if (! gelikon_checkout_cart_reaches_free_shipping_threshold()) {
		return $chosen_method;
	}

	if (isset($available_methods[$chosen_method]) && gelikon_checkout_is_cdek_shipping_rate($available_methods[$chosen_method])) {
		return $chosen_method;
	}

	foreach ($available_methods as $rate_id => $rate) {
		if (gelikon_checkout_is_cdek_shipping_rate($rate)) {
			return $rate_id;
		}
	}

	return $chosen_method;
}

function gelikon_checkout_shipping_method_label($label, $method) {
	if (! $method instanceof WC_Shipping_Rate) {
		return $label;
	}

	$cost = (float) $method->get_cost() + array_sum(array_map('floatval', (array) $method->get_taxes()));

	if ($cost <= 0 && strpos($label, 'amount') === false) {
		$label = wp_kses_post($method->get_label()) . ': ' . wc_price(0);
	}

	return $label;
}



// Отключить предупреждения WooCommerce в админке
add_filter('woocommerce_helper_suppress_admin_notices', '__return_true');

add_action('admin_init', function () {
    remove_all_actions('admin_notices');
    remove_all_actions('all_admin_notices');
}, 100);