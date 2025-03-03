<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Infrastructure\Persistence\Eloquent\Orders\Models\Order;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('endpoint_addresses', static function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('index')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('house')->nullable();
            $table->string('building')->nullable();
            $table->string('flat')->nullable();
            $table->string('entrance')->nullable();
            $table->string('floor')->nullable();
            $table->string('doorphone')->nullable();
            $table->string('region')->nullable();
            $table->string('line1')->nullable();
            $table->foreignIdFor(Order::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endpoint_addresses');
    }
};
