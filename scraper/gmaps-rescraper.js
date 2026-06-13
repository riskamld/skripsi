/**
 * gmaps-rescraper.js
 * Re-scrapes places with incomplete data. Loops until all done.
 *
 * Usage: MAFAZA_API_TOKEN=xxx HEADLESS=true LIMIT=985 node gmaps-rescraper.js
 */

const { chromium } = require('playwright');
const https = require('https');
const http  = require('http');
const fs    = require('fs');
const path  = require('path');

const API_BASE    = 'https://fezora.net/mafaza/public/api';
const API_TOKEN   = process.env.MAFAZA_API_TOKEN || '';
const HEADLESS    = process.env.HEADLESS !== 'false';
const TOTAL       = parseInt(process.env.LIMIT || '20');
const BATCH       = 50;
const DELAY_MIN   = 1200;
const DELAY_MAX   = 2800;
const COOKIE_FILE = path.join(__dirname, 'google-cookies.json');

function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }
function rand(a, b) { return Math.floor(Math.random() * (b - a + 1)) + a; }

function apiRequest(method, path, body = null) {
  return new Promise((resolve, reject) => {
    const urlObj = new URL(API_BASE + path);
    const isHttps = urlObj.protocol === 'https:';
    const opts = {
      hostname: urlObj.hostname,
      path: urlObj.pathname + urlObj.search,
      method,
      headers: {
        'X-API-TOKEN': API_TOKEN,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    };
    if (body) opts.headers['Content-Length'] = Buffer.byteLength(JSON.stringify(body));
    const req = (isHttps ? https : http).request(opts, (res) => {
      let data = '';
      res.on('data', c => data += c);
      res.on('end', () => {
        try { resolve({ status: res.statusCode, body: JSON.parse(data) }); }
        catch (e) { resolve({ status: res.statusCode, body: data }); }
      });
    });
    req.on('error', reject);
    if (body) req.write(JSON.stringify(body));
    req.end();
  });
}

async function fetchBatch(limit) {
  const res = await apiRequest('GET', `/places/needs-rescrape?limit=${limit}`);
  if (res.status !== 200 || !res.body?.data) throw new Error(`API ${res.status}`);
  return res.body.data;
}

async function patchPlace(id, data) {
  const res = await apiRequest('PATCH', `/places/${id}`, data);
  return res.status === 200;
}

// ── extract from open place page ─────────────────────────────────────────────

async function extractFromPage(page) {
  const result = {};
  try { await page.waitForSelector('h1', { timeout: 12000 }); }
  catch { return null; }

  // Name
  try { result.name = await page.$eval('h1', el => el.innerText.trim()); } catch {}

  // Category
  try {
    result.category = await page.$eval(
      'button[jsaction*="category"], .DkEaL',
      el => el.innerText.trim()
    );
  } catch {}

  // Permanently closed
  try {
    const closed = await page.evaluate(() => {
      const all = [...document.querySelectorAll('span, div')];
      return all.some(el => /tutup permanen|permanently closed/i.test(el.innerText));
    });
    result.permanently_closed = closed;
  } catch { result.permanently_closed = false; }

  // Description (editorial summary)
  try {
    result.description = await page.$eval(
      '[data-item-id="editorial"] .Io6YTe, .PYvSYb, [class*="description"] .Io6YTe, [jsaction*="description"] .Io6YTe',
      el => el.innerText.trim()
    );
  } catch {}
  // Fallback: cari teks ringkasan di bawah kategori
  if (!result.description) {
    try {
      result.description = await page.evaluate(() => {
        const cat = document.querySelector('button[jsaction*="category"]');
        if (!cat) return null;
        const next = cat.parentElement?.nextElementSibling;
        if (next && next.innerText.length > 20 && next.innerText.length < 300) return next.innerText.trim();
        return null;
      });
    } catch {}
  }

  // Rating
  try {
    const ratingEl = await page.$('[class*="F7nice"] span[aria-hidden="true"], [class*="MW4etd"]');
    if (ratingEl) {
      const r = parseFloat((await ratingEl.innerText()).replace(',', '.'));
      if (!isNaN(r) && r > 0 && r <= 5) result.rating = r;
    }
    if (!result.rating) {
      const starEl = await page.$('[aria-label*="bintang"], [aria-label*="star"]');
      if (starEl) {
        const label = await starEl.getAttribute('aria-label');
        const m = label?.match(/([\d,]+)/);
        if (m) { const r = parseFloat(m[1].replace(',', '.')); if (r > 0 && r <= 5) result.rating = r; }
      }
    }
  } catch {}

  // Review count — cara 1: aria-label
  try {
    const reviewEls = await page.$$('[aria-label]');
    for (const el of reviewEls) {
      const label = await el.getAttribute('aria-label');
      const match = label?.match(/([\d.,]+)\s*(ulasan|review|rating)/i);
      if (match) { result.review_count = parseInt(match[1].replace(/[.,]/g, '')); break; }
    }
    if (!result.review_count) {
      const bodyText = await page.evaluate(() => {
        const spans = [...document.querySelectorAll('span')];
        for (const s of spans) {
          const m = s.textContent.match(/^\(([0-9.,]+)\)$/);
          if (m) return m[1];
        }
        return null;
      });
      if (bodyText) result.review_count = parseInt(bodyText.replace(/[.,]/g, ''));
    }
  } catch {}

  // Address
  try {
    result.address = await page.$eval(
      'button[data-item-id="address"] .Io6YTe, [data-tooltip="Salin alamat"] .Io6YTe',
      el => el.innerText.trim()
    );
  } catch {}

  // Phone
  try {
    const raw = await page.$eval('button[data-item-id^="phone"] .Io6YTe', el => el.innerText.trim());
    const digits = raw.replace(/\D/g, '');
    if (digits) {
      result.phone = digits.startsWith('0') ? '62' + digits.slice(1)
        : digits.startsWith('62') ? digits : '62' + digits;
    }
  } catch {}

  // Website
  try {
    result.website = await page.$eval(
      'a[data-item-id="authority"] .Io6YTe, a[aria-label*="Website"] .Io6YTe',
      el => el.innerText.trim()
    );
  } catch {}

  // Opening hours
  try {
    await sleep(400);
    const hoursBtn = await page.$(
      '[aria-label*="hour" i][role="button"], [aria-label*="jam" i][role="button"], [aria-label*="buka" i][role="button"]'
    );
    if (hoursBtn) { await hoursBtn.click(); await sleep(700); }
    const hoursTable = await page.$(
      '[aria-label*="hour" i] table, table[aria-label*="hour" i], [aria-label*="jam" i] table, table[aria-label*="jam" i]'
    );
    if (hoursTable) {
      const txt = (await hoursTable.innerText()).replace(/\n/g, '').trim();
      if (txt.length > 3) result.opening_hours = txt;
    }
    if (!result.opening_hours) {
      const hoursEl = await page.$('[class*="t39EBf"], [class*="OqCZI"]');
      if (hoursEl) { const txt = (await hoursEl.innerText()).trim(); if (txt.length > 3) result.opening_hours = txt; }
    }
    if (!result.opening_hours) {
      const txt = await page.evaluate(() => {
        const days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        for (const div of document.querySelectorAll('div,tr,li')) {
          const t = div.innerText || '';
          if (days.some(d => t.includes(d)) && t.match(/\d{2}[.:]\d{2}/) && t.length < 400)
            return t.replace(/\n/g, ' ').trim();
        }
        return null;
      });
      if (txt && txt.length > 5) result.opening_hours = txt;
    }
  } catch {}

  // Coordinates from URL
  try {
    const m = page.url().match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (m) { result.lat = parseFloat(m[1]); result.lng = parseFloat(m[2]); }
  } catch {}

  // place_id (hex) from URL
  try {
    const m = page.url().match(/!1s(0x[0-9a-f]+:0x[0-9a-f]+)/i);
    if (m) result.place_id = m[1];
  } catch {}

  // Images (up to 4)
  try {
    const imgs = await page.$$eval(
      'img[src*="googleusercontent"], img[src*="lh3.google"], img[src*="lh4.google"]',
      els => els.map(e => e.src).filter(s => s && !s.includes('logo') && s.length > 50).slice(0, 4)
    );
    ['image_1','image_2','image_3','image_4'].forEach((f, i) => { if (imgs[i]) result[f] = imgs[i]; });
  } catch {}

  // Popular times
  result.popular_times = await extractPopularTimes(page);

  result.source = 'playwright-rescrape';
  return result;
}

async function extractPopularTimes(page) {
  try {
    const fromJs = await page.evaluate(() => {
      for (const script of document.querySelectorAll('script:not([src])')) {
        const t = script.textContent;
        const m = t.match(/\[(?:\[\d+(?:,\d+){23}\](?:,(?=\[))?){7}\]/);
        if (m) { try { return JSON.parse(m[0]); } catch {} }
      }
      return null;
    });
    if (fromJs && fromJs.length === 7 && fromJs.every(d => Array.isArray(d) && d.length === 24)) {
      const keys = ['sun','mon','tue','wed','thu','fri','sat'];
      const result = {};
      fromJs.forEach((d, i) => { result[keys[i]] = d; });
      return result;
    }

    const fromDom = await page.evaluate(() => {
      const selectors = ['[jslog*="popular_times"]','[aria-label*="Popular times"]',
                         '[aria-label*="Jam ramai"]','[aria-label*="Jam populer"]'];
      let container = null;
      for (const s of selectors) { container = document.querySelector(s); if (container) break; }
      if (!container) return null;

      const dayMap = { 'minggu':0,'senin':1,'selasa':2,'rabu':3,'kamis':4,'jumat':5,'sabtu':6,
                       'sunday':0,'monday':1,'tuesday':2,'wednesday':3,'thursday':4,'friday':5,'saturday':6 };
      const days = [{},{},{},{},{},,{}];
      let currentDay = -1;
      for (const bar of container.querySelectorAll('[aria-label]')) {
        const lbl = (bar.getAttribute('aria-label') || '').toLowerCase();
        for (const [name, idx] of Object.entries(dayMap)) {
          if (lbl === name) { currentDay = idx; break; }
        }
        const pctMatch = lbl.match(/(\d{1,3})%/);
        const hrMatch  = lbl.match(/pukul (\d{1,2})\.(\d{2})|(\d{1,2})\s?(am|pm)/i);
        if (pctMatch && hrMatch && currentDay >= 0) {
          let hr = hrMatch[3] ? parseInt(hrMatch[3]) : parseInt(hrMatch[1]);
          const pm = hrMatch[4] && hrMatch[4].toLowerCase() === 'pm';
          if (pm && hr !== 12) hr += 12;
          if (!pm && hr === 12) hr = 0;
          if (!days[currentDay]) days[currentDay] = {};
          days[currentDay][hr] = parseInt(pctMatch[1]);
        }
      }
      const result = {};
      const keys = ['sun','mon','tue','wed','thu','fri','sat'];
      let hasData = false;
      days.forEach((d, i) => {
        if (d && Object.keys(d).length > 0) {
          hasData = true;
          const arr = Array(24).fill(0);
          for (const [h, v] of Object.entries(d)) arr[parseInt(h)] = v;
          result[keys[i]] = arr;
        }
      });
      return hasData ? result : null;
    });
    return fromDom;
  } catch { return null; }
}

// ── main ─────────────────────────────────────────────────────────────────────

(async () => {
  if (!API_TOKEN) { console.error('❌ MAFAZA_API_TOKEN tidak diset'); process.exit(1); }

  // Load Google cookies if available
  let googleCookies = [];
  if (fs.existsSync(COOKIE_FILE)) {
    try {
      const raw = JSON.parse(fs.readFileSync(COOKIE_FILE, 'utf8'));
      // Support both EditThisCookie and Cookie-Editor formats
      googleCookies = raw.map(c => ({
        name:     c.name,
        value:    c.value,
        domain:   c.domain || '.google.com',
        path:     c.path || '/',
        expires:  c.expirationDate || c.expires || -1,
        httpOnly: c.httpOnly || false,
        secure:   c.secure || false,
        sameSite: c.sameSite === 'no_restriction' ? 'None' : (c.sameSite === 'lax' ? 'Lax' : (c.sameSite === 'strict' ? 'Strict' : 'None')),
      })).filter(c => c.name && c.value);
      console.log(`🍪 Loaded ${googleCookies.length} cookies dari ${COOKIE_FILE}`);
    } catch (e) {
      console.warn('⚠ Gagal load cookies:', e.message);
    }
  } else {
    console.warn('⚠ File google-cookies.json tidak ditemukan — popular_times mungkin tidak tersedia');
  }

  console.log(`🔄 Mulai rescraping hingga ${TOTAL} tempat dengan data tidak lengkap...`);

  const browser = await chromium.launch({
    headless: HEADLESS,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--lang=id-ID'],
  });
  const context = await browser.newContext({
    locale: 'id-ID',
    geolocation: { latitude: -7.2575, longitude: 112.7521 },
    permissions: ['geolocation'],
    userAgent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
  });
  if (googleCookies.length) await context.addCookies(googleCookies);
  const page = await context.newPage();
  await page.setExtraHTTPHeaders({ 'Accept-Language': 'id-ID,id;q=0.9' });

  let totalOk = 0, totalFail = 0, processed = 0;

  while (processed < TOTAL) {
    const batchSize = Math.min(BATCH, TOTAL - processed);
    let places;
    try {
      places = await fetchBatch(batchSize);
    } catch (e) {
      console.error('❌ Gagal ambil batch dari API:', e.message);
      break;
    }

    if (!places.length) {
      console.log('✅ Tidak ada lagi tempat yang perlu diperbarui!');
      break;
    }

    console.log(`\n📋 Batch baru: ${places.length} tempat (total diproses: ${processed}/${TOTAL})`);

    for (let i = 0; i < places.length; i++) {
      const place = places[i];
      const n = processed + i + 1;
      const label = `[${n}/${Math.min(TOTAL, processed + places.length)}]`;
      const missing = [];
      if (!place.opening_hours) missing.push('jam_buka');
      if (!place.review_count || place.review_count == 0) missing.push('ulasan');
      console.log(`${label} 🌐 ${place.name}`);
      if (missing.length) console.log(`${label}    perlu: ${missing.join(', ')}`);

      try {
        await page.goto(place.maps_url, { waitUntil: 'domcontentloaded', timeout: 20000 });

        // Cek redirect (tempat tidak ditemukan)
        const finalUrl = page.url();
        if (!finalUrl.includes('/maps/place/') || finalUrl.match(/\/@[\d.-]+,[\d.-]+,\d+z\/data=.*entry=ttu/)) {
          console.log(`${label} ⚠ Tempat tidak ditemukan di Google Maps`);
          totalFail++;
          if (i < places.length - 1) await sleep(rand(DELAY_MIN, DELAY_MAX));
          continue;
        }

        await page.waitForSelector('h1', { timeout: 15000 }).catch(() => {});
        await page.waitForSelector('[class*="F7nice"], [class*="fontBodyMedium"]', { timeout: 5000 }).catch(() => {});
        await sleep(rand(1000, 1800));

        const data = await extractFromPage(page);
        if (!data) {
          console.log(`${label} ⚠ Tidak bisa extract — timeout`);
          totalFail++;
        } else {
          const update = {};
          const FIELDS = ['name','category','rating','review_count','address','phone','website',
                          'opening_hours','lat','lng','place_id','image_1','image_2','image_3','image_4',
                          'description','permanently_closed','popular_times','source'];
          for (const f of FIELDS) {
            if (f === 'popular_times') {
              // Kirim {} jika tidak ada data (penanda "sudah dicek, tidak tersedia")
              // Kirim object sesungguhnya jika ada data
              // undefined → skip (field tidak di-extract)
              if (data[f] !== undefined) update[f] = data[f] ?? {};
            } else if (data[f] !== undefined && data[f] !== null && data[f] !== '') {
              update[f] = data[f];
            }
          }

          if (Object.keys(update).length <= 1) {
            console.log(`${label} ⚠ Tidak ada data baru`);
            totalFail++;
          } else {
            const ok = await patchPlace(place.id, update);
            const ptStatus = update.popular_times && Object.keys(update.popular_times).length > 0 ? '✓' : (update.popular_times !== undefined ? '∅' : '–');
            const got = `hours:${update.opening_hours?'✓':'–'} ulasan:${update.review_count??'–'} rating:${update.rating??'–'} pt:${ptStatus} tutup:${update.permanently_closed?'YA':'–'}`;
            if (ok) { console.log(`${label} ✓ ${got}`); totalOk++; }
            else    { console.log(`${label} ✗ PATCH gagal`); totalFail++; }
          }
        }
      } catch (err) {
        console.log(`${label} ✗ ${err.message.split('\n')[0]}`);
        totalFail++;
      }

      if (i < places.length - 1) await sleep(rand(DELAY_MIN, DELAY_MAX));
    }

    processed += places.length;
    if (places.length < batchSize) break; // tidak ada lagi data
  }

  await browser.close();

  console.log('');
  console.log(`📊 Selesai: ${totalOk} berhasil, ${totalFail} gagal dari ${processed} diproses`);
  console.log(`__SUMMARY__ Selesai: ${totalOk} berhasil, ${totalFail} gagal`);
})();
