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
        Schema::create('vehicle_type_rates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->bigInteger('current_base_price');
            $table->bigInteger('current_price_per_km');
            $table->bigInteger('current_price_per_min');
            $table->longText('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_type_rates');
    }
};
