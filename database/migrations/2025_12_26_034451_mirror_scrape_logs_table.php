<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('scrape_logs')) {
            return;
        }

        Schema::create('scrape_logs', function (Blueprint $table) {
            $table->id();

            $table->string('place_id')->nullable()->index();

            $table->enum('status', ['success', 'failed', 'skipped']);
            $table->text('error_message')->nullable();
            $table->mediumText('raw_payload')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        // mirror migration → do nothing
    }
};
