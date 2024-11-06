<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('iiko_menu_item_modifier_item_prices', static function (Blueprint $table) {
            $table->id();
            $table
                ->foreignIdFor(IikoMenuItemModifierItem::class)
                ->constrained(
                    'iiko_menu_item_modifier_items',
                    'id',
                    'modifier_item_prices_modifier_item_id_foreign',
                )
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table
                ->integer('price')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iiko_menu_item_modifier_item_prices');
    }
};
