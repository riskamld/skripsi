<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('places')) {
            return;
        }

        Schema::create('places', function (Blueprint $table) {
            $table->id();

            $table->string('place_id')->unique();
            $table->string('name')->nullable();

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->text('maps_url')->nullable();

            $table->decimal('rating', 2, 1)->nullable();
            $table->integer('review_count')->nullable();

            $table->string('category')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website')->nullable();

            $table->mediumText('raw_text')->nullable();
            $table->mediumText('raw_html')->nullable();

            $table->string('parser_version', 20)->nullable();
            $table->string('source', 50)->default('google_maps');

            $table->boolean('is_valid')->default(true);
            $table->timestamp('last_scraped_at')->nullable();

            $table->timestamps();

            $table->index('lat');
            $table->index('rating');
            $table->index('review_count');
            $table->index('category');
        });
    }

    public function down(): void
    {
        // mirror migration → do nothing
    }
};
