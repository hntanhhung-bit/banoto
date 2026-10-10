<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'partner_id',
        'product_id',
        'selected_color',
        'appointment_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'appointment_date',
        'appointment_time',
        'location_type',
        'address',
        'status',
        'deal_status',
        'deal_price',
        'commission_rate',
        'commission_amount',
        'commission_status',
        'commission_proof',
        'commission_paid_at',
        'note',
        'admin_note',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'commission_paid_at' => 'datetime',
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

    public function order()
    {
        return $this->hasOne(Order::class, 'appointment_id');
    }
}
