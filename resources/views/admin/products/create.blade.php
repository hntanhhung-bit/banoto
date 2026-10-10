@extends('layouts.admin')

@section('content')
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-weight-bold text-uppercase" style="border-left: 4px solid #28a745; padding-left: 10px;">Thêm xe mới & Cấu hình giá theo màu</h2>
        <a class="btn btn-secondary font-weight-bold" href="{{ route('admin.products.index') }}"><i class="fa fa-arrow-left"></i> Quay lại kho xe</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <strong>Khoan đã!</strong> Dữ liệu bạn nhập đang có lỗi:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Thông tin cơ bản -->
                <h5 class="font-weight-bold text-primary mb-3 border-bottom pb-2">
                    <i class="fa fa-car mr-1"></i> 1. Thông tin chung & Giá cơ bản
                </h5>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold">Tên mẫu xe <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="VD: Mercedes-Benz C300 AMG" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold">Giá bán niêm yết (đ) <small class="text-muted font-italic">(Tùy chọn)</small></label>
                        <input type="number" step="1" min="0" name="price" class="form-control" value="{{ old('price') }}" placeholder="VD: 1500000000 (Tùy chọn)">
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold">Giá thuê tự lái (đ/ngày) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="0" name="rent_price_per_day" id="base_rent_price" class="form-control text-danger font-weight-bold" value="{{ old('rent_price_per_day', 800000) }}" placeholder="VD: 800000" required oninput="updateColorPrices()">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold">Phí tài xế riêng (đ/ngày) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="0" name="driver_price_per_day" class="form-control text-primary font-weight-bold" value="{{ old('driver_price_per_day', 500000) }}" placeholder="VD: 500000" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold">Tiền cọc giữ xe (đ) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="0" name="rental_deposit" class="form-control" value="{{ old('rental_deposit', 5000000) }}" placeholder="VD: 5000000" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Hãng xe (Danh mục) <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-control" required>
                            <option value="">-- Chọn hãng xe --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Màu đại diện chính</label>
                        <input type="text" name="color" class="form-control" value="{{ old('color', 'Trắng') }}" placeholder="Màu chính của xe">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Trạng thái xe</label>
                        <select name="rental_status" class="form-control font-weight-bold">
                            <option value="available" selected>Xe đang rảnh (Sẵn sàng phục vụ)</option>
                            <option value="rented">Đang có khách thuê</option>
                            <option value="maintenance">Đang bảo dưỡng</option>
                        </select>
                    </div>
                </div>

                <!-- Bảng phân loại 3 màu phổ biến & giá riêng từng màu -->
                <div class="p-3 bg-light rounded border my-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="font-weight-bold text-dark mb-0">
                            <i class="fa fa-paint-brush text-danger mr-1"></i> 2. Phân loại Màu sắc & Giá theo màu (3 màu phổ biến nhất)
                        </h5>
                        <small class="text-muted font-italic">Giá thuê của xe sẽ tự động chênh lệch theo màu sắc</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered bg-white mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th width="20%">Màu sắc phổ biến</th>
                                    <th width="15%">Mã màu (Hex)</th>
                                    <th width="25%">Phụ phí màu (VNĐ/ngày)</th>
                                    <th width="25%">Tổng giá thuê (VNĐ/ngày)</th>
                                    <th width="15%" class="text-center">Số lượng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Màu 1: Trắng ngọc trai -->
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span style="display:inline-block; width:18px; height:18px; border-radius:50%; background-color:#FFFFFF; border:1.5px solid #ccc; margin-right:8px;"></span>
                                            <input type="text" name="colors[0][color_name]" value="Trắng ngọc trai" class="form-control form-control-sm font-weight-bold" required>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="colors[0][color_hex]" value="#FFFFFF" class="form-control form-control-sm text-center">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">+</span></div>
                                            <input type="number" step="1" min="0" name="colors[0][extra_rent_price]" id="extra_price_0" value="50000" class="form-control font-weight-bold text-success" oninput="calculateRowPrice(0)">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="0" name="colors[0][rent_price_per_day]" id="total_price_0" value="850000" class="form-control form-control-sm font-weight-bold text-danger" readonly>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" step="1" min="0" name="colors[0][quantity]" value="5" class="form-control form-control-sm text-center">
                                        <input type="hidden" name="colors[0][is_default]" value="1">
                                    </td>
                                </tr>

                                <!-- Màu 2: Đen ánh kim -->
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span style="display:inline-block; width:18px; height:18px; border-radius:50%; background-color:#111111; border:1.5px solid #111; margin-right:8px;"></span>
                                            <input type="text" name="colors[1][color_name]" value="Đen ánh kim" class="form-control form-control-sm font-weight-bold" required>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="colors[1][color_hex]" value="#111111" class="form-control form-control-sm text-center">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">+</span></div>
                                            <input type="number" step="1" min="0" name="colors[1][extra_rent_price]" id="extra_price_1" value="0" class="form-control font-weight-bold text-muted" oninput="calculateRowPrice(1)">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="0" name="colors[1][rent_price_per_day]" id="total_price_1" value="800000" class="form-control form-control-sm font-weight-bold text-danger" readonly>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" step="1" min="0" name="colors[1][quantity]" value="5" class="form-control form-control-sm text-center">
                                    </td>
                                </tr>

                                <!-- Màu 3: Đỏ thể thao -->
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span style="display:inline-block; width:18px; height:18px; border-radius:50%; background-color:#D0021B; border:1.5px solid #d0021b; margin-right:8px;"></span>
                                            <input type="text" name="colors[2][color_name]" value="Đỏ thể thao" class="form-control form-control-sm font-weight-bold" required>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="colors[2][color_hex]" value="#D0021B" class="form-control form-control-sm text-center">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">+</span></div>
                                            <input type="number" step="1" min="0" name="colors[2][extra_rent_price]" id="extra_price_2" value="100000" class="form-control font-weight-bold text-danger" oninput="calculateRowPrice(2)">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="0" name="colors[2][rent_price_per_day]" id="total_price_2" value="900000" class="form-control form-control-sm font-weight-bold text-danger" readonly>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" step="1" min="0" name="colors[2][quantity]" value="5" class="form-control form-control-sm text-center">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold">Mô tả thông số xe & trang bị tiện nghi</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Nhập thông tin động cơ, số chỗ ngồi, tính năng an toàn, tình trạng xe...">{{ old('description') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="font-weight-bold text-dark">
                            <i class="fa fa-camera text-primary mr-1"></i> 1. Hình ảnh đại diện chính (Ảnh bìa xe)
                        </label>
                        <input type="file" name="image" id="main_image_input" accept="image/*" class="form-control-file border p-2 rounded bg-light">
                        <small class="text-muted d-block mt-1">*(Hỗ trợ định dạng: jpeg, png, jpg, gif, webp - Tối đa 2MB)*</small>
                        <div id="main_image_preview" class="mt-2" style="display: none;">
                            <img src="" id="main_preview_img" class="img-thumbnail rounded shadow-sm" style="max-height: 140px; object-fit: cover;">
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-bold text-dark mb-0">
                                <i class="fa fa-images text-success mr-1"></i> 2. Bộ sưu tập ảnh xe (Chọn nhiều ảnh cùng 1 lúc)
                            </label>
                            <span class="badge badge-success px-2 py-1" id="gallery_count_badge" style="display: none;">0 ảnh đã chọn</span>
                        </div>
                        <input type="file" name="gallery_images[]" id="gallery_images_input" accept="image/*" multiple class="form-control-file border p-2 rounded bg-light">
                        <small class="text-success font-weight-bold d-block mt-1">
                            <i class="fa fa-info-circle"></i> Giữ phím <strong>Ctrl</strong> (hoặc <strong>Shift</strong>) khi chọn để tải lên nhiều ảnh (nội thất, ngoại thất, cốp, khoang lái...)
                        </small>
                        
                        <div id="gallery_preview_container" class="mt-2 d-flex flex-wrap" style="gap: 8px;"></div>
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-success font-weight-bold px-4 py-2"><i class="fa fa-plus-circle"></i> Thêm Xe Mới & Lưu Bảng Giá</button>
            </form>
        </div>
    </div>
</div>

<script>
    function updateColorPrices() {
        const basePrice = parseInt(document.getElementById('base_rent_price').value, 10) || 0;
        for (let i = 0; i < 3; i++) {
            const extra = parseInt(document.getElementById('extra_price_' + i).value, 10) || 0;
            document.getElementById('total_price_' + i).value = Math.round(basePrice + extra);
        }
    }

    function calculateRowPrice(index) {
        const basePrice = parseInt(document.getElementById('base_rent_price').value, 10) || 0;
        const extra = parseInt(document.getElementById('extra_price_' + index).value, 10) || 0;
        document.getElementById('total_price_' + index).value = Math.round(basePrice + extra);
    }

    // Xem trước ảnh đại diện chính
    document.getElementById('main_image_input')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewContainer = document.getElementById('main_image_preview');
        const previewImg = document.getElementById('main_preview_img');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                previewImg.src = evt.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    });

    // Xem trước danh sách nhiều ảnh gallery
    document.getElementById('gallery_images_input')?.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const container = document.getElementById('gallery_preview_container');
        const countBadge = document.getElementById('gallery_count_badge');
        container.innerHTML = '';

        if (files.length > 0) {
            countBadge.innerText = files.length + ' ảnh đã chọn';
            countBadge.style.display = 'inline-block';

            files.forEach((file, idx) => {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    const card = document.createElement('div');
                    card.className = 'position-relative border rounded shadow-sm bg-white overflow-hidden';
                    card.style.width = '80px';
                    card.style.height = '70px';
                    card.innerHTML = `
                        <img src="${evt.target.result}" style="width: 100%; height: 100%; object-fit: cover;" title="${file.name}">
                        <span class="badge badge-dark position-absolute" style="top: 2px; left: 2px; font-size: 9px; opacity: 0.85;">#${idx + 1}</span>
                    `;
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