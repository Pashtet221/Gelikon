<?php
/**
 * WooCommerce order status rules used by the store.
 *
 * Payment gateways remain responsible for confirming payments. These hooks only
 * keep a confirmed payment from being overwritten by a late cancellation and
 * define the expected status for payment on delivery.
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Allow a delayed payment callback to recover an order that was cancelled while
 * the customer was still completing payment on the gateway website.
 */
add_filter('woocommerce_valid_order_statuses_for_payment_complete', function ($statuses, $order) {
	if (!in_array('cancelled', $statuses, true)) {
		$statuses[] = 'cancelled';
	}

	return $statuses;
}, 10, 2);

/**
 * Keep pay-on-delivery orders on hold until a manager confirms fulfilment.
 * A manager can subsequently select Processing, Completed, or another status in
 * the standard WooCommerce order editor.
 */
add_filter('woocommerce_cod_process_payment_order_status', function ($status, $order) {
	return 'on-hold';
}, 10, 2);

/**
 * Repair a race where an expiry task or gateway sends a cancellation after a
 * successful payment callback. The paid date is independent of the current
 * status, unlike WC_Order::is_paid(), so it remains reliable during this hook.
 */
add_action('woocommerce_order_status_changed', function ($order_id, $from, $to, $order) {
	if ('cancelled' !== $to || !$order instanceof WC_Order || !$order->get_date_paid()) {
		return;
	}

	$paid_status = $order->needs_processing() ? 'processing' : 'completed';

	$order->update_status(
		$paid_status,
		__('Автоматическая отмена отклонена: оплата заказа уже подтверждена.', 'gelikon')
	);
}, 10, 4);
