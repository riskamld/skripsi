# 🗺️ Mafaza Fortuna - Chrome Extension

**Chrome Extension untuk Scraping Data Tempat dari Berbagai Website**

Extension ini memungkinkan Anda untuk dengan mudah mengumpulkan data tempat (restaurant, hotel, atraksi wisata, dll) dari berbagai website dan menyimpannya ke dalam database Mafaza Fortuna secara otomatis.

## ✨ Fitur Utama

### 🔍 **Intelligent Scraping**
- **Multi-Website Support**: Google Maps, Google Search, Bing, Yahoo
- **Robust Selectors**: Tahan terhadap perubahan struktur website
- **Smart Detection**: Otomatis mendeteksi konten tempat yang relevan

### ⚡ **Real-time Processing**
- **Background Queue**: Proses scraping tanpa mengganggu browsing
- **Batch Processing**: Optimasi untuk performa tinggi
- **Retry Logic**: Otomatis retry untuk request yang gagal

### 🎛️ **Advanced Controls**
- **Auto-Scraping Mode**: Scraping otomatis saat browsing
- **Manual Control**: Klik kanan untuk scraping manual
- **Visual Indicators**: Indikator saat extension aktif

### 🔧 **Anti-Detection Features**
- **Random Delays**: Hindari rate limiting
- **User Agent Rotation**: Simulasi browser berbeda
- **Request Throttling**: Kontrol kecepatan request

## 🚀 Instalasi

### Persiapan Laravel Backend

1. **Pastikan Laravel berjalan**:
   ```bash
   cd /opt/lampp/htdocs/mafaza_fortuna
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Dapatkan API Token**:
   - Buka web interface: `http://localhost:8000`
   - Pergi ke settings atau cek database untuk API token
   - Atau generate token baru via tinker:
   ```bash
   php artisan tinker
   >>> App\Models\ApiToken::create(['name' => 'Chrome Extension', 'abilities' => ['*']])
   ```

### Instalasi Chrome Extension

1. **Download Extension Files**:
   ```bash
   # Extension sudah ada di folder extension-chrome-mafaza/
   cd extension-chrome-mafaza
   ```

2. **Load Extension di Chrome**:
   - Buka Chrome dan ketik: `chrome://extensions/`
   - Aktifkan **"Developer mode"** (pojok kanan atas)
   - Klik **"Load unpacked"**
   - Pilih folder `extension-chrome-mafaza/`

3. **Konfigurasi Extension**:
   - Klik ikon extension (🗺️) di toolbar
   - Pergi ke tab **"Settings"**
   - Isi:
     - **API URL**: `http://localhost:8000/api`
     - **API Token**: [token yang didapat dari langkah 2]
   - Klik **"Save Settings"**

## 📖 Cara Penggunaan

### Mode Auto-Scraping (Direkomendasikan)

1. **Aktifkan Auto-Scraping**:
   - Klik ikon extension
   - Toggle **"Auto-Scraping"** ON
   - Extension akan otomatis mendeteksi halaman tempat

2. **Browse Normal**:
   - Buka Google Maps, search tempat
   - Extension otomatis scrape data
   - Notifikasi akan muncul saat berhasil

### Manual Scraping

1. **Klik Kanan**:
   - Di halaman Google Maps
   - Klik kanan → **"Scrape with Mafaza Fortuna"**

2. **Via Extension Popup**:
   - Klik ikon extension
   - Klik **"🔍 Scrape Page"**

### Monitoring

- **Dashboard**: `http://localhost:8000`
- **Extension Stats**: Klik ikon extension → tab "Dashboard"
- **Queue Status**: Badge merah menunjukkan item dalam antrian

## 🔧 Konfigurasi

### Settings Utama

| Setting | Default | Deskripsi |
|---------|---------|-----------|
| API URL | `http://localhost:8000/api` | URL Laravel API |
| API Token | - | Authentication token |
| Scrape Delay | 2 detik | Jeda antar request |
| Max Retries | 3 | Retry untuk request gagal |

### Advanced Settings

- **Batch Size**: Jumlah item diproses bersamaan
- **Debug Logging**: Log detail untuk debugging
- **User Agent**: Custom user agent string
- **Random Timing**: Randomize delay untuk anti-detection

## 🌐 Website Support

Extension dapat scraping dari:

- ✅ **Google Maps**: Full support dengan koordinat
- ✅ **Google Search**: Local business results
- ✅ **Bing**: Search results
- ✅ **Yahoo**: Search results
- ✅ **Generic Sites**: Structured data extraction

## 🛡️ Anti-Detection Strategy

### Robust Selector System
```javascript
// Multiple fallback selectors
const selectors = [
    '[data-testid="place-name"]',     // Current Google Maps
    '[jsaction*="place"] h1',         // Alternative selector
    'h1.place-name',                  // Fallback
    'h1',                             // Generic fallback
];
```

### Dynamic Element Detection
- MutationObserver untuk konten dinamis
- Fallback strategies untuk struktur berubah
- AI-powered pattern recognition

### Request Management
- Randomized delays (1-5 detik)
- User agent rotation
- Request throttling
- Automatic retry dengan exponential backoff

## 📊 Monitoring & Analytics

### Extension Dashboard
- **Total Scraped**: Total data berhasil disimpan
- **Today**: Data hari ini
- **Queue**: Item dalam antrian
- **Success Rate**: Tingkat keberhasilan

### Laravel Dashboard
- **Real-time Stats**: Update langsung
- **Place Management**: CRUD lengkap
- **Scrape Logs**: History aktivitas
- **API Monitoring**: Request/response logs

## 🔧 Troubleshooting

### Extension Tidak Muncul
```bash
# Check manifest.json syntax
cd extension-chrome-mafaza
cat manifest.json | jq .  # Should not error
```

### API Connection Failed
```bash
# Test API manually
curl -X GET "http://localhost:8000/api/places" \
  -H "X-API-TOKEN: your-token-here"
```

### Scraping Tidak Bekerja
1. Check console logs: `chrome://extensions/` → Extension details → View console
2. Verify API token is correct
3. Check if Laravel server is running
4. Test with different websites

## 🆕 Update & Maintenance

### Update Extension
1. Pull latest code
2. Reload extension di `chrome://extensions/`
3. Clear browser cache

### Database Maintenance
```bash
# Clean old logs
php artisan tinker
>>> App\Models\ScrapeLog::where('created_at', '<', now()->subDays(30))->delete()
```

## 📈 Performance Tips

### Optimal Settings
- **Scrape Delay**: 2-3 detik untuk menghindari blocking
- **Batch Size**: 5-10 untuk balance speed vs reliability
- **Max Retries**: 3 kali cukup

### Best Practices
- **Don't spam**: Beri jeda antar scraping
- **Monitor queue**: Jaga agar tidak overload
- **Regular cleanup**: Hapus data lama secara berkala
- **Update selectors**: Monitor perubahan website

## 🎯 Roadmap

### Version 1.1 (Next)
- [ ] Multi-account support
- [ ] Export data ke CSV/JSON
- [ ] Advanced filtering options
- [ ] Custom scraper rules

### Version 1.2
- [ ] Machine learning untuk selector detection
- [ ] Browser automation (Puppeteer integration)
- [ ] Cloud sync untuk multi-device
- [ ] Advanced analytics dashboard

## 🤝 Contributing

1. Fork repository
2. Create feature branch: `git checkout -b feature/new-scraper`
3. Commit changes: `git commit -am 'Add new scraper'`
4. Push to branch: `git push origin feature/new-scraper`
5. Submit Pull Request

## 📞 Support

- **Issues**: GitHub Issues
- **Discussions**: GitHub Discussions
- **Documentation**: Update README.md

---

**Mafaza Fortuna Chrome Extension** - Powerful, reliable, and easy-to-use place data scraper for modern web browsing. 🚀
