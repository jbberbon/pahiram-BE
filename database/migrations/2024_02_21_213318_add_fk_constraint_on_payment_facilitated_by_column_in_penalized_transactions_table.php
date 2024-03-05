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
            $table->foreign('payment_facilitated_by')->references('id')->on('users')
                ->onDelete('restrict')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalized_transactions', function (Blueprint $table) {
            //
        });
    }
};
