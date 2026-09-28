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
        // 1. Thêm partner_id vào bảng products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'partner_id')) {
                $table->foreignId('partner_id')->nullable()->after('category_id')->constrained('users')->nullOnDelete();
            }
        });

        // 2. Thêm partner_id vào bảng appointments
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'partner_id')) {
                $table->foreignId('partner_id')->nullable()->after('product_id')->constrained('users')->nullOnDelete();
            }
        });

        // 3. Thêm các trường Hoàn cọc & Phí đối soát vào bảng rentals
        Schema::table('rentals', function (Blueprint $table) {
            if (!Schema::hasColumn('rentals', 'partner_id')) {
                $table->foreignId('partner_id')->nullable()->after('product_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('rentals', 'refund_bank_name')) {
                $table->string('refund_bank_name')->nullable()->after('payment_status');
                $table->string('refund_account_number')->nullable()->after('refund_bank_name');
                $table->string('refund_account_holder')->nullable()->after('refund_account_number');
                $table->string('refund_status')->default('none')->after('refund_account_holder'); // none, pending, refunded, holding
                $table->decimal('refund_amount', 15, 2)->default(0)->after('refund_status');
                $table->decimal('refund_holding_fee', 15, 2)->default(0)->after('refund_amount'); // Khoản giữ phạt nguội (nếu có)
                $table->text('refund_notes')->nullable()->after('refund_holding_fee');
                $table->timestamp('refunded_at')->nullable()->after('refund_notes');
            }
            if (!Schema::hasColumn('rentals', 'platform_fee')) {
                $table->decimal('platform_fee', 15, 2)->default(0)->after('total_amount'); // Hoa hồng sàn (15%)
                $table->decimal('partner_payout', 15, 2)->default(0)->after('platform_fee'); // Tiền trả đối tác (85%)
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'partner_id')) {
                $table->dropForeign(['partner_id']);
                $table->dropColumn('partner_id');
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'partner_id')) {
                $table->dropForeign(['partner_id']);
                $table->dropColumn('partner_id');
            }
        });

        Schema::table('rentals', function (Blueprint $table) {
            if (Schema::hasColumn('rentals', 'partner_id')) {
                $table->dropForeign(['partner_id']);
                $table->dropColumn('partner_id');
            }
            $table->dropColumn([
                'refund_bank_name',
                'refund_account_number',
                'refund_account_holder',
                'refund_status',
                'refund_amount',
                'refund_holding_fee',
                'refund_notes',
                'refunded_at',
                'platform_fee',
                'partner_payout',
            ]);
        });
    }
};
