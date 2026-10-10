@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 mt-3 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-pencil-square-o text-primary mr-2"></i> CHỈNH SỬA THÔNG TIN XE #{{ $product->id }}
            </h3>
            <p class="text-muted small mb-0">Cập nhật giá thuê, tiền cọc, thông số kỹ thuật và hình ảnh của phương tiện</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary font-weight-bold btn-sm">
            <i class="fa fa-arrow-left mr-1"></i> Quay lại kho xe
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <strong>Khoan đã!</strong> Dữ liệu cập nhật đang có lỗi:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-body p-4">
                    @if($product->partner_id)
                        @if($product->approval_status === 'pending')
                            <div class="alert alert-warning border-warning">
                                <i class="fa fa-clock-o text-danger mr-1"></i>
                                <strong>Phương tiện của Đối tác đang chờ duyệt:</strong> Mẫu xe thuộc Showroom <strong>{{ $product->partner->company_name ?? $product->partner->name ?? 'Đối tác' }}</strong> đang chờ Ban Quản Trị thẩm định.
                            </div>
                        @elseif($product->approval_status === 'rejected')
                            <div class="alert alert-danger border-danger">
                                <i class="fa fa-times-circle mr-1"></i>
                                <strong>Phương tiện đã bị từ chối phê duyệt:</strong> {{ $product->admin_feedback ?: 'Chưa đạt tiêu chuẩn kiểm định.' }}
                            </div>
                        @elseif($product->approval_status === 'approved')
                            <div class="alert alert-success border-success small">
                                <i class="fa fa-check-circle mr-1"></i>
                                <strong>Xe đã được phê duyệt hợp chuẩn:</strong> Thuộc Showroom <strong>{{ $product->partner->company_name ?? $product->partner->name ?? 'Đối tác' }}</strong>, đang hiển thị đón khách trên sàn.
                            </div>
                        @endif
                    @endif

                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- 1. THÔNG TIN MẪU XE & HÃNG SẢN XUẤT -->
                        <h5 class="font-weight-bold text-primary border-bottom pb-2 mb-3">
                            <i class="fa fa-car mr-1"></i> 1. Thông Tin Mẫu Xe & Hãng Sản Xuất
                        </h5>

                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Tên mẫu xe <span class="text-danger">*</span>:</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Hãng xe / Phân khúc <span class="text-danger">*</span>:</label>
                                    <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn hãng xe --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-info">Đơn vị sở hữu / Showroom:</label>
                                    <select name="partner_id" class="form-control font-weight-bold">
                                        <option value="" {{ empty($product->partner_id) ? 'selected' : '' }}>⭐ Trực thuộc Sàn AutoCar</option>
                                        @if(isset($partners))
                                            @foreach($partners as $partner)
                                                <option value="{{ $partner->id }}" {{ old('partner_id', $product->partner_id) == $partner->id ? 'selected' : '' }}>
                                                    🏢 {{ $partner->company_name ?: $partner->name }} ({{ $partner->email }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <small class="text-muted">Gán cho Showroom hoặc Sàn quản lý.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-danger">Biển số xe (BKS) <span class="text-danger">*</span>:</label>
                                    <input type="text" name="car_plate" class="form-control font-weight-bold @error('car_plate') is-invalid @enderror" 
                                           placeholder="VD: 30K-888.88" value="{{ old('car_plate', $product->car_plate) }}" required>
                                    @error('car_plate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Năm SX / Đời xe <span class="text-danger">*</span>:</label>
                                    <input type="number" name="car_year" class="form-control @error('car_year') is-invalid @enderror" 
                                           placeholder="VD: 2023" value="{{ old('car_year', $product->car_year ?: date('Y')) }}" min="2000" max="{{ date('Y') + 1 }}" required>
                                    @error('car_year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Màu sắc chủ đạo:</label>
                                    <input type="text" name="color" class="form-control" 
                                           value="{{ old('color', $product->color ?: 'Trắng ngọc trai') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-primary">Tình trạng kỹ thuật, Đăng kiểm & Bảo hiểm <span class="text-danger">*</span>:</label>
                                    <textarea name="car_condition" rows="2" class="form-control @error('car_condition') is-invalid @enderror" required
                                              placeholder="VD: ODO 25,000 km. Hạn đăng kiểm đến 12/2026. Bảo hiểm vật chất 2 chiều. Xe bảo dưỡng định kỳ chính hãng...">{{ old('car_condition', $product->car_condition) }}</textarea>
                                    @error('car_condition')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Trạng thái xe hiện tại:</label>
                                    <select name="rental_status" class="form-control font-weight-bold">
                                        <option value="available" {{ old('rental_status', $product->rental_status) === 'available' ? 'selected' : '' }}>✓ Sẵn sàng nhận khách (Rảnh)</option>
                                        <option value="rented" {{ old('rental_status', $product->rental_status) === 'rented' ? 'selected' : '' }}>🚗 Đang có khách thuê</option>
                                        <option value="maintenance" {{ old('rental_status', $product->rental_status) === 'maintenance' ? 'selected' : '' }}>🔧 Đang bảo dưỡng / Tạm dừng</option>
                                        <option value="sold" {{ old('rental_status', $product->rental_status) === 'sold' ? 'selected' : '' }}>🏁 Đã bán thành công</option>
                                    </select>
                                </div>
                            </div>
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
                                           value="{{ old('rent_price_per_day', (int)$product->rent_price_per_day) }}" required min="0">
                                    @error('rent_price_per_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-dark">Tiền cọc thế chân giữ xe (VNĐ) <span class="text-danger">*</span>:</label>
                                    <input type="number" name="rental_deposit" class="form-control font-weight-bold @error('rental_deposit') is-invalid @enderror" 
                                           value="{{ old('rental_deposit', (int)$product->rental_deposit) }}" required min="0">
                                    @error('rental_deposit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-primary">Phí tài xế riêng (VNĐ / ngày):</label>
                                    <input type="number" name="driver_price_per_day" class="form-control font-weight-bold text-primary" 
                                           value="{{ old('driver_price_per_day', (int)$product->driver_price_per_day) }}" min="0">
                                    <small class="text-muted">Để 0 nếu chỉ cung cấp xe tự lái.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Giá bán xe niêm yết (VNĐ, tùy chọn nếu bán xe):</label>
                                    <input type="number" name="price" class="form-control" 
                                           value="{{ old('price', !is_null($product->price) ? (int)$product->price : '') }}" min="0">
                                    <small class="text-muted">Để trống hoặc 0 nếu xe này chỉ dành riêng cho thuê.</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold small">1. Thay đổi ảnh đại diện xe:</label>
                                    <input type="file" name="image" class="form-control-file border p-1 rounded" accept="image/*" onchange="previewCarImage(event)">
                                    <small class="text-muted d-block mt-1">Chọn ảnh mới nếu muốn thay đổi. Để trống nếu giữ nguyên.</small>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    @if($product->image)
                                        <div class="mr-3 text-center">
                                            <small class="text-muted d-block">Ảnh hiện tại:</small>
                                            <img src="{{ $product->image_url }}" class="rounded shadow-sm" style="height: 75px; object-fit: cover;"
                                                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=200&auto=format&fit=crop&q=60';">
                                        </div>
                                    @endif
                                    <div id="imagePreviewBox" style="display: none;" class="text-center">
                                        <small class="text-success d-block font-weight-bold">Ảnh mới:</small>
                                        <img id="previewImg" src="#" alt="Preview" class="rounded shadow-sm" style="height: 75px; object-fit: cover;">
                                    </div>
                                </div>

                                <!-- Quản lý ảnh gallery hiện có -->
                                @php
                                    $adminGallery = is_array($product->gallery_images) ? $product->gallery_images : (json_decode($product->gallery_images, true) ?: []);
                                @endphp
                                @if(!empty($adminGallery))
                                    <div class="mb-3 p-2 bg-light rounded border">
                                        <label class="font-weight-bold small text-dark d-block mb-1">Ảnh chi tiết đang có (Tick để xóa):</label>
                                        <div class="d-flex flex-wrap" style="gap: 8px;">
                                            @foreach($adminGallery as $aGImg)
                                                @php
                                                    $aGUrl = filter_var($aGImg, FILTER_VALIDATE_URL) ? $aGImg : asset('images/' . $aGImg);
                                                @endphp
                                                <div class="border rounded bg-white p-1 text-center shadow-sm" style="width: 85px;">
                                                    <img src="{{ $aGUrl }}" style="width: 75px; height: 50px; object-fit: cover;" class="rounded"
                                                         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=200&auto=format&fit=crop&q=60';">
                                                    <div class="form-check mt-1">
                                                        <input type="checkbox" name="remove_gallery[]" value="{{ $aGImg }}" class="form-check-input" id="a_rm_{{ md5($aGImg) }}">
                                                        <label class="form-check-label text-danger small font-weight-bold" for="a_rm_{{ md5($aGImg) }}" style="font-size: 11px;">Xóa</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group mb-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="font-weight-bold small mb-0 text-success">
                                            <i class="fa fa-images mr-1"></i> 2. Tải thêm ảnh vào bộ sưu tập:
                                        </label>
                                        <span class="badge badge-success" id="admin_edit_gallery_count" style="display: none;">0 ảnh</span>
                                    </div>
                                    <input type="file" name="gallery_images[]" id="admin_edit_gallery_input" multiple class="form-control-file border p-1 rounded mt-1" accept="image/*">
                                    <small class="text-muted d-block mt-1">Giữ <strong>Ctrl</strong> hoặc <strong>Shift</strong> để chọn nhiều ảnh cùng lúc.</small>
                                </div>
                                <div id="admin_edit_gallery_preview" class="d-flex flex-wrap mt-2" style="gap: 6px;"></div>
                            </div>
                        </div>

                        <!-- 3. MÔ TẢ & TIỆN NGHI -->
                        <h5 class="font-weight-bold text-primary border-bottom pb-2 mb-3 mt-4">
                            <i class="fa fa-info-circle mr-1"></i> 3. Mô Tả Chi Tiết & Trang Bị Của Xe
                        </h5>

                        <div class="form-group">
                            <label class="font-weight-bold small">Mô tả tình trạng xe, tiện nghi và điều kiện giao nhận:</label>
                            <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
                        </div>

                        <!-- NÚT SUBMIT -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary font-weight-bold">
                                Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm">
                                <i class="fa fa-save mr-1"></i> CẬP NHẬT THÔNG TIN XE
                            </button>
                        </div>
                    </form>
                </div>
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

document.getElementById('admin_edit_gallery_input')?.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const container = document.getElementById('admin_edit_gallery_preview');
    const countBadge = document.getElementById('admin_edit_gallery_count');
    container.innerHTML = '';

    if (files.length > 0) {
        countBadge.innerText = files.length + ' ảnh mới chọn';
        countBadge.style.display = 'inline-block';

        files.forEach((file) => {
            const reader = new FileReader();
            reader.onload = function(evt) {
                const card = document.createElement('div');
                card.className = 'border rounded bg-white shadow-sm overflow-hidden';
                card.style.width = '70px';
                card.style.height = '50px';
                card.innerHTML = `<img src="${evt.target.result}" style="width:100%; height:100%; object-fit:cover;" title="${file.name}">`;
                container.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
    } else {
        countBadge.style.display = 'none';
    }
});
</script>
@endsection