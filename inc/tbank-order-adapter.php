<?php
/** T-Bank 3.0.7 integration. Plugin files and internal WooCommerce IDs stay intact. */
if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/order-numbers.php';
define('GELIKON_ORDER_PREFIX', 'GL-');

function gelikon_format_order_number($order_number) {
	$order_number = (string) $order_number;
	return $order_number === '' || strpos($order_number, GELIKON_ORDER_PREFIX) === 0
		? $order_number : GELIKON_ORDER_PREFIX . $order_number;
}

function gelikon_prefix_woocommerce_order_number($order_number, $order = null) {
	$number = $order instanceof WC_Order ? gelikon_get_sequential_order_number($order->get_id()) : '';
	if ($order instanceof WC_Order && $order->get_id() && $order->get_type() === 'shop_order' && $number === '' && gelikon_order_requires_sequence($order->get_id()) && !$order->has_status(array('auto-draft', 'draft', 'checkout-draft'))) {
		// HPOS reads get_data()/get_order_number() after reserving an ID, before woocommerce_new_order.
		gelikon_assign_order_number($order->get_id(), $order);
		$number = gelikon_get_sequential_order_number($order->get_id());
		if ($number === '') {
			throw new RuntimeException('New order number could not be reserved.');
		}
	}
	return $number !== '' ? $number : gelikon_format_order_number($order_number);
}
add_filter('woocommerce_order_number', 'gelikon_prefix_woocommerce_order_number', 10, 2);

function gelikon_tbank_gateway() {
	$gateways = WC()->payment_gateways()->payment_gateways();
	return isset($gateways['tbank']) && $gateways['tbank'] instanceof WC_TBank ? $gateways['tbank'] : null;
}

/** The plugin has no request filter; wrap its receipt/renewal actions instead. */
function gelikon_tbank_register_adapter() {
	if (!class_exists('WC_TBank') || !class_exists('SupportPaymentTBank')) {
		return;
	}
	$gateway = gelikon_tbank_gateway();
	if (!$gateway) {
		return;
	}
	remove_action('woocommerce_receipt_tbank', array($gateway, 'receipt_page'));
	add_action('woocommerce_receipt_tbank', 'gelikon_tbank_receipt_page');
	remove_action('woocommerce_scheduled_subscription_payment_tbank', array($gateway, 'scheduled_subscription_payment'), 10);
	add_action('woocommerce_scheduled_subscription_payment_tbank', 'gelikon_tbank_subscription_payment', 10, 2);
}
add_action('wp_loaded', 'gelikon_tbank_register_adapter', 20);

/** Build fiscal receipts with internal IDs; change only the external fields. */
function gelikon_tbank_init_fields($order, $gateway) {
	$settings = array();
	foreach (array('email_company', 'payment_method_ffd', 'payment_object_ffd', 'check_data_tax', 'taxation', 'payment_form_language', 'ffd') as $name) {
		$settings[$name] = $gateway->get_option($name);
	}
	new SupportPaymentTBank($settings);
	$fields = SupportPaymentTBank::send_data($order, $order->get_id());
	$number = gelikon_get_sequential_order_number($order->get_id());
	if ($number === '') {
		throw new RuntimeException('Order has no reserved Gelikon number.');
	}
	$fields['OrderId'] = $number;
	$fields['NotificationURL'] = WC()->api_request_url('gelikon_tbank_notification');
	if (function_exists('wcs_order_contains_subscription') && wcs_order_contains_subscription($order)) {
		$fields['Recurrent'] = 'Y';
		$fields['CustomerKey'] = (string) $order->get_user_id();
	}
	return SupportPaymentTBank::get_setting_language($fields);
}

/** Token algorithm used by T-Bank, with its documented boolean encoding. */
function gelikon_tbank_token($fields, $secret) {
	unset($fields['Token']);
	$fields['Password'] = htmlspecialchars_decode((string) $secret, ENT_QUOTES);
	$fields = array_filter($fields, 'is_scalar');
	ksort($fields);
	$text = '';
	foreach ($fields as $value) {
		$text .= is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
	}
	return hash('sha256', $text);
}

/** Use WordPress HTTP with TLS verification; the bundled client disables it. */
function gelikon_tbank_request($method, $fields, $gateway) {
	$terminal = (string) $gateway->get_option('merchant_id');
	$secret = (string) $gateway->get_option('secret_key');
	if ($terminal === '' || $secret === '') {
		throw new RuntimeException('T-Bank terminal credentials are not configured.');
	}
	$fields['TerminalKey'] = $terminal;
	$fields['Token'] = gelikon_tbank_token($fields, $secret);
	$response = wp_remote_post('https://securepay.tinkoff.ru/v2/' . $method, array(
		'headers' => array('Content-Type' => 'application/json'),
		'body' => wp_json_encode($fields),
		'timeout' => 30,
		'sslverify' => true,
		'redirection' => 0,
	));
	if (is_wp_error($response)) {
		throw new RuntimeException('T-Bank connection failed.');
	}
	$data = json_decode(wp_remote_retrieve_body($response), true);
	if (wp_remote_retrieve_response_code($response) !== 200 || !is_array($data) || ($data['Success'] ?? false) !== true) {
		throw new RuntimeException('T-Bank did not accept the payment request.');
	}
	return $data;
}

function gelikon_tbank_order_lock($id) {
	global $wpdb;
	return 'gelikon-tbank-' . substr(hash('sha256', $wpdb->prefix . ':' . $id), 0, 40);
}

function gelikon_tbank_init_payment($order, $gateway) {
	global $wpdb;
	$lock = gelikon_tbank_order_lock($order->get_id());
	if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 10)', $lock)) !== 1) {
		throw new RuntimeException('T-Bank payment is being initialized; retry later.');
	}
	try {
		$data = gelikon_tbank_request('Init', gelikon_tbank_init_fields($order, $gateway), $gateway);
		if (empty($data['PaymentId']) || !is_scalar($data['PaymentId'])) {
			throw new RuntimeException('T-Bank returned no PaymentId.');
		}
		$order->read_meta_data(true);
		$payments = (array) $order->get_meta('_gelikon_tbank_payment_ids');
		$payments[] = (string) $data['PaymentId'];
		$order->update_meta_data('_gelikon_tbank_payment_ids', array_values(array_unique($payments)));
		$order->update_meta_data('_gelikon_tbank_payment_id', (string) $data['PaymentId']);
		$order->save_meta_data();
		return $data;
	} finally {
		$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
	}
}

function gelikon_tbank_payment_error($order, $error) {
	wc_get_logger()->error($error->getMessage(), array('source' => 'gelikon-tbank', 'order_id' => $order ? $order->get_id() : 0));
	wc_add_notice(__('Не удалось создать платёж Т-Банка. Попробуйте ещё раз или выберите другой способ оплаты.', 'gelikon'), 'error');
}

function gelikon_tbank_receipt_page($order_id) {
	$order = wc_get_order($order_id);
	$gateway = gelikon_tbank_gateway();
	if (!$order || !$gateway) {
		return;
	}
	if (gelikon_get_sequential_order_number($order_id) === '' && !gelikon_order_requires_sequence($order_id)) {
		// Preserve the native payment and callback flow for pre-cutover orders.
		$gateway->receipt_page($order_id);
		return;
	}
	if (!$order->needs_payment() || $order->get_payment_method() !== 'tbank') {
		return;
	}
	try {
		gelikon_assign_order_number($order->get_id(), $order);
		$data = gelikon_tbank_init_payment($order, $gateway);
		$url = $data['PaymentURL'] ?? '';
		if (!is_string($url) || wp_parse_url($url, PHP_URL_SCHEME) !== 'https') {
			throw new RuntimeException('T-Bank returned no HTTPS payment URL.');
		}
		// Preserve 3.0.7's stock setting semantics.
		if ($gateway->get_option('reduce_stock_levels') !== 'yes') {
			wc_reduce_stock_levels($order_id);
		}
		setcookie('tbankReturnUrl', $gateway->get_return_url($order), time() + 3600, '/');
		wp_redirect($url); // Bank URL returned by the authenticated Init request.
	} catch (Throwable $error) {
		gelikon_tbank_payment_error($order, $error);
	}
}

function gelikon_tbank_subscription_payment($amount, $order) {
	$gateway = gelikon_tbank_gateway();
	if (gelikon_get_sequential_order_number($order->get_id()) === '' && !gelikon_order_requires_sequence($order->get_id())) {
		$gateway->scheduled_subscription_payment($amount, $order);
		return;
	}
	if ((float) $amount === 0.0) {
		$order->payment_complete();
		return;
	}
	try {
		global $wpdb;
		$subscriptions = wcs_get_subscriptions_for_order($order->get_id(), array('order_type' => 'any'));
		$subscription = $subscriptions ? array_pop($subscriptions) : null;
		$rebill = $subscription ? $wpdb->get_var($wpdb->prepare("SELECT rebillId FROM {$wpdb->prefix}recurrent_tbank WHERE paymentId = %d ORDER BY id DESC LIMIT 1", $subscription->get_parent_id())) : '';
		if (!$rebill) {
			throw new RuntimeException('T-Bank renewal has no RebillId.');
		}
		gelikon_assign_order_number($order->get_id(), $order);
		$data = gelikon_tbank_init_payment($order, $gateway);
		gelikon_tbank_request('Charge', array('PaymentId' => $data['PaymentId'], 'RebillId' => $rebill), $gateway);
	} catch (Throwable $error) {
		gelikon_tbank_payment_error($order, $error);
	}
}

/** Validate signature before resolving the number or changing an order. */
function gelikon_tbank_process_notification($request, $gateway) {
	global $wpdb;
	if (!is_array($request) || !$gateway || $gateway->get_option('secret_key') === '' ||
		!isset($request['Token'], $request['TerminalKey']) || !is_string($request['Token']) || !is_string($request['TerminalKey']) ||
		(string) $request['TerminalKey'] !== (string) $gateway->get_option('merchant_id') ||
		!hash_equals(gelikon_tbank_token($request, $gateway->get_option('secret_key')), $request['Token'])) {
		return new WP_Error('invalid_signature', 'Invalid T-Bank signature', array('status' => 403));
	}
	if (!isset($request['OrderId'], $request['Amount'], $request['PaymentId'], $request['Status'], $request['Success']) ||
		!is_scalar($request['OrderId']) || !preg_match('/^[1-9][0-9]*$/D', (string) $request['OrderId']) ||
		!is_scalar($request['Amount']) || !preg_match('/^[0-9]+$/D', (string) $request['Amount']) ||
		!is_scalar($request['PaymentId']) || (string) $request['PaymentId'] === '' || !is_string($request['Status'])) {
		return new WP_Error('invalid_payload', 'Invalid T-Bank payload', array('status' => 400));
	}
	$id = (int) $wpdb->get_var($wpdb->prepare('SELECT order_id FROM ' . gelikon_order_numbers_table() . ' WHERE order_number = %s AND order_id <> 0', (string) $request['OrderId']));
	$lock = gelikon_tbank_order_lock($id);
	if (!$id || (int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 10)', $lock)) !== 1) {
		return new WP_Error('order_unavailable', 'Order unavailable', array('status' => 409));
	}
	try {
		$order = wc_get_order($id);
		if (!$order || $order->get_payment_method() !== 'tbank' || (int) round((float) $order->get_total() * 100) !== (int) $request['Amount']) {
			return new WP_Error('order_mismatch', 'Order or amount mismatch', array('status' => 400));
		}
		$order->read_meta_data(true);
		$payments = (array) $order->get_meta('_gelikon_tbank_payment_ids');
		if (!in_array((string) $request['PaymentId'], $payments, true)) {
			return new WP_Error('payment_mismatch', 'PaymentId mismatch', array('status' => 409));
		}
		$status = $request['Status'];
		if (in_array($status, array('AUTHORIZED', 'CONFIRMED', 'REFUNDED'), true) && $request['Success'] !== true) {
			return new WP_Error('unsuccessful_payment', 'Payment was not successful', array('status' => 400));
		}
		if ($status === 'REFUNDED') {
			if (!$order->has_status('refunded')) {
				$order->update_status('refunded', __('Платёж возвращён Т-Банком.', 'gelikon'));
			}
		} elseif (!$order->is_paid() && !$order->has_status('refunded')) {
			switch ($status) {
				case 'CONFIRMED':
					$order->payment_complete((string) $request['PaymentId']);
					break;
				case 'AUTHORIZED':
					if (!$order->has_status('on-hold')) {
						$order->update_status('on-hold');
					}
					break;
				case 'REJECTED':
					if (!$order->has_status('failed')) {
						$order->update_status('failed');
					}
					break;
				case 'CANCELED':
				case 'REVERSED':
					if (!$order->has_status('cancelled')) {
						$order->update_status('cancelled');
					}
					break;
			}
		}
		if ($status === 'CONFIRMED' && !empty($request['RebillId']) && is_scalar($request['RebillId']) && function_exists('wcs_get_subscriptions_for_order')) {
			foreach (wcs_get_subscriptions_for_order($id, array('order_type' => 'any')) as $subscription) {
				if ((int) $subscription->get_parent_id() === $id && !$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}recurrent_tbank WHERE paymentId = %d AND rebillId = %s", $id, (string) $request['RebillId']))) {
					$wpdb->insert($wpdb->prefix . 'recurrent_tbank', array('paymentId' => $id, 'rebillId' => (string) $request['RebillId']), array('%d', '%s'));
				}
			}
		}
		return true;
	} finally {
		$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
	}
}

function gelikon_tbank_notification() {
	$request = json_decode(file_get_contents('php://input'), true);
	try {
		$result = gelikon_tbank_process_notification($request, gelikon_tbank_gateway());
	} catch (Throwable $error) {
		$result = new WP_Error('notification_failed', 'Notification failed', array('status' => 503));
	}
	status_header(is_wp_error($result) ? $result->get_error_data()['status'] : 200);
	header('Content-Type: text/plain; charset=utf-8');
	echo is_wp_error($result) ? 'ERROR' : 'OK';
	exit;
}
add_action('woocommerce_api_gelikon_tbank_notification', 'gelikon_tbank_notification');
