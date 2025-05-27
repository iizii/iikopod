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
        Schema::table('order_items', static function (Blueprint $table) {
            $table->bigInteger('welcome_group_external_food_id')->nullable();
            $table->unsignedBigInteger('replaced_on')->nullable();
            $table->foreign('replaced_on')->references('id')->on('order_items')->onDelete('set null');        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', static function (Blueprint $table) {
            $table->dropColumn('welcome_group_external_food_id');
            $table->dropForeign(['replaced_on']);
            $table->dropColumn('replaced_on');
        });
    }
};
