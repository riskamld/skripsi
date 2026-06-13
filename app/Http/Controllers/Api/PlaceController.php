<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Place;
use App\Models\ScrapeLog;
use App\Jobs\ScrapePlaceJob;

class PlaceController extends Controller
{
    public function index()
    {
        $places = Place::latest()->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $places,
        ]);
    }

    public function store(Request $request)
    {
        Log::info('🌐 [API] PlaceController store called', [
            'request_data' => $request->all(),
            'headers' => [
                'content_type' => $request->header('Content-Type'),
                'api_token' => $request->header('X-API-TOKEN') ? 'PRESENT' : 'MISSING'
            ]
        ]);

        $validator = Validator::make($request->all(), [
            'place_id'           => 'required|string|max:255',
            'name'               => 'nullable|string|max:255',
            'lat'                => 'nullable|numeric',
            'lng'                => 'nullable|numeric',
            'maps_url'           => 'nullable|string|max:2048',
            'rating'             => 'nullable|numeric|min:0|max:5',
            'review_count'       => 'nullable|integer|min:0',
            'category'           => 'nullable|string|max:255',
            'address'            => 'nullable|string',
            'phone'              => 'nullable|string|max:50',
            'website'            => 'nullable|string|max:2048',
            'raw_text'           => 'nullable|string',
            'raw_html'           => 'nullable|string',
            'parser_version'     => 'nullable|string|max:20',
            'source'             => 'nullable|string|max:50',
            'opening_hours'      => 'nullable|string',
            'image_1'            => 'nullable|string|max:2048',
            'image_2'            => 'nullable|string|max:2048',
            'image_3'            => 'nullable|string|max:2048',
            'image_4'            => 'nullable|string|max:2048',
            // enrichment fields dari Playwright
            'popular_times'      => 'nullable|array',
            'price_level'        => 'nullable|string|max:10',
            'permanently_closed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            Log::error('❌ [API] Validation failed', [
                'errors' => $validator->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'status' => 'invalid',
                'errors' => $validator->errors(),
            ], 422);
        }

        Log::info('✅ [API] Validation passed, proceeding to save place', [
            'place_id' => $request->place_id,
            'name' => $request->name
        ]);

        try {
            // 2. Place::updateOrCreate()
            $place = Place::updateOrCreate(
                ['place_id' => $request->place_id],
                array_merge($validator->validated(), ['last_scraped_at' => now()])
            );

            Log::info('✅ [API] Place saved/updated', [
                'place_id' => $place->place_id,
                'name' => $place->name,
                'is_new' => $place->wasRecentlyCreated
            ]);

            // 3. Insert scrape_logs
            $scrapeLog = ScrapeLog::create([
                'place_id' => $place->place_id, // Use the Google place_id, not the auto-increment ID
                'status' => 'success',
                'error_message' => null,
                'raw_payload' => json_encode($request->all()),
            ]);

            Log::info('✅ [API] ScrapeLog created', [
                'scrape_log_id' => $scrapeLog->id,
                'place_id' => $scrapeLog->place_id
            ]);

            // 4. Queue scraping
            ScrapePlaceJob::dispatch($place);

            Log::info('✅ [API] ScrapePlaceJob dispatched', [
                'place_id' => $place->place_id,
                'job_queued' => true
            ]);

            // 5. Return success response
            Log::info('✅ [API] Returning success response');
            return response()->json([
                'status' => 'success',
                'message' => 'Place saved successfully',
                'data' => $place,
            ], 201);

        } catch (\Exception $e) {
            Log::error('❌ [API] Exception during place saving', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save place: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function needsRescrape(Request $request)
    {
        $limit = min((int) $request->get('limit', 30), 500);

        $places = Place::whereNotNull('maps_url')
            ->where('maps_url', '!=', '')
            ->where(function ($q) {
                // Missing popular_times → rescrape regardless of last_scraped_at
                $q->whereNull('popular_times')
                  // Missing basic info AND not recently scraped
                  ->orWhere(function ($inner) {
                      $inner->where(function ($d) {
                          $d->whereNull('opening_hours')
                            ->orWhere('opening_hours', '')
                            ->orWhereNull('review_count')
                            ->orWhere('review_count', 0);
                      })->where(function ($d) {
                          $d->whereNull('last_scraped_at')
                            ->orWhere('last_scraped_at', '<', now()->subHours(24));
                      });
                  });
            })
            ->orderByRaw('CASE WHEN popular_times IS NULL THEN 0 ELSE 1 END')
            ->orderByRaw('CASE WHEN opening_hours IS NULL OR opening_hours = "" THEN 0 ELSE 1 END')
            ->orderByRaw('CASE WHEN review_count IS NULL OR review_count = 0 THEN 0 ELSE 1 END')
            ->limit($limit)
            ->get(['id', 'name', 'maps_url', 'place_id', 'opening_hours', 'review_count', 'popular_times']);

        return response()->json([
            'status' => 'success',
            'count'  => $places->count(),
            'data'   => $places,
        ]);
    }

    public function update(Request $request, $id)
    {
        $place = Place::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'               => 'nullable|string|max:255',
            'lat'                => 'nullable|numeric',
            'lng'                => 'nullable|numeric',
            'place_id'           => 'nullable|string|max:255',
            'maps_url'           => 'nullable|string|max:2048',
            'rating'             => 'nullable|numeric|min:0|max:5',
            'review_count'       => 'nullable|integer|min:0',
            'category'           => 'nullable|string|max:255',
            'address'            => 'nullable|string',
            'phone'              => 'nullable|string|max:50',
            'website'            => 'nullable|string|max:2048',
            'opening_hours'      => 'nullable|string',
            'image_1'            => 'nullable|string|max:2048',
            'image_2'            => 'nullable|string|max:2048',
            'image_3'            => 'nullable|string|max:2048',
            'image_4'            => 'nullable|string|max:2048',
            'popular_times'      => 'nullable|array',
            'price_level'        => 'nullable|string|max:10',
            'permanently_closed' => 'nullable|boolean',
            'description'        => 'nullable|string',
            'source'             => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'invalid', 'errors' => $validator->errors()], 422);
        }

        $data = array_filter($validator->validated(), fn($v) => $v !== null && $v !== '' || is_bool($v));
        $data['last_scraped_at'] = now();

        $place->update($data);

        return response()->json(['status' => 'success', 'data' => $place]);
    }

    public function deleteScrapedToday(Request $request)
    {
        try {
            // Get today's date in YYYY-MM-DD format
            $today = now()->toDateString();

            // Delete places created today
            $deletedCount = Place::whereDate('created_at', $today)->delete();

            Log::info('🗑️ [API] Deleted scraped today places', [
                'deleted_count' => $deletedCount,
                'date' => $today
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Places scraped today deleted successfully',
                'deleted_count' => $deletedCount,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [API] Exception during delete scraped today', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete scraped today places: ' . $e->getMessage(),
            ], 500);
        }
    }
}
