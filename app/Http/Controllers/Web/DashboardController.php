<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\ScrapeLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_places' => Place::count(),
            'total_scrape_logs' => ScrapeLog::count(),
            'places_today' => Place::whereDate('created_at', today())->count(),
            'logs_today' => ScrapeLog::whereDate('created_at', today())->count(),
            'avg_rating' => Place::whereNotNull('rating')->avg('rating'),
            'top_categories' => Place::selectRaw('category, COUNT(*) as count')
                ->whereNotNull('category')
                ->groupBy('category')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
            'recent_places' => Place::latest()->limit(5)->get(),
            'recent_logs' => ScrapeLog::with('place')->latest()->limit(10)->get(),
        ];

        return view('dashboard', compact('stats'));
    }
}
