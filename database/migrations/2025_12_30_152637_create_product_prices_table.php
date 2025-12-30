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
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();

            // Product information
            $table->string('product_name');
            $table->string('product_category')->nullable();
            $table->string('unit')->default('pcs'); // pcs, kg, liter, etc.

            // Price data
            $table->decimal('price', 12, 2);
            $table->decimal('original_price', 12, 2)->nullable(); // If there's a discount

            // Location data
            $table->unsignedBigInteger('place_id');
            $table->foreign('place_id')->references('id')->on('places')->onDelete('cascade');

            // Geographic coordinates (for location-based analysis)
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();

            // Source and confidence
            $table->enum('source', ['manual', 'scraped', 'estimated', 'api'])->default('manual');
            $table->decimal('confidence_score', 3, 2)->default(1.00); // 0.00 to 1.00

            // Market factors
            $table->decimal('supply_index', 5, 2)->nullable(); // 0-100 scale
            $table->decimal('demand_index', 5, 2)->nullable(); // 0-100 scale

            // Seasonal factors
            $table->string('season')->nullable(); // 'peak', 'normal', 'low'
            $table->boolean('is_holiday_season')->default(false);

            // Additional metadata
            $table->json('metadata')->nullable(); // Store additional data like brand, quality, etc.
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamp('recorded_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['product_name', 'recorded_at']);
            $table->index(['place_id', 'recorded_at']);
            $table->index(['lat', 'lng']);
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
