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
        Schema::table('organization_settings', static function (Blueprint $table) {
            $table->unique(['iiko_api_key', 'iiko_restaurant_id'], 'unique_iiko_api_key_iiko_restaurant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_settings', static function (Blueprint $table) {
            $table->dropUnique('unique_iiko_api_key_iiko_restaurant_id');
        });
    }
};
