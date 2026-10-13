import json,urllib.request,urllib.error,urllib.parse,subprocess,argparse
parser=argparse.ArgumentParser()
parser.add_argument("fixture")
parser.add_argument("--compose",default="/workspace/.gelikon-env/compose.yaml")
args=parser.parse_args()
container=["docker","compose","-f",args.compose,"exec","-T","wordpress"]
f=json.load(open(args.fixture))
assert f['number']==str(f['id']), 'New order must use native number without prefix'
class NoRedirect(urllib.request.HTTPRedirectHandler):
 def redirect_request(self,*args,**kw):return None
opener=urllib.request.build_opener(NoRedirect)
def request(extra):
 parsed=urllib.parse.urlsplit(f['url']);q=urllib.parse.parse_qs(parsed.query);q.update(extra)
 url=urllib.parse.urlunsplit((parsed.scheme,parsed.netloc,parsed.path,urllib.parse.urlencode(q,doseq=True),''))
 try:r=opener.open(url,timeout=30)
 except urllib.error.HTTPError as e:r=e
 return r.code,r.headers.get('Location',''),r.read()
def count():
 r=subprocess.run(container+['cat','/tmp/payment-flow-count'],capture_output=True,text=True)
 return len(r.stdout.splitlines())
subprocess.run(container+['rm','-f','/tmp/payment-flow-count'],check=True)
code,location,body=request({'fixture_case':['baseline'],'fixture_broken_template':['1']})
assert code==200 and not location and count()==0
print('Reproduced: summary-only receipt template never initiates payment')
for extra in ({'fixture_broken_template':['1']},{},{'fixture_missing_handler':['1']},):
 before=count();code,location,body=request(extra)
 assert code==302 and location=='https://securepay.tinkoff.ru/fixture-payment' and count()==before+1
 print('Validated receipt: one gateway call, HTTP 302 to payment service')
before=count();code,location,body=request({'key':['wrong']})
assert not location and count()==before
print('Wrong order key: no gateway call')
before=count();code,location,body=request({'pay_for_order':['true']})
assert not location and count()==before
print('Pay-for-order form: no automatic gateway call')
before=count();code,location,body=request({'fixture_bank_error':['1']})
assert code==200 and not location and count()==before+1 and b'Fixture bank rejection' in body and b'woocommerce-error' in body
print('Gateway rejection: visible error, one call, no duplicate Init in template')

before=count();code,location,body=request({'fixture_headers_sent':['1']})
assert code==200 and not location and count()==before+1 and b'window.location.replace' in body and b'https://securepay.tinkoff.ru/fixture-payment' in body
print('Headers already sent: browser redirect/link fallback, one gateway call')
