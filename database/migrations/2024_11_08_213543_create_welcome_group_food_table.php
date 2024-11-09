<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFoodCategory;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('welcome_group_food', static function (Blueprint $table) {
            $table->id();
            $table
                ->foreignIdFor(IikoMenuItem::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table
                ->foreignIdFor(WelcomeGroupFoodCategory::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('external_id');
            $table->unsignedBigInteger('external_food_category_id');
            $table->unsignedBigInteger('workshop_id');
            $table->string('name');
            $table->string('description');
            $table->unsignedInteger('weight');
            $table->unsignedInteger('caloricity');
            $table->unsignedInteger('price');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_group_food');
    }
};
