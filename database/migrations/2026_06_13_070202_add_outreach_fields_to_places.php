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
            $table->string('outreach_status', 20)->nullable()->after('permanently_closed'); // null|sent|responded|not_interested
            $table->timestamp('outreach_sent_at')->nullable()->after('outreach_status');
            $table->string('outreach_device_id', 50)->nullable()->after('outreach_sent_at');
            $table->index('outreach_status');
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropIndex(['outreach_status']);
            $table->dropColumn(['outreach_status', 'outreach_sent_at', 'outreach_device_id']);
        });
    }
};
