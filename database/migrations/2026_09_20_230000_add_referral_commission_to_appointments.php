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
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'deal_status')) {
                $table->string('deal_status')->default('negotiating')->after('status'); // negotiating, deal_won, deal_lost
                $table->decimal('deal_price', 15, 2)->nullable()->after('deal_status'); // Giá trị xe chốt bán
                $table->decimal('commission_rate', 5, 2)->default(1.00)->after('deal_price'); // Tỷ lệ hoa hồng môi giới (1%)
                $table->decimal('commission_amount', 15, 2)->default(0)->after('commission_rate'); // Tiền hoa hồng sàn giới thiệu nhận được
                $table->string('commission_status')->default('pending')->after('commission_amount'); // pending, paid
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'deal_status')) {
                $table->dropColumn(['deal_status', 'deal_price', 'commission_rate', 'commission_amount', 'commission_status']);
            }
        });
    }
};
