@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">
        <!-- Tiêu đề -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <div>
                <h3 class="font-weight-bold text-dark mb-1">
                    <i class="fa fa-calendar-check-o text-success mr-2"></i> QUẢN LÝ LỊCH HẸN XEM XE & LÁI THỬ
                </h3>
                <p class="text-muted small mb-0">Tiếp nhận, xét duyệt và điều phối nhân viên phụ trách tư vấn khách hàng</p>
            </div>
        </div>

        <!-- Bộ lọc tìm kiếm -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('admin.appointments.index') }}" method="GET">
                    <div class="row align-items-center">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <div class="input-group">
                                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control"
                                    placeholder="Mã lịch hẹn, tên khách hàng, số điện thoại...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary font-weight-bold"><i
                                            class="fa fa-search"></i> Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xác nhận
                                </option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Đã duyệt
                                    hẹn</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã hoàn
                                    thành</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 text-right">
                            @if(request()->hasAny(['keyword', 'status']))
                                <a href="{{ route('admin.appointments.index') }}"
                                    class="btn btn-outline-secondary font-weight-bold btn-sm">
                                    <i class="fa fa-refresh"></i> Xóa lọc
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- FORM THAO TÁC HÀNG LOẠT (BULK ACTIONS) -->
        <form id="bulkAppointmentForm" action="{{ route('admin.appointments.bulkStatus') }}" method="POST">
            @csrf
            <!-- THANH CÔNG CỤ THAO TÁC HÀNG LOẠT -->
            <div class="card border-0 shadow-sm mb-3 bg-white" style="border-radius: 10px;">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center flex-wrap mb-2 mb-md-0">
                        <span class="badge badge-light border px-2 py-1 font-weight-bold text-dark mr-3" style="font-size: 13px;">
                            <i class="fa fa-check-square text-primary mr-1"></i> Đã chọn: 
                            <span id="selectedAppointmentCount" class="text-danger font-weight-bold">0</span> lịch hẹn
                        </span>
                        
                        <div class="d-flex align-items-center mr-2 mb-1 mb-md-0">
                            <span class="small font-weight-bold text-muted mr-1">Trạng thái hẹn:</span>
                            <select name="status" id="bulkAppointmentStatus" class="custom-select custom-select-sm" style="min-width: 140px;">
                                <option value="">-- Giữ nguyên --</option>
                                <option value="pending">⏳ Chờ duyệt</option>
                                <option value="confirmed">✓ Đã duyệt hẹn</option>
                                <option value="completed">★ Hoàn thành</option>
                                <option value="cancelled">✕ Đã hủy</option>
                            </select>
                        </div>

                        <div class="d-flex align-items-center mr-2 mb-1 mb-md-0">
                            <span class="small font-weight-bold text-muted mr-1">Hoa hồng giới thiệu:</span>
                            <select name="commission_status" id="bulkAppointmentCommission" class="custom-select custom-select-sm" style="min-width: 140px;">
                                <option value="">-- Giữ nguyên --</option>
                                <option value="paid">✅ Đã thanh toán</option>
                                <option value="pending">⌛ Chờ đối soát</option>
                            </select>
                        </div>

                        <button type="submit" id="btnBulkAppointmentSubmit" class="btn btn-sm btn-primary font-weight-bold shadow-sm" disabled onclick="return confirm('Bạn có chắc muốn cập nhật trạng thái cho các lịch hẹn đã chọn?');">
                            <i class="fa fa-refresh mr-1"></i> Cập nhật hàng loạt
                        </button>
                    </div>
                    
                    <div class="small text-muted">
                        <i class="fa fa-info-circle text-info"></i> Tích chọn các ô để thay đổi trạng thái cùng lúc
                    </div>
                </div>
            </div>

            <!-- Danh sách lịch hẹn -->
            <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase text-secondary font-weight-bold" style="font-size: 13px;">
                            <tr>
                                <th class="py-3 text-center align-middle" width="40px">
                                    <input type="checkbox" id="checkAllAppointments" style="transform: scale(1.2); cursor: pointer;" title="Chọn tất cả">
                                </th>
                                <th class="py-3 px-3">Mã lịch hẹn</th>
                                <th class="py-3">Khách hàng</th>
                                <th class="py-3">Mẫu xe & Ảnh</th>
                                <th class="py-3">Thời gian hẹn</th>
                                <th class="py-3">Địa điểm</th>
                                <th class="py-3 text-center">Trạng thái</th>
                                <th class="py-3 text-right px-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $app)
                                <tr>
                                    <td class="text-center align-middle">
                                        <input type="checkbox" name="appointment_ids[]" value="{{ $app->id }}" class="appointment-checkbox" style="transform: scale(1.2); cursor: pointer;">
                                    </td>
                                    <td class="px-3 font-weight-bold text-primary">
                                        <a href="{{ route('admin.appointments.show', $app->id) }}"
                                            class="text-primary font-weight-bold text-decoration-none">
                                            #{{ $app->appointment_code }}
                                        </a>
                                        <div class="small text-muted font-weight-normal">{{ $app->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-dark d-block"
                                            style="font-size: 14px;">{{ $app->customer_name }}</strong>
                                        <small class="text-muted d-block"><i class="fa fa-phone text-success"></i>
                                            {{ $app->customer_phone }}</small>
                                        @if($app->customer_email)
                                            <small class="text-muted d-block"><i class="fa fa-envelope-o"></i>
                                                {{ $app->customer_email }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $app->product ? $app->product->image_url : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=100&auto=format&fit=crop&q=60' }}" 
                                                 class="rounded mr-2" style="width: 55px; height: 40px; object-fit: cover;" 
                                                 alt="{{ $app->product->name ?? 'Xe' }}"
                                                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=100&auto=format&fit=crop&q=60';">
                                            <div>
                                                <strong class="text-dark d-block">{{ $app->product->name ?? 'Xe đã gỡ' }}</strong>
                                                <small class="text-info">{{ $app->product->category->name ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-dark"><i class="fa fa-calendar text-info mr-1"></i>
                                            {{ date('d/m/Y', strtotime($app->appointment_date)) }}</strong>
                                        <div class="small text-muted"><i class="fa fa-clock-o text-warning mr-1"></i>
                                            {{ $app->appointment_time }}</div>
                                    </td>
                                    <td>
                                        @if($app->location_type === 'at_showroom')
                                            <span class="badge badge-primary px-2 py-1"><i class="fa fa-building"></i> Showroom</span>
                                        @else
                                            <span class="badge badge-info px-2 py-1"><i class="fa fa-home"></i> Tận nhà</span>
                                            <div class="small text-muted mt-1" style="max-width: 200px;">{{ $app->address }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($app->status === 'pending')
                                            <span class="badge badge-warning px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                                <i class="fa fa-hourglass-half"></i> Chờ duyệt
                                            </span>
                                        @elseif($app->status === 'confirmed')
                                            <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                                <i class="fa fa-check-circle"></i> Đã duyệt
                                            </span>
                                        @elseif($app->status === 'completed')
                                            <span class="badge badge-primary px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                                <i class="fa fa-flag-checkered"></i> Hoàn thành
                                            </span>
                                        @else
                                            <span class="badge badge-secondary px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                                <i class="fa fa-times"></i> Đã hủy
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right px-4">
                                        <a href="{{ route('admin.appointments.show', $app->id) }}"
                                            class="btn btn-outline-primary btn-sm font-weight-bold mr-1"
                                            title="Xem chi tiết & Xử lý">
                                            <i class="fa fa-eye"></i> Xử lý
                                        </a>
                                        <form action="{{ route('admin.appointments.destroy', $app->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa lịch hẹn này không?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa fa-calendar-times-o fa-3x mb-2 text-muted"></i>
                                        <p class="mb-0">Không tìm thấy lịch hẹn xem xe nào phù hợp.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($appointments->hasPages())
                    <div class="p-3 border-top d-flex justify-content-center">
                        {{ $appointments->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </form>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAllAppointments');
    const checkboxes = document.querySelectorAll('.appointment-checkbox');
    const badge = document.getElementById('selectedAppointmentCount');
    const btnSubmit = document.getElementById('btnBulkAppointmentSubmit');
    const selectStatus = document.getElementById('bulkAppointmentStatus');
    const selectCommission = document.getElementById('bulkAppointmentCommission');

    function updateState() {
        const checked = document.querySelectorAll('.appointment-checkbox:checked');
        const count = checked.length;
        if (badge) badge.textContent = count;

        const hasSelection = count > 0;
        const hasAction = (selectStatus.value !== '' || selectCommission.value !== '');

        if (btnSubmit) {
            btnSubmit.disabled = !(hasSelection && hasAction);
        }
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

    if (selectStatus) selectStatus.addEventListener('change', updateState);
    if (selectCommission) selectCommission.addEventListener('change', updateState);
});
</script>
@endsection