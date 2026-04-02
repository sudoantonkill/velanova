// ─── Price Fetching ─────────────────────────────────────────────────
async function fetchPrices() {
    const productInput = document.getElementById('product');
    const product = productInput.value.trim();
    if (!product) {
        productInput.classList.add('shake');
        setTimeout(() => productInput.classList.remove('shake'), 500);
        return;
    }

    const loaderContainer = document.getElementById('loader-container');
    const resultsContainer = document.getElementById('results-container');
    const resultsTitle = document.getElementById('results-title');
    const resultsTime = document.getElementById('results-time');
    const priceResults = document.getElementById('price-results');

    // Show loader, hide results
    loaderContainer.style.display = 'flex';
    resultsContainer.style.display = 'none';
    priceResults.innerHTML = '';

    // Animate store dots
    animateStoreDots();

    // Scroll to loader
    loaderContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });

    try {
        const response = await fetch(`/fetch-prices?product=${encodeURIComponent(product)}`);
        
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}`);
        }
        
        const data = await response.json();

        // Hide loader
        loaderContainer.style.display = 'none';

        // Show results
        resultsTitle.textContent = `Results for "${data.product}"`;
        resultsTime.textContent = `⏱ ${data.elapsed}`;
        resultsContainer.style.display = 'block';

        // Build result cards for each store
        let html = '';
        const storeColors = {
            'Flipkart': { gradient: 'linear-gradient(135deg, #2874f0, #5b9cf6)', icon: '🛒', color: '#2874f0' },
            'Amazon': { gradient: 'linear-gradient(135deg, #ff9900, #ffb84d)', icon: '📦', color: '#ff9900' },
            'Croma': { gradient: 'linear-gradient(135deg, #00b300, #33cc33)', icon: '🏬', color: '#00b300' }
        };

        data.stores.forEach(store => {
            const colors = storeColors[store.store] || { gradient: 'linear-gradient(135deg, #666, #999)', icon: '🏪', color: '#666' };
            
            html += `<div class="store-card" style="--store-color: ${colors.color}">`;
            html += `<div class="store-header" style="background: ${colors.gradient}">`;
            html += `<span class="store-icon">${colors.icon}</span>`;
            html += `<h3>${store.store}</h3>`;
            html += `</div>`;
            html += `<div class="store-body">`;

            if (store.error || store.products.length === 0) {
                html += `
                    <div class="no-results">
                        <span class="no-results-icon">😕</span>
                        <p>No results found</p>
                        <span class="no-results-hint">${store.error ? 'Store may be blocking requests' : 'Try a different search term'}</span>
                    </div>`;
            } else {
                store.products.forEach((product, idx) => {
                    html += `
                        <div class="product-item ${idx === 0 ? 'best-price' : ''}" style="animation-delay: ${idx * 0.1}s">
                            ${idx === 0 ? '<span class="best-badge">Best Match</span>' : ''}
                            ${product.image ? `<img src="${product.image}" alt="${product.title}" class="product-thumb" onerror="this.style.display='none'">` : ''}
                            <div class="product-details">
                                <h4 class="product-title">${product.title}</h4>
                                <p class="product-price">${product.price}</p>
                            </div>
                            <a href="${product.link}" target="_blank" rel="noopener noreferrer" class="product-link" style="background: ${colors.gradient}">
                                View Deal →
                            </a>
                        </div>`;
                });
            }

            html += `</div></div>`;
        });

        priceResults.innerHTML = html;

        // Scroll to results
        resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

    } catch (error) {
        loaderContainer.style.display = 'none';
        resultsContainer.style.display = 'block';
        priceResults.innerHTML = `
            <div class="error-card">
                <span class="error-icon">⚠️</span>
                <h3>Something went wrong</h3>
                <p>${error.message || 'Failed to fetch prices. Please try again.'}</p>
                <button onclick="fetchPrices()" class="retry-btn">Retry</button>
            </div>`;
        resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        console.error('Error fetching prices:', error);
    }
}

// ─── Quick Search from tags/deals ───────────────────────────────────
function quickSearch(term) {
    const input = document.getElementById('product');
    input.value = term;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => fetchPrices(), 300);
}

// ─── Enter key support ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('product');
    if (input) {
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                fetchPrices();
            }
        });
    }
});

// ─── Animate Store Dots ─────────────────────────────────────────────
function animateStoreDots() {
    const dots = document.querySelectorAll('.store-dot');
    dots.forEach((dot, i) => {
        dot.classList.remove('active');
        setTimeout(() => dot.classList.add('active'), i * 800);
    });
}

// ─── Slideshow ──────────────────────────────────────────────────────
let slideIndex = 0;

function showSlides() {
    const slides = document.getElementsByClassName('slide');
    if (slides.length === 0) return;
    
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = 'none';
        slides[i].classList.remove('slide-active');
    }
    slideIndex++;
    if (slideIndex > slides.length) slideIndex = 1;
    slides[slideIndex - 1].style.display = 'block';
    slides[slideIndex - 1].classList.add('slide-active');
    setTimeout(showSlides, 4000);
}

showSlides();
