@extends('layouts.admin')

@section('content')
<style>
    .stat-card {
        border-radius: 12px;
        border: none;
        transition: transform 0.25s, box-shadow 0.25s;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
    }
</style>

<div class="container-fluid px-4">
    <!-- Tiêu đề Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-tachometer text-primary mr-2"></i> BẢNG ĐIỀU KHIỂN ĐẶT LỊCH & THUÊ XE & MUA BÁN
            </h3>
            <p class="text-muted mb-0">Xin chào, <strong>{{ Auth::user()->name }}</strong>! Hệ thống quản lý bán xe, thanh toán MoMo và dịch vụ thuê xe đang hoạt động ổn định.</p>
        </div>
        <div>
            <a href="{{ route('admin.payments.index') }}" class="btn font-weight-bold mr-1 text-white shadow-sm" style="background-color: #a50064;">
                <i class="fa fa-qrcode mr-1"></i> Báo cáo MoMo & Thu chi
            </a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-warning font-weight-bold mr-1 text-dark shadow-sm">
                <i class="fa fa-shopping-cart mr-1"></i> Đơn mua xe
            </a>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-success font-weight-bold mr-1">
                <i class="fa fa-calendar-check-o"></i> Lịch hẹn
            </a>
            <a href="{{ route('admin.rentals.index') }}" class="btn btn-danger font-weight-bold">
                <i class="fa fa-key"></i> Đơn thuê
            </a>
        </div>
    </div>

    <!-- 4 THẺ THỐNG KÊ SỐ LIỆU TRỌNG TÂM -->
    <div class="row mb-4">
        <!-- Thẻ 1: Lịch hẹn xem xe -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success text-white mr-3">
                        <i class="fa fa-calendar-check-o"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Lịch hẹn xem xe</div>
                        <h3 class="font-weight-bold text-dark mb-0">{{ $totalAppointments }} <small style="font-size: 15px;">lịch</small></h3>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold text-warning">{{ $pendingAppointments }} chờ duyệt</span>
                    <a href="{{ route('admin.appointments.index') }}" class="small font-weight-bold text-success">Chi tiết &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Thẻ 2: Đơn thuê xe & Tài xế -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-danger text-white mr-3">
                        <i class="fa fa-key"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Tổng đơn thuê xe</div>
                        <h3 class="font-weight-bold text-dark mb-0">{{ $totalRentals }} <small style="font-size: 15px;">hợp đồng</small></h3>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">{{ $selfDriveRentals }} tự lái / {{ $withDriverRentals }} có tài xế</span>
                    <a href="{{ route('admin.rentals.index') }}" class="small font-weight-bold text-danger">Quản lý &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Thẻ 3: Doanh thu tiền thuê -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary text-white mr-3">
                        <i class="fa fa-money"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Doanh thu tiền thuê</div>
                        <h4 class="font-weight-bold text-primary mb-0" style="font-size: 19px;">
                            {{ number_format($totalRentalRevenue) }} <small>VNĐ</small>
                        </h4>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">{{ $activeRentals }} đơn đang phục vụ</span>
                    <span class="badge badge-success">Đã thu</span>
                </div>
            </div>
        </div>

        <!-- Thẻ 4: Xe trong hệ thống -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info text-white mr-3">
                        <i class="fa fa-car"></i>
                    </div>
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Kho xe phục vụ</div>
                        <h3 class="font-weight-bold text-dark mb-0">{{ $totalProducts }} <small style="font-size: 15px;">xe</small></h3>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">{{ $totalCategories }} thương hiệu</span>
                    <a href="{{ route('admin.products.index') }}" class="small font-weight-bold text-info">Xem kho &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- HÀNG THẺ KPI: THANH TOÁN MOMO & ĐƠN MUA XE SHOWROOM -->
    <div class="row mb-4">
        <!-- Thẻ MoMo Revenue -->
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #a50064 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon mr-3 text-white" style="background-color: #a50064;">
                            <i class="fa fa-qrcode"></i>
                        </div>
                        <div>
                            <div class="text-muted small font-weight-bold text-uppercase">Doanh thu qua MoMo</div>
                            <h4 class="font-weight-bold mb-0" style="color: #a50064; font-size: 20px;">
                                {{ number_format($totalMomoRevenue) }} <small>VNĐ</small>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold text-success">{{ $totalMomoSuccess }} giao dịch thành công</span>
                    <a href="{{ route('admin.payments.index') }}" class="small font-weight-bold" style="color: #a50064;">Báo cáo MoMo &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Thẻ Đơn mua xe -->
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #ffc107 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning text-dark mr-3">
                            <i class="fa fa-shopping-cart"></i>
                        </div>
                        <div>
                            <div class="text-muted small font-weight-bold text-uppercase">Đơn mua xe Showroom</div>
                            <h3 class="font-weight-bold text-dark mb-0">{{ $totalOrders }} <small style="font-size: 15px;">đơn</small></h3>
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold text-danger">{{ $pendingOrders }} đơn chờ tiếp nhận</span>
                    <a href="{{ route('admin.orders.index') }}" class="small font-weight-bold text-warning text-dark">Quản lý &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Thẻ Doanh thu bán xe -->
        <div class="col-xl-4 col-md-12 mb-3">
            <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #28a745 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success text-white mr-3">
                            <i class="fa fa-line-chart"></i>
                        </div>
                        <div>
                            <div class="text-muted small font-weight-bold text-uppercase">Doanh thu bán xe (Đã thu)</div>
                            <h4 class="font-weight-bold text-success mb-0" style="font-size: 20px;">
                                {{ number_format($totalOrderRevenue) }} <small>VNĐ</small>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Bao gồm cước bàn giao</span>
                    <a href="{{ route('admin.orders.index') }}" class="small font-weight-bold text-success">Chi tiết &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 BẢNG THÔNG TIN MỚI NHẤT (LỊCH HẸN & ĐƠN THUÊ) -->
    <div class="row">
        <!-- Bảng 1: Lịch hẹn xem xe mới nhất -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-calendar-check-o text-success mr-1"></i> Lịch hẹn xem xe mới
                    </h5>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-success font-weight-bold">
                        Xem tất cả
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th>Khách hàng</th>
                                    <th>Mẫu xe</th>
                                    <th>Thời gian</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestAppointments as $app)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $app->customer_name }}</div>
                                        <small class="text-muted">{{ $app->customer_phone }}</small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-primary">{{ $app->product->name ?? 'Xe' }}</div>
                                    </td>
                                    <td>
                                        <small class="d-block">{{ date('d/m/Y', strtotime($app->appointment_date)) }}</small>
                                        <small class="text-muted">{{ $app->appointment_time }}</small>
                                    </td>
                                    <td>
                                        @if($app->status === 'pending')
                                            <span class="badge badge-warning">Chờ duyệt</span>
                                        @elseif($app->status === 'confirmed')
                                            <span class="badge badge-success">Đã duyệt</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $app->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Chưa có lịch hẹn xem xe nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng 2: Đơn thuê xe mới nhất -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-key text-danger mr-1"></i> Đơn thuê xe & Tài xế mới
                    </h5>
                    <a href="{{ route('admin.rentals.index') }}" class="btn btn-sm btn-outline-danger font-weight-bold">
                        Xem tất cả
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th>Mã đơn & Loại</th>
                                    <th>Khách hàng</th>
                                    <th>Xe & Chi phí</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestRentals as $rental)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.rentals.show', $rental->id) }}" class="font-weight-bold text-primary text-decoration-none">
                                            #{{ $rental->rental_code }}
                                        </a>
                                        @if($rental->rental_type === 'with_driver')
                                            <span class="badge badge-primary d-block mt-1">Có tài xế</span>
                                        @else
                                            <span class="badge badge-danger d-block mt-1">Tự lái</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $rental->customer_name }}</div>
                                        <small class="text-muted">{{ $rental->customer_phone }}</small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $rental->product->name ?? 'Xe' }}</div>
                                        <small class="text-danger font-weight-bold">{{ number_format($rental->total_amount) }} đ</small>
                                    </td>
                                    <td>
                                        @if($rental->rental_status === 'pending')
                                            <span class="badge badge-warning">Chờ duyệt</span>
                                        @elseif($rental->rental_status === 'confirmed')
                                            <span class="badge badge-info">Đã duyệt</span>
                                        @elseif($rental->rental_status === 'in_progress')
                                            <span class="badge badge-primary">Đang phục vụ</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $rental->rental_status }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Chưa có đơn thuê xe nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HÀNG 2 BẢNG: ĐƠN MUA XE MỚI & GIAO DỊCH THANH TOÁN MOMO MỚI NHẤT -->
    <div class="row">
        <!-- Bảng Đơn mua xe -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-shopping-cart text-warning mr-1"></i> Đơn mua xe Showroom mới nhất
                    </h5>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-warning font-weight-bold text-dark">
                        Xem tất cả
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng thanh toán</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestOrders as $ord)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $ord->id) }}" class="font-weight-bold text-primary text-decoration-none">
                                            #{{ $ord->order_code }}
                                        </a>
                                        <div class="small text-muted mt-1">
                                            @if($ord->payment_method === 'momo')
                                                <span class="badge text-white" style="background-color: #a50064;">MoMo</span>
                                            @elseif($ord->payment_method === 'bank_transfer')
                                                <span class="badge badge-info">VietQR</span>
                                            @else
                                                <span class="badge badge-secondary">COD</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $ord->customer_name }}</div>
                                        <small class="text-muted">{{ $ord->customer_phone }}</small>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-danger">{{ number_format($ord->total_amount) }} đ</span>
                                    </td>
                                    <td>
                                        @if($ord->payment_status === 'paid')
                                            <span class="badge badge-success">Đã thanh toán</span>
                                        @else
                                            <span class="badge badge-warning text-dark">Chờ thanh toán</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Chưa có đơn mua xe nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng Giao dịch MoMo mới nhất -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #a50064 !important;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <span class="badge mr-1 px-2 py-1 text-white" style="background-color: #a50064;">
                            <i class="fa fa-qrcode mr-1"></i> MoMo
                        </span>
                        Giao dịch MoMo gần nhất
                    </h5>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm font-weight-bold text-white shadow-sm" style="background-color: #a50064;">
                        Báo cáo chi tiết
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Trans ID / Mã GD</th>
                                    <th>Số tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestMomoTransactions as $tx)
                                <tr>
                                    <td class="small text-muted">
                                        {{ $tx->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        <strong class="text-dark small">{{ $tx->transaction_id ?: ($tx->gateway_order_id ?: 'Chờ kết nối') }}</strong>
                                        @if($tx->order)
                                            <div class="small text-muted">Đơn xe: #{{ $tx->order->order_code }}</div>
                                        @elseif($tx->rental)
                                            <div class="small text-danger">Đơn thuê: #{{ $tx->rental->rental_code }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-danger font-weight-bold">{{ number_format($tx->amount) }} đ</strong>
                                    </td>
                                    <td>
                                        @if($tx->status === 'paid')
                                            <span class="badge badge-success px-2 py-1"><i class="fa fa-check"></i> Thành công</span>
                                        @elseif($tx->status === 'failed')
                                            <span class="badge badge-danger px-2 py-1"><i class="fa fa-times"></i> Lỗi / Hủy</span>
                                        @else
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-clock-o"></i> Chờ GD</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Chưa có giao dịch MoMo nào phát sinh.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection