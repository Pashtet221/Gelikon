<?php
/**
 * Adds a theme-owned external order number and notification endpoint to T-Bank.
 *
 * WooCommerce order IDs remain unchanged. This adapter only changes OrderId in
 * the request sent to T-Bank and reverses that mapping in its own callback.
 */

if (!defined('ABSPATH')) {
	exit;
}

define('GELIKON_TBANK_ORDER_PREFIX', 'GL-');

/**
 * Build the deterministic order number used outside WooCommerce.
 *
 * @param int|string $order_id WooCommerce order ID.
 * @return string|false
 */
function gelikon_tbank_get_external_order_id($order_id) {
	$order_id = (string) $order_id;

	if (!preg_match('/^[0-9]+$/', $order_id) || (int) $order_id < 1) {
		return false;
	}

	return GELIKON_TBANK_ORDER_PREFIX . $order_id;
}

/**
 * Convert a strictly formatted external number back to a WooCommerce ID.
 *
 * @param mixed $external_order_id External T-Bank OrderId.
 * @return int|false
 */
function gelikon_tbank_get_wc_order_id($external_order_id) {
	if (!is_string($external_order_id)) {
		return false;
	}

	$pattern = '/^' . preg_quote(GELIKON_TBANK_ORDER_PREFIX, '/') . '([0-9]+)$/';

	if (!preg_match($pattern, $external_order_id, $matches)) {
		return false;
	}

	$order_id = (int) $matches[1];

	return $order_id > 0 ? $order_id : false;
}

/**
 * Write a structured entry to the WooCommerce log when logging is available.
 *
 * @param string $level   WC logger level.
 * @param string $message Log message without secrets or customer data.
 * @param array  $context Additional non-sensitive context.
 */
function gelikon_tbank_log($level, $message, $context = array()) {
	if (!function_exists('wc_get_logger')) {
		return;
	}

	$context['source'] = 'gelikon-tbank';
	$logger            = wc_get_logger();

	if (is_object($logger) && is_callable(array($logger, $level))) {
		$logger->{$level}($message, $context);
	}
}

/**
 * Return the existing T-Bank gateway instance. Never constructs a second one.
 *
 * @return object|false
 */
function gelikon_tbank_get_gateway() {
	if (!function_exists('WC') || !WC() || !WC()->payment_gateways()) {
		return false;
	}

	$gateways = WC()->payment_gateways()->payment_gateways();

	return isset($gateways['tbank']) ? $gateways['tbank'] : false;
}

/** Read a gateway option while retaining compatibility with its legacy key spelling. */
function gelikon_tbank_get_option($gateway, $key, $legacy_key = '') {
	$value = $gateway->get_option($key);
	return ($value === '' && $legacy_key !== '') ? $gateway->get_option($legacy_key) : $value;
}

/**
 * Replace the callback registered by WC_TBank after gateways are instantiated.
 */
function gelikon_tbank_replace_receipt_handler() {
	if (!function_exists('wc_get_order') || !class_exists('WC_TBank') ||
		!class_exists('SupportPaymentTBank') || !class_exists('TBankMerchantAPI')) {
		gelikon_tbank_log('warning', 'T-Bank adapter was not enabled because a required class is unavailable.');
		return;
	}

	$gateway = gelikon_tbank_get_gateway();

	if (!$gateway || !is_a($gateway, 'WC_TBank') || !is_callable(array($gateway, 'receipt_page'))) {
		gelikon_tbank_log('warning', 'T-Bank adapter could not find the active gateway instance.');
		return;
	}

	remove_action('woocommerce_receipt_tbank', array($gateway, 'receipt_page'));

	if (!has_action('woocommerce_receipt_tbank', 'gelikon_tbank_receipt_page')) {
		add_action('woocommerce_receipt_tbank', 'gelikon_tbank_receipt_page');
	}
}
add_action('woocommerce_init', 'gelikon_tbank_replace_receipt_handler', 100);

/**
 * Obtain a response value from either response shape used by plugin releases.
 *
 * @param array|object $response T-Bank API response.
 * @param string       $key      Response field.
 * @return mixed|null
 */
function gelikon_tbank_normalize_response($response) {
	if (is_string($response)) {
		$decoded = json_decode($response, true);
		return is_array($decoded) ? $decoded : false;
	}

	if (is_object($response)) {
		return get_object_vars($response);
	}

	return is_array($response) ? $response : false;
}

function gelikon_tbank_response_value($response, $key) {
	$response = gelikon_tbank_normalize_response($response);

	if (is_array($response) && array_key_exists($key, $response)) {
		return $response[$key];
	}

	return null;
}

/**
 * Theme receipt handler based on WC_TBank 3.0.7's Init flow.
 *
 * @param int $order_id Numeric WooCommerce order ID.
 */
function gelikon_tbank_receipt_page($order_id) {
	if (!function_exists('wc_get_order') || !class_exists('SupportPaymentTBank') || !class_exists('TBankMerchantAPI')) {
		gelikon_tbank_log('error', 'T-Bank Init is unavailable because a required dependency is missing.');
		wc_add_notice(__('Не удалось инициализировать оплату. Попробуйте другой способ оплаты.', 'gelikon'), 'error');
		return;
	}

	$order_id = absint($order_id);
	$order    = $order_id ? wc_get_order($order_id) : false;
	$gateway  = gelikon_tbank_get_gateway();

	if (!$order instanceof WC_Order || !$gateway) {
		gelikon_tbank_log('error', 'T-Bank Init rejected an unknown order or unavailable gateway.', array('wc_order_id' => $order_id));
		wc_add_notice(__('Заказ для оплаты не найден.', 'gelikon'), 'error');
		return;
	}

	$settings = array(
		'email_company'      => $gateway->get_option('email_company'),
		'payment_method_ffd' => $gateway->get_option('payment_method_ffd'),
		'payment_object_ffd' => $gateway->get_option('payment_object_ffd'),
		'check_data_tax'     => $gateway->get_option('check_data_tax'),
		'taxation'           => $gateway->get_option('taxation'),
		'payment_form_language' => $gateway->get_option('payment_form_language'),
		'ffd'                => $gateway->get_option('ffd'),
	);

	new SupportPaymentTBank($settings);
	$fields = SupportPaymentTBank::send_data($order, $order_id);

	if (!is_array($fields)) {
		gelikon_tbank_log('error', 'T-Bank request builder returned invalid Init fields.', array('wc_order_id' => $order_id));
		wc_add_notice(__('Не удалось подготовить данные для оплаты.', 'gelikon'), 'error');
		return;
	}

	$external_order_id = gelikon_tbank_get_external_order_id($order_id);
	$fields['OrderId'] = $external_order_id;
	$fields['NotificationURL'] = WC()->api_request_url('tbank_gl_callback');

	// The plugin adds its configured language to the request in this method.
	$language_fields = SupportPaymentTBank::get_setting_language($fields);
	if (is_array($language_fields)) {
		$fields = $language_fields;
	}

	gelikon_tbank_log('info', 'Creating T-Bank Init.', array(
		'wc_order_id'            => $order_id,
		'external_order_id'      => $external_order_id,
		'notification_endpoint'  => 'tbank_gl_callback',
	));

	// WC_TBank 3.0.7 calls the API with these two actual option names.
	$api = new TBankMerchantAPI(
		gelikon_tbank_get_option($gateway, 'terminalKey', 'terminal_key'),
		$gateway->get_option('password')
	);

	try {
		$response = $api->buildQuery('Init', $fields);
		$parsed   = gelikon_tbank_normalize_response($response);
		gelikon_tbank_log('debug', 'T-Bank Init response received.', array(
			'wc_order_id'       => $order_id,
			'external_order_id' => $external_order_id,
			'response_type'     => gettype($response),
			'response_length'   => is_string($response) ? strlen($response) : null,
			'response_fields'   => is_array($parsed) ? array_keys($parsed) : array(),
			'has_success'       => is_array($parsed) && array_key_exists('Success', $parsed),
			'has_payment_url'   => is_array($parsed) && array_key_exists('PaymentURL', $parsed),
			'has_payment_id'    => is_array($parsed) && array_key_exists('PaymentId', $parsed),
			'has_error_code'    => is_array($parsed) && array_key_exists('ErrorCode', $parsed),
			'has_message'       => is_array($parsed) && array_key_exists('Message', $parsed),
			'has_details'       => is_array($parsed) && array_key_exists('Details', $parsed),
		));
	} catch (Throwable $exception) {
		gelikon_tbank_log('error', 'T-Bank Init raised an exception.', array(
			'wc_order_id'       => $order_id,
			'external_order_id' => $external_order_id,
			'exception_class'   => get_class($exception),
			'exception_message' => $exception->getMessage(),
		));
		wc_add_notice(__('Т-Банк не смог создать платёж. Попробуйте ещё раз или выберите другой способ оплаты.', 'gelikon'), 'error');
		return;
	}
	$success  = gelikon_tbank_response_value($response, 'Success');
	$url      = gelikon_tbank_response_value($response, 'PaymentURL');
	$payment_id = gelikon_tbank_response_value($response, 'PaymentId');

	if (($success === true || $success === 'true' || $success === '1' || $success === 1) && is_string($url) && $url !== '') {
		$order->update_meta_data('_tbank_external_order_id', $external_order_id);
		if (is_scalar($payment_id) && (string) $payment_id !== '') {
			$order->update_meta_data('_tbank_payment_id', sanitize_text_field((string) $payment_id));
		}
		$order->save();
		setcookie('paymentId', (string) $payment_id, 0, '/');
		setcookie('returnUrl', $gateway->get_return_url($order), 0, '/');

		if ('yes' === $gateway->get_option('reduce_stock_levels')) {
			wc_reduce_stock_levels($order_id);
		}

		gelikon_tbank_log('info', 'T-Bank Init succeeded.', array(
			'wc_order_id'       => $order_id,
			'external_order_id' => $external_order_id,
			'payment_id'        => is_scalar($payment_id) ? (string) $payment_id : '',
		));
		wp_redirect($url); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- matches WC_TBank 3.0.7 and redirects to its API response.
		exit;
	}

	gelikon_tbank_log('error', 'T-Bank Init failed.', array(
		'wc_order_id'       => $order_id,
		'external_order_id' => $external_order_id,
		'error_code'        => (string) gelikon_tbank_response_value($response, 'ErrorCode'),
		'message'           => (string) gelikon_tbank_response_value($response, 'Message'),
	));
	wc_add_notice(__('Т-Банк не смог создать платёж. Попробуйте ещё раз или выберите другой способ оплаты.', 'gelikon'), 'error');
}

/**
 * Calculate a callback token according to T-Bank's top-level field algorithm.
 * Nested DATA/Receipt values do not participate in token generation.
 *
 * @param array  $request Callback payload.
 * @param string $secret  Gateway password.
 * @return string
 */
function gelikon_tbank_calculate_notification_token($request, $secret) {
	unset($request['Token']);
	$request['Password'] = (string) $secret;

	$request = array_filter($request, 'is_scalar');
	ksort($request);

	return hash('sha256', implode('', array_map('strval', array_values($request))));
}

/** Check a notification amount using the same integer minor units as T-Bank. */
function gelikon_tbank_notification_amount_matches($order_total, $amount) {
	if (filter_var($amount, FILTER_VALIDATE_INT) === false) {
		return false;
	}

	return (int) round((float) $order_total * 100) === (int) $amount;
}

/** Apply a verified T-Bank status. Kept separate so the idempotency is testable. */
function gelikon_tbank_apply_status($order, $status, $payment_id) {
	if ($order->has_status(array('processing', 'completed'))) {
		return;
	}

	switch ($status) {
		case 'AUTHORIZED':
			if (!$order->has_status('on-hold')) {
				$order->update_status('on-hold', __('Платёж авторизован Т-Банком.', 'gelikon'));
			}
			break;
		case 'CONFIRMED':
			$order->payment_complete($payment_id ?: '');
			break;
		case 'REJECTED':
			if (!$order->has_status('failed')) {
				$order->update_status('failed', __('Платёж отклонён Т-Банком.', 'gelikon'));
			}
			break;
		case 'CANCELED':
		case 'REVERSED':
			if (!$order->has_status('cancelled')) {
				$order->update_status('cancelled', __('Платёж отменён Т-Банком.', 'gelikon'));
			}
			break;
		case 'REFUNDED':
			if (!$order->has_status('refunded')) {
				$order->update_status('refunded', __('Платёж возвращён Т-Банком.', 'gelikon'));
			}
			break;
	}
}

/**
 * Send a plain-text callback response and stop WordPress rendering.
 *
 * @param int    $status  HTTP status.
 * @param string $message Plain response body.
 */
function gelikon_tbank_callback_response($status, $message) {
	status_header($status);
	header('Content-Type: text/plain; charset=utf-8');
	echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed protocol response strings only.
	exit;
}

/**
 * Validate and process GL-* T-Bank notifications.
 */
function gelikon_tbank_callback() {
	if (!function_exists('wc_get_order')) {
		gelikon_tbank_callback_response(503, 'ERROR');
	}

	$raw     = file_get_contents('php://input');
	$request = json_decode($raw, true);
	$gateway = gelikon_tbank_get_gateway();

	if (!is_array($request) || !$gateway) {
		gelikon_tbank_log('error', 'T-Bank callback contained invalid JSON or the gateway was unavailable.');
		gelikon_tbank_callback_response(400, 'ERROR');
	}

	$external_order_id = isset($request['OrderId']) && is_scalar($request['OrderId']) ? (string) $request['OrderId'] : '';
	$status            = isset($request['Status']) && is_scalar($request['Status']) ? strtoupper((string) $request['Status']) : '';
	$payment_id        = isset($request['PaymentId']) && is_scalar($request['PaymentId']) ? sanitize_text_field((string) $request['PaymentId']) : '';

	gelikon_tbank_log('info', 'T-Bank callback received.', array(
		'external_order_id' => $external_order_id,
		'status'            => $status,
		'payment_id'        => $payment_id,
	));

	$provided_token = isset($request['Token']) && is_string($request['Token']) ? strtolower($request['Token']) : '';
	$expected_token = gelikon_tbank_calculate_notification_token($request, $gateway->get_option('password'));

	if (strlen($provided_token) !== 64 || !hash_equals($expected_token, $provided_token)) {
		gelikon_tbank_log('warning', 'T-Bank callback Token validation failed.', array('external_order_id' => $external_order_id));
		gelikon_tbank_callback_response(403, 'ERROR');
	}

	$order_id = gelikon_tbank_get_wc_order_id($external_order_id);
	if (!$order_id) {
		gelikon_tbank_log('warning', 'T-Bank callback used an unknown OrderId format.', array('external_order_id' => $external_order_id));
		gelikon_tbank_callback_response(400, 'ERROR');
	}

	$order = wc_get_order($order_id);
	if (!$order instanceof WC_Order) {
		gelikon_tbank_log('warning', 'T-Bank callback referenced an unknown WooCommerce order.', array(
			'external_order_id' => $external_order_id,
			'wc_order_id'       => $order_id,
		));
		gelikon_tbank_callback_response(404, 'ERROR');
	}

	$stored_payment_id = (string) $order->get_meta('_tbank_payment_id', true);
	if ($stored_payment_id !== '' && ($payment_id === '' || !hash_equals($stored_payment_id, $payment_id))) {
		gelikon_tbank_log('warning', 'T-Bank callback PaymentId did not match the initialized payment.', array('wc_order_id' => $order_id));
		gelikon_tbank_callback_response(400, 'ERROR');
	}

	if (!isset($request['Amount']) || filter_var($request['Amount'], FILTER_VALIDATE_INT) === false) {
		gelikon_tbank_log('warning', 'T-Bank callback omitted a valid integer Amount.', array('wc_order_id' => $order_id));
		gelikon_tbank_callback_response(400, 'ERROR');
	}

	$expected_amount = (int) round((float) $order->get_total() * 100);
	$received_amount = (int) $request['Amount'];
	if (!gelikon_tbank_notification_amount_matches($order->get_total(), $request['Amount'])) {
		gelikon_tbank_log('warning', 'T-Bank callback Amount did not match the order.', array(
			'wc_order_id'     => $order_id,
			'expected_amount' => $expected_amount,
			'received_amount' => $received_amount,
		));
		gelikon_tbank_callback_response(400, 'ERROR');
	}

	gelikon_tbank_log('info', 'T-Bank callback matched a WooCommerce order.', array(
		'external_order_id' => $external_order_id,
		'wc_order_id'       => $order_id,
		'status'            => $status,
		'payment_id'        => $payment_id,
	));

	gelikon_tbank_apply_status($order, $status, $payment_id);

	gelikon_tbank_callback_response(200, 'OK');
}
add_action('woocommerce_api_tbank_gl_callback', 'gelikon_tbank_callback');
