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
        await this.loadSettings();
        this.setupEventListeners();
        this.updateStatus('Ready', 'Extension is ready to scrape', 'success');
        this.updateStats();
        this.checkApiConnection();
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
            chrome.runtime.sendMessage({
                action: 'updateSettings',
                settings: this.settings
            });

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
                    case 'dashboard':
                        this.openDashboard();
                        break;
                    case 'clear':
                        this.clearData();
                        break;
                    case 'save':
                        this.saveSettings();
                        break;
                }
            });
        });

        // Settings tab
        document.getElementById('auto-scraping-toggle').addEventListener('change', (e) => {
            this.settings.autoScraping = e.target.checked;
            chrome.runtime.sendMessage({
                action: 'toggleAutoScraping',
                enabled: e.target.checked
            });
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
            }
        }

        document.getElementById('scraped-count').textContent = this.stats.scrapedToday || 0;
        document.getElementById('queued-count').textContent = this.stats.queuedCount || 0;
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
            const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

            if (!tab) {
                this.showAlert('No active tab found', 'error');
                return;
            }

            // Show loading
            this.showLoading(true);

            // Send message to content script to scrape
            chrome.tabs.sendMessage(tab.id, {
                action: 'scrapePage',
                settings: this.settings
            });

        } catch (error) {
            console.error('Failed to scrape page:', error);
            this.showAlert('Failed to scrape page', 'error');
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
