<?php

namespace App\Services;

use App\Models\PlaceOrder;
use App\Models\OutreachLog;
use App\Models\Place;
use App\Models\TelegramSetting;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    private ?TelegramSetting $cfg;

    public function __construct()
    {
        $this->cfg = TelegramSetting::first();
    }

    public function isEnabled(string $notifKey = null): bool
    {
        if (!$this->cfg || !$this->cfg->enabled || !$this->cfg->bot_token || !$this->cfg->chat_id) {
            return false;
        }
        if ($notifKey && !($this->cfg->$notifKey ?? false)) {
            return false;
        }
        return true;
    }

    public function send(string $text): bool
    {
        if (!$this->cfg?->bot_token || !$this->cfg?->chat_id) return false;
        try {
            $resp = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$this->cfg->bot_token}/sendMessage",
                ['chat_id' => $this->cfg->chat_id, 'text' => $text, 'parse_mode' => 'HTML']
            );
            return $resp->successful();
        } catch (\Exception) {
            return false;
        }
    }

    public function testSend(): bool
    {
        if (!$this->cfg?->bot_token || !$this->cfg?->chat_id) return false;
        return $this->send("✅ <b>Mafaza Fortuna</b> — Koneksi Telegram berhasil!\nBot siap mengirim notifikasi.");
    }

    public function notifyScrapeDone(string $query, string $area, int $processed, int $totalDb): void
    {
        if (!$this->isEnabled('notif_scrape_done')) return;
        $area = $area ? " — {$area}" : '';
        $this->send("✅ <b>Scraping Selesai</b>\n🔍 {$query}{$area}\n📦 Diproses: <b>{$processed}</b> tempat\n🗄 Total DB: <b>{$totalDb}</b>");
    }

    public function notifyScraperError(string $query, string $error = ''): void
    {
        if (!$this->isEnabled('notif_scraper_error')) return;
        $this->send("❌ <b>Scraping Gagal</b>\n🔍 Query: {$query}\n⚠️ {$error}");
    }

    public function notifyWaChecked(int $valid, int $noWa, int $total): void
    {
        if (!$this->isEnabled('notif_wa_checked')) return;
        $this->send("📱 <b>Cek WA Selesai</b>\n✅ Punya WA: <b>{$valid}</b>\n❌ Tidak ada: <b>{$noWa}</b>\n📊 Total dicek: {$total}");
    }

    public function notifyOutreachSent(int $sent, int $failed, string $template, int $sentToday, int $dailyLimit): void
    {
        if (!$this->isEnabled('notif_outreach_sent')) return;
        $this->send("📤 <b>Pesan WA Terkirim</b>\n✅ Berhasil: <b>{$sent}</b>  ❌ Gagal: {$failed}\n📋 Template: {$template}\n📊 Hari ini: {$sentToday}/{$dailyLimit}");
    }

    public function notifyDailyLimit(int $limit): void
    {
        if (!$this->isEnabled('notif_daily_limit')) return;
        $this->send("⚠️ <b>Limit Harian WA Tercapai</b>\nSudah mengirim <b>{$limit}</b> pesan hari ini. Outreach bisa dilanjutkan besok.");
    }

    public function notifyInterested(string $name, string $phone): void
    {
        if (!$this->isEnabled('notif_interested')) return;
        $this->send("🎯 <b>Ada yang Tertarik!</b>\n🏪 {$name}\n📞 {$phone}");
    }

    public function notifyNewOrder(string $name, string $item, float $totalRp): void
    {
        if (!$this->isEnabled('notif_new_order')) return;
        $rp = 'Rp ' . number_format($totalRp, 0, ',', '.');
        $this->send("🛒 <b>Order Baru!</b>\n🏪 {$name}\n📦 {$item}\n💰 {$rp}");
    }

    public function notifyDuplicatesFound(int $count): void
    {
        if (!$this->isEnabled('notif_duplicates')) return;
        $this->send("🔍 <b>Duplikat Terdeteksi</b>\nDitemukan <b>{$count}</b> nomor telepon duplikat di database.");
    }

    public function notifyScheduledStart(string $name, string $query): void
    {
        if (!$this->isEnabled('notif_scrape_done')) return;
        $this->send("⏰ <b>Jadwal Scraping Dimulai</b>\n📌 Jadwal: <b>{$name}</b>\n🔍 Query: {$query}");
    }

    public function sendDailySummary(): void
    {
        if (!$this->isEnabled('notif_daily_summary')) return;

        $total      = Place::count();
        $targets    = Place::where('has_whatsapp', true)->count();
        $sent       = Place::where('outreach_status', 'sent')->count();
        $replied    = Place::where('outreach_status', 'replied')->count();
        $interested = Place::where('outreach_status', 'interested')->count();
        $ordered    = Place::where('outreach_status', 'ordered')->count();
        $orderVal   = PlaceOrder::sum('total_rp');
        $sentToday  = OutreachLog::where('action', 'sent')->whereDate('created_at', today())->count();

        $rp   = 'Rp ' . number_format($orderVal, 0, ',', '.');
        $date = now()->format('d/m/Y');

        $this->send(
            "📊 <b>Ringkasan Harian — {$date}</b>\n\n" .
            "🗄 Total tempat: <b>{$total}</b>\n" .
            "📱 Punya WA: <b>{$targets}</b>\n\n" .
            "📤 Terkirim total: <b>{$sent}</b>\n" .
            "💬 Dibalas: <b>{$replied}</b>\n" .
            "🎯 Tertarik: <b>{$interested}</b>\n" .
            "🛒 Order: <b>{$ordered}</b>\n\n" .
            "📬 Terkirim hari ini: <b>{$sentToday}</b>\n" .
            "💰 Total nilai order: <b>{$rp}</b>"
        );

        if ($this->cfg) {
            $this->cfg->update(['last_summary_date' => today()]);
        }
    }
}
