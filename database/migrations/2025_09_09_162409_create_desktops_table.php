<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desktops', function (Blueprint $table) {
            $table->id();
            $table->string('hostname')->unique();        // Auto-generate WSDPPADPS-0001
            $table->string('serialnumber')->nullable();
            $table->string('model')->nullable();
            $table->string('brand')->nullable();
            $table->string('location')->nullable();
            $table->string('typewindows')->nullable();
            $table->string('user')->nullable();
            $table->string('iprealvnc')->nullable();
            $table->string('osstatus')->nullable();
            $table->enum('status', ['available', 'in_use', 'broken', 'scrap'])
                ->default('available');
            $table->timestamps();
            $table->softDeletes();                       // recycle bin
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desktops');
    }
};
