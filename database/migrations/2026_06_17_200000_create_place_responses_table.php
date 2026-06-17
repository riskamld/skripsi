<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('place_id');
            $table->foreign('place_id')->references('id')->on('places')->onDelete('cascade');
            $table->string('outreach_status', 20)->nullable();
            $table->string('customer_name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('response_admin', 80)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['place_id', 'responded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_responses');
    }
};
