<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laptops', function (Blueprint $table) {
            $table->id();
            $table->string('hostname')->unique();        // Auto-generate WSMPPADPS-0001
            $table->string('serialnumber')->nullable();  // Nomor seri unik hardware
            $table->string('model')->nullable();         // Model laptop (Thinkpad X1, dll)
            $table->string('brand')->nullable();         // Merk (Dell, Asus, Lenovo, dll)
            $table->string('location')->nullable();      // Lokasi penyimpanan/penempatan
            $table->string('typewindows')->nullable();   // Windows type (Win10 Pro, Win11 Home)
            $table->string('user')->nullable();          // Nama user pemakai
            $table->string('iprealvnc')->nullable();     // IP RealVNC untuk remote
            $table->string('osstatus')->nullable();      // Status OS (support update / tidak)
            $table->enum('status', ['available', 'in_use', 'broken', 'scrap'])
                ->default('available');                // Kondisi laptop
            $table->timestamps();                        // created_at & updated_at
            $table->softDeletes();                       // recycle bin
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laptops');
    }
};
