<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('apc_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();

            // Make this a separate ADMIN TABLE
            // $table->boolean('is_admin')->default(0);
            
            // FKs to be assigned on a separate migration file
            $table->uuid('user_role_id');
            $table->uuid('acc_status_id');
            $table->uuid('department_id');
            $table->uuid('course_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
