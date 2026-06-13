<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceOrder extends Model
{
    protected $fillable = ['place_id', 'item', 'qty', 'unit', 'total_rp', 'order_date', 'notes'];

    protected $casts = [
        'order_date' => 'date',
        'total_rp'   => 'float',
        'qty'        => 'float',
    ];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
