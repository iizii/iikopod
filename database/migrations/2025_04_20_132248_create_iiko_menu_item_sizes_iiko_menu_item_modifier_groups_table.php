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
        Schema::create('iiko_menu_item_sizes_iiko_menu_item_modifier_groups', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('iiko_menu_item_modifier_group_id');
            $table->unsignedBigInteger('iiko_menu_item_size_id');
            $table->timestamps();

            // Явно создаём внешний ключ с коротким именем
            $table->foreign('iiko_menu_item_modifier_group_id', 'item_size_modifier_fk')
                ->references('id')
                ->on('iiko_menu_item_modifier_groups')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('iiko_menu_item_size_id', 'item_menu_item_size_fk')
                ->references('id')
                ->on('iiko_menu_item_sizes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iiko_menu_items_iiko_menu_item_sizes');
    }
};
