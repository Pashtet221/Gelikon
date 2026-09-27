<?php
define('ABSPATH', __DIR__);
function add_action() {}
function __($text) { return $text; }
require dirname(__DIR__) . '/inc/tbank-order-adapter.php';

function check($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

check(gelikon_tbank_get_external_order_id(1700) === 'GL-1700', 'external ID');
check(gelikon_tbank_get_wc_order_id('GL-1700') === 1700, 'internal ID');
foreach (array('1700', 'ABC-1700', 'GL-test', 'GL-1700-test', 'GL-', 'GL-0') as $invalid) {
	check(gelikon_tbank_get_wc_order_id($invalid) === false, 'rejected ' . $invalid);
}

$payload = array('OrderId' => 'GL-1700', 'Amount' => 12345, 'Status' => 'CONFIRMED');
$known   = gelikon_tbank_calculate_notification_token($payload, 'test-password');
check($known === '6fb8008f6587c0b8b5fb06d1f975dbe07f1e1a5e6bb2c67a976cb491aeea1f44', 'known token');
check(hash_equals($known, gelikon_tbank_calculate_notification_token($payload + array('Token' => $known), 'test-password')), 'valid token');
check(!hash_equals(str_repeat('0', 64), $known), 'invalid token');
check(gelikon_tbank_notification_amount_matches('123.45', 12345), 'valid amount');
check(!gelikon_tbank_notification_amount_matches('123.45', 12344), 'invalid amount');

$success = array('Success' => true, 'PaymentURL' => 'https://example.test/pay', 'PaymentId' => '42');
foreach (array($success, (object) $success, json_encode($success)) as $response) {
	check(gelikon_tbank_response_value($response, 'Success') === true, 'response format');
	check(gelikon_tbank_response_value($response, 'PaymentURL') === 'https://example.test/pay', 'payment URL');
}
check(gelikon_tbank_response_value('{"Success":false,"ErrorCode":"7"}', 'Success') === false, 'failed response');
check(gelikon_tbank_normalize_response('not json') === false, 'invalid response');

class TestOrder {
	public $status = 'pending';
	public $payments = array();
	public function has_status($statuses) { return in_array($this->status, (array) $statuses, true); }
	public function update_status($status, $note = '') { $this->status = $status; }
	public function payment_complete($payment_id) { $this->payments[] = $payment_id; $this->status = 'processing'; }
}
$order = new TestOrder();
gelikon_tbank_apply_status($order, 'CONFIRMED', 'payment-42');
gelikon_tbank_apply_status($order, 'CONFIRMED', 'payment-42');
check($order->payments === array('payment-42'), 'PaymentId and repeated CONFIRMED');

echo "T-Bank adapter tests passed\n";
