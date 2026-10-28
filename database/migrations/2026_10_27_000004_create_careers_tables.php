<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title', 160);
            $table->string('plant', 20)->index();
            $table->string('category', 20);
            $table->string('employment_type', 20);
            $table->string('status', 20)->default('open')->index();
            $table->string('hours', 160)->nullable();
            $table->unsignedSmallInteger('openings')->default(1);
            $table->text('summary');
            $table->text('duties')->nullable();
            $table->text('requirements')->nullable();
            $table->date('closes_on')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('job_posting_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plant', 20)->nullable()->index(); // null = any plant
            $table->string('category', 20);
            $table->string('status', 20)->default('new')->index();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('phone', 30);
            $table->string('phone_digits', 20)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('home_area', 80)->nullable();
            $table->boolean('right_to_work')->default(false);
            $table->string('experience', 20);
            $table->string('licences', 160)->nullable();
            $table->text('qualifications')->nullable();
            $table->date('available_from')->nullable();
            $table->text('message')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('cv_name', 200)->nullable();
            $table->string('cv_mime', 120)->nullable();
            $table->unsignedInteger('cv_size')->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_postings');
    }
};
