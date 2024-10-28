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
        Schema::create('penalized_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('borrowing_transac_id')->unique();
            $table->uuid('status_id');

            $table->uuid('cashier_id')->nullable();
            $table->text('remarks_by_cashier')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('receipt_number')->nullable()->unique();

            // Incase delinquent is awarded of 0 payment
            $table->uuid('balance_appeal_facilitated_by')->nullable();
            $table->text('remarks_by_appeal_facilitator')->nullable();
            $table->dateTime('settled_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalized_transactions');
    }
};
