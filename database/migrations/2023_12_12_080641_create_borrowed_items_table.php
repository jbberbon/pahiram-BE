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
        Schema::create('borrowed_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // FKs
            $table->uuid('borrowing_transac_id');
            $table->uuid('approver_id');
            $table->uuid('releaser_id')->nullable();
            $table->uuid('receiver_id')->nullable();
            $table->uuid('penalty_finalized_by')->nullable();
            $table->uuid('borrowed_item_status_id');
            $table->uuid('item_id');

            /**
             * 
             *  Nullable start and return date:: 
             *  as editing item dates in controller 
             *  needs to delete it first and
             *  finds new specific item based on new dates
             * 
             */
            $table->dateTime('start_date')->nullable();
            $table->dateTime('due_date')->nullable();

            $table->dateTime('date_returned');
            $table->decimal('penalty')->nullable();
            $table->text('remarks_by_receiver')->nullable();
            $table->text('remarks_by_penalty_finalizer')->nullable();
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
