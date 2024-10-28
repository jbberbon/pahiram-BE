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
        Schema::table('penalized_transactions', function (Blueprint $table) {
            $table->foreign('borrowing_transac_id')->references('id')->on('borrow_transactions')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
            $table->foreign('status_id')->references('id')->on('penalized_transaction_statuses')
                ->onDelete('restrict')
                ->cascadeOnUpdate();

            $table->foreign('cashier_id')->references('id')->on('users')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
            $table->foreign('balance_appeal_facilitated_by')->references('id')->on('users')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalized_transac', function (Blueprint $table) {
            //
        });
    }
};
