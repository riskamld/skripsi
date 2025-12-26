<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $table = 'places';

    protected $fillable = [
        'place_id',
        'name',
        'lat',
        'lng',
        'maps_url',
        'rating',
        'review_count',
        'category',
        'address',
        'phone',
        'website',
        'raw_text',
        'raw_html',
        'parser_version',
        'source',
        'is_valid',
        'last_scraped_at',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'last_scraped_at' => 'datetime',
        'rating' => 'float',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function scrapeLogs()
    {
        return $this->hasMany(ScrapeLog::class, 'place_id', 'place_id');
    }
}
