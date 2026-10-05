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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Delivery Settings
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            // VAT Settings
            $table->decimal('vat_percentage', 5, 2)->default(14.00);
            $table->boolean('vat_enabled')->default(true);

            // Policies
            $table->longText('terms_conditions_en')->nullable();
            $table->longText('terms_conditions_ar')->nullable();
            $table->longText('privacy_policy_en')->nullable();
            $table->longText('privacy_policy_ar')->nullable();
            $table->longText('return_exchange_policy_en')->nullable();
            $table->longText('return_exchange_policy_ar')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
