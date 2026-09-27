<?php
define('ABSPATH', __DIR__);

$registered_filters = array();

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
	global $registered_filters;
	$registered_filters[] = array($hook, $callback, $priority, $accepted_args);
}

require dirname(__DIR__) . '/inc/tbank-order-adapter.php';

function check($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

check(gelikon_format_order_number(1700) === 'GL-1700', 'numeric order number');
check(gelikon_format_order_number('1700') === 'GL-1700', 'string order number');
check(gelikon_format_order_number('GL-1700') === 'GL-1700', 'prefix is not duplicated');
check(gelikon_format_order_number('') === '', 'empty order number');
check(
	$registered_filters === array(array('woocommerce_order_number', 'gelikon_prefix_woocommerce_order_number', 10, 2)),
	'only the display order-number filter is registered'
);

echo "Order number adapter tests passed\n";
