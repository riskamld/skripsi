<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Place;
use Illuminate\Http\Request;

class ScraperController extends Controller
{
    private string $scraperPath;
    private string $logDir;

    public function __construct()
    {
        $this->scraperPath = base_path('scraper/gmaps-scraper.js');
        $this->logDir      = storage_path('scraper-logs');

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function index()
    {
        $stats = $this->dbStats();

        // Compact lat/lng for map dots (max 600)
        $existingPlaces = Place::whereNotNull('lat')->whereNotNull('lng')
            ->where('lat', '!=', 0)->where('lng', '!=', 0)
            ->select(['lat', 'lng', 'name', 'category', 'rating', 'review_count', 'has_whatsapp'])
            ->limit(1000)
            ->get()
            ->map(fn($p) => [
                (float)$p->lat,
                (float)$p->lng,
                $p->name,
                $p->category,
                $p->rating,
                $p->review_count,
                $p->has_whatsapp,  // true/false/null
            ]);

        return view('scraper.index', compact('stats', 'existingPlaces'));
    }

    public function stats()
    {
        return response()->json($this->dbStats());
    }

    public function start(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:100',
            'area'  => 'nullable|string|max:100',
            'lat'   => 'nullable|numeric|between:-90,90',
            'lng'   => 'nullable|numeric|between:-180,180',
            'zoom'  => 'nullable|integer|between:10,17',
            'limit' => 'required|integer|min:1|max:100',
        ]);

        $token = ApiToken::where('is_active', true)->value('token');
        if (!$token) {
            return response()->json(['error' => 'Tidak ada API token aktif. Buat token terlebih dahulu.'], 422);
        }

        $jobId   = uniqid('scrape_', true);
        $logFile = $this->logDir . "/{$jobId}.log";

        $query   = escapeshellarg($request->input('query'));
        $area    = escapeshellarg($request->input('area', ''));
        $limit   = (int) $request->input('limit', 20);
        $node    = trim(shell_exec('which node') ?: '/usr/bin/node');
        $scraper = escapeshellarg($this->scraperPath);

        $lat  = $request->lat  ? number_format((float)$request->lat,  7, '.', '') : '';
        $lng  = $request->lng  ? number_format((float)$request->lng,  7, '.', '') : '';
        $zoom = $request->zoom ? (int)$request->zoom : '';

        $cmd = "MAFAZA_API_TOKEN={$token} HEADLESS=true"
             . ($lat  ? " LAT={$lat}"   : '')
             . ($lng  ? " LNG={$lng}"   : '')
             . ($zoom ? " ZOOM={$zoom}" : '')
             . " {$node} {$scraper} {$query} {$area} {$limit}"
             . " >> " . escapeshellarg($logFile) . " 2>&1"
             . " && echo '__DONE_OK__' >> " . escapeshellarg($logFile)
             . " || echo '__DONE_ERROR__' >> " . escapeshellarg($logFile);

        exec("nohup bash -c " . escapeshellarg($cmd) . " &");

        file_put_contents($this->logDir . "/{$jobId}.meta", json_encode([
            'query'      => $request->input('query'),
            'area'       => $request->input('area'),
            'lat'        => $request->input('lat'),
            'lng'        => $request->input('lng'),
            'zoom'       => $request->input('zoom'),
            'limit'      => $limit,
            'started_at' => now()->toISOString(),
        ]));

        return response()->json(['job_id' => $jobId, 'limit' => $limit]);
    }

    public function log(string $jobId)
    {
        if (!preg_match('/^scrape_[a-f0-9._]+$/', $jobId)) {
            return response()->json(['error' => 'Invalid job ID'], 400);
        }

        $logFile = $this->logDir . "/{$jobId}.log";

        if (!file_exists($logFile)) {
            return response()->json(['lines' => [], 'done' => false, 'status' => 'waiting', 'processed' => 0]);
        }

        $content = file_get_contents($logFile);
        $lines   = array_values(array_filter(explode("\n", $content)));

        $done      = false;
        $status    = 'running';
        $processed = 0;

        // Hitung berapa tempat sudah diproses dari pola "[X/Y]"
        foreach ($lines as $line) {
            if (preg_match('/^\[(\d+)\/\d+\]/', $line)) {
                $processed = (int) explode('/', trim(explode('[', $line)[1]))[0];
            }
        }

        if (str_contains($content, '__DONE_OK__')) {
            $done   = true;
            $status = 'success';
            $lines  = array_values(array_filter($lines, fn($l) => !str_contains($l, '__DONE_')));
        } elseif (str_contains($content, '__DONE_ERROR__')) {
            $done   = true;
            $status = 'error';
            $lines  = array_values(array_filter($lines, fn($l) => !str_contains($l, '__DONE_')));
        }

        return response()->json([
            'lines'     => $lines,
            'done'      => $done,
            'status'    => $status,
            'processed' => $processed,
        ]);
    }

    public function rescrapeCount()
    {
        $count = Place::whereNotNull('maps_url')
            ->where('maps_url', '!=', '')
            ->where(function ($q) {
                $q->whereNull('opening_hours')
                  ->orWhere('opening_hours', '')
                  ->orWhereNull('review_count')
                  ->orWhere('review_count', 0);
            })
            ->count();

        return response()->json(['count' => $count]);
    }

    public function rescrape(Request $request)
    {
        $request->validate([
            'limit' => 'required|integer|min:1|max:1000',
        ]);

        $token = ApiToken::where('is_active', true)->value('token');
        if (!$token) {
            return response()->json(['error' => 'Tidak ada API token aktif.'], 422);
        }

        $jobId   = uniqid('scrape_', true);
        $logFile = $this->logDir . "/{$jobId}.log";
        $limit   = (int) $request->limit;

        $rescraperPath = base_path('scraper/gmaps-rescraper.js');
        $node    = trim(shell_exec('which node') ?: '/usr/bin/node');
        $scraper = escapeshellarg($rescraperPath);

        $cmd = "MAFAZA_API_TOKEN={$token} HEADLESS=true LIMIT={$limit} {$node} {$scraper}"
             . " >> " . escapeshellarg($logFile) . " 2>&1"
             . " && echo '__DONE_OK__' >> " . escapeshellarg($logFile)
             . " || echo '__DONE_ERROR__' >> " . escapeshellarg($logFile);

        exec("nohup bash -c " . escapeshellarg($cmd) . " &");

        file_put_contents($this->logDir . "/{$jobId}.meta", json_encode([
            'mode'       => 'rescrape',
            'limit'      => $limit,
            'started_at' => now()->toISOString(),
        ]));

        return response()->json(['job_id' => $jobId, 'limit' => $limit]);
    }

    public function rescrapeProgress()
    {
        $total    = Place::count();
        $scraped  = Place::whereNotNull('popular_times')->count();
        $hasPt    = \DB::selectOne('SELECT COUNT(*) as c FROM places WHERE popular_times IS NOT NULL AND popular_times != JSON_ARRAY()')->c;
        $noPt     = $scraped - $hasPt;
        $running  = (bool) shell_exec('pgrep -f gmaps-rescraper 2>/dev/null');
        return response()->json([
            'total'   => $total,
            'scraped' => $scraped,
            'has_pt'  => $hasPt,
            'no_pt'   => $noPt,
            'pending' => $total - $scraped,
            'pct'     => $total > 0 ? round($scraped / $total * 100, 1) : 0,
            'running' => $running,
        ]);
    }

    public function saveCookies(Request $request)
    {
        $request->validate(['cookies' => 'required|string']);

        $json = trim($request->input('cookies'));

        // Validate it's a JSON array
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || empty($decoded)) {
            return response()->json(['error' => 'Format tidak valid. Harus berupa JSON array dari cookies.'], 422);
        }

        // Verify it looks like Google cookies
        $hasGoogle = collect($decoded)->contains(fn($c) =>
            isset($c['domain']) && str_contains($c['domain'], 'google')
        );
        if (!$hasGoogle) {
            return response()->json(['error' => 'Tidak ditemukan cookie Google. Pastikan export dari google.com atau maps.google.com.'], 422);
        }

        $cookieFile = base_path('scraper/google-cookies.json');
        file_put_contents($cookieFile, $json);

        $count = count($decoded);
        return response()->json(['success' => true, 'message' => "✅ {$count} cookies tersimpan. Siap untuk rescrape."]);
    }

    public function cookieStatus()
    {
        $cookieFile = base_path('scraper/google-cookies.json');
        if (!file_exists($cookieFile)) {
            return response()->json(['exists' => false]);
        }
        $count = count(json_decode(file_get_contents($cookieFile), true) ?? []);
        $mtime = filemtime($cookieFile);
        return response()->json([
            'exists'    => true,
            'count'     => $count,
            'saved_at'  => date('d M Y H:i', $mtime),
        ]);
    }

    public function checkCookies()
    {
        $cookieFile = base_path('scraper/google-cookies.json');
        if (!file_exists($cookieFile)) {
            return response()->json(['valid' => false, 'message' => 'File cookies tidak ditemukan.']);
        }

        $node    = trim(shell_exec('which node') ?: '/usr/bin/node');
        $script  = base_path('scraper/check-cookies.js');

        $output = shell_exec("{$node} {$script} 2>&1");
        $result = json_decode(trim($output ?? ''), true);

        if (!$result) {
            return response()->json(['valid' => false, 'message' => 'Gagal menjalankan pengecekan: ' . substr($output ?? '', 0, 200)]);
        }

        return response()->json($result);
    }

    private function dbStats(): array
    {
        return [
            'total'    => Place::count(),
            'phone'    => Place::whereNotNull('phone')->count(),
            'today'    => Place::whereDate('created_at', today())->count(),
            'category' => Place::distinct('category')->count('category'),
        ];
    }
}
