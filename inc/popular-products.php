<?php
/**
 * Shared access to the products selected for the home-page popular-products block.
 */

defined('ABSPATH') || exit;

/**
 * Find the page whose ACF fields are the source for the home page.
 */
function gelikon_get_home_page_id() {
	$front_page_id = (int) get_option('page_on_front');

	if ($front_page_id) {
		return $front_page_id;
	}

	$pages = get_posts([
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_wp_page_template',
		'meta_value'     => 'page-home-gelikon.php',
		'no_found_rows'  => true,
	]);

	return !empty($pages) ? (int) $pages[0] : 0;
}

/**
 * Return the ordered WooCommerce products configured on the home page.
 *
 * The fallbacks deliberately mirror the historic home-page behaviour.
 */
function gelikon_get_home_popular_products($home_page_id = 0) {
	if (!function_exists('wc_get_products')) {
		return [];
	}

	$home_page_id = $home_page_id ? (int) $home_page_id : gelikon_get_home_page_id();
	$selected      = function_exists('get_field') ? get_field('home_popular_products', $home_page_id) : [];
	$product_ids   = [];

	if (is_array($selected)) {
		foreach ($selected as $selected_product) {
			if (is_object($selected_product) && isset($selected_product->ID)) {
				$product_ids[] = (int) $selected_product->ID;
			} elseif (is_numeric($selected_product)) {
				$product_ids[] = (int) $selected_product;
			}
		}
	}

	$product_ids = array_values(array_unique(array_filter($product_ids)));
	if ($product_ids) {
		return wc_get_products([
			'status'  => 'publish',
			'limit'   => count($product_ids),
			'include' => $product_ids,
			'orderby' => 'post__in',
		]);
	}

	$products = wc_get_products([
		'status'   => 'publish',
		'limit'    => 4,
		'featured' => true,
	]);

	return $products ?: wc_get_products([
		'status' => 'publish',
		'limit'  => 4,
	]);
}

/**
 * Locate the page assigned to the popular-products template.
 */
function gelikon_get_popular_products_page_url() {
	$pages = get_posts([
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_wp_page_template',
		'meta_value'     => 'template-popular-products.php',
		'no_found_rows'  => true,
	]);

	return !empty($pages) ? get_permalink((int) $pages[0]) : home_url('/popular-products/');
}
