<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifierType;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('welcome_group_modifiers', static function (Blueprint $table) {
            $table->id();
            $table
                ->foreignIdFor(WelcomeGroupModifierType::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table
                ->foreignIdFor(IikoMenuItemModifierItem::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('external_id');
            $table->unsignedBigInteger('external_modifier_type_id');
            $table->string('name');
            $table->boolean('is_default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_group_modifiers');
    }
};
