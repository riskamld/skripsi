#!/usr/bin/env node
/**
 * Google Maps Playwright Scraper untuk Mafaza Fortuna
 * Usage: node gmaps-scraper.js "toko buah" "Bandung" [limit] [--dry-run]
 *
 * Output: POST ke Laravel API atau simpan ke JSON jika --dry-run
 */

const { chromium } = require("playwright");
const https = require("https");
const http = require("http");
const fs = require("fs");
const path = require("path");

// ── Config ──────────────────────────────────────────────────────────────────
const CONFIG = {
  apiUrl: process.env.MAFAZA_API_URL || "https://fezora.net/mafaza/public/api/places",
  apiToken: process.env.MAFAZA_API_TOKEN || "",
  waApiUrl: process.env.WA_API_URL || "http://localhost:8000",
  waDeviceId: process.env.WA_DEVICE_ID || "device-1780035414781",
  headless: process.env.HEADLESS !== "false",
  slowMo: parseInt(process.env.SLOW_MO || "0"),
};

// URL endpoint existing-ids (sama base dengan apiUrl)
CONFIG.existingIdsUrl = CONFIG.apiUrl.replace(/\/places$/, '/places/existing-ids');

// ── CLI Args ─────────────────────────────────────────────────────────────────
const args = process.argv.slice(2);
const query = args[0] || "toko buah";
const area = args[1] || "";
const limit = parseInt(args[2] || "20");
const isDryRun = args.includes("--dry-run");
const outputFile = args.find((a) => a.startsWith("--output="))?.split("=")[1];

// Koordinat dari env (diset oleh ScraperController)
const targetLat  = parseFloat(process.env.LAT  || "");
const targetLng  = parseFloat(process.env.LNG  || "");
const targetZoom = parseInt(process.env.ZOOM || "14");
const useCoords  = !isNaN(targetLat) && !isNaN(targetLng);

if (!query) {
  console.error("Usage: node gmaps-scraper.js <query> [area] [limit]");
  process.exit(1);
}

const searchQuery = area ? `${query} ${area}` : query;

// ── Helpers ──────────────────────────────────────────────────────────────────

// Haversine distance dalam km
function haversineKm(lat1, lng1, lat2, lng2) {
  const R = 6371;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLng = (lng2 - lng1) * Math.PI / 180;
  const a = Math.sin(dLat/2)**2 +
            Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLng/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

// Radius maksimum (km) berdasarkan zoom
function maxRadiusKm(zoom) {
  const map = { 17:2, 16:3, 15:5, 14:15, 13:40, 12:60, 11:80, 10:150 };
  return map[zoom] ?? 50;
}

function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms));
}

function randomDelay(min = 1000, max = 3000) {
  return sleep(Math.floor(Math.random() * (max - min) + min));
}

function extractCoordinatesFromUrl(url) {
  const match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
  if (match) return { lat: parseFloat(match[1]), lng: parseFloat(match[2]) };
  return { lat: null, lng: null };
}

function extractPlaceIdFromUrl(url) {
  const match = url.match(/place\/[^/]+\/([^/]+)/);
  if (match) return match[1];
  const chunkMatch = url.match(/1s([^!]+)!/);
  if (chunkMatch) return chunkMatch[1];
  return null;
}

function cleanPhone(phone) {
  if (!phone) return null;
  return phone.replace(/[\s\-()]/g, "").replace(/^0/, "62");
}

// Hapus karakter Private Use Area (icon Google Maps) dan karakter kontrol tak terlihat
function cleanText(str) {
  if (!str) return str;
  return str
    .replace(/[-]/g, "")   // Private Use Area (Google Maps icons)
    .replace(/[​-‍﻿]/g, "") // Zero-width chars
    .replace(/\s{2,}/g, " ")            // Spasi ganda jadi satu
    .trim();
}

// Ambil place_id yang sudah ada di DB untuk area ini
async function fetchExistingIds(area) {
  return new Promise((resolve) => {
    if (!area || !CONFIG.apiToken) return resolve(new Set());
    const url = new URL(CONFIG.existingIdsUrl + '?area=' + encodeURIComponent(area));
    const isHttps = url.protocol === 'https:';
    const lib = isHttps ? https : http;
    const options = {
      hostname: url.hostname,
      port: url.port || (isHttps ? 443 : 80),
      path: url.pathname + url.search,
      method: 'GET',
      headers: { 'X-API-Token': CONFIG.apiToken },
    };
    const req = lib.request(options, (res) => {
      let body = '';
      res.on('data', (c) => (body += c));
      res.on('end', () => {
        try { resolve(new Set(JSON.parse(body).place_ids || [])); }
        catch { resolve(new Set()); }
      });
    });
    req.on('error', () => resolve(new Set()));
    req.end();
  });
}

async function postToApi(place) {
  return new Promise((resolve, reject) => {
    const data = JSON.stringify(place);
    const url = new URL(CONFIG.apiUrl);
    const isHttps = url.protocol === "https:";
    const lib = isHttps ? https : http;

    const options = {
      hostname: url.hostname,
      port: url.port || (isHttps ? 443 : 80),
      path: url.pathname,
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Content-Length": Buffer.byteLength(data),
        "X-API-Token": CONFIG.apiToken,
      },
    };

    const req = lib.request(options, (res) => {
      let body = "";
      res.on("data", (chunk) => (body += chunk));
      res.on("end", () => resolve({ status: res.statusCode, body }));
    });

    req.on("error", reject);
    req.write(data);
    req.end();
  });
}

// ── Popular Times Parser ──────────────────────────────────────────────────────
async function extractPopularTimes(page) {
  try {
    // Method 1: ambil dari data JS yang di-embed Google Maps di page source
    const fromJs = await page.evaluate(() => {
      for (const script of document.querySelectorAll('script:not([src])')) {
        const t = script.textContent;
        // Pola: 7 array masing-masing 24 angka (jam 0-23, nilai 0-100)
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

    // Method 2: parse dari tinggi bar elemen DOM (height px → 0-100%)
    const fromDom = await page.evaluate(() => {
      // Cari container popular times (berbagai selector Google Maps)
      const selectors = [
        '[jslog*="popular_times"]',
        '[aria-label*="Popular times"]',
        '[aria-label*="Jam ramai"]',
        '[aria-label*="Jam populer"]',
      ];
      let container = null;
      for (const s of selectors) {
        container = document.querySelector(s);
        if (container) break;
      }
      if (!container) return null;

      // Cari tab hari dan bar-bar jam
      const dayMap  = { 'minggu':0,'senin':1,'selasa':2,'rabu':3,'kamis':4,'jumat':5,'sabtu':6,
                        'sunday':0,'monday':1,'tuesday':2,'wednesday':3,'thursday':4,'friday':5,'saturday':6 };
      const days    = [{},{},{},{},{},{},{}];
      const maxH    = 64; // pixel max bar Google Maps

      // Coba baca dari aria-label bar: "Biasanya sibuk pukul 14.00, 80%"
      const bars = container.querySelectorAll('[aria-label]');
      let currentDay = -1;
      for (const bar of bars) {
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
          days[currentDay][hr] = parseInt(pctMatch[1]);
        }
      }

      // Konversi ke array 24 jam
      const result = {};
      const keys   = ['sun','mon','tue','wed','thu','fri','sat'];
      let hasData  = false;
      days.forEach((d, i) => {
        if (Object.keys(d).length > 0) {
          hasData = true;
          const arr = Array(24).fill(0);
          for (const [h, v] of Object.entries(d)) arr[parseInt(h)] = v;
          result[keys[i]] = arr;
        }
      });
      return hasData ? result : null;
    });

    return fromDom;
  } catch {
    return null;
  }
}

// ── Extract Place Detail ──────────────────────────────────────────────────────
async function extractPlaceDetail(page, url) {
  const detail = {
    place_id: null,
    name: null,
    lat: null,
    lng: null,
    maps_url: url,
    rating: null,
    review_count: null,
    category: null,
    address: null,
    phone: null,
    website: null,
    opening_hours: null,
    popular_times: null,
    price_level: null,
    permanently_closed: false,
    image_1: null,
    image_2: null,
    image_3: null,
    image_4: null,
    source: "playwright",
    parser_version: "1.0",
  };

  // Koordinat & place_id dari URL
  const currentUrl = page.url();
  const coords = extractCoordinatesFromUrl(currentUrl);
  detail.lat = coords.lat;
  detail.lng = coords.lng;

  // Ambil place_id bersih dari hex ID di URL (format: 0xABC:0xDEF)
  const hexMatch = currentUrl.match(/!1s(0x[0-9a-f]+:0x[0-9a-f]+)/i);
  detail.place_id = hexMatch ? hexMatch[1] : `gm_${Date.now()}`;

  // Nama tempat
  try {
    const nameEl = await page.$('h1');
    if (nameEl) detail.name = cleanText(await nameEl.innerText());
  } catch {}

  // Rating
  try {
    const ratingEl = await page.$('[class*="F7nice"] span[aria-hidden="true"]');
    if (ratingEl) detail.rating = parseFloat(await ratingEl.innerText());
  } catch {}

  // Review count — coba beberapa sumber
  try {
    // Cara 1: aria-label pada elemen dengan jumlah ulasan
    const reviewEls = await page.$$('[aria-label]');
    for (const el of reviewEls) {
      const label = await el.getAttribute("aria-label");
      const match = label?.match(/([\d.,]+)\s*(ulasan|review|rating)/i);
      if (match) {
        detail.review_count = parseInt(match[1].replace(/[.,]/g, ""));
        break;
      }
    }

    // Cara 2: teks "(X)" di dekat rating
    if (!detail.review_count) {
      const bodyText = await page.evaluate(() => {
        const spans = [...document.querySelectorAll('span')];
        for (const s of spans) {
          const m = s.textContent.match(/^\(([0-9.,]+)\)$/);
          if (m) return m[1];
        }
        return null;
      });
      if (bodyText) detail.review_count = parseInt(bodyText.replace(/[.,]/g, ""));
    }
  } catch {}

  // Kategori
  try {
    const catEl = await page.$('button[jsaction*="category"]');
    if (catEl) detail.category = cleanText(await catEl.innerText());
    else {
      // Fallback: ambil dari teks kecil di bawah nama
      const spans = await page.$$('span[jsan]');
      for (const s of spans) {
        const txt = cleanText(await s.innerText());
        if (txt && txt.length < 50 && !txt.includes("·")) {
          detail.category = txt;
          break;
        }
      }
    }
  } catch {}

  // Alamat
  try {
    const addressBtn = await page.$('[data-item-id="address"]');
    if (addressBtn) {
      detail.address = cleanText(await addressBtn.innerText());
    } else {
      const addressEl = await page.$('[aria-label*="Address"]');
      if (addressEl) detail.address = cleanText((await addressEl.innerText()).replace(/^Address:\s*/i, ""));
    }
  } catch {}

  // Telepon
  try {
    const telLink = await page.$('[href^="tel:"]');
    if (telLink) {
      const href = await telLink.getAttribute("href");
      detail.phone = cleanPhone(href.replace("tel:", ""));
    } else {
      const phoneBtn = await page.$('[data-item-id^="phone:"]');
      if (phoneBtn) detail.phone = cleanPhone((await phoneBtn.innerText()).trim());
    }
  } catch {}

  // Website
  try {
    const webEl = await page.$('[data-item-id="authority"]');
    if (webEl) {
      detail.website = await webEl.getAttribute("href") || (await webEl.innerText()).trim();
    }
  } catch {}

  // Opening hours
  try {
    // Klik tombol jam buka agar expanded
    const hoursBtn = await page.$('[aria-label*="hour" i][role="button"]');
    if (hoursBtn) {
      await hoursBtn.click();
      await sleep(500);
    }
    const hoursTable = await page.$('[aria-label*="hour" i] table, table[aria-label*="hour" i]');
    if (hoursTable) {
      detail.opening_hours = cleanText(await hoursTable.innerText());
    } else {
      // Fallback: ambil teks dari area jam
      const hoursEl = await page.$('[class*="t39EBf"]');
      if (hoursEl) detail.opening_hours = cleanText(await hoursEl.innerText());
    }
  } catch {}

  // Price level ($ symbols)
  try {
    const priceEl = await page.$('[aria-label*="Price"]');
    if (priceEl) detail.price_level = (await priceEl.getAttribute("aria-label"))?.replace(/[^$]/g, "") || null;
  } catch {}

  // Permanently closed
  try {
    const closedEls = await page.$$('[class*=""]');
    const bodyText = await page.evaluate(() => document.body.innerText);
    if (bodyText.includes("Permanently closed") || bodyText.includes("Tutup permanen")) {
      detail.permanently_closed = true;
    }
  } catch {}

  // Popular times
  detail.popular_times = await extractPopularTimes(page);

  // Gambar — deduplicate berdasarkan hash URL (sebelum =w) agar foto berbeda
  try {
    const imgs = await page.$$('img[src*="googleusercontent"], img[src*="ggpht"]');
    const imgUrls = [];
    const seenHashes = new Set();
    for (const img of imgs) {
      if (imgUrls.length >= 4) break;
      const src = await img.getAttribute("src");
      if (!src || src.includes("icon") || src.includes("avatar") || src.includes("/ogw/") || src.includes("=s32") || src.includes("=s64") || src.includes("-c-mo")) continue;
      const hash = src.split('=')[0];
      if (seenHashes.has(hash)) continue;
      seenHashes.add(hash);
      imgUrls.push(src);
    }
    if (imgUrls[0]) detail.image_1 = imgUrls[0];
    if (imgUrls[1]) detail.image_2 = imgUrls[1];
    if (imgUrls[2]) detail.image_3 = imgUrls[2];
    if (imgUrls[3]) detail.image_4 = imgUrls[3];
  } catch {}

  return detail;
}

// ── Main Scraper ──────────────────────────────────────────────────────────────
async function scrape() {
  console.log(`\n🔍 Mencari: "${searchQuery}" (limit: ${limit})`);
  console.log(`Mode: ${isDryRun ? "DRY RUN" : "LIVE → API"}\n`);

  const browser = await chromium.launch({
    headless: CONFIG.headless,
    slowMo: CONFIG.slowMo,
    args: [
      "--no-sandbox",
      "--disable-setuid-sandbox",
      "--disable-blink-features=AutomationControlled",
      "--disable-dev-shm-usage",
      "--disable-gpu",
      "--lang=id-ID,id",
    ],
  });

  const context = await browser.newContext({
    userAgent:
      "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
    locale: "id-ID",
    timezoneId: "Asia/Jakarta",
    viewport: { width: 1366, height: 768 },
  });

  // Sembunyikan webdriver flag
  await context.addInitScript(() => {
    Object.defineProperty(navigator, "webdriver", { get: () => undefined });
    window.chrome = { runtime: {} };
  });

  const page = await context.newPage();

  const results = [];
  const failed = [];

  try {
    const mapsUrl = useCoords
      ? `https://www.google.com/maps/search/${encodeURIComponent(query)}/@${targetLat},${targetLng},${targetZoom}z`
      : `https://www.google.com/maps/search/${encodeURIComponent(searchQuery)}`;

    if (useCoords) {
      console.log(`📍 Mode koordinat: lat=${targetLat}, lng=${targetLng}, zoom=${targetZoom}`);
    }
    console.log(`🌐 Buka: ${mapsUrl}`);
    await page.goto(mapsUrl, { waitUntil: "domcontentloaded", timeout: 30000 });
    // Tunggu panel hasil muncul
    await page.waitForSelector('div[role="feed"], div[jsaction*="result"]', { timeout: 15000 }).catch(() => {});
    await randomDelay(2000, 4000);

    // Tutup popup jika ada
    try {
      const rejectBtn = await page.$('[aria-label*="Reject all"], [aria-label*="Tolak"]');
      if (rejectBtn) {
        await rejectBtn.click();
        await sleep(1000);
      }
    } catch {}

    // Scroll panel hasil sampai limit tercapai
    console.log(`📋 Mengumpulkan link hasil...`);
    const feed = await page.$('div[role="feed"]');
    if (!feed) {
      console.error("❌ Panel hasil tidak ditemukan. Coba lagi.");
      await browser.close();
      return;
    }

    let placeLinks = [];
    let prevCount = 0;
    let stuckCount = 0;

    while (placeLinks.length < limit && stuckCount < 5) {
      // Ambil semua link place yang ada
      const links = await page.$$eval('a[href*="/maps/place/"]', (els) =>
        els.map((a) => a.href).filter((h, i, arr) => arr.indexOf(h) === i)
      );
      placeLinks = [...new Set(links)].slice(0, limit);

      if (placeLinks.length === prevCount) {
        stuckCount++;
      } else {
        stuckCount = 0;
        prevCount = placeLinks.length;
      }

      if (placeLinks.length >= limit) break;

      // Scroll ke bawah feed
      await page.evaluate(() => {
        const feed = document.querySelector('div[role="feed"]');
        if (feed) feed.scrollTop += 800;
      });
      await randomDelay(1500, 2500);

      // Cek apakah sudah habis
      const endText = await page.$('span[class*="HlvSq"]');
      if (endText) break;
    }

    console.log(`✅ Ditemukan ${placeLinks.length} link tempat\n`);

    // Filter: lewati place_id yang sudah ada di DB
    let existingIds = new Set();
    if (!isDryRun && area) {
      existingIds = await fetchExistingIds(area);
      console.log(`📂 DB sudah punya ${existingIds.size} tempat di area "${area}"`);
    }
    const newLinks    = placeLinks.filter(u => !existingIds.has(extractPlaceIdFromUrl(u)));
    const skippedCount = placeLinks.length - newLinks.length;
    if (skippedCount > 0) console.log(`⏭  Skip ${skippedCount} tempat (sudah di DB) → proses ${newLinks.length} tempat baru\n`);

    // Buka tiap tempat baru
    let selectorChecked = false;
    for (let i = 0; i < newLinks.length; i++) {
      const url = newLinks[i];
      console.log(`[${i + 1}/${newLinks.length}] Buka detail...`);

      try {
        await page.goto(url, { waitUntil: "domcontentloaded", timeout: 20000 });
        await randomDelay(2000, 3500);

        const detail = await extractPlaceDetail(page, url);

        // Opsi 3: cek field utama di tempat pertama saja
        if (!selectorChecked) {
          selectorChecked = true;
          if (!detail.name) {
            console.error(`\n⚠️ SELECTOR_BROKEN: field 'name' kosong di tempat pertama`);
            console.error(`   Kemungkinan struktur DOM Google Maps berubah — scraping dihentikan.\n`);
            process.exit(2);
          }
        }

        // Perbarui koordinat dari URL setelah redirect
        const finalCoords = extractCoordinatesFromUrl(page.url());
        if (finalCoords.lat) {
          detail.lat = finalCoords.lat;
          detail.lng = finalCoords.lng;
        }

        // Filter jarak — buang hasil yang terlalu jauh dari target
        if (useCoords) {
          const maxKm = maxRadiusKm(targetZoom);
          if (!detail.lat || !detail.lng) {
            // Koordinat tidak dapat diekstrak — tolak agar tidak masuk data jauh
            console.log(`  ⏭ Dilewati (koordinat tidak ditemukan): ${detail.name}`);
            failed.push({ url, error: 'no-coords' });
            continue;
          }
          const distKm = haversineKm(targetLat, targetLng, detail.lat, detail.lng);
          if (distKm > maxKm) {
            console.log(`  ⏭ Dilewati (${distKm.toFixed(0)}km dari target, maks ${maxKm}km): ${detail.name}`);
            failed.push({ url, error: `out-of-area (${distKm.toFixed(0)}km)` });
            continue;
          }
          console.log(
            `  ✓ ${detail.name || "(tanpa nama)"} | ${detail.phone || "no phone"} | rating: ${detail.rating || "-"} | reviews: ${detail.review_count || "-"} | ${distKm.toFixed(1)}km`
          );
        } else {
          console.log(
            `  ✓ ${detail.name || "(tanpa nama)"} | ${detail.phone || "no phone"} | rating: ${detail.rating || "-"} | reviews: ${detail.review_count || "-"}`
          );
        }

        results.push(detail);

        if (!isDryRun && CONFIG.apiToken) {
          try {
            const res = await postToApi(detail);
            console.log(`  → API: ${res.status}`);
          } catch (e) {
            console.warn(`  → API gagal: ${e.message}`);
          }
        }

        await randomDelay(1500, 3000);
      } catch (e) {
        console.warn(`  ✗ Gagal: ${e.message}`);
        failed.push({ url, error: e.message });
      }
    }
  } finally {
    await browser.close();
  }

  // Output
  console.log(`\n📊 Selesai: ${results.length} berhasil, ${failed.length} gagal`);
  console.log(`  📞 Punya nomor telepon: ${results.filter((r) => r.phone).length}`);
  console.log(`  ⭐ Rata-rata rating: ${(results.reduce((s, r) => s + (r.rating || 0), 0) / (results.filter((r) => r.rating).length || 1)).toFixed(2)}`);

  if (outputFile || isDryRun) {
    const outPath = outputFile || path.join(__dirname, `results_${Date.now()}.json`);
    fs.writeFileSync(outPath, JSON.stringify(results, null, 2));
    console.log(`  💾 Disimpan ke: ${outPath}`);
  }

  return results;
}

scrape().catch((e) => {
  console.error("Fatal error:", e);
  process.exit(1);
});
