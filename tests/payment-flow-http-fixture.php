<?php
/** Local-only fake receipt handler. Install as a temporary mu-plugin for HTTP tests. */
if (defined('WP_CLI') && WP_CLI) return;
if (!in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('localhost', '127.0.0.1'), true)) return;
add_filter('pre_http_request',function($pre,$args,$url){return new WP_Error('local_fixture','No external requests in payment fixture');},1,3);
add_action('template_redirect',function(){if(isset($_GET['fixture_headers_sent'])){echo str_repeat('x', 10000);flush();}},1);
add_action('template_redirect',function(){if(($_GET['fixture_case']??'')==='baseline')remove_action('template_redirect','gelikon_prepare_tbank_order_payment',35);},20);
add_filter('woocommerce_locate_template',function($path,$name){if($name==='checkout/order-receipt.php' && isset($_GET['fixture_broken_template']))return '/tmp/payment-flow-broken-receipt.php';return $path;},99,2);
function gelikon_test_receipt_response($id) {
 file_put_contents('/tmp/payment-flow-count',json_encode(['order_id'=>$id,'headers_sent'=>headers_sent()])."\n",FILE_APPEND);
 if(isset($_GET['fixture_bank_error'])){wc_add_notice('Fixture bank rejection','error');echo '<p>Fixture bank rejection</p>';return;}
 wp_redirect('https://securepay.tinkoff.ru/fixture-payment');
}
add_action('wp_loaded',function(){
 remove_all_actions('woocommerce_receipt_tbank');
 if(isset($_GET['fixture_missing_handler'])){
  WC()->payment_gateways()->payment_gateways=array(new class extends WC_TBank {public function __construct(){$this->id='tbank';}public function receipt_page($id){gelikon_test_receipt_response($id);}});
 }else{add_action('woocommerce_receipt_tbank','gelikon_test_receipt_response');}
},999);
