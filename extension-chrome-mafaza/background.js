// Background script for Mafaza Fortuna Chrome Extension
class MafazaBackground {
    constructor() {
        this.settings = {
            apiUrl: 'http://localhost/mafaza_fortuna/public/api',
            apiToken: '',
            scrapeDelay: 2,
            notifications: true,
            autoScraping: false
        };

        this.stats = {
            scrapedToday: 0,
            queuedCount: 0,
            lastReset: new Date().toDateString()
        };

        this.scrapingQueue = [];
        this.isProcessing = false;

        this.init();
    }

    async init() {
        console.log('Mafaza Fortuna Background Script initialized');

        await this.loadSettings();
        this.setupMessageListeners();
        this.setupContextMenu();
        this.resetDailyStats();
        this.updateBadge();

        // Check if we need to resume any pending operations
        this.processQueue();
    }

    async loadSettings() {
        try {
            const result = await chrome.storage.sync.get(['mafazaSettings']);
            if (result.mafazaSettings) {
                this.settings = { ...this.settings, ...result.mafazaSettings };
            }

            const statsResult = await chrome.storage.local.get(['mafazaStats']);
            if (statsResult.mafazaStats) {
                this.stats = { ...this.stats, ...statsResult.mafazaStats };
            }
        } catch (error) {
            console.error('Failed to load settings:', error);
        }
    }

    async saveSettings() {
        try {
            await chrome.storage.sync.set({ mafazaSettings: this.settings });
        } catch (error) {
            console.error('Failed to save settings:', error);
        }
    }

    async saveStats() {
        try {
            await chrome.storage.local.set({ mafazaStats: this.stats });
        } catch (error) {
            console.error('Failed to save stats:', error);
        }
    }

    setupMessageListeners() {
        // Listen for messages from popup and content scripts
        chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
            this.handleMessage(message, sender, sendResponse);
            return true; // Keep message channel open for async responses
        });

        // Listen for tab updates to potentially trigger auto-scraping
        chrome.tabs.onUpdated.addListener((tabId, changeInfo, tab) => {
            if (changeInfo.status === 'complete' && this.settings.autoScraping) {
                this.handleTabUpdate(tabId, tab);
            }
        });
    }

    async handleMessage(message, sender, sendResponse) {
        try {
            switch (message.action) {
                case 'updateSettings':
                    this.settings = { ...this.settings, ...message.settings };
                    await this.saveSettings();
                    sendResponse({ success: true });
                    break;

                case 'toggleAutoScraping':
                    this.settings.autoScraping = message.enabled;
                    await this.saveSettings();
                    sendResponse({ success: true });
                    break;

                case 'getStats':
                    sendResponse({ stats: this.stats });
                    break;

                case 'scrapeData':
                    console.log('📥 [BACKGROUND] Received scrapeData message');

                    // Check if this is chunked data
                    if (message.data && message.data.chunk_info) {
                        console.log(`📦 [BACKGROUND] Processing chunk ${message.data.chunk_info.index}/${message.data.chunk_info.total} (${message.data.chunk_info.size} places)`);
                        console.log('📊 [BACKGROUND] Chunk data sample:', {
                            first_place: message.data.places?.[0]?.name || 'NOT FOUND',
                            total_in_chunk: message.data.places?.length || 0
                        });
                    } else {
                        console.log('📊 [BACKGROUND] Single data received:', {
                            name: message.data?.name || 'NOT FOUND',
                            place_id: message.data?.place_id || 'NOT FOUND',
                            address: message.data?.address ? message.data.address.substring(0, 50) + '...' : 'NOT FOUND'
                        });
                    }

                    this.addToQueue(message.data);
                    sendResponse({ success: true, queued: true });
                    break;

                case 'getQueueStatus':
                    sendResponse({
                        queueLength: this.scrapingQueue.length,
                        isProcessing: this.isProcessing
                    });
                    break;

                default:
                    sendResponse({ error: 'Unknown action' });
            }
        } catch (error) {
            console.error('Error handling message:', error);
            sendResponse({ error: error.message });
        }
    }

    setupContextMenu() {
        // Handle context menu clicks
        chrome.contextMenus.onClicked.addListener((info, tab) => {
            if (info.menuItemId === 'mafaza-scrape') {
                this.scrapeCurrentTab(tab);
            }
        });

        // Try to remove existing menu item first (ignore errors if it doesn't exist)
        chrome.contextMenus.remove('mafaza-scrape', () => {
            // Create context menu for right-click scraping
            chrome.contextMenus.create({
                id: 'mafaza-scrape',
                title: 'Scrape with Mafaza Fortuna',
                contexts: ['page'],
                documentUrlPatterns: [
                    'https://www.google.com/maps/*',
                    'https://maps.google.com/*'
                ]
            }, () => {
                if (chrome.runtime.lastError) {
                    console.warn('Failed to create context menu:', chrome.runtime.lastError);
                }
            });
        });
    }

    async handleTabUpdate(tabId, tab) {
        // Auto-scrape logic for specific sites
        if (tab.url && this.isScrapableUrl(tab.url)) {
            // Add small delay to let page fully load
            setTimeout(() => {
                this.scrapeCurrentTab(tab);
            }, 2000);
        }
    }

    isScrapableUrl(url) {
        const scrapablePatterns = [
            /google\.com\/maps/,
            /google\.com\/search/,
            /bing\.com/,
            /yahoo\.com/
        ];

        return scrapablePatterns.some(pattern => pattern.test(url));
    }

    async scrapeCurrentTab(tab) {
        try {
            // Send message to content script to extract data
            const response = await chrome.tabs.sendMessage(tab.id, {
                action: 'extractPlaceData',
                settings: this.settings
            });

            if (response && response.data) {
                this.addToQueue(response.data);
                this.showNotification('Data queued for scraping', 'Place data has been added to the processing queue.');
            }
        } catch (error) {
            console.error('Failed to scrape tab:', error);
            this.showNotification('Scraping failed', 'Could not extract data from the current page.');
        }
    }

    addToQueue(data) {
        // Handle bulk data differently from single place data
        if (data && data.bulk_results && data.places && Array.isArray(data.places)) {
            // Handle bulk scraping results
            console.log(`📦 Processing bulk data with ${data.places.length} places`);

            let validPlacesCount = 0;
            data.places.forEach((place, index) => {
                if (this.validatePlaceData(place, true)) { // true = isBulkSearch
                    this.scrapingQueue.push({
                        ...place,
                        timestamp: Date.now(),
                        retries: 0,
                        bulk_source: true,
                        bulk_index: index
                    });
                    validPlacesCount++;
                } else {
                    console.warn(`❌ Invalid place in bulk data at index ${index}:`, place);
                }
            });

            if (validPlacesCount > 0) {
                this.stats.queuedCount += validPlacesCount;
                this.updateBadge();
                this.saveStats();
                this.processQueue();
                console.log(`✅ Added ${validPlacesCount} places from bulk data to queue`);
            } else {
                console.error('❌ No valid places found in bulk data');
            }
        } else if (this.validatePlaceData(data)) {
            // Handle single place data
            this.scrapingQueue.push({
                ...data,
                timestamp: Date.now(),
                retries: 0
            });

            this.stats.queuedCount++;
            this.updateBadge();
            this.saveStats();
            this.processQueue();
        } else {
            console.error('❌ Invalid place data:', data);
        }
    }

    validatePlaceData(data, isBulkSearch = false) {
        // Basic validation
        if (!data || typeof data !== 'object') {
            return false;
        }

        // For bulk search data, be more lenient - accept any data with minimal info
        if (isBulkSearch) {
            return data.place_id || data.name || data.address || data.phone || data.website;
        }

        // For single place data, require more complete info
        return (data.name || data.place_id) &&
               (data.lat !== undefined || data.location);
    }

    async processQueue() {
        if (this.isProcessing || this.scrapingQueue.length === 0) {
            return;
        }

        this.isProcessing = true;
        const item = this.scrapingQueue.shift();

        try {
            await this.sendToApi(item);
            this.stats.scrapedToday++;
            this.stats.queuedCount = Math.max(0, this.stats.queuedCount - 1);
            this.showNotification('Place scraped successfully', `${item.name || 'Unknown place'} has been saved to the database.`);

        } catch (error) {
            console.error('Failed to process queue item:', error);

            // Retry logic
            if (item.retries < 3) {
                item.retries++;
                this.scrapingQueue.unshift(item); // Put back at front
                setTimeout(() => this.processQueue(), this.settings.scrapeDelay * 1000);
            } else {
                this.showNotification('Scraping failed', `Failed to save ${item.name || 'place'} after 3 attempts.`);
                this.stats.queuedCount = Math.max(0, this.stats.queuedCount - 1);
            }
        }

        this.isProcessing = false;
        this.saveStats();
        this.updateBadge();

        // Continue processing queue
        if (this.scrapingQueue.length > 0) {
            setTimeout(() => this.processQueue(), this.settings.scrapeDelay * 1000);
        }
    }

    async sendToApi(data) {
        console.log('🌐 [BACKGROUND] Sending data to API...');
        console.log('📊 [BACKGROUND] API URL:', this.settings.apiUrl);
        console.log('🔑 [BACKGROUND] API Token present:', !!this.settings.apiToken);
        console.log('📦 [BACKGROUND] Data being sent:', {
            name: data.name || 'NOT FOUND',
            place_id: data.place_id || 'NOT FOUND',
            address: data.address ? data.address.substring(0, 50) + '...' : 'NOT FOUND'
        });

        try {
            const response = await fetch(`${this.settings.apiUrl}/places`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-TOKEN': this.settings.apiToken
                },
                body: JSON.stringify(data)
            });

            console.log('📥 [BACKGROUND] API Response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ [BACKGROUND] API Error response:', errorText);
                throw new Error(`API request failed: ${response.status} - ${errorText}`);
            }

            const result = await response.json();
            console.log('✅ [BACKGROUND] API Response success:', result);
            return result;
        } catch (error) {
            console.error('❌ [BACKGROUND] API Request failed:', error);
            throw error;
        }
    }

    showNotification(title, message) {
        if (!this.settings.notifications) return;

        chrome.notifications.create({
            type: 'basic',
            iconUrl: chrome.runtime.getURL('icons/icon128.svg'),
            title: title,
            message: message
        });
    }

    updateBadge() {
        const text = this.scrapingQueue.length > 0 ? this.scrapingQueue.length.toString() : '';
        chrome.action.setBadgeText({ text: text });

        // Set badge color
        if (this.scrapingQueue.length > 0) {
            chrome.action.setBadgeBackgroundColor({ color: '#e74c3c' }); // Red
        } else {
            chrome.action.setBadgeBackgroundColor({ color: '#27ae60' }); // Green
        }
    }

    resetDailyStats() {
        const today = new Date().toDateString();
        if (this.stats.lastReset !== today) {
            this.stats.scrapedToday = 0;
            this.stats.lastReset = today;
            this.saveStats();
        }
    }

    // Utility method to get API token (for content scripts)
    getApiToken() {
        return this.settings.apiToken;
    }

    getApiUrl() {
        return this.settings.apiUrl;
    }
}

// Initialize background script
const mafazaBackground = new MafazaBackground();
