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
                ->onDelete('restrict')
                ->cascadeOnUpdate();
            $table->foreign('item_status_id')->references('id')->on('item_statuses')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('located_at')->references('id')->on('locations')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('designated_to')->references('id')->on('users')
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
            $table->dropForeign([
                'item_group_id',
                'item_status_id',
                'purchase_order_id',
                'located_at',
                'designated_to'
            ]);
        });
    }
};
