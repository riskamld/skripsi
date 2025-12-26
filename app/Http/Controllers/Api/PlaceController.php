<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            'website' => 'nullable|string|max:255',
            'raw_text' => 'nullable|string',
            'raw_html' => 'nullable|string',
            'parser_version' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'invalid',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 2. Place::updateOrCreate()
        $place = Place::updateOrCreate(
            ['place_id' => $request->place_id],
            $request->all()
        );

        // 3. Insert scrape_logs
        ScrapeLog::create([
            'place_id' => $place->place_id, // Use the Google place_id, not the auto-increment ID
            'status' => 'success',
            'error_message' => null,
            'raw_payload' => json_encode($request->all()),
        ]);

        // 4. Queue scraping
        ScrapePlaceJob::dispatch($place);

        // 5. Return success response
        return response()->json([
            'status' => 'success',
            'message' => 'Place saved successfully',
            'data' => $place,
        ], 201);
    }
}
