<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapeLog extends Model
{
    protected $table = 'scrape_logs';

    protected $fillable = [
        'place_id',
        'status',
        'error_message',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id', 'place_id');
    }
}
