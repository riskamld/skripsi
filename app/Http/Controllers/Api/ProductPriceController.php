<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductPriceController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductPrice::with('place');

        // Filter by product name
        if ($request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $request->product_name . '%');
        }

        // Filter by place
        if ($request->filled('place_id')) {
            $query->where('place_id', $request->place_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('recorded_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('recorded_at', '<=', $request->date_to);
        }

        // Sort by recorded_at desc by default
        $query->orderBy('recorded_at', 'desc');

        $prices = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $prices
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            'metadata' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'recorded_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Set recorded_at to now if not provided
        $data = $request->all();
        if (!isset($data['recorded_at'])) {
            $data['recorded_at'] = now();
        }

        // Get place coordinates for location-based analysis
        $place = \App\Models\Place::find($data['place_id']);
        if ($place) {
            $data['lat'] = $place->lat;
            $data['lng'] = $place->lng;
        }

        $price = ProductPrice::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Product price recorded successfully',
            'data' => $price->load('place')
        ], 201);
    }

    public function show(ProductPrice $productPrice)
    {
        return response()->json([
            'status' => 'success',
            'data' => $productPrice->load('place')
        ]);
    }

    public function update(Request $request, ProductPrice $productPrice)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'place_id' => 'sometimes|required|exists:places,id',
            'product_category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'original_price' => 'nullable|numeric|min:0',
            'source' => 'nullable|in:manual,scraped,estimated,api',
            'confidence_score' => 'nullable|numeric|min:0|max:1',
            'supply_index' => 'nullable|numeric|min:0|max:100',
            'demand_index' => 'nullable|numeric|min:0|max:100',
            'season' => 'nullable|in:peak,normal,low',
            'is_holiday_season' => 'nullable|boolean',
            'metadata' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $productPrice->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Product price updated successfully',
            'data' => $productPrice->load('place')
        ]);
    }

    public function destroy(ProductPrice $productPrice)
    {
        $productPrice->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product price deleted successfully'
        ]);
    }

    // Bulk operations for efficiency
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prices' => 'required|array|min:1|max:100',
            'prices.*.product_name' => 'required|string|max:255',
            'prices.*.price' => 'required|numeric|min:0',
            'prices.*.place_id' => 'required|exists:places,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $createdPrices = [];

        foreach ($request->prices as $priceData) {
            // Get place coordinates
            $place = \App\Models\Place::find($priceData['place_id']);
            if ($place) {
                $priceData['lat'] = $place->lat;
                $priceData['lng'] = $place->lng;
            }

            // Set defaults
            $priceData['recorded_at'] = $priceData['recorded_at'] ?? now();
            $priceData['source'] = $priceData['source'] ?? 'api';

            $createdPrices[] = ProductPrice::create($priceData);
        }

        return response()->json([
            'status' => 'success',
            'message' => count($createdPrices) . ' product prices recorded successfully',
            'data' => $createdPrices
        ], 201);
    }

    // Get price history for predictions
    public function history(Request $request, $productName)
    {
        $history = ProductPrice::where('product_name', $productName)
            ->where('recorded_at', '>=', now()->subDays(90))
            ->orderBy('recorded_at', 'asc')
            ->get(['price', 'recorded_at', 'place_id', 'source']);

        return response()->json([
            'status' => 'success',
            'product' => $productName,
            'data_points' => $history->count(),
            'history' => $history
        ]);
    }
}
