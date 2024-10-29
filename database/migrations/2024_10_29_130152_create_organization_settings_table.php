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
        Schema::create('organization_settings', static function (Blueprint $table) {
            $table->id();

            $table->string('iiko_api_key');
            $table->unsignedBigInteger('iiko_restaurant_id');
            $table->unsignedBigInteger('welcome_group_restaurant_id');
            $table->unsignedBigInteger('welcome_group_default_workshop_id');
            $table->unsignedBigInteger('order_delivery_type_id');
            $table->unsignedBigInteger('order_pickup_type_id');
            $table->json('payment_types');
            $table->json('price_categories');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};
