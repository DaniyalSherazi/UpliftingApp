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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('rider_id');
            $table->foreign('rider_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->longText('pickup_location');
            $table->enum('status', ['pending', 'accepted', 'cancelled', 'completed'])->default('pending');
            $table->unsignedBigInteger('promo_code_id')->nullable();
            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->onUpdate('cascade')->onDelete('cascade');
            $table->decimal('distance', 8, 2)->nullable();
            $table->bigInteger('duration')->nullable();  
            $table->decimal('base_fare', 8, 2);
            $table->decimal('discount_amount', 8, 2);
            $table->decimal('final_fare', 8, 2);
            $table->bigInteger('current_rating')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
