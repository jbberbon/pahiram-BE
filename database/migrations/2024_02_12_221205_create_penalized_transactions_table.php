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
            
            $table->uuid('borrowing_transac_id');
            $table->uuid('status_id');
            $table->uuid('payment_receiver_id')->nullable();
            $table->uuid('balance_finalized_by')->nullable();

            $table->text('remarks_by_payment_receiver')->nullable();
            $table->text('remarks_by_payment_finalizer')->nullable();
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
