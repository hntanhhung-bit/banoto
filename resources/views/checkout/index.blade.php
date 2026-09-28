@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white shadow-sm">
            <li class="breadcrumb-item"><a href="{{ route('welcome') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Giỏ hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page">Đặt hàng & Vận chuyển GHN</li>
        </ol>
    </nav>

    <h2 class="font-weight-bold text-dark mb-4">
        <i class="fa fa-truck text-danger mr-2"></i> THÔNG TIN ĐẶT HÀNG & GIAO HÀNG GHN
    </h2>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <h6 class="font-weight-bold mb-1"><i class="fa fa-exclamation-circle mr-1"></i> Vui lòng kiểm tra lại các thông tin:</h6>
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
        @csrf
        <div class="row">
            <!-- Cột trái: Thông tin khách hàng & Địa chỉ GHN -->
            <div class="col-lg-7 mb-4">
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 12px;">
                    <h5 class="font-weight-bold text-dark border-bottom pb-3 mb-4">
                        <i class="fa fa-user-circle text-primary mr-1"></i> 1. Thông tin người nhận
                    </h5>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Họ và tên người nhận <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $user->name ?? '') }}" placeholder="Nhập họ và tên đầy đủ..." required>
                        @error('customer_name')
                            <small class="text-danger font-weight-bold">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">
                                Số điện thoại liên hệ <span class="text-danger">* (Bắt buộc đủ 10 số)</span>
                            </label>
                            <input type="tel" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" placeholder="Ví dụ: 0987654321..." pattern="[0-9]{10}" maxlength="10" minlength="10" required>
                            <small class="text-muted d-block mt-1">Yêu cầu nhập chính xác 10 chữ số.</small>
                            @error('customer_phone')
                                <small class="text-danger font-weight-bold d-block">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-dark">Địa chỉ Email</label>
                            <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $user->email ?? '') }}" placeholder="Nhập email nhận thông báo...">
                            @error('customer_email')
                                <small class="text-danger font-weight-bold">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Khung Chọn Hình thức nhận xe & Bàn giao xe -->
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 12px;">
                    <h5 class="font-weight-bold text-dark border-bottom pb-3 mb-4">
                        <i class="fa fa-car text-danger mr-1"></i> 2. Hình thức nhận xe & Bàn giao xe
                    </h5>

                    <!-- Chọn Nhận tại Showroom hoặc Gara mang xe qua tận nơi -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <div class="custom-control custom-radio p-3 border rounded h-100 delivery-option-box bg-light border-primary" id="box_delivery_showroom" style="cursor: pointer;">
                                <input type="radio" id="delivery_showroom" name="delivery_type" value="showroom" class="custom-control-input" checked>
                                <label class="custom-control-label font-weight-bold text-dark cursor-pointer d-flex align-items-center" for="delivery_showroom">
                                    <i class="fa fa-building text-primary mr-2" style="font-size: 20px;"></i>
                                    <span>Nhận xe tại Showroom</span>
                                </label>
                                <p class="small text-success font-weight-bold mb-0 mt-2 ml-4">
                                    <i class="fa fa-check-circle"></i> Miễn phí bàn giao xe (0 VNĐ)
                                </p>
                                <p class="small text-muted mb-0 ml-4">
                                    Địa chỉ: 35 Thanh Xuân, Hà Nội (Trụ sở chính Showroom)
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="custom-control custom-radio p-3 border rounded h-100 delivery-option-box" id="box_delivery_garage" style="cursor: pointer;">
                                <input type="radio" id="delivery_garage" name="delivery_type" value="garage_delivery" class="custom-control-input">
                                <label class="custom-control-label font-weight-bold text-dark cursor-pointer d-flex align-items-center" for="delivery_garage">
                                    <i class="fa fa-truck text-danger mr-2" style="font-size: 20px;"></i>
                                    <span>Gara mang xe qua tận nơi</span>
                                </label>
                                <p class="small text-danger font-weight-bold mb-0 mt-2 ml-4">
                                    <i class="fa fa-calculator"></i> Tính cước theo địa chỉ nhận
                                </p>
                                <p class="small text-muted mb-0 ml-4">
                                    Tài xế Gara lái xe hoặc xe chuyên dụng bàn giao tận nhà.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Khu vực nhập địa chỉ giao xe (Khi chọn Gara bàn giao tận nơi) -->
                    <div id="garage_delivery_container" style="display: none;" class="mt-3 p-3 border rounded bg-white">
                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="fa fa-map-marker text-danger mr-1"></i> Nhập địa chỉ để Gara tính cước và bàn giao xe:
                        </h6>
                        <!-- 3 Cấp Tỉnh - Huyện - Xã -->
                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                                <select name="province_id" id="province_select" class="form-control custom-select">
                                    <option value="">-- Đang tải Tỉnh/Thành... --</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark">Quận / Huyện <span class="text-danger">*</span></label>
                                <select name="to_district_id" id="district_select" class="form-control custom-select" disabled>
                                    <option value="">-- Chọn Quận/Huyện --</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-dark">Phường / Xã <span class="text-danger">*</span></label>
                                <select name="to_ward_code" id="ward_select" class="form-control custom-select" disabled>
                                    <option value="">-- Chọn Phường/Xã --</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark">Địa chỉ chi tiết (Số nhà, tên đường, khu vực...) <span class="text-danger">*</span></label>
                            <textarea name="customer_address" id="customer_address" rows="2" class="form-control" placeholder="Ví dụ: 128 Nguyễn Trãi, Thanh Xuân, Hà Nội...">{{ old('customer_address') }}</textarea>
                            @error('customer_address')
                                <small class="text-danger font-weight-bold">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- Hidden fields lưu thông số cước -->
                    <input type="hidden" name="shipping_fee" id="shipping_fee" value="0">
                    <input type="hidden" id="total_price_input" value="{{ $total ?? $totalPrice ?? 0 }}">

                    <div class="form-group mb-0 mt-3">
                        <label class="font-weight-bold text-dark">Ghi chú giao nhận xe:</label>
                        <input type="text" name="note" class="form-control" value="{{ old('note') }}" placeholder="Ví dụ: Bàn giao vào cuối tuần, kiểm tra và rửa xe sạch trước khi bàn giao...">
                    </div>
                </div>

                <!-- Khung Phương thức thanh toán -->
                <div class="card border-0 shadow-sm p-4" style="border-radius: 12px;">
                    <h5 class="font-weight-bold text-dark border-bottom pb-3 mb-4">
                        <i class="fa fa-money text-success mr-1"></i> 3. Phương thức thanh toán
                    </h5>

                    <!-- Cổng MoMo -->
                    <div class="custom-control custom-radio mb-3 p-3 border rounded bg-light" style="border-color: #d82d8b !important;">
                        <input type="radio" id="payment_momo" name="payment_method" value="momo" class="custom-control-input" checked>
                        <label class="custom-control-label font-weight-bold text-dark d-flex align-items-center" for="payment_momo">
                            <span class="badge mr-2 px-2 py-1" style="background-color: #a50064; color: #fff; font-size: 13px;">
                                <i class="fa fa-credit-card mr-1"></i> MoMo ATM
                            </span>
                            <span>Thanh toán bằng Thẻ ATM nội địa (Cổng MoMo Sandbox)</span>
                        </label>
                        <p class="small text-muted mb-0 mt-1 ml-4">
                            Kết nối trực tiếp đến cổng MoMo Sandbox để nhập thẻ ATM nội địa, tự động cập nhật trạng thái đơn và lưu vết đối soát.
                        </p>

                        <!-- Bảng hướng dẫn tài khoản thẻ ATM Test MoMo theo yêu cầu -->
                        <div class="mt-2 ml-4 p-2 rounded border bg-white small text-muted shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-dark"><i class="fa fa-credit-card text-primary mr-1"></i> Danh sách thẻ ATM Test (MoMo Sandbox):</strong>
                                <span class="badge badge-info">OTP bất kỳ</span>
                            </div>
                            <div class="table-responsive m-0">
                                <table class="table table-sm table-bordered m-0 text-center" style="font-size: 11.5px;">
                                    <thead class="bg-light text-dark font-weight-bold">
                                        <tr>
                                            <th style="width: 32px;">No</th>
                                            <th>Tên</th>
                                            <th>Số thẻ</th>
                                            <th>Hạn ghi trên thẻ</th>
                                            <th>OTP</th>
                                            <th>Trường hợp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-success">
                                            <td class="font-weight-bold">1</td>
                                            <td>NGUYEN VAN A</td>
                                            <td><code class="font-weight-bold text-dark">9704 0000 0000 0018</code></td>
                                            <td>12/30</td>
                                            <td>Bất kỳ</td>
                                            <td><strong class="text-success"><i class="fa fa-check-circle"></i> Thành công</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">2</td>
                                            <td>NGUYEN VAN A</td>
                                            <td><code class="font-weight-bold text-muted">9704 0000 0000 0026</code></td>
                                            <td>12/30</td>
                                            <td>Bất kỳ</td>
                                            <td><span class="badge badge-danger">Thẻ khóa</span></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">3</td>
                                            <td>NGUYEN VAN A</td>
                                            <td><code class="font-weight-bold text-muted">9704 0000 0000 0034</code></td>
                                            <td>12/30</td>
                                            <td>Bất kỳ</td>
                                            <td><span class="badge badge-warning text-dark">Không đủ tiền</span></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">4</td>
                                            <td>NGUYEN VAN A</td>
                                            <td><code class="font-weight-bold text-muted">9704 0000 0000 0042</code></td>
                                            <td>12/30</td>
                                            <td>Bất kỳ</td>
                                            <td><span class="badge badge-secondary">Hạn mức thẻ</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>



                    <!-- Cổng SePay QR Code Tự Động -->
                    <div class="custom-control custom-radio mb-3 p-3 border rounded bg-light" style="border-color: #005fb7 !important;">
                        <input type="radio" id="payment_sepay" name="payment_method" value="sepay" class="custom-control-input">
                        <label class="custom-control-label font-weight-bold text-dark d-flex align-items-center" for="payment_sepay">
                            <span class="badge badge-primary mr-2 px-2 py-1" style="font-size: 13px;">
                                <i class="fa fa-qrcode mr-1"></i> SePay QR
                            </span>
                            <span>Thanh toán quét mã QR Ngân hàng (SePay Tự Động 24/7)</span>
                        </label>
                        <p class="small text-muted mb-0 mt-1 ml-4">
                            Quét mã VietQR bằng mọi ứng dụng ngân hàng (TPBank, Vietcombank, MB, Techcombank...). Hệ thống tự động xác nhận sau 3 giây.
                        </p>
                    </div>

                    <!-- COD / Nhận tại Showroom hoặc Bàn giao -->
                    <div class="custom-control custom-radio p-3 border rounded bg-light">
                        <input type="radio" id="payment_cod" name="payment_method" value="cod" class="custom-control-input">
                        <label class="custom-control-label font-weight-bold text-dark d-flex align-items-center" for="payment_cod">
                            <i class="fa fa-handshake-o text-success mr-2" style="font-size: 20px;"></i>
                            <span>Thanh toán khi nhận bàn giao xe (Tiền mặt / Cà thẻ POS tại chỗ)</span>
                        </label>
                        <p class="small text-muted mb-0 mt-1 ml-4">
                            Thanh toán trực tiếp cho nhân viên đại lý khi nhận bàn giao xe tại Showroom hoặc tại nhà.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Tóm tắt đơn hàng & Phí bàn giao xe -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm p-4 sticky-top" style="top: 90px; border-radius: 12px;">
                    <h5 class="font-weight-bold text-dark border-bottom pb-3 mb-3">
                        <i class="fa fa-shopping-bag text-primary mr-1"></i> Đơn hàng ({{ count($cart) }} sản phẩm)
                    </h5>

                    <!-- Danh sách sản phẩm tóm tắt -->
                    <div class="order-items-list mb-3" style="max-height: 280px; overflow-y: auto;">
                        @foreach($cart as $id => $item)
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    @if(!empty($item['image']))
                                        <img src="{{ asset('images/'.$item['image']) }}" class="rounded mr-2" style="width: 55px; height: 40px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $item['name'] }}</div>
                                        <small class="text-muted">SL: <strong>x{{ $item['quantity'] }}</strong> | Màu: <strong class="text-dark">{{ $item['color'] ?? 'Trắng' }}</strong> | {{ is_array($item['category'] ?? null) ? ($item['category']['name'] ?? 'N/A') : ($item['category'] ?? 'N/A') }}</small>
                                    </div>
                                </div>
                                <div class="font-weight-bold text-danger" style="font-size: 14px;">
                                    {{ number_format($item['price'] * $item['quantity']) }} đ
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Bảng tính tiền & Cước bàn giao xe -->
                    @php
                        $subtotalCalc = $total ?? $totalPrice ?? 0;
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tiền xe tạm tính:</span>
                        <strong class="text-dark">{{ number_format($subtotalCalc) }} đ</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Cước bàn giao xe:</span>
                        <strong id="shipping_fee_text" class="text-success font-weight-bold">Miễn phí (Nhận tại Showroom)</strong>
                    </div>

                    <hr class="mt-0">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="font-weight-bold" style="font-size: 16px;">TỔNG THANH TOÁN:</span>
                        <h4 id="final_total_text" class="font-weight-bold text-danger mb-0">{{ number_format($subtotalCalc) }} VNĐ</h4>
                    </div>

                    <button type="submit" id="btn-submit-order" class="btn btn-danger btn-block py-3 font-weight-bold text-uppercase shadow" style="font-size: 16px; border-radius: 8px;">
                        <i class="fa fa-check-circle mr-1"></i> XÁC NHẬN ĐẶT XE & THANH TOÁN
                    </button>

                    <div class="text-center mt-3">
                        <a href="{{ route('cart.index') }}" class="small font-weight-bold text-muted">
                            <i class="fa fa-arrow-left"></i> Quay lại chỉnh sửa giỏ hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- SCRIPT: XỬ LÝ HÌNH THỨC NHẬN XE & TÍNH CƯỚC BÀN GIAO GARA -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const deliveryShowroomRadio = document.getElementById('delivery_showroom');
    const deliveryGarageRadio = document.getElementById('delivery_garage');
    const boxDeliveryShowroom = document.getElementById('box_delivery_showroom');
    const boxDeliveryGarage = document.getElementById('box_delivery_garage');
    const garageDeliveryContainer = document.getElementById('garage_delivery_container');

    const provinceSelect = document.getElementById('province_select');
    const districtSelect = document.getElementById('district_select');
    const wardSelect = document.getElementById('ward_select');
    const addressTextarea = document.getElementById('customer_address');

    const shippingFeeText = document.getElementById('shipping_fee_text');
    const finalTotalText = document.getElementById('final_total_text');
    const totalPriceInput = document.getElementById('total_price_input');
    const shippingFeeInput = document.getElementById('shipping_fee');
    const phoneInput = document.getElementById('customer_phone');
    const checkoutForm = document.getElementById('checkout-form');

    const districtsUrl = "{{ route('locations.districts', ['provinceId' => '__PROVINCE__']) }}";
    const wardsUrl = "{{ route('locations.wards', ['districtId' => '__DISTRICT__']) }}";

    // Lấy tiền xe tạm tính từ input ẩn
    const subtotal = parseInt(totalPriceInput ? totalPriceInput.value : 0) || 0;
    let calculatedShippingFee = 0;

    // Chuyển đổi qua lại giữa Nhận tại Showroom và Gara bàn giao tận nơi
    function handleDeliveryTypeChange() {
        if (deliveryShowroomRadio.checked) {
            boxDeliveryShowroom.classList.add('border-primary', 'bg-light');
            boxDeliveryGarage.classList.remove('border-danger', 'bg-light');
            garageDeliveryContainer.style.display = 'none';

            if (addressTextarea) addressTextarea.removeAttribute('required');
            if (provinceSelect) provinceSelect.removeAttribute('required');
            if (districtSelect) districtSelect.removeAttribute('required');
            if (wardSelect) wardSelect.removeAttribute('required');

            updateTotals(0, true);
        } else {
            boxDeliveryGarage.classList.add('border-danger', 'bg-light');
            boxDeliveryShowroom.classList.remove('border-primary', 'bg-light');
            garageDeliveryContainer.style.display = 'block';

            if (addressTextarea) addressTextarea.setAttribute('required', 'required');
            if (provinceSelect) provinceSelect.setAttribute('required', 'required');
            if (districtSelect) districtSelect.setAttribute('required', 'required');
            if (wardSelect) wardSelect.setAttribute('required', 'required');

            if (calculatedShippingFee > 0) {
                updateTotals(calculatedShippingFee, false);
            } else {
                shippingFeeText.innerHTML = '<span class="text-danger font-weight-bold">Vui lòng chọn địa chỉ để tính cước</span>';
                updateTotals(0, false);
            }
        }
    }

    deliveryShowroomRadio.addEventListener('change', handleDeliveryTypeChange);
    deliveryGarageRadio.addEventListener('change', handleDeliveryTypeChange);

    boxDeliveryShowroom.addEventListener('click', function () {
        deliveryShowroomRadio.checked = true;
        handleDeliveryTypeChange();
    });

    boxDeliveryGarage.addEventListener('click', function () {
        deliveryGarageRadio.checked = true;
        handleDeliveryTypeChange();
    });

    // 1. Tải danh sách Tỉnh/Thành phố
    fetch("{{ route('locations.provinces') }}")
        .then(res => res.json())
        .then(res => {
            if (res.data && res.data.length > 0) {
                let options = '<option value="">-- Chọn Tỉnh/Thành --</option>';
                res.data.forEach(p => {
                    options += `<option value="${p.ProvinceID}">${p.ProvinceName}</option>`;
                });
                provinceSelect.innerHTML = options;
            } else {
                provinceSelect.innerHTML = '<option value="">-- Không tải được tỉnh/thành --</option>';
            }
        })
        .catch(err => {
            console.error("Lỗi load tỉnh thành:", err);
            provinceSelect.innerHTML = '<option value="">-- Lỗi kết nối máy chủ --</option>';
        });

    // 2. Khi chọn Tỉnh -> Tải Quận/Huyện
    provinceSelect.addEventListener('change', function () {
        districtSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        districtSelect.disabled = true;
        wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
        wardSelect.disabled = true;
        calculatedShippingFee = 0;
        updateTotals(0, deliveryShowroomRadio.checked);

        if (!this.value) return;

        fetch(districtsUrl.replace('__PROVINCE__', this.value))
            .then(res => res.json())
            .then(res => {
                if (res.data && res.data.length > 0) {
                    let options = '<option value="">-- Chọn Quận/Huyện --</option>';
                    res.data.forEach(d => {
                        options += `<option value="${d.DistrictID}">${d.DistrictName}</option>`;
                    });
                    districtSelect.innerHTML = options;
                    districtSelect.disabled = false;
                } else {
                    districtSelect.innerHTML = '<option value="">-- Không tải được quận/huyện --</option>';
                }
            })
            .catch(err => {
                console.error("Lỗi load quận huyện:", err);
                districtSelect.innerHTML = '<option value="">-- Lỗi kết nối máy chủ --</option>';
            });
    });

    // 3. Khi chọn Quận/Huyện -> Tải Phường/Xã
    districtSelect.addEventListener('change', function () {
        wardSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        wardSelect.disabled = true;
        calculatedShippingFee = 0;
        updateTotals(0, deliveryShowroomRadio.checked);

        if (!this.value) return;

        fetch(wardsUrl.replace('__DISTRICT__', this.value))
            .then(res => res.json())
            .then(res => {
                if (res.data && res.data.length > 0) {
                    let options = '<option value="">-- Chọn Phường/Xã --</option>';
                    res.data.forEach(w => {
                        options += `<option value="${w.WardCode}">${w.WardName}</option>`;
                    });
                    wardSelect.innerHTML = options;
                    wardSelect.disabled = false;
                } else {
                    wardSelect.innerHTML = '<option value="">-- Không tải được phường/xã --</option>';
                }
            })
            .catch(err => {
                console.error("Lỗi load phường xã:", err);
                wardSelect.innerHTML = '<option value="">-- Lỗi kết nối máy chủ --</option>';
            });
    });

    // 4. Khi chọn Phường/Xã -> Tính cước bàn giao xe tận nơi của Gara
    wardSelect.addEventListener('change', function () {
        if (!this.value || !districtSelect.value) return;

        shippingFeeText.innerText = 'Đang tính cước bàn giao...';

        fetch("{{ route('locations.fee') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                to_district_id: districtSelect.value,
                to_ward_code: this.value
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.code === 200 && res.data) {
                calculatedShippingFee = parseInt(res.data.total) || 0;
                if (!deliveryShowroomRadio.checked) {
                    updateTotals(calculatedShippingFee, false);
                }
            } else {
                calculatedShippingFee = 0;
                if (!deliveryShowroomRadio.checked) {
                    shippingFeeText.innerText = '0 VNĐ';
                    updateTotals(0, false);
                }
            }
        })
        .catch(err => {
            console.error("Lỗi tính phí:", err);
            if (!deliveryShowroomRadio.checked) {
                shippingFeeText.innerText = '0 VNĐ';
                updateTotals(0, false);
            }
        });
    });

    function updateTotals(fee, isShowroom) {
        if (isShowroom) {
            shippingFeeText.innerHTML = '<span class="text-success font-weight-bold"><i class="fa fa-check-circle"></i> Miễn phí (Nhận tại Showroom)</span>';
            if (shippingFeeInput) shippingFeeInput.value = 0;
            finalTotalText.innerText = new Intl.NumberFormat('vi-VN').format(subtotal) + ' VNĐ';
        } else {
            if (fee > 0) {
                shippingFeeText.innerText = new Intl.NumberFormat('vi-VN').format(fee) + ' VNĐ';
            }
            if (shippingFeeInput) shippingFeeInput.value = fee;
            const finalAmount = subtotal + fee;
            finalTotalText.innerText = new Intl.NumberFormat('vi-VN').format(finalAmount) + ' VNĐ';
        }
    }

    // Kiểm tra số điện thoại hợp lệ khi gửi form
    if (checkoutForm && phoneInput) {
        checkoutForm.addEventListener('submit', function (e) {
            const phoneVal = phoneInput.value.trim();
            const phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(phoneVal)) {
                e.preventDefault();
                alert('Lỗi: Số điện thoại liên hệ phải có đúng 10 chữ số (Ví dụ: 0987654321)!');
                phoneInput.focus();
                return false;
            }

            if (deliveryGarageRadio.checked) {
                if (!provinceSelect.value || !districtSelect.value || !wardSelect.value) {
                    e.preventDefault();
                    alert('Vui lòng chọn đầy đủ Tỉnh/Thành, Quận/Huyện và Phường/Xã để Gara tính cước và giao xe!');
                    return false;
                }
            }
        });
    }

    // Khởi tạo trạng thái ban đầu
    handleDeliveryTypeChange();
});
</script>
@endsection
