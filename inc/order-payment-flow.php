<?php
/** Run receipt gateways before the theme renders the order-pay page. */
defined('ABSPATH') || exit;

function gelikon_prepare_tbank_order_payment() {
	if (!function_exists('is_checkout_pay_page') || !is_checkout_pay_page() || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET' || isset($_GET['pay_for_order'])) {
		return;
	}
	// Match WooCommerce's receipt branch: valid internal ID/key and payment due.
	$id = absint(get_query_var('order-pay'));
	$key = isset($_GET['key']) && is_string($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';
	$order = $id ? wc_get_order($id) : false;
	if (!$order || !$key || !hash_equals($order->get_order_key(), $key) || !$order->needs_payment() || $order->get_payment_method() !== 'tbank') {
		return;
	}
	$hook = 'woocommerce_receipt_tbank';
	if (!has_action($hook)) {
		$gateways = WC()->payment_gateways()->payment_gateways();
		$gateway = $gateways['tbank'] ?? null;
		if (!$gateway || !is_callable(array($gateway, 'receipt_page'))) {
			wc_get_logger()->error('T-Bank receipt handler is unavailable.', array('source'=>'gelikon-payment-flow', 'order_id'=>$id));
			wc_add_notice('Способ онлайн-оплаты временно недоступен. Обратитесь в магазин.', 'error');
			return;
		}
		add_action($hook, array($gateway, 'receipt_page'));
	}
	$payment_url = '';
	$capture_redirect = function ($url) use (&$payment_url) {
		if (is_string($url) && wp_parse_url($url, PHP_URL_SCHEME) === 'https') {
			$host = strtolower((string)wp_parse_url($url, PHP_URL_HOST));
			if ($host === 'tinkoff.ru' || $host === 'tbank.ru' || substr($host, -11) === '.tinkoff.ru' || substr($host, -9) === '.tbank.ru') {
				$payment_url = $url;
			}
		}
		return $url;
	};
	add_filter('wp_redirect', $capture_redirect, PHP_INT_MAX);
	ob_start();
	try {
		do_action($hook, $id);
	} catch (Throwable $error) {
		// Do not expose gateway exceptions, which may contain credentials/receipt data.
		wc_get_logger()->error('T-Bank receipt handler failed.', array('source'=>'gelikon-payment-flow', 'order_id'=>$id, 'exception_type'=>get_class($error)));
		wc_add_notice('Не удалось открыть страницу оплаты. Попробуйте позже или обратитесь в магазин.', 'error');
	} finally {
		$output = ob_get_clean();
		remove_filter('wp_redirect', $capture_redirect, PHP_INT_MAX);
	}
	if ($payment_url !== '') {
		// The plugin has already set Location. End before rendering the checkout.
		if (headers_sent()) {
			wc_get_logger()->warning('Payment redirect requires browser navigation because headers were already sent.', array('source'=>'gelikon-payment-flow', 'order_id'=>$id));
			echo '<p><a href="' . esc_url($payment_url) . '">Перейти к оплате</a></p>';
			echo '<script>window.location.replace(' . wp_json_encode($payment_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');</script>';
		}
		exit;
	}
	// The normal receipt template may run later. Replay output, never Init twice.
	remove_all_actions($hook);
	add_action($hook, function () use ($output) { echo $output; }); // Trusted output from the gateway.
	if (!wc_notice_count('error')) {
		wc_add_notice('Платёжный сервис не вернул ссылку на оплату. Попробуйте позже или обратитесь в магазин.', 'error');
	}
	wc_get_logger()->warning('T-Bank receipt completed without a payment redirect.', array('source'=>'gelikon-payment-flow', 'order_id'=>$id));
}
add_action('template_redirect', 'gelikon_prepare_tbank_order_payment', 35);
