<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('iiko_menu_items', static function (Blueprint $table) {
            $table->id();
            $table
                ->foreignIdFor(IikoMenuItemGroup::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('external_id');
            $table->string('sku');
            $table->string('name');
            $table
                ->string('description')
                ->nullable();
            $table
                ->string('type')
                ->nullable();
            $table
                ->string('measure_unit')
                ->nullable();
            $table
                ->string('payment_subject')
                ->nullable();
            $table
                ->boolean('is_hidden')
                ->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iiko_menu_items');
    }
};
