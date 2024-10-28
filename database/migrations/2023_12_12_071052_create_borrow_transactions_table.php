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
        Schema::create('borrow_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // FKs set constraint on other migration file
            $table->uuid('borrower_id');
            $table->uuid('endorsed_by')->nullable();
            $table->uuid('department_id');
            $table->uuid('transac_status_id');
            $table->uuid('purpose_id');

            $table->text('user_defined_purpose')->nullable();
            $table->decimal('penalty')->nullable();
            $table->string('remarks_by_endorser')->nullable();
            $table->string('remarks_by_approver')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_transactions');
    }
};
