<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rental;

class HandoverCodeSeeder extends Seeder
{
    public function run(): void
    {
        $rentals = Rental::whereNull('handover_code')->get();
        foreach ($rentals as $rental) {
            $rental->update([
                'handover_code' => (string) rand(100000, 999999),
                'handover_status' => $rental->rental_status === 'returned' ? 'returned' : ($rental->rental_status === 'in_progress' ? 'verified' : 'pending'),
                'handover_verified_at' => in_array($rental->rental_status, ['in_progress', 'returned']) ? now() : null,
            ]);
        }
    }
}
