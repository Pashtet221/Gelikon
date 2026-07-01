<?php
if (!defined('ABSPATH')) {
    exit;
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
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('wc-cart-fragments');
    }
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