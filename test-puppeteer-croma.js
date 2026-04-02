const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());
const fs = require('fs');

(async () => {
    try {
        const browser = await puppeteer.launch({
            executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            headless: 'new',
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
        });
        const page = await browser.newPage();
        await page.setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        await page.goto('https://www.croma.com/search/?text=iphone+15', { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 8000));
        
        await page.screenshot({ path: '/tmp/croma_full.png' });
        
        await browser.close();
    } catch(e) { console.error(e); }
})();
