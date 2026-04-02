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
        
        console.log("Navigating to Croma direct search URL...");
        await page.goto('https://www.croma.com/search/?text=iphone+15', { waitUntil: 'networkidle2', timeout: 30000 });
        
        console.log("Waiting 5s...");
        await new Promise(r => setTimeout(r, 5000));
        
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
