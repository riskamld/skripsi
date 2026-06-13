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
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('categories')) {
            $cats = is_array($request->categories) ? $request->categories : [$request->categories];
            $query->whereIn('category', $cats);
        } elseif ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Quick filters (tab-style)
        $quickFilter = $request->get('qf', '');
        match($quickFilter) {
            'wa'        => $query->where('has_whatsapp', true),
            'no_wa'     => $query->where('has_whatsapp', false),
            'unchecked' => $query->whereNull('has_whatsapp'),
            'has_pt'    => $query->whereNotNull('popular_times')->where('popular_times', '!=', '{}')->where('popular_times', '!=', '[]'),
            'target'    => $query->where('is_target', true),
            'prospect'  => $query->where('has_whatsapp', true)->where('is_target', true)
                                 ->whereIn('outreach_status', ['none', null, ''])->whereNull('outreach_status'),
            'sent'           => $query->where('outreach_status', 'sent'),
            'replied'        => $query->where('outreach_status', 'replied'),
            'interested'     => $query->where('outreach_status', 'interested'),
            'not_interested' => $query->where('outreach_status', 'not_interested'),
            'ordered'        => $query->where('outreach_status', 'ordered'),
            'unsent'         => $query->where('has_whatsapp', true)
                                 ->where(fn($q) => $q->whereNull('outreach_status')->orWhere('outreach_status', 'none')),
            default     => null,
        };

        // Sort
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');
        $allowedSorts = ['name', 'rating', 'review_count', 'busyness_score', 'created_at', 'updated_at', 'last_scraped_at'];
        if (in_array($sortBy, $allowedSorts)) {
            if (in_array($sortBy, ['review_count', 'rating', 'busyness_score'])) {
                $dir = $sortDir === 'asc' ? 'ASC' : 'DESC';
                $query->orderByRaw("{$sortBy} IS NULL ASC, {$sortBy} {$dir}");
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
        }

        $places = $query->paginate(50)->onEachSide(2);

        $categories = Place::whereNotNull('category')
            ->where('category', '!=', '')
            ->where('category', 'not regexp', '^[[:space:]]*$')
            ->select('category')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get()
            ->map(fn($item) => ['name' => $item->category, 'count' => $item->count]);

        return view('places.index', compact('places', 'categories'));
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
        $place->load('scrapeLogs');
        return view('places.show', compact('place'));
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
