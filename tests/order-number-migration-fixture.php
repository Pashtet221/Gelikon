<?php
/** Seed the previous 10000 numbering and orders created during the rollback. */
if (!defined('WP_CLI') || !WP_CLI || strpos($GLOBALS['wpdb']->prefix, 'gelikon_test_') !== 0 || wp_get_environment_type() !== 'local') {
	throw new RuntimeException('Isolated local database required');
}
$wpdb = $GLOBALS['wpdb'];
$table = $wpdb->prefix . 'gelikon_order_numbers';
$legacy_max = (int)$wpdb->get_var("SELECT MAX(ID) FROM {$wpdb->posts}");
$wpdb->query("CREATE TABLE $table (order_id bigint(20) unsigned NOT NULL, order_number bigint(20) unsigned NOT NULL, next_number bigint(20) unsigned NOT NULL DEFAULT 10000, legacy_max_id bigint(20) unsigned NOT NULL DEFAULT 0, PRIMARY KEY(order_id), UNIQUE KEY order_number(order_number)) ENGINE=InnoDB");
$old = wc_create_order(array('status'=>'pending'));
$old->set_payment_method('tbank');
$fee = new WC_Order_Item_Fee();
$fee->set_name('Old payment fixture');
$fee->set_amount(100);
$fee->set_total(100);
$old->add_item($fee);
$old->calculate_totals();
$old->update_meta_data('_gelikon_tbank_payment_ids', array('9001'));
$old->save();
$wpdb->insert($table, array('order_id'=>0, 'order_number'=>0, 'next_number'=>10010, 'legacy_max_id'=>$legacy_max));
$wpdb->insert($table, array('order_id'=>$old->get_id(), 'order_number'=>10000));
update_option('gelikon_order_numbers_schema', '2');
update_option('gelikon_test_old_numbered_order', $old->get_id());
$paused = wc_create_order(array('status'=>'pending'));
$paused->set_payment_method('tbank');
$paused->save();
update_option('gelikon_test_paused_order', $paused->get_id());
