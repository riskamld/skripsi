<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrape_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('query', 100);
            $table->string('area', 100)->nullable();
            $table->unsignedTinyInteger('limit')->default(20);
            $table->enum('frequency', ['daily', 'every_n_hours', 'weekly'])->default('daily');
            $table->unsignedTinyInteger('interval_hours')->nullable();
            $table->unsignedTinyInteger('run_hour')->default(8);
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 1=Mon..7=Sun
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->json('last_result')->nullable(); // {status, processed, error}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_schedules');
    }
};
