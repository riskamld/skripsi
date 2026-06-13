<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('wa_templates')->insert([
            [
                'name'       => 'Perkenalan Singkat',
                'body'       => "Halo {nama} 👋\n\nSaya dari *Mafaza Fortuna*, supplier buah segar lokal & impor.\n\nKami menyediakan berbagai jenis buah berkualitas dengan harga grosir yang kompetitif, cocok untuk toko buah, warung, atau usaha kuliner.\n\nBoleh saya share daftar harga & produk terbaru kami?",
                'is_active'  => 1,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Tawaran Harga Grosir',
                'body'       => "Assalamu'alaikum {nama} 🌟\n\nPerkenalkan, kami *Mafaza Fortuna* — distributor buah segar untuk area Jawa Timur.\n\n🍎 Buah lokal & impor pilihan\n💰 Harga grosir langsung dari kebun\n🚚 Pengiriman ke lokasi Anda\n\nAda kebutuhan buah untuk usaha Anda? Kami siap bantu!",
                'is_active'  => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Casual & Singkat',
                'body'       => "Halo kak {nama} 😊\n\nMau tanya, apakah {nama} sering butuh pasokan buah segar untuk usahanya?\n\nKami supplier buah dengan harga bersaing. Kalau tertarik boleh chat sini ya, gratis konsultasi 🙏",
                'is_active'  => 1,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_templates');
    }
};
