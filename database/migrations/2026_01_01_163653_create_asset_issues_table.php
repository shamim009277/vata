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
        Schema::create('asset_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('season');
            $table->foreignId('stock_id')->constrained('new_asset_stocks')->onDelete('cascade');
            $table->string('product_name');
            $table->string('product_category')->nullable();
            $table->string('to_whom');
            $table->date('issue_date');
            $table->string('location')->nullable();
            $table->integer('quantity');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->index('business_id');
            $table->index('store_id');
            $table->index('season');
            $table->index('product_name');
            $table->index('issue_date');
            $table->index('to_whom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_issues');
    }
};
