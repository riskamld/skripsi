<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductPrice;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductPrice::with('place');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'like', '%' . $search . '%')
                  ->orWhere('product_category', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%')
                  ->orWhereHas('place', function($placeQuery) use ($search) {
                      $placeQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by product name
        if ($request->filled('product_name')) {
            $query->where('product_name', $request->product_name);
        }

        // Filter by category
        if ($request->filled('product_category')) {
            $query->where('product_category', $request->product_category);
        }

        // Filter by place
        if ($request->filled('place_id')) {
            $query->where('place_id', $request->place_id);
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('recorded_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('recorded_at', '<=', $request->date_to);
        }

        // Sort functionality
        $sortField = $request->get('sort', 'recorded_at');
        $sortDirection = $request->get('direction', 'desc');

        if (in_array($sortField, ['product_name', 'price', 'recorded_at', 'source', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('recorded_at', 'desc');
        }

        $prices = $query->paginate(20)->onEachSide(2);

        // Get filter options
        $places = Place::select('id', 'name')->orderBy('name')->get();
        $productNames = ProductPrice::select('product_name')->distinct()->pluck('product_name')->sort();
        $categories = ProductPrice::select('product_category')->whereNotNull('product_category')->distinct()->pluck('product_category')->sort();
        $sources = ['manual', 'scraped', 'estimated', 'api'];

        return view('product-prices.index', compact('prices', 'places', 'productNames', 'categories', 'sources'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $places = Place::select('id', 'name')->orderBy('name')->get();
        $productNames = ProductPrice::select('product_name')->distinct()->pluck('product_name')->sort();

        return view('product-prices.create', compact('places', 'productNames'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'place_id' => 'required|exists:places,id',
            'product_category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'original_price' => 'nullable|numeric|min:0',
            'source' => 'nullable|in:manual,scraped,estimated,api',
            'confidence_score' => 'nullable|numeric|min:0|max:1',
            'supply_index' => 'nullable|numeric|min:0|max:100',
            'demand_index' => 'nullable|numeric|min:0|max:100',
            'season' => 'nullable|in:peak,normal,low',
            'is_holiday_season' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
            'recorded_at' => 'nullable|date',
        ]);

        // Set recorded_at to now if not provided
        $data = $request->all();
        if (!isset($data['recorded_at']) || empty($data['recorded_at'])) {
            $data['recorded_at'] = now();
        }

        // Set default values
        if (!isset($data['source'])) {
            $data['source'] = 'manual';
        }
        if (!isset($data['unit'])) {
            $data['unit'] = 'pcs';
        }
        if (!isset($data['confidence_score'])) {
            $data['confidence_score'] = 1.00;
        }

        // Get place coordinates for location-based analysis
        $place = Place::find($data['place_id']);
        if ($place) {
            $data['lat'] = $place->lat;
            $data['lng'] = $place->lng;
        }

        ProductPrice::create($data);

        return redirect()->route('product-prices.index')
            ->with('success', 'Product price created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductPrice $productPrice)
    {
        $productPrice->load('place');

        // Get related prices for the same product
        $relatedPrices = ProductPrice::where('product_name', $productPrice->product_name)
            ->where('id', '!=', $productPrice->id)
            ->with('place')
            ->orderBy('recorded_at', 'desc')
            ->limit(10)
            ->get();

        return view('product-prices.show', compact('productPrice', 'relatedPrices'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductPrice $productPrice)
    {
        $places = Place::select('id', 'name')->orderBy('name')->get();
        $productNames = ProductPrice::select('product_name')->distinct()->pluck('product_name')->sort();

        return view('product-prices.edit', compact('productPrice', 'places', 'productNames'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductPrice $productPrice)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'place_id' => 'required|exists:places,id',
            'product_category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'original_price' => 'nullable|numeric|min:0',
            'source' => 'nullable|in:manual,scraped,estimated,api',
            'confidence_score' => 'nullable|numeric|min:0|max:1',
            'supply_index' => 'nullable|numeric|min:0|max:100',
            'demand_index' => 'nullable|numeric|min:0|max:100',
            'season' => 'nullable|in:peak,normal,low',
            'is_holiday_season' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
            'recorded_at' => 'nullable|date',
        ]);

        $data = $request->all();

        // Set default values
        if (!isset($data['source'])) {
            $data['source'] = 'manual';
        }
        if (!isset($data['unit'])) {
            $data['unit'] = 'pcs';
        }
        if (!isset($data['confidence_score'])) {
            $data['confidence_score'] = 1.00;
        }

        // Update place coordinates
        $place = Place::find($data['place_id']);
        if ($place) {
            $data['lat'] = $place->lat;
            $data['lng'] = $place->lng;
        }

        $productPrice->update($data);

        return redirect()->route('product-prices.index')
            ->with('success', 'Product price updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductPrice $productPrice)
    {
        $productPrice->delete();

        return redirect()->route('product-prices.index')
            ->with('success', 'Product price deleted successfully!');
    }

    /**
     * Bulk delete selected product prices
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:product_prices,id',
        ]);

        $count = ProductPrice::whereIn('id', $request->ids)->delete();

        return redirect()->route('product-prices.index')
            ->with('success', "{$count} product price(s) deleted successfully!");
    }

    /**
     * Clear all product prices
     */
    public function clearAll()
    {
        $count = ProductPrice::count();
        ProductPrice::truncate();

        return redirect()->route('product-prices.index')
            ->with('success', "All {$count} product prices cleared successfully!");
    }
}
