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
        Schema::create('iiko_menu_item_modifier_groups', static function (Blueprint $table) {
            $table->id();
            $table
                ->foreignIdFor(IikoMenuItemSize::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('external_id');
            $table->string('name');
            $table->string('sku');
            $table
                ->string('description')
                ->nullable();
            $table
                ->boolean('splittable')
                ->default(false);
            $table
                ->boolean('is_hidden')
                ->default(false);
            $table
                ->boolean('child_modifiers_have_min_max_restrictions')
                ->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iiko_menu_item_modifier_groups');
    }
};
