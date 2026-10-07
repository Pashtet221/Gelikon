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
echo "Order number adapter tests passed\n";
