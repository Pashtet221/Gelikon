<?php
/** Seed a disposable LOCAL order. Never run on a real store. */
if (!defined('WP_CLI') || !WP_CLI || !in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('127.0.0.1', 'localhost'), true)) {
 throw new RuntimeException('A local WordPress site and WP-CLI are required');
}

$order=wc_create_order(['status'=>'pending']);$order->set_payment_method('tbank');$order->set_payment_method_title('Онлайн оплата');$order->set_billing_email('fixture@example.test');$fee=new WC_Order_Item_Fee();$fee->set_name('Payment flow fixture');$fee->set_total(100);$fee->set_amount(100);$order->add_item($fee);$order->calculate_totals();$order->save();
$gateway=gelikon_tbank_gateway();$result=$gateway->process_payment($order->get_id());
file_put_contents('/tmp/payment-flow-fixture.json',json_encode(['id'=>$order->get_id(),'number'=>$order->get_order_number(),'url'=>$result['redirect'],'key'=>$order->get_order_key()]));
echo "Payment fixture ready\n";
