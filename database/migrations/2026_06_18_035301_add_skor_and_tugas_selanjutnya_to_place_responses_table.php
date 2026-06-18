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
        Schema::table('place_responses', function (Blueprint $table) {
            $table->unsignedTinyInteger('skor')->nullable()->after('notes');
            $table->text('tugas_selanjutnya')->nullable()->after('skor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place_responses', function (Blueprint $table) {
            $table->dropColumn(['skor', 'tugas_selanjutnya']);
        });
    }
};
