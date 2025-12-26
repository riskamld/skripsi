// Options page script for Mafaza Fortuna Chrome Extension

class MafazaOptions {
    constructor() {
        this.defaults = {
            apiUrl: 'http://localhost/mafaza_fortuna/public/api',
            apiToken: '',
            autoScraping: false,
            scrapeDelay: 2,
            maxRetries: 3,
            notifications: true,
            successNotifications: true,
            errorNotifications: true,
            batchSize: 10,
            debugLogging: false,
            userAgent: '',
            randomTiming: true
        };

        this.init();
    }

    async init() {
        console.log('Mafaza Fortuna Options initialized');

        await this.loadSettings();
        this.setupEventListeners();
        this.loadStats();
    }

    async loadSettings() {
        try {
            const result = await chrome.storage.sync.get(['mafazaSettings']);
            const settings = result.mafazaSettings || {};

            // Merge with defaults
            const mergedSettings = { ...this.defaults, ...settings };

            // Update form fields
            this.updateFormFields(mergedSettings);

        } catch (error) {
            console.error('Failed to load settings:', error);
            this.showAlert('Failed to load settings', 'error');
        }
    }

    updateFormFields(settings) {
        document.getElementById('api-url').value = settings.apiUrl || '';
        document.getElementById('api-token').value = settings.apiToken || '';
        document.getElementById('auto-scraping').checked = settings.autoScraping || false;
        document.getElementById('scrape-delay').value = settings.scrapeDelay || 2;
        document.getElementById('max-retries').value = settings.maxRetries || 3;
        document.getElementById('notifications').checked = settings.notifications || false;
        document.getElementById('success-notifications').checked = settings.successNotifications !== false;
        document.getElementById('error-notifications').checked = settings.errorNotifications !== false;
        document.getElementById('batch-size').value = settings.batchSize || 10;
        document.getElementById('debug-logging').checked = settings.debugLogging || false;
        document.getElementById('user-agent').value = settings.userAgent || '';
        document.getElementById('random-timing').checked = settings.randomTiming !== false;
    }

    setupEventListeners() {
        // Save button
        document.getElementById('save-btn')?.addEventListener('click', () => this.saveSettings());

        // Test connection button
        document.getElementById('test-connection-btn')?.addEventListener('click', () => this.testApiConnection());

        // Reset button
        document.getElementById('reset-btn')?.addEventListener('click', () => this.resetSettings());

        // Enter key on inputs
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.saveSettings();
                }
            });
        });
    }

    async saveSettings() {
        try {
            const settings = this.collectFormData();

            // Validate required fields
            if (!settings.apiUrl.trim()) {
                this.showAlert('API URL is required', 'error');
                document.getElementById('api-url').focus();
                return;
            }

            await chrome.storage.sync.set({ mafazaSettings: settings });

            // Update background script
            await chrome.runtime.sendMessage({ action: 'updateSettings', settings });

            this.showAlert('Settings saved successfully!', 'success');

        } catch (error) {
            console.error('Failed to save settings:', error);
            this.showAlert('Failed to save settings', 'error');
        }
    }

    collectFormData() {
        return {
            apiUrl: document.getElementById('api-url').value.trim(),
            apiToken: document.getElementById('api-token').value.trim(),
            autoScraping: document.getElementById('auto-scraping').checked,
            scrapeDelay: parseInt(document.getElementById('scrape-delay').value) || 2,
            maxRetries: parseInt(document.getElementById('max-retries').value) || 3,
            notifications: document.getElementById('notifications').checked,
            successNotifications: document.getElementById('success-notifications').checked,
            errorNotifications: document.getElementById('error-notifications').checked,
            batchSize: parseInt(document.getElementById('batch-size').value) || 10,
            debugLogging: document.getElementById('debug-logging').checked,
            userAgent: document.getElementById('user-agent').value.trim(),
            randomTiming: document.getElementById('random-timing').checked
        };
    }

    async testApiConnection() {
        const settings = this.collectFormData();

        if (!settings.apiUrl) {
            this.showAlert('Please enter API URL first', 'error');
            return;
        }

        try {
            this.showLoading(true, 'Testing connection...');

            const response = await fetch(`${settings.apiUrl}/places`, {
                method: 'GET',
                headers: {
                    'X-API-TOKEN': settings.apiToken,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.showAlert(`✅ Connection successful! Found ${data.length || 0} places.`, 'success');
            } else {
                this.showAlert(`❌ API Error: ${response.status} ${response.statusText}`, 'error');
            }

        } catch (error) {
            console.error('Connection test failed:', error);
            this.showAlert(`❌ Connection failed: ${error.message}`, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async resetSettings() {
        if (!confirm('Are you sure you want to reset all settings to defaults? This cannot be undone.')) {
            return;
        }

        try {
            await chrome.storage.sync.set({ mafazaSettings: this.defaults });
            this.updateFormFields(this.defaults);
            this.showAlert('Settings reset to defaults', 'success');
        } catch (error) {
            console.error('Failed to reset settings:', error);
            this.showAlert('Failed to reset settings', 'error');
        }
    }

    async loadStats() {
        try {
            const result = await chrome.storage.local.get(['mafazaStats']);
            const stats = result.mafazaStats || {
                scrapedToday: 0,
                queuedCount: 0,
                totalScraped: 0,
                successRate: 100
            };

            document.getElementById('total-scraped').textContent = stats.totalScraped || 0;
            document.getElementById('today-scraped').textContent = stats.scrapedToday || 0;
            document.getElementById('queue-size').textContent = stats.queuedCount || 0;
            document.getElementById('success-rate').textContent = `${stats.successRate || 100}%`;

        } catch (error) {
            console.error('Failed to load stats:', error);
        }
    }

    showAlert(message, type = 'info') {
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');

        // Hide all alerts first
        successAlert.style.display = 'none';
        errorAlert.style.display = 'none';

        // Show appropriate alert
        if (type === 'success') {
            successAlert.textContent = message;
            successAlert.style.display = 'block';
        } else if (type === 'error') {
            errorAlert.textContent = message;
            errorAlert.style.display = 'block';
        }

        // Auto-hide after 5 seconds
        setTimeout(() => {
            successAlert.style.display = 'none';
            errorAlert.style.display = 'none';
        }, 5000);
    }

    showLoading(show, message = '') {
        // Could implement loading overlay here
        const saveBtn = document.querySelector('button[onclick="saveSettings()"]');
        if (saveBtn) {
            saveBtn.disabled = show;
            saveBtn.textContent = show ? (message || 'Saving...') : '💾 Save Settings';
        }
    }
}

// Global functions for HTML onclick handlers
function saveSettings() {
    window.mafazaOptions.saveSettings();
}

function testApiConnection() {
    window.mafazaOptions.testApiConnection();
}

function resetSettings() {
    window.mafazaOptions.resetSettings();
}

function toggleAdvanced() {
    const advanced = document.getElementById('advanced-settings');
    advanced.classList.toggle('show');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.mafazaOptions = new MafazaOptions();
});
