<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckPlacesWhatsApp extends Command
{
    protected $signature = 'places:check-wa
                            {--limit=50 : Jumlah places yang dicek per run}
                            {--force : Cek ulang meski sudah pernah dicek}
                            {--device=device-1780035414781 : Device ID wa-api}';

    protected $description = 'Cek nomor telepon places apakah punya WhatsApp';

    private string $waApiUrl = 'http://localhost:8000';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $force = $this->option('force');
        $deviceId = $this->option('device');

        $query = Place::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('is_valid', true);

        if (!$force) {
            $query->whereNull('wa_checked_at');
        }

        $places = $query->limit($limit)->get();

        if ($places->isEmpty()) {
            $this->info('Tidak ada places yang perlu dicek.');
            return 0;
        }

        $this->info("Memeriksa {$places->count()} nomor telepon...\n");

        $bar = $this->output->createProgressBar($places->count());
        $bar->start();

        $hasWa = 0;
        $noWa = 0;
        $failed = 0;

        foreach ($places as $place) {
            try {
                $response = Http::timeout(10)->post("{$this->waApiUrl}/check-wa", [
                    'number'   => $place->phone,
                    'deviceId' => $deviceId,
                ]);

                if ($response->successful()) {
                    $exists = $response->json('status') === true;
                    $place->update([
                        'has_whatsapp'  => $exists,
                        'wa_checked_at' => now(),
                    ]);
                    $exists ? $hasWa++ : $noWa++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->warn("Gagal cek {$place->phone}: {$e->getMessage()}");
            }

            $bar->advance();
            // Delay agar tidak flood wa-api
            usleep(random_int(500000, 1500000));
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Status', 'Jumlah'],
            [
                ['✅ Punya WhatsApp', $hasWa],
                ['❌ Tidak ada WA',   $noWa],
                ['⚠️  Gagal dicek',    $failed],
            ]
        );

        return 0;
    }
}
