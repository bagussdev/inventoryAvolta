<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Siapa target notifikasinya
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained('store')->onDelete('cascade');

            // Siapa yang memicu notifikasi
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');

            // Isi notifikasi
            $table->string('type'); // contoh: incident, request, maintenance, transaction
            $table->string('title');
            $table->text('message');

            // Referensi ke entitas terkait (optional)
            $table->string('reference_type')->nullable(); // contoh: incidents, requests, etc.
            $table->unsignedBigInteger('reference_id')->nullable();

            // Status baca
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
}
