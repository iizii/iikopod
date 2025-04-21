<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('iiko_menu_items_iiko_menu_item_sizes', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('iiko_menu_item_id');
            $table->unsignedBigInteger('iiko_menu_item_size_id');
            $table->timestamps();

            // Явно создаём внешний ключ с коротким именем
            $table->foreign('iiko_menu_item_id', 'item_fk')
                ->references('id')
                ->on('iiko_menu_items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('iiko_menu_item_size_id', 'item_size_fk')
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
