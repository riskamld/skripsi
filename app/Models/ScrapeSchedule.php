<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapeSchedule extends Model
{
    protected $fillable = [
        'name', 'query', 'area', 'limit', 'frequency',
        'interval_hours', 'run_hour', 'day_of_week',
        'enabled', 'last_run_at', 'last_result',
    ];

    protected $casts = [
        'enabled'     => 'boolean',
        'last_run_at' => 'datetime',
        'last_result' => 'array',
        'limit'       => 'integer',
        'run_hour'    => 'integer',
        'interval_hours' => 'integer',
        'day_of_week' => 'integer',
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
            'daily'       => "Setiap hari jam {$this->run_hour}:00",
            'every_n_hours' => "Setiap {$this->interval_hours} jam",
            'weekly'      => "Setiap " . ($days[$this->day_of_week] ?? '?') . " jam {$this->run_hour}:00",
            default       => $this->frequency,
        };
    }
}
