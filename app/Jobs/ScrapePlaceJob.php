<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Place;

class ScrapePlaceJob implements ShouldQueue
{
    use Queueable;

    protected $place;

    /**
     * Create a new job instance.
     */
    public function __construct(Place $place)
    {
        $this->place = $place;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Update last scraped timestamp
        $this->place->update(['last_scraped_at' => now()]);

        // TODO: Implement actual scraping logic here
        // e.g., fetch reviews, photos, etc. from Google Maps API
    }
}
