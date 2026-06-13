<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->boolean('has_whatsapp')->nullable()->after('phone');
            $table->timestamp('wa_checked_at')->nullable()->after('has_whatsapp');
            $table->boolean('is_target')->default(false)->after('wa_checked_at');
            $table->decimal('busyness_score', 8, 2)->nullable()->after('is_target');
            $table->json('popular_times')->nullable()->after('busyness_score');
            $table->string('price_level', 10)->nullable()->after('popular_times');
            $table->boolean('permanently_closed')->default(false)->after('price_level');

            $table->index('has_whatsapp');
            $table->index('is_target');
            $table->index('busyness_score');
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn([
                'has_whatsapp', 'wa_checked_at', 'is_target',
                'busyness_score', 'popular_times', 'price_level', 'permanently_closed',
            ]);
        });
    }
};
