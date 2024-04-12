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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->year('year')->nullable();
            $table->integer('mileage')->nullable();
            $table->string('number_plate')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('color')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('visibility')->default('public')->index();
            $table->string('status')->default('sale')->index();
            $table->string('condition')->default('new')->index();
            $table->unsignedBigInteger('car_brand_id')->nullable();
            $table->unsignedBigInteger('car_type_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable(); // Add this line
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('car_brand_id')->references('id')->on('car_brands')->onDelete('cascade');
            $table->foreign('car_type_id')->references('id')->on('car_types')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade'); // Add this line
            $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};