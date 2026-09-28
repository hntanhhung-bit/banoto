<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductColor extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color_name',
        'color_hex',
        'extra_rent_price',
        'rent_price_per_day',
        'extra_sale_price',
        'quantity',
        'is_default',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
