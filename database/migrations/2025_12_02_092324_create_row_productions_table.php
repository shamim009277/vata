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
        Schema::create('row_productions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('season');
            $table->foreignId('field_list_id')->constrained('field_lists')->cascadeOnDelete();
            $table->date('product_date');
            $table->integer('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->string('note')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->index('business_id');
            $table->index('store_id');
            $table->index('season');
            $table->index('field_list_id');
            $table->index('product_date');
            $table->index('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('row_productions');
    }
};
