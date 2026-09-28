<?php

namespace App\Services;

use App\Models\Order;

class GHNOrderService
{
    public function __construct(private GHNService $ghn)
    {
    }

    public function create(Order $order, bool $isPaid = false): array
    {
        $items = [];
        $weight = 0;
        $defaultCarWeight = (int) config('services.ghn.default_weight', 25000); // 25kg > 20kg

        foreach ($order->items as $item) {
            $itemWeight = (int) ($item->product->weight ?? $defaultCarWeight);
            if ($itemWeight < 20000) {
                $itemWeight = $defaultCarWeight;
            }
            $weight += $itemWeight * (int) $item->quantity;
            $items[] = [
                'name' => $item->product_name ?? ($item->product->name ?? 'Xe ô tô'),
                'quantity' => (int) $item->quantity,
                'price' => (int) $item->price,
                'weight' => $itemWeight,
            ];
        }

        if (empty($items)) {
            $items[] = [
                'name' => 'Xe ô tô / Sản phẩm',
                'quantity' => 1,
                'price' => (int) ($order->total_amount ?? 5000000),
                'weight' => $defaultCarWeight,
            ];
            $weight = $defaultCarWeight;
        }

        return $this->ghn->createOrder([
            'payment_type_id' => 2, // 2: Người nhận thanh toán cước phí / COD
            'note' => 'Đơn hàng xe #' . ($order->order_code ?? $order->id),
            'required_note' => 'KHONGCHOXEMHANG',
            'to_name' => $order->customer_name ?? ($order->name ?? 'Khách hàng'),
            'to_phone' => $order->customer_phone ?? ($order->phone ?? ''),
            'to_address' => $order->customer_address ?? ($order->address ?? ''),
            'to_ward_code' => (string) ($order->to_ward_code ?? ''),
            'to_district_id' => (int) ($order->to_district_id ?? 0),
            'cod_amount' => $isPaid ? 0 : (int) ($order->total_amount ?? 0),
            'content' => 'Giao xe ô tô / Đơn hàng #' . ($order->order_code ?? $order->id),
            'weight' => $weight >= 20000 ? $weight : $defaultCarWeight,
            'length' => 100,
            'width' => 80,
            'height' => 60,
            'service_id' => 53320,
            'service_type_id' => 2,
            'items' => $items,
        ]);
    }
}
