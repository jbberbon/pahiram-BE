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
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('apc_item_id')->unique();

            // FKs: add constraint on separate file
            $table->uuid('item_group_id')->nullable();
            $table->uuid('item_status_id')->nullable();
            $table->uuid('purchase_order_id')->nullable();
            $table->uuid('located_at')->nullable();
            $table->uuid('possessed_by')->nullable();
            $table->uuid('borrowed_by')->nullable();

            $table->string('manufacturer_serial_num')->unique()->nullable();
            $table->date('warranty_expiration')->nullable();
            $table->integer('unit_cost')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('supplier_tel_num')->nullable();
            $table->string('supplier_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
