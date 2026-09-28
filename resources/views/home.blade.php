@extends('layouts.app')

@section('content')
<!-- Tùy chỉnh CSS để giống phong cách Oto.com.vn -->
<style>
    body { background-color: #f4f4f4; }
    .search-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 30px;}
    .search-btn { background-color: #005fb7; color: white; font-weight: bold; border: none; }
    .search-btn:hover { background-color: #004b93; color: white;}
    .car-card { border: none; border-radius: 8px; transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; }
    .car-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .car-title { font-size: 16px; font-weight: 600; color: #333; margin-top: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;}
    .car-title:hover { color: #005fb7; text-decoration: none;}
    .car-price { font-size: 18px; font-weight: 700; color: #d0021b; margin-top: 5px;}
    .car-meta { font-size: 13px; color: #777; margin-bottom: 15px;}
    .section-title { font-size: 24px; font-weight: bold; color: #333; margin-bottom: 20px; text-transform: uppercase; border-left: 4px solid #d0021b; padding-left: 10px;}
</style>

<div class="container">
    <!-- KHU VỰC TÌM KIẾM (Giống hệt thanh tìm kiếm đầu trang của Oto.com) -->
    <div class="search-box">
        <h4 class="mb-3 font-weight-bold text-center" style="color: #333;">TÌM CHIẾC XE DÀNH CHO BẠN</h4>
        <form action="{{ route('home') }}" method="GET" class="row">
            <div class="col-md-3 mb-2">
                <select name="category_id" class="form-control" style="height: 45px;">
                    <option value="">Tất cả hãng xe</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <input type="number" name="price_min" value="{{ request('price_min') }}" class="form-control" placeholder="Giá từ (VNĐ)" style="height: 45px;">
            </div>
            <div class="col-md-3 mb-2">
                <input type="number" name="price_max" value="{{ request('price_max') }}" class="form-control" placeholder="Đến giá (VNĐ)" style="height: 45px;">
            </div>
            <div class="col-md-3 mb-2">
                <button type="submit" class="btn search-btn w-100" style="height: 45px;">🔍 Tìm kiếm ngay</button>
            </div>
        </form>
    </div>

    <!-- KHU VỰC DANH SÁCH XE NỔI BẬT -->
    <h2 class="section-title">Tin bán xe mới nhất</h2>
    
    <div class="row">
        @forelse ($products as $product)
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card car-card">
                <!-- Vùng Ảnh -->
                <a href="{{ route('products.show', $product->id) }}">
                    @if($product->image)
                        <img src="{{ asset('images/'.$product->image) }}" class="card-img-top" style="height: 180px; object-fit: cover; border-bottom: 1px solid #eee;" alt="{{ $product->name }}">
                    @else
                        <img src="https://via.placeholder.com/300x200?text=Chưa+cập+nhật+ảnh" class="card-img-top" style="height: 180px; object-fit: cover;">
                    @endif
                </a>
                
                <!-- Vùng Thông tin (Thiết kế chữ đỏ, font đậm) -->
                <div class="card-body p-3">
                    <a href="{{ route('products.show', $product->id) }}" style="text-decoration: none;">
                        <h5 class="car-title" title="{{ $product->name }}">{{ $product->name }}</h5>
                    </a>
                    
                    <p class="car-price">{{ number_format($product->price) }} VNĐ</p>
                    
                    <div class="car-meta d-flex justify-content-between">
                        <span><i class="fa fa-tags"></i> {{ $product->category->name ?? 'Không xác định' }}</span>
                        <span>Màu: {{ $product->color }}</span>
                    </div>
                    
                    <a class="btn btn-outline-danger btn-sm w-100 font-weight-bold" style="border-radius: 4px;" href="{{ route('products.show', $product->id) }}">Chi tiết xe</a>
                </div>
            </div>
        </div>
        @empty
        <!-- Hiển thị khi không tìm thấy xe -->
        <div class="col-12 text-center py-5">
            <img src="https://oto.com.vn/Content/images/empty-car.png" alt="Không có xe" style="width: 150px; opacity: 0.5">
            <h5 class="mt-3 text-muted">Hiện chưa có tin đăng bán xe nào phù hợp.</h5>
        </div>
        @endforelse
    </div>
</div>
@endsection