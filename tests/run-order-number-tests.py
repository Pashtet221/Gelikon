import subprocess,uuid,concurrent.futures
import argparse
parser=argparse.ArgumentParser()
parser.add_argument('--compose',default='/workspace/.gelikon-env/compose.yaml')
compose=['docker','compose','-f',parser.parse_args().compose]
for mode in ['cpt','hpos']:
 prefix='gelikon_test_'+mode[0]+uuid.uuid4().hex[:5]+'_'
 base=compose+['exec','-T','-e','WORDPRESS_TABLE_PREFIX='+prefix,'-e','WP_ENVIRONMENT_TYPE=local','wordpress','php','/opt/wp-cli.phar','--allow-root']
 def run(*args):
  r=subprocess.run(base+list(args),text=True,capture_output=True)
  if r.returncode:
   print(r.stdout+r.stderr)
   raise RuntimeError('Failed '+str(args))
  return r.stdout
 run('core','install','--url=http://127.0.0.1:8080','--title=Gelikon isolated tests','--admin_user=integration','--admin_password=local-test-only','--admin_email=integration@example.test','--skip-email')
 run('plugin','activate','woocommerce','tbank-woocommerce','advanced-custom-fields')
 if mode=='hpos':
  run('option','update','woocommerce_custom_orders_table_data_sync_enabled','no')
  run('option','update','woocommerce_custom_orders_table_enabled','yes')
 run('eval', "WC_Install::create_pages(); $order = wc_create_order(); $order->set_payment_method('tbank'); $order->save(); update_option('gelikon_test_legacy_order', $order->get_id()); $draft = new WC_Order(); $draft->set_status('checkout-draft'); $draft->save(); update_option('gelikon_test_legacy_draft', $draft->get_id());")
 run('theme','activate','gelikon')
 print(mode,run('eval-file','/var/www/html/wp-content/themes/gelikon/tests/order-numbers-integration.php'))
 # Concurrent processes all contend for the same counter row.
 code="echo 'RESULT:' . wc_create_order()->get_order_number();"
 with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
  results=list(pool.map(lambda _:run('eval',code),range(16)))
 numbers=[int(s.split('RESULT:')[1].strip()) for s in results]
 assert sorted(numbers)==list(range(10004,10020)),numbers
 # Concurrent retry of the same order must not consume further numbers.
 code="gelikon_assign_order_number((int)get_option('gelikon_test_parallel_order')); echo 'RESULT:' . gelikon_get_sequential_order_number((int)get_option('gelikon_test_parallel_order'));"
 with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
  results=list(pool.map(lambda _:run('eval',code),range(16)))
 assert all(s.split('RESULT:')[1].strip()=='10002' for s in results),results
 assert 'RESULT:10020' in run('eval',"echo 'RESULT:' . wc_create_order()->get_order_number();")
 print(mode+': PASS 16 parallel creations and 16 parallel retries, no duplicates or gaps')
 print('PREFIX:'+prefix)
