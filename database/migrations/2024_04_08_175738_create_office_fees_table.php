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
        Schema::create('office_fees', function (Blueprint $table) {
            $table->id();
            $table->string('service_description')->nullable();
            $table->string('photo_url')->nullable();
            $table->decimal('fee_amount', 10, 2);
            $table->string('currency')->default('UGX');
            $table->text('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->integer('room_capacity')->nullable();
            $table->string('billing_cycle')->default('monthly');
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
        Schema::dropIfExists('office_fees');
    }
};
