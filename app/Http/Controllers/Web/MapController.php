<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MapController extends Controller
{
    public function index()
    {
        // Fetch all places with valid coordinates
        $places = Place::whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0)
            ->orderBy('created_at', 'desc')
            ->get(); // Show all places with coordinates

        return view('map.index', compact('places'));
    }

    public function checkUpdates(Request $request)
    {
        $lastUpdate = $request->get('last_update', null);
        $clientPlaceIds = $request->get('place_ids', []); // Array of place IDs client currently has

        // Get places that have been updated since last check
        $query = Place::whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0);

        if ($lastUpdate) {
            $query->where('updated_at', '>', Carbon::parse($lastUpdate));
        }

        $updatedPlaces = $query->orderBy('updated_at', 'desc')->get();

        // Find places that have been deleted (exist in client but not in current DB)
        $currentPlaceIds = Place::whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0)
            ->pluck('id')
            ->toArray();

        $deletedPlaceIds = array_diff($clientPlaceIds, $currentPlaceIds);

        // Get latest update timestamp
        $latestUpdate = Place::whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0)
            ->max('updated_at');

        return response()->json([
            'updated_places' => $updatedPlaces,
            'deleted_place_ids' => array_values($deletedPlaceIds),
            'last_update' => $latestUpdate,
            'has_changes' => count($updatedPlaces) > 0 || count($deletedPlaceIds) > 0
        ]);
    }

    public function deleteCategory(Request $request)
    {
        $category = $request->get('category');

        if (!$category) {
            return response()->json(['error' => 'Category is required'], 400);
        }

        // Delete all places with this category
        $deletedCount = Place::where('category', $category)->delete();

        return response()->json([
            'success' => true,
            'deleted_count' => $deletedCount,
            'message' => "Deleted {$deletedCount} places with category '{$category}'"
        ]);
    }
}
