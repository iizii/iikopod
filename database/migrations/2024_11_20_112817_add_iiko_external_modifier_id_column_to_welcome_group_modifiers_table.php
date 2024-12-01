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
        Schema::table('welcome_group_modifiers', static function (Blueprint $table) {
            $table->string('iiko_external_modifier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('welcome_group_modifiers', static function (Blueprint $table) {
            $table->dropColumn('iiko_external_modifier_id');
        });
    }
};
