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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->year('year');
            $table->date('date');
            $table->enum('type',['purchase','sale','expense','labor','others']);
            $table->unsignedBigInteger('related_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->index(['type', 'year', 'related_id','payment_id','payment_method','date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
