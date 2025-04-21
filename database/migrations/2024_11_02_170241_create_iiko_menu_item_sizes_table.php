<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('iiko_menu_item_sizes', static function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable();
            $table->string('sku');
            $table->string('measure_unit_type');
            $table->boolean('is_default')->default(false);
            $table->integer('weight');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iiko_menu_item_sizes');
    }
};
