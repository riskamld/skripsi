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
            'place_id' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'maps_url' => 'nullable|url',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'category' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'raw_text' => 'nullable|string',
            'raw_html' => 'nullable|string',
            'parser_version' => 'nullable|string|max:20',
            'opening_hours' => 'nullable|string',
            'image_1' => 'nullable|url',
            'image_2' => 'nullable|url',
            'image_3' => 'nullable|url',
            'image_4' => 'nullable|url',
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
                $request->all()
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
