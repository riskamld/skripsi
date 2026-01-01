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

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            Log::info('Search query', ['search' => $search]);

            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });

            // Debug: Log the count of results
            $countBeforePaginate = $query->count();
            Log::info('Search results count', ['count' => $countBeforePaginate, 'search' => $search]);
        }

        // Filter by categories (multiple selection support)
        if ($request->filled('categories')) {
            $categories = is_array($request->categories) ? $request->categories : [$request->categories];
            $query->whereIn('category', $categories);
        } elseif ($request->filled('category')) {
            // Backward compatibility with single category
            $query->where('category', $request->category);
        }

        // Filter by rating range
        if ($request->filled('rating_min')) {
            $query->where('rating', '>=', $request->rating_min);
        }
        if ($request->filled('rating_max')) {
            $query->where('rating', '<=', $request->rating_max);
        }

        // Sort options with MySQL-compatible NULL handling
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        // Debug logging
        Log::info('Sort parameters', ['sort' => $sortBy, 'direction' => $sortDir, 'all_params' => $request->all()]);

        $allowedSorts = ['name', 'rating', 'review_count', 'created_at', 'updated_at', 'last_scraped_at'];
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'review_count') {
                // MySQL compatible: NULL values go to bottom for review_count
                if ($sortDir === 'desc') {
                    $query->orderByRaw('review_count IS NULL ASC, review_count DESC');
                } else {
                    $query->orderByRaw('review_count IS NULL ASC, review_count ASC');
                }
            } elseif ($sortBy === 'rating') {
                // MySQL compatible: NULL values go to bottom for rating
                if ($sortDir === 'desc') {
                    $query->orderByRaw('rating IS NULL ASC, rating DESC');
                } else {
                    $query->orderByRaw('rating IS NULL ASC, rating ASC');
                }
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
        }

        // Handle AJAX requests for infinite scroll (only when no filters applied)
        if (request()->ajax() && !request()->filled('category') && !request()->filled('search')) {
            $places = $query->paginate(20); // Smaller chunks for infinite scroll
            return response()->json([
                'places' => $places->items(),
                'has_more' => $places->hasMorePages(),
                'next_page' => $places->hasMorePages() ? $places->currentPage() + 1 : null
            ]);
        }

        // When category filter is applied (single or multiple), show all results without pagination
        if (request()->filled('categories') || request()->filled('category') || request()->filled('search')) {
            $places = $query->get(); // Get all results when filtered
        } else {
            $places = $query->paginate(50)->onEachSide(2); // Normal pagination when no filter
        }

        // Get unique categories for filter dropdown with counts (sorted by count descending)
        $categories = Place::whereNotNull('category')
            ->select('category')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('count', 'desc') // Sort by count descending (most places first)
            ->get()
            ->map(function ($item) {
                return ['name' => $item->category, 'count' => $item->count];
            });

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
