<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Place::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter tanggal respon
        if ($request->filled('resp_from')) {
            $query->whereDate('responded_at', '>=', $request->resp_from);
        }
        if ($request->filled('resp_to')) {
            $query->whereDate('responded_at', '<=', $request->resp_to);
        }

        // Category filter
        if ($request->filled('categories')) {
            $cats = is_array($request->categories) ? $request->categories : [$request->categories];
            $query->whereIn('category', $cats);
        } elseif ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Faceted quick filters — multi-select per grup (qf[]=target&qf[]=wa&qf[]=sent)
        // Antar grup: AND. Di dalam satu grup: OR.
        $qf = array_filter((array) $request->get('qf', []));

        $dataKeys     = ['target', 'has_pt', 'irrelevant'];
        $waKeys       = ['unchecked', 'wa', 'no_wa'];
        $outreachKeys = ['unsent', 'sent', 'replied', 'interested', 'not_interested', 'ordered'];

        $selData     = array_values(array_intersect($qf, $dataKeys));
        $selWa       = array_values(array_intersect($qf, $waKeys));
        $selOutreach = array_values(array_intersect($qf, $outreachKeys));

        if (!empty($selData)) {
            $query->where(function ($q) use ($selData) {
                foreach ($selData as $key) {
                    match ($key) {
                        'target' => $q->orWhere('is_target', true),
                        'has_pt' => $q->orWhere(fn($qq) => $qq->whereNotNull('popular_times')
                            ->where('popular_times', '!=', '{}')->where('popular_times', '!=', '[]')
                            ->where(fn($qqq) => $qqq->whereNull('is_valid')->orWhere('is_valid', true))),
                        'irrelevant' => $q->orWhere('is_valid', false),
                        default => null,
                    };
                }
            });
        } else {
            // default: sembunyikan yang sudah ditandai tidak relevan
            $query->where(fn($q) => $q->whereNull('is_valid')->orWhere('is_valid', true));
        }

        if (!empty($selWa)) {
            $query->where(function ($q) use ($selWa) {
                foreach ($selWa as $key) {
                    match ($key) {
                        'unchecked' => $q->orWhere(fn($qq) => $qq->whereNull('has_whatsapp')
                            ->where(fn($qqq) => $qqq->whereNull('is_valid')->orWhere('is_valid', true))),
                        'wa' => $q->orWhere(fn($qq) => $qq->where('has_whatsapp', true)
                            ->where(fn($qqq) => $qqq->whereNull('is_valid')->orWhere('is_valid', true))),
                        'no_wa' => $q->orWhere(fn($qq) => $qq->where('has_whatsapp', false)
                            ->where(fn($qqq) => $qqq->whereNull('is_valid')->orWhere('is_valid', true))),
                        default => null,
                    };
                }
            });
        }

        if (!empty($selOutreach)) {
            $query->where(function ($q) use ($selOutreach) {
                foreach ($selOutreach as $key) {
                    match ($key) {
                        'unsent' => $q->orWhere(fn($qq) => $qq->where('has_whatsapp', true)
                            ->where(fn($qqq) => $qqq->whereNull('outreach_status')->orWhere('outreach_status', 'none'))),
                        'sent'           => $q->orWhere('outreach_status', 'sent'),
                        'replied'        => $q->orWhere('outreach_status', 'replied'),
                        'interested'     => $q->orWhere('outreach_status', 'interested'),
                        'not_interested' => $q->orWhere('outreach_status', 'not_interested'),
                        'ordered'        => $q->orWhere('outreach_status', 'ordered'),
                        default          => null,
                    };
                }
            });
        }

        // Sort
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');
        $relevantCats = \App\Http\Controllers\Web\WhatsAppController::RELEVANT_CATEGORIES;
        $catList = implode(',', array_map(fn($c) => "'" . addslashes($c) . "'", $relevantCats));
        $prioritySql = "
            (CASE WHEN category IN ({$catList}) THEN 30 ELSE 0 END)
            + (CASE WHEN popular_times IS NOT NULL AND popular_times != '[]' THEN 20 ELSE 0 END)
            + (CASE WHEN review_count >= 200 THEN 20 WHEN review_count >= 100 THEN 15 WHEN review_count >= 50 THEN 10 WHEN review_count >= 10 THEN 5 ELSE 0 END)
            + (CASE WHEN rating >= 4.5 THEN 5 WHEN rating >= 4.0 THEN 3 ELSE 0 END)
        ";

        $allowedSorts = ['name', 'rating', 'review_count', 'busyness_score', 'created_at', 'updated_at', 'last_scraped_at', 'pt_peak', 'priority'];
        if ($sortBy === 'priority') {
            $query->orderByRaw("({$prioritySql}) DESC");
        } elseif ($sortBy === 'pt_peak') {
            // Tempat dengan jam ramai dulu, urut terbanyak
            $query->orderByRaw("CASE WHEN popular_times IS NULL OR popular_times = '[]' THEN 0 ELSE 1 END DESC")
                  ->orderByDesc('review_count');
        } elseif (in_array($sortBy, $allowedSorts)) {
            if (in_array($sortBy, ['review_count', 'rating', 'busyness_score'])) {
                $dir = $sortDir === 'asc' ? 'ASC' : 'DESC';
                $query->orderByRaw("{$sortBy} IS NULL ASC, {$sortBy} {$dir}");
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
        }

        $places = $query->paginate(50)->onEachSide(2)->appends($request->query());

        // Hitung badge count untuk tiap filter
        $filterCounts = \Illuminate\Support\Facades\DB::selectOne("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN is_valid = 0 THEN 1 ELSE 0 END) as irrelevant,
                SUM(CASE WHEN (is_valid IS NULL OR is_valid = 1) AND has_whatsapp IS NULL THEN 1 ELSE 0 END) as unchecked,
                SUM(CASE WHEN (is_valid IS NULL OR is_valid = 1) AND has_whatsapp = 1 THEN 1 ELSE 0 END) as wa,
                SUM(CASE WHEN (is_valid IS NULL OR is_valid = 1) AND has_whatsapp = 0 THEN 1 ELSE 0 END) as no_wa,
                SUM(CASE WHEN (is_valid IS NULL OR is_valid = 1) AND is_target = 1 THEN 1 ELSE 0 END) as target,
                SUM(CASE WHEN (is_valid IS NULL OR is_valid = 1) AND popular_times IS NOT NULL AND popular_times NOT IN ('{}','[]') THEN 1 ELSE 0 END) as has_pt,
                SUM(CASE WHEN (is_valid IS NULL OR is_valid = 1) AND has_whatsapp = 1 AND (outreach_status IS NULL OR outreach_status = 'none') THEN 1 ELSE 0 END) as unsent,
                SUM(CASE WHEN outreach_status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN outreach_status = 'replied' THEN 1 ELSE 0 END) as replied,
                SUM(CASE WHEN outreach_status = 'interested' THEN 1 ELSE 0 END) as interested,
                SUM(CASE WHEN outreach_status = 'not_interested' THEN 1 ELSE 0 END) as not_interested,
                SUM(CASE WHEN outreach_status = 'ordered' THEN 1 ELSE 0 END) as ordered
            FROM places
        ");

        $categories = Place::whereNotNull('category')
            ->where('category', '!=', '')
            ->where('category', 'not regexp', '^[[:space:]]*$')
            ->select('category')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get()
            ->map(fn($item) => ['name' => $item->category, 'count' => $item->count]);

        return view('places.index', compact('places', 'categories', 'filterCounts'));
    }

    public function create()
    {
        return view('places.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'place_id' => 'required|string|max:255|unique:places,place_id',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'is_valid' => 'boolean',
        ]);

        Place::create($request->all());

        return redirect()->route('places.index')
            ->with('success', 'Place created successfully');
    }

    public function show(Place $place)
    {
        $place->load(['scrapeLogs', 'orders']);
        $outreachLogs = \App\Models\OutreachLog::where('place_id', $place->id)
            ->orderByDesc('created_at')->limit(20)->get();
        $responseTimeline = \App\Models\PlaceResponse::where('place_id', $place->id)
            ->orderByDesc('responded_at')->get();
        return view('places.show', compact('place', 'outreachLogs', 'responseTimeline'));
    }

    public function edit(Place $place)
    {
        return view('places.edit', compact('place'));
    }

    public function update(Request $request, Place $place)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'is_valid' => 'boolean',
        ]);

        $place->update($request->all());

        return redirect()->route('places.show', $place)
            ->with('success', 'Place updated successfully');
    }

    public function destroy(Place $place)
    {
        $place->delete();

        return redirect()->route('places.index')
            ->with('success', 'Place deleted successfully');
    }

    public function clearAll(Request $request)
    {
        Place::query()->delete();

        return redirect()->route('places.index')
            ->with('success', 'All places cleared successfully');
    }

    public function quickSearch(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (mb_strlen($q) < 2) return response()->json([]);

        $places = Place::where(fn($query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
            )
            ->where(fn($query) => $query->whereNull('is_valid')->orWhere('is_valid', true))
            ->orderByDesc('busyness_score')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'category', 'outreach_status', 'customer_name', 'response_admin', 'image_1']);

        return response()->json($places->map(fn($p) => [
            'id'              => $p->id,
            'name'            => $p->name,
            'phone'           => $p->phone,
            'category'        => $p->category,
            'outreach_status' => $p->outreach_status,
            'customer_name'   => $p->customer_name,
            'response_admin'  => $p->response_admin,
            'thumb'           => $p->image_1 ? preg_replace('/=w\d+-h\d+[^"]*$/', '=w48-h48-k-no', $p->image_1) : null,
            'detail_url'      => route('places.show', $p->id),
        ]));
    }

    public function toggleRelevance(Place $place)
    {
        $markIrrelevant = $place->is_valid !== false;
        $place->update([
            'is_valid'  => !$markIrrelevant,
            'is_target' => $markIrrelevant ? $place->is_target : false,
        ]);
        return response()->json([
            'status'   => 'ok',
            'is_valid' => !$markIrrelevant,
            'message'  => $markIrrelevant ? 'Ditandai tidak relevan.' : 'Diaktifkan kembali.',
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:places,id',
        ]);

        $count = Place::whereIn('id', $request->ids)->delete();

        return redirect()->route('places.index')
            ->with('success', "{$count} tempat berhasil dihapus!");
    }
}
