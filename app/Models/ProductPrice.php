<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    protected $fillable = [
        'product_name',
        'product_category',
        'unit',
        'price',
        'original_price',
        'place_id',
        'lat',
        'lng',
        'source',
        'confidence_score',
        'supply_index',
        'demand_index',
        'season',
        'is_holiday_season',
        'metadata',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'confidence_score' => 'decimal:2',
        'supply_index' => 'decimal:2',
        'demand_index' => 'decimal:2',
        'is_holiday_season' => 'boolean',
        'metadata' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    // Scope for recent prices
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }

    // Scope for specific product
    public function scopeProduct($query, $productName)
    {
        return $query->where('product_name', $productName);
    }

    // Scope for location-based queries
    public function scopeNearLocation($query, $lat, $lng, $radiusKm = 10)
    {
        // Haversine formula for location filtering
        $earthRadius = 6371; // km

        return $query->whereRaw("
            ({$earthRadius} * acos(
                cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) +
                sin(radians(?)) * sin(radians(lat))
            )) <= ?
        ", [$lat, $lng, $lat, $radiusKm]);
    }
}
