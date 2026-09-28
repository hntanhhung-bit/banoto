@extends('partner.layout')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h3 class="font-weight-bold text-dark mb-1">
            <i class="fa fa-plus-circle text-success mr-2"></i> THÊM XE MỚI VÀO SHOWROOM
        </h3>
        <p class="text-muted small mb-0">Đăng tải phương tiện của showroom bạn lên sàn để đón khách thuê xe và khách đặt lịch hẹn xem xe</p>
    </div>
    <a href="{{ route('partner.cars') }}" class="btn btn-outline-secondary font-weight-bold btn-sm">
        <i class="fa fa-arrow-left mr-1"></i> Quay lại đội xe
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body p-4">
                <form action="{{ route('partner.cars.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- 1. THÔNG TIN CƠ BẢN CỦA XE -->
                    <h5 class="font-weight-bold text-primary border-bottom pb-2 mb-3">
                        <i class="fa fa-car mr-1"></i> 1. Thông Tin Mẫu Xe & Hãng Sản Xuất
                    </h5>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="font-weight-bold small">Tên mẫu xe <span class="text-danger">*</span>:</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="VD: Mazda CX-5 2.0L Premium 2024" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="font-weight-bold small">Hãng xe / Phân khúc <span class="text-danger">*</span>:</label>
                                <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn hãng xe --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold small text-danger">Biển số xe (BKS) <span class="text-danger">*</span>:</label>
                                <input type="text" name="car_plate" class="form-control font-weight-bold @error('car_plate') is-invalid @enderror" 
                                       placeholder="VD: 30K-888.88" value="{{ old('car_plate') }}" required>
                                @error('car_plate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold small">Năm SX / Đời xe <span class="text-danger">*</span>:</label>
                                <input type="number" name="car_year" class="form-control @error('car_year') is-invalid @enderror" 
                                       placeholder="VD: 2023" value="{{ old('car_year', date('Y')) }}" min="2000" max="{{ date('Y') + 1 }}" required>
                                @error('car_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold small">Màu sắc chủ đạo:</label>
                                <input type="text" name="color" class="form-control" 
                                       placeholder="VD: Trắng ngọc trai, Đen..." value="{{ old('color', 'Trắng ngọc trai') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold small">Số lượng xe:</label>
                                <input type="number" name="quantity" class="form-control" 
                                       value="{{ old('quantity', 1) }}" min="1">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small text-primary">Tình trạng kỹ thuật, Đăng kiểm & Bảo hiểm <span class="text-danger">*</span>:</label>
                        <textarea name="car_condition" rows="2" class="form-control @error('car_condition') is-invalid @enderror" required
                                  placeholder="VD: ODO 25,000 km. Hạn đăng kiểm đến 12/2026. Bảo hiểm vật chất 2 chiều. Xe bảo dưỡng định kỳ chính hãng, lốp xe mới 95%...">{{ old('car_condition') }}</textarea>
                        <small class="text-muted">Thông tin này giúp Ban Quản Trị thẩm định chất lượng phương tiện trước khi duyệt xe lên sàn.</small>
                        @error('car_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 2. BẢNG GIÁ DỊCH VỤ & KÝ QUỸ -->
                    <h5 class="font-weight-bold text-primary border-bottom pb-2 mb-3 mt-4">
                        <i class="fa fa-money mr-1"></i> 2. Giá Dịch Vụ Thuê Xe & Bảo Lãnh Cọc
                    </h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold small text-danger">Giá thuê tự lái (VNĐ / ngày) <span class="text-danger">*</span>:</label>
                                <input type="number" name="rent_price_per_day" class="form-control font-weight-bold text-danger @error('rent_price_per_day') is-invalid @enderror" 
                                       placeholder="VD: 800000" value="{{ old('rent_price_per_day', 800000) }}" required min="0">
                                <small class="text-muted">Bạn nhận về 90% doanh thu thuê xe.</small>
                                @error('rent_price_per_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold small text-dark">Tiền cọc thế chân giữ xe (VNĐ) <span class="text-danger">*</span>:</label>
                                <input type="number" name="rental_deposit" class="form-control font-weight-bold @error('rental_deposit') is-invalid @enderror" 
                                       placeholder="VD: 5000000" value="{{ old('rental_deposit', 5000000) }}" required min="0">
                                <small class="text-muted">Được Sàn ký quỹ bảo lãnh 100% an toàn.</small>
                                @error('rental_deposit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold small text-primary">Phí tài xế riêng (VNĐ / ngày nếu có):</label>
                                <input type="number" name="driver_price_per_day" class="form-control font-weight-bold text-primary" 
                                       placeholder="VD: 500000" value="{{ old('driver_price_per_day', 500000) }}" min="0">
                                <small class="text-muted">Để 0 nếu Showroom chỉ cho thuê tự lái.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Giá bán xe niêm yết (VNĐ, tùy chọn nếu bán xe):</label>
                                <input type="number" name="price" class="form-control" 
                                       placeholder="VD: 850000000" value="{{ old('price') }}" min="0">
                                <small class="text-muted">Khi khách chốt mua qua lịch hẹn xem xe, bạn trích 1% hoa hồng môi giới cho sàn.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold small">Tải lên hình ảnh xe:</label>
                                <input type="file" name="image" class="form-control-file border p-1 rounded" accept="image/*" onchange="previewCarImage(event)">
                                <small class="text-muted d-block mt-1">Định dạng JPG, PNG, WEBP tối đa 4MB.</small>
                            </div>
                            <div id="imagePreviewBox" class="mt-2" style="display: none;">
                                <img id="previewImg" src="#" alt="Preview" class="rounded shadow-sm" style="max-height: 120px; object-fit: cover;">
                            </div>
                        </div>
                    </div>

                    <!-- 3. MÔ TẢ & TIỆN NGHI -->
                    <h5 class="font-weight-bold text-primary border-bottom pb-2 mb-3 mt-4">
                        <i class="fa fa-info-circle mr-1"></i> 3. Mô Tả Chi Tiết & Trang Bị Của Xe
                    </h5>

                    <div class="form-group">
                        <label class="font-weight-bold small">Mô tả tình trạng xe, tiện nghi và điều kiện giao nhận:</label>
                        <textarea name="description" rows="4" class="form-control" 
                                  placeholder="VD: Xe đời 2024 mới 99%, đầy đủ camera hành trình, cảm biến áp suất lốp, bảo dưỡng định kỳ chính hãng. Hỗ trợ giao nhận tận nơi hoặc tại Showroom...">{{ old('description') }}</textarea>
                    </div>

                    <div class="p-3 bg-light rounded border mb-4 small text-muted">
                        <i class="fa fa-shield text-success mr-1"></i>
                        <strong>Cam kết sở hữu:</strong> Mẫu xe này sẽ được gán tự động vào <strong>{{ Auth::user()->company_name ?: Auth::user()->name }}</strong>. Mọi đơn thuê và lịch hẹn xem xe sẽ chuyển thẳng vào bảng điều khiển đối tác của bạn.
                    </div>

                    <!-- NÚT SUBMIT -->
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('partner.cars') }}" class="btn btn-outline-secondary font-weight-bold">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-success font-weight-bold px-4 py-2 shadow-sm">
                            <i class="fa fa-check mr-1"></i> LƯU & NIÊM YẾT VÀO SHOWROOM
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewCarImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('previewImg');
        output.src = reader.result;
        document.getElementById('imagePreviewBox').style.display = 'block';
    };
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>
@endsection
