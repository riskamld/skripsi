// Popup script for Mafaza Fortuna Chrome Extension
class MafazaScraper {
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
            queuedCount: 0
        };

        this.init();
    }

    async init() {
        try {
            await this.loadSettings();
            this.setupEventListeners();
            this.updateStatus('Ready', 'Extension is ready to scrape', 'success');

            // Delay stats update to avoid race conditions
            setTimeout(() => {
                this.updateStats();
            }, 100);

            // Delay API connection check
            setTimeout(() => {
                this.checkApiConnection();
            }, 200);

        } catch (error) {
            console.error('Failed to initialize extension:', error);
            this.updateStatus('Initialization Failed', 'Please reload the extension', 'error');
        }
    }

    async loadSettings() {
        try {
            const result = await chrome.storage.sync.get(['mafazaSettings']);
            if (result.mafazaSettings) {
                this.settings = { ...this.settings, ...result.mafazaSettings };
            }

            // Update form fields
            document.getElementById('api-url').value = this.settings.apiUrl;
            document.getElementById('api-token').value = this.settings.apiToken;
            document.getElementById('scrape-delay').value = this.settings.scrapeDelay;
            document.getElementById('notifications-toggle').checked = this.settings.notifications;
            document.getElementById('auto-scraping-toggle').checked = this.settings.autoScraping;

        } catch (error) {
            console.error('Failed to load settings:', error);
            this.showAlert('Failed to load settings', 'error');
        }
    }

    async saveSettings() {
        try {
            this.settings = {
                apiUrl: document.getElementById('api-url').value.trim(),
                apiToken: document.getElementById('api-token').value.trim(),
                scrapeDelay: parseInt(document.getElementById('scrape-delay').value) || 2,
                notifications: document.getElementById('notifications-toggle').checked,
                autoScraping: document.getElementById('auto-scraping-toggle').checked
            };

            await chrome.storage.sync.set({ mafazaSettings: this.settings });

            this.showAlert('Settings saved successfully!', 'success');

            // Update background script with new settings
            try {
                await chrome.runtime.sendMessage({
                    action: 'updateSettings',
                    settings: this.settings
                });
            } catch (error) {
                console.error('Failed to update background settings:', error);
            }

        } catch (error) {
            console.error('Failed to save settings:', error);
            this.showAlert('Failed to save settings', 'error');
        }
    }

    setupEventListeners() {
        // Tab switching event listeners
        const tabButtons = document.querySelectorAll('.tab-button');

        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const tabName = button.getAttribute('data-tab');
                if (tabName) {
                    showTab(tabName, e); // Call global function
                }
            });
        });

        // Other button event listeners using data attributes
        const actionButtons = document.querySelectorAll('button[data-action]');

        actionButtons.forEach(button => {
            const action = button.getAttribute('data-action');
            button.addEventListener('click', () => {
                switch(action) {
                    case 'scrape':
                        this.scrapeCurrentPage();
                        break;
                    case 'scrape-csv':
                        this.scrapeCurrentPageCSV();
                        break;
                    case 'dashboard':
                        this.openDashboard();
                        break;
                case 'clear':
                    this.clearData();
                    break;
                case 'delete-today':
                    this.deleteScrapedToday();
                    break;
                case 'save':
                    this.saveSettings();
                    break;
                case 'debug':
                    this.debugImageExtraction();
                    break;
                }
            });
        });

        // Settings tab
        document.getElementById('auto-scraping-toggle').addEventListener('change', async (e) => {
            this.settings.autoScraping = e.target.checked;
            try {
                await chrome.runtime.sendMessage({
                    action: 'toggleAutoScraping',
                    enabled: e.target.checked
                });
            } catch (error) {
                console.error('Failed to toggle auto-scraping:', error);
            }
        });

        // Listen for messages from background script
        chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
            if (message.action === 'scrapingProgress') {
                this.updateProgress(message.progress);
            } else if (message.action === 'scrapingComplete') {
                this.updateStats();
                this.showAlert(`Scraped ${message.count} places successfully!`, 'success');
            } else if (message.action === 'scrapingError') {
                this.showAlert(`Scraping failed: ${message.error}`, 'error');
            } else if (message.action === 'statsUpdate') {
                this.updateStats(message.stats);
            }
        });
    }

    async checkApiConnection() {
        try {
            const response = await fetch(`${this.settings.apiUrl}/places`, {
                method: 'GET',
                headers: {
                    'X-API-TOKEN': this.settings.apiToken,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                this.updateStatus('Connected', 'API connection successful', 'success');
            } else {
                this.updateStatus('API Error', `HTTP ${response.status}`, 'error');
            }
        } catch (error) {
            this.updateStatus('Connection Failed', 'Cannot connect to API', 'error');
        }
    }

    updateStatus(title, text, type = 'info') {
        const statusCard = document.getElementById('status-card');
        const statusIcon = document.getElementById('status-icon');
        const statusTitle = document.getElementById('status-title');
        const statusText = document.getElementById('status-text');

        // Remove existing status classes
        statusCard.classList.remove('error', 'warning');

        // Set icon and add class based on type
        switch(type) {
            case 'success':
                statusIcon.textContent = '✅';
                break;
            case 'error':
                statusIcon.textContent = '❌';
                statusCard.classList.add('error');
                break;
            case 'warning':
                statusIcon.textContent = '⚠️';
                statusCard.classList.add('warning');
                break;
            default:
                statusIcon.textContent = '🔄';
        }

        statusTitle.textContent = title;
        statusText.textContent = text;
    }

    updateProgress(progress) {
        const progressContainer = document.getElementById('progress-container');
        const progressFill = document.getElementById('progress-fill');

        if (progress > 0) {
            progressContainer.classList.remove('hidden');
            progressFill.style.width = `${progress}%`;
        } else {
            progressContainer.classList.add('hidden');
        }
    }

    async updateStats(stats = null) {
        if (stats) {
            this.stats = stats;
        } else {
            // Get stats from background script
            try {
                const response = await chrome.runtime.sendMessage({ action: 'getStats' });
                if (response && response.stats) {
                    this.stats = response.stats;
                }
            } catch (error) {
                console.error('Failed to get stats:', error);
                // Set default stats on error
                this.stats = { scrapedToday: 0, queuedCount: 0 };
            }
        }

        // Update DOM elements safely
        try {
            const scrapedElement = document.getElementById('scraped-count');
            const queuedElement = document.getElementById('queued-count');

            if (scrapedElement) {
                scrapedElement.textContent = this.stats.scrapedToday || 0;
            }
            if (queuedElement) {
                queuedElement.textContent = this.stats.queuedCount || 0;
            }
        } catch (error) {
            console.error('Failed to update stats display:', error);
        }
    }

    showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;

        alertContainer.appendChild(alert);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 5000);
    }

    async scrapeCurrentPage() {
        try {
            console.log('🔍 [POPUP] Starting scrapeCurrentPage...');
            const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

            if (!tab) {
                console.error('❌ [POPUP] No active tab found');
                this.showAlert('No active tab found', 'error');
                return;
            }

            console.log('✅ [POPUP] Found active tab:', tab.id, tab.url);

            // Show loading
            this.showLoading(true);

            // Send message to content script to extract data
            try {
                console.log('📤 [POPUP] Sending extractPlaceData message to content script...');
                const response = await chrome.tabs.sendMessage(tab.id, {
                    action: 'extractPlaceData'
                });

                console.log('📥 [POPUP] Received response from content script:', response);

                if (response && response.data) {
                    console.log('✅ [POPUP] Data received, sending to background script...');
                    console.log('📊 [POPUP] Data summary:', {
                        name: response.data.name || 'NOT FOUND',
                        place_id: response.data.place_id || 'NOT FOUND',
                        address: response.data.address ? response.data.address.substring(0, 50) + '...' : 'NOT FOUND'
                    });

                    // Send the extracted data to background script for processing
                    const bgResponse = await chrome.runtime.sendMessage({
                        action: 'scrapeData',
                        data: response.data
                    });

                    console.log('📥 [POPUP] Background script response:', bgResponse);

                    this.showAlert('Data sent to queue successfully!', 'success');
                    this.updateStats(); // Refresh stats
                } else {
                    console.error('❌ [POPUP] No data received from page. Response:', response);
                    this.showAlert('No data received from page', 'error');
                }

            } catch (error) {
                console.error('❌ [POPUP] Failed to scrape page:', error);
                this.showAlert('Failed to extract data. Try refreshing the page.', 'error');
            }

        } catch (error) {
            console.error('❌ [POPUP] Failed to scrape page:', error);
            this.showAlert('Failed to scrape page', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async scrapeCurrentPageCSV() {
        try {
            const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

            if (!tab) {
                this.showAlert('No active tab found', 'error');
                return;
            }

            // Show loading
            this.showLoading(true);

            // Send message to content script to extract bulk data and download CSV
            try {
                const response = await chrome.tabs.sendMessage(tab.id, {
                    action: 'extractPlaceData'
                });

                if (response && response.data && response.data.bulk_results) {
                    // For bulk results, trigger CSV download directly in content script
                    await chrome.tabs.sendMessage(tab.id, {
                        action: 'downloadCSV',
                        data: response.data.places
                    });

                    this.showAlert(`CSV download initiated for ${response.data.places_count} places!`, 'success');

                    // Also send data to API if configured
                    if (this.settings.apiUrl && this.settings.apiToken && response.data.places.length > 0) {
                        try {
                            // Send each place to the API
                            let sentCount = 0;
                            for (const place of response.data.places) {
                                try {
                                    // Convert CSV format to API format
                                    const apiData = {
                                        name: place.Nama,
                                        category: place.Kategori,
                                        rating: place.Rating !== '0' ? parseFloat(place.Rating.replace(',', '.')) : null,
                                        review_count: place.Ulasan !== '0' ? parseInt(place.Ulasan) : null,
                                        phone: place.Telepon || null,
                                        address: place.Alamat !== 'Cek Manual' ? place.Alamat : null,
                                        maps_url: place.Link,
                                        source: 'chrome_extension_csv',
                                        scraped_at: new Date().toISOString()
                                    };

                                    const apiResponse = await fetch(`${this.settings.apiUrl}/places`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-API-TOKEN': this.settings.apiToken
                                        },
                                        body: JSON.stringify(apiData)
                                    });

                                    if (apiResponse.ok) {
                                        sentCount++;
                                    } else {
                                        console.warn('Failed to send place to API:', place.Nama, apiResponse.status);
                                    }

                                    // Small delay between API calls
                                    await new Promise(resolve => setTimeout(resolve, 200));

                                } catch (apiError) {
                                    console.warn('API error for place:', place.Nama, apiError);
                                }
                            }

                            if (sentCount > 0) {
                                this.showAlert(`Sent ${sentCount}/${response.data.places.length} places to database!`, 'success');
                                this.updateStats();
                            }

                        } catch (apiError) {
                            console.warn('Failed to send data to API:', apiError);
                            this.showAlert('CSV downloaded but failed to send some data to database.', 'warning');
                        }
                    }

                } else {
                    this.showAlert('No bulk data found. Make sure you\'re on a Google Maps search results page.', 'error');
                }

            } catch (error) {
                console.error('Failed to scrape page for CSV:', error);
                this.showAlert('Failed to extract data. Try refreshing the page.', 'error');
            }

        } catch (error) {
            console.error('Failed to scrape page for CSV:', error);
            this.showAlert('Failed to scrape page', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    openDashboard() {
        chrome.tabs.create({
            url: this.settings.apiUrl.replace('/api', '')
        });
    }

    async clearData() {
        if (!confirm('Are you sure you want to clear all local data? This cannot be undone.')) {
            return;
        }

        try {
            await chrome.storage.local.clear();
            await chrome.storage.sync.remove(['mafazaSettings']);
            this.stats = { scrapedToday: 0, queuedCount: 0 };
            this.updateStats();
            this.showAlert('Local data cleared successfully', 'success');
        } catch (error) {
            console.error('Failed to clear data:', error);
            this.showAlert('Failed to clear data', 'error');
        }
    }

    async debugImageExtraction() {
        try {
            const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

            if (!tab) {
                this.showAlert('No active tab found', 'error');
                return;
            }

            // Send debug message to content script
            try {
                const response = await chrome.tabs.sendMessage(tab.id, {
                    action: 'debugImages'
                });

                if (response && response.success) {
                    this.showAlert('Debug completed! Check browser console for detailed results.', 'success');
                } else {
                    this.showAlert('Debug failed - check console for errors', 'error');
                }
            } catch (error) {
                console.error('Failed to send debug message to content script:', error);
                this.showAlert('Failed to communicate with page. Try refreshing the page.', 'error');
            }

        } catch (error) {
            console.error('Failed to debug image extraction:', error);
            this.showAlert('Failed to debug image extraction', 'error');
        }
    }

    async deleteScrapedToday() {
        if (!confirm('Are you sure you want to delete all places scraped today? This cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch(`${this.settings.apiUrl}/places/delete-today`, {
                method: 'DELETE',
                headers: {
                    'X-API-TOKEN': this.settings.apiToken,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.showAlert(`Deleted ${result.deleted_count} places scraped today!`, 'success');
                this.updateStats();
            } else {
                const error = await response.json();
                this.showAlert(`Failed to delete: ${error.error || 'Unknown error'}`, 'error');
            }
        } catch (error) {
            console.error('Failed to delete scraped today:', error);
            this.showAlert('Failed to delete scraped today', 'error');
        }
    }

    // Update debug progress bar
    updateDebugProgress(current, total) {
        const progressElement = document.getElementById('debug-progress');
        const progressFillElement = document.getElementById('debug-progress-fill');

        if (progressElement && progressFillElement) {
            progressElement.textContent = `${current}/${total}`;

            const percentage = total > 0 ? (current / total) * 100 : 0;
            progressFillElement.style.width = `${percentage}%`;
        }
    }

    showLoading(show) {
        const loading = document.getElementById('loading');
        loading.style.display = show ? 'block' : 'none';
    }
}

// Global tab switching function (used by event listeners)
function showTab(tabName, event) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });

    // Show selected tab
    document.getElementById(`${tabName}-tab`).classList.add('active');

    // Add active class to clicked button
    if (event && event.target) {
        event.target.classList.add('active');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.mafazaScraper = new MafazaScraper();
});
