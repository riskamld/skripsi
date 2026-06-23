<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->unsignedTinyInteger('cluster')->nullable()->after('review_count');
            $table->string('cluster_label', 20)->nullable()->after('cluster');
            $table->decimal('cluster_score', 8, 4)->nullable()->after('cluster_label');
            $table->timestamp('cluster_computed_at')->nullable()->after('cluster_score');

            $table->index('cluster');
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropIndex(['cluster']);
            $table->dropColumn(['cluster', 'cluster_label', 'cluster_score', 'cluster_computed_at']);
        });
    }
};
