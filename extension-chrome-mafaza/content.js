// Content script for Mafaza Fortuna Chrome Extension
// Simplified scraper using direct Google Maps extraction method

class MafazaContentScraper {
    constructor() {
        this.settings = {};
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
            this.injectDebugPanel();
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

                case 'downloadCSV':
                    this.downloadCSV(message.data);
                    sendResponse({ success: true });
                    break;

                case 'debugImages':
                    const debugResult = this.debugImageExtraction();
                    sendResponse({ success: true, images: debugResult });
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

                // If this is bulk search results, return directly without additional validation
                if (data && data.bulk_results) {
                    return data;
                }
            } else if (url.includes('google.com/search')) {
                data = await this.extractFromGoogleSearch();
            } else {
                data = await this.extractGeneric();
            }

        // Add metadata
        data.source = 'chrome_extension';
        data.scraped_at = new Date().toISOString();
        data.page_url = url;

        // Add raw content
        data.raw_text = this.extractRawText();
        data.raw_html = this.extractRawHtml();

        // Validate extracted data
        data = this.validateAndCleanData(data);

            // Log data in a more readable format to avoid truncation
            console.log('📊 [EXTRACTED] Place Data Summary:');
            console.log('├── Name:', data.name || 'Not found');
            console.log('├── Place ID:', data.place_id || 'Not found');
            console.log('├── Address:', data.address ? data.address.substring(0, 50) + '...' : 'Not found');
            console.log('├── Phone:', data.phone || 'Not found');
            console.log('├── Rating:', data.rating || 'Not found');
            console.log('├── Reviews:', data.review_count || 'Not found');
            console.log('├── Opening Hours:', data.opening_hours ? data.opening_hours.substring(0, 50) + '...' : 'Not found');
            console.log('├── Images:', data.image_1 ? 'Found' : 'Not found');
            console.log('└── Raw HTML length:', data.raw_html ? data.raw_html.length : 0, 'characters');

            // Also log the full object for debugging
            try {
                console.log('📋 [FULL DATA]:', JSON.stringify(data, null, 2));
            } catch (error) {
                console.warn('Could not stringify data:', error);
                console.log('📋 [FULL DATA]:', data);
            }
            return data;

        } catch (error) {
            console.error('Failed to extract place data:', error);
            throw error;
        }
    }

    async extractFromGoogleMaps() {
        const url = window.location.href;
        let data = {};

        try {
            if (url.includes('/place/')) {
                // Individual place page
                return await this.extractFromGoogleMapsPlacePage();
            } else if (url.includes('/search')) {
                // Search results page - bulk scraping mode
                return await this.extractFromGoogleMapsSearchResults();
            } else {
                // Fallback to generic extraction
                return await this.extractGeneric();
            }
        } catch (error) {
            console.error('Error in extractFromGoogleMaps:', error);
            throw error;
        }
    }

    async extractFromGoogleMapsSearchResults() {
        // Revised Google Maps bulk scraping using the improved auto-scroll method
        console.log("%c V31: AUTO-SCROLL & WEB-LINK HUNTER ", "background: #27ae60; color: #ffffff; font-size: 15px; font-weight: bold;");

        this.updateDebugStatus('Initializing scraper...', 'info');
        this.addDebugLog('Starting bulk scraping process');

        const scrollContainer = document.querySelector('div[role="feed"]');
        if (!scrollContainer) {
            console.error("Elemen scroll tidak ditemukan. Pastikan Anda berada di panel hasil pencarian Google Maps.");
            this.updateDebugStatus('Error: Scroll container not found', 'error');
            this.addDebugLog('Error: Could not find scroll container');
            return {
                bulk_results: true,
                places_count: 0,
                places: [],
                search_url: window.location.href,
                extracted_at: new Date().toISOString(),
                error: "Scroll container not found"
            };
        }

        // --- FUNGSI AUTO SCROLL ---
        this.updateDebugStatus('Auto-scrolling...', 'info');
        this.addDebugLog('Starting auto-scroll to load all results');

        async function autoScroll() {
            let lastHeight = 0;
            let currentHeight = scrollContainer.scrollHeight;

            while (lastHeight < currentHeight) {
                lastHeight = scrollContainer.scrollHeight;
                scrollContainer.scrollTo(0, lastHeight);
                console.log("Scrolling... Mengambil data baru...");
                await new Promise(resolve => setTimeout(resolve, 3000)); // Tunggu 3 detik untuk loading
                currentHeight = scrollContainer.scrollHeight;
            }
            console.log("Selesai Scroll: Semua tempat telah dimuat.");
        }

        // Jalankan Scroll dahulu
        await autoScroll();

        this.updateDebugStatus('Extracting data...', 'warning');
        this.addDebugLog('Auto-scroll completed, now extracting place data');

        // --- PROSES EKSTRAKSI DATA ---
        let items = document.querySelectorAll('div[role="article"]');
        let results = [];

        items.forEach((item, i) => {
            let nameElem = item.querySelector('.fontHeadlineSmall');
            if (!nameElem) return;

            let name = nameElem.innerText.trim();
            let fullText = item.innerText.replace(/\n/g, ' | ');

            // 1. Rating & Review
            let ratingMatch = fullText.match(/(\d[.,]\d)\s*\(([\d,.]+)\)/);
            let rating = ratingMatch ? ratingMatch[1].replace(',', '.') : "0";
            let reviews = ratingMatch ? ratingMatch[2].replace(/[.,]/g, '') : "0";

            // 2. Link Hunter (Website)
            let website = "NULL";
            let allLinks = Array.from(item.querySelectorAll('a[href]'));
            for (let link of allLinks) {
                let href = link.href;
                if (!href.includes('googleusercontent.com') &&
                    !href.includes('google.co.id/maps') &&
                    !href.includes('search?') &&
                    href.startsWith('http')) {
                    website = href;
                    break;
                }
            }

            // 3. Ekstraksi Kategori
            let segments = fullText.split('·').map(s => s.trim());
            let rawCatSegment = segments.find(s => s.includes('(') || s.length > 3) || segments[0];
            let cleanCategory = rawCatSegment
                .replace(name, '').replace(/\(\d+[\d,.]*\)/, '').replace(/\d[.,]\d/, '')
                .replace(/Tidak ada ulasan/g, '').replace(/Tutup pukul.*/i, '').replace(/Buka pukul.*/i, '')
                .replace(/\bBuka\b/gi, '').replace(/\bRute\b/gi, '').replace(/\|/g, '')
                .replace(/[^\x20-\x7E]/g, '').trim();

            // 4. Alamat & Telepon
            let address = segments.find(s =>
                s.includes('Jl.') || s.includes('Dusun') || s.includes('Desa') ||
                s.includes('Kec.') || s.includes('Kab.') || /[A-Z0-9]{4}\+/.test(s)
            ) || "NULL";
            let phoneMatch = fullText.match(/(?:\+62|08)[0-9\-\s]{8,20}/);

            // 5. URL Maps & Koordinat
            let mapsLinkElem = item.querySelector('a[href*="/maps/place/"]');
            let mapsUrl = mapsLinkElem ? mapsLinkElem.href.split('?')[0] : "NULL";
            let coordMatch = mapsUrl.match(/!3d([-.\d]+)!4d([-.\d]+)/);

            // 6. Extract place_id from Maps URL or generate unique fallback
            let placeId = mapsUrl.match(/place\/([^\/]+)\//)?.[1] || null;
            if (!placeId || placeId === "NULL") {
                // Generate unique place_id based on name, address, and coordinates
                const uniqueString = `${name}_${address}_${coordMatch ? coordMatch[1] : '0'}_${coordMatch ? coordMatch[2] : '0'}`;
                placeId = btoa(unescape(encodeURIComponent(uniqueString))).replace(/[^a-zA-Z0-9]/g, '').substring(0, 50);
            }

            results.push({
                place_id: placeId,
                name: name,
                lat: coordMatch ? coordMatch[1] : "0",
                lng: coordMatch ? coordMatch[2] : "0",
                maps_url: mapsUrl,
                rating: rating,
                review_count: reviews,
                category: cleanCategory || "NULL",
                address: address.replace(/\|/g, '').replace(/Buka$/, '').trim(),
                phone: phoneMatch ? phoneMatch[0].replace(/[^0-9+]/g, '') : "NULL",
                website: website,
                parser_version: "V31-AUTO-ID",
                last_scraped_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
            });

            console.log(`✅ [${i+1}] ${name} | Place ID: ${placeId} | Web: ${website}`);
        });

        console.table(results);
        window.scrapedData = results;
        console.log(`%c BERHASIL! Mendapatkan ${results.length} data.`, "color: #2ecc71; font-weight: bold;");

        // Update debug panel with final results
        this.updateDebugStatus('Data extracted successfully!', 'success');
        this.updateDebugCount(results.length);
        this.addDebugLog(`Successfully extracted ${results.length} places`);
        this.addDebugLog('Sending data to background script in chunks...');

        // Send data in chunks to avoid message size limits
        const chunkSize = 10;
        let sentChunks = 0;
        const totalChunks = Math.ceil(results.length / chunkSize);

        for (let i = 0; i < results.length; i += chunkSize) {
            const chunk = results.slice(i, i + chunkSize);
            const chunkIndex = Math.floor(i / chunkSize) + 1;

            try {
                await new Promise((resolve, reject) => {
                    chrome.runtime.sendMessage({
                        action: 'scrapeData',
                        data: {
                            bulk_results: true,
                            places_count: results.length,
                            places: chunk,
                            chunk_info: {
                                index: chunkIndex,
                                total: totalChunks,
                                size: chunk.length
                            },
                            search_url: window.location.href,
                            extracted_at: new Date().toISOString()
                        }
                    }, (response) => {
                        if (chrome.runtime.lastError) {
                            reject(new Error(chrome.runtime.lastError.message));
                        } else if (response && response.success) {
                            sentChunks++;
                            this.addDebugLog(`Chunk ${chunkIndex}/${totalChunks} sent (${chunk.length} places)`);
                            resolve(response);
                        } else {
                            reject(new Error('Background script rejected chunk'));
                        }
                    });
                });

                // Small delay between chunks to avoid overwhelming
                if (i + chunkSize < results.length) {
                    await new Promise(resolve => setTimeout(resolve, 200));
                }

            } catch (error) {
                this.addDebugLog(`Error sending chunk ${chunkIndex}: ${error.message}`);
                this.updateDebugStatus('Error sending data', 'error');
                throw error;
            }
        }

        this.addDebugLog(`All ${sentChunks} chunks sent successfully`);
        this.updateDebugStatus('All data sent to background!', 'success');

        // Return summary for compatibility
        return {
            bulk_results: true,
            places_count: results.length,
            chunks_sent: sentChunks,
            search_url: window.location.href,
            extracted_at: new Date().toISOString()
        };
    }

    findSearchResultCards() {
        // Find place cards in Google Maps search results
        const cardSelectors = [
            '[role="listitem"]',  // Individual result items
            '.section-result',    // Result containers
            '.place-result',      // Place result cards
            '[data-result-index]', // Indexed results
            '.Nv2PK'              // Result card containers
        ];

        let allCards = [];

        cardSelectors.forEach(selector => {
            try {
                const cards = document.querySelectorAll(selector);
                cards.forEach(card => {
                    // Avoid duplicates
                    if (!allCards.includes(card)) {
                        allCards.push(card);
                    }
                });
            } catch (error) {
                // Skip invalid selectors
            }
        });

        // Filter to only place/business results (not ads, etc.)
        const placeCards = allCards.filter(card => {
            const text = card.textContent.toLowerCase();
            // Look for indicators that this is a business/place result
            return text.includes('buka') || text.includes('tutup') ||
                   text.includes('rating') || text.includes('ulasan') ||
                   card.querySelector('h3, .place-name, [data-attrid*="title"]');
        });

        return placeCards.slice(0, 20); // Limit to 20 results
    }

    async extractPlaceFromCard(card, index) {
        // Extract place data from a single search result card
        const data = {
            source: 'chrome_extension',
            scraped_at: new Date().toISOString(),
            search_result_index: index + 1,
            page_url: window.location.href
        };

        try {
            // Extract name - Google Maps search result cards have specific structure
            // Try multiple approaches for search result cards
            const nameSelectors = [
                '.fontHeadlineSmall', // Main place name in search results
                '.qBF1Pd', // Alternative place name class
                'h3', // Fallback heading
                '[role="heading"]', // ARIA heading
                '.place-name', // Generic place name
                'a[aria-label]', // Link with aria-label
                '.section-result-title' // Result title
            ];

            for (const selector of nameSelectors) {
                const nameElement = card.querySelector(selector);
                if (nameElement) {
                    const nameText = this.cleanUnicodeText(nameElement.textContent.trim());
                    // Filter out generic text like "Hasil" or very short text
                    if (nameText && nameText !== 'Hasil' && nameText.length > 2) {
                        data.name = nameText;
                        break;
                    }
                }
            }

            // Extract combined info (Google search results mix name, rating, category, address, phone)
            const combinedInfoSelectors = [
                '.fontBodyMedium', // Main info text in search results
                '.LrzXr', // Specific combined info class
                '[aria-label*="address"]', // ARIA labeled
                '.section-result-content' // Result content
            ];

            let combinedText = '';
            for (const selector of combinedInfoSelectors) {
                const element = card.querySelector(selector);
                if (element) {
                    combinedText = this.cleanUnicodeText(element.textContent.trim());
                    if (combinedText && combinedText.length > 10) {
                        break;
                    }
                }
            }

            // Parse the combined text to extract different pieces of information
            if (combinedText) {
                // Extract rating and review count pattern like "4,9(17)" or "4.5(32)"
                const ratingMatch = combinedText.match(/(\d+[,.]\d+)\s*\(\s*(\d+(?:[,.]\d+)*)\s*\)/);
                if (ratingMatch) {
                    data.rating = parseFloat(ratingMatch[1].replace(',', '.'));
                    data.review_count = parseInt(ratingMatch[2].replace(/[,.]/g, ''));
                }

                // Extract phone number pattern (Indonesian format: 08xx-xxxx-xxxx)
                const phoneMatches = combinedText.match(/(\+?62|0)[8-9]\d{1,2}[-.\s]?\d{3,4}[-.\s]?\d{3,4}/g);
                if (phoneMatches && phoneMatches.length > 0) {
                    // Take the last phone number found (usually the main one)
                    data.phone = phoneMatches[phoneMatches.length - 1].replace(/\s+/g, '').replace(/[-.]/g, '-');
                }

                // Remove rating pattern from text before extracting category
                let cleanedText = combinedText.replace(/\d+[,.]\d+\s*\(\s*\d+(?:[,.]\d+)*\s*\)/g, '');
                
                // Extract category - look for complete business type keywords
                const categoryKeywords = [
                    'Toko bahan makanan', 'Toko Swalayan', 'Toko Pakaian', 'Toko Roti',
                    'Toko Kelontong', 'Toko Elektronik', 'Toko Bangunan', 'Toko Sembako',
                    'Toko Alat Tulis', 'Toko Mainan', 'Toko Buah', 'Toko Kue',
                    'Restoran', 'Rumah Makan', 'Warung Makan', 'Kedai Kopi',
                    'Hotel', 'Penginapan', 'Bank', 'ATM', 'Apotek', 'Klinik',
                    'Minimarket', 'Supermarket', 'Warung', 'Kafe', 'Cafe',
                    'Bengkel', 'Salon', 'Barbershop', 'Laundry', 'Fotokopi',
                    'Toko Hp', 'Toko Komputer', 'Toko Sepatu', 'Toko Tas',
                    'Dealer', 'Showroom', 'Gudang', 'Pabrik', 'Kantor',
                    'Sekolah', 'Universitas', 'Rumah Sakit', 'Puskesmas',
                    'Masjid', 'Gereja', 'Vihara', 'Pura', 'SPBU', 'Pompa Bensin'
                ];
                
                // Try to find category from known keywords (case-insensitive)
                for (const keyword of categoryKeywords) {
                    const regex = new RegExp(keyword, 'i');
                    if (regex.test(cleanedText)) {
                        data.category = keyword;
                        break;
                    }
                }
                
                // If no exact match, try to extract generic patterns
                if (!data.category) {
                    const genericPatterns = [
                        /(?:^|\s)(Toko\s+\w+(?:\s+\w+)?)/i,
                        /(?:^|\s)(Warung\s+\w+)/i,
                        /(?:^|\s)(Rumah\s+Makan\s*\w*)/i
                    ];
                    
                    for (const pattern of genericPatterns) {
                        const match = cleanedText.match(pattern);
                        if (match && match[1] && match[1].length > 5 && 
                            !match[1].includes('Buka') && !match[1].includes('Tutup')) {
                            data.category = match[1].trim();
                            break;
                        }
                    }
                }

                // Extract opening hours - look for patterns like "Buka · Tutup pukul 21.00"
                const hoursMatch = combinedText.match(/(Buka\s*[·⋅]?\s*Tutup\s+pukul\s+\d{1,2}(?:[.:]\d{1,2})?)/i);
                if (hoursMatch) {
                    data.opening_hours = hoursMatch[1];
                }

                // Extract address - improved logic
                // Combined text structure: "4,9(17)Toko bahan makanan · RCHR+PGQ, Jl. Sidomulyo · Buka"
                let addressText = '';
                
                // Split by separator (·) and analyze each part
                const parts = combinedText.split(/\s*[·⋅]\s*/);
                console.log('🏠 [DEBUG] Address extraction - text parts:', parts);
                
                for (const part of parts) {
                    const trimmedPart = part.trim();
                    
                    // Skip if it contains rating pattern
                    if (/^\d+[,.]\d+\s*\(\d+\)/.test(trimmedPart)) continue;
                    
                    // Skip if it's a category keyword
                    if (/^(Toko|Warung|Restoran|Rumah\s+Makan|Hotel|Bank|Apotek|Klinik|Minimarket|Supermarket)\s/i.test(trimmedPart)) continue;
                    
                    // Skip if it starts with Buka/Tutup
                    if (/^(Buka|Tutup)/i.test(trimmedPart)) continue;
                    
                    // Skip if it's a phone number
                    if (/^(\+?62|0)[8-9]\d/.test(trimmedPart)) continue;
                    
                    // Check if this looks like an address (has street name, plus code, or number)
                    const isAddress = /R[A-Z]{2,}\+\w+/.test(trimmedPart) ||  // Plus code
                                     /Jl\.|Jalan|No\.|Desa|Kec\.|Kel\.|Kab\./i.test(trimmedPart) ||  // Street indicators
                                     /\d+[A-Za-z]?/.test(trimmedPart);  // Has numbers
                    
                    if (isAddress && trimmedPart.length > 5 && trimmedPart.length < 150) {
                        addressText = trimmedPart;
                        break;
                    }
                }
                
                // Fallback: try to find Plus Code or street name directly
                if (!addressText) {
                    // Look for Plus Code pattern
                    const plusCodeMatch = combinedText.match(/([A-Z]{2,}\d*\+[A-Z0-9]+(?:,?\s*[^·⋅]+)?)/);
                    if (plusCodeMatch) {
                        let extracted = plusCodeMatch[1].trim();
                        // Remove trailing Buka/Tutup
                        extracted = extracted.replace(/\s*(Buka|Tutup).*$/i, '').trim();
                        if (extracted.length > 5) {
                            addressText = extracted;
                        }
                    }
                }
                
                // Fallback: look for street name
                if (!addressText) {
                    const streetMatch = combinedText.match(/(Jl\.|Jalan)\s+[^·⋅]+/i);
                    if (streetMatch) {
                        let extracted = streetMatch[0].trim();
                        extracted = extracted.replace(/\s*(Buka|Tutup).*$/i, '').trim();
                        if (extracted.length > 5) {
                            addressText = extracted;
                        }
                    }
                }

                // Final validation and assignment
                if (addressText && addressText.trim() &&
                    addressText !== data.name &&
                    addressText !== data.category &&
                    !addressText.includes('Buka') &&
                    !addressText.includes('Tutup') &&
                    !/^(\+?62|0)[8-9]/.test(addressText)) {
                    data.address = addressText.trim();
                    console.log('🏠 [DEBUG] Extracted address:', data.address);
                }
            }

            // Don't override phone if already extracted from combined text
            if (!data.phone) {
                // Extract phone - may not be visible in search results, but check anyway
                const phoneSelectors = [
                    '[href^="tel:"]',
                    '.phone-text',
                    '[aria-label*="phone"]',
                    '.section-result-phone'
                ];

                for (const selector of phoneSelectors) {
                    const phoneElement = card.querySelector(selector);
                    if (phoneElement) {
                        if (phoneElement.href && phoneElement.href.startsWith('tel:')) {
                            data.phone = phoneElement.href.replace('tel:', '');
                            break;
                        } else if (phoneElement.textContent) {
                            const phoneText = phoneElement.textContent.trim();
                            if (/[\d\-\(\)\s\+]{7,}/.test(phoneText)) {
                                data.phone = phoneText;
                                break;
                            }
                        }
                    }
                }
            }

            // Extract website - usually not shown in basic search results
            const websiteSelectors = [
                'a[href^="http"]:not([href*="google"]):not([href*="maps"])',
                '.website-text',
                '[aria-label*="website"]'
            ];

            for (const selector of websiteSelectors) {
                const websiteElement = card.querySelector(selector);
                if (websiteElement && websiteElement.href) {
                    const href = websiteElement.href.toLowerCase();
                    // Make sure it's not Google or Maps related
                    if (!href.includes('google') && !href.includes('maps.google')) {
                        data.website = websiteElement.href;
                        break;
                    }
                }
            }

            // Try to extract place ID from links - this is most reliable
            const links = card.querySelectorAll('a[href]');
            for (const link of links) {
                const href = link.href;
                if (href && href.includes('/place/')) {
                    data.place_url = href;
                    // Try to extract Place ID from the URL
                    data.place_id = this.extractGooglePlaceId(href);
                    break;
                }
            }

            // If no place ID from links, try to find it in the card's data attributes or other sources
            if (!data.place_id) {
                // Sometimes the place ID is in data attributes
                const placeIdAttr = card.getAttribute('data-place-id') ||
                                  card.getAttribute('data-pid') ||
                                  card.getAttribute('data-result-id');
                if (placeIdAttr) {
                    data.place_id = placeIdAttr;
                }
            }

            // Extract coordinates if available (usually not in basic search results)
            if (data.place_id) {
                data.lat = null; // Would need additional parsing from URL data
                data.lng = null;
            }

            // Debug: Log what we found vs what we didn't
            console.log(`🔍 [CARD ${index + 1}] Extracted data:`, {
                name: data.name || 'NOT FOUND',
                address: data.address || 'NOT FOUND',
                rating: data.rating || 'NOT FOUND',
                phone: data.phone || 'NOT FOUND',
                website: data.website || 'NOT FOUND',
                place_id: data.place_id || 'NOT FOUND'
            });

        } catch (error) {
            console.warn(`❌ Error extracting data from card ${index}:`, error);
        }

        return data;
    }

    extractRatingFromCard(card, data) {
        // Extract rating from search result card
        try {
            const ratingSelectors = ['.rating-text', '[aria-label*="rating"]', '.stars-container'];
            for (const selector of ratingSelectors) {
                const ratingElement = card.querySelector(selector);
                if (ratingElement) {
                    const text = ratingElement.textContent.trim();

                    // Look for patterns like "4.8 (28)" or "4.8 28 ulasan"
                    const ratingPatterns = [
                        /(\d+[,.]\d+)\s*\(\s*(\d+(?:[,.]\d+)*)\s*\)/,  // "4.8 (28)"
                        /(\d+[,.]\d+)\s+(\d+(?:[,.]\d+)*)/,             // "4.8 28"
                        /(\d+[,.]\d+)/                                  // Just "4.8"
                    ];

                    for (const pattern of ratingPatterns) {
                        const match = text.match(pattern);
                        if (match) {
                            data.rating = parseFloat(match[1].replace(',', '.'));
                            if (match[2]) {
                                data.review_count = parseInt(match[2].replace(/[,.]/g, ''));
                            }
                            return; // Found rating, exit
                        }
                    }
                }
            }
        } catch (error) {
            console.warn('Error extracting rating from card:', error);
        }
    }

    validatePlaceData(data, isBulkSearch = false) {
        // Validate that we have minimum required data for a place
        // More lenient validation for bulk search results

        if (!data || typeof data !== 'object') {
            return false;
        }

        // For bulk search results, be more lenient - just need some basic info
        if (isBulkSearch) {
            return data.place_id || data.name || data.address || data.phone || data.website;
        }

        // For individual place pages, require more complete data
        return (data.name || data.place_id) &&
               (data.address || data.phone || data.website);
    }

    async extractFromGoogleMapsPlacePage() {
        const data = {};
        const currentUrl = window.location.href;

        // Wait for page to fully load
        await this.waitForElement('h1', 3000);

        // Extract place name - prioritize specific place name selectors
        data.name = this.findElementText([
            'h1.place-name',
            '[data-attrid="title"]',
            '.qBF1Pd.fontHeadlineSmall', // Specific class for place name
            'h1:not(.fontTitleLarge)', // Avoid search result titles
            '[data-testid="place-name"]',
            '.place-title h1'
        ]);

        // If name is still generic like "Hasil", try to get the actual business name
        if (!data.name || data.name === 'Hasil' || data.name.length < 3) {
            // Look for business names in the result list
            const businessNames = document.querySelectorAll('.qBF1Pd.fontHeadlineSmall');
            for (const nameElement of businessNames) {
                const name = nameElement.textContent.trim();
                if (name && name !== 'Hasil' && name.length > 2) {
                    data.name = name;
                    break;
                }
            }
        }

        // Extract address
        data.address = this.findElementText([
            '[data-item-id*="address"]',
            '.section-info-text[data-attrid="kc:/location/location:address"]',
            '[jsaction*="address"]',
            '.address-text',
            '.address'
        ]);

        // Extract rating and review count from various sources
        this.extractRatingAndReviews(data);

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

        // Extract opening hours
        data.opening_hours = this.extractOpeningHours();

        // Extract images
        const images = this.extractImages();
        if (images.length > 0) {
            for (let i = 0; i < Math.min(images.length, 4); i++) {
                data[`image_${i + 1}`] = images[i];
            }
        }

        // Try to extract coordinates from URL
        const coords = this.extractCoordinatesFromUrl(currentUrl);
        if (coords) {
            data.lat = coords.lat;
            data.lng = coords.lng;
        }

        // Extract place ID from URL - try to get the real Google Place ID
        data.place_id = this.extractGooglePlaceId(currentUrl);

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

    extractGooglePlaceId(url) {
        // Extract the real Google Place ID from Google Maps URLs
        console.log('🆔 [DEBUG] Extracting Google Place ID from URL...');

        try {
            // Method 1: Extract from URL data parameter (most reliable)
            // Google Maps URLs contain data like: !1s0x2dd68b6fea476d9d:0xf6a8efc8f30ec1f5
            const dataParamMatch = url.match(/data=([^&]+)/);
            if (dataParamMatch) {
                const dataParam = decodeURIComponent(dataParamMatch[1]);
                console.log('🆔 [DEBUG] Found data parameter:', dataParam);

                // Look for the place ID pattern in the data
                // Format: !1s[PLACE_ID]! or similar
                const placeIdPatterns = [
                    /!1s([^!]+)!/g,  // !1sPLACE_ID!
                    /!3m6!1s([^!]+)!/g, // !3m6!1sPLACE_ID!
                    /!4m7!3m6!1s([^!]+)!/g // !4m7!3m6!1sPLACE_ID!
                ];

                for (const pattern of placeIdPatterns) {
                    const matches = dataParam.match(pattern);
                    if (matches) {
                        for (const match of matches) {
                            const placeId = match.replace(/![^!]*!1s/, '').replace(/!.*/, '');
                            if (placeId && placeId.length > 10 && /^\d+x[\da-f]+(?::0x[\da-f]+)?$/.test(placeId)) {
                                console.log('🆔 [DEBUG] Extracted Place ID from data param:', placeId);
                                return placeId;
                            }
                        }
                    }
                }
            }

            // Method 2: Fallback to URL path extraction (less reliable)
            const pathMatch = url.match(/\/place\/([^\/@]+)/);
            if (pathMatch) {
                const pathPlaceId = decodeURIComponent(pathMatch[1]);
                console.log('🆔 [DEBUG] Fallback to URL path Place ID:', pathPlaceId);

                // If it looks like a real Place ID (contains hex), use it
                if (/^\d+x[\da-f]+(?::0x[\da-f]+)?$/.test(pathPlaceId)) {
                    return pathPlaceId;
                }

                // Otherwise, it's probably just the place name
                console.log('🆔 [DEBUG] URL path contains place name, not Place ID');
            }

            // Method 3: Try to find Place ID in page data attributes
            const dataAttributes = [
                '[data-place-id]',
                '[data-pid]',
                '[data-google-place-id]'
            ];

            for (const attr of dataAttributes) {
                try {
                    const element = document.querySelector(attr);
                    if (element) {
                        const placeId = element.getAttribute('data-place-id') ||
                                      element.getAttribute('data-pid') ||
                                      element.getAttribute('data-google-place-id');
                        if (placeId && /^\d+x[\da-f]+(?::0x[\da-f]+)?$/.test(placeId)) {
                            console.log('🆔 [DEBUG] Found Place ID in data attribute:', placeId);
                            return placeId;
                        }
                    }
                } catch (error) {
                    continue;
                }
            }

            // Method 4: Generate coordinate-based ID as last resort
            const coords = this.extractCoordinatesFromUrl(url);
            if (coords) {
                // Create a coordinate-based identifier
                const coordId = `${coords.lat.toFixed(6)},${coords.lng.toFixed(6)}`;
                console.log('🆔 [DEBUG] Using coordinate-based ID as fallback:', coordId);
                return coordId;
            }

        } catch (error) {
            console.warn('🆔 [DEBUG] Error extracting Place ID:', error);
        }

        console.log('🆔 [DEBUG] No valid Place ID found, using fallback');
        return null;
    }

    validateAndCleanData(data) {
        // Clean and validate the extracted data

        // Clean strings and remove unicode characters
        Object.keys(data).forEach(key => {
            if (typeof data[key] === 'string') {
                data[key] = this.cleanUnicodeText(data[key]);
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

    extractRawText() {
        // Extract raw text content from the main content area
        // Focus on relevant sections to avoid too much noise
        const mainContentSelectors = [
            '[role="main"]',
            '.section-layout',
            '.place-details',
            'main',
            'article',
            '#content',
            '.content'
        ];

        let rawText = '';

        // Try to get text from main content areas first
        for (const selector of mainContentSelectors) {
            const element = document.querySelector(selector);
            if (element) {
                // Get text content but clean it up
                const text = element.textContent || element.innerText || '';
                if (text.length > rawText.length) {
                    rawText = text;
                }
            }
        }

        // Fallback to body text if no main content found
        if (!rawText || rawText.length < 100) {
            rawText = document.body.textContent || document.body.innerText || '';
        }

        // Clean up the text: remove excessive whitespace, normalize line breaks
        return rawText
            .replace(/\s+/g, ' ')  // Replace multiple whitespace with single space
            .replace(/\n\s*\n/g, '\n')  // Remove empty lines
            .trim()
            .substring(0, 10000);  // Limit to 10k characters
    }

    extractImages(debugMode = false) {
        console.log('🔍 [DEBUG] Starting image extraction...');

        const results = {
            urlPattern: [],
            dataAttributes: [],
            semanticSelectors: [],
            jsonParsing: [],
            xpathQueries: [],
            combined: new Set()
        };

        // METHOD 1: URL PATTERN MATCHING (Most Robust)
        console.log('📋 [DEBUG] Testing URL Pattern Matching...');
        results.urlPattern = this.addImagesByUrlPattern(results.combined, debugMode);

        // METHOD 2: Data attributes (More stable than CSS classes)
        console.log('📋 [DEBUG] Testing Data Attributes...');
        results.dataAttributes = this.addImagesByDataAttributes(results.combined, debugMode);

        // METHOD 3: Semantic selectors (Fallback)
        console.log('📋 [DEBUG] Testing Semantic Selectors...');
        results.semanticSelectors = this.addImagesBySemanticSelectors(results.combined, debugMode);

        // METHOD 4: JSON Data Parsing (Very Robust)
        console.log('📋 [DEBUG] Testing JSON Data Parsing...');
        results.jsonParsing = this.addImagesByJsonParsing(results.combined, debugMode);

        // METHOD 5: XPath Queries (Alternative to CSS selectors)
        console.log('📋 [DEBUG] Testing XPath Queries...');
        results.xpathQueries = this.addImagesByXPath(results.combined, debugMode);

        // Final filtering and validation with size preferences
        console.log('🎯 [DEBUG] Combined set size before filtering:', results.combined.size);
        console.log('🎯 [DEBUG] Combined set contents:', Array.from(results.combined));

        const validImages = Array.from(results.combined)
            .filter(url => {
                const isValid = this.isValidImageUrl(url);
                console.log(`🔍 [DEBUG] Filtering ${url}: ${isValid ? 'VALID' : 'INVALID'}`);
                return isValid;
            })
            .filter(url => {
                const isNotIcon = !url.includes('icon') && !url.includes('logo') && !url.includes('avatar');
                console.log(`🔍 [DEBUG] Filtering ${url}: ${isNotIcon ? 'NOT_ICON' : 'IS_ICON'}`);
                return isNotIcon;
            })
            .filter(url => {
                const isNotTiny = !this.isTinyImage(url);
                console.log(`🔍 [DEBUG] Filtering ${url}: ${isNotTiny ? 'NOT_TINY' : 'IS_TINY'}`);
                return isNotTiny;
            })
            // Sort by size preference (larger images first)
            .sort((a, b) => this.getImageSizePreference(b) - this.getImageSizePreference(a))
            .slice(0, 4); // Limit to 4 images max

        console.log('🎯 [DEBUG] Final valid images:', validImages.length, 'images');
        console.log('📸 [DEBUG] Final image URLs:', validImages);

        // Debug output
        if (debugMode) {
            console.log('🎯 [DEBUG] Image Extraction Results:');
            console.log('├── URL Pattern:', results.urlPattern.length, 'images');
            console.log('├── Data Attributes:', results.dataAttributes.length, 'images');
            console.log('├── Semantic Selectors:', results.semanticSelectors.length, 'images');
            console.log('├── JSON Parsing:', results.jsonParsing.length, 'images');
            console.log('├── XPath Queries:', results.xpathQueries.length, 'images');
            console.log('└── Final Combined:', validImages.length, 'images');
        }

        return validImages;
    }

    // Debug method - extract images without sending to server
    debugImageExtraction() {
        console.log('🐛 [DEBUG MODE] Starting comprehensive image extraction test...');
        console.log('📍 Current URL:', window.location.href);

        // Extract all images using debug mode
        const images = this.extractImages(true);

        // Additional debugging info
        console.log('📊 [DEBUG] Additional Analysis:');
        console.log('├── Total <img> elements:', document.querySelectorAll('img').length);
        console.log('├── Google-hosted images:', document.querySelectorAll('img[src*="googleusercontent.com"]').length);
        console.log('├── Maps images:', document.querySelectorAll('img[src*="maps.googleapis.com"]').length);
        console.log('├── Streetview images:', document.querySelectorAll('img[src*="streetviewpixels-pa.googleapis.com"]').length);

        // Show sample of what we found
        if (images.length > 0) {
            console.log('✅ [DEBUG] SUCCESS: Found', images.length, 'valid images');
            images.forEach((url, index) => {
                console.log(`   ${index + 1}. ${url}`);
            });
        } else {
            console.log('❌ [DEBUG] NO IMAGES FOUND - trying fallback methods...');

            // Try even more aggressive methods
            this.debugFallbackMethods();
        }

        return images;
    }

    debugFallbackMethods() {
        console.log('🔍 [DEBUG] Trying aggressive fallback methods...');

        // Method 1: Get ALL images on page
        const allImages = Array.from(document.querySelectorAll('img'))
            .map(img => img.src || img.getAttribute('data-src'))
            .filter(src => src && src.length > 10);

        console.log('├── All images on page:', allImages.length);
        if (allImages.length > 0) {
            console.log('   Sample:', allImages.slice(0, 3));
        }

        // Method 2: Search for Google image URLs in all text content
        const pageText = document.body.textContent || document.body.innerText || '';
        const googleUrls = pageText.match(/https:\/\/[^\s"']*\.(?:jpg|jpeg|png|webp)[^\s"']*/g) || [];
        const googleImageUrls = googleUrls.filter(url =>
            url.includes('googleusercontent.com') ||
            url.includes('maps.googleapis.com') ||
            url.includes('streetviewpixels-pa.googleapis.com')
        );

        console.log('├── Google image URLs in text:', googleImageUrls.length);
        if (googleImageUrls.length > 0) {
            console.log('   Sample:', googleImageUrls.slice(0, 3));
        }

        // Method 3: Check for lazy-loaded images
        const lazyImages = Array.from(document.querySelectorAll('img[data-src], img[data-original], img[data-lazy-src]'))
            .map(img => img.getAttribute('data-src') || img.getAttribute('data-original') || img.getAttribute('data-lazy-src'))
            .filter(src => src && src.length > 10);

        console.log('├── Lazy-loaded images:', lazyImages.length);
        if (lazyImages.length > 0) {
            console.log('   Sample:', lazyImages.slice(0, 3));
        }
    }

    addImagesByUrlPattern(images, debugMode = false) {
        console.log('🔍 [DEBUG] Starting URL Pattern method...');

        // Most robust method - Google URL patterns rarely change
        const urlPatterns = [
            'img[src*="lh3.googleusercontent.com"]',
            'img[src*="maps.googleapis.com"]',
            'img[src*="streetviewpixels-pa.googleapis.com"]',
            'img[src*="googleusercontent.com/p/"]',
            'img[data-src*="lh3.googleusercontent.com"]',
            'img[data-original*="lh3.googleusercontent.com"]'
        ];

        const foundImages = [];
        let totalElements = 0;

        urlPatterns.forEach(pattern => {
            try {
                console.log(`🔍 [DEBUG] Testing pattern: ${pattern}`);
                const imgElements = document.querySelectorAll(pattern);
                console.log(`🔍 [DEBUG] Pattern "${pattern}" found ${imgElements.length} elements`);
                totalElements += imgElements.length;

                imgElements.forEach((img, index) => {
                    const src = img.src || img.getAttribute('data-src') || img.getAttribute('data-original');
                    console.log(`🔍 [DEBUG] Element ${index + 1}: src="${src?.substring(0, 100)}..."`);
                    if (src && src.length > 10) {
                        images.add(src);
                        foundImages.push(src);
                        console.log('   ✅ URL Pattern found valid image:', src.substring(0, 80) + '...');
                    } else {
                        console.log('   ❌ Invalid or empty src');
                    }
                });
            } catch (error) {
                console.warn('❌ Invalid URL pattern:', pattern, error);
            }
        });

        console.log(`🎯 [DEBUG] URL Pattern method result: ${foundImages.length} valid images from ${totalElements} elements`);
        return foundImages;
    }

    addImagesByDataAttributes(images, debugMode = false) {
        // More stable than CSS classes
        const dataSelectors = [
            'img[data-photo-index]',
            'img[data-photo-id]',
            '[data-photo] img',
            '[data-image] img'
        ];

        const foundImages = [];

        dataSelectors.forEach(selector => {
            try {
                const imgElements = document.querySelectorAll(selector);
                imgElements.forEach(img => {
                    const src = img.src || img.getAttribute('data-src') || img.getAttribute('data-original');
                    if (src && src.length > 10) {
                        images.add(src);
                        foundImages.push(src);
                        if (debugMode) console.log('   Data Attribute found:', src);
                    }
                });
            } catch (error) {
                if (debugMode) console.warn('Invalid data selector:', selector, error);
            }
        });

        if (debugMode) console.log(`   → Data Attributes: ${foundImages.length} images found`);
        return foundImages;
    }

    addImagesBySemanticSelectors(images, debugMode = false) {
        // Fallback method - less reliable but better than nothing
        const semanticSelectors = [
            'img[alt*="photo"]',
            'img[alt*="gambar"]',
            '[role="img"] img',
            'figure img'
        ];

        const foundImages = [];

        semanticSelectors.forEach(selector => {
            try {
                const imgElements = document.querySelectorAll(selector);
                imgElements.forEach(img => {
                    const src = img.src || img.getAttribute('data-src') || img.getAttribute('data-original');
                    if (src && src.length > 10) {
                        images.add(src);
                        foundImages.push(src);
                        if (debugMode) console.log('   Semantic Selector found:', src);
                    }
                });
            } catch (error) {
                if (debugMode) console.warn('Invalid semantic selector:', selector, error);
            }
        });

        if (debugMode) console.log(`   → Semantic Selectors: ${foundImages.length} images found`);
        return foundImages;
    }

    addImagesByJsonParsing(images, debugMode = false) {
        // Very robust - parse JSON data that Google embeds in the page
        const foundImages = [];

        try {
            // Look for Google Photos API data or embedded JSON
            const scripts = document.querySelectorAll('script');
            for (const script of scripts) {
                try {
                    const content = script.textContent || script.innerText;
                    if (content && (content.includes('photos') || content.includes('images') || content.includes('lh3.googleusercontent.com'))) {
                        // Try to extract image URLs from JSON content
                        const jsonMatches = content.match(/"(https:\/\/[^\s"']*\.(?:jpg|jpeg|png|webp)[^\s"']*)"/g);
                        if (jsonMatches) {
                            jsonMatches.forEach(match => {
                                const url = match.slice(1, -1); // Remove quotes
                                if (this.isValidImageUrl(url) && !Array.from(images).includes(url)) {
                                    images.add(url);
                                    foundImages.push(url);
                                    if (debugMode) console.log('   JSON Parsing found:', url);
                                }
                            });
                        }
                    }
                } catch (error) {
                    // Skip problematic scripts
                    continue;
                }
            }
        } catch (error) {
            if (debugMode) console.warn('JSON parsing failed:', error);
        }

        if (debugMode) console.log(`   → JSON Parsing: ${foundImages.length} images found`);
        return foundImages;
    }

    addImagesByXPath(images, debugMode = false) {
        // Alternative to CSS selectors using XPath
        const foundImages = [];

        try {
            // XPath queries for images
            const xpathQueries = [
                "//img[contains(@src, 'lh3.googleusercontent.com')]",
                "//img[contains(@src, 'maps.googleapis.com')]",
                "//img[contains(@src, 'streetviewpixels-pa.googleapis.com')]",
                "//img[@data-photo-index]",
                "//*[@data-photo]/img",
                "//img[contains(@alt, 'photo')]"
            ];

            xpathQueries.forEach(xpath => {
                try {
                    const result = document.evaluate(xpath, document, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);
                    for (let i = 0; i < result.snapshotLength; i++) {
                        const img = result.snapshotItem(i);
                        const src = img.src || img.getAttribute('data-src') || img.getAttribute('data-original');
                        if (src && src.length > 10 && !Array.from(images).includes(src)) {
                            images.add(src);
                            foundImages.push(src);
                            if (debugMode) console.log('   XPath found:', src);
                        }
                    }
                } catch (error) {
                    if (debugMode) console.warn('Invalid XPath:', xpath, error);
                }
            });
        } catch (error) {
            if (debugMode) console.warn('XPath queries failed:', error);
        }

        if (debugMode) console.log(`   → XPath Queries: ${foundImages.length} images found`);
        return foundImages;
    }

    extractRatingAndReviews(data) {
        // Extract rating and review count from various sources in Google Maps

        // Try to find rating information in different formats
        const ratingSelectors = [
            '[data-attrid="kc:/location/location:rating"]',
            '.section-star-display',
            '.rating-text',
            '[aria-label*="rating"]',
            '.stars-container',
            '.rating',
            '[data-rating]'
        ];

        // Look for patterns like "4,8(28)" or "4.8 (28 reviews)"
        const ratingPatterns = [
            /(\d+[,.]\d+)\s*\(\s*(\d+(?:[,.]\d+)*)\s*(?:ulasan|reviews?|reviews?)\s*\)/i,
            /(\d+[,.]\d+)\s*\(\s*(\d+(?:[,.]\d+)*)\s*\)/,
            /rating["\s]*:\s*["']?(\d+[,.]\d+)["']?/i,
            /stars["\s]*:\s*["']?(\d+[,.]\d+)["']?/i
        ];

        // Try selectors first
        for (const selector of ratingSelectors) {
            try {
                const element = document.querySelector(selector);
                if (element) {
                    const text = element.textContent.trim();
                    // Look for rating patterns in the text
                    for (const pattern of ratingPatterns) {
                        const match = text.match(pattern);
                        if (match) {
                            data.rating = parseFloat(match[1].replace(',', '.'));
                            if (match[2]) {
                                data.review_count = parseInt(match[2].replace(/[,.]/g, ''));
                            }
                            return; // Found rating, exit
                        }
                    }

                    // Try simpler rating extraction
                    const ratingMatch = text.match(/(\d+[,.]\d*)/);
                    if (ratingMatch && !data.rating) {
                        data.rating = parseFloat(ratingMatch[1].replace(',', '.'));
                    }

                    // Try to find review count separately
                    const reviewMatch = text.match(/(\d+(?:[,.]\d+)*)/g);
                    if (reviewMatch && reviewMatch.length >= 2 && !data.review_count) {
                        // Take the second number as review count
                        data.review_count = parseInt(reviewMatch[1].replace(/[,.]/g, ''));
                    }
                }
            } catch (error) {
                // Skip invalid selectors
                continue;
            }
        }

        // If still not found, search in raw HTML
        if (!data.rating || !data.review_count) {
            const rawHtml = this.extractRawHtml();

            for (const pattern of ratingPatterns) {
                const match = rawHtml.match(pattern);
                if (match) {
                    if (!data.rating) {
                        data.rating = parseFloat(match[1].replace(',', '.'));
                    }
                    if (!data.review_count && match[2]) {
                        data.review_count = parseInt(match[2].replace(/[,.]/g, ''));
                    }
                    break;
                }
            }

            // Look for standalone rating and review numbers
            if (!data.rating) {
                const ratingMatch = rawHtml.match(/(\d+[,.]\d*)\s*(?:bintang|stars?|rating)/i);
                if (ratingMatch) {
                    data.rating = parseFloat(ratingMatch[1].replace(',', '.'));
                }
            }

            if (!data.review_count) {
                const reviewMatch = rawHtml.match(/(\d+(?:[,.]\d+)*)\s*(?:ulasan|reviews?)/i);
                if (reviewMatch) {
                    data.review_count = parseInt(reviewMatch[1].replace(/[,.]/g, ''));
                }
            }
        }

        // Validate rating range
        if (data.rating && (data.rating < 0 || data.rating > 5)) {
            delete data.rating;
        }
    }

    cleanUnicodeText(text) {
        // Remove Google Maps unicode symbols and emojis
        if (!text) return text;

        return text
            .replace(/[\uE000-\uF8FF]|\uD83C[\uDF00-\uDFFF]|\uD83D[\uDC00-\uDDFF]/g, '') // Remove emojis and symbols
            .replace(/\s+/g, ' ') // Normalize whitespace
            .trim();
    }

    extractOpeningHours() {
        console.log('🕐 [DEBUG] Extracting opening hours...');

        // Primary: Semantic selector (most stable)
        let openingHours = this.extractOpeningHoursFromSemanticSelector();

        // Secondary: Fallback to other selectors
        if (!openingHours) {
            console.log('🕐 [DEBUG] Semantic selector failed, trying fallbacks...');
            openingHours = this.extractOpeningHoursFromFallbacks();
        }

        if (openingHours) {
            console.log('🕐 [DEBUG] Opening hours extracted:', openingHours.substring(0, 100) + '...');
        } else {
            console.log('🕐 [DEBUG] No opening hours found');
        }

        return openingHours;
    }

    extractOpeningHoursFromSemanticSelector() {
        // Use the semantic selector provided by user - most stable approach
        const semanticSelectors = [
            '.OqCZI.fontBodyMedium.VrynGf.WVXvdc',  // Primary - user's discovery
            '[jsaction*="openhours"]',                // Secondary - jsaction pattern
            '[aria-label*="opening hours"]',          // Tertiary - accessibility
            'table.eK4R0e.fontBodyMedium',            // Table fallback
            '.t39EBf.GUrTXd'                          // Expanded hours container
        ];

        // Try each selector
        for (const selector of semanticSelectors) {
            try {
                const element = document.querySelector(selector);
                if (element) {
                    console.log(`🕐 [DEBUG] Found opening hours element with selector: ${selector}`);

                    // Try to parse as table structure (most complete)
                    const tableHours = this.parseOpeningHoursTable(element);
                    if (tableHours) {
                        return tableHours;
                    }

                    // Try to find table within this element
                    const tableElement = element.querySelector('table');
                    if (tableElement) {
                        console.log('🕐 [DEBUG] Found table within container element');
                        const nestedTableHours = this.parseOpeningHoursTable(tableElement);
                        if (nestedTableHours) {
                            return nestedTableHours;
                        }
                    }

                    // Fallback to simple text extraction
                    const textHours = this.cleanUnicodeText(element.textContent.trim());
                    if (textHours && textHours.length > 5 && !textHours.includes('Loading')) {
                        return textHours;
                    }
                }
            } catch (error) {
                console.warn(`🕐 [DEBUG] Error with selector ${selector}:`, error);
                continue;
            }
        }

        // Try to find expanded hours content (when user clicks to show full schedule)
        try {
            const expandedHours = document.querySelector('.t39EBf.GUrTXd');
            if (expandedHours) {
                console.log('🕐 [DEBUG] Found expanded hours content');
                const tableElement = expandedHours.querySelector('table');
                if (tableElement) {
                    const expandedTableHours = this.parseOpeningHoursTable(tableElement);
                    if (expandedTableHours) {
                        return expandedTableHours;
                    }
                }
                // Fallback to text content
                const expandedText = this.cleanUnicodeText(expandedHours.textContent.trim());
                if (expandedText && expandedText.length > 10) {
                    return expandedText;
                }
            }
        } catch (error) {
            console.warn('🕐 [DEBUG] Error checking expanded hours:', error);
        }

        return null;
    }

    parseOpeningHoursTable(containerElement) {
        // Parse the table structure to get complete weekly schedule
        try {
            // Find the table within the container
            const table = containerElement.querySelector('table.eK4R0e.fontBodyMedium') ||
                         containerElement.querySelector('table');

            if (!table) {
                console.log('🕐 [DEBUG] No table found in opening hours container');
                return null;
            }

            const rows = table.querySelectorAll('tr.y0skZc');
            console.log(`🕐 [DEBUG] Found ${rows.length} opening hours rows`);

            if (rows.length === 0) {
                return null;
            }

            const schedule = [];

            rows.forEach((row, index) => {
                try {
                    const dayElement = row.querySelector('.ylH6lf');
                    const hoursElement = row.querySelector('.G8aQO');

                    if (dayElement && hoursElement) {
                        const day = this.cleanUnicodeText(dayElement.textContent.trim());
                        const hours = this.cleanUnicodeText(hoursElement.textContent.trim());

                        if (day && hours) {
                            schedule.push(`${day}: ${hours}`);
                        }
                    }
                } catch (error) {
                    console.warn(`🕐 [DEBUG] Error parsing row ${index}:`, error);
                }
            });

            if (schedule.length > 0) {
                // Format as readable schedule
                const formattedSchedule = schedule.join('\n');
                console.log('🕐 [DEBUG] Parsed weekly schedule:', formattedSchedule);
                return formattedSchedule;
            }

        } catch (error) {
            console.warn('🕐 [DEBUG] Error parsing opening hours table:', error);
        }

        return null;
    }

    extractOpeningHoursFromFallbacks() {
        // Fallback methods if semantic selector fails
        const fallbackSelectors = [
            '[data-attrid="kc:/location/location:hours"]',
            '.section-info-text[data-attrid*="hours"]',
            '.opening-hours',
            '.hours-text',
            '[aria-label*="hours"]'
        ];

        for (const selector of fallbackSelectors) {
            try {
                const element = document.querySelector(selector);
                if (element) {
                    const text = this.cleanUnicodeText(element.textContent.trim());
                    if (text && text.length > 5 && !text.includes('Loading')) {
                        console.log(`🕐 [DEBUG] Fallback selector worked: ${selector}`);
                        return text;
                    }
                }
            } catch (error) {
                continue;
            }
        }

        // Last resort: search raw HTML for hours patterns
        const rawHtml = this.extractRawHtml();
        const hoursPatterns = [
            /Buka(?:\s+jam)?\s*[\d:]+\s*-\s*[\d:]+/gi,
            /Tutup(?:\s+jam)?\s*[\d:]+\s*-\s*[\d:]+/gi,
            /\d{1,2}:\d{2}\s*(?:AM|PM|am|pm)?\s*-\s*\d{1,2}:\d{2}\s*(?:AM|PM|am|pm)?/gi
        ];

        for (const pattern of hoursPatterns) {
            const matches = rawHtml.match(pattern);
            if (matches && matches.length > 0) {
                console.log(`🕐 [DEBUG] Found hours pattern in raw HTML: ${matches[0]}`);
                return this.cleanUnicodeText(matches[0]);
            }
        }

        return null;
    }



    isValidImageUrl(url) {
        // Enhanced validation for image URLs, more permissive for Google services
        if (!url || typeof url !== 'string') return false;
        if (!url.startsWith('http://') && !url.startsWith('https://')) return false;

        // Skip tiny tracking pixels and spacers
        if (url.includes('1x1') || url.includes('spacer')) return false;

        // For Google services, be more permissive
        if (url.includes('googleusercontent.com') ||
            url.includes('maps.googleapis.com') ||
            url.includes('streetviewpixels-pa.googleapis.com')) {
            // Google images often don't have traditional extensions or have query params
            return url.length > 20; // Basic length check
        }

        // For other URLs, require traditional image extensions
        return /\.(jpg|jpeg|png|webp|gif)(\?|$)/i.test(url);
    }

    extractRawHtml() {
        // Extract raw HTML from relevant sections
        const mainContentSelectors = [
            '[role="main"]',
            '.section-layout',
            '.place-details',
            'main',
            'article',
            '#content',
            '.content'
        ];

        let rawHtml = '';

        // Try to get HTML from main content areas first
        for (const selector of mainContentSelectors) {
            const element = document.querySelector(selector);
            if (element) {
                const html = element.innerHTML;
                if (html && html.length > rawHtml.length) {
                    rawHtml = html;
                }
            }
        }

        // Fallback to body HTML if no main content found
        if (!rawHtml || rawHtml.length < 200) {
            // Clone body to avoid modifying original
            const bodyClone = document.body.cloneNode(true);

            // Remove script and style elements
            const scripts = bodyClone.querySelectorAll('script, style, noscript');
            scripts.forEach(el => el.remove());

            rawHtml = bodyClone.innerHTML;
        }

        // Limit HTML size and clean up
        return rawHtml.substring(0, 50000);  // Limit to 50k characters
    }

    isTinyImage(url) {
        // Check if image URL indicates a very small/thumbnail image
        if (!url) return false;

        // Check for explicit size parameters (Google style)
        const sizeMatch = url.match(/[&=]w(\d+)/) || url.match(/[&=]h(\d+)/);
        if (sizeMatch) {
            const size = parseInt(sizeMatch[1]);
            // Consider images smaller than 100px as tiny
            if (size < 100) {
                return true;
            }
        }

        // Check for common thumbnail indicators
        if (url.includes('w32') || url.includes('h32') ||
            url.includes('w64') || url.includes('h64') ||
            url.includes('w80') || url.includes('h80')) {
            return true;
        }

        return false;
    }

    getImageSizePreference(url) {
        // Return a preference score for image size (higher = better)
        if (!url) return 0;

        // Extract size from URL parameters
        const widthMatch = url.match(/[&=]w(\d+)/);
        const heightMatch = url.match(/[&=]h(\d+)/);

        if (widthMatch && heightMatch) {
            const width = parseInt(widthMatch[1]);
            const height = parseInt(heightMatch[1]);
            // Prefer larger images, but penalize extremely large ones (likely banners)
            const size = Math.min(width, height);
            if (size > 1000) return 50; // Too big, lower preference
            if (size > 500) return 100; // Large images
            if (size > 200) return 75;  // Medium images
            if (size > 100) return 50;  // Small images
            return 25; // Tiny images
        }

        // If no size info, assume medium preference
        return 60;
    }

    // Method to download CSV directly
    downloadCSV(results) {
        // Convert results to CSV format
        let csvRows = ["Nama;Kategori;Rating;Ulasan;Telepon;Alamat;Link"];

        results.forEach(r => {
            csvRows.push(`"${r.Nama || ''}";"${r.Kategori || ''}";"${r.Rating || ''}";"${r.Ulasan || ''}";"${r.Telepon || ''}";"${r.Alamat || ''}";"${r.Link || ''}"`);
        });

        let csvContent = "\uFEFF" + csvRows.join("\n"); // Add BOM for Excel compatibility
        let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        let url = URL.createObjectURL(blob);

        let link = document.createElement("a");
        link.href = url;
        link.download = `Database_Gmaps_${new Date().toISOString().split('T')[0]}.csv`;
        link.style.display = 'none';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        URL.revokeObjectURL(url);

        this.showNotification(`Downloaded ${results.length} records as CSV!`, 'green');
    }

    injectDebugPanel() {
        // Create floating debug panel
        const debugPanel = document.createElement('div');
        debugPanel.id = 'mafaza-debug-panel';
        debugPanel.innerHTML = `
            <div class="debug-header">
                <span class="debug-title">🔍 Mafaza Debug</span>
                <button class="debug-close" onclick="this.parentElement.parentElement.style.display='none'">✕</button>
            </div>
            <div class="debug-content">
                <div class="debug-section">
                    <div class="debug-status">Status: <span id="debug-status">Ready</span></div>
                    <div class="debug-progress">Progress: <span id="debug-progress">0/0</span></div>
                    <div class="debug-count">Data Found: <span id="debug-count">0</span></div>
                </div>
                <div class="debug-logs" id="debug-logs">
                    <div class="log-entry">Extension initialized - waiting for scrape...</div>
                </div>
                <div class="debug-actions">
                    <button onclick="document.getElementById('mafaza-debug-panel').style.display='none'" class="debug-btn">Hide Panel</button>
                </div>
            </div>
        `;

        // Add CSS styles
        const style = document.createElement('style');
        style.textContent = `
            #mafaza-debug-panel {
                position: fixed;
                top: 60px;
                right: 10px;
                width: 350px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.3);
                z-index: 10000;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 12px;
                overflow: hidden;
            }

            .debug-header {
                background: rgba(255,255,255,0.1);
                padding: 8px 12px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid rgba(255,255,255,0.2);
            }

            .debug-title {
                font-weight: bold;
                font-size: 14px;
            }

            .debug-close {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                font-size: 16px;
                padding: 0;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
            }

            .debug-close:hover {
                background: rgba(255,255,255,0.2);
            }

            .debug-content {
                padding: 12px;
            }

            .debug-section {
                margin-bottom: 12px;
            }

            .debug-section > div {
                margin-bottom: 4px;
                font-weight: 500;
            }

            .debug-logs {
                background: rgba(255,255,255,0.1);
                border-radius: 6px;
                padding: 8px;
                max-height: 150px;
                overflow-y: auto;
                margin-bottom: 12px;
            }

            .log-entry {
                margin-bottom: 4px;
                padding: 2px 0;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }

            .log-entry:last-child {
                border-bottom: none;
            }

            .debug-actions {
                display: flex;
                gap: 8px;
            }

            .debug-btn {
                flex: 1;
                background: rgba(255,255,255,0.2);
                border: 1px solid rgba(255,255,255,0.3);
                color: white;
                padding: 6px 12px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 11px;
                transition: all 0.2s;
            }

            .debug-btn:hover {
                background: rgba(255,255,255,0.3);
                transform: translateY(-1px);
            }

            .debug-status-success { color: #27ae60; }
            .debug-status-error { color: #e74c3c; }
            .debug-status-warning { color: #f39c12; }
            .debug-status-info { color: #3498db; }
        `;

        document.head.appendChild(style);
        document.body.appendChild(debugPanel);

        // Initialize debug methods
        this.updateDebugStatus('Ready', 'success');
        this.addDebugLog('Debug panel initialized');
    }

    updateDebugStatus(status, type = 'info') {
        const statusElement = document.getElementById('debug-status');
        if (statusElement) {
            statusElement.textContent = status;
            statusElement.className = `debug-status-${type}`;
        }
    }

    updateDebugProgress(current, total) {
        const progressElement = document.getElementById('debug-progress');
        if (progressElement) {
            progressElement.textContent = `${current}/${total}`;
        }
    }

    updateDebugCount(count) {
        const countElement = document.getElementById('debug-count');
        if (countElement) {
            countElement.textContent = count;
        }
    }

    addDebugLog(message) {
        const logsElement = document.getElementById('debug-logs');
        if (logsElement) {
            const logEntry = document.createElement('div');
            logEntry.className = 'log-entry';
            logEntry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;

            logsElement.appendChild(logEntry);
            logsElement.scrollTop = logsElement.scrollHeight;
        }

        // Also log to console for debugging
        console.log(`🔍 [DEBUG] ${message}`);
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
