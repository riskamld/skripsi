<?php

namespace App\Console\Commands;

use App\Models\ApiToken;
use App\Models\Place;
use App\Models\ScrapeSchedule;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunScheduledScraper extends Command
{
    protected $signature = 'scraper:run-scheduled';
    protected $description = 'Jalankan jadwal scraping yang sudah waktunya';

    public function handle(TelegramService $telegram): void
    {
        // Cegah tabrakan dengan scraping manual dari FE atau rescraper
        if (!empty(trim(shell_exec('pgrep -f "[g]maps-scraper.js" 2>/dev/null') ?? '')) || !empty(trim(shell_exec('pgrep -f "[g]maps-rescraper.js" 2>/dev/null') ?? ''))) {
            $this->warn('Scraper sedang berjalan, jadwal dilewati.');
            return;
        }

        // Reset is_running stale dari crash sebelumnya
        ScrapeSchedule::where('is_running', true)->update(['is_running' => false]);

        $schedules = ScrapeSchedule::where('enabled', true)->get();

        foreach ($schedules as $schedule) {
            if (!$schedule->shouldRunNow()) continue;

            // Cek ulang sebelum tiap jadwal (mungkin ada jadwal sebelumnya yang baru selesai)
            if (!empty(trim(shell_exec('pgrep -f "[g]maps-scraper.js" 2>/dev/null') ?? ''))) {
                $this->warn("Scraper baru selesai, skip jadwal: {$schedule->name}");
                continue;
            }

            $this->info("Menjalankan jadwal: {$schedule->name}");

            $token = ApiToken::where('is_active', true)->value('token');
            if (!$token) {
                $this->warn("Tidak ada API token aktif, skip.");
                continue;
            }

            $scraperPath = base_path('scraper/gmaps-scraper.js');
            $logDir      = storage_path('scraper-logs');
            if (!is_dir($logDir)) mkdir($logDir, 0755, true);

            $jobId   = uniqid('sched_', true);
            $logFile = "{$logDir}/{$jobId}.log";
            $node    = trim(shell_exec('which node') ?: '/usr/bin/node');
            $query   = escapeshellarg($schedule->query);
            $area    = escapeshellarg($schedule->area ?? '');
            $limit   = (int) $schedule->limit;

            $coords = ($schedule->lat && $schedule->lng)
                ? " LAT={$schedule->lat} LNG={$schedule->lng} ZOOM={$schedule->zoom}"
                : '';

            $cmd = "MAFAZA_API_TOKEN={$token} HEADLESS=true{$coords}"
                 . " {$node} " . escapeshellarg($scraperPath)
                 . " {$query} {$area} {$limit}"
                 . " >> " . escapeshellarg($logFile) . " 2>&1"
                 . " && echo '__DONE_OK__' >> " . escapeshellarg($logFile)
                 . " || echo '__DONE_ERROR__' >> " . escapeshellarg($logFile);

            $schedule->update(['last_run_at' => now(), 'is_running' => true, 'current_log_file' => $logFile]);
            $telegram->notifyScheduledStart($schedule->name, $schedule->query);

            // Jalankan sinkron — command menunggu hingga selesai (~20-30 menit)
            exec($cmd, $out, $exitCode);

            // Reconnect MySQL — koneksi bisa timeout selama exec() panjang
            try { DB::statement('SELECT 1'); } catch (\Exception $e) { DB::reconnect(); }

            $logContent = file_exists($logFile) ? file_get_contents($logFile) : '';
            $processed  = 0;
            foreach (explode("\n", $logContent) as $line) {
                if (preg_match('/^\[(\d+)\/\d+\]/', $line, $m)) {
                    $processed = (int) $m[1];
                }
            }

            $selectorBroken   = str_contains($logContent, 'SELECTOR_BROKEN');
            $success          = !$selectorBroken && str_contains($logContent, '__DONE_OK__');
            $status           = $selectorBroken ? 'selector_broken' : ($success ? 'success' : 'error');
            $prevEmpty        = (int) ($schedule->last_result['consecutive_empty'] ?? 0);
            $consecutiveEmpty = ($success && $processed === 0) ? $prevEmpty + 1 : 0;
            $result           = ['status' => $status, 'processed' => $processed, 'consecutive_empty' => $consecutiveEmpty, 'job_id' => $jobId];

            $schedule->update(['last_result' => $result, 'is_running' => false]);

            if ($selectorBroken) {
                $telegram->notifySelectorBroken($schedule->name, $schedule->query);
                $this->error("SELECTOR RUSAK: {$schedule->name} — scraping dihentikan.");
                // Hentikan semua jadwal berikutnya — semua akan gagal dengan masalah yang sama
                break;
            } elseif ($success) {
                if ($processed === 0) {
                    if ($consecutiveEmpty >= 3) {
                        $schedule->update(['enabled' => false]);
                        $telegram->notifyScheduleAutoDisabled($schedule->name, $schedule->query, $schedule->area ?? '', $consecutiveEmpty);
                        $this->warn("Auto-dinonaktifkan ({$consecutiveEmpty}× kosong berturut): {$schedule->name}");
                    } else {
                        $telegram->notifyEmptyResult($schedule->name, $schedule->query, $schedule->area ?? '', $consecutiveEmpty);
                        $this->warn("Selesai tapi 0 tempat ({$consecutiveEmpty}/3): {$schedule->name}");
                    }
                } else {
                    $totalDb = Place::count();
                    $telegram->notifyScrapeDone($schedule->query, $schedule->area ?? '', $processed, $totalDb);
                    $this->info("Selesai: {$processed} tempat diproses.");
                }
            } else {
                $telegram->notifyScraperError($schedule->query, 'Scraper exit non-zero');
                $this->error("Gagal: {$schedule->name}");
            }
        }
    }
}
