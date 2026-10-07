<?php
/** Immutable order numbers, isolated from WordPress IDs (CPT and HPOS). */
if (!defined('ABSPATH')) {
	exit;
}

function gelikon_order_numbers_table() {
	global $wpdb;
	return $wpdb->prefix . 'gelikon_order_numbers';
}

/** Establish the cutover once, without renumbering any existing order or draft. */
function gelikon_install_order_numbers() {
	global $wpdb;
	if (get_option('gelikon_order_numbers_schema') === '2') {
		return;
	}
	$table = gelikon_order_numbers_table();
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta("CREATE TABLE $table (
		order_id bigint(20) unsigned NOT NULL,
		order_number bigint(20) unsigned NOT NULL,
		next_number bigint(20) unsigned NOT NULL DEFAULT 10000,
		legacy_max_id bigint(20) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (order_id),
		UNIQUE KEY order_number (order_number)
	) ENGINE=InnoDB " . $wpdb->get_charset_collate() . ';');
	$engine = $wpdb->get_var($wpdb->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s', $table));
	if (strtoupper((string) $engine) !== 'INNODB') {
		throw new RuntimeException('Gelikon order numbers require an InnoDB table.');
	}
	$cutover = (int) $wpdb->get_var("SELECT MAX(ID) FROM {$wpdb->posts}");
	$hpos = $wpdb->prefix . 'wc_orders';
	if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($hpos))) === $hpos) {
		$cutover = max($cutover, (int) $wpdb->get_var("SELECT MAX(id) FROM $hpos"));
	}
	// A concurrent initializer must never overwrite the first cutoff/counter.
	if ($wpdb->query($wpdb->prepare("INSERT IGNORE INTO $table (order_id, order_number, legacy_max_id) VALUES (0, 0, %d)", $cutover)) === false) {
		throw new RuntimeException('Could not initialize Gelikon order numbers.');
	}
	update_option('gelikon_order_numbers_schema', '2', false);
}
add_action('init', 'gelikon_install_order_numbers', 5);

function gelikon_get_sequential_order_number($order_id) {
	global $wpdb;
	static $numbers = array();
	$key = $wpdb->prefix . ':' . (int) $order_id;
	if (isset($numbers[$key])) {
		return $numbers[$key];
	}
	if (get_option('gelikon_order_numbers_schema') !== '2' || !$order_id) {
		return '';
	}
	$number = (string) $wpdb->get_var($wpdb->prepare('SELECT order_number FROM ' . gelikon_order_numbers_table() . ' WHERE order_id = %d', $order_id));
	if ($number !== '') {
		$numbers[$key] = $number; // Cache only immutable positive mappings, never misses.
	}
	return $number;
}

function gelikon_order_requires_sequence($order_id) {
	global $wpdb;
	$cutover = $wpdb->get_var('SELECT legacy_max_id FROM ' . gelikon_order_numbers_table() . ' WHERE order_id = 0');
	return $cutover !== null && (int) $order_id > (int) $cutover;
}

/** One transaction locks the counter, rechecks the mapping, then commits both. */
function gelikon_assign_order_number($order_id, $order = null) {
	global $wpdb;
	$order = $order instanceof WC_Order ? $order : wc_get_order($order_id);
	if (!$order || $order->get_type() !== 'shop_order' || $order->has_status(array('auto-draft', 'draft', 'checkout-draft'))) {
		return;
	}
	gelikon_install_order_numbers();
	$table = gelikon_order_numbers_table();
	if ($wpdb->query('START TRANSACTION') === false) {
		throw new RuntimeException('Could not start order numbering transaction.');
	}
	try {
		$counter = $wpdb->get_row("SELECT next_number, legacy_max_id FROM $table WHERE order_id = 0 FOR UPDATE");
		if (!$counter) {
			throw new RuntimeException('Order numbering counter is unavailable.');
		}
		if ((int) $order_id > (int) $counter->legacy_max_id) {
			$existing = $wpdb->get_var($wpdb->prepare("SELECT order_number FROM $table WHERE order_id = %d", $order_id));
			if (!$existing) {
				$number = (int) $counter->next_number;
				// A historic bank OrderId cannot be reused on the same terminal.
				$legacy = $number <= (int) $counter->legacy_max_id ? wc_get_order($number) : false;
				if ($legacy && $legacy->get_payment_method() === 'tbank') {
					throw new RuntimeException('New order number conflicts with a historic T-Bank OrderId: ' . $number);
				}
				if ($wpdb->insert($table, array('order_id' => $order_id, 'order_number' => $number), array('%d', '%d')) !== 1 ||
					$wpdb->query($wpdb->prepare("UPDATE $table SET next_number = %d WHERE order_id = 0", $number + 1)) !== 1) {
					throw new RuntimeException('Could not reserve a unique order number.');
				}
			}
		}
		if ($wpdb->query('COMMIT') === false) {
			throw new RuntimeException('Could not commit order number.');
		}
	} catch (Throwable $error) {
		$wpdb->query('ROLLBACK');
		throw $error; // Never proceed to payment with an unreserved number.
	}
}
add_action('woocommerce_new_order', 'gelikon_assign_order_number', 1, 2);

// Checkout must not advance to a gateway after WC_Order::save swallowed a DB error.
add_action('woocommerce_checkout_order_processed', 'gelikon_assign_order_number', 1, 1);
add_action('woocommerce_store_api_checkout_order_processed', function ($order) {
	gelikon_assign_order_number($order->get_id(), $order);
}, 1);
