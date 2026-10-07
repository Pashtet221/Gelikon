<?php
/** Run with WP-CLI against an ISOLATED gelikon_test_* prefix, never production. */
if (!defined('WP_CLI') || !WP_CLI || strpos($GLOBALS['wpdb']->prefix, 'gelikon_test_') !== 0 || wp_get_environment_type() !== 'local') {
	throw new RuntimeException('This integration test requires an isolated local gelikon_test_* database prefix.');
}
function gelikon_test_assert($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}
function gelikon_test_order() {
	$order = new WC_Order();
	$order->set_status('pending');
	$order->set_payment_method('tbank');
	$order->set_customer_id(1);
	$order->set_billing_email('test@example.test');
	$item = new WC_Order_Item_Fee();
	$item->set_name('Integration fixture');
	$item->set_amount(100);
	$item->set_total(100);
	$order->add_item($item);
	$order->calculate_totals();
	$order->save();
	return $order;
}
$wpdb = $GLOBALS['wpdb'];
$table = gelikon_order_numbers_table();
gelikon_test_assert((int) $wpdb->get_var("SELECT next_number FROM $table WHERE order_id = 0") === 10000, 'Use a fresh test prefix for each run');
$legacy_id = (int) get_option('gelikon_test_legacy_order');
$legacy = wc_get_order($legacy_id);
gelikon_test_assert($legacy && $legacy->get_order_number() === 'GL-' . $legacy_id, 'legacy number unchanged');
$legacy_draft = wc_get_order((int)get_option('gelikon_test_legacy_draft'));
$legacy_draft->set_status('pending');
$legacy_draft->save();
gelikon_test_assert($legacy_draft->get_order_number() === 'GL-' . $legacy_draft->get_id(), 'pre-cutover draft retains its legacy number after submission');
$first = gelikon_test_order();
$second = gelikon_test_order();
gelikon_test_assert($first->get_order_number() === '10000', 'first number = 10000');
gelikon_test_assert($second->get_order_number() === '10001', 'second number = 10001');
gelikon_test_assert($first->get_id() !== 10000 && $second->get_id() !== 10001, 'internal IDs unchanged');
gelikon_assign_order_number($first->get_id(), $first);
gelikon_test_assert((int) $wpdb->get_var("SELECT next_number FROM $table WHERE order_id = 0") === 10002, 'repeat assignment does not consume a number');
$legacy->update_status('on-hold');
gelikon_assign_order_number($legacy_id, $legacy);
gelikon_test_assert(wc_get_order($legacy_id)->get_order_number() === 'GL-' . $legacy_id, 'editing legacy order preserves number');

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
$admin_table = new Automattic\WooCommerce\Internal\Admin\Orders\ListTable();
foreach (array($first, $second) as $order) {
	// The trash branch renders the same real number column without PageController dependencies.
	$status = $order->get_status();
	$order->set_status('trash');
	ob_start();
	$admin_table->render_order_number_column($order);
	$html = ob_get_clean();
	$order->set_status($status);
	gelikon_test_assert(strpos($html, '#' . $order->get_order_number()) !== false, 'admin number column');
}
$email = WC()->mailer()->get_emails()['WC_Email_Customer_Processing_Order'];
$email->object = $first;
$html = $email->get_content_html();
gelikon_test_assert(strpos($html, '10000') !== false && strpos($html, 'GL-' . $first->get_id()) === false, 'email number');
ob_start();
wc_get_template('checkout/thankyou.php', array('order' => $first));
$html = ob_get_clean();
gelikon_test_assert(preg_match('/<strong>\s*10000\s*<\/strong>/', $html) === 1, 'order thank-you number');
ob_start();
wc_get_template('myaccount/orders.php', array('has_orders' => true, 'customer_orders' => (object) array('orders'=>array($first, $second), 'total_pages'=>1), 'current_page'=>1));
$html = ob_get_clean();
gelikon_test_assert(strpos($html, '10000') !== false && strpos($html, '10001') !== false, 'account order numbers');

$gateway = gelikon_tbank_gateway();
$gateway->settings['merchant_id'] = 'gelikon-test-terminal';
$gateway->settings['secret_key'] = 'gelikon-test-secret';
$gateway->settings['check_data_tax'] = 'no';
$captured = array();
$redirects = array();
add_filter('wp_redirect', function ($location) use (&$redirects) { $redirects[] = $location; return false; });
$gateway->settings['reduce_stock_levels'] = 'yes';
add_filter('pre_http_request', function ($pre, $args, $url) use (&$captured) {
	// Capture the fully signed serialized request at the actual HTTP boundary.
	if (strpos($url, 'https://securepay.tinkoff.ru/v2/') !== 0) {
		return new WP_Error('blocked_external_test', 'No external network in payment test');
	}
	$captured[] = array('url'=>$url, 'args'=>$args, 'fields'=>json_decode($args['body'], true));
	return array('response'=>array('code'=>200), 'headers'=>array(), 'body'=>json_encode(array('Success'=>true, 'PaymentId'=>(string)(7000 + count($captured)), 'PaymentURL'=>'https://securepay.tinkoff.ru/test-payment')));
}, 10, 3);
foreach (array($first, $second) as $order) {
	do_action('woocommerce_receipt_tbank', $order->get_id());
	$wire = end($captured);
	gelikon_test_assert($wire['fields']['OrderId'] === $order->get_order_number(), 'actual serialized Init OrderId equals display number');
	gelikon_test_assert($wire['args']['sslverify'] === true, 'TLS validation retained');
	gelikon_test_assert(strpos($wire['fields']['NotificationURL'], 'gelikon_tbank_notification') !== false, 'mapped callback endpoint');
	// Compare outbound signature with the UNMODIFIED plugin implementation.
	$api = new TBankMerchantAPI('gelikon-test-terminal', 'gelikon-test-secret');
	$method = new ReflectionMethod($api, '_genToken');
	$method->setAccessible(true);
	$fields = $wire['fields'];
	unset($fields['Token']);
	gelikon_test_assert($wire['fields']['Token'] === $method->invoke($api, $fields), 'signature matches plugin for final OrderId');
}
gelikon_test_assert($captured[0]['fields']['OrderId'] === '10000' && $captured[1]['fields']['OrderId'] === '10001', 'both actual HTTP payload numbers');
gelikon_test_assert(count($redirects) === 2, 'both real receipt hooks redirect to payment');
do_action('woocommerce_receipt_tbank', $first->get_id());
gelikon_test_assert(end($captured)['fields']['OrderId'] === '10000', 'payment retry retains external number');
$paid_count = 0;
add_action('woocommerce_payment_complete', function () use (&$paid_count) { ++$paid_count; });
$request = array('TerminalKey'=>'gelikon-test-terminal', 'OrderId'=>'10000', 'Amount'=>10000, 'PaymentId'=>'7001', 'Status'=>'CONFIRMED', 'Success'=>true);
$request['Token'] = gelikon_tbank_token($request, 'gelikon-test-secret');
gelikon_test_assert(gelikon_tbank_process_notification($request, $gateway) === true, 'valid bank notification');
gelikon_test_assert(wc_get_order($first->get_id())->is_paid(), 'mapped internal order paid');
gelikon_test_assert(wc_get_order($second->get_id())->has_status('pending'), 'unrelated order unchanged');
gelikon_test_assert(gelikon_tbank_process_notification($request, $gateway) === true && $paid_count === 1, 'duplicate callback is idempotent');
$bad = $request;
$bad['OrderId'] = '10001';
gelikon_test_assert(is_wp_error(gelikon_tbank_process_notification($bad, $gateway)), 'tampered signature rejected');
$bad = $request;
$bad['Amount'] = 1;
$bad['Token'] = gelikon_tbank_token($bad, 'gelikon-test-secret');
gelikon_test_assert(is_wp_error(gelikon_tbank_process_notification($bad, $gateway)), 'signed wrong amount rejected');
$bad = $request;
$bad['PaymentId'] = 'wrong';
$bad['Token'] = gelikon_tbank_token($bad, 'gelikon-test-secret');
gelikon_test_assert(is_wp_error(gelikon_tbank_process_notification($bad, $gateway)), 'wrong payment ID rejected');
$request['OrderId'] = '10001';
$request['PaymentId'] = '7002';
$request['Token'] = gelikon_tbank_token($request, 'gelikon-test-secret');
gelikon_test_assert(gelikon_tbank_process_notification($request, $gateway) === true && wc_get_order($second->get_id())->is_paid(), '10001 notification maps correctly');

// Exercise an actual database insert failure: both mapping and counter roll back.
$trigger = $wpdb->prefix . 'reject_number';
$wpdb->query("CREATE TRIGGER $trigger BEFORE INSERT ON $table FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Injected numbering failure'");
$wpdb->suppress_errors(true);
$failed = false;
try {
	$failed_order = gelikon_test_order();
	$failed = gelikon_get_sequential_order_number($failed_order->get_id()) === '';
} catch (Throwable $error) {
	$failed = true;
}
$wpdb->suppress_errors(false);
$wpdb->query("DROP TRIGGER $trigger");
gelikon_test_assert($failed && (int) $wpdb->get_var("SELECT next_number FROM $table WHERE order_id = 0") === 10002, 'failed allocation rolls back without a gap');
$third = gelikon_test_order();
gelikon_test_assert($third->get_order_number() === '10002', 'next successful allocation after failure');
$draft = new WC_Order();
$draft->set_status('checkout-draft');
$draft->save();
gelikon_test_assert(gelikon_get_sequential_order_number($draft->get_id()) === '', 'checkout draft does not consume a number');
$draft->set_status('pending');
$draft->save();
gelikon_test_assert($draft->get_order_number() === '10003', 'draft becomes numbered on submission');
$draft_id = $draft->get_id();
$draft->delete(true);
gelikon_test_assert((string)$wpdb->get_var($wpdb->prepare("SELECT order_number FROM $table WHERE order_id = %d", $draft_id)) === '10003', 'deleted order number remains reserved');
update_option('gelikon_test_parallel_order', $third->get_id(), false);
echo "PASS: 10000/10001; unchanged legacy IDs/numbers; admin, email, account, thank-you; signed HTTP Init bodies; valid/tampered/duplicate callbacks; transaction rollback; drafts.\n";
