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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_for_rent')->default(true)->after('price');
            $table->decimal('rent_price_per_day', 15, 2)->default(800000)->after('is_for_rent');
            $table->decimal('rental_deposit', 15, 2)->default(5000000)->after('rent_price_per_day');
            $table->string('rental_status')->default('available')->after('rental_deposit'); // available, rented, maintenance
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_for_rent', 'rent_price_per_day', 'rental_deposit', 'rental_status']);
        });
    }
};
