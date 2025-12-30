<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ScrapeLog;
use Illuminate\Http\Request;

class ScrapeLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ScrapeLog::with('place');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by place name or error message
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('place', function($placeQuery) use ($search) {
                    $placeQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('error_message', 'like', "%{$search}%")
                ->orWhere('raw_payload', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['created_at', 'updated_at', 'status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        // Handle AJAX requests for infinite scroll
        if (request()->ajax()) {
            $logs = $query->paginate(15); // Smaller chunks for infinite scroll
            return response()->json([
                'logs' => $logs->items(),
                'has_more' => $logs->hasMorePages(),
                'next_page' => $logs->hasMorePages() ? $logs->currentPage() + 1 : null
            ]);
        }

        $logs = $query->paginate(20)->onEachSide(2);

        // Get status counts for summary
        $statusCounts = [
            'success' => ScrapeLog::where('status', 'success')->count(),
            'failed' => ScrapeLog::where('status', 'failed')->count(),
            'skipped' => ScrapeLog::where('status', 'skipped')->count(),
        ];

        return view('scrape-logs.index', compact('logs', 'statusCounts'));
    }

    public function show(ScrapeLog $scrapeLog)
    {
        $scrapeLog->load('place');
        return view('scrape-logs.show', compact('scrapeLog'));
    }

    public function destroy(ScrapeLog $scrapeLog)
    {
        $scrapeLog->delete();

        return redirect()->route('scrape-logs.index')
            ->with('success', 'Scrape log deleted successfully');
    }

    public function clearAll(Request $request)
    {
        ScrapeLog::query()->delete();

        return redirect()->route('scrape-logs.index')
            ->with('success', 'All scrape logs cleared successfully');
    }
}
