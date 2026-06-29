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
		'menu_order' => __('По умолчанию (ручной порядок)', 'gelikon'),
		'date_desc'  => __('Сначала новые', 'gelikon'),
		'price_asc'  => __('Сначала дешевле', 'gelikon'),
		'price_desc' => __('Сначала дороже', 'gelikon'),
		'title_asc'  => __('По названию', 'gelikon'),
	];
}

function gelikon_sanitize_catalog_orderby($value) {
	$value   = sanitize_key($value);
	$options = gelikon_catalog_sorting_options();

	return array_key_exists($value, $options) ? $value : 'price_desc';
}

function gelikon_get_default_catalog_orderby() {
	return gelikon_sanitize_catalog_orderby(get_theme_mod('gelikon_catalog_default_orderby', 'price_desc'));
}

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
				'orderby' => 'date',
				'order'   => 'DESC',
			];

		case 'price_asc':
			return [
				'meta_key' => '_price',
				'orderby'  => 'meta_value_num',
				'order'    => 'ASC',
			];

		case 'price_desc':
			return [
				'meta_key' => '_price',
				'orderby'  => 'meta_value_num',
				'order'    => 'DESC',
			];

		case 'title_asc':
			return [
				'orderby' => 'title',
				'order'   => 'ASC',
			];

		case 'menu_order':
		default:
			return [
				'orderby' => 'menu_order title',
				'order'   => 'ASC',
			];
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
