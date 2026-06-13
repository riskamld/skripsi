<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaIncomingMessage extends Model
{
    protected $fillable = [
        'device_id', 'from_number', 'message',
        'place_id', 'is_prospect', 'action_taken', 'received_at',
    ];

    protected $casts = [
        'is_prospect' => 'boolean',
        'received_at' => 'datetime',
    ];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
