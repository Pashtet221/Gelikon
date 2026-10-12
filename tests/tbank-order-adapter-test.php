<?php
/** Standalone tests; integration coverage is in order-numbers-integration.php. */
define('ABSPATH', __DIR__);
$registered_filters = array();
$registered_actions = array();
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
	global $registered_filters;
	$registered_filters[] = array($hook, $callback, $priority, $accepted_args);
}
function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
	global $registered_actions;
	$registered_actions[] = array($hook, $callback, $priority, $accepted_args);
}
require dirname(__DIR__) . '/inc/tbank-order-adapter.php';
function check($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}
check(gelikon_format_order_number(1700) === 'GL-1700', 'legacy order number preserved');
check(gelikon_format_order_number('GL-1700') === 'GL-1700', 'prefix is not duplicated');
check(gelikon_format_order_number('') === '', 'empty order number');
check($registered_filters === array(array('woocommerce_order_number', 'gelikon_prefix_woocommerce_order_number', 10, 2)), 'display hook');
$fields = array('TerminalKey'=>'test', 'OrderId'=>'10000', 'Amount'=>10000, 'Success'=>true, 'Status'=>'CONFIRMED', 'DATA'=>array('ignore'=>1));
check(gelikon_tbank_token($fields, 'secret') === hash('sha256', '10000' . '10000' . 'secret' . 'CONFIRMED' . 'true' . 'test'), 'signature scalar sort and boolean encoding');
$fields['Token'] = 'ignored';
check(gelikon_tbank_token($fields, 'secret') === hash('sha256', '10000' . '10000' . 'secret' . 'CONFIRMED' . 'true' . 'test'), 'token field excluded');
// Exercise the actual request boundary with a rejected bank response.
class WP_Error {
	public function get_error_code() { return 'http_request_failed'; }
	public function get_error_message() { return 'cURL error 60: certificate verification failed'; }
}
function home_url() { return 'https://main.example.test'; }
function wp_parse_url($url, $component) { return parse_url($url, $component); }
function wp_json_encode($data) { return json_encode($data); }
function sanitize_text_field($text) { return strip_tags($text); }
function wp_html_excerpt($text, $length, $suffix) { return $text; }
function is_wp_error($response) { return $response instanceof WP_Error; }
function wp_remote_post($url, $args) {
	$GLOBALS['wire_request'] = json_decode($args['body'], true);
	return $GLOBALS['bank_response'];
}
function wp_remote_retrieve_body($response) { return $response['body']; }
function wp_remote_retrieve_response_code($response) { return $response['response']['code']; }
$gateway = new class {
	public function get_option($name) { return $name === 'merchant_id' ? 'test-terminal-key' : 'test-secret-password'; }
};
$GLOBALS['bank_response'] = array('response'=>array('code'=>200), 'body'=>json_encode(array('Success'=>false, 'ErrorCode'=>'8', 'Message'=>'Invalid status', 'Details'=>'OrderId already exists. test-terminal-key test-secret-password', 'Token'=>'not-for-logs', 'Receipt'=>array('Email'=>'private@example.test'))));
try {
	gelikon_tbank_request('Init', array('OrderId'=>'10007', 'Amount'=>10000), $gateway);
	throw new RuntimeException('Rejected Init must fail');
} catch (Gelikon_TBank_Request_Exception $error) {
	check($error->diagnostics['bank_order_id'] === '10007' && $error->diagnostics['bank_ErrorCode'] === '8', 'exact submitted number and bank rejection code');
	check($error->diagnostics['site_host'] === 'main.example.test', 'originating site identified');
	$text = json_encode($error->diagnostics);
	foreach (array('test-terminal-key', 'test-secret-password', 'not-for-logs', 'private@example.test', $GLOBALS['wire_request']['Token']) as $secret) {
		check(strpos($text, $secret) === false, 'request credentials/receipt not logged');
	}
}
$GLOBALS['bank_response'] = new WP_Error();
try {
	gelikon_tbank_request('Init', array('OrderId'=>'10007'), $gateway);
	throw new RuntimeException('Transport failure must fail');
} catch (Gelikon_TBank_Request_Exception $error) {
	check($error->diagnostics['transport_error_code'] === 'http_request_failed' && strpos($error->diagnostics['transport_error_message'], 'cURL error 60') !== false, 'transport failures distinguish TLS problems from bank rejections');
}
$GLOBALS['bank_response'] = array('response'=>array('code'=>502), 'body'=>'<html>Bad gateway</html>');
try {
	gelikon_tbank_request('Init', array('OrderId'=>'10007'), $gateway);
	throw new RuntimeException('Non-JSON failure must fail');
} catch (Gelikon_TBank_Request_Exception $error) {
	check($error->diagnostics['http_status'] === 502 && $error->diagnostics['invalid_json'] === true, 'non-JSON gateway failure');
}
$GLOBALS['bank_response'] = array('response'=>array('code'=>200), 'body'=>json_encode(array('Success'=>true,'PaymentId'=>'123','PaymentURL'=>'https://bank.example.test/pay')));
check(gelikon_tbank_request('Init', array('OrderId'=>'10007'), $gateway)['PaymentId'] === '123', 'successful bank response preserved');
echo "Order number adapter and payment diagnostics tests passed\n";
