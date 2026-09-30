import {chromium} from 'playwright-core';
import {spawn,spawnSync} from 'node:child_process';
import {mkdtempSync,writeFileSync,mkdirSync,existsSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join} from 'node:path';
import assert from 'node:assert/strict';
const directory=mkdtempSync(join(tmpdir(),'little-learners-browser-'));
const database=join(directory,'database.sqlite');writeFileSync(database,'');
const port=8127,base=`http://127.0.0.1:${port}`;
const env={...process.env,APP_ENV:'testing',APP_URL:base,DB_CONNECTION:'sqlite',DB_DATABASE:database,DB_URL:'',CACHE_STORE:'array',SESSION_DRIVER:'file',SESSION_COOKIE:'learning_browser_test'};
const migrated=spawnSync('php',['artisan','migrate','--seed','--force'],{env,encoding:'utf8',windowsHide:true});assert.equal(migrated.status,0,migrated.stdout+migrated.stderr);
const server=spawn('php',['artisan','serve','--host=127.0.0.1',`--port=${port}`],{env,stdio:'pipe',windowsHide:true});
let browser;
const errors=[];
try {
 for(let i=0;i<100;i++){try{const response=await fetch(base);if(response.ok)break;}catch{}await new Promise(r=>setTimeout(r,150));}
 const executable=process.env.BROWSER_PATH || ['C:/Program Files/Google/Chrome/Application/chrome.exe','C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(existsSync);
 browser=await chromium.launch({executablePath:executable,headless:true});
 const context=await browser.newContext({viewport:{width:1440,height:1050}});const page=await context.newPage();page.on('pageerror',error=>errors.push(error.message));
 mkdirSync('storage/app/testing',{recursive:true});
 await page.goto(base);await page.locator('.category-grid').waitFor();await page.screenshot({path:'storage/app/testing/desktop-home.png',fullPage:true});
 assert.equal(await page.locator('.learning-card').count(),12);
 await page.goto(base+'/register');await page.getByLabel('Your name',{exact:true}).fill('Browser Parent');await page.getByLabel('Email',{exact:true}).fill('browser@example.test');await page.getByLabel('Password',{exact:true}).fill('browser-password-123');await page.getByLabel('Confirm password',{exact:true}).fill('browser-password-123');await page.getByRole('button',{name:'Create account'}).click();await page.waitForURL('**/parent/dashboard');
 await page.getByRole('link',{name:'+ Add a child'}).click();await page.getByLabel('Name',{exact:true}).fill('Mia');await page.getByLabel('Date of birth').fill('2022-01-01');await page.getByLabel('Learning level').selectOption('level-2');await page.getByLabel('Choose a friend').selectOption('fox');await page.getByRole('button',{name:'Save profile'}).click();await page.waitForURL('**/parent/dashboard');
 await page.getByRole('link',{name:"Let's learn"}).click();await page.waitForURL('**/learn/*');await page.screenshot({path:'storage/app/testing/desktop-dashboard.png',fullPage:true});
 await page.locator('.learning-card').filter({has:page.getByRole('heading',{name:'English Alphabet',exact:true})}).getByRole('link').click();await page.getByRole('link',{name:'Start learning'}).click();await page.locator('#itemValue').waitFor();assert.equal(await page.locator('#itemValue').textContent(),'A');
 const bookmark=page.waitForResponse(r=>r.url().endsWith('/bookmark')&&r.status()===200);await page.getByRole('button',{name:'Next →',exact:true}).click();await page.waitForFunction(()=>document.getElementById('itemValue').textContent==='B');await bookmark;
 await page.reload();assert.equal(await page.locator('#itemValue').textContent(),'B');
 await page.locator('#caseToggle').click();assert.equal(await page.locator('#itemValue').textContent(),'b');
 await page.locator('#drawingCanvas').scrollIntoViewIfNeeded();const box=await page.locator('#drawingCanvas').boundingBox();await page.mouse.move(box.x+box.width*.4,box.y+box.height*.3);await page.mouse.down();await page.mouse.move(box.x+box.width*.55,box.y+box.height*.7,{steps:20});await page.mouse.up();await page.locator('#finishTracing').click();await page.locator('#tracingStatus').filter({hasText:'saved'}).waitFor();
 await page.getByRole('button',{name:'I finished this lesson'}).click();await page.locator('.notice').filter({hasText:'star is saved'}).waitFor();
 await page.getByRole('link',{name:'Picture match'}).click();for(let i=0;i<5;i++){await page.locator('#gameOptions button').first().click();await page.locator('#gameNext').waitFor({state:'visible'});await page.locator('#gameNext').click();}await page.locator('#gameReplay').waitFor({state:'visible'});assert.match(await page.locator('#gameFeedback').textContent(),/practice star is saved/);
 await page.setViewportSize({width:390,height:844});await page.goto(base);await page.screenshot({path:'storage/app/testing/mobile-home.png',fullPage:true});assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true,'Mobile landing overflows');
 await page.goto(base+'/parent/dashboard');await page.getByRole('link',{name:"Let's learn"}).click();assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true,'Mobile dashboard overflows');await page.screenshot({path:'storage/app/testing/mobile-dashboard.png',fullPage:true});
 await page.locator('.learning-card').filter({has:page.getByRole('heading',{name:'Shapes',exact:true})}).getByRole('link').click();await page.getByRole('link',{name:'Start learning'}).click();assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true,'Mobile lesson overflows');await page.screenshot({path:'storage/app/testing/mobile-lesson.png',fullPage:true});
 assert.deepEqual(errors,[],'Browser JavaScript errors');console.log('PASS: registration, child creation, navigation, saved position, lowercase, canvas reward, lesson completion, game completion, desktop/mobile layouts, zero JavaScript errors.');
} finally {await browser?.close();if(process.platform==='win32')spawnSync('taskkill',['/pid',String(server.pid),'/T','/F'],{windowsHide:true,stdio:'ignore'});else server.kill();}
