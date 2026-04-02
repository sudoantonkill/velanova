const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());

(async () => {
    try {
        console.log("Launching system Chrome...");
        const browser = await puppeteer.launch({
            executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            headless: 'new',
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
        });
        const page = await browser.newPage();
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        console.log("Opening Flipkart...");
        await page.goto('https://www.flipkart.com/search?q=iphone+15', { waitUntil: 'domcontentloaded', timeout: 15000 });
        
        // Wait for price container
        console.log("Waiting for prices...");
        await page.waitForSelector('div.Nx9bqj._4b5DiR', { timeout: 5000 }).catch(() => console.log("Timeout waiting for div.Nx9bqj"));
        
        const products = await page.evaluate(() => {
            const items = [];
            document.querySelectorAll('div[data-id]').forEach(el => {
                const title = el.querySelector('div.KzDlHZ, a.IRpwTa, div._4rR01T')?.innerText;
                const price = el.querySelector('div.Nx9bqj._4b5DiR, div.Nx9bqj, div._30jeq3')?.innerText;
                if (title && price && items.length < 3) items.push({title, price});
            });
            return items;
        });
        
        console.log("Flipkart Products:", JSON.stringify(products, null, 2));
        await browser.close();
    } catch(e) {
        console.error("Error:", e);
    }
})();
