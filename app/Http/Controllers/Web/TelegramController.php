<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TelegramSetting;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function index()
    {
        $cfg = TelegramSetting::get();
        return view('telegram.index', compact('cfg'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'bot_token'          => 'nullable|string|max:500',
            'chat_id'            => 'nullable|string|max:50',
            'daily_summary_time' => 'required|string|regex:/^\d{2}:\d{2}$/',
        ]);

        $cfg = TelegramSetting::get();
        $cfg->update([
            'bot_token'           => $request->bot_token,
            'chat_id'             => $request->chat_id,
            'enabled'             => $request->boolean('enabled'),
            'daily_summary_time'  => $request->daily_summary_time,
            'notif_scrape_done'   => $request->boolean('notif_scrape_done'),
            'notif_scraper_error' => $request->boolean('notif_scraper_error'),
            'notif_wa_checked'    => $request->boolean('notif_wa_checked'),
            'notif_outreach_sent' => $request->boolean('notif_outreach_sent'),
            'notif_daily_limit'   => $request->boolean('notif_daily_limit'),
            'notif_daily_summary' => $request->boolean('notif_daily_summary'),
            'notif_interested'    => $request->boolean('notif_interested'),
            'notif_new_order'     => $request->boolean('notif_new_order'),
            'notif_duplicates'    => $request->boolean('notif_duplicates'),
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function test()
    {
        $ok = app(TelegramService::class)->testSend();
        return response()->json([
            'status' => $ok ? 'ok' : 'error',
            'message' => $ok ? 'Pesan uji berhasil dikirim.' : 'Gagal. Periksa Bot Token dan Chat ID.',
        ]);
    }
}
