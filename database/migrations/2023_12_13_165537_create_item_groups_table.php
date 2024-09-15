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
        Schema::create('item_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Shouldnt be unique because there would be items that will be repeating accross different offices
            $table->string('model_name');
            $table->boolean('is_required_supervisor_approval')->default(0);
            // Description column with a 500 character limit
            $table->string('description', 500)->nullable();

            // FKs
            $table->uuid('group_category_id');
            $table->uuid('department_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_groups');
    }
};
