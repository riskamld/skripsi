<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_incoming_messages', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 50);
            $table->string('from_number', 30);
            $table->text('message')->nullable();
            $table->unsignedBigInteger('place_id')->nullable();
            $table->boolean('is_prospect')->default(false);
            $table->string('action_taken', 30)->nullable(); // status_updated / noted / none
            $table->timestamp('received_at');
            $table->timestamps();
            $table->index('from_number');
            $table->index(['is_prospect','received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_incoming_messages');
    }
};
