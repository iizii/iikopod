  SQLSTATE[42S02]: Base table or view not found: 1146 Table 'default.organizations' doesn't exist (Connection: mysql, SQL: alter table `organizations` add `iiko_restaurant_id` varchar(255) not null)
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table
                ->string('iiko_restaurant_id')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table
                ->integer('iiko_restaurant_id')
                ->change();
        });
    }
};
