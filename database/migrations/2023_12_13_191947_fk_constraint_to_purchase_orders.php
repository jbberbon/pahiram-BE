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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('requested_by')->references('id')->on('users')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('verified_by')->references('id')->on('users')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('funding_assured_by')->references('id')->on('users')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('approved_by')->references('id')->on('users')
                ->onDelete('set null')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['verified_by']);
            $table->dropForeign(['funding_assured_by']);
            $table->dropForeign(['approved_by']);
        });
    }
};
