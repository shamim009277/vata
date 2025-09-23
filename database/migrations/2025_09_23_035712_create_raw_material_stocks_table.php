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
        Schema::create('raw_material_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('raw_material_id');
            $table->decimal('stock_in', 15, 2)->default(0);
            $table->decimal('stock_out', 15, 2)->default(0);
            $table->decimal('current_stock', 15, 2)->default(0);
            $table->unsignedBigInteger('unit_id');
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->cascadeOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_material_stocks');
    }
};
