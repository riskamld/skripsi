<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_settings', function (Blueprint $table) {
            $table->id();
            $table->text('bot_token')->nullable();
            $table->string('chat_id', 50)->nullable();
            $table->boolean('enabled')->default(false);
            $table->boolean('notif_scrape_done')->default(true);
            $table->boolean('notif_scraper_error')->default(true);
            $table->boolean('notif_wa_checked')->default(true);
            $table->boolean('notif_outreach_sent')->default(true);
            $table->boolean('notif_daily_limit')->default(true);
            $table->boolean('notif_daily_summary')->default(true);
            $table->boolean('notif_interested')->default(true);
            $table->boolean('notif_new_order')->default(true);
            $table->boolean('notif_duplicates')->default(false);
            $table->string('daily_summary_time', 5)->default('07:00');
            $table->date('last_summary_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_settings');
    }
};
