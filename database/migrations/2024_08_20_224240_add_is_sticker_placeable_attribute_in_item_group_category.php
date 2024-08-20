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
        Schema::table('item_group_category', function (Blueprint $table) {
            $table->boolean('is_barcode_sticker_placeable')->default(false); // Add this line
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_group_category', function (Blueprint $table) {
            $table->dropColumn('is_sticker_placeable'); // Add this line
        });
    }
};
