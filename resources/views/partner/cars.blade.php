@extends('partner.layout')

@section('content')
<style>
.car-card {
    transition: all 0.25s ease-in-out;
}
.car-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.1) !important;
}
.car-card.selected-car {
    border: 2px solid #28a745 !important;
    box-shadow: 0 0.5rem 1.25rem rgba(40, 167, 69, 0.25) !important;
    background-color: #fafffb;
}
.cursor-pointer {
    cursor: pointer;
}
.car-select-checkbox-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(0, 0, 0, 0.15);
    border-radius: 8px;
    padding: 4px 10px;
    transition: all 0.2s;
}
.car-select-checkbox-container:hover {
    background: #ffffff;
    border-color: #28a745;
}
</style>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h3 class="font-weight-bold text-dark mb-1">
            <i class="fa fa-car text-success mr-2"></i> QUẢN LÝ ĐỘI XE SHOWROOM
        </h3>
        <p class="text-muted small mb-0">Quản lý, thêm mới, sửa giá và cập nhật trạng thái các dòng xe thuộc sở hữu của Showroom bạn trên sàn</p>
    </div>
    <div class="mt-2 mt-md-0">
        <a href="{{ route('partner.cars.create') }}" class="btn btn-success font-weight-bold shadow-sm">
            <i class="fa fa-plus-circle mr-1"></i> THÊM XE MỚI VÀO SHOWROOM
        </a>
    </div>
</div>

<!-- TAB LỌC TRẠNG THÁI XE -->
<ul class="nav nav-pills mb-3 flex-wrap">
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ (!$status && !$approvalStatus) ? 'active bg-dark text-white' : 'bg-white text-dark border' }}" 
           href="{{ route('partner.cars') }}">
            Tất cả đội xe ({{ $totalCars }})
        </a>
    </li>
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ $status === 'available' ? 'active bg-success' : 'bg-white text-success border' }}" 
           href="{{ route('partner.cars', ['rental_status' => 'available']) }}">
            <i class="fa fa-check-circle mr-1"></i> Sẵn sàng đón khách ({{ $countAvailable }})
        </a>
    </li>
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ $status === 'rented' ? 'active bg-warning text-dark' : 'bg-white text-warning border' }}" 
           href="{{ route('partner.cars', ['rental_status' => 'rented']) }}">
            <i class="fa fa-car mr-1"></i> Đang có khách ({{ $countRented }})
        </a>
    </li>
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ $status === 'maintenance' ? 'active bg-secondary' : 'bg-white text-secondary border' }}" 
           href="{{ route('partner.cars', ['rental_status' => 'maintenance']) }}">
            <i class="fa fa-wrench mr-1"></i> Bảo dưỡng ({{ $countMaintenance }})
        </a>
    </li>
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ $status === 'sold' ? 'active bg-dark text-white' : 'bg-white text-dark border' }}" 
           href="{{ route('partner.cars', ['rental_status' => 'sold']) }}">
            <i class="fa fa-handshake-o mr-1"></i> Đã bán xe ({{ $countSold ?? 0 }})
        </a>
    </li>
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ $approvalStatus === 'pending' ? 'active bg-info' : 'bg-white text-info border' }}" 
           href="{{ route('partner.cars', ['approval_status' => 'pending']) }}">
            <i class="fa fa-clock-o mr-1"></i> Chờ Admin duyệt ({{ $countPendingApproval }})
        </a>
    </li>
</ul>

<!-- BỘ LỌC TÌM KIẾM XE -->
<div class="card shadow-sm mb-3">
    <div class="card-body p-3">
        <form action="{{ route('partner.cars') }}" method="GET" class="row align-items-center">
            @if($status) <input type="hidden" name="rental_status" value="{{ $status }}"> @endif
            @if($approvalStatus) <input type="hidden" name="approval_status" value="{{ $approvalStatus }}"> @endif
            <div class="col-md-5 mb-2 mb-md-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                    </div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control"
                           placeholder="Tìm theo tên xe, biển số BKS, màu sắc...">
                </div>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select name="category_id" class="form-control form-control-sm">
                    <option value="">-- Tất cả hãng xe / danh mục --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-sm font-weight-bold mr-1">
                    <i class="fa fa-filter"></i> Lọc dữ liệu
                </button>
                @if(request()->filled('keyword') || request()->filled('category_id') || $status || $approvalStatus)
                    <a href="{{ route('partner.cars') }}" class="btn btn-outline-secondary btn-sm font-weight-bold">
                        <i class="fa fa-times"></i> Xóa lọc
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- THANH CÔNG CỤ THAO TÁC HÀNG LOẠT (BULK ACTIONS BAR) -->
<div class="card border-0 shadow-sm mb-3 bg-white">
    <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center mb-2 mb-md-0 flex-wrap">
            <!-- Checkbox Chọn tất cả (Select All) -->
            <div class="custom-control custom-checkbox mr-3 mb-1">
                <input type="checkbox" class="custom-control-input" id="selectAllCars">
                <label class="custom-control-label font-weight-bold text-dark cursor-pointer" for="selectAllCars">
                    Chọn tất cả
                </label>
            </div>

            <span class="badge badge-light border px-2 py-1 font-weight-bold text-dark mr-3 mb-1" style="font-size: 13px;">
                <i class="fa fa-check-square text-success mr-1"></i> Đã chọn: 
                <span id="selectedCountBadge" class="text-danger font-weight-bold">0</span> xe
            </span>

            <span class="small font-weight-bold text-muted mr-2 mb-1">Đổi nhanh tình trạng:</span>

            <!-- Nút 1: Sẵn sàng đón khách -->
            <button type="button" class="btn btn-sm btn-outline-success font-weight-bold mr-2 mb-1 btn-bulk-car-action" data-status="available" disabled>
                <i class="fa fa-check-circle mr-1"></i> Sẵn sàng đón khách
            </button>

            <!-- Nút 2: Đang có khách thuê -->
            <button type="button" class="btn btn-sm btn-outline-warning font-weight-bold mr-2 mb-1 btn-bulk-car-action text-dark" data-status="rented" disabled>
                <i class="fa fa-car mr-1"></i> Đang có khách
            </button>

            <!-- Nút 3: Tạm ngưng / Bảo dưỡng -->
            <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold mr-2 mb-1 btn-bulk-car-action" data-status="maintenance" disabled>
                <i class="fa fa-wrench mr-1"></i> Bảo dưỡng
            </button>

            <!-- Dropdown thao tác khác -->
            <div class="input-group input-group-sm d-inline-flex w-auto mb-1">
                <select id="bulkSelectAction" class="custom-select custom-select-sm" style="min-width: 190px;">
                    <option value="">-- Chọn tình trạng khác --</option>
                    <option value="available">✓ Sẵn sàng nhận khách</option>
                    <option value="rented">🚗 Đang có khách thuê</option>
                    <option value="maintenance">🔧 Tạm ngưng / Bảo dưỡng</option>
                </select>
                <div class="input-group-append">
                    <button type="button" id="btnBulkApply" class="btn btn-secondary font-weight-bold" disabled>
                        Áp dụng
                    </button>
                </div>
            </div>
        </div>

        <div class="text-muted small">
            <i class="fa fa-info-circle text-info mr-1"></i> Tích chọn các ô vuông để cập nhật trạng thái nhiều xe cùng lúc.
        </div>
    </div>
</div>

<!-- FORM POST ẨN ĐỂ XỬ LÝ HÀNG LOẠT -->
<form id="formBulkCarStatus" action="{{ route('partner.cars.bulkStatus') }}" method="POST" style="display: none;">
    @csrf
    <div id="hiddenCarIdsContainer"></div>
    <input type="hidden" name="rental_status" id="inputBulkCarStatus" value="">
</form>

<div class="row">
    @forelse($cars as $car)
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-0 rounded-lg car-card position-relative" id="car_card_{{ $car->id }}">
            <div class="position-relative">
                <!-- Checkbox Chọn xe hàng loạt (Nổi bật góc trên bên trái) -->
                <div class="position-absolute" style="top: 12px; left: 12px; z-index: 10;">
                    <div class="custom-control custom-checkbox car-select-checkbox-container shadow-sm">
                        <input type="checkbox" class="custom-control-input car-checkbox" id="car_check_{{ $car->id }}" value="{{ $car->id }}">
                        <label class="custom-control-label font-weight-bold small text-dark cursor-pointer mb-0" for="car_check_{{ $car->id }}">
                            Chọn
                        </label>
                    </div>
                </div>

                <img src="{{ $car->image_url }}" class="card-img-top" 
                     style="height: 200px; object-fit: cover; border-top-left-radius: 12px; border-top-right-radius: 12px;" 
                     alt="{{ $car->name }}"
                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=500&auto=format&fit=crop&q=60';">
                
                <!-- Huy hiệu Trạng thái Phê duyệt của Admin -->
                <div class="position-absolute" style="top: 12px; left: 95px; z-index: 9;">
                    @if($car->approval_status === 'pending')
                        <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold shadow-sm" style="background-color: #fff3cd; border: 1px solid #ffeeba;">
                            <i class="fa fa-clock-o text-danger mr-1"></i> Chờ Admin duyệt
                        </span>
                    @elseif($car->approval_status === 'rejected')
                        <span class="badge badge-danger px-2 py-1 font-weight-bold shadow-sm" title="{{ $car->admin_feedback }}">
                            <i class="fa fa-times-circle mr-1"></i> Bị từ chối
                        </span>
                    @else
                        <span class="badge badge-success px-2 py-1 font-weight-bold shadow-sm">
                            <i class="fa fa-check-circle mr-1"></i> Đã duyệt sàn
                        </span>
                    @endif
                </div>

                <!-- Huy hiệu Tình trạng Hoạt động của Xe -->
                <div class="position-absolute" style="top: 12px; right: 12px; z-index: 9;">
                    @if($car->rental_status === 'sold')
                        <span class="badge badge-dark px-3 py-2 font-weight-bold shadow-sm"><i class="fa fa-handshake-o mr-1"></i> Đã bán xe</span>
                    @elseif($car->rental_status === 'available' || empty($car->rental_status))
                        <span class="badge badge-success px-3 py-2 font-weight-bold shadow-sm"><i class="fa fa-check-circle mr-1"></i> Sẵn sàng đón khách</span>
                    @elseif($car->rental_status === 'rented')
                        <span class="badge badge-warning px-3 py-2 font-weight-bold shadow-sm text-dark"><i class="fa fa-car mr-1"></i> Đang có khách</span>
                    @else
                        <span class="badge badge-secondary px-3 py-2 font-weight-bold shadow-sm"><i class="fa fa-wrench mr-1"></i> Bảo dưỡng</span>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="font-weight-bold text-dark mb-1">{{ $car->name }}</h5>
                        <div>
                            @if($car->car_plate)
                                <span class="badge badge-dark px-2 py-1 font-weight-bold mr-1">{{ $car->car_plate }}</span>
                            @endif
                            @if($car->car_year)
                                <span class="badge badge-light border text-muted font-weight-bold mr-1">Đời {{ $car->car_year }}</span>
                            @endif
                            <small class="text-muted"><i class="fa fa-paint-brush mr-1"></i> {{ $car->color ?: 'Màu chuẩn' }}</small>
                        </div>
                    </div>
                    <span class="badge badge-light border text-muted font-weight-bold">{{ $car->category->name ?? 'Dòng xe' }}</span>
                </div>

                @if($car->approval_status === 'pending')
                    <div class="alert alert-warning p-2 small mb-2 border-warning text-dark">
                        <i class="fa fa-info-circle text-danger mr-1"></i> <strong>Chờ kiểm định:</strong> Mẫu xe sẽ hiển thị trên sàn ngay khi Admin phê duyệt.
                    </div>
                @elseif($car->approval_status === 'rejected')
                    <div class="alert alert-danger p-2 small mb-2 border-danger">
                        <i class="fa fa-times-circle mr-1"></i> <strong>Lý do từ chối:</strong> {{ $car->admin_feedback ?: 'Thông tin chưa đạt chuẩn.' }}
                    </div>
                @endif

                <div class="p-2 bg-light rounded small mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Giá thuê tự lái:</span>
                        <strong class="text-danger">{{ number_format($car->rent_price_per_day ?: 800000) }} đ/ngày</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Phí tài xế riêng:</span>
                        <strong class="text-primary">{{ number_format($car->driver_price_per_day ?: 500000) }} đ/ngày</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Tiền cọc thế chân:</span>
                        <strong class="text-dark">{{ number_format($car->rental_deposit ?: 5000000) }} đ</strong>
                    </div>
                </div>

                <!-- Form cập nhật trạng thái nhanh cho từng xe -->
                <form action="{{ route('partner.cars.updateStatus', $car->id) }}" method="POST" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <div class="form-group mb-0">
                        <label class="small text-muted font-weight-bold mb-1">Cập nhật nhanh tình trạng xe:</label>
                        <select name="rental_status" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()">
                            <option value="available" {{ ($car->rental_status === 'available' || empty($car->rental_status)) ? 'selected' : '' }}>✓ Sẵn sàng nhận khách</option>
                            <option value="rented" {{ $car->rental_status === 'rented' ? 'selected' : '' }}>🚗 Đang có khách thuê</option>
                            <option value="maintenance" {{ $car->rental_status === 'maintenance' ? 'selected' : '' }}>🔧 Tạm ngưng / Bảo dưỡng</option>
                            <option value="sold" {{ $car->rental_status === 'sold' ? 'selected' : '' }}>🤝 Đã bán xe (Gỡ khỏi sàn)</option>
                        </select>
                    </div>
                </form>

                <!-- CÁC NÚT THAO TÁC SỬA / GỠ XE / XEM -->
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <a href="{{ route('partner.cars.edit', $car->id) }}" class="btn btn-sm btn-outline-warning font-weight-bold flex-grow-1 mr-1 text-dark">
                        <i class="fa fa-pencil mr-1"></i> Sửa xe
                    </a>

                    <a href="{{ route('products.show', $car->id) }}" target="_blank" class="btn btn-sm btn-outline-info font-weight-bold flex-grow-1 mr-1" title="Xem trên sàn">
                        <i class="fa fa-external-link mr-1"></i> Xem sàn
                    </a>

                    <form action="{{ route('partner.cars.destroy', $car->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn gỡ mẫu xe {{ $car->name }} khỏi Showroom không?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger font-weight-bold" title="Gỡ xe">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="p-5 bg-white rounded shadow-sm border">
            <i class="fa fa-car fa-4x text-muted mb-3"></i>
            <h5 class="text-dark font-weight-bold">Không tìm thấy mẫu xe nào phù hợp</h5>
            <p class="small text-muted mb-4">Hãy thử thay đổi điều kiện lọc hoặc thêm mới các dòng xe vào Showroom của bạn.</p>
            <a href="{{ route('partner.cars.create') }}" class="btn btn-success font-weight-bold px-4 py-2 shadow-sm">
                <i class="fa fa-plus-circle mr-1"></i> Thêm Chiếc Xe Mới Ngay
            </a>
        </div>
    </div>
    @endforelse
</div>

<div class="mt-3">
    {{ $cars->links() }}
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('selectAllCars');
    var checkboxes = document.querySelectorAll('.car-checkbox');
    var selectedCountBadge = document.getElementById('selectedCountBadge');
    var bulkButtons = document.querySelectorAll('.btn-bulk-car-action');
    var bulkSelectAction = document.getElementById('bulkSelectAction');
    var btnBulkApply = document.getElementById('btnBulkApply');
    var formBulkStatus = document.getElementById('formBulkCarStatus');
    var hiddenCarIdsContainer = document.getElementById('hiddenCarIdsContainer');
    var inputBulkCarStatus = document.getElementById('inputBulkCarStatus');

    var statusLabels = {
        'available': 'Sẵn sàng đón khách',
        'rented': 'Đang có khách thuê',
        'maintenance': 'Tạm ngưng / Bảo dưỡng'
    };

    function updateBulkUI() {
        var checkedBoxes = document.querySelectorAll('.car-checkbox:checked');
        var count = checkedBoxes.length;
        selectedCountBadge.innerText = count;

        var isEnabled = count > 0;

        // Toggle card visual highlight
        checkboxes.forEach(function(cb) {
            var card = document.getElementById('car_card_' + cb.value);
            if (card) {
                if (cb.checked) {
                    card.classList.add('selected-car');
                } else {
                    card.classList.remove('selected-car');
                }
            }
        });

        // Toggle action buttons
        bulkButtons.forEach(function(btn) {
            btn.disabled = !isEnabled;
            var st = btn.getAttribute('data-status');
            var title = statusLabels[st] || st;

            if (count > 0) {
                var icon = 'fa-check';
                if (st === 'available') icon = 'fa-check-circle';
                else if (st === 'rented') icon = 'fa-car';
                else if (st === 'maintenance') icon = 'fa-wrench';

                btn.innerHTML = '<i class="fa ' + icon + ' mr-1"></i> ' + title + ' (' + count + ')';
            } else {
                var icon = 'fa-check';
                if (st === 'available') icon = 'fa-check-circle';
                else if (st === 'rented') icon = 'fa-car';
                else if (st === 'maintenance') icon = 'fa-wrench';

                btn.innerHTML = '<i class="fa ' + icon + ' mr-1"></i> ' + title;
            }
        });

        if (btnBulkApply) {
            btnBulkApply.disabled = !isEnabled;
        }

        if (selectAll && checkboxes.length > 0) {
            selectAll.checked = (checkedBoxes.length === checkboxes.length);
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            var isChecked = this.checked;
            checkboxes.forEach(function(cb) {
                cb.checked = isChecked;
            });
            updateBulkUI();
        });
    }

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            updateBulkUI();
        });
    });

    function submitBulk(newStatus) {
        var checkedBoxes = document.querySelectorAll('.car-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Vui lòng tích chọn ít nhất 1 xe để thực hiện thao tác.');
            return;
        }

        var label = statusLabels[newStatus] || newStatus;
        if (!confirm('Bạn có chắc chắn muốn chuyển ' + checkedBoxes.length + ' xe đã chọn sang tình trạng: "' + label + '" không?')) {
            return;
        }

        hiddenCarIdsContainer.innerHTML = '';
        checkedBoxes.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'car_ids[]';
            input.value = cb.value;
            hiddenCarIdsContainer.appendChild(input);
        });

        inputBulkCarStatus.value = newStatus;
        formBulkStatus.submit();
    }

    bulkButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var st = this.getAttribute('data-status');
            submitBulk(st);
        });
    });

    if (btnBulkApply) {
        btnBulkApply.addEventListener('click', function() {
            var st = bulkSelectAction.value;
            if (!st) {
                alert('Vui lòng chọn một tình trạng xe từ danh sách.');
                return;
            }
            submitBulk(st);
        });
    }
});
</script>
@endsection
