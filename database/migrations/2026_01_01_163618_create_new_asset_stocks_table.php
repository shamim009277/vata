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
        Schema::create('new_asset_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('season');
            $table->date('stock_date');
            $table->string('product_name');
            $table->string('product_category')->nullable();
            $table->string('vendor')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->string('location')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('has_warranty')->default(false);
            $table->date('warranty_expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('business_id');
            $table->index('store_id');
            $table->index('season');
            $table->index('product_name');
            $table->index('stock_date');
            $table->index('product_category');
            $table->index('has_warranty');
        });
    }

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_asset_stocks');
    }
};
