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
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('user_role_id')->references('id')->on('roles')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('course_id')->references('id')->on('courses')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('acc_status_id')->references('id')->on('account_statuses')
                ->onDelete('set null')
                ->cascadeOnUpdate();
            $table->foreign('department_id')->references('id')->on('account_statuses')
                ->onDelete('set null')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['user_role_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['acc_status_id']);
            $table->dropForeign(['department_id']);

        });
    }
};
