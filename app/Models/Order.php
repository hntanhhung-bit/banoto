<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'partner_id',
        'appointment_id',
        'order_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'latitude',
        'longitude',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'shipping_status',
        'ghn_order_code',
        'ghn_total_fee',
        'delivery_type',
        'to_district_id',
        'to_ward_code',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function latestPaymentTransaction()
    {
        return $this->hasOne(PaymentTransaction::class)->latestOfMany();
    }

    /**
     * Đồng bộ hoặc tạo Đơn mua xe (Order) khi Đối tác Showroom chốt bán xe thành công (deal_won)
     */
    public static function syncFromAppointment(Appointment $appointment, ?string $paymentMethod = null, ?string $paymentStatus = null): self
    {
        $dealPrice = (float) ($appointment->deal_price ?: ($appointment->product?->price ?: 500000000));

        // Tìm đơn hàng gắn với lịch hẹn này hoặc tạo mới
        $order = self::where('appointment_id', $appointment->id)->first();

        // Xác định user_id của người mua
        $userId = $appointment->user_id;
        if (!$userId && $appointment->customer_email) {
            $existingUser = User::where('email', $appointment->customer_email)->first();
            if ($existingUser) {
                $userId = $existingUser->id;
            }
        }
        if (!$userId && $appointment->customer_phone) {
            $existingUser = User::where('phone', $appointment->customer_phone)->first();
            if ($existingUser) {
                $userId = $existingUser->id;
            }
        }

        // Phương thức thanh toán (VietQR, MoMo, Showroom / Chuyển khoản)
        $method = $paymentMethod ?: ($order?->payment_method ?: 'bank_transfer');

        // Trạng thái thanh toán (Mặc định khi chốt xe là đã thanh toán hoặc theo tiến độ)
        $payStatus = $paymentStatus ?: ($order?->payment_status ?: ($appointment->commission_status === 'paid' ? 'paid' : 'paid'));

        $orderData = [
            'user_id' => $userId,
            'partner_id' => $appointment->partner_id,
            'appointment_id' => $appointment->id,
            'customer_name' => $appointment->customer_name ?: 'Khách hàng',
            'customer_phone' => $appointment->customer_phone ?: '',
            'customer_email' => $appointment->customer_email ?: null,
            'customer_address' => $appointment->address ?: ($appointment->partner?->partner_showroom_address ?: ($appointment->partner?->showroom_address ?: 'Nhận trực tiếp tại Showroom đối tác')),
            'total_amount' => $dealPrice,
            'payment_method' => $method,
            'payment_status' => $payStatus,
            'order_status' => 'confirmed', // Trạng thái đơn: Đã xác nhận
            'delivery_type' => 'showroom_pickup',
            'note' => 'Đơn mua xe chốt từ Lịch hẹn #' . $appointment->appointment_code . ' qua Showroom ' . ($appointment->partner?->partner_showroom_name ?: ($appointment->partner?->showroom_name ?: $appointment->partner?->name)),
        ];

        if (!$order) {
            $orderData['order_code'] = 'ORD-' . strtoupper(\Illuminate\Support\Str::random(6));
            $order = self::create($orderData);
        } else {
            $order->update($orderData);
        }

        // Cập nhật hoặc tạo sản phẩm trong đơn hàng (OrderItem)
        $product = $appointment->product;
        if ($product) {
            $item = $order->items()->first();
            $itemData = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $dealPrice,
                'quantity' => 1,
                'category_name' => $product->brand?->name ?: ($product->category?->name ?: 'Xe thương mại'),
                'image' => $product->image,
                'color' => $appointment->selected_color ?: null,
            ];

            if ($item) {
                $item->update($itemData);
            } else {
                $order->items()->create($itemData);
            }
        }

        return $order;
    }
}
