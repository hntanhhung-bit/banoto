<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\GHNService;
use Illuminate\Http\Request;

class GHNController extends Controller
{
    public function getProvinces(GHNService $ghn)
    {
        return response()->json($ghn->getProvinces());
    }

    public function getDistricts(int $provinceId, GHNService $ghn)
    {
        return response()->json($ghn->getDistricts($provinceId));
    }

    public function getWards(int $districtId, GHNService $ghn)
    {
        return response()->json($ghn->getWards($districtId));
    }

    public function getShippingFee(Request $request, GHNService $ghn)
    {
        $request->validate([
            'to_district_id' => 'required|integer',
            'to_ward_code' => 'required|string',
        ]);

        $cart = session('cart', []);
        $defaultCarWeight = (int) config('services.ghn.default_weight', 25000); // 25kg / xe (>20kg)
        
        $totalWeight = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $itemWeight = (int) ($item['weight'] ?? $defaultCarWeight);
                if ($itemWeight < 20000) {
                    $itemWeight = $defaultCarWeight;
                }
                $totalWeight += $itemWeight * (int) ($item['quantity'] ?? 1);
            }
        }
        
        if ($totalWeight < 20000) {
            $totalWeight = $defaultCarWeight;
        }

        return response()->json($ghn->calculateFee(array_merge([
            'from_district_id' => (int) config('services.ghn.from_district_id', 1450),
            'to_district_id' => (int) $request->to_district_id,
            'to_ward_code' => (string) $request->to_ward_code,
        ], $ghn->packageParameters($totalWeight))));
    }
}
