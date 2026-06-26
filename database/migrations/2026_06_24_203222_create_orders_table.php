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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->foreignId('address_details_id')->constrained('address_details');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->foreignId('delivery_method_id')->constrained('delivery_methods');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping', 10, 2);
            $table->decimal('discount', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('status');
            $table->string('payment_status');
            $table->boolean('terms_and_condition_agreed')->default(false);
            $table->date('estimated_delivery_date')->nullable();
            $table->string('user_full_name');
            $table->string('email');
            $table->string('phone_to_number');
             $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
