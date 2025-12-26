// Content script for Mafaza Fortuna Chrome Extension
// This script runs on web pages and extracts place data

class MafazaContentScraper {
    constructor() {
        this.settings = {};
        this.selectors = this.getRobustSelectors();
        this.init();
    }

    init() {
        console.log('Mafaza Fortuna Content Scraper initialized');

        // Listen for messages from popup/background scripts
        chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
            this.handleMessage(message, sender, sendResponse);
            return true;
        });

        // Auto-detect if we're on a scrapable page
        if (this.isOnScrapablePage()) {
            this.injectScrapingIndicator();
        }
    }

    getRobustSelectors() {
        // Comprehensive selectors for different websites
        // Multiple fallbacks to handle website changes
        return {
            googleMaps: {
                placeName: [
                    '[data-testid="place-name"]',
                    '[jsaction*="place"] h1',
                    'h1[data-attrid="title"]',
                    '.place-title h1',
                    'h1.place-name',
                    '[data-item-id] h1',
                    '.section-hero-header-title h1',
                    // Generic fallbacks
                    'h1',
                    '.place-name',
                    '[role="main"] h1'
                ],
                address: [
                    '[data-item-id*="address"]',
                    '.section-info-text[data-attrid="kc:/location/location:address"]',
                    '.address-text',
                    '[jsaction*="address"]',
                    '.place-address',
                    // Generic fallbacks
                    '[data-attrid*="address"]',
                    '.address'
                ],
                rating: [
                    '[data-attrid="kc:/location/location:rating"]',
                    '.section-star-display',
                    '.rating-text',
                    '.stars-container',
                    '[aria-label*="rating"]',
                    // Generic fallbacks
                    '.rating',
                    '[data-rating]'
                ],
                phone: [
                    '[data-attrid="kc:/location/location:phone"]',
                    '.section-info-text[data-attrid*="phone"]',
                    '[jsaction*="phone"]',
                    '.phone-text',
                    // Generic fallbacks
                    '[href^="tel:"]',
                    '.phone'
                ],
                website: [
                    '[data-attrid="kc:/location/location:website"]',
                    '[jsaction*="website"]',
                    '.website-text',
                    // Generic fallbacks
                    '[href^="http"]',
                    'a[href*="."]'
                ]
            },

            googleSearch: {
                placeName: [
                    '.SPZz6b h2',
                    '.qrShPb h2',
                    '.sATSHe h2',
                    '[data-attrid="title"]',
                    '.aLF0Z h2',
                    // Generic fallbacks
                    'h2',
                    '.place-name'
                ],
                address: [
                    '.LrzXr',
                    '.z3HNkc',
                    '[data-attrid*="address"]',
                    '.address-text',
                    // Generic fallbacks
                    '.address',
                    '.location'
                ]
            },

            generic: {
                placeName: [
                    'h1',
                    'h2',
                    '.place-name',
                    '.business-name',
                    '.location-name',
                    '[data-name]',
                    '[itemprop="name"]'
                ],
                address: [
                    '.address',
                    '.location',
                    '[itemprop="address"]',
                    '[data-address]',
                    '.street-address'
                ],
                phone: [
                    '[href^="tel:"]',
                    '.phone',
                    '[itemprop="telephone"]',
                    '[data-phone]'
                ],
                website: [
                    '[href^="http"]',
                    '.website',
                    '[itemprop="url"]'
                ]
            }
        };
    }

    isOnScrapablePage() {
        const url = window.location.href;
        return /google\.com\/maps|google\.com\/search|bing\.com|yahoo\.com/.test(url);
    }

    injectScrapingIndicator() {
        // Add a small indicator that scraping is available
        const indicator = document.createElement('div');
        indicator.id = 'mafaza-indicator';
        indicator.innerHTML = '🔍 Mafaza Ready';
        indicator.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 9999;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        `;

        indicator.onclick = () => this.manualScrape();

        // Add hover effect
        indicator.onmouseover = () => indicator.style.transform = 'scale(1.05)';
        indicator.onmouseout = () => indicator.style.transform = 'scale(1)';

        document.body.appendChild(indicator);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (indicator.parentNode) {
                indicator.style.opacity = '0';
                setTimeout(() => indicator.remove(), 300);
            }
        }, 5000);
    }

    async handleMessage(message, sender, sendResponse) {
        try {
            switch (message.action) {
                case 'extractPlaceData':
                    const data = await this.extractPlaceData();
                    sendResponse({ data: data });
                    break;

                case 'scrapePage':
                    const scrapedData = await this.scrapeCurrentPage(message.settings);
                    sendResponse({ success: true, data: scrapedData });
                    break;

                default:
                    sendResponse({ error: 'Unknown action' });
            }
        } catch (error) {
            console.error('Content script error:', error);
            sendResponse({ error: error.message });
        }
    }

    async extractPlaceData() {
        const url = window.location.href;
        let data = {};

        try {
            if (url.includes('google.com/maps')) {
                data = await this.extractFromGoogleMaps();
            } else if (url.includes('google.com/search')) {
                data = await this.extractFromGoogleSearch();
            } else {
                data = await this.extractGeneric();
            }

            // Add metadata
            data.source = 'chrome_extension';
            data.scraped_at = new Date().toISOString();
            data.page_url = url;

            // Validate extracted data
            data = this.validateAndCleanData(data);

            console.log('Extracted place data:', data);
            return data;

        } catch (error) {
            console.error('Failed to extract place data:', error);
            throw error;
        }
    }

    async extractFromGoogleMaps() {
        const data = {};
        const currentUrl = window.location.href;

        // Check if this is a place details page or search results
        if (currentUrl.includes('/place/')) {
            // Individual place page
            return await this.extractFromGoogleMapsPlacePage();
        } else if (currentUrl.includes('/search')) {
            // Search results page - try to get the first/top result
            return await this.extractFromGoogleMapsSearchPage();
        } else {
            // Fallback to generic extraction
            return await this.extractGeneric();
        }
    }

    async extractFromGoogleMapsPlacePage() {
        const data = {};
        const currentUrl = window.location.href;

        // Wait for page to fully load
        await this.waitForElement('h1', 3000);

        // Extract place name - try multiple selectors
        data.name = this.findElementText([
            'h1[data-attrid="title"]',
            'h1.place-name',
            '[role="main"] h1',
            'h1',
            '[data-testid="place-name"]',
            '.place-title h1'
        ]);

        // Extract address
        data.address = this.findElementText([
            '[data-item-id*="address"]',
            '.section-info-text[data-attrid="kc:/location/location:address"]',
            '[jsaction*="address"]',
            '.address-text',
            '.address'
        ]);

        // Extract rating
        const ratingText = this.findElementText([
            '[data-attrid="kc:/location/location:rating"]',
            '.section-star-display',
            '.rating-text',
            '[aria-label*="rating"]'
        ]);
        if (ratingText) {
            const ratingMatch = ratingText.match(/(\d+\.?\d*)/);
            if (ratingMatch) {
                data.rating = parseFloat(ratingMatch[1]);
            }
        }

        // Extract review count
        const reviewText = this.findElementText([
            '[aria-label*="reviews"]',
            '[data-attrid*="reviews"]',
            '.section-rating-term'
        ]);
        if (reviewText) {
            const reviewMatch = reviewText.match(/(\d+(?:,\d+)*)/);
            if (reviewMatch) {
                data.review_count = parseInt(reviewMatch[1].replace(/,/g, ''));
            }
        }

        // Extract phone
        const phoneElement = this.findElement([
            '[data-attrid="kc:/location/location:phone"]',
            '[jsaction*="phone"]',
            '[href^="tel:"]'
        ]);
        if (phoneElement) {
            if (phoneElement.href && phoneElement.href.startsWith('tel:')) {
                data.phone = phoneElement.href.replace('tel:', '');
            } else {
                data.phone = phoneElement.textContent.trim();
            }
        }

        // Extract website
        const websiteElement = this.findElement([
            '[data-attrid="kc:/location/location:website"]',
            '[jsaction*="website"]',
            'a[href^="http"]:not([href*="google"])'
        ]);
        if (websiteElement && websiteElement.href) {
            data.website = websiteElement.href;
        }

        // Extract category
        data.category = this.findElementText([
            '[jsaction*="category"]',
            '.category-text',
            '[data-category]'
        ]);

        // Try to extract coordinates from URL
        const coords = this.extractCoordinatesFromUrl(currentUrl);
        if (coords) {
            data.lat = coords.lat;
            data.lng = coords.lng;
        }

        // Extract place ID from URL
        const placeIdMatch = currentUrl.match(/place\/([^\/@]+)/);
        if (placeIdMatch) {
            data.place_id = decodeURIComponent(placeIdMatch[1]);
        }

        return data;
    }

    async extractFromGoogleMapsSearchPage() {
        const data = {};

        // For search results, try to get the most prominent result
        // This is more challenging as Google Maps changes layout frequently

        // Try to find the first search result
        const firstResult = document.querySelector([
            '[data-result-index="0"]',
            '.section-result:first-child',
            '.place-result:first-child',
            '[role="listitem"]:first-child'
        ].join(', '));

        if (firstResult) {
            // Extract from the first result element
            data.name = this.findElementText([
                'h3',
                '.place-name',
                '[data-attrid="title"]'
            ], firstResult);

            data.address = this.findElementText([
                '.address-text',
                '.location',
                '[data-attrid*="address"]'
            ], firstResult);

            // Try to get rating
            const ratingText = this.findElementText([
                '.rating-text',
                '[aria-label*="rating"]'
            ], firstResult);
            if (ratingText) {
                const ratingMatch = ratingText.match(/(\d+\.?\d*)/);
                if (ratingMatch) {
                    data.rating = parseFloat(ratingMatch[1]);
                }
            }
        } else {
            // Fallback: try to get any visible place information
            data.name = this.findElementText([
                'h3:first-child',
                '.place-name',
                '[data-attrid="title"]'
            ]);

            data.address = this.findElementText([
                '.address-text',
                '.location',
                '[data-attrid*="address"]'
            ]);
        }

        // Extract coordinates from current map view if available
        const coords = this.extractCoordinatesFromUrl(window.location.href);
        if (coords) {
            data.lat = coords.lat;
            data.lng = coords.lng;
        }

        return data;
    }

    async extractFromGoogleSearch() {
        const data = {};

        data.name = this.findElementText(this.selectors.googleSearch.placeName);
        data.address = this.findElementText(this.selectors.googleSearch.address);

        return data;
    }

    async extractGeneric() {
        const data = {};

        data.name = this.findElementText(this.selectors.generic.placeName);
        data.address = this.findElementText(this.selectors.generic.address);

        // Try to find phone
        const phoneElement = this.findElement(this.selectors.generic.phone);
        if (phoneElement) {
            if (phoneElement.href && phoneElement.href.startsWith('tel:')) {
                data.phone = phoneElement.href.replace('tel:', '');
            } else {
                data.phone = phoneElement.textContent.trim();
            }
        }

        // Try to find website
        const websiteElement = this.findElement(this.selectors.generic.website);
        if (websiteElement && websiteElement.href) {
            data.website = websiteElement.href;
        }

        return data;
    }

    findElement(selectors, context = document) {
        // If selectors is an array, try each selector
        if (Array.isArray(selectors)) {
            for (const selector of selectors) {
                try {
                    const element = context.querySelector(selector);
                    if (element) {
                        return element;
                    }
                } catch (error) {
                    // Skip invalid selectors
                    continue;
                }
            }
            return null;
        } else {
            // Single selector
            try {
                return context.querySelector(selectors);
            } catch (error) {
                return null;
            }
        }
    }

    findElementText(selectors, context = document) {
        const element = this.findElement(selectors, context);
        return element ? element.textContent.trim() : null;
    }

    extractCoordinatesFromUrl(url) {
        // Try different coordinate patterns in URLs
        const patterns = [
            /@(-?\d+\.\d+),(-?\d+\.\d+)/,  // @lat,lng
            /\/(-?\d+\.\d+),(-?\d+\.\d+)/, // /lat,lng
            /q=(-?\d+\.\d+),(-?\d+\.\d+)/  // q=lat,lng
        ];

        for (const pattern of patterns) {
            const match = url.match(pattern);
            if (match) {
                return {
                    lat: parseFloat(match[1]),
                    lng: parseFloat(match[2])
                };
            }
        }

        return null;
    }

    validateAndCleanData(data) {
        // Clean and validate the extracted data

        // Clean strings
        Object.keys(data).forEach(key => {
            if (typeof data[key] === 'string') {
                data[key] = data[key].trim();
                if (data[key] === '') {
                    delete data[key];
                }
            }
        });

        // Validate rating
        if (data.rating && (data.rating < 0 || data.rating > 5)) {
            delete data.rating;
        }

        // Clean phone number
        if (data.phone) {
            data.phone = data.phone.replace(/[^\d+\-\s()]/g, '');
        }

        // Ensure we have at least minimal data - be more lenient for search pages
        const hasMinimalData = data.name || data.place_id || data.address || data.phone || data.website;
        if (!hasMinimalData) {
            throw new Error('Insufficient data extracted from page - no identifiable information found');
        }

        return data;
    }

    async scrapeCurrentPage(settings) {
        try {
            const data = await this.extractPlaceData();

            // Send to background script for processing
            chrome.runtime.sendMessage({
                action: 'scrapeData',
                data: data
            });

            // Show success indicator
            this.showNotification('Data scraped successfully!', 'green');

            return data;
        } catch (error) {
            console.error('Scraping failed:', error);
            this.showNotification('Scraping failed: ' + error.message, 'red');
            throw error;
        }
    }

    async manualScrape() {
        try {
            const data = await this.extractPlaceData();

            // Send to background script
            chrome.runtime.sendMessage({
                action: 'scrapeData',
                data: data
            });

            this.showNotification('Data sent to queue!', 'green');
        } catch (error) {
            this.showNotification('Failed to scrape: ' + error.message, 'red');
        }
    }

    showNotification(message, color) {
        // Remove existing notifications
        const existing = document.getElementById('mafaza-notification');
        if (existing) existing.remove();

        // Create new notification
        const notification = document.createElement('div');
        notification.id = 'mafaza-notification';
        notification.innerHTML = `🔍 ${message}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: ${color === 'green' ? '#27ae60' : '#e74c3c'};
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: slideDown 0.3s ease;
        `;

        document.body.appendChild(notification);

        // Add animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideDown {
                from { transform: translateX(-50%) translateY(-100%); opacity: 0; }
                to { transform: translateX(-50%) translateY(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideDown 0.3s ease reverse';
                setTimeout(() => notification.remove(), 300);
            }
        }, 3000);
    }

    // Utility method to wait for elements to load
    waitForElement(selector, timeout = 5000) {
        return new Promise((resolve, reject) => {
            const element = document.querySelector(selector);
            if (element) {
                resolve(element);
                return;
            }

            const observer = new MutationObserver(() => {
                const element = document.querySelector(selector);
                if (element) {
                    observer.disconnect();
                    resolve(element);
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            setTimeout(() => {
                observer.disconnect();
                reject(new Error(`Element ${selector} not found within ${timeout}ms`));
            }, timeout);
        });
    }

    // Method to handle dynamic content loading
    observePageChanges() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    // Check if new content might contain place data
                    const hasPlaceContent = mutation.addedNodes.some(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            return node.querySelector('h1, .place-name, .address, .phone');
                        }
                        return false;
                    });

                    if (hasPlaceContent) {
                        // Try to extract data again
                        setTimeout(() => this.extractPlaceData(), 1000);
                    }
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}

// Initialize content scraper
const mafazaScraper = new MafazaContentScraper();

// Export for debugging
if (window.location.hostname === 'localhost' || window.location.protocol === 'chrome-extension:') {
    window.mafazaScraper = mafazaScraper;
}
