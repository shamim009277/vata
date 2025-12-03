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
        Schema::create('payment_kathas', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('season');
            $table->foreignId('payment_head_id')->constrained('payment_heads')->cascadeOnDelete();
            $table->enum('payment_type',['1'=>'Regular','2'=>'Advance','3'=>'Due']);
            $table->date('payment_date');
            $table->string('payment_details')->nullable();
            $table->decimal('quantity',18,2)->default(0);
            $table->decimal('amount',18,2)->default(0);
            $table->decimal('paid_amount',18,2)->default(0);
            $table->decimal('due_amount',18,2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->index('business_id');
            $table->index('store_id');
            $table->index('season');
            $table->index('payment_head_id');
            $table->index('payment_type');
            $table->index('payment_date');
            $table->index('is_locked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_kathas');
    }
};
