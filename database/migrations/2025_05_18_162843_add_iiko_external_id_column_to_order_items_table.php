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
            $table->string('iiko_external_id')->nullable();
//            $table->string('welcome_group_external_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', static function (Blueprint $table) {
            $table->dropColumn('iiko_external_id');
//            $table->dropColumn('welcome_group_external_id');
        });
    }
};
