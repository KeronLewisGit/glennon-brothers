<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frame_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            // In the customer's drawings link, with access_key: never the auto-increment id.
            $table->ulid('public_id')->unique();
            $table->string('access_key', 40);
            $table->string('status', 20)->default('new')->index();
            $table->string('client_type', 20);
            $table->string('project_type', 20);
            $table->string('name', 120);
            $table->string('company', 160)->nullable();
            $table->string('email');
            $table->string('phone', 30);
            $table->string('phone_digits', 20)->nullable()->index();
            $table->string('site_address', 300);
            $table->string('county', 40);
            $table->string('eircode', 8)->nullable();
            $table->string('planning_status', 20);
            $table->char('start_month', 7); // Y-m
            $table->unsignedSmallInteger('floor_area_m2')->nullable();
            $table->unsignedSmallInteger('units')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('quoted_total', 12, 2)->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->string('source_url', 300)->nullable();
            $table->timestamps();
        });

        Schema::create('frame_drawings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('frame_enquiry_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name', 200);
            $table->string('mime', 120)->nullable();
            $table->unsignedInteger('size');
            $table->string('added_by', 20)->default('customer'); // customer | staff
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frame_drawings');
        Schema::dropIfExists('frame_enquiries');
    }
};
