<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;

class DashboardController extends Controller
{
    // Kategori relevan untuk bisnis buah
    private const RELEVANT = [
        'Toko Buah dan Sayur', 'Grosir Buah dan Sayur', 'Toko Buah Kering',
        'Restoran', 'Warung Makan', 'Restoran Ayam', 'Restoran Cepat Saji',
        'Toko Bahan Makanan', 'Minimarket', 'Kedai Jus', 'Toko Es Krim',
        'Restoran Bakso', 'Restoran Mie', 'Restoran Nasi Goreng',
        'Restoran Sate', 'Restoran Seafood', 'Kedai Hidangan Penutup', 'Pasar',
    ];

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
            'outreach_sent'  => Place::where('outreach_status', 'sent')->count(),
            'replied'        => Place::whereIn('outreach_status', ['replied','responded'])->count(),
            'interested'     => Place::where('outreach_status', 'interested')->count(),
            'not_interested' => Place::where('outreach_status', 'not_interested')->count(),
            'ordered'        => Place::where('outreach_status', 'ordered')->count(),
            'relevant'       => Place::whereIn('category', self::RELEVANT)->count(),
            'top_categories' => Place::selectRaw('category, COUNT(*) as count')
                ->whereNotNull('category')
                ->groupBy('category')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'recent_places'  => Place::whereNotNull('phone')
                ->orderByDesc('busyness_score')
                ->limit(8)
                ->get(['id', 'name', 'phone', 'category', 'rating', 'review_count', 'busyness_score', 'has_whatsapp', 'is_target', 'outreach_status']),
        ];

        return view('dashboard', compact('stats'));
    }
}
