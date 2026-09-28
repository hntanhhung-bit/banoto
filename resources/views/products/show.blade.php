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

    .service-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 700;
        font-size: 15px;
        color: #6c757d;
        padding: 14px 20px;
        border-radius: 0;
        transition: all 0.25s;
    }
    .service-tabs .nav-link:hover {
        color: #005fb7;
    }
    .service-tabs .nav-link.active {
        color: #005fb7;
        border-bottom-color: #005fb7;
        background-color: transparent;
    }

    .booking-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
    }

    .price-badge-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }

    .calc-summary-box {
        background: #ebf4ff;
        border: 1px solid #bee3f8;
        border-radius: 10px;
        padding: 16px;
        margin-top: 15px;
        margin-bottom: 20px;
    }
    .calc-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 14px;
    }
    .calc-total {
        font-size: 17px;
        font-weight: 800;
        color: #c53030;
        border-top: 1px dashed #cbd5e0;
        padding-top: 8px;
        margin-top: 8px;
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
    @endphp

    <div class="row mt-3">
        <!-- CỘT TRÁI: HÌNH ẢNH & THÔNG TIN CHI TIẾT XE -->
        <div class="col-lg-6 mb-4">
            <div class="car-detail-card p-3 mb-4">
                <div class="position-relative overflow-hidden rounded mb-3" style="background: #f8f9fa;">
                    <img src="{{ $product->image_url }}" class="img-fluid w-100 rounded" style="max-height: 420px; object-fit: cover;" alt="{{ $product->name }}"
                         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=800&auto=format&fit=crop&q=80';">

                    @if($product->rental_status === 'rented')
                        <span class="badge badge-danger position-absolute px-3 py-2" style="top: 15px; right: 15px; font-size: 13px; font-weight: 700;">
                            <i class="fa fa-clock-o"></i> Đang có khách thuê
                        </span>
                    @else
                        <span class="badge badge-success position-absolute px-3 py-2" style="top: 15px; right: 15px; font-size: 13px; font-weight: 700;">
                            <i class="fa fa-check-circle"></i> Sẵn sàng phục vụ
                        </span>
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

                <!-- Mô tả chi tiết xe -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-info-circle text-primary mr-1"></i> Mô tả thông số & trang bị xe:</h6>
                    <p class="text-muted small" style="white-space: pre-line; line-height: 1.7;">{{ $product->description ?: 'Mẫu xe được bảo dưỡng định kỳ chính hãng, nội thất sang trọng, trang bị đầy đủ camera 360, định vị GPS, camera hành trình và hệ thống an toàn cao cấp.' }}</p>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: KHU VỰC ĐẶT DỊCH VỤ 3 TAB -->
        <div class="col-lg-6">
            <div class="car-detail-card">
                <!-- Navigation Tabs cho 3 Dịch vụ -->
                <ul class="nav nav-tabs service-tabs nav-fill bg-light border-bottom" id="serviceTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-appointment-btn" data-toggle="tab" href="#tab-appointment" role="tab" aria-controls="tab-appointment" aria-selected="true">
                            <i class="fa fa-calendar-check-o text-success"></i><br>
                            <span>1. Đặt lịch xem xe</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-self-drive-btn" data-toggle="tab" href="#tab-rental-self" role="tab" aria-controls="tab-rental-self" aria-selected="false">
                            <i class="fa fa-key text-danger"></i><br>
                            <span>2. Thuê xe tự lái</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-driver-btn" data-toggle="tab" href="#tab-rental-driver" role="tab" aria-controls="tab-rental-driver" aria-selected="false">
                            <i class="fa fa-user-circle text-primary"></i><br>
                            <span>3. Thuê người lái</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content p-4" id="serviceTabContent">
                    
                    <!-- TAB 1: ĐẶT LỊCH XEM XE & LÁI THỬ HỘ (BÊN THỨ BA) -->
                    <div class="tab-pane fade show active" id="tab-appointment" role="tabpanel">
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3 p-2 bg-success text-white rounded-circle"><i class="fa fa-calendar-check-o fa-lg"></i></div>
                            <div>
                                <h5 class="font-weight-bold mb-0 text-dark">Đặt lịch xem xe & Lái thử hộ</h5>
                                <small class="text-muted">Dịch vụ Bên thứ ba hoàn toàn miễn phí. Đặt hẹn hộ với Showroom đối tác & cử chuyên viên hỗ trợ.</small>
                            </div>
                        </div>

                        <!-- 3 BƯỚC QUY TRÌNH ĐẶT HỘ -->
                        <div class="p-2 mb-3 bg-light border rounded small">
                            <div class="row text-center text-muted" style="font-size: 11px;">
                                <div class="col-4 border-right">
                                    <strong class="text-success d-block"><i class="fa fa-edit"></i> Bước 1</strong>
                                    Chọn xe & khung giờ
                                </div>
                                <div class="col-4 border-right">
                                    <strong class="text-primary d-block"><i class="fa fa-phone"></i> Bước 2</strong>
                                    Bên thứ 3 book Showroom
                                </div>
                                <div class="col-4">
                                    <strong class="text-info d-block"><i class="fa fa-car"></i> Bước 3</strong>
                                    Đến xem hoặc giao tận nơi
                                </div>
                            </div>
                        </div>

                        @auth
                            <form action="{{ route('appointments.store', $product->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="selected_color" class="global-selected-color-input" value="{{ $defaultColor->color_name }}">

                                <div class="form-group mb-2">
                                    <div class="p-2 bg-light border rounded small d-flex justify-content-between align-items-center">
                                        <span><i class="fa fa-paint-brush text-danger mr-1"></i> Màu xe muốn xem:</span>
                                        <strong class="text-primary current-color-name-text">{{ $defaultColor->color_name }}</strong>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Họ và tên của bạn: <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_name" class="form-control" value="{{ Auth::user()->name }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Số điện thoại liên hệ: <span class="text-danger">*</span></label>
                                        <input type="tel" name="customer_phone" class="form-control" placeholder="0904xxxxxx" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small">Email nhận thông báo:</label>
                                    <input type="email" name="customer_email" class="form-control" value="{{ Auth::user()->email }}">
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Ngày hẹn xem xe: <span class="text-danger">*</span></label>
                                        <input type="date" name="appointment_date" class="form-control" value="{{ date('Y-m-d', strtotime('+1 day')) }}" min="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Khung giờ hẹn: <span class="text-danger">*</span></label>
                                        <select name="appointment_time" class="form-control" required>
                                            <option value="08:30 - 10:00">08:30 - 10:00 (Buổi sáng)</option>
                                            <option value="10:30 - 12:00">10:30 - 12:00 (Buổi trưa)</option>
                                            <option value="14:00 - 15:30" selected>14:00 - 15:30 (Buổi chiều)</option>
                                            <option value="16:00 - 17:30">16:00 - 17:30 (Chiều muộn)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- ĐẶC QUYỀN BÊN THỨ BA: CỬ CHUYÊN VIÊN KỸ THUẬT ĐI CÙNG -->
                                <div class="form-group mb-3">
                                    <div class="custom-control custom-checkbox p-2 bg-light border rounded">
                                        <input type="checkbox" class="custom-control-input" id="with_inspector" name="with_inspector" value="1" checked>
                                        <label class="custom-control-label font-weight-bold text-dark small" for="with_inspector">
                                            <i class="fa fa-user-secret text-success mr-1"></i> Cử Chuyên viên Kỹ thuật độc lập đi cùng xem xe & kiểm tra hộ (Miễn phí)
                                        </label>
                                        <div class="small text-muted pl-4">Chuyên viên độc lập của Nền tảng sẽ đo độ dày sơn, test lỗi động cơ, kiểm tra đâm đụng ngập nước và hỗ trợ thương lượng giá tốt nhất cho bạn.</div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small">Địa điểm hẹn xem xe:</label>
                                    <div class="d-flex flex-column" style="gap: 8px;">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="loc_showroom" name="location_type" value="at_showroom" class="custom-control-input" checked onchange="toggleAppAddress(false)">
                                            <label class="custom-control-label font-weight-bold small" for="loc_showroom">
                                                Tại Showroom đối tác: <strong class="text-primary">{{ $product->partner_showroom->name }}</strong>
                                                <div class="text-muted font-weight-normal">{{ $product->partner_showroom->address }}</div>
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio mt-2">
                                            <input type="radio" id="loc_home" name="location_type" value="at_home" class="custom-control-input" onchange="toggleAppAddress(true)">
                                            <label class="custom-control-label font-weight-bold small" for="loc_home">
                                                Yêu cầu Bên thứ ba điều phối mang xe lái thử tận nhà tôi
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" id="app_address_group" style="display: none;">
                                    <label class="font-weight-bold small">Địa chỉ của bạn:</label>
                                    <input type="text" name="address" class="form-control" placeholder="Nhập số nhà, tên đường, quận/huyện...">
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small">Ghi chú thêm cho bên thứ ba:</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Cần kiểm tra kỹ gầm xe, muốn tư vấn thủ tục sang tên đổi chủ..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-success btn-block py-2 font-weight-bold shadow-sm" style="font-size: 15px; border-radius: 8px;">
                                    <i class="fa fa-calendar-check-o mr-1"></i> GỬI YÊU CẦU ĐẶT HỘ LỊCH XEM XE
                                </button>
                            </form>
                        @else
                            <div class="text-center p-4 bg-light rounded border">
                                <i class="fa fa-lock text-muted fa-3x mb-2"></i>
                                <h6 class="font-weight-bold">Vui lòng đăng nhập để đặt lịch xem xe</h6>
                                <p class="small text-muted mb-3">Tài khoản giúp bạn dễ dàng theo dõi trạng thái lịch hẹn và nhận thông báo từ chuyên viên.</p>
                                <a href="{{ route('login') }}" class="btn btn-primary px-4 font-weight-bold mr-2">Đăng nhập ngay</a>
                                <a href="{{ route('register') }}" class="btn btn-outline-primary px-3 font-weight-bold">Đăng ký</a>
                            </div>
                        @endauth
                    </div>

                    <!-- TAB 2: THUÊ XE TỰ LÁI HỘ (BẢO LÃNH CỌC) -->
                    <div class="tab-pane fade" id="tab-rental-self" role="tabpanel">
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3 p-2 bg-danger text-white rounded-circle"><i class="fa fa-key fa-lg"></i></div>
                            <div>
                                <h5 class="font-weight-bold mb-0 text-dark">Đặt thuê xe tự lái hộ</h5>
                                <small class="text-muted">Bên thứ ba bảo lãnh cọc qua SePay/MoMo, điều phối xe từ Nhà xe đối tác.</small>
                            </div>
                        </div>

                        <!-- CƠ CHẾ BẢO LÃNH BÊN THỨ BA -->
                        <div class="alert alert-info py-2 px-3 small mb-3 border-0" style="background: #e8f4fd; color: #0056b3;">
                            <i class="fa fa-shield text-primary mr-1"></i> <strong>Cơ chế Bảo lãnh bên thứ ba:</strong> Tiền cọc được Nền tảng tạm giữ an toàn, chỉ giải ngân cho Nhà xe đối tác khi bạn nhận bàn giao xe đúng chuẩn hợp đồng. Cam kết hoàn cọc 100% nếu xe không đúng mô tả.
                        </div>

                        @auth
                            <form action="{{ route('rentals.store', $product->id) }}" method="POST" id="rental-self-form">
                                @csrf
                                <input type="hidden" name="rental_type" value="self_drive">
                                <input type="hidden" name="selected_color" class="global-selected-color-input" value="{{ $defaultColor->color_name }}">
                                <input type="hidden" name="pickup_lat" id="self_lat">
                                <input type="hidden" name="pickup_lng" id="self_lng">

                                <div class="form-group mb-2">
                                    <div class="p-2 bg-light border rounded small d-flex justify-content-between align-items-center">
                                        <span><i class="fa fa-paint-brush text-danger mr-1"></i> Màu xe đã chọn:</span>
                                        <strong class="text-primary current-color-name-text">{{ $defaultColor->color_name }}</strong>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Họ và tên người thuê: <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_name" class="form-control" value="{{ Auth::user()->name }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Số điện thoại liên hệ: <span class="text-danger">*</span></label>
                                        <input type="tel" name="customer_phone" class="form-control" placeholder="0904xxxxxx" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Ngày nhận xe: <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" id="self_start_date" class="form-control" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required onchange="calculateSelfRent()">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Ngày trả xe: <span class="text-danger">*</span></label>
                                        <input type="date" name="end_date" id="self_end_date" class="form-control" value="{{ date('Y-m-d', strtotime('+2 days')) }}" min="{{ date('Y-m-d') }}" required onchange="calculateSelfRent()">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small">Hình thức nhận xe:</label>
                                    <div class="d-flex flex-column" style="gap: 8px;">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="pickup_self_showroom" name="pickup_location" value="at_showroom" class="custom-control-input" checked onchange="toggleSelfAddress(false)">
                                            <label class="custom-control-label font-weight-bold small" for="pickup_self_showroom">
                                                Nhận tại Nhà xe đối tác: <strong class="text-danger">{{ $product->partner_showroom->name }}</strong>
                                                <div class="text-muted font-weight-normal">{{ $product->partner_showroom->address }}</div>
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio mt-2">
                                            <input type="radio" id="pickup_self_home" name="pickup_location" value="delivery_home" class="custom-control-input" onchange="toggleSelfAddress(true)">
                                            <label class="custom-control-label font-weight-bold small" for="pickup_self_home">
                                                Yêu cầu Bên thứ ba điều phối giao xe tận nơi cho tôi
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" id="self_address_group" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="font-weight-bold small mb-0">Địa chỉ giao xe:</label>
                                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" onclick="getLocation('self')">
                                            <i class="fa fa-map-marker text-danger"></i> <span id="self_gps_text">Lấy vị trí GPS hiện tại</span>
                                        </button>
                                    </div>
                                    <input type="text" name="customer_address" id="self_address_input" class="form-control" placeholder="Nhập địa chỉ nhận xe...">
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small">Phương thức thanh toán cọc:</label>
                                    <select name="payment_method" class="form-control">
                                        <option value="sepay" selected>Quét mã QR SePay (Chuyển khoản Ngân hàng tự động 24/7)</option>
                                        <option value="momo">Thanh toán cọc bằng Thẻ ATM nội địa (Cổng MoMo)</option>
                                        <option value="cod">Thanh toán cọc trực tiếp khi nhận bàn giao xe</option>
                                    </select>
                                </div>

                                <!-- THÔNG TIN TÀI KHOẢN NHẬN HOÀN CỌC KÝ QUỸ -->
                                <div class="p-3 mb-3 rounded border" style="background: #f8fafc;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="font-weight-bold small text-dark mb-0">
                                            <i class="fa fa-university text-success mr-1"></i> Tài khoản nhận hoàn cọc (Bảo lãnh Escrow):
                                        </label>
                                        <span class="badge badge-success px-2 py-1" style="font-size: 10px;">Hoàn cọc 100% tự động</span>
                                    </div>
                                    <p class="text-muted" style="font-size: 11px; margin-bottom: 8px;">
                                        Khi trả xe đúng chuẩn và kết thúc hợp đồng, Sàn sẽ hoàn chuyển khoản tiền cọc trực tiếp vào tài khoản ngân hàng này của bạn.
                                    </p>
                                    <div class="form-row">
                                        <div class="col-md-5 form-group mb-2">
                                            <label class="small text-muted font-weight-bold mb-1">Ngân hàng:</label>
                                            <select name="refund_bank_name" class="form-control form-control-sm">
                                                <option value="Vietcombank">Vietcombank (VCB)</option>
                                                <option value="MBBank">MB Bank (Quân Đội)</option>
                                                <option value="Techcombank">Techcombank (TCB)</option>
                                                <option value="VPBank">VPBank</option>
                                                <option value="ACB">ACB</option>
                                                <option value="BIDV">BIDV</option>
                                                <option value="VietinBank">VietinBank</option>
                                                <option value="TPBank">TPBank</option>
                                            </select>
                                        </div>
                                        <div class="col-md-7 form-group mb-2">
                                            <label class="small text-muted font-weight-bold mb-1">Số tài khoản ngân hàng:</label>
                                            <input type="text" name="refund_account_number" class="form-control form-control-sm" placeholder="Nhập số tài khoản của bạn...">
                                        </div>
                                        <div class="col-12 form-group mb-0">
                                            <label class="small text-muted font-weight-bold mb-1">Tên chủ tài khoản (In hoa không dấu):</label>
                                            <input type="text" name="refund_account_holder" class="form-control form-control-sm" value="{{ Auth::user()->name }}" placeholder="VD: NGUYEN VAN A">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small">Ghi chú đơn thuê:</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Yêu cầu thêm (rửa xe, lắp ghế trẻ em,...)"></textarea>
                                </div>

                                <!-- Bảng tạm tính chi phí -->
                                <div class="calc-summary-box">
                                    <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-calculator text-primary mr-1"></i> Chi phí tạm tính:</h6>
                                    <div class="calc-row">
                                        <span>Số ngày thuê:</span>
                                        <strong id="self_days_display">2 ngày</strong>
                                    </div>
                                    <div class="calc-row">
                                        <span>Giá thuê màu <strong class="current-color-name-text">{{ $defaultColor->color_name }}</strong>:</span>
                                        <strong id="self_rent_fee_display">{{ number_format($initialRentPrice * 2) }} đ</strong>
                                    </div>
                                    <div class="calc-row">
                                        <span>Tiền cọc xe (hoàn lại khi trả xe):</span>
                                        <strong>{{ number_format($depositAmount) }} đ</strong>
                                    </div>
                                    <div class="calc-row calc-total">
                                        <span>Tổng thanh toán dự kiến:</span>
                                        <span id="self_total_display">{{ number_format(($initialRentPrice * 2) + $depositAmount) }} đ</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-danger btn-block py-2 font-weight-bold shadow-sm" style="font-size: 15px; border-radius: 8px;">
                                    <i class="fa fa-key mr-1"></i> XÁC NHẬN ĐẶT THUÊ XE TỰ LÁI HỘ
                                </button>
                            </form>
                        @else
                            <div class="text-center p-4 bg-light rounded border">
                                <i class="fa fa-lock text-muted fa-3x mb-2"></i>
                                <h6 class="font-weight-bold">Vui lòng đăng nhập để đăng ký thuê xe</h6>
                                <p class="small text-muted mb-3">Đăng nhập giúp tạo hợp đồng điện tử và nhận mã QR chuyển khoản cọc an toàn.</p>
                                <a href="{{ route('login') }}" class="btn btn-primary px-4 font-weight-bold mr-2">Đăng nhập ngay</a>
                                <a href="{{ route('register') }}" class="btn btn-outline-primary px-3 font-weight-bold">Đăng ký</a>
                            </div>
                        @endauth
                    </div>

                    <!-- TAB 3: THUÊ XE KÈM TÀI XẾ HỘ -->
                    <div class="tab-pane fade" id="tab-rental-driver" role="tabpanel">
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3 p-2 bg-primary text-white rounded-circle"><i class="fa fa-user-circle fa-lg"></i></div>
                            <div>
                                <h5 class="font-weight-bold mb-0 text-dark">Đặt thuê xe có tài xế riêng hộ</h5>
                                <small class="text-muted">Tài xế chuyên nghiệp từ mạng lưới đối tác đã thẩm định lý lịch, theo dõi GPS an toàn.</small>
                            </div>
                        </div>

                        <!-- CAM KẾT DỊCH VỤ TRUNG GIAN -->
                        <div class="alert alert-primary py-2 px-3 small mb-3 border-0" style="background: #e7f1ff; color: #004085;">
                            <i class="fa fa-shield text-primary mr-1"></i> <strong>Bảo vệ quyền lợi bởi Bên thứ ba:</strong> Nền tảng điều phối tài xế đúng chuẩn, cam kết xe sạch đẹp, đón đúng giờ. Hỗ trợ đổi tài xế hoặc hoàn cọc 100% nếu có sự cố.
                        </div>

                        @auth
                            <form action="{{ route('rentals.store', $product->id) }}" method="POST" id="rental-driver-form">
                                @csrf
                                <input type="hidden" name="rental_type" value="with_driver">
                                <input type="hidden" name="selected_color" class="global-selected-color-input" value="{{ $defaultColor->color_name }}">
                                <input type="hidden" name="pickup_location" value="delivery_home">
                                <input type="hidden" name="pickup_lat" id="driver_lat">
                                <input type="hidden" name="pickup_lng" id="driver_lng">

                                <div class="form-group mb-2">
                                    <div class="p-2 bg-light border rounded small d-flex justify-content-between align-items-center">
                                        <span><i class="fa fa-paint-brush text-danger mr-1"></i> Màu xe đã chọn:</span>
                                        <strong class="text-primary current-color-name-text">{{ $defaultColor->color_name }}</strong>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Họ và tên khách hàng: <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_name" class="form-control" value="{{ Auth::user()->name }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Số điện thoại liên hệ: <span class="text-danger">*</span></label>
                                        <input type="tel" name="customer_phone" class="form-control" placeholder="0904xxxxxx" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Ngày bắt đầu đón: <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" id="driver_start_date" class="form-control" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required onchange="calculateDriverRent()">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold small">Ngày kết thúc: <span class="text-danger">*</span></label>
                                        <input type="date" name="end_date" id="driver_end_date" class="form-control" value="{{ date('Y-m-d', strtotime('+1 day')) }}" min="{{ date('Y-m-d') }}" required onchange="calculateDriverRent()">
                                    </div>
                                </div>

                                <!-- Điểm đón khách có GPS -->
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="font-weight-bold small mb-0">Địa chỉ đón khách: <span class="text-danger">*</span></label>
                                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" onclick="getLocation('driver')">
                                            <i class="fa fa-map-marker text-danger"></i> <span id="driver_gps_text">Lấy vị trí GPS đón tôi</span>
                                        </button>
                                    </div>
                                    <input type="text" name="customer_address" id="driver_address_input" class="form-control" placeholder="Nhập địa chỉ đón (VD: 123 Cầu Giấy, Hà Nội)..." required>
                                </div>

                                <!-- Điểm đến / Lộ trình -->
                                <div class="form-group">
                                    <label class="font-weight-bold small">Điểm đến / Lộ trình di chuyển dự kiến:</label>
                                    <input type="text" name="destination_address" class="form-control" placeholder="Ví dụ: Đưa đón sân bay Nội Bài / Đi Quảng Ninh 2 ngày 1 đêm...">
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small">Yêu cầu đặc biệt đối với tài xế:</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Tài xế biết tiếng Anh, điềm đạm, hỗ trợ hành lý..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small">Phương thức thanh toán cọc:</label>
                                    <select name="payment_method" class="form-control">
                                        <option value="sepay" selected>Quét mã QR SePay (Chuyển khoản Ngân hàng tự động 24/7)</option>
                                        <option value="momo">Thanh toán cọc bằng Thẻ ATM nội địa (Cổng MoMo)</option>
                                        <option value="cod">Thanh toán trực tiếp khi gặp tài xế</option>
                                    </select>
                                </div>

                                <!-- THÔNG TIN TÀI KHOẢN NHẬN HOÀN CỌC KÝ QUỸ -->
                                <div class="p-3 mb-3 rounded border" style="background: #f8fafc;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="font-weight-bold small text-dark mb-0">
                                            <i class="fa fa-university text-success mr-1"></i> Tài khoản nhận hoàn cọc (Bảo lãnh Escrow):
                                        </label>
                                        <span class="badge badge-success px-2 py-1" style="font-size: 10px;">Hoàn cọc 100% tự động</span>
                                    </div>
                                    <p class="text-muted" style="font-size: 11px; margin-bottom: 8px;">
                                        Khi hoàn tất lộ trình đón trả và kết thúc hợp đồng, Sàn sẽ hoàn chuyển khoản tiền cọc trực tiếp vào tài khoản ngân hàng này của bạn.
                                    </p>
                                    <div class="form-row">
                                        <div class="col-md-5 form-group mb-2">
                                            <label class="small text-muted font-weight-bold mb-1">Ngân hàng:</label>
                                            <select name="refund_bank_name" class="form-control form-control-sm">
                                                <option value="Vietcombank">Vietcombank (VCB)</option>
                                                <option value="MBBank">MB Bank (Quân Đội)</option>
                                                <option value="Techcombank">Techcombank (TCB)</option>
                                                <option value="VPBank">VPBank</option>
                                                <option value="ACB">ACB</option>
                                                <option value="BIDV">BIDV</option>
                                                <option value="VietinBank">VietinBank</option>
                                                <option value="TPBank">TPBank</option>
                                            </select>
                                        </div>
                                        <div class="col-md-7 form-group mb-2">
                                            <label class="small text-muted font-weight-bold mb-1">Số tài khoản ngân hàng:</label>
                                            <input type="text" name="refund_account_number" class="form-control form-control-sm" placeholder="Nhập số tài khoản của bạn...">
                                        </div>
                                        <div class="col-12 form-group mb-0">
                                            <label class="small text-muted font-weight-bold mb-1">Tên chủ tài khoản (In hoa không dấu):</label>
                                            <input type="text" name="refund_account_holder" class="form-control form-control-sm" value="{{ Auth::user()->name }}" placeholder="VD: NGUYEN VAN A">
                                        </div>
                                    </div>
                                </div>

                                <!-- Bảng tạm tính chi phí -->
                                <div class="calc-summary-box">
                                    <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-calculator text-primary mr-1"></i> Chi phí tạm tính (Xe + Tài xế):</h6>
                                    <div class="calc-row">
                                        <span>Thời gian phục vụ:</span>
                                        <strong id="driver_days_display">1 ngày</strong>
                                    </div>
                                    <div class="calc-row">
                                        <span>Phí thuê xe (màu <strong class="current-color-name-text">{{ $defaultColor->color_name }}</strong>):</span>
                                        <strong id="driver_rent_fee_display">{{ number_format($initialRentPrice) }} đ</strong>
                                    </div>
                                    <div class="calc-row">
                                        <span>Phí dịch vụ tài xế ({{ number_format($driverDailyFee) }} đ/ngày):</span>
                                        <strong id="driver_fee_display">{{ number_format($driverDailyFee) }} đ</strong>
                                    </div>
                                    <div class="calc-row">
                                        <span>Tiền cọc giữ xe & tài xế:</span>
                                        <strong>{{ number_format($depositAmount) }} đ</strong>
                                    </div>
                                    <div class="calc-row calc-total">
                                        <span>Tổng chi phí dự kiến:</span>
                                        <span id="driver_total_display">{{ number_format($initialRentPrice + $driverDailyFee + $depositAmount) }} đ</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm" style="font-size: 15px; border-radius: 8px;">
                                    <i class="fa fa-user-circle mr-1"></i> XÁC NHẬN ĐẶT THUÊ XE CÓ TÀI XẾ HỘ
                                </button>
                            </form>
                        @else
                            <div class="text-center p-4 bg-light rounded border">
                                <i class="fa fa-lock text-muted fa-3x mb-2"></i>
                                <h6 class="font-weight-bold">Vui lòng đăng nhập để thuê tài xế</h6>
                                <p class="small text-muted mb-3">Đăng nhập giúp kết nối tài xế và nhận thông tin chuyến đi nhanh chóng.</p>
                                <a href="{{ route('login') }}" class="btn btn-primary px-4 font-weight-bold mr-2">Đăng nhập ngay</a>
                                <a href="{{ route('register') }}" class="btn btn-outline-primary px-3 font-weight-bold">Đăng ký</a>
                            </div>
                        @endauth
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentDailyPrice = {{ $initialRentPrice }};
    const driverDailyFee = {{ $driverDailyFee }};
    const depositAmount = {{ $depositAmount }};

    function selectCarColor(colorName, rentPrice, extraPrice, element) {
        currentDailyPrice = rentPrice;

        // Cập nhật class active trên nút màu
        document.querySelectorAll('.color-variant-card').forEach(el => el.classList.remove('active'));
        if (element) element.classList.add('active');

        // Cập nhật badge & text màu
        document.getElementById('selected-color-badge').innerText = colorName;
        document.querySelectorAll('.current-color-name-text').forEach(el => el.innerText = colorName);
        document.querySelectorAll('.global-selected-color-input').forEach(el => el.value = colorName);

        // Cập nhật hiển thị giá niêm yết
        document.getElementById('display-self-rent-price').innerText = new Intl.NumberFormat('vi-VN').format(currentDailyPrice);
        document.getElementById('display-driver-rent-price').innerText = new Intl.NumberFormat('vi-VN').format(currentDailyPrice + driverDailyFee);

        // Tính lại tiền trên 2 tab thuê xe
        calculateSelfRent();
        calculateDriverRent();
    }

    function toggleAppAddress(show) {
        document.getElementById('app_address_group').style.display = show ? 'block' : 'none';
    }

    function toggleSelfAddress(show) {
        document.getElementById('self_address_group').style.display = show ? 'block' : 'none';
    }

    function calculateSelfRent() {
        const startEl = document.getElementById('self_start_date');
        const endEl = document.getElementById('self_end_date');
        if (!startEl || !endEl) return;

        const start = new Date(startEl.value);
        const end = new Date(endEl.value);
        let days = Math.round((end - start) / (1000 * 60 * 60 * 24));
        if (days <= 0) days = 1;

        const rentFee = days * currentDailyPrice;
        const total = rentFee + depositAmount;

        document.getElementById('self_days_display').innerText = days + ' ngày';
        document.getElementById('self_rent_fee_display').innerText = new Intl.NumberFormat('vi-VN').format(rentFee) + ' đ';
        document.getElementById('self_total_display').innerText = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
    }

    function calculateDriverRent() {
        const startEl = document.getElementById('driver_start_date');
        const endEl = document.getElementById('driver_end_date');
        if (!startEl || !endEl) return;

        const start = new Date(startEl.value);
        const end = new Date(endEl.value);
        let days = Math.round((end - start) / (1000 * 60 * 60 * 24));
        if (days <= 0) days = 1;

        const rentFee = days * currentDailyPrice;
        const driverFee = days * driverDailyFee;
        const total = rentFee + driverFee + depositAmount;

        document.getElementById('driver_days_display').innerText = days + ' ngày';
        document.getElementById('driver_rent_fee_display').innerText = new Intl.NumberFormat('vi-VN').format(rentFee) + ' đ';
        document.getElementById('driver_fee_display').innerText = new Intl.NumberFormat('vi-VN').format(driverFee) + ' đ';
        document.getElementById('driver_total_display').innerText = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
    }

    // Tự động lấy vị trí GPS hiện tại và Reverse Geocode
    function getLocation(type) {
        const btnText = document.getElementById(type + '_gps_text');
        const addrInput = document.getElementById(type + '_address_input');
        const latInput = document.getElementById(type + '_lat');
        const lngInput = document.getElementById(type + '_lng');

        if (!navigator.geolocation) {
            alert('Trình duyệt của bạn không hỗ trợ định vị GPS.');
            return;
        }

        btnText.innerText = 'Đang định vị...';

        navigator.geolocation.getCurrentPosition(
            async function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                latInput.value = lat;
                lngInput.value = lng;

                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
                    const data = await res.json();
                    if (data && data.display_name) {
                        addrInput.value = data.display_name;
                        btnText.innerText = 'Đã lấy vị trí GPS ✓';
                    } else {
                        addrInput.value = `Tọa độ: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        btnText.innerText = 'Đã lấy tọa độ GPS ✓';
                    }
                } catch (e) {
                    addrInput.value = `Tọa độ: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    btnText.innerText = 'Đã lấy tọa độ GPS ✓';
                }
            },
            function(err) {
                alert('Không thể truy cập vị trí: ' + err.message);
                btnText.innerText = 'Thử lại GPS';
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    // Tự động chuyển tab nếu URL có hash
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash;
        if (hash === '#tab-rental-self') {
            $('#tab-self-drive-btn').tab('show');
        } else if (hash === '#tab-rental-driver') {
            $('#tab-driver-btn').tab('show');
        } else if (hash === '#tab-appointment') {
            $('#tab-appointment-btn').tab('show');
        }
    });
</script>
@endsection