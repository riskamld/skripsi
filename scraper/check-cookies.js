/**
 * check-cookies.js
 * Validates google-cookies.json by loading a known Google Maps page
 * and checking if login is active + popular times data is accessible.
 * Outputs a single JSON line to stdout.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const COOKIE_FILE = path.join(__dirname, 'google-cookies.json');
// Jember Roxy Square — known to have popular times data
const TEST_URL = 'https://www.google.com/maps/place/Jember+Roxy+Square/@-8.1836449,113.6630745,17z/data=!4m7!3m6!1s0x2dd693fe1d4f0dd1:0x2d9587af4a3c612d!8m2!3d-8.1836449!4d113.6630745!16s%2Fg%2F1pzs23njj';

function out(obj) { process.stdout.write(JSON.stringify(obj) + '\n'); }

(async () => {
  if (!fs.existsSync(COOKIE_FILE)) {
    return out({ valid: false, message: 'File google-cookies.json tidak ditemukan.' });
  }

  let cookies;
  try {
    const raw = JSON.parse(fs.readFileSync(COOKIE_FILE, 'utf8'));
    cookies = raw.map(c => ({
      name:     c.name,
      value:    c.value,
      domain:   c.domain || '.google.com',
      path:     c.path || '/',
      expires:  c.expirationDate || c.expires || -1,
      httpOnly: c.httpOnly || false,
      secure:   c.secure || false,
      sameSite: c.sameSite === 'no_restriction' ? 'None'
              : c.sameSite === 'lax'            ? 'Lax'
              : c.sameSite === 'strict'          ? 'Strict'
              : 'None',
    })).filter(c => c.name && c.value);
  } catch (e) {
    return out({ valid: false, message: 'Format cookies tidak valid: ' + e.message });
  }

  // Check expiry
  const now = Date.now() / 1000;
  const expired = cookies.filter(c => c.expires > 0 && c.expires < now);
  if (expired.length > cookies.length * 0.5) {
    return out({ valid: false, message: `Cookies sudah expired (${expired.length} dari ${cookies.length} kadaluarsa).` });
  }

  let browser;
  try {
    browser = await chromium.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--lang=id-ID'],
      timeout: 15000,
    });
    const ctx = await browser.newContext({
      locale: 'id-ID',
      userAgent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    });
    await ctx.addCookies(cookies);
    const page = await ctx.newPage();

    await page.goto(TEST_URL, { waitUntil: 'domcontentloaded', timeout: 20000 });
    await page.waitForSelector('h1', { timeout: 8000 }).catch(() => {});
    await new Promise(r => setTimeout(r, 2500));

    // Scroll to load popular times
    const panel = await page.$('[role="main"]');
    if (panel) {
      for (let i = 0; i < 6; i++) {
        await panel.evaluate(el => el.scrollBy(0, 400));
        await new Promise(r => setTimeout(r, 300));
      }
    }
    await new Promise(r => setTimeout(r, 1500));

    const checks = await page.evaluate(() => {
      const text = document.body.innerText || '';
      const hasLimited  = text.includes('tampilan terbatas');
      const hasLogin    = text.includes('Masuk ke akun') || text.includes('Login');
      const hasPt       = !!document.querySelector('[aria-label*="ramai pada pukul"]');
      const title       = document.title || '';
      return { hasLimited, hasLogin, hasPt, title };
    });

    await browser.close();

    const loggedIn = !checks.hasLimited && !checks.hasLogin;

    if (!loggedIn) {
      return out({
        valid: false,
        message: 'Cookies tidak valid atau sudah expired — Google Maps menampilkan versi terbatas.',
      });
    }

    return out({
      valid: true,
      message: checks.hasPt
        ? `✅ Cookies valid! Login aktif dan data Jam Ramai dapat diakses.`
        : `✅ Cookies valid! Login Google aktif (${cookies.length} cookies). Jam Ramai akan ter-scrape saat rescrape dijalankan.`,
      count: cookies.length,
    });

  } catch (e) {
    if (browser) await browser.close().catch(() => {});
    return out({ valid: false, message: 'Error: ' + e.message.split('\n')[0] });
  }
})();
