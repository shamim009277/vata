<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('business_stores')->cascadeOnDelete();
            $table->string('season')->nullable();
            $table->string('name');
            $table->string('number');
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('status')->default('active'); // active, maintenance, inactive
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('business_stores')->cascadeOnDelete();
            $table->string('season')->nullable();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('driver_name')->nullable();
            $table->string('start_location');
            $table->string('end_location');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('purpose')->nullable();
            $table->string('status')->default('running'); // running, completed, cancelled
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_fuels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('business_stores')->cascadeOnDelete();
            $table->string('season')->nullable();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('fuel_type')->nullable();
            $table->string('voucher_no')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('business_stores')->cascadeOnDelete();
            $table->string('season')->nullable();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('workshop_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_services');
        Schema::dropIfExists('vehicle_fuels');
        Schema::dropIfExists('vehicle_trips');
        Schema::dropIfExists('vehicles');
    }
};
