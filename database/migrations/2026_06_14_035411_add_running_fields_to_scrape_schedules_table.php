<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scrape_schedules', function (Blueprint $table) {
            $table->boolean('is_running')->default(false)->after('enabled');
            $table->string('current_log_file')->nullable()->after('is_running');
        });
    }

    public function down(): void
    {
        Schema::table('scrape_schedules', function (Blueprint $table) {
            $table->dropColumn(['is_running', 'current_log_file']);
        });
    }
};
