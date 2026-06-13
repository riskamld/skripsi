<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total'          => Place::count(),
            'has_phone'      => Place::whereNotNull('phone')->count(),
            'has_wa'         => Place::where('has_whatsapp', true)->count(),
            'wa_unchecked'   => Place::whereNotNull('phone')->whereNull('wa_checked_at')->count(),
            'is_target'      => Place::where('is_target', true)->count(),
            'today'          => Place::whereDate('created_at', today())->count(),
            'high_score'     => Place::where('busyness_score', '>', 50)->count(),
            'top_categories' => Place::selectRaw('category, COUNT(*) as count')
                ->whereNotNull('category')
                ->groupBy('category')
                ->orderByDesc('count')
                ->limit(8)
                ->get(),
            'recent_places'  => Place::whereNotNull('phone')
                ->orderByDesc('busyness_score')
                ->limit(8)
                ->get(['name', 'phone', 'category', 'rating', 'review_count', 'busyness_score', 'has_whatsapp', 'is_target', 'address']),
        ];

        return view('dashboard', compact('stats'));
    }
}
