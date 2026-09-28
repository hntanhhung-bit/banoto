@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-car text-info mr-2"></i> QUẢN LÝ KHO XE & DUYỆT XE ĐỐI TÁC
            </h3>
            <p class="text-muted small mb-0">Kiểm tra thông tin biển số, năm sản xuất, tình trạng kỹ thuật & đăng kiểm trước khi cấp phép xe của đối tác hiển thị trên sàn.</p>
        </div>
        <div>
            <a class="btn btn-success font-weight-bold" href="{{ route('admin.products.create') }}">
                <i class="fa fa-plus"></i> Đăng xe mới
            </a>
            <a class="btn btn-secondary font-weight-bold ml-2" href="{{ route('admin.categories.index') }}">
                <i class="fa fa-tags"></i> Quản lý Danh mục
            </a>
        </div>
    </div>

    <!-- TABS PHÂN LOẠI NGUỒN XE & TRẠNG THÁI DUYỆT -->
    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <a class="nav-link font-weight-bold {{ (!request('filter') || request('filter') == 'all') ? 'active bg-dark' : 'bg-white text-dark border shadow-sm' }}" 
               href="{{ route('admin.products.index', ['filter' => 'all']) }}">
                Tất cả xe ({{ $countTotal }})
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link font-weight-bold {{ request('filter') == 'pending_approval' ? 'active bg-warning text-dark' : 'bg-white text-warning border shadow-sm' }}" 
               href="{{ route('admin.products.index', ['filter' => 'pending_approval']) }}">
                <i class="fa fa-clock-o text-danger mr-1"></i> Xe đối tác chờ kiểm định & duyệt ({{ $countPendingCars }})
                @if($countPendingCars > 0)
                    <span class="badge badge-danger ml-1">{{ $countPendingCars }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link font-weight-bold {{ request('filter') == 'partner_cars' ? 'active bg-info' : 'bg-white text-info border shadow-sm' }}" 
               href="{{ route('admin.products.index', ['filter' => 'partner_cars']) }}">
                <i class="fa fa-building-o mr-1"></i> Xe của Đối tác ({{ $countPartnerCars }})
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link font-weight-bold {{ request('filter') == 'admin_cars' ? 'active bg-primary' : 'bg-white text-primary border shadow-sm' }}" 
               href="{{ route('admin.products.index', ['filter' => 'admin_cars']) }}">
                <i class="fa fa-shield mr-1"></i> Xe trực thuộc Sàn ({{ $countAdminCars }})
            </a>
        </li>
    </ul>

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card mb-4 bg-white shadow-sm border-0">
        <div class="card-body p-3">
            <form action="{{ route('admin.products.index') }}" method="GET" class="row align-items-center">
                <input type="hidden" name="filter" value="{{ request('filter', 'all') }}">
                <div class="col-md-5 mb-2 mb-md-0">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm" placeholder="Tìm theo tên xe, biển số, tình trạng kỹ thuật...">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select name="category_id" class="form-control form-control-sm">
                        <option value="">-- Tất cả hãng xe --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold mr-1"><i class="fa fa-filter"></i> Lọc dữ liệu</button>
                    @if(request()->hasAny(['keyword', 'category_id']))
                        <a href="{{ route('admin.products.index', ['filter' => request('filter')]) }}" class="btn btn-outline-secondary btn-sm font-weight-bold"><i class="fa fa-times"></i> Xóa lọc</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- FORM THAO TÁC HÀNG LOẠT (BULK ACTIONS) -->
    <form id="bulkProductForm" action="{{ route('admin.products.bulkApproval') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulkProductAction" value="approve">

        <!-- THANH CÔNG CỤ THAO TÁC HÀNG LOẠT -->
        <div class="card border-0 shadow-sm mb-3 bg-white" style="border-radius: 10px;">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center flex-wrap mb-2 mb-md-0">
                    <span class="badge badge-light border px-2 py-1 font-weight-bold text-dark mr-3" style="font-size: 13px;">
                        <i class="fa fa-check-square text-primary mr-1"></i> Đã chọn: 
                        <span id="selectedProductCount" class="text-danger font-weight-bold">0</span> mẫu xe
                    </span>
                    
                    <!-- Nút Phê duyệt nhanh hàng loạt -->
                    <button type="button" id="btnBulkProductApprove" class="btn btn-sm btn-success font-weight-bold mr-2 shadow-sm" disabled onclick="submitBulkProduct('approve');">
                        <i class="fa fa-check-circle mr-1"></i> Phê duyệt các xe đã chọn
                    </button>

                    <!-- Nút Từ chối nhanh hàng loạt -->
                    <button type="button" id="btnBulkProductReject" class="btn btn-sm btn-danger font-weight-bold mr-2 shadow-sm" disabled data-toggle="modal" data-target="#bulkRejectCarsModal">
                        <i class="fa fa-times-circle mr-1"></i> Từ chối các xe đã chọn
                    </button>
                </div>
                
                <div class="small text-muted">
                    <i class="fa fa-info-circle text-info"></i> Tích chọn các mẫu xe của đối tác để phê duyệt nhanh cùng lúc
                </div>
            </div>
        </div>

        <!-- BẢNG DANH SÁCH XE -->
        <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light text-uppercase text-secondary font-weight-bold" style="font-size: 12px;">
                        <tr>
                            <th class="py-3 text-center align-middle" width="40px">
                                <input type="checkbox" id="checkAllProducts" style="transform: scale(1.2); cursor: pointer;" title="Chọn tất cả">
                            </th>
                            <th class="py-3 px-2 text-center" style="width: 45px;">ID</th>
                            <th class="py-3" style="width: 80px;">Hình ảnh</th>
                            <th class="py-3" style="min-width: 200px;">Tên xe & Phân loại</th>
                            <th class="py-3" style="min-width: 170px;">Chủ xe / Đối tác</th>
                            <th class="py-3" style="min-width: 160px;">Biển số & Năm SX</th>
                            <th class="py-3" style="min-width: 140px;">Giá thuê & Cọc</th>
                            <th class="py-3 text-center" style="min-width: 140px;">Kiểm định sàn</th>
                            <th class="py-3 text-right px-3" style="min-width: 180px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                        <tr class="{{ ($product->partner_id && $product->approval_status === 'pending') ? 'table-warning' : '' }}">
                            <td class="text-center align-middle">
                                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-checkbox" style="transform: scale(1.2); cursor: pointer;">
                            </td>
                            <td class="text-center align-middle font-weight-bold text-muted">{{ $product->id }}</td>
                        
                        <!-- Hình ảnh -->
                        <td class="align-middle">
                            @php
                                $imgSrc = asset('images/default-car.png');
                                if ($product->image) {
                                    if (file_exists(public_path('images/' . $product->image))) {
                                        $imgSrc = asset('images/' . $product->image);
                                    } elseif (file_exists(public_path('storage/' . $product->image))) {
                                        $imgSrc = asset('storage/' . $product->image);
                                    } else {
                                        $imgSrc = asset('images/' . $product->image);
                                    }
                                }
                            @endphp
                            <img src="{{ $imgSrc }}" class="rounded shadow-sm" width="70px" height="50px" style="object-fit: cover;" alt="{{ $product->name }}">
                        </td>

                        <!-- Tên xe & Hãng -->
                        <td class="align-middle">
                            <strong class="text-dark d-block" style="font-size: 13px;">{{ $product->name }}</strong>
                            <span class="badge badge-info">{{ $product->category->name ?? 'Dòng xe' }}</span>
                            <small class="text-muted"><i class="fa fa-tint"></i> {{ $product->color ?: 'Màu chuẩn' }}</small>
                        </td>

                        <!-- Chủ xe / Showroom Đối tác -->
                        <td class="align-middle">
                            @if($product->partner_id && $product->partner)
                                <div class="font-weight-bold text-primary">
                                    <i class="fa fa-building-o mr-1"></i> {{ $product->partner->showroom_name ?: $product->partner->name }}
                                </div>
                                <div class="text-muted small"><i class="fa fa-phone"></i> {{ $product->partner->phone }}</div>
                            @else
                                <span class="badge badge-secondary px-2 py-1 font-weight-bold">
                                    <i class="fa fa-shield mr-1"></i> Trực thuộc Sàn AutoCar
                                </span>
                            @endif
                        </td>

                        <!-- Biển số, Năm sản xuất & Tình trạng xe -->
                        <td class="align-middle">
                            <div>
                                @if($product->car_plate)
                                    <span class="badge badge-dark px-2 py-1 font-weight-bold">{{ $product->car_plate }}</span>
                                @else
                                    <span class="badge badge-light border text-muted">Chưa có BKS</span>
                                @endif
                                @if($product->car_year)
                                    <span class="badge badge-light border text-dark font-weight-bold">Đời {{ $product->car_year }}</span>
                                @endif
                            </div>
                            @if($product->car_condition)
                                <button type="button" class="btn btn-xs btn-outline-info font-weight-bold mt-1 py-0" 
                                        data-toggle="modal" data-target="#conditionModal{{ $product->id }}">
                                    <i class="fa fa-file-text-o mr-1"></i> Xem tình trạng kỹ thuật
                                </button>
                            @endif
                        </td>

                        <!-- Giá thuê & Cọc -->
                        <td class="align-middle">
                            <div class="text-danger font-weight-bold">{{ number_format($product->rent_price_per_day ?: 800000) }} đ/ngày</div>
                            <div class="text-muted small">Cọc: <strong>{{ number_format($product->rental_deposit ?: 5000000) }} đ</strong></div>
                        </td>

                        <!-- Trạng thái Kiểm định & Duyệt sàn -->
                        <td class="text-center align-middle">
                            @if(!$product->partner_id)
                                <span class="badge badge-success px-2 py-1 font-weight-bold">
                                    <i class="fa fa-check-circle"></i> Xe chính hãng sàn
                                </span>
                            @elseif($product->approval_status === 'approved')
                                <span class="badge badge-success px-2 py-1 font-weight-bold">
                                    <i class="fa fa-check-circle"></i> ĐÃ DUYỆT SÀN
                                </span>
                                <div class="text-muted" style="font-size: 10px;">{{ $product->approved_at ? \Carbon\Carbon::parse($product->approved_at)->format('d/m/Y') : 'Đang hoạt động' }}</div>
                            @elseif($product->approval_status === 'rejected')
                                <span class="badge badge-danger px-2 py-1 font-weight-bold" data-toggle="tooltip" title="{{ $product->admin_feedback }}">
                                    <i class="fa fa-times-circle"></i> TỪ CHỐI DUYỆT
                                </span>
                                @if($product->admin_feedback)
                                    <div class="text-danger small mt-1 text-truncate" style="max-width: 140px;" title="{{ $product->admin_feedback }}">
                                        {{ $product->admin_feedback }}
                                    </div>
                                @endif
                            @else
                                <span class="badge badge-warning px-2 py-1 font-weight-bold text-dark" style="background-color: #ffeeba; border: 1px solid #f5c6cb;">
                                    <i class="fa fa-clock-o text-danger"></i> CHỜ DUYỆT XE
                                </span>
                                <div class="text-danger small font-weight-bold" style="font-size: 10px;">Chưa hiển thị trên sàn</div>
                            @endif
                        </td>

                        <!-- Thao tác -->
                        <td class="text-right align-middle px-3">
                            <!-- NÚT DUYỆT / TỪ CHỐI XE ĐỐI TÁC -->
                            @if($product->partner_id)
                                @if($product->approval_status === 'pending')
                                    <form action="{{ route('admin.products.approveCar', $product->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Xác nhận PHÊ DUYỆT mẫu xe {{ $product->name }} (BKS: {{ $product->car_plate }}) để mở cho khách thuê trên sàn?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success font-weight-bold mb-1" title="Duyệt cho thuê">
                                            <i class="fa fa-check mr-1"></i> Duyệt xe
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold mb-1" 
                                            data-toggle="modal" data-target="#rejectCarModal{{ $product->id }}" title="Từ chối xe">
                                        <i class="fa fa-ban mr-1"></i> Từ chối
                                    </button>
                                @elseif($product->approval_status === 'approved')
                                    <button type="button" class="btn btn-xs btn-outline-secondary mb-1" 
                                            data-toggle="modal" data-target="#rejectCarModal{{ $product->id }}" title="Tạm dừng duyệt">
                                        <i class="fa fa-ban"></i> Ngưng duyệt
                                    </button>
                                @elseif($product->approval_status === 'rejected')
                                    <form action="{{ route('admin.products.approveCar', $product->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Duyệt lại mẫu xe {{ $product->name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-success mb-1">
                                            <i class="fa fa-refresh"></i> Duyệt lại
                                        </button>
                                    </form>
                                @endif
                            @endif

                            <!-- CÁC NÚT XEM, SỬA, XÓA TIÊU CHUẨN -->
                            <div class="mt-1">
                                <a class="btn btn-info btn-sm text-white" href="{{ route('admin.products.show', $product->id) }}" title="Xem chi tiết"><i class="fa fa-eye"></i></a>
                                <a class="btn btn-primary btn-sm text-white" href="{{ route('admin.products.edit', $product->id) }}" title="Sửa"><i class="fa fa-edit"></i></a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa chiếc xe này không?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Xóa"><i class="fa fa-trash"></i></button>
                                </form>
                            </div>

                            <!-- MODAL XEM TÌNH TRẠNG KỸ THUẬT & ĐĂNG KIỂM -->
                            <div class="modal fade text-left" id="conditionModal{{ $product->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-info text-white font-weight-bold">
                                            <h6 class="modal-title font-weight-bold"><i class="fa fa-car mr-1"></i> THÔNG TIN KIỂM ĐỊNH XE #{{ $product->id }} - {{ $product->name }}</h6>
                                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body small">
                                            <div class="text-center mb-3">
                                                <img src="{{ $imgSrc }}" class="rounded shadow-sm img-fluid" style="max-height: 180px; object-fit: cover;" alt="{{ $product->name }}">
                                            </div>
                                            <table class="table table-bordered table-sm mb-0">
                                                <tbody>
                                                    <tr>
                                                        <th class="bg-light" style="width: 35%;">Chủ xe / Showroom:</th>
                                                        <td class="font-weight-bold text-primary">{{ $product->partner ? ($product->partner->showroom_name ?: $product->partner->name) : 'Sàn AutoCar' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">Biển kiểm soát (BKS):</th>
                                                        <td><span class="badge badge-dark font-weight-bold">{{ $product->car_plate ?: 'Chưa cập nhật' }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">Năm sản xuất / Đời:</th>
                                                        <td>{{ $product->car_year ?: 'Chưa cập nhật' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">Tình trạng kỹ thuật, Đăng kiểm & Bảo hiểm:</th>
                                                        <td class="text-dark font-weight-bold">{{ $product->car_condition ?: 'Chưa có thông tin kiểm định.' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">Giá thuê tự lái:</th>
                                                        <td class="text-danger font-weight-bold">{{ number_format($product->rent_price_per_day) }} đ/ngày</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">Tiền cọc thế chân:</th>
                                                        <td class="font-weight-bold">{{ number_format($product->rental_deposit) }} đ</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODAL TỪ CHỐI KIỂM ĐỊNH XE KÈM LÝ DO -->
                            <div class="modal fade text-left" id="rejectCarModal{{ $product->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white">
                                            <h6 class="modal-title font-weight-bold"><i class="fa fa-ban mr-1"></i> TỪ CHỐI DUYỆT XE: {{ $product->name }}</h6>
                                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form action="{{ route('admin.products.rejectCar', $product->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="alert alert-warning small">
                                                    <i class="fa fa-info-circle mr-1"></i> Vui lòng nêu rõ lý do từ chối (VD: Hết hạn đăng kiểm, ảnh chụp xe không đạt, hoặc biển số không trùng khớp) để đối tác cập nhật lại.
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label class="font-weight-bold small">Lý do từ chối kiểm định xe <span class="text-danger">*</span>:</label>
                                                    <textarea name="admin_feedback" rows="3" class="form-control" required
                                                              placeholder="VD: Hạn đăng kiểm xe đã hết hoặc ảnh chụp ngoại thất không rõ ràng..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Hủy bỏ</button>
                                                <button type="submit" class="btn btn-danger btn-sm font-weight-bold">
                                                    <i class="fa fa-check mr-1"></i> Xác nhận từ chối
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fa fa-inbox fa-2x mb-2 d-block text-muted"></i>
                            Chưa có dữ liệu xe nào phù hợp với bộ lọc hiện tại.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $products->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    <!-- MODAL TỪ CHỐI DUYỆT XE HÀNG LOẠT -->
    <div class="modal fade" id="bulkRejectCarsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title font-weight-bold">
                        <i class="fa fa-ban mr-1"></i> TỪ CHỐI DUYỆT HÀNG LOẠT CÁC MẪU XE
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <i class="fa fa-exclamation-triangle mr-1"></i> Tất cả các xe được chọn sẽ chuyển sang trạng thái <strong>Bị từ chối</strong> và gửi phản hồi này đến đối tác sở hữu xe.
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Lý do từ chối kiểm định xe <span class="text-danger">*</span>:</label>
                        <textarea name="reason" id="bulkRejectReason" rows="3" class="form-control"
                                  placeholder="VD: Hồ sơ đăng kiểm xe đã hết hạn hoặc hình ảnh xe mờ không đạt tiêu chuẩn..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Hủy bỏ</button>
                    <button type="button" class="btn btn-danger btn-sm font-weight-bold" onclick="submitBulkProduct('reject');">
                        <i class="fa fa-times-circle mr-1"></i> Xác nhận từ chối hàng loạt
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAllProducts');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const badge = document.getElementById('selectedProductCount');
    const btnApprove = document.getElementById('btnBulkProductApprove');
    const btnReject = document.getElementById('btnBulkProductReject');

    function updateState() {
        const checked = document.querySelectorAll('.product-checkbox:checked');
        const count = checked.length;
        if (badge) badge.textContent = count;

        const hasSelection = count > 0;
        if (btnApprove) btnApprove.disabled = !hasSelection;
        if (btnReject) btnReject.disabled = !hasSelection;

        if (checkAll && checkboxes.length > 0) {
            checkAll.checked = (count === checkboxes.length);
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = checkAll.checked);
            updateState();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateState);
    });
});

function submitBulkProduct(action) {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (checked.length === 0) {
        alert('Vui lòng tích chọn ít nhất 1 mẫu xe!');
        return;
    }

    if (action === 'approve') {
        if (!confirm('Bạn có chắc muốn phê duyệt cho ' + checked.length + ' mẫu xe đã chọn hiển thị trên sàn?')) {
            return;
        }
    } else if (action === 'reject') {
        const reasonInput = document.getElementById('bulkRejectReason');
        if (!reasonInput || !reasonInput.value.trim()) {
            alert('Vui lòng nhập lý do từ chối kiểm định xe!');
            return;
        }
    }

    document.getElementById('bulkProductAction').value = action;
    document.getElementById('bulkProductForm').submit();
}
</script>
@endsection