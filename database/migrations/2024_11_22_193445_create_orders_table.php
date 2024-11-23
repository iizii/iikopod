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
        Schema::create('orders', static function (Blueprint $table): void {
            $table->id();
            $table->string('source');
            $table->string('status');
            $table
                ->string('iiko_external_id')
                ->nullable();
            $table
                ->string('welcome_group_external_id')
                ->nullable();
            $table
                ->string('comment')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
