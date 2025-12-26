<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapeLog extends Model
{
    protected $table = 'scrape_logs';

    protected $fillable = [
        'source',
        'query',
        'total_found',
        'total_saved',
        'status',
        'message',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
