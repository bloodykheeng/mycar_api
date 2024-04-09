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
        Schema::create('parkings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('currency')->nullable();
            $table->string('billing_cycle')->nullable();
            $table->string('status')->default('active');
            $table->decimal('fee_amount', 15, 2);
            $table->date('start_date')->index();
            $table->date('end_date')->index()->nullable();
            $table->text('details')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Define foreign key constraints
            $table->foreign('car_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking');
    }
};
