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
        'notes',
        'notes_updated_at',
        'customer_name',
        'response_admin',
        'responded_at',
    ];

    protected $casts = [
        'is_valid'          => 'boolean',
        'has_whatsapp'      => 'boolean',
        'is_target'         => 'boolean',
        'permanently_closed'=> 'boolean',
        'last_scraped_at'   => 'datetime',
        'wa_checked_at'     => 'datetime',
        'notes_updated_at'  => 'datetime',
        'responded_at'      => 'datetime',
        'outreach_sent_at'  => 'datetime',
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

    public function responses()
    {
        return $this->hasMany(PlaceResponse::class)->orderByDesc('responded_at');
    }

    public function scrapeLogs()
    {
        return $this->hasMany(ScrapeLog::class, 'place_id', 'place_id');
    }

    public function orders()
    {
        return $this->hasMany(PlaceOrder::class);
    }

    // Nilai puncak per hari dari popular_times → [day => maxValue]
    public function popularTimePeaks(): array
    {
        if (!$this->popular_times) return [];
        $peaks = [];
        foreach ($this->popular_times as $day => $hours) {
            $peaks[$day] = max($hours);
        }
        return $peaks;
    }

    // Hari & jam paling ramai: ['day'=>'sat','hour'=>14,'value'=>90]
    public function busiestSlot(): ?array
    {
        if (!$this->popular_times) return null;
        $best = null;
        foreach ($this->popular_times as $day => $hours) {
            $max = max($hours);
            $hr  = array_search($max, $hours);
            if (!$best || $max > $best['value']) {
                $best = ['day' => $day, 'hour' => $hr, 'value' => $max];
            }
        }
        return $best;
    }

    // Kecamatan diparsing dari kolom address, mis. "Kec. Tanggul" → "Tanggul".
    // Ambil kemunculan TERAKHIR — alamat Google Maps kadang mengandung teks
    // landmark/nama tempat yang menyisipkan "Kec. X" duplikat di tengah,
    // sementara penanda yang valid selalu paling dekat ke nama provinsi di akhir.
    public function kecamatan(): ?string
    {
        if ($this->address && preg_match_all('/Kec(?:amatan)?\.\s*([^,]+)/iu', $this->address, $m)) {
            return trim(end($m[1]));
        }
        return null;
    }

    // Kabupaten/Kota diparsing dari kolom address, mis. "Kabupaten Jember" → "Kabupaten Jember"
    public function kabupaten(): ?string
    {
        if ($this->address && preg_match_all('/Kabupaten\s+([^,]+)/iu', $this->address, $m)) {
            return 'Kabupaten ' . trim(end($m[1]));
        }
        if ($this->address && preg_match_all('/Kota\s+([^,]+)/iu', $this->address, $m)) {
            return 'Kota ' . trim(end($m[1]));
        }
        return null;
    }

    // Gabungan "Kecamatan — Kabupaten" untuk grouping/tampilan ringkas
    public function area(): string
    {
        $kec = $this->kecamatan();
        $kab = $this->kabupaten();
        if ($kec && $kab) return $kec . ' — ' . $kab;
        if ($kec) return $kec;
        if ($kab) return $kab;
        return 'Area tidak diketahui';
    }
}
