// Run against a disposable WordPress + ACF installation with Playwright installed.
// node tests/contacts-editor-browser-test.js /path/to/local-fixture.json
// Fixture: {page: <contacts page ID>, cookies: [{name, value}], url?: <base URL>}.
// Seed company_address with <p><strong>Existing address</strong></p>, and use
// page-contacts.php. The test saves edits to this disposable page. Keep auth
// cookies outside Git; delete the fixture and test page afterwards.
const fs=require('fs'),assert=require('assert');const {chromium}=require('playwright');
(async()=>{
 const fixture=JSON.parse(fs.readFileSync(process.argv[2]));
 const baseURL = fixture.url || 'http://127.0.0.1:8080';
 const browser=await chromium.launch({executablePath:'/usr/bin/chromium',headless:true,args:['--no-sandbox']});
 const context=await browser.newContext();
 await context.addCookies(fixture.cookies.map(c=>({...c,url:baseURL})));
 const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.route('**/*',r=>new URL(r.request().url()).origin === new URL(baseURL).origin?r.continue():r.abort());
 const url=`${baseURL}/wp-admin/post.php?post=${fixture.page}&action=edit`;
 await page.goto(url,{waitUntil:'domcontentloaded'});
 await page.waitForFunction(()=>window.tinymce?.editors.filter(e=>e.id.startsWith('acf-editor')&&e.initialized).length===9);
 await page.evaluate(()=>{if(wp.data.select('core/edit-post').isFeatureActive('welcomeGuide'))wp.data.dispatch('core/edit-post').toggleFeature('welcomeGuide');});
 const address=page.locator('.acf-field[data-name="company_address"]');
 const details=page.locator('.acf-field[data-name="company_details_content"]');
 assert(await address.frameLocator('iframe').locator('body').innerText()==='Existing address');
 console.log('All nine visual editors initialized with saved values');
 // Reproduce Gutenberg moving all initialized iframe editors.
 const addressBody = address.frameLocator('iframe').locator('body');
 await addressBody.click(); await addressBody.press('Control+End');
 await addressBody.pressSequentially(' Unsaved address edit');
 for (let move = 0; move < 3; move++) {
   await page.evaluate(()=>{const box=document.querySelector('.acf-field[data-name="company_address"]').closest('.postbox'); const parent=box.parentNode; const next=box.nextSibling; box.remove();parent.insertBefore(box,next);});
   await page.waitForFunction(()=>Array.from(document.querySelectorAll('.acf-field-wysiwyg iframe')).length === 9 && Array.from(document.querySelectorAll('.acf-field-wysiwyg iframe')).every(f=>f.contentDocument.body.isContentEditable));
   assert((await addressBody.innerText()).includes('Unsaved address edit'));
 }
 console.log('Repeated Gutenberg metabox moves: all nine editors recover and retain unsaved input');
 // Round trip tabs with existing rich content.
 await address.locator('.switch-html').click();
 assert((await address.locator('textarea.wp-editor-area').inputValue()).includes('Existing address'));
 await address.locator('.switch-tmce').click();
 await address.frameLocator('iframe').locator('body').waitFor({state:'visible'});
 assert((await address.frameLocator('iframe').locator('body').innerText()).includes('Existing address'));
 console.log('Text → Visual round trip retains existing content');
 // Simulate the missing TinyMCE instance that otherwise leaves a blank Visual pane.
 await page.evaluate(()=>{const field=document.querySelector('.acf-field[data-name="company_address"]');const id=field.querySelector('textarea').id;tinymce.get(id).remove();delete tinyMCEPreInit.mceInit[id];field.querySelector('.acf-editor-wrap').classList.remove('tmce-active');field.querySelector('.acf-editor-wrap').classList.add('html-active');});
 await address.locator('.switch-tmce').click();
 await address.frameLocator('iframe').locator('body').waitFor({state:'visible'});
 assert((await address.frameLocator('iframe').locator('body').innerText()).includes('Existing address'));
 console.log('Missing TinyMCE instance recovers on Visual tab');
 await address.locator('.switch-html').click();
 await address.locator('textarea.wp-editor-area').fill('<p><strong>Existing address</strong> New text mode edit</p>');
 await page.evaluate(()=>{const box=document.querySelector('.acf-field[data-name="company_address"]').closest('.postbox');const parent=box.parentNode;box.remove();parent.appendChild(box);});
 await address.locator('.switch-tmce').click();
 await page.waitForFunction(()=>{const f=document.querySelector('.acf-field[data-name="company_address"] iframe');return f && f.contentDocument.body.isContentEditable;});
 assert((await addressBody.innerText()).includes('New text mode edit'));
 console.log('Text mode edits survive iframe reload and Visual switch');
 const savedValue = 'Saved from visual editor ' + Date.now();
 const body = details.frameLocator('iframe').locator('body');
 await body.click();
 await body.press('Control+A');
 await body.pressSequentially(savedValue);
 await body.press('Space');
 await details.locator('label').first().click();
 const metaSaved = page.waitForResponse(r => new URL(r.url()).pathname === '/wp-admin/post.php' && r.request().method() === 'POST' && r.status() === 302);
 await page.getByRole('button',{name:'Save',exact:true}).click();
 await metaSaved;
 await page.waitForFunction(()=>!wp.data.select('core/editor').isSavingPost());
 await page.reload({waitUntil:'domcontentloaded'});
 await page.waitForFunction(()=>window.tinymce?.editors.filter(e=>e.id.startsWith('acf-editor')&&e.initialized).length===9);
 assert((await details.frameLocator('iframe').locator('body').innerText()).includes(savedValue));
 console.log('Typing in Visual mode, Save and reload: passed');
 assert.deepStrictEqual(errors,[]);await browser.close();
})().catch(e=>{console.error(e.message);process.exit(1)});
