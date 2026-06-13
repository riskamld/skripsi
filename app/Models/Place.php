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
        'opening_hours',
        'image_1',
        'image_2',
        'image_3',
        'image_4',
        // enrichment
        'has_whatsapp',
        'wa_checked_at',
        'is_target',
        'busyness_score',
        'popular_times',
        'price_level',
        'permanently_closed',
        'description',
        'outreach_status',
        'outreach_sent_at',
        'outreach_device_id',
    ];

    protected $casts = [
        'is_valid'          => 'boolean',
        'has_whatsapp'      => 'boolean',
        'is_target'         => 'boolean',
        'permanently_closed'=> 'boolean',
        'last_scraped_at'   => 'datetime',
        'wa_checked_at'     => 'datetime',
        'rating'            => 'float',
        'busyness_score'    => 'float',
        'lat'               => 'float',
        'lng'               => 'float',
        'popular_times'     => 'array',
    ];

    public function scopeTargets($query)
    {
        return $query->where('is_target', true)->where('has_whatsapp', true);
    }

    public function scopeHasPhone($query)
    {
        return $query->whereNotNull('phone')->where('phone', '!=', '');
    }

    public function scrapeLogs()
    {
        return $this->hasMany(ScrapeLog::class, 'place_id', 'place_id');
    }
}
