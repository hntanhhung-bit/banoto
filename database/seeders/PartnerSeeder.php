<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partner1 = User::firstOrCreate(
            ['email' => 'partner1@autocar.vn'],
            [
                'name' => 'Showroom AutoCar Cầu Giấy (Đối tác)',
                'password' => Hash::make('12345678'),
                'role' => 'partner',
                'email_verified_at' => now(),
            ]
        );
        $partner1->update(['role' => 'partner', 'name' => 'Showroom AutoCar Cầu Giấy (Đối tác)']);

        $partner2 = User::firstOrCreate(
            ['email' => 'partner2@autocar.vn'],
            [
                'name' => 'Hệ thống Nhà xe Long Biên (Đối tác)',
                'password' => Hash::make('12345678'),
                'role' => 'partner',
                'email_verified_at' => now(),
            ]
        );
        $partner2->update(['role' => 'partner', 'name' => 'Hệ thống Nhà xe Long Biên (Đối tác)']);

        $products = Product::all();
        foreach ($products as $idx => $product) {
            $partner = ($idx % 2 === 0) ? $partner1 : $partner2;
            $product->update(['partner_id' => $partner->id]);
        }

        // Cập nhật các lịch hẹn và đơn thuê xe cũ nếu chưa có partner_id
        \App\Models\Appointment::whereNull('partner_id')->get()->each(function ($app) use ($partner1, $partner2) {
            $partnerId = $app->product?->partner_id ?? $partner1->id;
            $app->update(['partner_id' => $partnerId]);
        });

        \App\Models\Rental::whereNull('partner_id')->get()->each(function ($rent) use ($partner1, $partner2) {
            $partnerId = $rent->product?->partner_id ?? $partner1->id;
            // Tính toán hoa hồng sàn 15% và đối tác 85% cho các đơn hiện có
            $totalRentalFee = $rent->total_rental_fee ?: ($rent->daily_price * $rent->total_days);
            $platformFee = round($totalRentalFee * 0.15);
            $partnerPayout = $totalRentalFee - $platformFee;

            $rent->update([
                'partner_id' => $partnerId,
                'platform_fee' => $platformFee,
                'partner_payout' => $partnerPayout,
                'refund_status' => $rent->payment_status === 'paid' ? 'holding' : 'none',
                'refund_amount' => $rent->deposit_amount,
            ]);
        });
    }
}
