<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Place;

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
}
