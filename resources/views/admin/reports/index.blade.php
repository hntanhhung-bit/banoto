@extends('layouts.admin')

@section('title', 'Báo cáo doanh thu & Thống kê')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-line-chart text-primary mr-2"></i> BÁO CÁO DOANH THU & THỐNG KÊ KINH DOANH
            </h3>
            <p class="text-muted small mb-0">
                Dữ liệu doanh thu tích hợp đồng bộ từ sổ giao dịch thanh toán thực nhận (MoMo, VietQR & Showroom), chỉ tính các giao dịch đã thanh toán thành công.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.reports.charts') }}" class="btn btn-primary font-weight-bold shadow-sm">
                <i class="fa fa-bar-chart mr-1"></i> Xem Biểu Đồ Trực Quan (Charts) &rarr;
            </a>
        </div>
    </div>

    <!-- TABS ĐIỀU HƯỚNG BÁO CÁO -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold bg-primary shadow-sm" href="{{ route('admin.reports.index') }}">
                <i class="fa fa-table mr-1"></i> Bảng số liệu chi tiết
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link font-weight-bold bg-white text-primary border shadow-sm" href="{{ route('admin.reports.charts') }}">
                <i class="fa fa-pie-chart mr-1"></i> Biểu đồ trực quan (Chart.js)
            </a>
        </li>
    </ul>

    <!-- KPI CARDS -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #3b82f6 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Tổng GD đã thanh toán</span>
                        <div class="h3 font-weight-bold text-primary mb-0 mt-1">{{ number_format($totalPaidTx) }} <span style="font-size: 15px; font-weight: normal;">GD</span></div>
                        <small class="text-muted">Khớp lệnh MoMo & VietQR</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #eff6ff; color: #3b82f6;">
                        <i class="fa fa-credit-card fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #8b5cf6 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Tổng số khách hàng</span>
                        <div class="h3 font-weight-bold text-purple mb-0 mt-1" style="color: #7c3aed;">{{ number_format($totalCustomers) }}</div>
                        <small class="text-muted">Tài khoản khách hàng đăng ký</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #f5f3ff; color: #8b5cf6;">
                        <i class="fa fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #10b981 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Tổng doanh thu thực nhận</span>
                        <div class="h3 font-weight-bold text-success mb-0 mt-1">{{ number_format($totalRevenue, 0, ',', '.') }} đ</div>
                        <small class="text-success"><i class="fa fa-check-circle"></i> MoMo: {{ number_format($totalMomoRevenue, 0, ',', '.') }} đ | NH: {{ number_format($totalBankRevenue, 0, ',', '.') }} đ</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #ecfdf5; color: #10b981;">
                        <i class="fa fa-money fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #f59e0b !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Hoa hồng sàn thuê xe (10%)</span>
                        <div class="h3 font-weight-bold text-warning mb-0 mt-1" style="color: #d97706 !important;">{{ number_format($totalPlatformRentalFee, 0, ',', '.') }} đ</div>
                        <small class="text-muted">Tổng doanh thu thuê: {{ number_format($totalRentalRevenue, 0, ',', '.') }} đ</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #fffbeb; color: #f59e0b;">
                        <i class="fa fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG 1: DOANH THU THEO DANH MỤC / HÃNG XE -->
    <div class="card shadow-sm border-0 mb-4 rounded-lg overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="font-weight-bold text-dark" style="font-size: 15px;">
                <i class="fa fa-tags text-primary mr-1"></i> Doanh thu theo danh mục / Hãng xe
            </div>
            <div class="small text-muted">Thống kê phân bổ theo hãng xe và dịch vụ từ các giao dịch đã thanh toán thành công.</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 small">
                    <thead class="bg-light font-weight-bold text-muted text-uppercase">
                        <tr>
                            <th class="py-3 px-3">Danh mục / Hãng xe</th>
                            <th class="py-3 text-right">Số lượt giao dịch</th>
                            <th class="py-3 text-right px-3">Doanh thu thực thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categoryRevenue as $revenue)
                        <tr>
                            <td class="py-3 px-3 font-weight-bold text-dark">
                                <i class="fa fa-car text-secondary mr-2"></i> {{ $revenue->category_name ?? ('Danh mục #'.$revenue->category_id) }}
                            </td>
                            <td class="py-3 text-right font-weight-bold">{{ number_format($revenue->total_qty) }} lượt</td>
                            <td class="py-3 text-right px-3 font-weight-bold text-success" style="font-size: 14px;">
                                {{ number_format($revenue->total_revenue, 0, ',', '.') }} đ
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu doanh thu theo danh mục.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CÁC BẢNG DOANH THU THEO THỜI GIAN (NGÀY, THÁNG, NĂM) -->
    @foreach([
        ['Doanh thu theo ngày (Chi tiết)', 'Ngày giao dịch', 'date', $revenueByDate, 'd/m/Y', 'calendar'],
        ['Doanh thu theo tháng', 'Tháng giao dịch', 'month', $revenueByMonth, 'm/Y', 'calendar-o'],
        ['Doanh thu theo năm', 'Năm tài chính', 'year', $revenueByYear, null, 'calendar-check-o'],
    ] as [$title, $label, $field, $rows, $format, $icon])
    <div class="card shadow-sm border-0 mb-4 rounded-lg overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom font-weight-bold text-dark" style="font-size: 15px;">
            <i class="fa fa-{{ $icon }} text-info mr-1"></i> {{ $title }}
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 small">
                    <thead class="bg-light font-weight-bold text-muted text-uppercase">
                        <tr>
                            <th class="py-3 px-3">{{ $label }}</th>
                            <th class="py-3 text-right">Số giao dịch đã thanh toán</th>
                            <th class="py-3 text-right px-3">Doanh thu thu về</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $rev)
                        <tr>
                            <td class="py-3 px-3 font-weight-bold text-dark">
                                {{ $format ? \Carbon\Carbon::parse($rev->{$field}.($field === 'month' ? '-01' : ''))->format($format) : $rev->{$field} }}
                            </td>
                            <td class="py-3 text-right font-weight-bold">
                                <span class="badge badge-light border px-2 py-1">{{ number_format($rev->order_count) }} GD</span>
                            </td>
                            <td class="py-3 text-right px-3 font-weight-bold text-primary" style="font-size: 14px;">
                                {{ number_format($rev->total_revenue, 0, ',', '.') }} đ
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu doanh thu ghi nhận.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
