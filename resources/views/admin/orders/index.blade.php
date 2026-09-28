@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="font-weight-bold text-uppercase mb-0" style="border-left: 4px solid #f59e0b; padding-left: 12px;">
            <i class="fa fa-shopping-bag text-warning"></i> Quản lý Đơn Mua Xe & Thanh Toán
        </h2>
    </div>

    <!-- CHUYỂN TAB ĐƠN MUA XE / ĐƠN THUÊ XE -->
    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" href="{{ route('admin.orders.index') }}" style="background-color: #005fb7;">
                <i class="fa fa-shopping-cart mr-1"></i> Đơn Mua Xe ({{ \App\Models\Order::count() }})
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link font-weight-bold bg-white text-danger border shadow-sm" href="{{ route('admin.rentals.index') }}">
                <i class="fa fa-key mr-1"></i> Đơn Thuê Xe & Đặt Cọc MoMo ({{ \App\Models\Rental::count() }})
            </a>
        </li>
    </ul>

    <!-- BỘ LỌC TÌM KIẾM ĐƠN HÀNG -->
    <div class="card mb-4 bg-white shadow-sm border-0" style="border-radius: 10px;">
        <div class="card-body">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row align-items-center">
                <div class="col-lg-4 col-md-6 mb-2 mb-lg-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Mã đơn hàng, tên khách, số điện thoại...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2 mb-lg-0">
                    <select name="order_status" class="form-control">
                        <option value="">-- Tất cả trạng thái đơn --</option>
                        <option value="pending" {{ request('order_status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                        <option value="confirmed" {{ request('order_status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="shipping" {{ request('order_status') == 'shipping' ? 'selected' : '' }}>Đang giao xe</option>
                        <option value="completed" {{ request('order_status') == 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                        <option value="cancelled" {{ request('order_status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 mb-2 mb-lg-0">
                    <select name="payment_status" class="form-control">
                        <option value="">-- Trạng thái thanh toán --</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-12 text-right">
                    <button type="submit" class="btn btn-primary font-weight-bold mr-1">
                        <i class="fa fa-filter"></i> Lọc
                    </button>
                    @if(request()->hasAny(['keyword', 'order_status', 'payment_status']))
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary font-weight-bold">
                            <i class="fa fa-times"></i> Xóa lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- FORM THAO TÁC HÀNG LOẠT (BULK ACTIONS) -->
    <form id="bulkOrderForm" action="{{ route('admin.orders.bulkStatus') }}" method="POST">
        @csrf
        <!-- THANH CÔNG CỤ THAO TÁC HÀNG LOẠT -->
        <div class="card border-0 shadow-sm mb-3 bg-white" style="border-radius: 10px;">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center flex-wrap mb-2 mb-md-0">
                    <span class="badge badge-light border px-2 py-1 font-weight-bold text-dark mr-3" style="font-size: 13px;">
                        <i class="fa fa-check-square text-primary mr-1"></i> Đã chọn: 
                        <span id="selectedOrderCount" class="text-danger font-weight-bold">0</span> đơn hàng
                    </span>
                    
                    <div class="d-flex align-items-center mr-2 mb-1 mb-md-0">
                        <span class="small font-weight-bold text-muted mr-1">Trạng thái đơn:</span>
                        <select name="order_status" id="bulkOrderStatus" class="custom-select custom-select-sm" style="min-width: 140px;">
                            <option value="">-- Giữ nguyên --</option>
                            <option value="pending">⏳ Chờ xử lý</option>
                            <option value="confirmed">✓ Đã xác nhận</option>
                            <option value="shipping">🚗 Đang giao xe</option>
                            <option value="completed">★ Hoàn tất</option>
                            <option value="cancelled">✕ Đã hủy</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center mr-2 mb-1 mb-md-0">
                        <span class="small font-weight-bold text-muted mr-1">Thanh toán:</span>
                        <select name="payment_status" id="bulkOrderPayment" class="custom-select custom-select-sm" style="min-width: 140px;">
                            <option value="">-- Giữ nguyên --</option>
                            <option value="paid">✅ Đã thanh toán</option>
                            <option value="unpaid">⌛ Chưa thanh toán</option>
                        </select>
                    </div>

                    <button type="submit" id="btnBulkOrderSubmit" class="btn btn-sm btn-primary font-weight-bold shadow-sm" disabled onclick="return confirm('Bạn có chắc muốn cập nhật trạng thái cho các đơn hàng đã chọn?');">
                        <i class="fa fa-refresh mr-1"></i> Cập nhật hàng loạt
                    </button>
                </div>
                
                <div class="small text-muted">
                    <i class="fa fa-info-circle text-info"></i> Tích chọn các ô để thay đổi trạng thái cùng lúc
                </div>
            </div>
        </div>

        <!-- BẢNG DANH SÁCH ĐƠN HÀNG -->
        <div class="card shadow-sm border-0" style="border-radius: 10px; overflow: hidden;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped m-0 align-middle">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th class="text-center align-middle" width="40px">
                                    <input type="checkbox" id="checkAllOrders" style="transform: scale(1.2); cursor: pointer;" title="Chọn tất cả">
                                </th>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Địa chỉ nhận (GPS)</th>
                                <th class="text-center">Tổng tiền</th>
                                <th class="text-center">Thanh toán</th>
                                <th class="text-center">Trạng thái đơn</th>
                                <th class="text-center">Ngày tạo</th>
                                <th class="text-center" width="160px">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                            <tr>
                                <td class="text-center align-middle">
                                    <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox" style="transform: scale(1.2); cursor: pointer;">
                                </td>
                                <td class="font-weight-bold text-primary align-middle">
                                    #{{ $order->order_code }}
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold text-dark">{{ $order->customer_name }}</div>
                                    <div class="small text-muted"><i class="fa fa-phone"></i> {{ $order->customer_phone }}</div>
                                </td>
                                <td class="align-middle" style="max-width: 250px;">
                                    <div class="text-truncate" title="{{ $order->customer_address }}">
                                        {{ $order->customer_address }}
                                    </div>
                                    @if($order->latitude && $order->longitude)
                                        <a href="https://www.google.com/maps?q={{ $order->latitude }},{{ $order->longitude }}" target="_blank" class="badge badge-danger text-white mt-1">
                                            <i class="fa fa-map-marker"></i> Tọa độ GPS
                                        </a>
                                    @endif
                                </td>
                                <td class="text-center align-middle font-weight-bold text-danger" style="font-size: 15px;">
                                    {{ number_format($order->total_amount) }} đ
                                </td>
                                <td class="text-center align-middle">
                                    @if($order->payment_status === 'paid')
                                        <span class="badge badge-success px-2 py-1"><i class="fa fa-check"></i> Đã thanh toán</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1"><i class="fa fa-clock-o"></i> Chưa thanh toán</span>
                                    @endif
                                    <div class="mt-1" style="font-size: 11px;">
                                        @if($order->payment_method === 'momo')
                                            <span class="badge" style="background-color: #a50064; color: #fff;">MoMo</span>
                                        @elseif($order->payment_method === 'bank_transfer')
                                            <span class="badge badge-info">VietQR</span>
                                        @else
                                            <span class="badge badge-secondary">COD / Showroom</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    @if($order->order_status === 'completed')
                                        <span class="badge badge-success px-2 py-1">Hoàn tất</span>
                                    @elseif($order->order_status === 'confirmed')
                                        <span class="badge badge-primary px-2 py-1">Đã xác nhận</span>
                                    @elseif($order->order_status === 'shipping')
                                        <span class="badge badge-info px-2 py-1">Đang giao xe</span>
                                    @elseif($order->order_status === 'cancelled')
                                        <span class="badge badge-danger px-2 py-1">Đã hủy</span>
                                    @else
                                        <span class="badge badge-warning px-2 py-1 text-dark">Chờ xử lý</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle small text-muted">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center">
                                        <a class="btn btn-info btn-sm text-white mr-1" href="{{ route('admin.orders.show', $order->id) }}" title="Xem chi tiết & Cập nhật">
                                            <i class="fa fa-eye"></i> Chi tiết
                                        </a>
                                        <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="mb-0" onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Xóa đơn">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Chưa có đơn hàng nào trong hệ thống.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
                <div class="card-footer bg-white d-flex justify-content-center">
                    {{ $orders->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAllOrders');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    const badge = document.getElementById('selectedOrderCount');
    const btnSubmit = document.getElementById('btnBulkOrderSubmit');
    const selectStatus = document.getElementById('bulkOrderStatus');
    const selectPayment = document.getElementById('bulkOrderPayment');

    function updateState() {
        const checked = document.querySelectorAll('.order-checkbox:checked');
        const count = checked.length;
        if (badge) badge.textContent = count;

        const hasSelection = count > 0;
        const hasAction = (selectStatus.value !== '' || selectPayment.value !== '');

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
    if (selectPayment) selectPayment.addEventListener('change', updateState);
});
</script>
@endsection
