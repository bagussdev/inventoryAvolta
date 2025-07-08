<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['Unit', 'Pcs', 'Box']);
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('items_id')->constrained('items')->onDelete('cascade');
            $table->string('S/N');
            $table->integer('qty');
            $table->string('photo')->nullable();
            $table->string('supplier')->nullable();
            $table->timestamps();
        });

        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('items_id')->constrained('items')->onDelete('cascade');
            $table->integer('qty');
            $table->string('photo')->nullable();
            $table->string('supplier')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('items_id')->constrained('items')->onDelete('cascade');
            $table->string('S/N')->nullable();
            $table->enum('type', ['spareparts', 'equipments']);
            $table->integer('qty');
            $table->string('photoitems')->nullable();
            $table->string('attachmentfile')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->onDelete('cascade');
            $table->enum('frequensi', ['weekly', 'monthly']);
            $table->date('maintenance_date');
            $table->string('picstaff')->nullable();
            $table->enum('status', ['not due', 'maintenance', 'resolved', 'confirm', 'pending'])->default('not due');
            $table->string('attachment')->nullable();
            $table->text('notes')->nullable();
            $table->string('confirmBy')->nullable();
            $table->timestamps();
        });
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('pic_user');
            $table->foreignId('item_problem')->constrained('equipments')->onDelete('cascade');
            $table->string('department_to');
            $table->enum('status', ['waiting', 'in-progress', 'pending', 'resolved', 'completed'])->default('waiting');
            $table->string('location');
            $table->text('message_user')->nullable();
            $table->string('attachmentUser')->nullable();
            $table->string('pic_staff')->nullable();
            $table->text('message_staff')->nullable();
            $table->string('attachment_staff')->nullable();
            $table->string('resolvedBy')->nullable();
            $table->string('confirmBy')->nullable();
            $table->timestamps();
        });
        Schema::create('used_spareparts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spareparts_id')->constrained('spareparts')->onDelete('cascade');
            $table->foreignId('maintenance_id')->nullable()->constrained('maintenances')->onDelete('cascade');
            $table->foreignId('incident_id')->nullable()->constrained('incidents')->onDelete('cascade');
            $table->integer('qty');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('pic_user');
            $table->string('department_to');
            $table->text('item_request');
            $table->enum('status', ['waiting', 'completed', 'pending'])->default('waiting');
            $table->foreignId('store_id')->constrained('store')->onDelete('cascade');
            $table->text('message_user')->nullable();
            $table->string('pic_staff')->nullable();
            $table->text('message_staff')->nullable();
            $table->string('attachmentStaff')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('used_spareparts');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('spareparts');
        Schema::dropIfExists('equipments');
        Schema::dropIfExists('items');
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('requests');
        Schema::dropIfExists('incidents');
    }
};
