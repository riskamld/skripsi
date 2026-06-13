<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramSetting extends Model
{
    protected $fillable = [
        'bot_token', 'chat_id', 'enabled',
        'notif_scrape_done', 'notif_scraper_error', 'notif_wa_checked',
        'notif_outreach_sent', 'notif_daily_limit', 'notif_daily_summary',
        'notif_interested', 'notif_incoming_message', 'notif_new_order', 'notif_duplicates',
        'daily_summary_time', 'last_summary_date',
    ];

    protected $casts = [
        'enabled'              => 'boolean',
        'notif_scrape_done'    => 'boolean',
        'notif_scraper_error'  => 'boolean',
        'notif_wa_checked'     => 'boolean',
        'notif_outreach_sent'  => 'boolean',
        'notif_daily_limit'    => 'boolean',
        'notif_daily_summary'  => 'boolean',
        'notif_interested'        => 'boolean',
        'notif_incoming_message'  => 'boolean',
        'notif_new_order'         => 'boolean',
        'notif_duplicates'     => 'boolean',
        'last_summary_date'    => 'date',
    ];

    public static function get(): self
    {
        return static::firstOrCreate([], ['daily_summary_time' => '07:00']);
    }
}
