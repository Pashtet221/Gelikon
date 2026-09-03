<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product displays use whole rubles without trailing kopecks.
 *
 * @return int
 */
function gelikon_product_price_decimals() {
	return 0;
}

/**
 * Return standard WooCommerce product price HTML without decimal places.
 *
 * The decimal override is kept local to this call so prices in orders and
 * other accounting-related areas retain the store's configured precision.
 *
 * @param WC_Product $product Product shown in the card.
 * @return string
 */
function gelikon_get_product_price_html($product) {
	if (!$product instanceof WC_Product) {
		return '';
	}

	// wc_price() gets its default precision from wc_get_price_decimals().
	// Use WooCommerce's current hook (rather than the similarly named legacy
	// setting hook) and run last so another theme/plugin cannot restore kopecks.
	add_filter('wc_get_price_decimals', 'gelikon_product_price_decimals', PHP_INT_MAX);
	$price_html = $product->get_price_html();
	remove_filter('wc_get_price_decimals', 'gelikon_product_price_decimals', PHP_INT_MAX);

	return $price_html;
}

/**
 * Backward-compatible alias for code rendering catalog cards.
 *
 * @param WC_Product $product Product shown in the card.
 * @return string
 */
function gelikon_get_product_card_price_html($product) {
	return gelikon_get_product_price_html($product);
}

/**
 * Keep the price selected for a variation consistent with the product page.
 *
 * WooCommerce sends this HTML to its variation script separately from the
 * initial product markup, so it must be formatted explicitly as well.
 *
 * @param array                $data      Variation data sent to the browser.
 * @param WC_Product_Variable $product   Parent product.
 * @param WC_Product_Variation $variation Selected variation.
 * @return array
 */
function gelikon_format_available_variation_price($data, $product, $variation) {
	$data['price_html'] = gelikon_get_product_price_html($variation);

	return $data;
}
add_filter('woocommerce_available_variation', 'gelikon_format_available_variation_price', 10, 3);

/**
 * Format an individual price for a product card without decimal places.
 *
 * @param float|string $price Raw WooCommerce price.
 * @return string
 */
function gelikon_wc_product_card_price($price) {
	return wc_price($price, ['decimals' => 0]);
}

function gelikon_wc_wrapper_before() {
    echo '<main id="primary" class="site-main"><div class="gl-container gl-shop">';
}
add_action('woocommerce_before_main_content', 'gelikon_wc_wrapper_before', 5);

function gelikon_wc_wrapper_after() {
    echo '</div></main>';
}
add_action('woocommerce_after_main_content', 'gelikon_wc_wrapper_after', 50);

remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

function gelikon_woocommerce_enqueue_fragments() {
	// Do not force WooCommerce cart fragments on every page. The theme refreshes
	// the mini cart on demand, and the native fragments script can add an extra
	// slow get_refreshed_fragments request after add-to-cart.
}
add_action('wp_enqueue_scripts', 'gelikon_woocommerce_enqueue_fragments');

function gelikon_loop_columns() {
    return 3;
}
add_filter('loop_shop_columns', 'gelikon_loop_columns');

function gelikon_products_per_page() {
    return 9;
}
add_filter('loop_shop_per_page', 'gelikon_products_per_page', 20);

function gelikon_sale_flash_text($html) {
    $badge = get_theme_mod('gelikon_promo_badge', 'Хит');
    return '<span class="onsale">' . esc_html($badge) . '</span>';
}
add_filter('woocommerce_sale_flash', 'gelikon_sale_flash_text');


/**
 * Catalog sorting options shared by catalog templates, AJAX filtering and Customizer.
 */
function gelikon_catalog_sorting_options() {
	return [
		'menu_order'  => __('По умолчанию', 'gelikon'),
		'popularity'  => __('По популярности', 'gelikon'),
		'date_desc'   => __('Сначала новые', 'gelikon'),
		'price_asc'  => __('Сначала дешевле', 'gelikon'),
		'price_desc' => __('Сначала дороже', 'gelikon'),
		'title_asc'  => __('По названию', 'gelikon'),
	];
}

function gelikon_sanitize_catalog_orderby($value) {
	$value   = sanitize_key($value);
	$options = gelikon_catalog_sorting_options();

	return array_key_exists($value, $options) ? $value : 'menu_order';
}

function gelikon_get_default_catalog_orderby() {
	return gelikon_sanitize_catalog_orderby(get_theme_mod('gelikon_catalog_default_orderby', 'menu_order'));
}

function gelikon_get_manual_catalog_product_ids() {
	$value = get_theme_mod('gelikon_catalog_manual_product_ids', '');

	if (!is_string($value) || $value === '') {
		return [];
	}

	$ids = preg_split('/[\s,;]+/', $value);
	$ids = array_map('absint', is_array($ids) ? $ids : []);
	$ids = array_values(array_unique(array_filter($ids)));

	return $ids;
}

function gelikon_sanitize_manual_catalog_product_ids($value) {
	$ids = preg_split('/[\s,;]+/', (string) $value);
	$ids = array_map('absint', is_array($ids) ? $ids : []);
	$ids = array_values(array_unique(array_filter($ids)));

	return implode(', ', $ids);
}

function gelikon_catalog_manual_ids_orderby($orderby, $query) {
	$ids = $query->get('gelikon_manual_product_ids');

	if (empty($ids) || !is_array($ids)) {
		return $orderby;
	}

	global $wpdb;

	$ids_sql = implode(',', array_map('absint', $ids));

	if ($ids_sql === '') {
		return $orderby;
	}

	return "CASE WHEN {$wpdb->posts}.ID IN ({$ids_sql}) THEN 0 ELSE 1 END ASC, FIELD({$wpdb->posts}.ID, {$ids_sql}) ASC, {$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_title ASC";
}
add_filter('posts_orderby', 'gelikon_catalog_manual_ids_orderby', 10, 2);

function gelikon_catalog_stock_last_clauses($clauses, $query) {
	if (!$query->get('gelikon_stock_last')) {
		return $clauses;
	}

	global $wpdb;

	$stock_alias        = 'gelikon_stock_status_pm';
	$discontinued_alias = 'gelikon_discontinued_pm';

	if (strpos($clauses['join'], " {$stock_alias} ") === false) {
		$clauses['join'] .= $wpdb->prepare(
			" LEFT JOIN {$wpdb->postmeta} AS {$stock_alias} ON ({$stock_alias}.post_id = {$wpdb->posts}.ID AND {$stock_alias}.meta_key = %s)",
			'_stock_status'
		);
	}

	if (strpos($clauses['join'], " {$discontinued_alias} ") === false) {
		$clauses['join'] .= $wpdb->prepare(
			" LEFT JOIN {$wpdb->postmeta} AS {$discontinued_alias} ON ({$discontinued_alias}.post_id = {$wpdb->posts}.ID AND {$discontinued_alias}.meta_key = %s)",
			'_gelikon_discontinued'
		);
	}

	$stock_last_order = "CASE WHEN {$discontinued_alias}.meta_value IN ('1', 'yes', 'on') OR {$stock_alias}.meta_value = 'outofstock' THEN 1 ELSE 0 END ASC";
	$clauses['orderby'] = $stock_last_order . ($clauses['orderby'] ? ', ' . $clauses['orderby'] : '');

	return $clauses;
}
add_filter('posts_clauses', 'gelikon_catalog_stock_last_clauses', 20, 2);

function gelikon_get_catalog_orderby_from_request($source = null) {
	if ($source === null) {
		$source = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	if (isset($source['orderby']) && $source['orderby'] !== '') {
		return gelikon_sanitize_catalog_orderby(wp_unslash($source['orderby']));
	}

	return gelikon_get_default_catalog_orderby();
}

function gelikon_get_catalog_orderby_args($orderby_selected) {
	$orderby_selected = gelikon_sanitize_catalog_orderby($orderby_selected);

	switch ($orderby_selected) {
		case 'popularity':
			return [
				'gelikon_stock_last' => true,
				'meta_key'           => 'total_sales',
				'orderby'            => 'meta_value_num',
				'order'              => 'DESC',
			];

		case 'date_desc':
			return [
				'gelikon_stock_last' => true,
				'orderby' => 'date',
				'order'   => 'DESC',
			];

		case 'price_asc':
			return [
				'gelikon_stock_last' => true,
				'meta_key' => '_price',
				'orderby'  => 'meta_value_num',
				'order'    => 'ASC',
			];

		case 'price_desc':
			return [
				'gelikon_stock_last' => true,
				'meta_key' => '_price',
				'orderby'  => 'meta_value_num',
				'order'    => 'DESC',
			];

		case 'title_asc':
			return [
				'gelikon_stock_last' => true,
				'orderby' => 'title',
				'order'   => 'ASC',
			];

		case 'menu_order':
		default:
			$args       = [
				'gelikon_stock_last' => true,
				'orderby' => 'menu_order title',
				'order'   => 'ASC',
			];
			$manual_ids = gelikon_get_manual_catalog_product_ids();

			if (!empty($manual_ids)) {
				$args['gelikon_manual_product_ids'] = $manual_ids;
			}

			return $args;
	}
}

/**
 * Return the most relevant catalog category URL for a product.
 *
 * Yoast's primary product category is preferred when it is configured. Otherwise
 * the deepest assigned category is used, so a product in "Catalog > Smart watches"
 * links to "Smart watches" rather than the broad catalog page.
 */
function gelikon_get_product_catalog_url($product_id) {
	$product_id = absint($product_id);
	$shop_url   = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');

	if (!$product_id) {
		return $shop_url;
	}

	$primary_term_id = absint(get_post_meta($product_id, '_yoast_wpseo_primary_product_cat', true));

	if ($primary_term_id) {
		$primary_term = get_term($primary_term_id, 'product_cat');

		if ($primary_term && !is_wp_error($primary_term)) {
			$primary_url = get_term_link($primary_term);

			if (!is_wp_error($primary_url)) {
				return $primary_url;
			}
		}
	}

	$categories = wp_get_post_terms($product_id, 'product_cat');

	if (is_wp_error($categories) || empty($categories)) {
		return $shop_url;
	}

	$categories = array_values(array_filter($categories, static function ($category) {
		return $category instanceof WP_Term && 'uncategorized' !== $category->slug;
	}));

	if (empty($categories)) {
		return $shop_url;
	}

	usort($categories, static function ($first, $second) {
		$first_depth  = count(get_ancestors($first->term_id, 'product_cat', 'taxonomy'));
		$second_depth = count(get_ancestors($second->term_id, 'product_cat', 'taxonomy'));

		return $second_depth <=> $first_depth;
	});

	$category_url = get_term_link($categories[0]);

	return is_wp_error($category_url) ? $shop_url : $category_url;
}

/**
 * Always make customer registration available on the My Account page.
 *
 * The custom account template renders login/registration tabs there, so the
 * WooCommerce registration handler must also be enabled for that page.
 */
function gelikon_enable_myaccount_registration_on_account_page($value) {
    $is_registration_request = !empty($_POST['register']); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $is_account_page = function_exists('is_account_page') && is_account_page();

    if (!is_admin() && !is_user_logged_in() && ($is_account_page || $is_registration_request)) {
        return 'yes';
    }

    return $value;
}
add_filter('pre_option_woocommerce_enable_myaccount_registration', 'gelikon_enable_myaccount_registration_on_account_page');

/**
 * Rename the default WooCommerce dashboard menu item to a clearer label.
 */
function gelikon_rename_myaccount_dashboard_menu_item($items) {
	if (isset($items['dashboard'])) {
		$items['dashboard'] = __('Главная', 'gelikon');
	}

	return $items;
}
add_filter('woocommerce_account_menu_items', 'gelikon_rename_myaccount_dashboard_menu_item');

/**
 * Make WooCommerce customer registration available during checkout.
 *
 * The checkout template already renders WooCommerce billing hooks, so enabling
 * this option exposes the native "Create an account" checkbox for guests.
 */
function gelikon_enable_checkout_registration($value) {
    $is_checkout_request = !empty($_POST['createaccount']) || !empty($_POST['woocommerce-process-checkout-nonce']); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $is_checkout_page = function_exists('is_checkout') && is_checkout();

    if (!is_admin() && !is_user_logged_in() && ($is_checkout_page || $is_checkout_request)) {
        return 'yes';
    }

    return $value;
}
add_filter('pre_option_woocommerce_enable_signup_and_login_from_checkout', 'gelikon_enable_checkout_registration');

/**
 * Hide WooCommerce's category display type setting.
 *
 * Product category archives use the theme's custom catalog template and always
 * render products, so the native display type selector has no effect.
 */
function gelikon_hide_product_category_display_type_field() {
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;

	if (!$screen || 'product_cat' !== $screen->taxonomy) {
		return;
	}
	?>
	<style id="gelikon-product-category-admin-css">
		.term-display-type-wrap {
			display: none;
		}
	</style>
	<?php
}
add_action('admin_head-edit-tags.php', 'gelikon_hide_product_category_display_type_field');
add_action('admin_head-term.php', 'gelikon_hide_product_category_display_type_field');
