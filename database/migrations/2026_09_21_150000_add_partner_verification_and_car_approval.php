<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm các trường giấy tờ kinh doanh & duyệt đối tác vào bảng users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'partner_status')) {
                $table->string('partner_status', 30)->default('none')->after('role'); // none, pending, approved, rejected
            }
            if (!Schema::hasColumn('users', 'showroom_name')) {
                $table->string('showroom_name')->nullable()->after('partner_status');
            }
            if (!Schema::hasColumn('users', 'showroom_address')) {
                $table->string('showroom_address', 500)->nullable()->after('showroom_name');
            }
            if (!Schema::hasColumn('users', 'representative_name')) {
                $table->string('representative_name')->nullable()->after('showroom_address');
            }
            if (!Schema::hasColumn('users', 'id_card_number')) {
                $table->string('id_card_number', 50)->nullable()->after('representative_name');
            }
            if (!Schema::hasColumn('users', 'tax_code')) {
                $table->string('tax_code', 50)->nullable()->after('id_card_number');
            }
            if (!Schema::hasColumn('users', 'business_license_image')) {
                $table->string('business_license_image')->nullable()->after('tax_code');
            }
            if (!Schema::hasColumn('users', 'id_card_image')) {
                $table->string('id_card_image')->nullable()->after('business_license_image');
            }
            if (!Schema::hasColumn('users', 'partner_applied_at')) {
                $table->timestamp('partner_applied_at')->nullable()->after('id_card_image');
            }
            if (!Schema::hasColumn('users', 'partner_approved_at')) {
                $table->timestamp('partner_approved_at')->nullable()->after('partner_applied_at');
            }
            if (!Schema::hasColumn('users', 'partner_reject_reason')) {
                $table->text('partner_reject_reason')->nullable()->after('partner_approved_at');
            }
        });

        // Tự động đánh dấu partner_status = 'approved' cho các đối tác đã tồn tại
        DB::table('users')->where('role', 'partner')->update(['partner_status' => 'approved', 'partner_approved_at' => now()]);

        // 2. Thêm các trường duyệt & tình trạng xe vào bảng products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'approval_status')) {
                $table->string('approval_status', 30)->default('approved')->after('rental_status'); // pending, approved, rejected
            }
            if (!Schema::hasColumn('products', 'car_plate')) {
                $table->string('car_plate', 50)->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('products', 'car_year')) {
                $table->integer('car_year')->nullable()->after('car_plate');
            }
            if (!Schema::hasColumn('products', 'car_condition')) {
                $table->text('car_condition')->nullable()->after('car_year');
            }
            if (!Schema::hasColumn('products', 'admin_feedback')) {
                $table->text('admin_feedback')->nullable()->after('car_condition');
            }
            if (!Schema::hasColumn('products', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('admin_feedback');
            }
        });

        // 3. Thêm các trường hoa hồng sàn 10% khi nghiệm thu vào bảng rentals
        Schema::table('rentals', function (Blueprint $table) {
            if (!Schema::hasColumn('rentals', 'partner_commission_fee')) {
                $table->decimal('partner_commission_fee', 15, 2)->default(0)->after('partner_payout');
            }
            if (!Schema::hasColumn('rentals', 'partner_commission_status')) {
                $table->string('partner_commission_status', 30)->default('unpaid')->after('partner_commission_fee'); // unpaid, paid
            }
            if (!Schema::hasColumn('rentals', 'partner_commission_proof')) {
                $table->string('partner_commission_proof')->nullable()->after('partner_commission_status');
            }
            if (!Schema::hasColumn('rentals', 'partner_commission_paid_at')) {
                $table->timestamp('partner_commission_paid_at')->nullable()->after('partner_commission_proof');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [
                'partner_status', 'showroom_name', 'showroom_address',
                'representative_name', 'id_card_number', 'tax_code',
                'business_license_image', 'id_card_image',
                'partner_applied_at', 'partner_approved_at', 'partner_reject_reason'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $cols = ['approval_status', 'car_plate', 'car_year', 'car_condition', 'admin_feedback', 'approved_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('rentals', function (Blueprint $table) {
            $cols = ['partner_commission_fee', 'partner_commission_status', 'partner_commission_proof', 'partner_commission_paid_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('rentals', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
