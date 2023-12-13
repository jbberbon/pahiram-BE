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
        Schema::create('borrowed_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // FKs
            $table->uuid('borrowing_transac_id')->nullable();
            $table->uuid('approver_id')->nullable();
            $table->uuid('borrowed_item_status_id')->nullable();
            $table->uuid('item_id')->nullable();
            
            $table->dateTime('start_date');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('date_returned')->nullable();
            $table->decimal('penalty')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowed_items');
    }
};
