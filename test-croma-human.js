const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());

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
        
        console.log("Navigating to Croma home...");
        await page.goto('https://www.croma.com/', { waitUntil: 'networkidle2', timeout: 30000 });
        console.log("Typing in search box...");
        await page.waitForSelector('input#searchV2', { timeout: 10000 });
        await page.type('input#searchV2', 'iphone 15', { delay: 100 });
        await page.keyboard.press('Enter');
        
        console.log("Waiting for results...");
        await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }).catch(() => {});
        await new Promise(r => setTimeout(r, 4000));
        
        await page.screenshot({ path: '/tmp/croma_search.png' });
        console.log("Saved screenshot!");
        
        const products = await page.evaluate(() => {
            const items = [];
            document.querySelectorAll('li.product-item, div.product-item').forEach(el => {
                const titleEl = el.querySelector('h3 a');
                const priceEl = el.querySelector('span.amount');
                if (titleEl && priceEl) {
                    items.push({
                        title: titleEl.innerText.trim(),
                        price: priceEl.innerText.trim()
                    });
                }
            });
            return items;
        });
        
        console.log("Products found:", products);
        await browser.close();
    } catch(e) { console.error(e); }
})();
