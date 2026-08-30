const puppeteer = require('puppeteer');
(async () => {
  const browser = await puppeteer.launch({ headless: 'new' });
  const page = await browser.newPage();
  page.on('console', msg => console.log('PAGE LOG:', msg.text()));
  page.on('pageerror', error => console.log('PAGE ERROR:', error.message));
  page.on('response', response => console.log('RESPONSE:', response.status(), response.url()));
  
  await page.goto('http://127.0.0.1:8000/student/login');
  await page.type('#email', 'otu@gmail.com');
  await page.type('#password', 'password');
  await page.click('button[type="submit"]');
  
  await page.waitForTimeout(3000);
  console.log('Final URL:', page.url());
  await browser.close();
})();
