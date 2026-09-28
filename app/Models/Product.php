<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Cho phép điền dữ liệu vào các cột này
    protected $fillable = [
        'category_id',
        'partner_id',
        'name',
        'price',
        'is_for_rent',
        'rent_price_per_day',
        'driver_price_per_day',
        'rental_deposit',
        'rental_status',
        'approval_status',
        'car_plate',
        'car_year',
        'car_condition',
        'admin_feedback',
        'approved_at',
        'quantity',
        'color',
        'description',
        'image',
    ];

    // Khai báo: 1 Sản phẩm (Ô tô) thuộc về 1 Danh mục
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Quan hệ với Đối tác Showroom / Nhà xe sở hữu xe
    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    // Quan hệ với các Lịch hẹn xem xe
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Quan hệ với các Đơn thuê xe
    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    // Quan hệ với các Lượt mua hàng (OrderItem)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Quan hệ với bảng Màu sắc & Giá theo màu
    public function colors()
    {
        return $this->hasMany(ProductColor::class);
    }

    // Lấy danh sách 3 màu phổ biến kèm giá tương ứng cho xe
    public function getColorVariants()
    {
        if ($this->colors()->count() > 0) {
            return $this->colors;
        }

        $baseRentPrice = $this->rent_price_per_day ?: 800000;
        $baseSalePrice = $this->price ?: 500000000;

        // 3 màu phổ biến nhất của ô tô với phân loại giá khác nhau
        return collect([
            (object)[
                'color_name' => 'Trắng ngọc trai',
                'color_hex' => '#FFFFFF',
                'border' => '#ccc',
                'extra_rent_price' => 50000,
                'rent_price_per_day' => $baseRentPrice + 50000,
                'sale_price' => $baseSalePrice + 10000000,
                'is_default' => ($this->color === 'Trắng' || empty($this->color)),
            ],
            (object)[
                'color_name' => 'Đen ánh kim',
                'color_hex' => '#111111',
                'border' => '#111',
                'extra_rent_price' => 0,
                'rent_price_per_day' => $baseRentPrice,
                'sale_price' => $baseSalePrice,
                'is_default' => ($this->color === 'Đen'),
            ],
            (object)[
                'color_name' => 'Đỏ thể thao',
                'color_hex' => '#D0021B',
                'border' => '#d0021b',
                'extra_rent_price' => 100000,
                'rent_price_per_day' => $baseRentPrice + 100000,
                'sale_price' => $baseSalePrice + 20000000,
                'is_default' => ($this->color === 'Đỏ'),
            ],
        ]);
    }

    // Thông tin Showroom / Nhà xe đối tác sở hữu xe (Mô hình Bên thứ 3 đặt hộ & bảo lãnh)
    public function getPartnerShowroomAttribute()
    {
        $showrooms = [
            1 => [
                'name' => 'Showroom AutoCar Partner Cầu Giấy',
                'address' => 'Số 68 Đường Cầu Giấy, Phường Quan Hoa, Cầu Giấy, Hà Nội',
                'phone' => '024.3833.6868',
                'rating' => '4.9/5 (128 đánh giá)',
            ],
            2 => [
                'name' => 'Đại lý Ô tô Đối tác Mỹ Đình',
                'address' => 'Số 18 Lê Đức Thọ, Phường Mỹ Đình, Nam Từ Liêm, Hà Nội',
                'phone' => '024.3768.9999',
                'rating' => '4.8/5 (95 đánh giá)',
            ],
            3 => [
                'name' => 'Hệ thống Nhà xe Đối tác Long Biên',
                'address' => 'Số 3-5 Nguyễn Văn Linh, Phường Gia Thụy, Long Biên, Hà Nội',
                'phone' => '024.3875.5555',
                'rating' => '5.0/5 (156 đánh giá)',
            ],
        ];

        $key = ($this->id % 3) + 1;
        $default = $showrooms[$key];

        if ($this->partner) {
            $default['name'] = $this->partner->name;
            $default['email'] = $this->partner->email;
        }

        return (object)$default;
    }

    // Đường dẫn hình ảnh xe an toàn (hỗ trợ cả public/images, storage, URL bên ngoài)
    public function getImageUrlAttribute()
    {
        if (!empty($this->image)) {
            // Trường hợp là URL ngoài
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                return $this->image;
            }
            // Thư mục upload chính của hệ thống: public/images/
            if (file_exists(public_path('images/' . $this->image))) {
                return asset('images/' . $this->image);
            }
            // Thư mục storage nếu có
            if (file_exists(public_path('storage/' . $this->image))) {
                return asset('storage/' . $this->image);
            }
            // File trực tiếp trong public/
            if (file_exists(public_path($this->image))) {
                return asset($this->image);
            }
            // Mặc định trả về link images/
            return asset('images/' . $this->image);
        }

        // Ảnh xe mặc định chất lượng cao khi chưa tải ảnh lên
        return 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=800&auto=format&fit=crop&q=80';
    }
}