<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapeSchedule extends Model
{
    protected $fillable = [
        'name', 'query', 'area', 'lat', 'lng', 'zoom', 'limit', 'frequency',
        'interval_hours', 'run_hour', 'day_of_week',
        'enabled', 'is_running', 'current_log_file',
        'last_run_at', 'last_result',
    ];

    protected $casts = [
        'enabled'        => 'boolean',
        'is_running'     => 'boolean',
        'last_run_at'    => 'datetime',
        'last_result'    => 'array',
        'limit'          => 'integer',
        'run_hour'       => 'integer',
        'interval_hours' => 'integer',
        'day_of_week'    => 'integer',
        'lat'            => 'float',
        'lng'            => 'float',
        'zoom'           => 'integer',
    ];

    public function shouldRunNow(): bool
    {
        $now  = now();
        $last = $this->last_run_at;

        switch ($this->frequency) {
            case 'every_n_hours':
                $hours = $this->interval_hours ?: 6;
                return !$last || $last->addHours($hours)->lte($now);

            case 'daily':
                $todaySlot = $now->copy()->setHour($this->run_hour)->setMinute(0)->setSecond(0);
                return $now->gte($todaySlot) && (!$last || $last->lt($todaySlot));

            case 'weekly':
                if ($now->dayOfWeekIso != $this->day_of_week) return false;
                $thisSlot = $now->copy()->setHour($this->run_hour)->setMinute(0)->setSecond(0);
                return $now->gte($thisSlot) && (!$last || $last->lt($thisSlot));
        }

        return false;
    }

    public function frequencyLabel(): string
    {
        $days = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        return match ($this->frequency) {
            'daily'         => "Setiap hari jam {$this->run_hour}:00",
            'every_n_hours' => "Setiap {$this->interval_hours} jam",
            'weekly'        => "Setiap " . ($days[$this->day_of_week] ?? '?') . " jam {$this->run_hour}:00",
            default         => $this->frequency,
        };
    }

    public function isKecamatanLevel(): bool
    {
        return ($this->zoom ?? 13) >= 15;
    }

    public function radiusKm(): int
    {
        $map = [17 => 2, 16 => 3, 15 => 5, 14 => 15, 13 => 40, 12 => 60, 11 => 80, 10 => 150];
        return $map[$this->zoom ?? 13] ?? 40;
    }

    public function methodBadge(): string
    {
        return $this->isKecamatanLevel() ? 'Kecamatan' : 'Kota';
    }
}
