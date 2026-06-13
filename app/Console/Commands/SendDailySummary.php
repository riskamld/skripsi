<?php

namespace App\Console\Commands;

use App\Models\TelegramSetting;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class SendDailySummary extends Command
{
    protected $signature = 'telegram:daily-summary';
    protected $description = 'Kirim ringkasan harian ke Telegram';

    public function handle(TelegramService $telegram): void
    {
        $cfg = TelegramSetting::first();
        if (!$cfg || !$cfg->enabled || !$cfg->notif_daily_summary) return;

        [$h] = explode(':', $cfg->daily_summary_time ?? '07:00');
        if ((int) $h !== now()->hour) return;

        if ($cfg->last_summary_date && $cfg->last_summary_date->isToday()) return;

        $telegram->sendDailySummary();
        $this->info('Ringkasan harian terkirim.');
    }
}
