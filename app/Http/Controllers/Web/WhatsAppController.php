<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OutreachLog;
use App\Models\Place;
use App\Models\PlaceOrder;
use App\Models\WaIncomingMessage;
use App\Models\WaTemplate;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    private string $waApiUrl;

    public function __construct()
    {
        $this->waApiUrl = rtrim(env('WA_API_URL', 'http://localhost:8000'), '/');
    }

    public function index()
    {
        $devices   = $this->getDevices();
        $stats     = $this->getStats();
        $templates = WaTemplate::orderBy('sort_order')->orderBy('id')->get();

        return view('whatsapp.index', compact('devices', 'stats', 'templates'));
    }

    public function devices()
    {
        return response()->json(['devices' => $this->getDevices()]);
    }

    public function checkWA(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'limit'     => 'integer|min:1|max:100',
        ]);

        $deviceId = $request->device_id;
        $limit    = (int) $request->get('limit', 30);

        $places = Place::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereNull('has_whatsapp')
            ->limit($limit)
            ->get(['id', 'name', 'phone']);

        $results = ['checked' => 0, 'has_wa' => 0, 'no_wa' => 0, 'error' => 0];

        if ($places->isEmpty()) {
            return response()->json(['status' => 'ok', 'results' => $results,
                'remaining' => 0]);
        }

        // Pakai batch endpoint — satu request untuk semua nomor
        try {
            $numbers = $places->pluck('phone')->toArray();
            $resp = Http::timeout(60)
                ->withHeaders(['X-API-Key' => env('WA_API_KEY', '')])
                ->post("{$this->waApiUrl}/check-wa-batch?deviceId={$deviceId}", [
                    'numbers' => $numbers,
                ]);

            if ($resp->successful() && $resp->json('status')) {
                $waResults = collect($resp->json('results'))->keyBy('number');
                foreach ($places as $place) {
                    $waResult = $waResults->get($place->phone);
                    $hasWa    = $waResult ? (bool) $waResult['exists'] : false;
                    $place->update(['has_whatsapp' => $hasWa, 'wa_checked_at' => now()]);
                    $hasWa ? $results['has_wa']++ : $results['no_wa']++;
                    $results['checked']++;
                }
            } else {
                $results['error'] = $places->count();
            }
        } catch (\Exception $e) {
            Log::warning("checkWA batch error: " . $e->getMessage());
            $results['error'] = $places->count();
        }

        $remaining = Place::whereNotNull('phone')->where('phone', '!=', '')->whereNull('has_whatsapp')->count();

        app(TelegramService::class)->notifyWaChecked($results['has_wa'], $results['no_wa'], $results['checked']);

        return response()->json([
            'status'    => 'ok',
            'results'   => $results,
            'remaining' => $remaining,
        ]);
    }

    public function sendOutreach(Request $request)
    {
        $request->validate([
            'device_id'       => 'required|string',
            'template_id'     => 'required',
            'limit'           => 'integer|min:1|max:50',
            'category_filter' => 'nullable|string',
            'place_ids'       => 'nullable|array',
            'place_ids.*'     => 'integer',
        ]);

        $deviceId      = $request->device_id;
        $templateId    = $request->template_id;
        $limit         = (int) $request->get('limit', 5);
        $categoryFilter= $request->get('category_filter', 'relevant');
        $placeIds      = $request->input('place_ids', []);
        $isRandom      = ($templateId == 0);

        // Cek limit harian
        $sentToday = OutreachLog::where('action', 'sent')
            ->whereDate('created_at', today())->count();
        $dailyLimit = (int) env('WA_DAILY_LIMIT', 50);
        if ($sentToday >= $dailyLimit) {
            app(TelegramService::class)->notifyDailyLimit($dailyLimit);
            return response()->json(['error' => "Limit harian {$dailyLimit} pesan sudah tercapai ({$sentToday} terkirim hari ini)."], 422);
        }
        $canSend = min(!empty($placeIds) ? count($placeIds) : $limit, $dailyLimit - $sentToday);

        $activeTemplates = WaTemplate::active()->get();
        if ($activeTemplates->isEmpty()) {
            return response()->json(['error' => 'Tidak ada template aktif'], 422);
        }
        if (!$isRandom) {
            $single = $activeTemplates->firstWhere('id', $templateId);
            if (!$single) return response()->json(['error' => 'Template tidak valid'], 422);
        }

        if (!empty($placeIds)) {
            // Kirim ke target spesifik dari preview modal
            $places = Place::whereIn('id', array_slice($placeIds, 0, $canSend))
                ->where('has_whatsapp', true)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get(['id', 'name', 'phone', 'category', 'address']);
        } else {
            // Ambil phone yang sudah pernah dikirim untuk dedup
            $sentPhones = Place::where('outreach_status', 'sent')
                ->whereNotNull('phone')->pluck('phone')->flip()->all();

            $q = Place::where('has_whatsapp', true)
                ->whereNull('outreach_status')
                ->whereNotNull('phone')
                ->where('phone', '!=', '');

            if ($categoryFilter === 'relevant') {
                $q->whereIn('category', self::RELEVANT_CATEGORIES);
            } elseif ($categoryFilter) {
                $q->where('category', $categoryFilter);
            }
            $this->applyChainExclusion($q);

            $places = $q->orderByRaw($this->priorityScoreExpr() . ' DESC')
                ->limit($canSend * 3)
                ->get(['id', 'name', 'phone', 'category', 'address'])
                ->filter(fn($p) => !isset($sentPhones[$p->phone]))
                ->take($canSend);
        }

        $results = ['sent' => 0, 'failed' => 0];

        foreach ($places as $place) {
            $template = $isRandom ? $activeTemplates->random() : $single;
            $message  = $this->renderTemplate($template->body, $place);

            try {
                $resp = Http::timeout(15)->post("{$this->waApiUrl}/send-message?deviceId={$deviceId}", [
                    'number'  => $place->phone,
                    'message' => $message,
                ]);

                if ($resp->successful() && $resp->json('status')) {
                    $place->update([
                        'outreach_status'    => 'sent',
                        'outreach_sent_at'   => now(),
                        'outreach_device_id' => $deviceId,
                    ]);
                    OutreachLog::create([
                        'place_id'      => $place->id,
                        'action'        => 'sent',
                        'status'        => 'sent',
                        'template_id'   => $template->id,
                        'template_name' => $template->name,
                    ]);
                    $results['sent']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                Log::warning("sendOutreach error for place {$place->id}: " . $e->getMessage());
                $results['failed']++;
            }

            // Delay random 3-7 detik antar pesan
            usleep(rand(3000000, 7000000));
        }

        $remaining = Place::where('has_whatsapp', true)->whereNull('outreach_status')
            ->when($categoryFilter === 'relevant', fn($q) => $q->whereIn('category', self::RELEVANT_CATEGORIES))
            ->when($categoryFilter && $categoryFilter !== 'relevant', fn($q) => $q->where('category', $categoryFilter))
            ->count();

        if ($results['sent'] > 0) {
            $tplName = $isRandom ? 'Acak' : ($single->name ?? 'Template');
            app(TelegramService::class)->notifyOutreachSent(
                $results['sent'], $results['failed'], $tplName,
                $sentToday + $results['sent'], $dailyLimit
            );
        }

        return response()->json([
            'status'     => 'ok',
            'results'    => $results,
            'remaining'  => $remaining,
            'sent_today' => $sentToday + $results['sent'],
            'daily_limit'=> $dailyLimit,
        ]);
    }

    public function markStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:none,sent,replied,interested,not_interested,ordered']);
        $place = Place::findOrFail($id);
        $old = $place->outreach_status;
        $place->update(['outreach_status' => $request->status]);
        if ($old !== $request->status) {
            OutreachLog::create(['place_id' => $place->id, 'action' => 'status_changed', 'status' => $request->status]);
            if ($request->status === 'interested') {
                app(TelegramService::class)->notifyInterested($place->name, $place->phone ?? '');
            }
        }
        return response()->json(['status' => 'ok', 'outreach_status' => $request->status]);
    }

    public function saveNotes(Request $request, $id)
    {
        $request->validate(['notes' => 'nullable|string|max:2000']);
        $place = Place::findOrFail($id);
        $place->update([
            'notes'            => $request->notes ?: null,
            'notes_updated_at' => now(),
        ]);
        if ($request->notes) {
            OutreachLog::create(['place_id' => $place->id, 'action' => 'note_added', 'note' => mb_substr($request->notes, 0, 200)]);
        }
        return response()->json(['status' => 'ok']);
    }

    public function stats()
    {
        return response()->json($this->getStats());
    }

    public function targetList(Request $request)
    {
        $filter   = $request->get('filter', 'pending');
        $category = $request->get('category', '');

        $q = Place::where('has_whatsapp', true)->whereNotNull('phone');

        match ($filter) {
            'sent'      => $q->where('outreach_status', 'sent'),
            'responded' => $q->whereIn('outreach_status', ['responded','replied']),
            'interested'=> $q->where('outreach_status', 'interested'),
            'ordered'   => $q->where('outreach_status', 'ordered'),
            'pending'   => $q->whereNull('outreach_status'),
            default     => null,
        };

        if ($category === 'relevant') {
            $q->whereIn('category', self::RELEVANT_CATEGORIES);
        } elseif ($category) {
            $q->where('category', $category);
        }

        $q->selectRaw('id, name, phone, category, address, rating, review_count, outreach_status, outreach_sent_at, (' . $this->priorityScoreExpr() . ') as priority_score');

        if ($filter === 'pending') {
            $q->orderByRaw('priority_score DESC');
        } else {
            $q->orderByDesc('outreach_sent_at');
        }

        $places = $q->limit(200)->get();

        return response()->json([
            'status' => 'ok',
            'count'  => $places->count(),
            'data'   => $places,
        ]);
    }

    public function previewTargets(Request $request)
    {
        $request->validate([
            'limit'           => 'integer|min:1|max:50',
            'category_filter' => 'nullable|string',
            'template_id'     => 'nullable|integer',
        ]);

        $limit          = (int) $request->get('limit', 5);
        $categoryFilter = $request->get('category_filter', 'relevant');
        $templateId     = (int) $request->get('template_id', 0);

        $activeTemplates = WaTemplate::active()->get();
        $sampleTemplate  = ($templateId && $templateId !== 0)
            ? $activeTemplates->firstWhere('id', $templateId)
            : $activeTemplates->first();

        $sentPhones = Place::where('outreach_status', 'sent')
            ->whereNotNull('phone')->pluck('phone')->flip()->all();

        $q = Place::where('has_whatsapp', true)
            ->whereNull('outreach_status')
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if ($categoryFilter === 'relevant') {
            $q->whereIn('category', self::RELEVANT_CATEGORIES);
        } elseif ($categoryFilter) {
            $q->where('category', $categoryFilter);
        }
        $this->applyChainExclusion($q);

        $places = $q->orderByRaw('(' . $this->priorityScoreExpr() . ') DESC')
            ->limit($limit * 3)
            ->get(['id', 'name', 'phone', 'category', 'address', 'rating', 'review_count'])
            ->filter(fn($p) => !isset($sentPhones[$p->phone]))
            ->take($limit)
            ->values();

        $data = $places->map(fn($p) => [
            'id'           => $p->id,
            'name'         => $p->name,
            'phone'        => $p->phone,
            'category'     => $p->category,
            'address'      => $p->address,
            'rating'       => $p->rating,
            'review_count' => $p->review_count,
        ]);

        $sampleMessage = '';
        if ($sampleTemplate && $places->isNotEmpty()) {
            $sampleMessage = $this->renderTemplate($sampleTemplate->body, $places->first());
        }

        return response()->json([
            'status'         => 'ok',
            'count'          => $data->count(),
            'data'           => $data,
            'sample_message' => $sampleMessage,
        ]);
    }

    // ── FITUR 1: Follow-up ────────────────────────────────────────────────────

    public function followupList()
    {
        $followup = Place::where('outreach_status', 'sent')
            ->where('outreach_sent_at', '<', now()->subDays(3))
            ->orderBy('outreach_sent_at', 'asc')
            ->get(['id', 'name', 'phone', 'category', 'outreach_sent_at'])
            ->map(fn($p) => [
                'id'               => $p->id,
                'name'             => $p->name,
                'phone'            => $p->phone,
                'category'         => $p->category,
                'outreach_sent_at' => $p->outreach_sent_at?->toDateTimeString(),
                'days_since'       => $p->outreach_sent_at ? (int) $p->outreach_sent_at->diffInDays(now()) : null,
            ]);

        $interested = Place::where('outreach_status', 'interested')
            ->orderBy('outreach_sent_at', 'asc')
            ->get(['id', 'name', 'phone', 'category', 'outreach_sent_at'])
            ->map(fn($p) => [
                'id'               => $p->id,
                'name'             => $p->name,
                'phone'            => $p->phone,
                'category'         => $p->category,
                'outreach_sent_at' => $p->outreach_sent_at?->toDateTimeString(),
                'days_since'       => $p->outreach_sent_at ? (int) $p->outreach_sent_at->diffInDays(now()) : null,
            ]);

        $replied = Place::where('outreach_status', 'replied')
            ->orderBy('outreach_sent_at', 'asc')
            ->get(['id', 'name', 'phone', 'category', 'outreach_sent_at'])
            ->map(fn($p) => [
                'id'               => $p->id,
                'name'             => $p->name,
                'phone'            => $p->phone,
                'category'         => $p->category,
                'outreach_sent_at' => $p->outreach_sent_at?->toDateTimeString(),
                'days_since'       => $p->outreach_sent_at ? (int) $p->outreach_sent_at->diffInDays(now()) : null,
            ]);

        return response()->json([
            'status'     => 'ok',
            'followup'   => $followup,
            'interested' => $interested,
            'replied'    => $replied,
        ]);
    }

    // ── FITUR 2: Template stats ───────────────────────────────────────────────

    public function templateStats()
    {
        $stats = OutreachLog::where('action', 'sent')
            ->whereNotNull('template_name')
            ->selectRaw('template_id, template_name, COUNT(*) as sent_count')
            ->groupBy('template_id', 'template_name')
            ->get()
            ->map(function ($row) {
                $placeIds = OutreachLog::where('action', 'sent')->where('template_id', $row->template_id)->pluck('place_id');
                return [
                    'template_id'   => $row->template_id,
                    'template_name' => $row->template_name,
                    'sent'          => $row->sent_count,
                    'replied'       => Place::whereIn('id', $placeIds)->whereIn('outreach_status', ['replied', 'responded'])->count(),
                    'interested'    => Place::whereIn('id', $placeIds)->where('outreach_status', 'interested')->count(),
                    'ordered'       => Place::whereIn('id', $placeIds)->where('outreach_status', 'ordered')->count(),
                ];
            });

        return response()->json(['status' => 'ok', 'data' => $stats]);
    }

    // ── FITUR 3: Order tracking ───────────────────────────────────────────────

    public function storeOrder(Request $request, $id)
    {
        $place = Place::findOrFail($id);
        $request->validate([
            'item'       => 'required|string|max:100',
            'qty'        => 'required|numeric|min:0.01',
            'unit'       => 'required|in:kg,pcs,dus,box',
            'total_rp'   => 'required|numeric|min:0',
            'order_date' => 'required|date',
            'notes'      => 'nullable|string|max:500',
        ]);

        $order = PlaceOrder::create([
            'place_id'   => $place->id,
            'item'       => $request->item,
            'qty'        => $request->qty,
            'unit'       => $request->unit,
            'total_rp'   => $request->total_rp,
            'order_date' => $request->order_date,
            'notes'      => $request->notes,
        ]);

        app(TelegramService::class)->notifyNewOrder($place->name, $request->item, (float) $request->total_rp);

        return response()->json(['status' => 'ok', 'order' => $order]);
    }

    public function getOrders($id)
    {
        $place  = Place::findOrFail($id);
        $orders = PlaceOrder::where('place_id', $place->id)->orderByDesc('order_date')->get();
        $total  = $orders->sum('total_rp');
        return response()->json(['status' => 'ok', 'data' => $orders, 'total_rp' => $total]);
    }

    public function deleteOrder($id, $orderId)
    {
        $order = PlaceOrder::where('place_id', $id)->findOrFail($orderId);
        $order->delete();
        return response()->json(['status' => 'ok']);
    }

    // ── FITUR 4: Bulk status ──────────────────────────────────────────────────

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer',
            'status' => 'required|in:none,sent,replied,interested,not_interested,ordered',
        ]);

        $count = Place::whereIn('id', $request->ids)->update(['outreach_status' => $request->status]);
        foreach ($request->ids as $id) {
            OutreachLog::create(['place_id' => $id, 'action' => 'status_changed', 'status' => $request->status]);
        }

        return response()->json(['status' => 'ok', 'updated' => $count]);
    }

    // ── FITUR 5: Re-check WA ─────────────────────────────────────────────────

    public function reCheckCount()
    {
        $count = Place::whereNotNull('phone')->where('phone', '!=', '')->where('has_whatsapp', false)->count();
        return response()->json(['status' => 'ok', 'count' => $count]);
    }

    public function reCheckWA(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'limit'     => 'integer|min:1|max:100',
        ]);

        $deviceId = $request->device_id;
        $limit    = (int) $request->get('limit', 30);

        $places = Place::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('has_whatsapp', false)
            ->limit($limit)
            ->get(['id', 'name', 'phone']);

        $results = ['checked' => 0, 'has_wa' => 0, 'no_wa' => 0, 'error' => 0];

        if ($places->isEmpty()) {
            return response()->json(['status' => 'ok', 'results' => $results, 'remaining' => 0]);
        }

        try {
            $numbers = $places->pluck('phone')->toArray();
            $resp = Http::timeout(60)
                ->withHeaders(['X-API-Key' => env('WA_API_KEY', '')])
                ->post("{$this->waApiUrl}/check-wa-batch?deviceId={$deviceId}", [
                    'numbers' => $numbers,
                ]);

            if ($resp->successful() && $resp->json('status')) {
                $waResults = collect($resp->json('results'))->keyBy('number');
                foreach ($places as $place) {
                    $waResult = $waResults->get($place->phone);
                    $hasWa    = $waResult ? (bool) $waResult['exists'] : false;
                    $place->update(['has_whatsapp' => $hasWa, 'wa_checked_at' => now()]);
                    $hasWa ? $results['has_wa']++ : $results['no_wa']++;
                    $results['checked']++;
                }
            } else {
                $results['error'] = $places->count();
            }
        } catch (\Exception $e) {
            Log::warning("reCheckWA batch error: " . $e->getMessage());
            $results['error'] = $places->count();
        }

        $remaining = Place::whereNotNull('phone')->where('phone', '!=', '')->where('has_whatsapp', false)->count();

        app(TelegramService::class)->notifyWaChecked($results['has_wa'], $results['no_wa'], $results['checked']);

        return response()->json([
            'status'    => 'ok',
            'results'   => $results,
            'remaining' => $remaining,
        ]);
    }

    // ── FITUR 6: Duplikat detection ───────────────────────────────────────────

    public function duplicates()
    {
        $dupePhones = DB::select("
            SELECT phone, COUNT(*) as cnt, GROUP_CONCAT(id ORDER BY id SEPARATOR ',') as ids,
                   GROUP_CONCAT(name ORDER BY id SEPARATOR '||') as names
            FROM places WHERE phone IS NOT NULL AND phone != ''
            GROUP BY phone HAVING COUNT(*) > 1
            ORDER BY cnt DESC LIMIT 100
        ");

        $dupes = array_map(function ($row) {
            $ids   = explode(',', $row->ids);
            $names = explode('||', $row->names);
            $entries = [];
            foreach ($ids as $i => $id) {
                $entries[] = ['id' => (int) $id, 'name' => $names[$i] ?? ''];
            }
            return ['phone' => $row->phone, 'count' => $row->cnt, 'entries' => $entries];
        }, $dupePhones);

        if (count($dupes) > 0) {
            app(TelegramService::class)->notifyDuplicatesFound(count($dupes));
        }

        return response()->json(['status' => 'ok', 'count' => count($dupes), 'data' => $dupes]);
    }

    // ── constants ─────────────────────────────────────────────────────────────

    const EXCLUDED_CHAINS = [
        'indomaret', 'alfamart', 'alfamidi', 'circle k', 'lawson', 'family mart',
        'mcdonald', 'kfc', 'burger king', 'pizza hut', 'wendy',
        'starbucks', 'jco', 'chatime', 'gong cha', 'fore coffee',
        'hypermart', 'carrefour', 'superindo', 'lotte mart', 'transmart',
        'hokben', 'hoka hoka', 'solaria', 'yoshinoya', 'popeyes', 'texas chicken',
    ];

    const RELEVANT_CATEGORIES = [
        'Restoran', 'Warung Makan', 'Restoran Ayam', 'Restoran Bakso',
        'Restoran Cepat Saji', 'Restoran Seafood', 'Restoran Sate',
        'Restoran Mie', 'Restoran Nasi Goreng', 'Restoran China',
        'Restoran Indonesia', 'Restoran Padang',
        'Toko Buah dan Sayur', 'Toko Buah dan Sayuran', 'Grosir Buah dan Sayur',
        'Toko grosir buah dan sayur', 'Toko Buah Kering',
        'Toko Bahan Makanan', 'Minimarket', 'Supermarket', 'Pasar',
        'Kedai Jus', 'Kedai Hidangan Penutup', 'Toko Es Krim',
        'Kedai Kopi', 'Pusat Perbelanjaan',
    ];

    // ── private ───────────────────────────────────────────────────────────────

    private function applyChainExclusion($query): void
    {
        if (!self::EXCLUDED_CHAINS) return;
        $pattern = implode('|', self::EXCLUDED_CHAINS);
        $query->whereRaw('LOWER(name) NOT REGEXP ?', [$pattern]);
    }

    private function priorityScoreExpr(): string
    {
        $cats = implode(',', array_map(fn($c) => "'" . addslashes($c) . "'", self::RELEVANT_CATEGORIES));
        return "
            (CASE WHEN category IN ({$cats}) THEN 30 ELSE 0 END)
            + (CASE WHEN popular_times IS NOT NULL AND popular_times != '[]' THEN 20 ELSE 0 END)
            + (CASE WHEN review_count >= 200 THEN 20
                    WHEN review_count >= 100 THEN 15
                    WHEN review_count >= 50  THEN 10
                    WHEN review_count >= 10  THEN 5
                    ELSE 0 END)
            + (CASE WHEN rating >= 4.5 THEN 5 WHEN rating >= 4.0 THEN 3 ELSE 0 END)
        ";
    }

    private function getDevices(): array
    {
        try {
            $resp = Http::timeout(5)->get("{$this->waApiUrl}/api/devices");
            return $resp->successful() ? ($resp->json('data') ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getStats(): array
    {
        $dailyLimit = (int) env('WA_DAILY_LIMIT', 50);
        $sentToday  = OutreachLog::where('action', 'sent')->whereDate('created_at', today())->count();
        return [
            'total'          => Place::count(),
            'with_phone'     => Place::whereNotNull('phone')->where('phone', '!=', '')->count(),
            'has_wa'         => Place::where('has_whatsapp', true)->count(),
            'no_wa'          => Place::where('has_whatsapp', false)->count(),
            'unchecked'      => Place::whereNotNull('phone')->where('phone', '!=', '')->whereNull('has_whatsapp')->count(),
            'outreach_sent'   => Place::where('outreach_status', 'sent')->count(),
            'replied'         => Place::whereIn('outreach_status', ['replied','responded'])->count(),
            'interested'      => Place::where('outreach_status', 'interested')->count(),
            'not_interested'  => Place::where('outreach_status', 'not_interested')->count(),
            'ordered'         => Place::where('outreach_status', 'ordered')->count(),
            'remaining'       => Place::where('has_whatsapp', true)
                                    ->where(fn($q) => $q->whereNull('outreach_status')->orWhere('outreach_status', 'none'))
                                    ->count(),
            'remaining_relevant' => Place::where('has_whatsapp', true)
                                    ->whereIn('category', self::RELEVANT_CATEGORIES)
                                    ->where(fn($q) => $q->whereNull('outreach_status')->orWhere('outreach_status', 'none'))
                                    ->count(),
            'sent_today'     => $sentToday,
            'daily_limit'    => $dailyLimit,
            'daily_remaining'=> max(0, $dailyLimit - $sentToday),
        ];
    }

    // ── Template CRUD ─────────────────────────────────────────────────────────

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:80',
            'body' => 'required|string|max:2000',
        ]);
        $tpl = WaTemplate::create([
            'name'       => $request->name,
            'body'       => $request->body,
            'is_active'  => true,
            'sort_order' => WaTemplate::max('sort_order') + 1,
        ]);
        return response()->json(['status' => 'ok', 'template' => $tpl]);
    }

    public function updateTemplate(Request $request, WaTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:80',
            'body' => 'required|string|max:2000',
        ]);
        $template->update($request->only('name', 'body'));
        return response()->json(['status' => 'ok', 'template' => $template]);
    }

    public function destroyTemplate(WaTemplate $template)
    {
        $template->delete();
        return response()->json(['status' => 'ok']);
    }

    public function toggleTemplate(WaTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        return response()->json(['status' => 'ok', 'is_active' => $template->is_active]);
    }

    // ── WEBHOOK: Pesan WA Masuk ───────────────────────────────────────────────

    public function handleWebhook(Request $request)
    {
        $event    = $request->input('event');
        $from     = preg_replace('/[^0-9]/', '', $request->input('from', ''));
        $message  = $request->input('message', '');
        $deviceId = $request->input('device_id', '');
        $ts       = $request->input('timestamp');

        if ($event !== 'message.received' || !$from) {
            return response()->json(['ok' => true]);
        }

        $receivedAt = $ts ? \Carbon\Carbon::createFromTimestamp($ts) : now();

        // Cari prospek berdasarkan nomor telepon
        $place = Place::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get(['id', 'name', 'phone', 'outreach_status'])
            ->first(function ($p) use ($from) {
                return preg_replace('/[^0-9]/', '', $p->phone) === $from;
            });

        $isProspect  = (bool) $place;
        $actionTaken = 'none';

        if ($place) {
            // Auto-update ke replied jika status masih sent
            if ($place->outreach_status === 'sent') {
                $place->update(['outreach_status' => 'replied']);
                OutreachLog::create([
                    'place_id' => $place->id,
                    'action'   => 'status_changed',
                    'status'   => 'replied',
                    'note'     => 'Auto-update dari pesan WA masuk: ' . mb_substr($message, 0, 100),
                ]);
                $actionTaken = 'status_updated';
            }

            // Kirim notif Telegram
            $snippet = mb_strlen($message) > 60 ? mb_substr($message, 0, 60) . '…' : $message;
            app(TelegramService::class)->notifyIncomingMessage($place->name, $place->phone, $snippet, $place->outreach_status);
        }

        WaIncomingMessage::create([
            'device_id'    => $deviceId,
            'from_number'  => $from,
            'message'      => mb_substr($message, 0, 1000),
            'place_id'     => $place?->id,
            'is_prospect'  => $isProspect,
            'action_taken' => $actionTaken,
            'received_at'  => $receivedAt,
        ]);

        return response()->json(['ok' => true]);
    }

    public function webhookStatus()
    {
        $waApiUrl = rtrim(env('WA_API_URL', 'http://localhost:8000'), '/');
        $myUrl    = url('/whatsapp/webhook');

        try {
            $resp = Http::timeout(5)->get("{$waApiUrl}/api/config/webhooks");
            $urls = $resp->json('data') ?? [];
        } catch (\Exception) {
            $urls = [];
        }

        return response()->json([
            'registered' => in_array($myUrl, $urls),
            'webhook_url' => $myUrl,
            'all_urls'   => $urls,
        ]);
    }

    public function registerWebhook()
    {
        $waApiUrl = rtrim(env('WA_API_URL', 'http://localhost:8000'), '/');
        $myUrl    = url('/whatsapp/webhook');

        try {
            $resp = Http::timeout(5)->post("{$waApiUrl}/api/config/webhooks", ['url' => $myUrl]);
            return response()->json(['status' => $resp->json('status') ? 'ok' : 'error']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function unregisterWebhook()
    {
        $waApiUrl = rtrim(env('WA_API_URL', 'http://localhost:8000'), '/');
        $myUrl    = url('/whatsapp/webhook');

        try {
            $resp = Http::timeout(5)->post("{$waApiUrl}/api/config/webhooks", ['url' => $myUrl, 'action' => 'delete']);
            return response()->json(['status' => $resp->json('status') ? 'ok' : 'error']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function incomingMessages()
    {
        $messages = WaIncomingMessage::with('place:id,name,outreach_status')
            ->where('is_prospect', true)
            ->orderByDesc('received_at')
            ->limit(50)
            ->get();

        return response()->json(['status' => 'ok', 'data' => $messages]);
    }

    private function renderTemplate(string $body, Place $place): string
    {
        $nama = $place->name ?? 'Bapak/Ibu';
        return str_replace(['{nama}', '{kategori}', '{alamat}'], [$nama, $place->category ?? '', $place->address ?? ''], $body);
    }
}
