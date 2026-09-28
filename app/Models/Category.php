<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['name']; 

    // Khai báo: 1 Danh mục có thể chứa nhiều Sản phẩm (Ô tô)
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}