@extends('partner.layout')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-line-chart text-success mr-2"></i> BÁO CÁO DOANH THU & THỐNG KÊ KINH DOANH SHOWROOM
            </h3>
            <p class="text-muted small mb-0">
                Thống kê số liệu kinh doanh dành riêng cho Showroom / Nhà xe: <strong>{{ $partnerUser->showroom_name ?: ($partnerUser->company_name ?: $partnerUser->name) }}</strong> (Chỉ tính các hợp đồng đã thanh toán thành công).
            </p>
        </div>
        <div class="d-flex align-items-center mt-2 mt-md-0">
            @if(Auth::user()->role === 'admin' && $allPartners->count() > 0)
            <form action="{{ route('partner.reports.index') }}" method="GET" class="mr-2">
                <select name="partner_id" class="form-control form-control-sm font-weight-bold border-success" onchange="this.form.submit()">
                    @foreach($allPartners as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $partnerId ? 'selected' : '' }}>
                        🏪 {{ $p->showroom_name ?: ($p->company_name ?: $p->name) }} (ID: {{ $p->id }})
                    </option>
                    @endforeach
                </select>
            </form>
            @endif
            <a href="{{ route('partner.reports.charts', ['partner_id' => $partnerId]) }}" class="btn btn-success font-weight-bold shadow-sm">
                <i class="fa fa-bar-chart mr-1"></i> Xem Biểu Đồ Trực Quan (Charts) &rarr;
            </a>
        </div>
    </div>

    <!-- TABS ĐIỀU HƯỚNG BÁO CÁO -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold bg-success shadow-sm" href="{{ route('partner.reports.index', ['partner_id' => $partnerId]) }}">
                <i class="fa fa-table mr-1"></i> Bảng số liệu chi tiết
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link font-weight-bold bg-white text-success border shadow-sm" href="{{ route('partner.reports.charts', ['partner_id' => $partnerId]) }}">
                <i class="fa fa-pie-chart mr-1"></i> Biểu đồ trực quan (Chart.js)
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link font-weight-bold bg-white text-muted border shadow-sm" href="{{ route('partner.payouts') }}">
                <i class="fa fa-file-text-o mr-1"></i> Sổ đối soát hợp đồng
            </a>
        </li>
    </ul>

    <!-- KPI CARDS MẪU TƯƠNG TỰ ADMIN -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #10b981 !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Thuê xe nhận về (90%)</span>
                        <div class="h3 font-weight-bold text-success mb-0 mt-1">+{{ number_format($totalPartnerNet, 0, ',', '.') }} đ</div>
                        <small class="text-success"><i class="fa fa-check-circle"></i> Tiền thực nhận về Showroom</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #ecfdf5; color: #10b981;">
                        <i class="fa fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #3b82f6 !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Tổng tiền dịch vụ thuê</span>
                        <div class="h3 font-weight-bold text-primary mb-0 mt-1">{{ number_format($totalGross, 0, ',', '.') }} đ</div>
                        <small class="text-danger font-weight-bold">Phí sàn (10%): -{{ number_format($totalPlatformCommission, 0, ',', '.') }} đ</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #eff6ff; color: #3b82f6;">
                        <i class="fa fa-money fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #8b5cf6 !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Hoa hồng bán xe (1%)</span>
                        <div class="h3 font-weight-bold mb-0 mt-1" style="color: #7c3aed;">{{ number_format($totalSalesCommission, 0, ',', '.') }} đ</div>
                        <small class="text-muted">{{ number_format($totalCarsSold) }} ca chốt bán xe thành công</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #f5f3ff; color: #8b5cf6;">
                        <i class="fa fa-handshake-o fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #f59e0b !important; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Tổng thu nhập đối tác</span>
                        <div class="h3 font-weight-bold mb-0 mt-1" style="color: #d97706 !important;">{{ number_format($totalPartnerIncome, 0, ',', '.') }} đ</div>
                        <small class="text-muted">Thuê xe (90%) + Môi giới bán xe</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #fffbeb; color: #f59e0b;">
                        <i class="fa fa-trophy fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG 1: DOANH THU THEO MẪU XE / HÃNG XE TRONG KHO SHOWROOM -->
    <div class="card shadow-sm border-0 mb-4 rounded-lg overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <div class="font-weight-bold text-dark" style="font-size: 15px;">
                    <i class="fa fa-car text-success mr-1"></i> Doanh thu theo mẫu xe của Showroom
                </div>
                <div class="small text-muted">Thống kê hiệu quả cho thuê của từng dòng xe thuộc quyền sở hữu của đối tác.</div>
            </div>
            <span class="badge badge-success px-3 py-1 font-weight-bold">{{ $carRevenue->count() }} dòng xe</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 small">
                    <thead class="bg-light font-weight-bold text-muted text-uppercase">
                        <tr>
                            <th class="py-3 px-3">Mẫu xe & Hãng</th>
                            <th class="py-3 text-right">Số lượt thuê</th>
                            <th class="py-3 text-right">Tổng tiền dịch vụ</th>
                            <th class="py-3 text-right text-danger">Phí sàn (10%)</th>
                            <th class="py-3 text-right px-3 text-success">Showroom nhận về (90%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($carRevenue as $car)
                        <tr>
                            <td class="py-3 px-3 font-weight-bold text-dark">
                                <i class="fa fa-car text-success mr-2"></i> {{ $car->car_name }}
                                <div class="small text-muted font-weight-normal">{{ $car->category_name }}</div>
                            </td>
                            <td class="py-3 text-right font-weight-bold">{{ number_format($car->total_qty) }} lượt</td>
                            <td class="py-3 text-right font-weight-bold text-dark">{{ number_format($car->gross_revenue, 0, ',', '.') }} đ</td>
                            <td class="py-3 text-right font-weight-bold text-danger">-{{ number_format($car->platform_fee, 0, ',', '.') }} đ</td>
                            <td class="py-3 text-right px-3 font-weight-bold text-success" style="font-size: 14px;">
                                +{{ number_format($car->partner_net, 0, ',', '.') }} đ
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có giao dịch thuê xe hoàn tất cho các mẫu xe của Showroom.</td>
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
            <i class="fa fa-{{ $icon }} text-success mr-1"></i> {{ $title }}
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 small">
                    <thead class="bg-light font-weight-bold text-muted text-uppercase">
                        <tr>
                            <th class="py-3 px-3">{{ $label }}</th>
                            <th class="py-3 text-right">Số hợp đồng</th>
                            <th class="py-3 text-right">Tổng tiền dịch vụ</th>
                            <th class="py-3 text-right text-danger">Phí sàn (10%)</th>
                            <th class="py-3 text-right px-3 text-success">Showroom nhận về (90%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $rev)
                        <tr>
                            <td class="py-3 px-3 font-weight-bold text-dark">
                                {{ $format ? \Carbon\Carbon::parse($rev->{$field}.($field === 'month' ? '-01' : ''))->format($format) : $rev->{$field} }}
                            </td>
                            <td class="py-3 text-right font-weight-bold">
                                <span class="badge badge-light border px-2 py-1">{{ number_format($rev->order_count) }} hợp đồng</span>
                            </td>
                            <td class="py-3 text-right font-weight-bold text-dark">
                                {{ number_format($rev->gross_revenue, 0, ',', '.') }} đ
                            </td>
                            <td class="py-3 text-right font-weight-bold text-danger">
                                -{{ number_format($rev->platform_fee, 0, ',', '.') }} đ
                            </td>
                            <td class="py-3 text-right px-3 font-weight-bold text-success" style="font-size: 14px;">
                                +{{ number_format($rev->partner_net, 0, ',', '.') }} đ
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu doanh thu phát sinh trong kỳ này.</td>
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
