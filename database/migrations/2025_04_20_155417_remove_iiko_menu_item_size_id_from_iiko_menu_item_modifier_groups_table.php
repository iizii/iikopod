<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('iiko_menu_item_modifier_groups', static function (Blueprint $table) {
            $table->dropForeign(['iiko_menu_item_size_id']);
            $table->dropColumn('iiko_menu_item_size_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iiko_menu_item_modifier_groups', static function (Blueprint $table) {
            $table
                ->foreignIdFor(IikoMenuItemSize::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
