<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceResponse extends Model
{
    protected $fillable = [
        'place_id',
        'outreach_status',
        'customer_name',
        'notes',
        'skor',
        'tugas_selanjutnya',
        'response_admin',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
