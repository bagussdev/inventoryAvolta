<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('store_location')->nullable()->change();
            $table->foreign('store_location')
                ->references('id')
                ->on('store')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['store_location']);
            $table->string('store_location', 255)->nullable()->change(); // Kembalikan ke varchar jika sebelumnya varchar
        });
    }
};
