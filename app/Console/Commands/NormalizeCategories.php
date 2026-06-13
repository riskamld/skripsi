<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;

class NormalizeCategories extends Command
{
    protected $signature = 'places:normalize-categories {--dry-run : Tampilkan perubahan tanpa menyimpan}';
    protected $description = 'Normalisasi kategori yang duplikat/mirip';

    private array $map = [
        // Buah & Sayur
        'toko buah dan sayuran'         => 'Toko Buah dan Sayur',
        'toko buah & sayur'             => 'Toko Buah dan Sayur',
        'toko buah dan sayur'           => 'Toko Buah dan Sayur',
        'grosir buah'                   => 'Grosir Buah dan Sayur',
        'grosir sayur dan buah'         => 'Grosir Buah dan Sayur',
        'toko grosir buah dan sayur'    => 'Grosir Buah dan Sayur',
        'toko buah kering'              => 'Toko Buah Kering',
        // Makanan
        'rumah makan'                   => 'Restoran',
        'warung nasi'                   => 'Warung Makan',
        'warung'                        => 'Warung Makan',
        'kedai sarapan & makan siang'   => 'Warung Makan',
        'kedai sarapan'                 => 'Warung Makan',
        'restoran jawa'                 => 'Restoran',
        'restoran indonesia'            => 'Restoran',
        'restoran keluarga'             => 'Restoran',
        'restoran masakan ayam'         => 'Restoran Ayam',
        'restoran cepat saji'           => 'Restoran Cepat Saji',
        // Bunga
        'toko bunga kering'             => 'Toko Bunga',
        // Bahan makanan
        'toko makanan'                  => 'Toko Bahan Makanan',
        'toko bahan makanan'            => 'Toko Bahan Makanan',
        'minimarket'                    => 'Minimarket',
        'toko swalayan'                 => 'Minimarket',
    ];

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        $total = 0;

        foreach ($this->map as $raw => $canonical) {
            $q = Place::whereRaw('LOWER(category) = ?', [$raw]);
            $count = $q->count();
            if ($count === 0) continue;

            $this->line("  {$count}× \"{$raw}\" → \"{$canonical}\"");
            if (!$dry) {
                $q->update(['category' => $canonical]);
            }
            $total += $count;
        }

        $msg = $dry ? "(dry-run, tidak disimpan)" : "✅ Selesai";
        $this->info("{$msg} — {$total} tempat dinormalisasi.");
        return 0;
    }
}
