@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f4f6f9; }

    .car-detail-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        background: #fff;
        overflow: hidden;
    }

    .price-badge-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }

    /* Color Selector Box */
    .color-variant-card {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fff;
    }
    .color-variant-card:hover {
        border-color: #005fb7;
        background: #f0f7ff;
    }
    .color-variant-card.active {
        border-color: #005fb7;
        background: #ebf4ff;
        box-shadow: 0 3px 8px rgba(0,95,183,0.15);
    }

    /* Gallery Slider Styles */
    .gallery-main-frame {
        position: relative;
        overflow: hidden;
        border-radius: 14px;
        background: #0f172a;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .gallery-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.9);
        color: #1e293b;
        border: none;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        z-index: 5;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .gallery-nav-btn:hover {
        background: #ffffff;
        color: #007bff;
        transform: translateY(-50%) scale(1.1);
    }
    .gallery-nav-btn.prev-btn { left: 14px; }
    .gallery-nav-btn.next-btn { right: 14px; }
    .gallery-thumb-item {
        cursor: pointer;
        flex-shrink: 0;
        width: 82px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        border: 2.5px solid transparent;
        opacity: 0.65;
        transition: all 0.2s ease;
        background: #e2e8f0;
    }
    .gallery-thumb-item:hover {
        opacity: 0.95;
    }
    .gallery-thumb-item.active {
        border-color: #007bff;
        opacity: 1;
        transform: scale(1.05);
        box-shadow: 0 3px 10px rgba(0,123,255,0.3);
    }
    .gallery-thumb-strip::-webkit-scrollbar {
        height: 6px;
    }
    .gallery-thumb-strip::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    /* CTA Action Buttons */
    .service-cta-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        background: #fff;
    }

    .cta-btn-appointment {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        border-radius: 12px;
        padding: 18px 24px;
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        text-align: left;
        display: block;
        width: 100%;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .cta-btn-appointment:hover {
        background: linear-gradient(135deg, #218838, #1aab82);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(40,167,69,0.3);
        text-decoration: none;
    }

    .cta-btn-self-drive {
        background: linear-gradient(135deg, #dc3545, #fd7e14);
        border: none;
        border-radius: 12px;
        padding: 18px 24px;
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        text-align: left;
        display: block;
        width: 100%;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .cta-btn-self-drive:hover {
        background: linear-gradient(135deg, #c82333, #e96b0e);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(220,53,69,0.3);
        text-decoration: none;
    }

    .cta-btn-driver {
        background: linear-gradient(135deg, #007bff, #6610f2);
        border: none;
        border-radius: 12px;
        padding: 18px 24px;
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        text-align: left;
        display: block;
        width: 100%;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .cta-btn-driver:hover {
        background: linear-gradient(135deg, #0069d9, #560bd0);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,123,255,0.3);
        text-decoration: none;
    }

    .cta-btn-icon {
        font-size: 24px;
        margin-right: 12px;
        opacity: 0.9;
    }

    .spec-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #f1f1f1;
        font-size: 14px;
    }
    .spec-table td:first-child {
        color: #6c757d;
        width: 45%;
        font-weight: 600;
    }
    .spec-table td:last-child {
        color: #2d3748;
        font-weight: 700;
    }
</style>

<div class="container mt-4 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white shadow-sm py-2 px-3 rounded" style="border: 1px solid #e9ecef;">
            <li class="breadcrumb-item"><a href="{{ route('welcome') }}" class="text-primary"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="breadcrumb-item">{{ $product->category->name ?? 'Dòng xe' }}</li>
            <li class="breadcrumb-item active text-dark font-weight-bold" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    @php
        $colorVariants = $product->getColorVariants();
        $defaultColor = $colorVariants->firstWhere('is_default', true) ?: $colorVariants->first();
        $initialRentPrice = $defaultColor->rent_price_per_day;
        $driverDailyFee = $product->driver_price_per_day ?: 500000;
        $depositAmount = $product->rental_deposit ?: 5000000;
        $selectedColor = request('color', $defaultColor->color_name);
        $allImages = $product->getAllImages();
    @endphp

    <div class="row mt-3">
        <!-- CỘT TRÁI: HÌNH ẢNH & THÔNG TIN CHI TIẾT XE -->
        <div class="col-lg-7 mb-4">
            <div class="car-detail-card p-3 mb-4">
                
                <!-- BỘ SƯU TẬP HÌNH ẢNH XE (GALLERY & SLIDER) -->
                <div class="gallery-container mb-3">
                    <!-- Khung ảnh chính lớn -->
                    <div class="gallery-main-frame position-relative" style="height: 420px;">
                        <img src="{{ $allImages[0] ?? $product->image_url }}" id="gallery-main-img" class="img-fluid w-100 h-100" style="object-fit: cover; transition: opacity 0.2s ease;" alt="{{ $product->name }}"
                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=800&auto=format&fit=crop&q=80';">

                        <!-- Badge trạng thái xe -->
                        @if($product->isSold())
                            <span class="badge badge-dark position-absolute px-3 py-2" style="top: 15px; right: 15px; font-size: 13px; font-weight: 700; z-index: 4;">
                                <i class="fa fa-handshake-o"></i> ĐÃ BÁN XE
                            </span>
                        @elseif($product->isServing())
                            <span class="badge badge-danger position-absolute px-3 py-2" style="top: 15px; right: 15px; font-size: 13px; font-weight: 700; z-index: 4;">
                                <i class="fa fa-road"></i> Đang phục vụ khách hàng
                            </span>
                        @else
                            <span class="badge badge-success position-absolute px-3 py-2" style="top: 15px; right: 15px; font-size: 13px; font-weight: 700; z-index: 4;">
                                <i class="fa fa-check-circle"></i> Sẵn sàng phục vụ
                            </span>
                        @endif

                        @if(count($allImages) > 1)
                            <!-- Nút chuyển ảnh trước -->
                            <button type="button" class="gallery-nav-btn prev-btn" onclick="prevGalleryImage()" title="Ảnh trước">
                                <i class="fa fa-chevron-left"></i>
                            </button>

                            <!-- Nút chuyển ảnh kế tiếp -->
                            <button type="button" class="gallery-nav-btn next-btn" onclick="nextGalleryImage()" title="Ảnh kế tiếp">
                                <i class="fa fa-chevron-right"></i>
                            </button>

                            <!-- Badge đếm số ảnh -->
                            <div class="position-absolute" style="bottom: 12px; right: 14px; z-index: 4;">
                                <span class="badge badge-dark px-2 py-1 shadow" style="background: rgba(15,23,42,0.75); font-size: 12px; backdrop-filter: blur(4px);">
                                    <i class="fa fa-camera mr-1"></i> <span id="gallery-current-idx">1</span> / {{ count($allImages) }} ảnh
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Dải thumbnail nhỏ bên dưới -->
                    @if(count($allImages) > 1)
                        <div class="gallery-thumb-strip d-flex align-items-center mt-2 pb-1" style="overflow-x: auto; gap: 10px; scroll-behavior: smooth;">
                            @foreach($allImages as $idx => $imgUrl)
                                <div class="gallery-thumb-item {{ $idx === 0 ? 'active' : '' }}" onclick="switchGalleryImage({{ $idx }})" data-index="{{ $idx }}">
                                    <img src="{{ $imgUrl }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Ảnh chi tiết {{ $idx + 1 }}"
                                         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=200&auto=format&fit=crop&q=60';">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <h3 class="font-weight-bold text-dark mb-1">{{ $product->name }}</h3>
                <div class="d-flex align-items-center mb-3">
                    <span class="badge badge-info px-2 py-1 mr-2"><i class="fa fa-tag"></i> {{ $product->category->name ?? 'Ô tô' }}</span>
                    <span class="badge badge-light border px-2 py-1 mr-2"><i class="fa fa-shield text-success"></i> Bảo hiểm 2 chiều</span>
                    <span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-handshake-o"></i> Bên thứ ba bảo lãnh</span>
                </div>

                <!-- KHUNG THÔNG TIN SHOWROOM ĐỐI TÁC & BẢO CHỨNG BÊN THỨ BA -->
                <div class="p-3 mb-3 rounded shadow-sm" style="background: #f0f7ff; border: 1.5px solid #b8daff;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge badge-primary px-2 py-1" style="font-size: 11px;">
                            <i class="fa fa-building"></i> ĐỐI TÁC ỦY QUYỀN SỞ HỮU XE
                        </span>
                        <span class="text-success font-weight-bold small">
                            <i class="fa fa-check-circle"></i> Đã kiểm định 160 điểm độc lập
                        </span>
                    </div>
                    <div class="font-weight-bold text-dark" style="font-size: 15px;">
                        <i class="fa fa-map-marker text-danger mr-1"></i> {{ $product->partner_showroom->name }}
                    </div>
                    <div class="small text-muted mt-1">
                        Địa chỉ: {{ $product->partner_showroom->address }}
                    </div>
                    <div class="small text-muted mt-1 d-flex justify-content-between flex-wrap">
                        <span><i class="fa fa-phone text-success"></i> Hotline đối tác: <strong>{{ $product->partner_showroom->phone }}</strong></span>
                        <span><i class="fa fa-star text-warning"></i> Đánh giá: <strong>{{ $product->partner_showroom->rating }}</strong></span>
                    </div>
                    <div class="mt-2 pt-2 border-top small text-primary font-weight-bold" style="font-size: 12px;">
                        <i class="fa fa-shield text-success mr-1"></i> Nền tảng Bên thứ ba: Đặt lịch xem xe hộ miễn phí • Cử thợ check xe hộ • Bảo lãnh tiền cọc 100%.
                    </div>
                </div>

                <!-- BỘ PHÂN LOẠI MÀU SẮC & BẢNG GIÁ THEO MÀU -->
                <div class="p-3 bg-light rounded border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="font-weight-bold small text-uppercase text-dark mb-0">
                            <i class="fa fa-paint-brush text-danger mr-1"></i> Phân loại màu sắc & Giá theo màu:
                        </label>
                        <span class="badge badge-primary font-weight-bold" id="selected-color-badge" style="font-size: 12px;">
                            {{ $defaultColor->color_name }}
                        </span>
                    </div>

                    <div class="row" style="gap: 8px 0;">
                        @foreach($colorVariants as $cv)
                            <div class="col-md-4 col-sm-6 mb-2">
                                <div class="color-variant-card {{ $cv->color_name === $defaultColor->color_name ? 'active' : '' }}"
                                     onclick="selectCarColor('{{ $cv->color_name }}', {{ $cv->rent_price_per_day }}, {{ $cv->extra_rent_price }}, this)">
                                    <div class="d-flex align-items-center mb-1">
                                        <span style="display:inline-block; width:16px; height:16px; border-radius:50%; background-color:{{ $cv->color_hex }}; border:1.5px solid {{ $cv->border ?? '#aaa' }}; margin-right:6px;"></span>
                                        <strong class="small text-dark">{{ $cv->color_name }}</strong>
                                    </div>
                                    <div class="text-danger font-weight-bold small">
                                        {{ number_format($cv->rent_price_per_day) }} đ<small class="text-muted">/ngày</small>
                                    </div>
                                    @if($cv->extra_rent_price > 0)
                                        <small class="text-success font-weight-bold d-block" style="font-size: 11px;">+{{ number_format($cv->extra_rent_price) }} đ phụ phí màu</small>
                                    @else
                                        <small class="text-muted font-italic d-block" style="font-size: 11px;">Màu chuẩn</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Bảng giá dịch vụ áp dụng cho mẫu xe này -->
                <div class="mb-3">
                    <h6 class="font-weight-bold text-uppercase small text-muted mb-2"><i class="fa fa-list-alt text-primary mr-1"></i> Bảng giá dịch vụ:</h6>

                    <div class="price-badge-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-success"><i class="fa fa-calendar-check-o mr-1"></i> 1. Đặt lịch xem xe & lái thử:</strong>
                            <div class="small text-muted">Tại showroom hoặc mang xe tới tận nhà</div>
                        </div>
                        <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 13px;">MIỄN PHÍ</span>
                    </div>

                    <div class="price-badge-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-danger"><i class="fa fa-key mr-1"></i> 2. Thuê xe tự lái:</strong>
                            <div class="small text-muted">Tiền đặt cọc xe: {{ number_format($depositAmount) }} VNĐ</div>
                        </div>
                        <span class="text-danger font-weight-bold" style="font-size: 16px;">
                            <span id="display-self-rent-price">{{ number_format($initialRentPrice) }}</span> đ<small class="text-muted font-weight-normal">/ngày</small>
                        </span>
                    </div>

                    <div class="price-badge-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-primary"><i class="fa fa-user-circle mr-1"></i> 3. Thuê có người lái (Tài xế):</strong>
                            <div class="small text-muted">Phí xe + Phí tài xế ({{ number_format($driverDailyFee) }} đ/ngày)</div>
                        </div>
                        <span class="text-primary font-weight-bold" style="font-size: 16px;">
                            <span id="display-driver-rent-price">{{ number_format($initialRentPrice + $driverDailyFee) }}</span> đ<small class="text-muted font-weight-normal">/ngày</small>
                        </span>
                    </div>
                </div>

                <!-- Thông số kỹ thuật xe -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-cogs text-primary mr-1"></i> Thông số kỹ thuật:</h6>
                    <table class="table table-borderless spec-table mb-0">
                        <tbody>
                            @if($product->car_plate)
                            <tr><td><i class="fa fa-id-card-o mr-1"></i> Biển số xe</td><td>{{ $product->car_plate }}</td></tr>
                            @endif
                            @if($product->year)
                            <tr><td><i class="fa fa-calendar mr-1"></i> Năm sản xuất</td><td>{{ $product->year }}</td></tr>
                            @endif
                            @if($product->seats)
                            <tr><td><i class="fa fa-users mr-1"></i> Số chỗ ngồi</td><td>{{ $product->seats }} chỗ</td></tr>
                            @endif
                            @if($product->fuel_type)
                            <tr><td><i class="fa fa-tint mr-1"></i> Nhiên liệu</td><td>{{ $product->fuel_type }}</td></tr>
                            @endif
                            @if($product->transmission)
                            <tr><td><i class="fa fa-cog mr-1"></i> Hộp số</td><td>{{ $product->transmission }}</td></tr>
                            @endif
                            @if($product->mileage)
                            <tr><td><i class="fa fa-road mr-1"></i> Số km đã đi</td><td>{{ number_format($product->mileage) }} km</td></tr>
                            @endif
                            @if($product->origin)
                            <tr><td><i class="fa fa-globe mr-1"></i> Xuất xứ</td><td>{{ $product->origin }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Mô tả chi tiết xe -->
                <div class="mt-3 pt-3 border-top">
                    <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-info-circle text-primary mr-1"></i> Mô tả thông số & trang bị xe:</h6>
                    <p class="text-muted small" style="white-space: pre-line; line-height: 1.7;">{{ $product->description ?: 'Mẫu xe được bảo dưỡng định kỳ chính hãng, nội thất sang trọng, trang bị đầy đủ camera 360, định vị GPS, camera hành trình và hệ thống an toàn cao cấp.' }}</p>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: CÁC NÚT CTA ĐẶT DỊCH VỤ -->
        <div class="col-lg-5">
            <div class="service-cta-card p-4 mb-4">
                <h5 class="font-weight-bold text-dark mb-1">
                    <i class="fa fa-concierge-bell text-primary mr-1"></i> Chọn dịch vụ
                </h5>
                @if($product->isSold())
                    <div class="alert alert-dark p-3 my-3 shadow-sm border-dark" style="border-radius: 12px; background-color: #1e293b; color: #fff;">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-danger px-2 py-1 mr-2"><i class="fa fa-handshake-o"></i> ĐÃ BÁN XE</span>
                            <strong class="text-white" style="font-size: 14px;">Xe đã có chủ sở hữu mới</strong>
                        </div>
                        <p class="small text-light mb-2" style="line-height: 1.6;">
                            Mẫu xe này đã được khách hàng <strong>chốt mua thành công</strong> qua hệ thống Showroom đối tác. Mẫu xe đã được gỡ khỏi danh sách hiển thị trên trang chủ và không còn nhận đặt lịch hoặc cho thuê.
                        </p>
                        <div class="small text-light font-italic" style="opacity: 0.85;">
                            <i class="fa fa-info-circle mr-1"></i> Quý khách vui lòng tham khảo các mẫu xe khác đang sẵn sàng trên sàn.
                        </div>
                    </div>

                    <a href="{{ route('welcome') }}" class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm" style="border-radius: 10px;">
                        <i class="fa fa-car mr-1"></i> Khám phá các mẫu xe khác trên sàn &rarr;
                    </a>
                @elseif($product->isServing())
                    <div class="alert alert-danger p-3 my-3 shadow-sm border-danger" style="border-radius: 12px; background-color: #fff5f5;">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-danger px-2 py-1 mr-2"><i class="fa fa-road"></i> XE ĐANG PHỤC VỤ</span>
                            <strong class="text-danger" style="font-size: 14px;">Tạm khóa dịch vụ</strong>
                        </div>
                        <p class="small text-dark mb-2" style="line-height: 1.6;">
                            Mẫu xe này hiện <strong>đang trong chuyến phục vụ khách hàng (đang cho thuê)</strong>. Hệ thống <strong>tạm khóa</strong> các tính năng <strong>Đặt lịch xem xe, Lái thử</strong> và <strong>Thuê xe (tự lái / có tài xế)</strong> cho mẫu xe này để đảm bảo đúng lịch trình.
                        </p>
                        <div class="small text-muted font-italic">
                            <i class="fa fa-info-circle mr-1"></i> Xe sẽ tự động mở lại ngay khi khách hàng trả xe và nhà xe hoàn tất kiểm tra.
                        </div>
                    </div>

                    <!-- NÚT 1: ĐẶT LỊCH XEM XE (TẠM KHÓA) -->
                    <div class="cta-btn-appointment mb-3 d-flex align-items-center" style="opacity: 0.55; cursor: not-allowed; background: #e2e8f0; border-color: #cbd5e0; filter: grayscale(70%);" title="Xe đang phục vụ - Không thể đặt xem xe">
                        <span class="cta-btn-icon bg-secondary text-white"><i class="fa fa-calendar-times-o"></i></span>
                        <div>
                            <div class="text-muted font-weight-bold">1. Đặt lịch xem xe & Lái thử hộ <span class="badge badge-danger ml-1">Tạm khóa</span></div>
                            <small class="text-muted" style="font-weight: 400;">Xe đang trong chuyến phục vụ khách</small>
                        </div>
                        <i class="fa fa-lock ml-auto text-muted"></i>
                    </div>

                    <!-- NÚT 2: THUÊ XE TỰ LÁI (TẠM KHÓA) -->
                    <div class="cta-btn-self-drive mb-3 d-flex align-items-center" style="opacity: 0.55; cursor: not-allowed; background: #e2e8f0; border-color: #cbd5e0; filter: grayscale(70%);" title="Xe đang phục vụ - Không thể thuê xe">
                        <span class="cta-btn-icon bg-secondary text-white"><i class="fa fa-ban"></i></span>
                        <div>
                            <div class="text-muted font-weight-bold">2. Thuê xe tự lái <span class="badge badge-danger ml-1">Tạm khóa</span></div>
                            <small class="text-muted" style="font-weight: 400;">Xe đang trong chuyến phục vụ khách</small>
                        </div>
                        <i class="fa fa-lock ml-auto text-muted"></i>
                    </div>

                    <!-- NÚT 3: THUÊ XE CÓ TÀI XẾ (TẠM KHÓA) -->
                    <div class="cta-btn-driver mb-3 d-flex align-items-center" style="opacity: 0.55; cursor: not-allowed; background: #e2e8f0; border-color: #cbd5e0; filter: grayscale(70%);" title="Xe đang phục vụ - Không thể thuê xe">
                        <span class="cta-btn-icon bg-secondary text-white"><i class="fa fa-ban"></i></span>
                        <div>
                            <div class="text-muted font-weight-bold">3. Thuê xe có tài xế riêng <span class="badge badge-danger ml-1">Tạm khóa</span></div>
                            <small class="text-muted" style="font-weight: 400;">Xe đang trong chuyến phục vụ khách</small>
                        </div>
                        <i class="fa fa-lock ml-auto text-muted"></i>
                    </div>

                    <a href="{{ route('welcome') }}" class="btn btn-outline-primary btn-block font-weight-bold py-2 shadow-sm" style="border-radius: 10px;">
                        <i class="fa fa-car mr-1"></i> Xem các mẫu xe đang sẵn sàng phục vụ khác &rarr;
                    </a>
                @else
                    <p class="small text-muted mb-4">Chọn một trong 3 dịch vụ bên dưới để tiến hành đặt lịch & thanh toán:</p>

                    <!-- NÚT 1: ĐẶT LỊCH XEM XE -->
                    <a href="{{ route('products.booking', ['product' => $product->id, 'action' => 'appointment', 'color' => $selectedColor]) }}"
                       class="cta-btn-appointment mb-3 d-flex align-items-center">
                        <span class="cta-btn-icon"><i class="fa fa-calendar-check-o"></i></span>
                        <div>
                            <div>1. Đặt lịch xem xe & Lái thử hộ</div>
                            <small style="font-weight: 400; opacity: 0.85;">Hoàn toàn miễn phí • Bên thứ ba đặt hộ & cử chuyên viên</small>
                        </div>
                        <i class="fa fa-chevron-right ml-auto"></i>
                    </a>

                    <!-- NÚT 2: THUÊ XE TỰ LÁI -->
                    <a href="{{ route('products.booking', ['product' => $product->id, 'action' => 'self_drive', 'color' => $selectedColor]) }}"
                       class="cta-btn-self-drive mb-3 d-flex align-items-center">
                        <span class="cta-btn-icon"><i class="fa fa-key"></i></span>
                        <div>
                            <div>2. Thuê xe tự lái</div>
                            <small style="font-weight: 400; opacity: 0.85;">Từ <span id="cta-self-price">{{ number_format($initialRentPrice) }}</span> đ/ngày • Bảo lãnh cọc SePay/MoMo</small>
                        </div>
                        <i class="fa fa-chevron-right ml-auto"></i>
                    </a>

                    <!-- NÚT 3: THUÊ XE CÓ TÀI XẾ -->
                    <a href="{{ route('products.booking', ['product' => $product->id, 'action' => 'with_driver', 'color' => $selectedColor]) }}"
                       class="cta-btn-driver mb-3 d-flex align-items-center">
                        <span class="cta-btn-icon"><i class="fa fa-user-circle"></i></span>
                        <div>
                            <div>3. Thuê xe có tài xế riêng</div>
                            <small style="font-weight: 400; opacity: 0.85;">Từ <span id="cta-driver-price">{{ number_format($initialRentPrice + $driverDailyFee) }}</span> đ/ngày • Tài xế thẩm định lý lịch</small>
                        </div>
                        <i class="fa fa-chevron-right ml-auto"></i>
                    </a>
                @endif

                <!-- Thông tin tin cậy -->
                <div class="mt-3 p-3 rounded" style="background: #f8fafc; border: 1px dashed #cbd5e0;">
                    <div class="d-flex flex-column" style="gap: 8px;">
                        <div class="small text-muted"><i class="fa fa-shield text-success mr-1"></i> <strong>Bảo lãnh cọc 100%</strong> — Nền tảng giữ cọc escrow, hoàn tự động</div>
                        <div class="small text-muted"><i class="fa fa-lock text-primary mr-1"></i> <strong>Thanh toán an toàn</strong> — SePay QR · MoMo · Tiền mặt</div>
                        <div class="small text-muted"><i class="fa fa-headphones text-danger mr-1"></i> <strong>Hỗ trợ 24/7</strong> — Hotline & Livechat tức thì</div>
                    </div>
                </div>
            </div>

            <!-- Xe tương tự nếu có -->
            @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="car-detail-card p-3">
                <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-th-large text-primary mr-1"></i> Xe tương tự</h6>
                @foreach($relatedProducts->take(3) as $related)
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <img src="{{ $related->image_url }}" class="rounded mr-3" style="width: 70px; height: 50px; object-fit: cover;"
                         onerror="this.src='https://via.placeholder.com/70x50?text=Xe'">
                    <div class="flex-grow-1">
                        <div class="font-weight-bold small text-dark">{{ $related->name }}</div>
                        <div class="text-danger small font-weight-bold">{{ number_format($related->getColorVariants()->first()->rent_price_per_day ?? 0) }} đ/ngày</div>
                    </div>
                    <a href="{{ route('products.show', $related->id) }}" class="btn btn-outline-primary btn-sm">Xem</a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    let currentDailyPrice = {{ $initialRentPrice }};
    const driverDailyFee = {{ $driverDailyFee }};
    let selectedColorName = '{{ $defaultColor->color_name }}';

    function selectCarColor(colorName, rentPrice, extraPrice, element) {
        currentDailyPrice = rentPrice;
        selectedColorName = colorName;

        // Cập nhật class active trên nút màu
        document.querySelectorAll('.color-variant-card').forEach(el => el.classList.remove('active'));
        if (element) element.classList.add('active');

        // Cập nhật badge & text màu
        document.getElementById('selected-color-badge').innerText = colorName;

        // Cập nhật hiển thị giá niêm yết
        document.getElementById('display-self-rent-price').innerText = new Intl.NumberFormat('vi-VN').format(currentDailyPrice);
        document.getElementById('display-driver-rent-price').innerText = new Intl.NumberFormat('vi-VN').format(currentDailyPrice + driverDailyFee);

        // Cập nhật giá hiển thị trên CTA buttons
        if (document.getElementById('cta-self-price')) {
            document.getElementById('cta-self-price').innerText = new Intl.NumberFormat('vi-VN').format(currentDailyPrice);
        }
        if (document.getElementById('cta-driver-price')) {
            document.getElementById('cta-driver-price').innerText = new Intl.NumberFormat('vi-VN').format(currentDailyPrice + driverDailyFee);
        }

        // Cập nhật URL của các nút CTA để truyền màu đã chọn
        const productId = {{ $product->id }};
        const baseUrl = `/products/${productId}/booking`;
        document.querySelectorAll('a[href*="/booking"]').forEach(link => {
            const url = new URL(link.href);
            url.searchParams.set('color', colorName);
            link.href = url.toString();
        });
    }

    // === GALLERY SLIDER CONTROLLER ===
    const galleryImages = @json($allImages);
    let currentGalleryIdx = 0;

    function switchGalleryImage(index) {
        if (!galleryImages || index < 0 || index >= galleryImages.length) return;
        currentGalleryIdx = index;
        const mainImg = document.getElementById('gallery-main-img');
        if (mainImg) {
            mainImg.style.opacity = '0.35';
            setTimeout(() => {
                mainImg.src = galleryImages[index];
                mainImg.style.opacity = '1';
            }, 120);
        }
        const counter = document.getElementById('gallery-current-idx');
        if (counter) counter.innerText = index + 1;

        // Cập nhật thumbnail đang active
        document.querySelectorAll('.gallery-thumb-item').forEach((item, i) => {
            if (i === index) {
                item.classList.add('active');
                item.style.borderColor = '#007bff';
                item.style.opacity = '1';
                item.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                item.classList.remove('active');
                item.style.borderColor = 'transparent';
                item.style.opacity = '0.65';
            }
        });
    }

    function prevGalleryImage() {
        if (!galleryImages || galleryImages.length <= 1) return;
        const nextIdx = (currentGalleryIdx - 1 + galleryImages.length) % galleryImages.length;
        switchGalleryImage(nextIdx);
    }

    function nextGalleryImage() {
        if (!galleryImages || galleryImages.length <= 1) return;
        const nextIdx = (currentGalleryIdx + 1) % galleryImages.length;
        switchGalleryImage(nextIdx);
    }

    // Hỗ trợ phím mũi tên Trái / Phải để chuyển ảnh
    document.addEventListener('keydown', function(e) {
        if (galleryImages && galleryImages.length > 1) {
            if (e.key === 'ArrowLeft') prevGalleryImage();
            if (e.key === 'ArrowRight') nextGalleryImage();
        }
    });
</script>
@endsection