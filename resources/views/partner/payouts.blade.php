@extends('partner.layout')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h3 class="font-weight-bold text-dark mb-1">
            <i class="fa fa-money text-success mr-2"></i> BẢNG ĐỐI SOÁT DOANH THU & HOA HỒNG GIỚI THIỆU
        </h3>
        <p class="text-muted small mb-0">Hệ thống phân chia tự động: Hoa hồng Môi giới Bán xe (1%) & Phân chia Doanh thu Thuê xe (10% Sàn - 90% Showroom)</p>
    </div>
    <div class="mt-2 mt-md-0">
        <a href="{{ route('partner.reports.charts') }}" class="btn btn-success font-weight-bold shadow-sm">
            <i class="fa fa-bar-chart mr-1"></i> Xem Biểu Đồ Trực Quan (Charts) &rarr;
        </a>
    </div>
</div>

<!-- TABS ĐIỀU HƯỚNG BÁO CÁO -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link font-weight-bold bg-white text-success border shadow-sm" href="{{ route('partner.reports.index') }}">
            <i class="fa fa-table mr-1"></i> Bảng số liệu chi tiết
        </a>
    </li>
    <li class="nav-item ml-2">
        <a class="nav-link font-weight-bold bg-white text-success border shadow-sm" href="{{ route('partner.reports.charts') }}">
            <i class="fa fa-pie-chart mr-1"></i> Biểu đồ trực quan (Chart.js)
        </a>
    </li>
    <li class="nav-item ml-2">
        <a class="nav-link active font-weight-bold bg-success shadow-sm" href="{{ route('partner.payouts') }}">
            <i class="fa fa-file-text-o mr-1"></i> Sổ đối soát hợp đồng
        </a>
    </li>
</ul>

<!-- THẺ TỔNG HỢP TÀI CHÍNH 2 DÒNG TIỀN -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #8b5cf6 !important;">
            <span class="text-muted small font-weight-bold text-uppercase">Hoa hồng Giới thiệu Bán xe</span>
            <div class="h3 font-weight-bold text-purple mb-0 mt-1" style="color: #7c3aed;">{{ number_format($totalSalesCommission) }} đ</div>
            <small class="text-muted">1% trên các ca bán xe thành công</small>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #3b82f6 !important;">
            <span class="text-muted small font-weight-bold text-uppercase">Tổng tiền dịch vụ thuê</span>
            <div class="h3 font-weight-bold text-dark mb-0 mt-1">{{ number_format($totalGross) }} đ</div>
            <small class="text-muted">Tiền thuê ngày + phí tài xế</small>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #ef4444 !important;">
            <span class="text-muted small font-weight-bold text-uppercase">Phí Sàn Giới thiệu Thuê (10%)</span>
            <div class="h3 font-weight-bold text-danger mb-0 mt-1">-{{ number_format($totalPlatformCommission) }} đ</div>
            <small class="text-muted">Chi phí kết nối khách & bảo lãnh cọc</small>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #10b981 !important;">
            <span class="text-muted small font-weight-bold text-uppercase">Thuê xe nhận về (90%)</span>
            <div class="h3 font-weight-bold text-success mb-0 mt-1">+{{ number_format($totalPartnerNet) }} đ</div>
            <small class="text-success"><i class="fa fa-check-circle"></i> Tiền thực nhận về Showroom</small>
        </div>
    </div>
</div>

<!-- BẢNG 1: HOA HỒNG GIỚI THIỆU BÁN XE THÀNH CÔNG -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="font-weight-bold text-dark mb-0">
            <i class="fa fa-handshake-o text-purple mr-1" style="color: #7c3aed;"></i> 1. Các Hợp Đồng Mua Bán Xe Đã Chốt Thành Công (Hoa hồng 1%)
        </h5>
        <span class="badge badge-success px-3 py-1 font-weight-bold">{{ $soldCarAppointments->count() }} Giao dịch thành công</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-light font-weight-bold text-muted text-uppercase">
                    <tr>
                        <th>Mã Lịch Hẹn</th>
                        <th>Khách Mua Xe</th>
                        <th>Mẫu Xe Chốt Bán</th>
                        <th>Giá Trị Xe Thực Bán</th>
                        <th>Tỷ Lệ Môi Giới</th>
                        <th>Hoa Hồng Sàn Nhận</th>
                        <th>Trạng Thái Đối Soát</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($soldCarAppointments as $app)
                    <tr>
                        <td>
                            <strong class="text-primary font-weight-bold">#{{ $app->appointment_code }}</strong>
                            <div class="text-muted">{{ $app->updated_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $app->customer_name }}</div>
                            <div class="text-muted">{{ $app->customer_phone }}</div>
                        </td>
                        <td>
                            <strong class="text-dark">{{ $app->product->name ?? 'Xe' }}</strong>
                            <div class="text-muted">{{ $app->selected_color }}</div>
                        </td>
                        <td>
                            <strong class="text-dark" style="font-size: 14px;">{{ number_format($app->deal_price ?: ($app->product?->price ?: 0)) }} VNĐ</strong>
                        </td>
                        <td>
                            <span class="badge badge-info px-2 py-1 font-weight-bold">1.0%</span>
                        </td>
                        <td>
                            <strong class="text-danger font-weight-bold" style="font-size: 14px;">{{ number_format($app->commission_amount) }} VNĐ</strong>
                        </td>
                        <td>
                            @if($app->commission_status === 'paid')
                                <span class="badge badge-success px-2 py-1"><i class="fa fa-check"></i> Đã thanh toán</span>
                            @else
                                <span class="badge badge-warning px-2 py-1"><i class="fa fa-clock-o"></i> Chờ đối soát tháng</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Chưa có giao dịch chốt bán xe nào được ghi nhận. Khi có khách mua xe thành công, hãy bấm "Chốt bán xe" tại mục Lịch hẹn xem xe!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- BẢNG 2: PHÂN CHIA DOANH THU CHO THUÊ XE (10% - 90%) -->
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-key text-warning mr-1"></i> 2. Hợp Đồng Cho Thuê Xe & Phân Bổ Doanh Thu (10% - 90%)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-light font-weight-bold text-muted text-uppercase">
                    <tr>
                        <th>Mã Hợp Đồng</th>
                        <th>Khách hàng</th>
                        <th>Mẫu Xe</th>
                        <th>Tổng tiền thuê</th>
                        <th>Phí Sàn Giới Thiệu (10%)</th>
                        <th>Thực nhận về Showroom (90%)</th>
                        <th>Ký quỹ cọc</th>
                        <th>Thanh toán</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rentals as $rental)
                    @php
                        $serviceFee = $rental->total_rental_fee + $rental->total_driver_fee;
                        $platFee = $rental->platform_fee ?: round($serviceFee * 0.10);
                        $netPayout = $rental->partner_payout ?: ($serviceFee - $platFee);
                    @endphp
                    <tr>
                        <td>
                            <strong class="text-primary font-weight-bold">#{{ $rental->rental_code }}</strong>
                            <div class="text-muted">{{ $rental->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $rental->customer_name }}</div>
                            <div class="text-muted">{{ $rental->customer_phone }}</div>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $rental->product->name ?? 'Xe' }}</div>
                            <div class="text-muted">{{ $rental->total_days }} ngày ({{ date('d/m', strtotime($rental->start_date)) }} - {{ date('d/m', strtotime($rental->end_date)) }})</div>
                        </td>
                        <td>
                            <strong class="text-dark">{{ number_format($serviceFee) }} đ</strong>
                        </td>
                        <td>
                            <span class="text-danger font-weight-bold">-{{ number_format($platFee) }} đ</span>
                        </td>
                        <td>
                            <strong class="text-success font-weight-bold" style="font-size: 14px;">+{{ number_format($netPayout) }} đ</strong>
                        </td>
                        <td>
                            @if($rental->refund_status === 'refunded')
                                <span class="badge badge-success px-2 py-1"><i class="fa fa-check"></i> Đã hoàn cọc</span>
                            @elseif($rental->refund_status === 'holding')
                                <span class="badge badge-warning px-2 py-1"><i class="fa fa-clock-o"></i> Ký quỹ cọc</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1">Chưa cọc</span>
                            @endif
                        </td>
                        <td>
                            @if($rental->payment_status === 'fully_paid' || $rental->payment_status === 'deposit_paid')
                                <span class="badge badge-success px-2 py-1">Đã quyết toán</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1">Chờ thanh toán</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Chưa có giao dịch thuê xe nào được ghi nhận.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $rentals->links() }}
</div>
@endsection
