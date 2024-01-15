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
        // Drop the existing foreign key constraint
        Schema::table('borrowed_items', function (Blueprint $table) {
            $table->dropForeign(['approver_id']);
        });

        // Make the column nullable
        Schema::table('borrowed_items', function (Blueprint $table) {
            $table->uuid('approver_id')->nullable()->change();
        });

        // Add a new foreign key constraint with the modified settings
        Schema::table('borrowed_items', function (Blueprint $table) {
            $table->foreign('approver_id')
                ->references('id')->on('users')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the changes if needed
        Schema::table('borrowed_items', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['approver_id']);

            // Change the column back to non-nullable
            $table->uuid('approver_id')->nullable(false)->change();

            // Add the original foreign key constraint
            $table->foreign('approver_id')
                ->references('id')->on('users')
                ->onDelete('restrict');
        });
    }
};
