<?php

namespace App\Console\Commands;

use App\Models\ApiToken;
use App\Models\Place;
use App\Models\ScrapeSchedule;
use App\Services\TelegramService;
use Illuminate\Console\Command;

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

            // Jalankan sinkron — command menunggu hingga selesai
            exec($cmd, $out, $exitCode);

            $logContent = file_exists($logFile) ? file_get_contents($logFile) : '';
            $processed  = 0;
            foreach (explode("\n", $logContent) as $line) {
                if (preg_match('/^\[(\d+)\/\d+\]/', $line, $m)) {
                    $processed = (int) $m[1];
                }
            }

            $success = str_contains($logContent, '__DONE_OK__');
            $result  = ['status' => $success ? 'success' : 'error', 'processed' => $processed, 'job_id' => $jobId];

            $schedule->update(['last_result' => $result, 'is_running' => false]);

            if ($success) {
                $totalDb = Place::count();
                $telegram->notifyScrapeDone($schedule->query, $schedule->area ?? '', $processed, $totalDb);
                $this->info("Selesai: {$processed} tempat diproses.");
            } else {
                $telegram->notifyScraperError($schedule->query, 'Scraper exit non-zero');
                $this->error("Gagal: {$schedule->name}");
            }
        }
    }
}
