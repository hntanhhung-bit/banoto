@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f4f6f9; }
    
    /* Hero Banner */
    .hero-banner {
        background: linear-gradient(135deg, #0d3b66 0%, #005fb7 50%, #0084ff 100%);
        color: white;
        border-radius: 16px;
        padding: 40px 30px;
        margin-top: 20px;
        margin-bottom: 25px;
        box-shadow: 0 10px 25px rgba(0, 95, 183, 0.2);
    }
    .hero-title {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }
    .service-card-box {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 12px;
        padding: 16px;
        color: white;
        transition: transform 0.2s ease, background 0.2s ease;
        text-decoration: none;
        display: block;
        height: 100%;
    }
    .service-card-box:hover {
        transform: translateY(-4px);
        background: rgba(255, 255, 255, 0.22);
        color: white;
        text-decoration: none;
    }

    /* Search Box */
    .search-box {
        background: #ffffff;
        padding: 25px 30px;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
    }
    .search-title {
        color: #1a202c;
        font-weight: 700;
        font-size: 19px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .search-btn {
        background-color: #005fb7;
        color: white;
        font-weight: bold;
        border: none;
        transition: 0.3s;
    }
    .search-btn:hover {
        background-color: #004687;
        color: white;
    }

    /* Service Selector Pills */
    .service-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    .service-btn-pill {
        padding: 10px 22px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 14px;
        border: 2px solid #dee2e6;
        background: #fff;
        color: #495057;
        text-decoration: none;
        transition: all 0.25s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }
    .service-btn-pill:hover {
        border-color: #005fb7;
        color: #005fb7;
        text-decoration: none;
    }
    .service-btn-pill.active {
        background: #005fb7;
        color: #fff;
        border-color: #005fb7;
        box-shadow: 0 4px 10px rgba(0,95,183,0.3);
    }
    .service-btn-pill.active-view {
        background: #28a745;
        border-color: #28a745;
        color: #fff;
    }
    .service-btn-pill.active-rent {
        background: #d0021b;
        border-color: #d0021b;
        color: #fff;
    }
    .service-btn-pill.active-driver {
        background: #ff9800;
        border-color: #ff9800;
        color: #fff;
    }

    /* Category Pill Badges */
    .category-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 25px;
    }
    .category-pill {
        padding: 6px 16px;
        border-radius: 20px;
        background: #ffffff;
        color: #495057;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
    }
    .category-pill:hover {
        background: #e8f2fc;
        color: #005fb7;
        border-color: #005fb7;
        text-decoration: none;
    }
    .category-pill.active {
        background: #17a2b8;
        color: #ffffff;
        border-color: #17a2b8;
    }

    /* Car Card */
    .car-card {
        border: none;
        border-radius: 14px;
        transition: transform 0.25s, box-shadow 0.25s;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .car-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 28px rgba(0,0,0,0.12);
    }
    .car-img-wrapper {
        position: relative;
        overflow: hidden;
        background-color: #e2e8f0;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .car-img-wrapper img {
        height: 200px;
        width: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
    }
    .car-card:hover .car-img-wrapper img {
        transform: scale(1.05);
    }
    .car-category-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(0, 0, 0, 0.75);
        color: #fff;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
    .car-status-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .car-title {
        font-size: 16px;
        font-weight: 700;
        color: #212529;
        margin-top: 5px;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 44px;
        line-height: 1.4;
    }
    .car-title:hover {
        color: #005fb7;
        text-decoration: none;
    }
    
    /* Price Table in Card */
    .pricing-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 12px;
        border: 1px solid #eef0f3;
    }
    .pricing-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        margin-bottom: 4px;
    }
    .pricing-row:last-child {
        margin-bottom: 0;
    }
    .pricing-label {
        color: #6c757d;
        font-weight: 600;
    }
    .pricing-value {
        font-weight: 800;
    }

    /* Action Buttons in Card */
    .card-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        margin-top: auto;
    }
    .btn-action-custom {
        font-size: 12px;
        font-weight: 700;
        padding: 7px 8px;
        border-radius: 6px;
        text-align: center;
        white-space: nowrap;
        transition: 0.2s;
    }
</style>

<div class="container">
    <!-- HERO BANNER: 3 DỊCH VỤ TRỌNG TÂM -->
    <div class="hero-banner">
        <div class="row align-items-center">
            <div class="col-lg-12 mb-3">
                <span class="badge badge-warning px-3 py-1 font-weight-bold text-dark mb-2" style="font-size: 12px;">
                    <i class="fa fa-shield"></i> NỀN TẢNG BÊN THỨ BA ĐỘC LẬP & BẢO LÃNH DỊCH VỤ
                </span>
                <h1 class="hero-title mb-2">SÀN ĐẶT HỘ LỊCH XEM XE & DỊCH VỤ THUÊ XE TOÀN QUỐC</h1>
                <p class="mb-0 text-white-50 font-weight-normal" style="font-size: 15px;">Kết nối trực tiếp bạn với các Showroom & Nhà xe đối tác đã kiểm định độc lập. Đặt lịch xem xe hộ miễn phí, cử chuyên viên kiểm tra xe và bảo lãnh cọc thuê xe an toàn 100%.</p>
            </div>
            
            <div class="col-lg-4 col-md-4 mb-2">
                <a href="{{ route('welcome', ['service' => 'view']) }}" class="service-card-box">
                    <div class="d-flex align-items-center mb-1">
                        <i class="fa fa-calendar-check-o fa-2x text-warning mr-3"></i>
                        <div>
                            <h6 class="font-weight-bold mb-0 text-white">1. Đặt Lịch Xem Xe Hộ</h6>
                            <small class="text-white-50">Đặt hẹn Showroom đối tác & Cử thợ check xe</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-4 mb-2">
                <a href="{{ route('welcome', ['service' => 'rent_self']) }}" class="service-card-box">
                    <div class="d-flex align-items-center mb-1">
                        <i class="fa fa-key fa-2x text-danger mr-3"></i>
                        <div>
                            <h6 class="font-weight-bold mb-0 text-white">2. Thuê Xe Tự Lái Hộ</h6>
                            <small class="text-white-50">Bảo lãnh cọc 100%, nhận xe tại nhà qua GPS</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-4 mb-2">
                <a href="{{ route('welcome', ['service' => 'rent_driver']) }}" class="service-card-box">
                    <div class="d-flex align-items-center mb-1">
                        <i class="fa fa-user-circle fa-2x text-info mr-3"></i>
                        <div>
                            <h6 class="font-weight-bold mb-0 text-white">3. Thuê Có Tài Xế Hộ</h6>
                            <small class="text-white-50">Tài xế chuyên nghiệp, theo dõi GPS an toàn</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- THANH CHỌN NHANH DỊCH VỤ -->
    <div class="service-selector">
        <a href="{{ route('welcome') }}" 
           class="service-btn-pill {{ !request('service') ? 'active' : '' }}">
            <i class="fa fa-th-large mr-1"></i> Tất cả xe
        </a>
        <a href="{{ route('welcome', array_merge(request()->except('service'), ['service' => 'view'])) }}" 
           class="service-btn-pill {{ request('service') == 'view' ? 'active-view' : '' }}">
            <i class="fa fa-calendar-check-o mr-1"></i> 📅 Đặt lịch xem xe
        </a>
        <a href="{{ route('welcome', array_merge(request()->except('service'), ['service' => 'rent_self'])) }}" 
           class="service-btn-pill {{ request('service') == 'rent_self' ? 'active-rent' : '' }}">
            <i class="fa fa-key mr-1"></i> 🔑 Thuê xe tự lái
        </a>
        <a href="{{ route('welcome', array_merge(request()->except('service'), ['service' => 'rent_driver'])) }}" 
           class="service-btn-pill {{ request('service') == 'rent_driver' ? 'active-driver' : '' }}">
            <i class="fa fa-user-circle mr-1"></i> 👨‍✈️ Thuê người lái
        </a>
    </div>

    <!-- KHU VỰC TÌM KIẾM & BỘ LỌC NÂNG CAO -->
    <div class="search-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="search-title mb-0">
                <i class="fa fa-search text-primary mr-2"></i> Tìm kiếm & Lọc xe
            </h4>
            @if(request()->hasAny(['keyword', 'category_id', 'service', 'price_range', 'price_min', 'price_max', 'color', 'sort']))
                <a href="{{ route('welcome') }}" class="btn btn-outline-danger btn-sm font-weight-bold" style="border-radius: 20px;">
                    <i class="fa fa-times"></i> Xóa tất cả bộ lọc
                </a>
            @endif
        </div>

        <form action="{{ route('welcome') }}" method="GET" id="search-form">
            @if(request('service'))
                <input type="hidden" name="service" value="{{ request('service') }}">
            @endif

            <div class="row">
                <!-- Từ khóa / Tên xe -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <label class="small font-weight-bold text-muted">Từ khóa tìm kiếm:</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fa fa-car text-muted"></i></span>
                        </div>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control border-left-0" placeholder="Tên xe, mẫu xe, hãng..." style="height: 45px;">
                    </div>
                </div>

                <!-- Phân loại / Hãng xe -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="small font-weight-bold text-muted">Hãng xe / Phân loại:</label>
                    <select name="category_id" class="form-control" style="height: 45px;">
                        <option value="">-- Tất cả các hãng --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->products_count }} xe)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Khoảng giá thuê -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="small font-weight-bold text-muted">Mức giá thuê / ngày:</label>
                    <select name="price_range" class="form-control" style="height: 45px;">
                        <option value="">-- Tất cả mức giá --</option>
                        <option value="under_1m" {{ request('price_range') == 'under_1m' ? 'selected' : '' }}>Dưới 1 triệu / ngày</option>
                        <option value="1m_2m" {{ request('price_range') == '1m_2m' ? 'selected' : '' }}>1 triệu - 2 triệu / ngày</option>
                        <option value="above_2m" {{ request('price_range') == 'above_2m' ? 'selected' : '' }}>Trên 2 triệu / ngày</option>
                    </select>
                </div>

                <!-- Màu sắc -->
                <div class="col-lg-2 col-md-6 mb-3">
                    <label class="small font-weight-bold text-muted">Màu sắc:</label>
                    <select name="color" class="form-control" style="height: 45px;">
                        <option value="">-- Tất cả màu --</option>
                        @foreach($availableColors as $c)
                            <option value="{{ $c }}" {{ request('color') == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Hàng lọc tùy chỉnh & Nút bấm -->
            <div class="row align-items-center mt-2">
                <div class="col-lg-6 col-md-12 mb-2">
                    <div class="d-flex align-items-center">
                        <span class="small font-weight-bold text-muted mr-2" style="white-space: nowrap;">Hoặc khoảng giá (VNĐ):</span>
                        <input type="number" name="price_min" value="{{ request('price_min') }}" class="form-control form-control-sm mr-2" placeholder="Giá từ">
                        <span class="text-muted mr-2">-</span>
                        <input type="number" name="price_max" value="{{ request('price_max') }}" class="form-control form-control-sm" placeholder="Đến giá">
                    </div>
                </div>

                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif

                <div class="col-lg-6 col-md-12 mb-2 text-right">
                    <button type="submit" class="btn search-btn px-4 py-2 font-weight-bold shadow-sm" style="height: 42px; border-radius: 8px;">
                        <i class="fa fa-filter mr-1"></i> Áp dụng bộ lọc
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- PHÂN LOẠI NHANH THEO THƯƠNG HIỆU -->
    <div class="category-pills">
        <a href="{{ route('welcome', array_merge(request()->except(['category_id', 'page']))) }}" 
           class="category-pill {{ !request('category_id') ? 'active' : '' }}">
            <i class="fa fa-bars mr-1"></i> Tất cả các hãng
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('welcome', array_merge(request()->except(['category_id', 'page']), ['category_id' => $cat->id])) }}" 
               class="category-pill {{ request('category_id') == $cat->id ? 'active' : '' }}">
                {{ $cat->name }} <span class="badge badge-light ml-1">{{ $cat->products_count }}</span>
            </a>
        @endforeach
    </div>

    <!-- DANH SÁCH XE -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div>
            <h4 class="font-weight-bold mb-1 text-dark" style="border-left: 4px solid #005fb7; padding-left: 10px;">
                @if(request('service') == 'view')
                    DANH SÁCH XE SẴN SÀNG LÁI THỬ & XEM XE
                @elseif(request('service') == 'rent_self')
                    DANH SÁCH XE CHO THUÊ TỰ LÁI
                @elseif(request('service') == 'rent_driver')
                    DANH SÁCH XE THUÊ CÓ TÀI XẾ
                @else
                    DANH SÁCH XE ĐẶT LỊCH & CHO THUÊ
                @endif
            </h4>
            <p class="text-muted small mb-0">Tìm thấy <strong>{{ $products->total() }}</strong> mẫu xe phù hợp với tiêu chí của bạn</p>
        </div>

        <!-- Sắp xếp -->
        <div class="d-flex align-items-center mt-2 mt-sm-0">
            <span class="small font-weight-bold text-muted mr-2" style="white-space: nowrap;"><i class="fa fa-sort"></i> Sắp xếp:</span>
            <select class="form-control form-control-sm" style="width: 200px; height: 38px; border-radius: 6px;" onchange="location = this.value;">
                <option value="{{ route('welcome', array_merge(request()->except('sort'), ['sort' => 'newest'])) }}" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                <option value="{{ route('welcome', array_merge(request()->except('sort'), ['sort' => 'best_seller'])) }}" {{ request('sort') == 'best_seller' ? 'selected' : '' }}>🔥 Xe bán chạy nhất</option>
                <option value="{{ route('welcome', array_merge(request()->except('sort'), ['sort' => 'rent_asc'])) }}" {{ request('sort') == 'rent_asc' ? 'selected' : '' }}>Giá thuê: Thấp đến Cao</option>
                <option value="{{ route('welcome', array_merge(request()->except('sort'), ['sort' => 'rent_desc'])) }}" {{ request('sort') == 'rent_desc' ? 'selected' : '' }}>Giá thuê: Cao đến Thấp</option>
                <option value="{{ route('welcome', array_merge(request()->except('sort'), ['sort' => 'name_asc'])) }}" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên xe: A - Z</option>
            </select>
        </div>
    </div>

    <!-- GRID XE -->
    @if($products->count() > 0)
        <div class="row">
            @foreach($products as $product)
                @php
                    $rentPrice = $product->rent_price_per_day ?: 800000;
                    $driverPrice = $product->driver_price_per_day ?: 500000;
                    $totalDriverRent = $rentPrice + $driverPrice;
                    $isRented = ($product->rental_status === 'rented');
                @endphp
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="car-card">
                        <div class="car-img-wrapper">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=500&auto=format&fit=crop&q=60';">

                            @if($product->category)
                                <span class="car-category-badge">
                                    <i class="fa fa-tag text-warning mr-1"></i> {{ $product->category->name }}
                                </span>
                            @endif

                            @if($isRented)
                                <span class="car-status-badge badge-danger">Đang cho thuê</span>
                            @else
                                <span class="car-status-badge badge-success">Xe đang rảnh</span>
                            @endif
                        </div>

                        <div class="card-body p-3 d-flex flex-column flex-grow-1">
                            <h5 class="car-title">
                                <a href="{{ route('products.show', $product->id) }}" class="text-dark">
                                    {{ $product->name }}
                                </a>
                            </h5>

                            <!-- Bảng giá dịch vụ -->
                            <div class="pricing-box">
                                <div class="pricing-row">
                                    <span class="pricing-label"><i class="fa fa-key text-danger"></i> Thuê tự lái:</span>
                                    <span class="pricing-value text-danger">{{ number_format($rentPrice) }} đ<small class="text-muted font-weight-normal">/ngày</small></span>
                                </div>
                                <div class="pricing-row">
                                    <span class="pricing-label"><i class="fa fa-user text-warning"></i> Kèm tài xế:</span>
                                    <span class="pricing-value text-primary">{{ number_format($totalDriverRent) }} đ<small class="text-muted font-weight-normal">/ngày</small></span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center small text-muted mb-2 pb-1 border-bottom">
                                <span><i class="fa fa-tint text-info"></i> {{ $product->color ?: 'Nhiều màu' }}</span>
                                <span><i class="fa fa-shield text-success"></i> Đã kiểm định</span>
                            </div>

                            <div class="small text-muted mb-3 text-truncate" title="{{ $product->partner_showroom->name }}">
                                <i class="fa fa-building text-primary mr-1"></i> <strong class="text-dark">{{ $product->partner_showroom->name }}</strong>
                            </div>

                            <!-- 3 Nút hành động dịch vụ Bên thứ ba -->
                            <div class="card-actions mb-2">
                                <a href="{{ route('products.show', $product->id) }}#tab-appointment" class="btn btn-outline-success btn-action-custom">
                                    <i class="fa fa-calendar-check-o"></i> Đặt xem hộ
                                </a>
                                <a href="{{ route('products.show', $product->id) }}#tab-rental-self" class="btn btn-outline-danger btn-action-custom">
                                    <i class="fa fa-key"></i> Thuê tự lái
                                </a>
                            </div>

                            <a href="{{ route('products.show', $product->id) }}#tab-rental-driver" class="btn btn-primary btn-sm btn-block font-weight-bold" style="border-radius: 6px; font-size: 13px;">
                                <i class="fa fa-user-circle mr-1"></i> Đặt thuê có tài xế hộ
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Phân trang -->
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links('pagination::bootstrap-4') }}
        </div>
    @else
        <div class="text-center py-5 bg-white rounded shadow-sm border my-4">
            <i class="fa fa-car text-muted fa-4x mb-3"></i>
            <h4 class="text-secondary font-weight-bold">Không tìm thấy xe nào phù hợp</h4>
            <p class="text-muted">Vui lòng thử điều chỉnh lại bộ lọc tìm kiếm, hãng xe hoặc khoảng giá thuê.</p>
            <a href="{{ route('welcome') }}" class="btn btn-primary px-4 font-weight-bold">
                <i class="fa fa-refresh mr-1"></i> Đặt lại tất cả bộ lọc
            </a>
        </div>
    @endif
</div>
@endsection