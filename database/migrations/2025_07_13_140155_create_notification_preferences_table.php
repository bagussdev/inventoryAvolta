<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationPreferencesTable extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type'); // contoh: create_item, edit_item, delete_item
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['notification_type', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
}
