<?php

namespace App\Services;

use App\Models\Place;
use Illuminate\Support\Facades\DB;

/**
 * Analisis K-Means Clustering toko/agen buah berdasarkan proposal skripsi:
 * X1 = rating, X2 = jumlah review, X3 = latitude, X4 = longitude.
 * Preprocessing: buang data tidak lengkap/duplikat, Min-Max Normalization,
 * lalu Euclidean Distance untuk assignment ke centroid terdekat.
 */
class KMeansService
{
    public const K = 3;
    public const MAX_ITERATIONS = 100;

    /**
     * Jalankan clustering atas seluruh data places yang valid, simpan hasil
     * ke kolom cluster/cluster_label/cluster_score, dan kembalikan ringkasan.
     */
    public function run(): array
    {
        $places = $this->fetchEligiblePlaces();

        if ($places->count() < self::K) {
            return [
                'ok' => false,
                'message' => "Data tidak cukup untuk clustering (minimal " . self::K . " tempat dengan rating, review, dan koordinat lengkap).",
            ];
        }

        $features = ['rating', 'review_count', 'lat', 'lng'];

        // Hapus duplikat (place_id sudah unique di DB, tapi proteksi tambahan by name+address)
        $deduped = $places->unique(function ($p) {
            return mb_strtolower(trim($p->name . '|' . $p->address));
        })->values();

        if ($deduped->count() < self::K) {
            return [
                'ok' => false,
                'message' => "Data tidak cukup setelah penghapusan duplikat (minimal " . self::K . " tempat unik).",
            ];
        }

        $rows = $deduped->map(fn ($p) => [
            'rating' => (float) $p->rating,
            'review_count' => (float) $p->review_count,
            'lat' => (float) $p->lat,
            'lng' => (float) $p->lng,
        ])->all();

        $bounds = $this->computeMinMax($rows, $features);
        $vectors = array_map(fn ($row) => $this->normalizeRow($row, $features, $bounds), $rows);

        [$labels, $centroids] = $this->fit($vectors);

        $clusterRanking = $this->rankClustersByPotential($labels, $rows);

        $this->persistResults($deduped, $labels, $vectors, $centroids, $clusterRanking);

        return [
            'ok' => true,
            'total' => $deduped->count(),
            'clusters' => $this->summarize($deduped, $labels, $clusterRanking),
        ];
    }

    private function fetchEligiblePlaces()
    {
        return Place::query()
            ->whereNotNull('rating')
            ->whereNotNull('review_count')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get();
    }

    private function computeMinMax(array $rows, array $features): array
    {
        $bounds = [];
        foreach ($features as $f) {
            $values = array_column($rows, $f);
            $bounds[$f] = ['min' => min($values), 'max' => max($values)];
        }
        return $bounds;
    }

    private function normalizeRow(array $row, array $features, array $bounds): array
    {
        $vector = [];
        foreach ($features as $f) {
            $min = $bounds[$f]['min'];
            $max = $bounds[$f]['max'];
            $range = ($max - $min) ?: 1.0; // hindari pembagian nol jika semua nilai sama
            $vector[] = ($row[$f] - $min) / $range;
        }
        return $vector;
    }

    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0;
        foreach ($a as $i => $v) {
            $sum += ($v - $b[$i]) ** 2;
        }
        return sqrt($sum);
    }

    /**
     * Lloyd's algorithm: inisialisasi centroid acak, assignment step,
     * update step, ulangi sampai stabil atau mencapai batas iterasi.
     */
    private function fit(array $vectors): array
    {
        $n = count($vectors);
        $k = self::K;

        $initialIndexes = array_rand($vectors, $k);
        $centroids = array_map(fn ($i) => $vectors[$i], (array) $initialIndexes);

        $labels = array_fill(0, $n, 0);

        for ($iter = 0; $iter < self::MAX_ITERATIONS; $iter++) {
            $changed = false;

            foreach ($vectors as $i => $v) {
                $bestCluster = 0;
                $bestDistance = INF;
                foreach ($centroids as $c => $centroid) {
                    $distance = $this->euclideanDistance($v, $centroid);
                    if ($distance < $bestDistance) {
                        $bestDistance = $distance;
                        $bestCluster = $c;
                    }
                }
                if ($labels[$i] !== $bestCluster) {
                    $labels[$i] = $bestCluster;
                    $changed = true;
                }
            }

            if (!$changed && $iter > 0) {
                break;
            }

            $dim = count($vectors[0]);
            $sums = array_fill(0, $k, array_fill(0, $dim, 0.0));
            $counts = array_fill(0, $k, 0);

            foreach ($vectors as $i => $v) {
                $cluster = $labels[$i];
                $counts[$cluster]++;
                foreach ($v as $d => $val) {
                    $sums[$cluster][$d] += $val;
                }
            }

            foreach ($sums as $cluster => $sum) {
                if ($counts[$cluster] === 0) {
                    continue; // pertahankan centroid lama jika cluster kosong
                }
                $centroids[$cluster] = array_map(fn ($s) => $s / $counts[$cluster], $sum);
            }
        }

        return [$labels, $centroids];
    }

    /**
     * Beri label Tinggi/Sedang/Rendah berdasarkan rata-rata rating + jumlah
     * review tiap cluster (indikator potensi kemitraan bisnis).
     */
    private function rankClustersByPotential(array $labels, array $rows): array
    {
        $scores = [];
        $counts = [];

        foreach ($labels as $i => $cluster) {
            $scores[$cluster] = ($scores[$cluster] ?? 0) + $rows[$i]['rating'] + log($rows[$i]['review_count'] + 1);
            $counts[$cluster] = ($counts[$cluster] ?? 0) + 1;
        }

        $avgScores = [];
        foreach ($scores as $cluster => $total) {
            $avgScores[$cluster] = $counts[$cluster] > 0 ? $total / $counts[$cluster] : 0;
        }

        arsort($avgScores);
        $order = array_keys($avgScores);
        $labelsByRank = ['Tinggi', 'Sedang', 'Rendah'];

        $ranking = [];
        foreach ($order as $rank => $cluster) {
            $ranking[$cluster] = [
                'label' => $labelsByRank[$rank] ?? ('Cluster ' . $cluster),
                'avg_score' => $avgScores[$cluster],
            ];
        }

        return $ranking;
    }

    private function persistResults($places, array $labels, array $vectors, array $centroids, array $clusterRanking): void
    {
        DB::transaction(function () use ($places, $labels, $vectors, $clusterRanking) {
            $now = now();
            foreach ($places as $i => $place) {
                $cluster = $labels[$i];
                $place->update([
                    'cluster' => $cluster,
                    'cluster_label' => $clusterRanking[$cluster]['label'] ?? null,
                    'cluster_score' => round($vectors[$i][0] + $vectors[$i][1], 4),
                    'cluster_computed_at' => $now,
                ]);
            }
        });
    }

    private function summarize($places, array $labels, array $clusterRanking): array
    {
        $summary = [];
        foreach ($places as $i => $place) {
            $cluster = $labels[$i];
            $label = $clusterRanking[$cluster]['label'] ?? 'Tidak diketahui';
            $summary[$label]['count'] = ($summary[$label]['count'] ?? 0) + 1;
            $summary[$label]['avg_rating'] = ($summary[$label]['avg_rating'] ?? 0) + (float) $place->rating;
            $summary[$label]['avg_review_count'] = ($summary[$label]['avg_review_count'] ?? 0) + (float) $place->review_count;
        }

        foreach ($summary as $label => &$data) {
            $data['avg_rating'] = round($data['avg_rating'] / $data['count'], 2);
            $data['avg_review_count'] = round($data['avg_review_count'] / $data['count'], 1);
        }

        // Urutkan Tinggi -> Sedang -> Rendah
        $order = ['Tinggi' => 0, 'Sedang' => 1, 'Rendah' => 2];
        uksort($summary, fn ($a, $b) => ($order[$a] ?? 99) <=> ($order[$b] ?? 99));

        return $summary;
    }
}
