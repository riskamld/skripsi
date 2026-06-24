<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Services\KMeansService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KMeansController extends Controller
{
    public function index(KMeansService $service): View
    {
        $places = Place::query()
            ->whereNotNull('cluster')
            ->orderByRaw("FIELD(cluster_label, 'Tinggi', 'Sedang', 'Rendah')")
            ->orderByDesc('cluster_score')
            ->get();

        $summary = $places->groupBy('cluster_label')->map(function ($group) {
            return [
                'count' => $group->count(),
                'avg_rating' => round($group->avg('rating'), 2),
                'avg_review_count' => round($group->avg('review_count'), 1),
            ];
        });

        $order = ['Tinggi' => 0, 'Sedang' => 1, 'Rendah' => 2];
        $summary = $summary->sortBy(fn ($v, $label) => $order[$label] ?? 99);

        $lastComputedAt = $places->max('cluster_computed_at');
        $eligibleCount = Place::whereNotNull('rating')->whereNotNull('review_count')
            ->whereNotNull('lat')->whereNotNull('lng')->count();

        return view('kmeans.index', [
            'places' => $places,
            'summary' => $summary,
            'lastComputedAt' => $lastComputedAt,
            'eligibleCount' => $eligibleCount,
            'evaluation' => $service->getEvaluation(),
        ]);
    }

    public function run(KMeansService $service): RedirectResponse
    {
        $result = $service->run();

        if (!$result['ok']) {
            return redirect()->route('kmeans.index')->with('error', $result['message']);
        }

        return redirect()->route('kmeans.index')
            ->with('success', "Analisis K-Means selesai untuk {$result['total']} tempat.");
    }
}
