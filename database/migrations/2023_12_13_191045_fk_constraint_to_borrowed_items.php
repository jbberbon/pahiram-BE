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
        Schema::table('borrowed_items', function (Blueprint $table) {
            $table->foreign('borrowing_transac_id')->references('id')->on('borrow_transactions')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
            $table->foreign('approver_id')->references('id')->on('users')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
            $table->foreign('borrowed_item_status_id')->references('id')->on('borrowed_item_statuses')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
            $table->foreign('item_id')->references('id')->on('items')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowed_items', function (Blueprint $table) {
            $table->dropForeign([
                'borrowing_transac_id',
                'approver_id',
                'borrowed_item_status_id',
                'item_id'
            ]);
        });
    }
};
