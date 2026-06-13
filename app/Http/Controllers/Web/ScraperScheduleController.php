<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ScrapeSchedule;
use Illuminate\Http\Request;

class ScraperScheduleController extends Controller
{
    public function index()
    {
        $schedules = ScrapeSchedule::orderBy('enabled', 'desc')->orderBy('name')->get();
        return view('scraper.schedules', compact('schedules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'query'          => 'required|string|max:100',
            'area'           => 'nullable|string|max:100',
            'limit'          => 'required|integer|min:1|max:100',
            'frequency'      => 'required|in:daily,every_n_hours,weekly',
            'interval_hours' => 'nullable|integer|min:1|max:168',
            'run_hour'       => 'required|integer|min:0|max:23',
            'day_of_week'    => 'nullable|integer|min:1|max:7',
        ]);

        ScrapeSchedule::create($data + ['enabled' => true]);

        return response()->json(['status' => 'ok']);
    }

    public function update(Request $request, ScrapeSchedule $scrapeSchedule)
    {
        $data = $request->validate([
            'name'           => 'sometimes|string|max:100',
            'query'          => 'sometimes|string|max:100',
            'area'           => 'nullable|string|max:100',
            'limit'          => 'sometimes|integer|min:1|max:100',
            'frequency'      => 'sometimes|in:daily,every_n_hours,weekly',
            'interval_hours' => 'nullable|integer|min:1|max:168',
            'run_hour'       => 'sometimes|integer|min:0|max:23',
            'day_of_week'    => 'nullable|integer|min:1|max:7',
            'enabled'        => 'sometimes|boolean',
        ]);

        $scrapeSchedule->update($data);
        return response()->json(['status' => 'ok']);
    }

    public function destroy(ScrapeSchedule $scrapeSchedule)
    {
        $scrapeSchedule->delete();
        return response()->json(['status' => 'ok']);
    }

    public function toggle(ScrapeSchedule $scrapeSchedule)
    {
        $scrapeSchedule->update(['enabled' => !$scrapeSchedule->enabled]);
        return response()->json(['status' => 'ok', 'enabled' => $scrapeSchedule->enabled]);
    }
}
