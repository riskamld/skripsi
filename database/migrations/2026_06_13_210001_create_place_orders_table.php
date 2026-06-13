<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('place_id');
            $table->string('item', 100);
            $table->decimal('qty', 10, 2);
            $table->string('unit', 20)->default('kg');
            $table->decimal('total_rp', 14, 2);
            $table->date('order_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('place_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_orders');
    }
};
