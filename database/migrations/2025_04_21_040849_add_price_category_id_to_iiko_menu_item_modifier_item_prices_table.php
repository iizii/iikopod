<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('iiko_menu_item_modifier_item_prices', static function (Blueprint $table) {
            $table->string('price_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iiko_menu_item_modifier_item_prices', static function (Blueprint $table) {
            $table->dropColumn('price_category_id');
        });
    }
};
