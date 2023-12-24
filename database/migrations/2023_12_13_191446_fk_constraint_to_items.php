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
        Schema::table('items', function (Blueprint $table) {
            $table->foreign('item_group_id')->references('id')->on('item_groups')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('item_status_id')->references('id')->on('item_statuses')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('located_at')->references('id')->on('locations')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('possessed_by')->references('id')->on('users')
                ->onDelete('set null')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['item_group_id']);
            $table->dropForeign(['item_status_id']);
            $table->dropForeign(['purchase_order_id']);
            $table->dropForeign(['located_at']);
            $table->dropForeign(['possessed_by']);
        });
    }
};
