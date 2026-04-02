const express = require('express');
const path = require('path');
const cors = require('cors');
const axios = require('axios');
const cheerio = require('cheerio');
const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());

const app = express();
app.use(cors());
app.use(express.json());
app.use(express.static(__dirname));

const DESKTOP_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

let browserInstance = null;
async function getBrowser() {
    if (!browserInstance) {
        console.log("Launching background browser (System Chrome)...");
        try {
            browserInstance = await puppeteer.launch({
                executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                headless: 'new',
                args: [
                    '--no-sandbox', 
                    '--disable-setuid-sandbox', 
                    '--disable-dev-shm-usage',
                    '--disable-gpu',
                    '--no-zygote',
                    '--single-process'
                ]
            });
        } catch (e) {
            console.error("Failed to launch browser:", e.message);
        }
    }
    return browserInstance;
}

// Ensure browser is closed on exit
process.on('exit', () => { if (browserInstance) browserInstance.close(); });
process.on('SIGINT', () => { if (browserInstance) browserInstance.close(); process.exit(); });

// ─── Fetch from Flipkart (Puppeteer) ──────────────────────────────
async function fetchFromFlipkart(productName) {
    try {
        const url = `https://www.flipkart.com/search?q=${encodeURIComponent(productName)}&sort=relevance`;
        console.log(`  📘 Flipkart: fetching via Headless Chrome...`);
        
        const browser = await getBrowser();
        if (!browser) throw new Error("Browser not available");
        
        const page = await browser.newPage();
        await page.setUserAgent(DESKTOP_UA);
        
        // Disable images and CSS to speed up loading
        await page.setRequestInterception(true);
        page.on('request', (req) => {
            if (['image', 'stylesheet', 'font'].includes(req.resourceType())) {
                req.abort();
            } else {
                req.continue();
            }
        });

        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 12000 }).catch(() => {});
        
        // Wait a small delay to let React render the DOM tags
        await new Promise(r => setTimeout(r, 1000));
        
        const products = await page.evaluate(() => {
            const items = [];
            // Target the specific div classes/ids that hold price info
            const productContainers = document.querySelectorAll('div[data-id], div._1AtVbE');
            
            productContainers.forEach(el => {
                if (items.length >= 3) return;
                
                // Titles usually in these tags
                const titleEl = el.querySelector('div.KzDlHZ, a.IRpwTa, div._4rR01T, a.s1Q9rs, a.WKTcLC, .RG5Slk');
                // Prices usually in these tags
                const priceEl = el.querySelector('div.Nx9bqj._4b5DiR, div.Nx9bqj, div._30jeq3, .hZ3P6w');
                // Links
                const linkEl = el.querySelector('a[href*="/p/"]') || el.querySelector('a');
                // Images
                const imgEl = el.querySelector('img[src*="rukminim"]');

                if (titleEl && priceEl) {
                    const title = titleEl.innerText.trim();
                    const price = priceEl.innerText.trim();
                    const link = linkEl ? linkEl.getAttribute('href') : null;
                    const image = imgEl ? imgEl.getAttribute('src') : null;

                    if (title.length > 5) {
                        items.push({
                            title: title.substring(0, 100),
                            price,
                            link: link ? (link.startsWith('http') ? link : `https://www.flipkart.com${link}`) : '#',
                            image: image || ''
                        });
                    }
                }
            });
            return items;
        });

        await page.close();

        if (products.length > 0) {
            console.log(`  📘 Flipkart: found ${products.length} products`);
            return { store: 'Flipkart', products, error: null };
        } else {
            throw new Error("No products found in DOM");
        }
    } catch (error) {
        console.error(`  ❌ Flipkart error: ${error.message}`);
        // Fallback
        return { 
            store: 'Flipkart', 
            products: [{
                title: `Search "${productName}" on Flipkart`,
                price: 'Click to view',
                link: `https://www.flipkart.com/search?q=${encodeURIComponent(productName)}`,
                image: ''
            }], 
            error: null 
        };
    }
}

// ─── Fetch from Amazon (HTTP/Cheerio - works reliably) ─────────────
async function fetchFromAmazon(productName) {
    try {
        const url = `https://www.amazon.in/s?k=${encodeURIComponent(productName)}`;
        console.log(`  📙 Amazon: fetching via Axios...`);
        
        const response = await axios.get(url, {
            headers: {
                'User-Agent': DESKTOP_UA,
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            },
            timeout: 10000,
            maxRedirects: 5,
        });

        const $ = cheerio.load(response.data);
        const products = [];

        $('div[data-component-type="s-search-result"]').each(function(i) {
            if (products.length >= 3) return false;
            const el = $(this);
            let title = el.find('h2 a span').text().trim() || el.find('.a-text-normal').first().text().trim();
            let priceWhole = el.find('.a-price-whole').first().text().trim().replace(/[,.]/g, '');
            let price = priceWhole ? `₹${parseInt(priceWhole).toLocaleString('en-IN')}` : el.find('.a-price .a-offscreen').first().text().trim();
            let link = el.find('h2 a').attr('href') || el.find('a.a-link-normal').first().attr('href');
            let image = el.find('img.s-image').attr('src') || '';

            if (title && price) {
                products.push({
                    title: title.substring(0, 100),
                    price,
                    link: link ? (link.startsWith('http') ? link : `https://www.amazon.in${link}`) : '#',
                    image
                });
            }
        });

        console.log(`  📙 Amazon: found ${products.length} products`);
        return { store: 'Amazon', products, error: null };
    } catch (error) {
        console.error(`  ❌ Amazon error: ${error.message}`);
        return { 
            store: 'Amazon', products: [{title: `Search "${productName}"`, price: 'Click to view', link: `https://www.amazon.in/s?k=${encodeURIComponent(productName)}`, image: ''}], error: null 
        };
    }
}

// ─── Fetch from Croma (Puppeteer - Human Simulation) ───────────────────
async function fetchFromCroma(productName) {
    try {
        console.log(`  📗 Croma: fetching via Headless Chrome (Human Sim)...`);
        
        const browser = await getBrowser();
        if (!browser) throw new Error("Browser not available");
        
        const page = await browser.newPage();
        await page.setViewport({ width: 1920, height: 1080 });
        await page.setUserAgent(DESKTOP_UA);

        // Go to home page to bypass Bot block
        await page.goto('https://www.croma.com/', { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
        
        // Type into search bar and search
        await page.waitForSelector('input#searchV2', { timeout: 8000 });
        await page.type('input#searchV2', productName, { delay: 50 });
        await page.keyboard.press('Enter');
        
        // Wait for results
        await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 12000 }).catch(() => {});
        await new Promise(r => setTimeout(r, 2000)); // Additional wait for React DOM to settle
        
        const products = await page.evaluate(() => {
            const items = [];
            document.querySelectorAll('li.product-item, div.product-item, div.cp-product').forEach(el => {
                if (items.length >= 3) return;
                const titleEl = el.querySelector('h3 a, .product-title a, a.product__list--name');
                const priceEl = el.querySelector('span.amount, .new-price, .pdpPrice');
                const imgEl = el.querySelector('img');

                if (titleEl && priceEl) {
                    items.push({
                        title: titleEl.innerText.trim().substring(0, 100),
                        price: priceEl.innerText.trim(),
                        link: titleEl.getAttribute('href') ? `https://www.croma.com${titleEl.getAttribute('href')}` : '#',
                        image: imgEl ? (imgEl.getAttribute('src') || imgEl.getAttribute('data-src')) : ''
                    });
                }
            });
            return items;
        });

        await page.close();

        if (products.length > 0) {
            console.log(`  📗 Croma: found ${products.length} products`);
            return { store: 'Croma', products, error: null };
        } else {
            throw new Error("No products found in DOM");
        }
    } catch (error) {
        console.error(`  ❌ Croma error: ${error.message}`);
        return { 
            store: 'Croma', products: [{title: `Search "${productName}"`, price: 'Click to view', link: `https://www.croma.com/search/?text=${encodeURIComponent(productName)}`, image: ''}], error: null 
        };
    }
}

// ─── Routes ────────────────────────────────────────────────────────

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index3.html'));
});

app.get('/fetch-prices', async (req, res) => {
    const product = req.query.product;
    if (!product || product.trim() === '') {
        return res.status(400).json({ error: 'Product name is required' });
    }

    console.log(`\n🔍 Fetching prices for: "${product}"`);
    const startTime = Date.now();

    // Fire requests concurrently
    try {
        const [flipkart, amazon, croma] = await Promise.all([
            fetchFromFlipkart(product),
            fetchFromAmazon(product),
            fetchFromCroma(product)
        ]);

        const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
        console.log(`✅ Done in ${elapsed}s | FK:${flipkart.products.length} AM:${amazon.products.length} CR:${croma.products.length}\n`);

        res.json({ product, stores: [flipkart, amazon, croma], elapsed: `${elapsed}s` });
    } catch (error) {
        console.error('Error in /fetch-prices:', error);
        res.status(500).json({ error: 'Failed to fetch prices.' });
    }
});

// ─── Start Server ──────────────────────────────────────────────────
const PORT = 4000;
app.listen(PORT, async () => {
    console.log(`\n🚀 VelaNova server running at http://localhost:${PORT}`);
    console.log(`📦 Using Puppeteer for JS-rendered sites + Axios for static sites`);
    // Pre-warm the background browser
    await getBrowser();
});
