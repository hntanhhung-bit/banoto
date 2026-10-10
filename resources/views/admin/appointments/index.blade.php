@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 py-3">
        <!-- Tiêu đề & Thống kê nhanh -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div>
                <h3 class="font-weight-bold text-dark mb-1">
                    <i class="fa fa-calendar-check-o text-success mr-2"></i> QUẢN LÝ LỊCH HẸN XEM XE & LÁI THỬ
                </h3>
                <p class="text-muted small mb-0">Tiếp nhận, phân công showroom đối tác, theo dõi trạng thái tiếp đón và kết quả chốt bán xe</p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-md-0">
                <span class="badge badge-primary px-3 py-2 font-weight-bold" style="font-size: 13px;">
                    <i class="fa fa-list-alt mr-1"></i> Tổng cộng: {{ $stats['total'] ?? 0 }} lịch hẹn
                </span>
            </div>
        </div>

        <!-- THẺ LỌC NHANH (QUICK STATUS PILLS) -->
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('admin.appointments.index') }}" 
               class="btn btn-sm {{ !request('status') && !request('deal_status') ? 'btn-dark font-weight-bold' : 'btn-outline-secondary' }} mr-2 mb-2">
                Tất cả ({{ $stats['total'] ?? 0 }})
            </a>
            <a href="{{ route('admin.appointments.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}" 
               class="btn btn-sm {{ request('status') == 'pending' ? 'btn-warning font-weight-bold text-dark' : 'btn-outline-warning text-dark' }} mr-2 mb-2">
                <i class="fa fa-clock-o"></i> ⏳ Chờ tiếp nhận ({{ $stats['pending'] ?? 0 }})
            </a>
            <a href="{{ route('admin.appointments.index', array_merge(request()->except(['status', 'page']), ['status' => 'confirmed'])) }}" 
               class="btn btn-sm {{ request('status') == 'confirmed' ? 'btn-primary font-weight-bold' : 'btn-outline-primary' }} mr-2 mb-2">
                <i class="fa fa-calendar-check-o"></i> 📅 Đã xác nhận đón khách ({{ $stats['confirmed'] ?? 0 }})
            </a>
            <a href="{{ route('admin.appointments.index', array_merge(request()->except(['deal_status', 'page']), ['deal_status' => 'deal_won'])) }}" 
               class="btn btn-sm {{ request('deal_status') == 'deal_won' ? 'btn-success font-weight-bold' : 'btn-outline-success' }} mr-2 mb-2">
                <i class="fa fa-trophy"></i> 🏆 Khách đã mua xe ({{ $stats['deal_won'] ?? 0 }})
            </a>
            <a href="{{ route('admin.appointments.index', array_merge(request()->except(['deal_status', 'page']), ['deal_status' => 'deal_lost'])) }}" 
               class="btn btn-sm {{ request('deal_status') == 'deal_lost' ? 'btn-danger font-weight-bold' : 'btn-outline-danger' }} mr-2 mb-2">
                <i class="fa fa-ban"></i> ✕ Khách không mua ({{ $stats['deal_lost'] ?? 0 }})
            </a>
        </div>

        <!-- Bộ lọc tìm kiếm chi tiết -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.appointments.index') }}" method="GET">
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Từ khóa tìm kiếm:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control"
                                    placeholder="Mã lịch hẹn, tên khách, số điện thoại, xe...">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Trạng thái tiếp đón:</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">-- Tất cả trạng thái tiếp đón --</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Chờ tiếp nhận</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>📅 Đã xác nhận đón khách</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✓ Đã tiếp đón / Xong</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>✕ Đã hủy tiếp đón</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Kết quả chốt bán:</label>
                            <select name="deal_status" class="form-control form-control-sm">
                                <option value="">-- Tất cả kết quả chốt bán --</option>
                                <option value="negotiating" {{ request('deal_status') == 'negotiating' ? 'selected' : '' }}>💬 Đang tư vấn đàm phán</option>
                                <option value="deal_won" {{ request('deal_status') == 'deal_won' ? 'selected' : '' }}>🏆 Khách đã mua xe</option>
                                <option value="deal_lost" {{ request('deal_status') == 'deal_lost' ? 'selected' : '' }}>✕ Khách không mua</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Showroom đối tác:</label>
                            <select name="partner_id" class="form-control form-control-sm">
                                <option value="">-- Tất cả showroom --</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                        {{ $partner->partner_showroom_name ?: ($partner->showroom_name ?: $partner->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 mt-1">
                            <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3">
                                <i class="fa fa-filter mr-1"></i> Áp dụng bộ lọc
                            </button>
                            @if(request()->hasAny(['keyword', 'status', 'deal_status', 'partner_id']))
                                <a href="{{ route('admin.appointments.index') }}"
                                    class="btn btn-outline-secondary btn-sm font-weight-bold px-3 ml-2">
                                    <i class="fa fa-refresh mr-1"></i> Xóa lọc
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- THANH CÔNG CỤ THAO TÁC HÀNG LOẠT -->
        <div class="card border-0 shadow-sm mb-3 bg-white" style="border-radius: 10px;">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center flex-wrap mb-2 mb-md-0">
                        <span class="badge badge-light border px-2 py-1 font-weight-bold text-dark mr-3"
                            style="font-size: 13px;">
                            <i class="fa fa-check-square text-primary mr-1"></i> Đã chọn:
                            <span id="selectedAppointmentCount" class="text-danger font-weight-bold">0</span> lịch hẹn
                        </span>

                        <div class="d-flex align-items-center mr-2 mb-1 mb-md-0">
                            <span class="small font-weight-bold text-muted mr-1">Trạng thái hẹn:</span>
                            <select name="status" id="bulkAppointmentStatus" class="custom-select custom-select-sm"
                                style="min-width: 140px;">
                                <option value="">-- Giữ nguyên --</option>
                                <option value="pending">⏳ Chờ tiếp nhận</option>
                                <option value="confirmed">📅 Đã xác nhận đón khách</option>
                                <option value="completed">✓ Đã tiếp đón / Xong</option>
                                <option value="cancelled">✕ Đã hủy</option>
                            </select>
                        </div>

                        <div class="d-flex align-items-center mr-2 mb-1 mb-md-0">
                            <span class="small font-weight-bold text-muted mr-1">Hoa hồng giới thiệu:</span>
                            <select name="commission_status" id="bulkAppointmentCommission"
                                class="custom-select custom-select-sm" style="min-width: 140px;">
                                <option value="">-- Giữ nguyên --</option>
                                <option value="paid">✅ Đã nộp hoa hồng</option>
                                <option value="pending">⌛ Chờ nộp hoa hồng</option>
                            </select>
                        </div>

                        <button type="submit" id="btnBulkAppointmentSubmit"
                            class="btn btn-sm btn-primary font-weight-bold shadow-sm" disabled
                            onclick="return confirm('Bạn có chắc muốn cập nhật trạng thái cho các lịch hẹn đã chọn?');">
                            <i class="fa fa-refresh mr-1"></i> Cập nhật hàng loạt
                        </button>
                    </div>

                    <div class="small text-muted">
                        <i class="fa fa-info-circle text-info"></i> Tích chọn các ô để thay đổi trạng thái cùng lúc
                    </div>
                </div>
            </div>

            <!-- Danh sách lịch hẹn -->
            <div class="card border-0 shadow-sm rounded-lg overflow-hidden" style="border-radius: 10px;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase text-secondary font-weight-bold" style="font-size: 13px;">
                            <tr>
                                <th class="py-3 text-center align-middle" width="40px">
                                    <input type="checkbox" id="checkAllAppointments"
                                        style="transform: scale(1.2); cursor: pointer;" title="Chọn tất cả">
                                </th>
                                <th class="py-3 px-3">Mã lịch hẹn</th>
                                <th class="py-3">Khách hàng</th>
                                <th class="py-3">Mẫu xe quan tâm</th>
                                <th class="py-3">Thời gian hẹn</th>
                                <th class="py-3 text-center">Trạng thái tiếp đón</th>
                                <th class="py-3 text-center">Kết quả chốt bán</th>
                                <th class="py-3 text-center">Giá bán / Hoa hồng</th>
                                <th class="py-3 text-center px-3" width="110px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $app)
                                <tr>
                                    <!-- Checkbox -->
                                    <td class="text-center align-middle">
                                        <input type="checkbox" name="appointment_ids[]" value="{{ $app->id }}"
                                            class="appointment-checkbox" style="transform: scale(1.2); cursor: pointer;">
                                    </td>

                                    <!-- Mã lịch hẹn -->
                                    <td class="px-3 font-weight-bold align-middle">
                                        <a href="{{ route('admin.appointments.show', $app->id) }}"
                                            class="text-primary font-weight-bold text-decoration-none" style="font-size: 14px;">
                                            #{{ $app->appointment_code }}
                                        </a>
                                        <div class="small text-muted font-weight-normal">
                                            {{ $app->created_at ? $app->created_at->format('d/m/Y H:i') : '' }}
                                        </div>
                                        @if($app->order)
                                            <a href="{{ route('admin.orders.show', $app->order->id) }}"
                                                class="badge badge-success text-white mt-1 d-inline-block text-decoration-none"
                                                title="Xem đơn hàng mua xe">
                                                <i class="fa fa-shopping-cart"></i> Đơn: #{{ $app->order->order_code }}
                                            </a>
                                        @endif
                                    </td>

                                    <!-- Khách hàng -->
                                    <td class="align-middle">
                                        <strong class="text-dark d-block" style="font-size: 14px;">{{ $app->customer_name }}</strong>
                                        <small class="text-muted d-block">
                                            <i class="fa fa-phone text-success mr-1"></i>{{ $app->customer_phone }}
                                        </small>
                                        @if($app->customer_email)
                                            <small class="text-muted d-block">
                                                <i class="fa fa-envelope-o text-muted mr-1"></i>{{ $app->customer_email }}
                                            </small>
                                        @endif
                                    </td>

                                    <!-- Mẫu xe quan tâm & Showroom -->
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $app->product ? $app->product->image_url : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=100&auto=format&fit=crop&q=60' }}"
                                                class="rounded mr-2 shadow-sm" style="width: 55px; height: 42px; object-fit: cover;"
                                                alt="{{ $app->product->name ?? 'Xe' }}"
                                                onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=100&auto=format&fit=crop&q=60';">
                                            <div>
                                                <strong class="text-dark d-block" style="font-size: 14px;">
                                                    {{ $app->product->name ?? 'Xe đã gỡ' }}
                                                </strong>
                                                <div class="small text-danger font-weight-bold">
                                                    {{ $app->product ? number_format($app->product->price) . ' đ' : 'N/A' }}
                                                </div>
                                                <div class="mt-1">
                                                    @if($app->partner)
                                                        <span class="badge badge-light border text-dark" style="font-size: 11px;">
                                                            <i class="fa fa-building text-primary mr-1"></i>{{ $app->partner->partner_showroom_name ?: ($app->partner->showroom_name ?: $app->partner->name) }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary" style="font-size: 11px;">Chưa phân Showroom</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Thời gian hẹn -->
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark">
                                            <i class="fa fa-calendar text-info mr-1"></i>
                                            {{ $app->appointment_date ? date('d/m/Y', strtotime($app->appointment_date)) : '' }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="fa fa-clock-o text-warning mr-1"></i>{{ $app->appointment_time }}
                                        </div>
                                        <div class="mt-1">
                                            @if($app->location_type === 'at_showroom')
                                                <span class="badge badge-primary px-2 py-1" style="font-size: 11px;">
                                                    <i class="fa fa-building mr-1"></i> Showroom
                                                </span>
                                            @else
                                                <span class="badge badge-info px-2 py-1" style="font-size: 11px;">
                                                    <i class="fa fa-home mr-1"></i> Tận nhà
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Trạng thái tiếp đón -->
                                    <td class="text-center align-middle">
                                        @if($app->status === 'completed')
                                            <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                                <i class="fa fa-check-circle mr-1"></i> Đã tiếp đón / Xong
                                            </span>
                                        @elseif($app->status === 'confirmed')
                                            <span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 12px; background-color: #0d6efd;">
                                                <i class="fa fa-calendar-check-o mr-1"></i> Đã xác nhận đón khách
                                            </span>
                                        @elseif($app->status === 'cancelled')
                                            <span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                                <i class="fa fa-times-circle mr-1"></i> Đã hủy tiếp đón
                                            </span>
                                        @else
                                            <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold" style="font-size: 12px; background-color: #ffc107;">
                                                <i class="fa fa-clock-o mr-1"></i> Chờ tiếp nhận
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Kết quả chốt bán -->
                                    <td class="text-center align-middle">
                                        @if($app->deal_status === 'deal_won')
                                            <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                                <i class="fa fa-trophy mr-1"></i> 🏆 Khách đã mua xe
                                            </span>
                                        @elseif($app->deal_status === 'deal_lost')
                                            <span class="badge badge-secondary px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                                <i class="fa fa-ban mr-1"></i> ✕ Khách không mua
                                            </span>
                                        @else
                                            <span class="badge badge-info px-2 py-1 font-weight-bold" style="font-size: 12px; background-color: #17a2b8;">
                                                <i class="fa fa-comments-o mr-1"></i> Đang tư vấn đàm phán
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Giá bán / Hoa hồng -->
                                    <td class="text-center align-middle">
                                        @if($app->deal_price > 0)
                                            <div class="font-weight-bold text-danger" style="font-size: 14px;">
                                                {{ number_format($app->deal_price) }} đ
                                            </div>
                                            <div class="small text-muted font-weight-normal">
                                                1% HH: {{ number_format($app->commission_amount) }} đ
                                            </div>
                                            <div class="mt-1">
                                                @if($app->commission_status === 'paid')
                                                    <span class="badge badge-success px-2 py-1" style="font-size: 11px;">
                                                        <i class="fa fa-check mr-1"></i> Đã nộp HH
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 11px;">
                                                        <i class="fa fa-clock-o mr-1"></i> Chờ nộp HH
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted font-italic">Chưa chốt</span>
                                        @endif
                                    </td>

                                    <!-- Thao tác -->
                                    <td class="text-center align-middle px-3">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <a href="{{ route('admin.appointments.show', $app->id) }}"
                                                class="btn btn-outline-primary btn-sm font-weight-bold mr-1"
                                                title="Xem chi tiết & Xử lý">
                                                <i class="fa fa-external-link mr-1"></i> Xem
                                            </a>
                                            <form action="{{ route('admin.appointments.destroy', $app->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa lịch hẹn này không?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fa fa-calendar-times-o fa-3x mb-2 text-muted"></i>
                                        <p class="mb-0 font-weight-bold">Không tìm thấy lịch hẹn xem xe nào phù hợp.</p>
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

        <!-- FORM ẨN ĐỂ SUBMIT BULK ACTION (TRÁNH LỖI NESTED FORM TRONG TABLE) -->
        <form id="bulkAppointmentForm" action="{{ route('admin.appointments.bulkStatus') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="status" id="hiddenBulkAppointmentStatus" value="">
            <input type="hidden" name="commission_status" id="hiddenBulkAppointmentCommission" value="">
            <div id="hiddenAppointmentIdsContainer"></div>
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

            if (btnSubmit) {
                btnSubmit.addEventListener('click', function(e) {
                    e.preventDefault();
                    const checked = document.querySelectorAll('.appointment-checkbox:checked');
                    if (checked.length === 0) {
                        alert('Vui lòng chọn ít nhất 1 lịch hẹn!');
                        return;
                    }
                    if (!confirm('Bạn có chắc muốn cập nhật trạng thái cho các lịch hẹn đã chọn?')) {
                        return;
                    }

                    document.getElementById('hiddenBulkAppointmentStatus').value = selectStatus.value;
                    document.getElementById('hiddenBulkAppointmentCommission').value = selectCommission.value;

                    const container = document.getElementById('hiddenAppointmentIdsContainer');
                    container.innerHTML = '';
                    checked.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'appointment_ids[]';
                        input.value = cb.value;
                        container.appendChild(input);
                    });

                    document.getElementById('bulkAppointmentForm').submit();
                });
            }
        });
    </script>
@endsection