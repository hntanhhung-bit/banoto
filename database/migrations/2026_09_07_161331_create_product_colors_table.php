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
        if (!Schema::hasTable('product_colors')) {
            Schema::create('product_colors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('color_name'); // Trắng, Đen, Đỏ, Bạc...
                $table->string('color_hex')->default('#FFFFFF');
                $table->decimal('extra_rent_price', 15, 2)->default(0); // Chênh lệch giá thuê / ngày
                $table->decimal('rent_price_per_day', 15, 2)->nullable(); // Giá thuê riêng theo màu
                $table->decimal('extra_sale_price', 15, 2)->default(0); // Chênh lệch giá bán
                $table->integer('quantity')->default(5);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('appointments') && !Schema::hasColumn('appointments', 'selected_color')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('selected_color')->nullable()->after('product_id');
            });
        }

        if (Schema::hasTable('rentals') && !Schema::hasColumn('rentals', 'selected_color')) {
            Schema::table('rentals', function (Blueprint $table) {
                $table->string('selected_color')->nullable()->after('product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_colors');

        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'selected_color')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('selected_color');
            });
        }

        if (Schema::hasTable('rentals') && Schema::hasColumn('rentals', 'selected_color')) {
            Schema::table('rentals', function (Blueprint $table) {
                $table->dropColumn('selected_color');
            });
        }
    }
};
