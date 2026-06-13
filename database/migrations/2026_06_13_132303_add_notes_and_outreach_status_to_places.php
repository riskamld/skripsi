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
        Schema::table('places', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('outreach_sent_at');
            $table->timestamp('notes_updated_at')->nullable()->after('notes');
            // outreach_status values: none | sent | replied | interested | not_interested | ordered
            // Column already exists, just widening accepted values via app logic
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn(['notes', 'notes_updated_at']);
        });
    }
};
