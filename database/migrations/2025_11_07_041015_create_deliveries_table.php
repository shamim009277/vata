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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('season');
            $table->unsignedBigInteger('delivery_no');
            $table->date('delivery_date');
            $table->unsignedBigInteger('invoice_no');
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('address');
            $table->string('driver_name');
            $table->string('driver_phone');
            $table->string('truck_number');

            $table->integer('quantity')->default(0);
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('delivery_qty', 10, 2)->default(0);
            $table->decimal('delivery_rant', 10, 2)->default(0);
            $table->string('note');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->index('business_id');
            $table->index('store_id');
            $table->index('season');
            $table->index('delivery_no');
            $table->index('invoice_no');
            $table->index('customer_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
