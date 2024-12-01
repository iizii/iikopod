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
        Schema::create('welcome_group_restaurant_modifiers', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('welcome_group_restaurant_id');
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('welcome_group_modifier_id');
            $table->unsignedBigInteger('modifier_id');
            $table->unsignedBigInteger('external_id');
            $table->string('status');
            $table->string('status_comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_group_restaurant_modifiers');
    }
};
