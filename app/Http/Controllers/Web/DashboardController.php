<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OutreachLog;
use App\Models\Place;
use App\Models\ScrapeSchedule;
use App\Models\TelegramSetting;
use App\Models\WaIncomingMessage;
use Illuminate\Support\Facades\DB;

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
            'followup_due'   => Place::where('outreach_status', 'sent')
                                    ->where('outreach_sent_at', '<', now()->subDays(3))
                                    ->count(),
            'total_order_value' => DB::table('place_orders')->sum('total_rp'),
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

        // Tren outreach 14 hari terakhir
        $trendDays  = 14;
        $trendDates = collect(range($trendDays - 1, 0))->map(fn($i) => today()->subDays($i)->toDateString());
        $trendRaw   = OutreachLog::where('action', 'sent')
            ->where('created_at', '>=', today()->subDays($trendDays - 1))
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')->pluck('c', 'd');
        $trendData = $trendDates->map(fn($d) => $trendRaw->get($d, 0));

        // Pesan masuk terbaru dari prospek (5 terakhir)
        $recentIncoming = WaIncomingMessage::with('place:id,name,outreach_status')
            ->where('is_prospect', true)
            ->orderByDesc('received_at')
            ->limit(5)
            ->get();

        // Jadwal scraping berikutnya
        $nextSchedule = ScrapeSchedule::where('enabled', true)->get()
            ->map(function ($s) {
                $now = now();
                $next = match ($s->frequency) {
                    'every_n_hours' => $s->last_run_at
                        ? $s->last_run_at->addHours($s->interval_hours ?: 6)
                        : $now,
                    'daily' => (function () use ($s, $now) {
                        $slot = $now->copy()->setHour($s->run_hour)->setMinute(0)->setSecond(0);
                        return $now->lt($slot) ? $slot : $slot->addDay();
                    })(),
                    'weekly' => (function () use ($s, $now) {
                        $days = ['', 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                        $dayName = $days[$s->day_of_week] ?? 'Monday';
                        $slot = $now->copy()->next($dayName)->setHour($s->run_hour)->setMinute(0)->setSecond(0);
                        // Jika hari ini sama dan belum lewat jam, gunakan hari ini
                        $todaySlot = $now->copy()->setHour($s->run_hour)->setMinute(0)->setSecond(0);
                        if ($now->dayOfWeekIso === $s->day_of_week && $now->lt($todaySlot)) {
                            return $todaySlot;
                        }
                        return $slot;
                    })(),
                    default => null,
                };
                return $next ? ['name' => $s->name, 'next' => $next, 'query' => $s->query] : null;
            })
            ->filter()
            ->sortBy('next')
            ->first();

        // Status sistem
        $telegramEnabled = TelegramSetting::first()?->enabled ?? false;
        $webhookUrl      = url('/whatsapp/webhook');
        $waApiUrl        = rtrim(env('WA_API_URL', 'http://localhost:8000'), '/');
        try {
            $wbResp = \Illuminate\Support\Facades\Http::timeout(3)->get("{$waApiUrl}/api/config/webhooks");
            $webhookRegistered = in_array($webhookUrl, $wbResp->json('data') ?? []);
        } catch (\Exception) {
            $webhookRegistered = false;
        }

        // Pesan WA masuk hari ini
        $incomingToday = WaIncomingMessage::whereDate('received_at', today())->count();

        return view('dashboard', compact(
            'stats', 'trendDates', 'trendData',
            'recentIncoming', 'nextSchedule',
            'telegramEnabled', 'webhookRegistered', 'incomingToday'
        ));
    }
}
