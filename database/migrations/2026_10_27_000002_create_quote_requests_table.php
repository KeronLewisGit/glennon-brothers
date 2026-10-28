<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('status', 20)->default('new')->index();
            $table->string('customer_type', 20);
            $table->string('company', 160);
            $table->string('account_number', 40)->nullable();
            $table->string('contact_name', 120);
            $table->string('email');
            $table->string('phone', 30);
            $table->string('phone_digits', 20)->nullable()->index();
            $table->string('country', 3);
            $table->string('region', 10)->index();
            $table->char('currency', 3);
            $table->string('delivery_address', 300);
            $table->string('postcode', 12)->nullable();
            $table->date('required_by')->nullable();
            $table->text('remarks')->nullable();
            $table->string('schedule_path')->nullable();
            $table->string('schedule_name', 200)->nullable();
            $table->string('schedule_mime', 120)->nullable();
            $table->unsignedInteger('schedule_size')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('quoted_total', 12, 2)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->string('source_url', 300)->nullable();
            $table->timestamps();
        });

        Schema::create('quote_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('product', 20);
            $table->string('description', 160)->nullable();
            $table->unsignedSmallInteger('thickness_mm')->nullable();
            $table->unsignedSmallInteger('width_mm')->nullable();
            $table->decimal('length_m', 5, 2)->nullable();
            $table->string('treatment', 10);
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 10);
            $table->string('delivery_site', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_lines');
        Schema::dropIfExists('quote_requests');
    }
};
