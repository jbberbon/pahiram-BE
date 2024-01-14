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
        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->foreign('borrower_id')->references('id')->on('users')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
            $table->foreign('endorsed_by')->references('id')->on('users')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('department_id')->references('id')->on('departments')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
            $table->foreign('transac_status_id')->references('id')->on('borrow_transaction_statuses')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
            $table->foreign('purpose_id')->references('id')->on('borrow_purposes')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrow_transactions', function (Blueprint $table) {
            $table->dropForeign([
                'borrower_id',
                'endorsed_by',
                'department_id',
                'transac_status_id',
                'purpose_id'
            ]);
        });
    }
};
