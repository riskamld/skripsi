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
            // Add indexes for sortable columns to improve performance
            $table->index('name');
            $table->index('rating');
            $table->index('review_count');
            $table->index('created_at');
            $table->index('updated_at');

            // Composite index for common sort combinations
            $table->index(['rating', 'review_count']);
            $table->index(['created_at', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['name']);
            $table->dropIndex(['rating']);
            $table->dropIndex(['review_count']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['rating', 'review_count']);
            $table->dropIndex(['created_at', 'updated_at']);
        });
    }
};
