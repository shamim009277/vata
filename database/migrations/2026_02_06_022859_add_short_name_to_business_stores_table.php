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
        Schema::table('business_stores', function (Blueprint $table) {
            $table->string('short_name')->nullable()->after('store_name_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_stores', function (Blueprint $table) {
            $table->dropColumn('short_name');
        });
    }
};
