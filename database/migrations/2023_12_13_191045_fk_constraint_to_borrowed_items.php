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
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('approver_id')->references('id')->on('users')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('borrowed_item_status_id')->references('id')->on('borrowed_item_statuses')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('item_id')->references('id')->on('items')
                ->onDelete('set null')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowed_items', function (Blueprint $table) {
            $table->dropForeign(['borrowing_transac_id']);
            $table->dropForeign(['approver_id']);
            $table->dropForeign(['borrowed_item_status_id']);
            $table->dropForeign(['item_id']);
        });
    }
};
