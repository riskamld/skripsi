<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\WaTemplate;
use Illuminate\Http\Request;
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

        return response()->json([
            'status'    => 'ok',
            'results'   => $results,
            'remaining' => $remaining,
        ]);
    }

    public function sendOutreach(Request $request)
    {
        $request->validate([
            'device_id'   => 'required|string',
            'template_id' => 'required',        // 0 = acak, angka = id spesifik
            'limit'       => 'integer|min:1|max:20',
        ]);

        $deviceId   = $request->device_id;
        $templateId = $request->template_id;   // "0" = random
        $limit      = (int) $request->get('limit', 5);
        $isRandom   = ($templateId == 0);

        $activeTemplates = WaTemplate::active()->get();
        if ($activeTemplates->isEmpty()) {
            return response()->json(['error' => 'Tidak ada template aktif'], 422);
        }

        if (!$isRandom) {
            $single = $activeTemplates->firstWhere('id', $templateId);
            if (!$single) {
                return response()->json(['error' => 'Template tidak valid'], 422);
            }
        }

        // Places: punya WA, belum pernah di-outreach
        $places = Place::where('has_whatsapp', true)
            ->whereNull('outreach_status')
            ->whereNotNull('phone')
            ->limit($limit)
            ->get(['id', 'name', 'phone', 'category', 'address']);

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

        $remaining = Place::where('has_whatsapp', true)->whereNull('outreach_status')->count();

        return response()->json([
            'status'    => 'ok',
            'results'   => $results,
            'remaining' => $remaining,
        ]);
    }

    public function markStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:none,sent,replied,interested,not_interested,ordered']);
        $place = Place::findOrFail($id);
        $place->update(['outreach_status' => $request->status]);
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
        return response()->json(['status' => 'ok']);
    }

    public function stats()
    {
        return response()->json($this->getStats());
    }

    public function targetList(Request $request)
    {
        $filter = $request->get('filter', 'pending');

        $q = Place::where('has_whatsapp', true)->whereNotNull('phone');

        match ($filter) {
            'sent'      => $q->where('outreach_status', 'sent'),
            'responded' => $q->where('outreach_status', 'responded'),
            'pending'   => $q->whereNull('outreach_status'),
            default     => null,
        };

        $places = $q->orderByDesc('outreach_sent_at')
            ->limit(100)
            ->get(['id', 'name', 'phone', 'category', 'address', 'rating', 'outreach_status', 'outreach_sent_at']);

        return response()->json([
            'status' => 'ok',
            'count'  => $places->count(),
            'data'   => $places,
        ]);
    }

    // ── private ───────────────────────────────────────────────────────────────

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

    private function renderTemplate(string $body, Place $place): string
    {
        $nama = $place->name ?? 'Bapak/Ibu';
        return str_replace(['{nama}', '{kategori}', '{alamat}'], [$nama, $place->category ?? '', $place->address ?? ''], $body);
    }
}
