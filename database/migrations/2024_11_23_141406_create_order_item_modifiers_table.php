<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_item_modifiers', static function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(OrderItem::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignIdFor(IikoMenuItemModifierItem::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_modifiers');
    }
};
