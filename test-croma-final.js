const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());
const fs = require('fs');

(async () => {
    try {
        const browser = await puppeteer.launch({
            executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            headless: 'new',
            args: [
                '--no-sandbox', 
                '--disable-setuid-sandbox', 
                '--window-size=1920,1080',
                '--disable-web-security'
            ]
        });
        const page = await browser.newPage();
        await page.setViewport({ width: 1920, height: 1080 });
        await page.setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        console.log("Navigating to Croma...");
        await page.goto('https://www.croma.com/search/?text=iphone+15', { waitUntil: 'networkidle2', timeout: 30000 });
        
        console.log("Waiting 5 seconds for JS to render...");
        await new Promise(r => setTimeout(r, 5000));
        
        await page.screenshot({ path: '/tmp/croma_test.png' });
        console.log("Saved Croma screenshot!");
        
        const html = await page.content();
        fs.writeFileSync('/tmp/croma_test.html', html);
        
        await browser.close();
    } catch(e) { console.error(e); }
})();
