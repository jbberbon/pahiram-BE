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
        Schema::table('item_groups', function (Blueprint $table) {
            $table->foreign('group_category_id')->references('id')->on('item_group_categories')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('department_id')->references('id')->on('departments')
                ->onDelete('set null')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_groups', function (Blueprint $table) {
            $table->dropForeign(['group_category_id']);
            $table->dropForeign(['department_id']);
        });
    }
};
