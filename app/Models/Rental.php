<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'partner_id',
        'product_id',
        'selected_color',
        'rental_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'start_date',
        'end_date',
        'rental_type',
        'driver_fee_per_day',
        'total_driver_fee',
        'destination_address',
        'pickup_lat',
        'pickup_lng',
        'total_days',
        'daily_price',
        'total_rental_fee',
        'deposit_amount',
        'total_amount',
        'platform_fee',
        'partner_payout',
        'partner_commission_fee',
        'partner_commission_status',
        'partner_commission_proof',
        'partner_commission_paid_at',
        'pickup_location',
        'payment_method',
        'payment_status',
        'refund_bank_name',
        'refund_account_number',
        'refund_account_holder',
        'refund_status',
        'refund_amount',
        'refund_holding_fee',
        'refund_notes',
        'refunded_at',
        'handover_code',
        'handover_status',
        'handover_verified_at',
        'handover_verified_by',
        'handover_odo',
        'handover_fuel',
        'handover_notes',
        'return_odo',
        'return_fuel',
        'return_verified_at',
        'rental_status',
        'note',
        'admin_note',
        'driver_name',
        'driver_phone',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'refunded_at' => 'datetime',
        'handover_verified_at' => 'datetime',
        'return_verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function latestPaymentTransaction()
    {
        return $this->hasOne(PaymentTransaction::class)->latestOfMany();
    }
}
