<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->string('customer_name', 100)->nullable()->after('notes');
            $table->string('response_admin', 80)->nullable()->after('customer_name');
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'response_admin']);
        });
    }
};
