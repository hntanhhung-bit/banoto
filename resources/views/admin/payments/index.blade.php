@extends('layouts.admin')

@section('content')
<style>
    .kpi-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    }
    .momo-badge {
        background-color: #a50064 !important;
        color: #ffffff !important;
        font-weight: bold;
    }
    .momo-btn {
        background-color: #a50064 !important;
        border-color: #a50064 !important;
        color: #ffffff !important;
    }
    .momo-btn:hover {
        background-color: #83004f !important;
        color: #ffffff !important;
    }
</style>

<div class="container-fluid px-4">
    <!-- Tiêu đề trang -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <span class="badge momo-badge px-3 py-2 mr-2" style="font-size: 18px;">
                    <i class="fa fa-qrcode mr-1"></i> MoMo
                </span>
                BÁO CÁO & QUẢN LÝ GIAO DỊCH THANH TOÁN
            </h3>
            <p class="text-muted mb-0">Theo dõi nhật ký tài chính, đối soát thanh toán MoMo, VietQR và tiền mặt theo thời gian thực.</p>
        </div>
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary font-weight-bold mr-1">
                <i class="fa fa-shopping-cart mr-1"></i> Quản lý Đơn mua xe
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary font-weight-bold">
                <i class="fa fa-tachometer mr-1"></i> Bảng điều khiển
            </a>
        </div>
    </div>

    <!-- 4 THẺ KPI BÁO CÁO DOANH THU & GIAO DỊCH MOMO -->
    <div class="row mb-4">
        <!-- KPI 1: Doanh thu qua MoMo -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card kpi-card p-3 bg-white border-left" style="border-left: 5px solid #a50064 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Doanh thu qua MoMo</div>
                        <h3 class="font-weight-bold mb-0" style="color: #a50064;">
                            {{ number_format($totalMomoRevenue) }} <small style="font-size: 14px;">đ</small>
                        </h3>
                    </div>
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background-color: #fff0f6; color: #a50064; width: 55px; height: 55px; font-size: 24px;">
                        <i class="fa fa-credit-card"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top small text-muted d-flex justify-content-between">
                    <span>Đã thực thu thành công</span>
                    <strong class="text-success">{{ $totalMomoSuccess }} giao dịch</strong>
                </div>
            </div>
        </div>

        <!-- KPI 2: Tỉ lệ thành công MoMo -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card kpi-card p-3 bg-white border-left" style="border-left: 5px solid #28a745 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Tỉ lệ MoMo thành công</div>
                        <h3 class="font-weight-bold text-success mb-0">
                            {{ $momoSuccessRate }}%
                        </h3>
                    </div>
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center bg-light text-success" style="width: 55px; height: 55px; font-size: 24px;">
                        <i class="fa fa-line-chart"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top small text-muted d-flex justify-content-between">
                    <span>Tổng lượt gọi MoMo: <strong>{{ $totalMomoCount }}</strong></span>
                    <span class="text-danger">Lỗi/Hủy: <strong>{{ $totalMomoFailed }}</strong></span>
                </div>
            </div>
        </div>

        <!-- KPI 3: Doanh thu VietQR & Khác -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card kpi-card p-3 bg-white border-left" style="border-left: 5px solid #17a2b8 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Doanh thu VietQR / COD</div>
                        <h3 class="font-weight-bold text-info mb-0">
                            {{ number_format($totalBankRevenue + $totalCodRevenue) }} <small style="font-size: 14px;">đ</small>
                        </h3>
                    </div>
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center bg-light text-info" style="width: 55px; height: 55px; font-size: 24px;">
                        <i class="fa fa-university"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top small text-muted d-flex justify-content-between">
                    <span>VietQR: <strong>{{ number_format($totalBankRevenue) }} đ</strong></span>
                    <span>COD: <strong>{{ number_format($totalCodRevenue) }} đ</strong></span>
                </div>
            </div>
        </div>

        <!-- KPI 4: Tổng thu tất cả các cổng -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card kpi-card p-3 bg-white border-left" style="border-left: 5px solid #ffc107 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Tổng doanh thu thực nhận</div>
                        <h3 class="font-weight-bold text-dark mb-0">
                            {{ number_format($grandTotalRevenue) }} <small style="font-size: 14px;">đ</small>
                        </h3>
                    </div>
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center bg-light text-warning" style="width: 55px; height: 55px; font-size: 24px;">
                        <i class="fa fa-money"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top small text-muted d-flex justify-content-between">
                    <span>Bao gồm xe bán & cước giao</span>
                    <strong class="text-primary">100% đối soát</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- BỘ LỌC TÌM KIẾM GIAO DỊCH -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.payments.index') }}">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <input type="text" name="keyword" class="form-control" placeholder="Tìm TransID, Mã đơn, Khách hàng..." value="{{ request('keyword') }}">
                    </div>
                    <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                        <select name="gateway" class="form-control">
                            <option value="">-- Tất cả cổng --</option>
                            <option value="sepay" {{ request('gateway') === 'sepay' ? 'selected' : '' }}>Cổng SePay QR</option>
                            <option value="momo" {{ request('gateway') === 'momo' ? 'selected' : '' }}>Ví MoMo</option>
                            <option value="bank_transfer" {{ request('gateway') === 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản VietQR</option>
                            <option value="cod" {{ request('gateway') === 'cod' ? 'selected' : '' }}>COD / Showroom</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                        <select name="status" class="form-control">
                            <option value="">-- Trạng thái --</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>✅ Thành công (Paid)</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Đang chờ (Pending)</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>❌ Thất bại / Hủy (Failed)</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                        <input type="date" name="date_from" class="form-control" title="Từ ngày" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-lg-3 col-md-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary font-weight-bold mr-1">
                            <i class="fa fa-filter mr-1"></i> Lọc
                        </button>
                        @if(request()->hasAny(['keyword', 'gateway', 'status', 'date_from', 'date_to']))
                            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary font-weight-bold">
                                <i class="fa fa-times mr-1"></i> Xóa lọc
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- BẢNG NHẬT KÝ GIAO DỊCH PAYMENT TRANSACTIONS -->
    <div class="card shadow-sm border-0" style="border-radius: 10px; overflow: hidden;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="font-weight-bold text-dark mb-0">
                <i class="fa fa-list-alt text-primary mr-1"></i> Danh sách Nhật ký Giao dịch (Payment Transactions)
            </h5>
            <span class="badge badge-light border text-muted">
                Tổng cộng: {{ $transactions->total() }} bản ghi
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped m-0 align-middle">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>ID</th>
                            <th>Thời gian GD</th>
                            <th>Cổng thanh toán</th>
                            <th>Mã GD Cổng (Trans ID)</th>
                            <th>Mã đơn hàng</th>
                            <th>Khách hàng</th>
                            <th class="text-center">Số tiền</th>
                            <th class="text-center">Trạng thái</th>
                            <th>Thông điệp cổng MoMo</th>
                            <th class="text-center">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $t)
                        <tr>
                            <td class="font-weight-bold text-muted align-middle">#{{ $t->id }}</td>
                            <td class="align-middle text-muted small">
                                <div>{{ $t->created_at->format('d/m/Y H:i:s') }}</div>
                                @if($t->paid_at)
                                    <small class="text-success font-weight-bold"><i class="fa fa-check"></i> Đã trả: {{ $t->paid_at->format('H:i:s') }}</small>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($t->gateway === 'sepay')
                                    <span class="badge badge-primary px-2 py-1">
                                        <i class="fa fa-qrcode mr-1"></i> SePay QR
                                    </span>
                                @elseif($t->gateway === 'momo')
                                    <span class="badge momo-badge px-2 py-1">
                                        <i class="fa fa-credit-card mr-1"></i> MoMo ATM
                                    </span>
                                @elseif($t->gateway === 'bank_transfer')
                                    <span class="badge badge-info px-2 py-1">
                                        <i class="fa fa-university mr-1"></i> VietQR
                                    </span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1">
                                        <i class="fa fa-money mr-1"></i> COD / Showroom
                                    </span>
                                @endif
                            </td>
                            <td class="align-middle font-weight-bold text-dark small">
                                @if($t->transaction_id)
                                    <span class="badge badge-light border text-primary" style="font-size: 12px;">{{ $t->transaction_id }}</span>
                                @elseif($t->gateway_order_id)
                                    <span class="badge badge-light border text-muted" style="font-size: 11px;">{{ $t->gateway_order_id }}</span>
                                @else
                                    <span class="text-muted font-italic">Chưa phát sinh</span>
                                @endif
                            </td>
                            <td class="align-middle font-weight-bold">
                                @if($t->order)
                                    <a href="{{ route('admin.orders.show', $t->order->id) }}" class="text-primary font-weight-bold">
                                        <span class="badge badge-primary px-2 py-1 mb-1">Mua xe</span><br>
                                        #{{ $t->order->order_code }}
                                    </a>
                                @elseif($t->rental)
                                    <a href="{{ route('admin.rentals.show', $t->rental->id) }}" class="text-danger font-weight-bold">
                                        <span class="badge badge-danger px-2 py-1 mb-1">Cọc thuê</span><br>
                                        #{{ $t->rental->rental_code }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($t->order)
                                    <div class="font-weight-bold text-dark">{{ $t->order->customer_name }}</div>
                                    <div class="small text-muted">{{ $t->order->customer_phone }}</div>
                                @elseif($t->rental)
                                    <div class="font-weight-bold text-dark">{{ $t->rental->customer_name }}</div>
                                    <div class="small text-muted">{{ $t->rental->customer_phone }}</div>
                                @else
                                    <span class="text-muted">Khách vãng lai</span>
                                @endif
                            </td>
                            <td class="text-center align-middle font-weight-bold text-danger" style="font-size: 15px;">
                                {{ number_format($t->amount) }} đ
                            </td>
                            <td class="text-center align-middle">
                                @if($t->status === 'paid')
                                    <span class="badge badge-success px-2 py-1">
                                        <i class="fa fa-check-circle"></i> Thành công
                                    </span>
                                @elseif($t->status === 'failed')
                                    <span class="badge badge-danger px-2 py-1">
                                        <i class="fa fa-times-circle"></i> Thất bại
                                    </span>
                                @else
                                    <span class="badge badge-warning text-dark px-2 py-1">
                                        <i class="fa fa-clock-o"></i> Chờ thanh toán
                                    </span>
                                @endif
                            </td>
                            <td class="align-middle small">
                                <div class="text-muted">{{ $t->message ?: 'N/A' }}</div>
                                @if(!is_null($t->result_code))
                                    <span class="badge badge-light border">Mã: {{ $t->result_code }}</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                @if($t->response_payload)
                                    <button type="button" class="btn btn-sm btn-outline-info font-weight-bold" data-toggle="modal" data-target="#payloadModal{{ $t->id }}" title="Xem dữ liệu MoMo trả về">
                                        <i class="fa fa-code"></i> Payload
                                    </button>

                                    <!-- Modal Xem Payload JSON -->
                                    <div class="modal fade" id="payloadModal{{ $t->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg text-left" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header bg-dark text-white">
                                                    <h5 class="modal-title font-weight-bold">
                                                        <i class="fa fa-code text-warning mr-1"></i> Chi tiết Payload MoMo - GD #{{ $t->id }}
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body p-3 bg-light">
                                                    <h6 class="font-weight-bold text-primary mb-2">Response Payload (Webhook / IPN / Callback):</h6>
                                                    <pre class="bg-white p-3 border rounded text-dark" style="max-height: 250px; overflow-y: auto; font-size: 13px;">{{ json_encode($t->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

                                                    @if($t->request_payload)
                                                        <h6 class="font-weight-bold text-success mb-2 mt-3">Request Payload gửi sang MoMo:</h6>
                                                        <pre class="bg-white p-3 border rounded text-dark" style="max-height: 200px; overflow-y: auto; font-size: 13px;">{{ json_encode($t->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Đóng</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small font-italic">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fa fa-credit-card fa-3x text-muted mb-2"></i>
                                <p class="mb-0">Chưa có giao dịch thanh toán nào phù hợp với điều kiện tìm kiếm.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center py-3">
                {{ $transactions->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection
