@extends('partner.layout')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-tachometer text-success mr-2"></i> TỔNG QUAN HOẠT ĐỘNG ĐỐI TÁC
            </h3>
            <p class="text-muted small mb-0">
                Xin chào <strong>{{ $partnerUser->name }}</strong>! Dưới đây là dữ liệu kinh doanh, lịch hẹn xem xe và đơn
                thuê thực tế.
            </p>
        </div>
        <div>
            <a href="{{ route('partner.cars') }}" class="btn btn-success font-weight-bold shadow-sm"
                style="border-radius: 8px;">
                <i class="fa fa-car mr-1"></i> Quản lý đội xe
            </a>
        </div>
    </div>

    <!-- 4 THẺ THỐNG KÊ (STAT CARDS) -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #059669 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Xe đang niêm yết</div>
                        <div class="h3 font-weight-bold text-dark mb-0 mt-1">{{ $totalCars }}</div>
                        <small class="text-success"><i class="fa fa-check-circle"></i> Đang hoạt động trên sàn</small>
                    </div>
                    <div class="rounded-circle p-3 text-white"
                        style="background-color: #ecfdf5; color: #059669 !important;">
                        <i class="fa fa-car fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #3b82f6 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Lịch hẹn khách xem xe</div>
                        <div class="h3 font-weight-bold text-primary mb-0 mt-1">{{ $pendingAppointments }}</div>
                        <small class="text-primary"><a href="{{ route('partner.appointments') }}">Đang chờ tiếp đón
                                &rarr;</a></small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #eff6ff; color: #3b82f6;">
                        <i class="fa fa-calendar-check-o fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #8b5cf6 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Xe đã chốt bán qua Sàn</div>
                        <div class="h3 font-weight-bold text-purple mb-0 mt-1" style="color: #7c3aed;">{{ $wonDealsCount }}
                            <small style="font-size: 14px;">xe</small></div>
                        <small class="text-muted">Hoa hồng giới thiệu: <strong>{{ number_format($totalSalesCommission) }}
                                đ</strong></small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #f5f3ff; color: #8b5cf6;">
                        <i class="fa fa-trophy fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left" style="border-left: 5px solid #10b981 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small font-weight-bold text-uppercase">Doanh thu thuê nhận (90%)</div>
                        <div class="h4 font-weight-bold text-success mb-0 mt-1">{{ number_format($partnerPayoutTotal) }} đ
                        </div>
                        <small class="text-muted">Phí sàn giới thiệu (10%): {{ number_format($platformFeeTotal) }} đ</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #ecfdf5; color: #10b981;">
                        <i class="fa fa-money fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GIỚI THIỆU CHÍNH SÁCH HỢP TÁC BÊN THỨ BA (CAR BROKER) -->
    <div class="alert alert-light border bg-white p-3 mb-4 rounded-lg shadow-sm d-flex align-items-center">
        <div class="mr-3 text-success font-weight-bold" style="font-size: 32px;">
            <i class="fa fa-handshake-o"></i>
        </div>
        <div>
            <div class="font-weight-bold text-dark" style="font-size: 15px;">
                Mô hình Hợp tác: Sàn Giới thiệu Khách hàng & Bảo lãnh Giao dịch Ô tô
            </div>
            <div class="text-muted small">
                Nền tảng đóng vai trò <strong>Bên Giới Thiệu (Car Broker & Concierge)</strong>: Tiếp thị trực tuyến, tìm
                kiếm và điều phối khách hàng chất lượng đến mua xe và thuê xe tại Showroom của bạn. Showroom chịu trách
                nhiệm xe thực tế & ký hợp đồng, thanh toán hoa hồng môi giới khi giao dịch thành công.
            </div>
        </div>
    </div>

    <div class="row">
        <!-- CỘT TRÁI: LỊCH HẸN MỚI NHẤT -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-calendar text-primary mr-1"></i> Lịch hẹn
                        xem xe mới</h5>
                    <a href="{{ route('partner.appointments') }}" class="small font-weight-bold text-primary">Xem tất cả
                        &rarr;</a>
                </div>
                <div class="card-body p-0">
                    @if($recentAppointments->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa fa-calendar-o fa-2x mb-2"></i>
                            <p class="mb-0">Chưa có lịch hẹn xem xe nào</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Khách hàng</th>
                                        <th>Mẫu xe</th>
                                        <th>Thời gian hẹn</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAppointments as $app)
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $app->customer_name }}</div>
                                                <div class="text-muted">{{ $app->customer_phone }}</div>
                                            </td>
                                            <td>
                                                <div class="font-weight-bold text-primary">{{ $app->product->name ?? 'Xe' }}</div>
                                                <div class="text-muted">{{ $app->selected_color }}</div>
                                            </td>
                                            <td>
                                                <div>{{ date('d/m/Y', strtotime($app->appointment_date)) }}</div>
                                                <div class="text-muted">{{ $app->appointment_time }}</div>
                                            </td>
                                            <td>
                                                @if($app->status === 'pending')
                                                    <span class="badge badge-warning">Chờ tiếp nhận</span>
                                                @elseif($app->status === 'confirmed')
                                                    <span class="badge badge-primary">Đã xác nhận</span>
                                                @elseif($app->status === 'completed')
                                                    <span class="badge badge-success">Đã hoàn thành</span>
                                                @else
                                                    <span class="badge badge-secondary">Đã hủy</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: ĐƠN THUÊ XE MỚI NHẤT -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-key text-warning mr-1"></i> Đơn thuê xe & Ký
                        quỹ cọc</h5>
                    <a href="{{ route('partner.rentals') }}" class="small font-weight-bold text-primary">Xem tất cả
                        &rarr;</a>
                </div>
                <div class="card-body p-0">
                    @if($recentRentals->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa fa-key fa-2x mb-2"></i>
                            <p class="mb-0">Chưa có đơn thuê xe nào</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách / Xe</th>
                                        <th>Cọc ký quỹ</th>
                                        <th>Tiền nhận (90%)</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRentals as $rental)
                                        <tr>
                                            <td>
                                                <strong class="text-dark">#{{ $rental->rental_code }}</strong>
                                                <div class="text-muted">
                                                    {{ $rental->rental_type === 'with_driver' ? 'Kèm tài xế' : 'Tự lái' }}</div>
                                            </td>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $rental->customer_name }}</div>
                                                <div class="text-primary">{{ $rental->product->name ?? 'Xe' }}</div>
                                            </td>
                                            <td>
                                                <div class="text-danger font-weight-bold">
                                                    {{ number_format($rental->deposit_amount) }} đ</div>
                                                @if($rental->refund_status === 'refunded')
                                                    <span class="badge badge-success" style="font-size: 10px;">Đã hoàn cọc</span>
                                                @elseif($rental->refund_status === 'holding')
                                                    <span class="badge badge-warning" style="font-size: 10px;">Đang giữ cọc</span>
                                                @else
                                                    <span class="badge badge-secondary" style="font-size: 10px;">Chưa hoàn</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong
                                                    class="text-success font-weight-bold">{{ number_format($rental->partner_payout ?: round(($rental->total_rental_fee + $rental->total_driver_fee) * 0.85)) }}
                                                    đ</strong>
                                            </td>
                                            <td>
                                                @if($rental->rental_status === 'pending')
                                                    <span class="badge badge-warning">Chờ bàn giao</span>
                                                @elseif($rental->rental_status === 'in_progress')
                                                    <span class="badge badge-info">Đang đi</span>
                                                @elseif($rental->rental_status === 'returned')
                                                    <span class="badge badge-success">Đã trả xe</span>
                                                @else
                                                    <span class="badge badge-secondary">Đã hủy</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection