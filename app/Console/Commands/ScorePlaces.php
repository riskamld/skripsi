<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;

class ScorePlaces extends Command
{
    protected $signature = 'places:score
                            {--mark-targets : Tandai is_target setelah scoring}
                            {--min-score=5 : Minimum busyness_score untuk is_target}
                            {--require-wa : is_target hanya untuk yang punya WA}';

    protected $description = 'Hitung busyness_score dan tandai is_target pada places';

    public function handle(): int
    {
        $this->info('Menghitung busyness_score...');

        $updated = 0;

        Place::whereNotNull('rating')
            ->whereNotNull('review_count')
            ->chunk(500, function ($places) use (&$updated) {
                foreach ($places as $place) {
                    // score = rating × log10(review_count + 1) × 10
                    // Contoh: rating 4.5, reviews 200 → 4.5 × 2.30 × 10 = 103.6
                    $score = round(
                        $place->rating * log10($place->review_count + 1) * 10,
                        2
                    );
                    $place->busyness_score = $score;
                    $place->save();
                    $updated++;
                }
            });

        $this->info("✅ {$updated} places diperbarui skor-nya.");

        if ($this->option('mark-targets')) {
            $this->markTargets();
        }

        $this->showStats();
        return 0;
    }

    private function markTargets(): void
    {
        $minScore  = (float) $this->option('min-score');
        $requireWa = $this->option('require-wa');

        // Reset semua is_target
        Place::query()->update(['is_target' => false]);

        $query = Place::where('is_valid', true)
            ->where('permanently_closed', false)
            ->where('busyness_score', '>=', $minScore)
            ->whereNotNull('phone');

        if ($requireWa) {
            $query->where('has_whatsapp', true);
        }

        $count = $query->update(['is_target' => true]);
        $this->info("🎯 {$count} places ditandai sebagai target.");
    }

    private function showStats(): void
    {
        $stats = [
            ['Total places',             Place::count()],
            ['Punya rating & review',    Place::whereNotNull('rating')->whereNotNull('review_count')->count()],
            ['Score > 10 (ramai)',        Place::where('busyness_score', '>', 10)->count()],
            ['Score > 30 (sangat ramai)', Place::where('busyness_score', '>', 30)->count()],
            ['Punya WhatsApp',           Place::where('has_whatsapp', true)->count()],
            ['Ditandai sebagai target',  Place::where('is_target', true)->count()],
        ];

        $this->table(['Metrik', 'Jumlah'], $stats);
    }
}
