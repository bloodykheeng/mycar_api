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
        Schema::create('parking_fees', function (Blueprint $table) {
            $table->id();
            $table->decimal('fee_amount', 10, 2); // Assuming parking fee is a fixed amount
            $table->string('currency')->default('UGX');
            $table->string('billing_cycle')->default('daily');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_fees');
    }
};
