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
        Schema::create('iiko_menu_item_nutrition', static function (Blueprint $table) {
            $table->id();
            $table
                ->foreignIdFor(IikoMenuItemSize::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->float('fats');
            $table->float('proteins');
            $table->float('carbs');
            $table->float('energy');
            $table
                ->float('saturated_fatty_acid')
                ->nullable();
            $table
                ->float('salt')
                ->nullable();
            $table
                ->float('sugar')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iiko_menu_item_nutrition');
    }
};
