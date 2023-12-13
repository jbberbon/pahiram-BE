<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('apc_purchase_order_id')->unique();
            $table->date('date_filed')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('total_cost')->nullable();

            // FKs
            $table->uuid('requested_by')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->uuid('funding_assured_by')->nullable();
            $table->uuid('approved_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
