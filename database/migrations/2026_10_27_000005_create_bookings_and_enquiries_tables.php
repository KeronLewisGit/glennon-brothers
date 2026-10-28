<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('status', 20)->default('requested')->index();
            $table->string('plant', 20)->index();
            $table->string('purpose', 20);
            $table->date('slot_date')->index();
            $table->string('slot', 4);
            $table->string('haulier', 160);
            $table->string('driver_name', 120)->nullable();
            $table->string('email');
            $table->string('phone', 30);
            $table->string('vehicle_reg', 16);
            $table->string('vehicle_type', 20);
            $table->string('order_reference', 60)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->string('source_url', 300)->nullable();
            $table->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('status', 20)->default('new')->index();
            $table->string('topic', 20);
            $table->string('plant', 20)->nullable();
            $table->string('name', 120);
            $table->string('organisation', 160)->nullable();
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->text('message');
            $table->string('source_url', 300)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('delivery_bookings');
    }
};
