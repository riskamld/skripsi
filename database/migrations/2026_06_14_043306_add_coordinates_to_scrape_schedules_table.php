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
            $table->decimal('lat', 10, 6)->nullable()->after('area');
            $table->decimal('lng', 10, 6)->nullable()->after('lat');
            $table->unsignedTinyInteger('zoom')->default(13)->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('scrape_schedules', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'zoom']);
        });
    }
};
