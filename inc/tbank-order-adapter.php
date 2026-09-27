<?php
/**
 * Adds the Gelikon prefix to order numbers shown to customers and managers.
 *
 * This deliberately does not replace any T-Bank handlers and does not change
 * the numeric WooCommerce order ID sent to a payment gateway. Payment plugins
 * must remain responsible for creating payments and processing callbacks.
 */

if (!defined('ABSPATH')) {
	exit;
}

define('GELIKON_ORDER_PREFIX', 'GL-');

/**
 * Format an order number for display without changing the stored order ID.
 *
 * @param mixed $order_number Order number supplied by WooCommerce.
 * @return string
 */
function gelikon_format_order_number($order_number) {
	$order_number = (string) $order_number;

	if ($order_number === '' || strpos($order_number, GELIKON_ORDER_PREFIX) === 0) {
		return $order_number;
	}

	return GELIKON_ORDER_PREFIX . $order_number;
}

/**
 * Prefix the human-facing WooCommerce order number.
 *
 * The filter changes presentation only: WC_Order::get_id() stays numeric, so
 * T-Bank and other gateways continue to use their native payment flow.
 */
function gelikon_prefix_woocommerce_order_number($order_number, $order = null) {
	return gelikon_format_order_number($order_number);
}
add_filter('woocommerce_order_number', 'gelikon_prefix_woocommerce_order_number', 10, 2);
