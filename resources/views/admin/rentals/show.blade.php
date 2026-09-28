@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-key text-danger mr-2"></i> CHI TIẾT ĐƠN THUÊ XE #{{ $rental->rental_code }}
            </h3>
            <p class="text-muted small mb-0">Giám sát tiến trình bàn giao của Showroom Đối tác và xử lý chuyển tiền hoàn cọc ký quỹ Escrow cho khách hàng</p>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('rentals.voucher', $rental->id) }}" target="_blank" class="btn btn-warning font-weight-bold shadow-sm mr-2 text-dark">
                <i class="fa fa-file-text-o mr-1"></i> Xem Phiếu Đơn Thuê Xe & QR
            </a>
            <a href="{{ route('admin.rentals.index') }}" class="btn btn-outline-secondary font-weight-bold">
                <i class="fa fa-arrow-left mr-1"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    @php
        $isDriver = ($rental->rental_type === 'with_driver');
        $isWaitingRefund = ($rental->refund_status === 'waiting_admin' || ($rental->rental_status === 'returned' && $rental->refund_status !== 'refunded'));
        $showroomName = $rental->partner?->company_name ?: ($rental->partner?->name ?: ($rental->product?->partner?->company_name ?? ($rental->product?->partner?->name ?? 'Showroom AutoCar')));
    @endphp

    @if($isWaitingRefund)
        <div class="alert alert-warning border-danger shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <div class="mr-3 text-danger" style="font-size: 32px;"><i class="fa fa-bell"></i></div>
                <div>
                    <h5 class="font-weight-bold text-danger mb-1">SHOWROOM ĐỐI TÁC ĐÃ NGHIỆM THU XE - CẦN ADMIN THANH TOÁN HOÀN CỌC!</h5>
                    <p class="mb-0 text-dark small">
                        Showroom <strong>{{ $showroomName }}</strong> đã kiểm tra xe, chốt số ODO và gửi đề xuất hoàn trả <strong>{{ number_format($rental->refund_amount ?: $rental->deposit_amount) }} VNĐ</strong> cho khách hàng. Vui lòng kiểm tra tài khoản nhận hoàn bên dưới và thực hiện thanh toán.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Cột trái: Thông tin đơn thuê, Xe & Đối tác -->
        <div class="col-lg-7 mb-4">
            <!-- Mẫu xe -->
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-car text-primary mr-1"></i> Thông tin mẫu xe</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ $rental->product ? $rental->product->image_url : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=200&auto=format&fit=crop&q=60' }}" 
                             class="rounded mr-3 border" style="width: 110px; height: 80px; object-fit: cover;" 
                             alt="{{ $rental->product->name ?? 'Xe' }}"
                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=200&auto=format&fit=crop&q=60';">
                        <div>
                            <h5 class="font-weight-bold text-dark mb-1">{{ $rental->product->name ?? 'Xe không tồn tại' }}</h5>
                            <span class="badge badge-info mr-1">{{ $rental->product->category->name ?? 'Dòng xe' }}</span>
                            @if($rental->selected_color)
                                <span class="badge badge-dark px-2 py-1"><i class="fa fa-paint-brush"></i> Màu: {{ $rental->selected_color }}</span>
                            @else
                                <span class="badge badge-secondary">{{ $rental->product->color ?? 'Màu tiêu chuẩn' }}</span>
                            @endif
                            <div class="mt-1 small text-muted">
                                Giá thuê: <strong class="text-danger">{{ number_format($rental->daily_price) }} đ/ngày</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Showroom Đối tác & Doanh thu -->
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-building-o text-success mr-1"></i> Showroom Đối tác trực tiếp quản lý xe</h5>
                    <span class="badge badge-success px-3 py-1 font-weight-bold">Đối tác Sàn AutoCar</span>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center p-3 bg-light rounded mb-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3" style="width: 48px; height: 48px; font-size: 20px;">
                            <i class="fa fa-building"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 16px;">{{ $showroomName }}</div>
                            <div class="text-muted small">Tài khoản đối tác: {{ $rental->partner?->email ?? 'Chưa liên kết tài khoản' }}</div>
                        </div>
                    </div>
                    <div class="row text-center small mb-3">
                        <div class="col-4 border-right">
                            <span class="text-muted font-weight-bold">Tổng tiền thuê:</span>
                            <div class="font-weight-bold text-dark mt-1" style="font-size: 15px;">{{ number_format($rental->total_rental_fee + $rental->total_driver_fee) }} đ</div>
                        </div>
                        <div class="col-4 border-right">
                            <span class="text-primary font-weight-bold">Hoa hồng Sàn (10%):</span>
                            <div class="font-weight-bold text-primary mt-1" style="font-size: 15px;">+{{ number_format($rental->partner_commission_fee ?: round(($rental->total_rental_fee + $rental->total_driver_fee) * 0.10)) }} đ</div>
                        </div>
                        <div class="col-4">
                            <span class="text-success font-weight-bold">Showroom nhận (90%):</span>
                            <div class="font-weight-bold text-success mt-1" style="font-size: 15px;">{{ number_format($rental->partner_payout ?: round(($rental->total_rental_fee + $rental->total_driver_fee) * 0.90)) }} đ</div>
                        </div>
                    </div>

                    @if($rental->partner_id)
                        <div class="alert alert-{{ $rental->partner_commission_status === 'paid' ? 'success' : 'warning' }} small mb-0 py-2">
                            <i class="fa fa-{{ $rental->partner_commission_status === 'paid' ? 'check-circle' : 'clock-o' }} mr-1"></i>
                            <strong>Tình trạng nộp 10% hoa hồng sàn:</strong>
                            @if($rental->partner_commission_status === 'paid')
                                <span class="text-success font-weight-bold">Đã nộp {{ number_format($rental->partner_commission_fee) }} VNĐ (Mã GD ngân hàng: <code>{{ $rental->partner_commission_proof }}</code>).</span>
                            @else
                                <span class="text-danger font-weight-bold">Đối tác chưa nộp 10% hoa hồng!</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- TIẾN TRÌNH VẬN HÀNH THỰC TẾ CỦA SHOWROOM ĐỐI TÁC -->
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-tasks text-info mr-1"></i> Tiến trình bàn giao & Nghiệm thu xe của Showroom
                    </h5>
                    <span class="badge badge-info font-weight-bold">Theo dõi thời gian thực</span>
                </div>
                <div class="card-body">
                    <!-- Giai đoạn 1: Bàn giao xe -->
                    <div class="p-3 mb-3 rounded border {{ $rental->handover_status === 'verified' || in_array($rental->rental_status, ['in_progress', 'returned']) ? 'bg-light border-success' : 'bg-light border-warning' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold mb-0 {{ $rental->handover_status === 'verified' || in_array($rental->rental_status, ['in_progress', 'returned']) ? 'text-success' : 'text-warning' }}">
                                <i class="fa fa-key mr-1"></i> 1. BÀN GIAO XE BẰNG MÃ BẢO MẬT (OTP)
                            </h6>
                            @if($rental->handover_status === 'verified' || in_array($rental->rental_status, ['in_progress', 'returned']))
                                <span class="badge badge-success"><i class="fa fa-check"></i> ĐÃ BÀN GIAO XE</span>
                            @else
                                <span class="badge badge-warning text-dark"><i class="fa fa-clock-o"></i> Chờ khách đến nhận xe</span>
                            @endif
                        </div>
                        <div class="row small">
                            <div class="col-sm-6 mb-1">
                                <span class="text-muted">Mã OTP bảo mật của khách:</span>
                                @if($rental->handover_code)
                                    <strong class="badge badge-success ml-1 font-monospace" style="font-size: 14px; letter-spacing: 2px;">{{ $rental->handover_code }}</strong>
                                @else
                                    <span class="badge badge-secondary ml-1 font-weight-normal">Chưa cấp (Chờ nộp cọc)</span>
                                @endif
                            </div>
                            <div class="col-sm-6 mb-1">
                                <span class="text-muted">Thời điểm bàn giao:</span>
                                <strong>{{ $rental->handover_verified_at ? $rental->handover_verified_at->format('d/m/Y H:i') : 'Chưa giao' }}</strong>
                            </div>
                            <div class="col-sm-6 mb-1">
                                <span class="text-muted">Số ODO lúc giao:</span>
                                <strong>{{ $rental->handover_odo ? number_format($rental->handover_odo) . ' km' : 'Chưa ghi nhận' }}</strong>
                            </div>
                            <div class="col-sm-6 mb-1">
                                <span class="text-muted">Mức nhiên liệu:</span>
                                <strong>{{ $rental->handover_fuel !== null ? $rental->handover_fuel . '%' : '100%' }}</strong>
                            </div>
                            @if($rental->handover_notes)
                                <div class="col-12 mt-1">
                                    <span class="text-muted">Ghi chú giao xe:</span>
                                    <span class="font-italic text-dark">{{ $rental->handover_notes }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Giai đoạn 2: Khách trả xe & Nghiệm thu -->
                    <div class="p-3 rounded border {{ $rental->return_verified_at || $rental->rental_status === 'returned' || $rental->refund_status === 'waiting_admin' ? 'bg-light border-success' : 'bg-light' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold mb-0 {{ $rental->return_verified_at || $rental->rental_status === 'returned' || $rental->refund_status === 'waiting_admin' ? 'text-success' : 'text-muted' }}">
                                <i class="fa fa-clipboard mr-1"></i> 2. NGHIỆM THU TRẢ XE & ĐỀ XUẤT HOÀN CỌC
                            </h6>
                            @if($rental->return_verified_at || $rental->rental_status === 'returned' || $rental->refund_status === 'waiting_admin')
                                <span class="badge badge-success"><i class="fa fa-check"></i> ĐỐI TÁC ĐÃ NGHIỆM THU XE</span>
                            @else
                                <span class="badge badge-secondary"><i class="fa fa-hourglass-start"></i> Xe đang lưu thông / Chưa trả</span>
                            @endif
                        </div>
                        <div class="row small">
                            <div class="col-sm-6 mb-1">
                                <span class="text-muted">Thời điểm nhận lại xe:</span>
                                <strong>{{ $rental->return_verified_at ? $rental->return_verified_at->format('d/m/Y H:i') : 'Chưa nhận' }}</strong>
                            </div>
                            <div class="col-sm-6 mb-1">
                                <span class="text-muted">Số ODO lúc trả xe:</span>
                                <strong class="text-primary">{{ $rental->return_odo ? number_format($rental->return_odo) . ' km' : 'Chưa ghi nhận' }}</strong>
                            </div>
                            <div class="col-sm-6 mb-1">
                                <span class="text-muted">Mức xăng lúc trả:</span>
                                <strong>{{ $rental->return_fuel !== null ? $rental->return_fuel . '%' : 'Chưa ghi nhận' }}</strong>
                            </div>
                            <div class="col-sm-6 mb-1">
                                <span class="text-muted">Đề xuất hoàn cọc:</span>
                                <strong class="text-danger font-weight-bold">{{ number_format($rental->refund_amount ?: $rental->deposit_amount) }} đ</strong>
                            </div>
                            @if($rental->refund_notes)
                                <div class="col-12 mt-1">
                                    <span class="text-muted">Biên bản nghiệm thu ngoại thất của đối tác:</span>
                                    <div class="p-2 bg-white rounded border font-italic text-dark mt-1">{{ $rental->refund_notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Quản lý Ký quỹ & Hoàn tiền cọc (Escrow Refund) - CHỨC NĂNG DÀNH CHO ADMIN -->
            <div class="card border-0 shadow-sm rounded-lg mb-4" id="refund-box">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-money text-warning mr-1"></i> Quản lý Tiền cọc Ký quỹ & Thanh toán Hoàn cọc (Dành cho Admin)</h5>
                    @if($rental->refund_status === 'refunded')
                        <span class="badge badge-success px-3 py-1 font-weight-bold"><i class="fa fa-check-circle"></i> ĐÃ HOÀN CỌC</span>
                    @elseif($rental->refund_status === 'waiting_admin')
                        <span class="badge badge-warning px-3 py-1 font-weight-bold text-dark"><i class="fa fa-clock-o text-danger"></i> CHỜ ADMIN CHUYỂN TIỀN</span>
                    @elseif($rental->refund_status === 'holding')
                        <span class="badge badge-secondary px-3 py-1 font-weight-bold"><i class="fa fa-shield"></i> ĐANG GIỮ KÝ QUỸ</span>
                    @else
                        <span class="badge badge-secondary px-3 py-1 font-weight-bold">CHƯA HOÀN</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($rental->refund_status === 'refunded')
                        <div class="alert alert-success p-3 rounded mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-check-circle fa-2x text-success mr-3"></i>
                                <div>
                                    <strong class="text-success">SÀN AUTOCAR ĐÃ HOÀN CỌC THÀNH CÔNG CHO KHÁCH HÀNG!</strong>
                                    <div class="small text-dark mt-1">
                                        Số tiền đã hoàn: <strong class="text-success">{{ number_format($rental->refund_amount) }} đ</strong> vào tài khoản <strong>{{ $rental->refund_account_number }}</strong> ({{ $rental->refund_bank_name }} - {{ $rental->refund_account_holder }}).
                                    </div>
                                    <div class="small text-muted">Thời gian hoàn tiền: {{ $rental->refunded_at ? $rental->refunded_at->format('d/m/Y H:i:s') : '' }}</div>
                                </div>
                            </div>
                        </div>
                    @elseif(!$rental->return_verified_at && $rental->rental_status !== 'returned' && $rental->refund_status !== 'waiting_admin')
                        <div class="alert alert-info small mb-3">
                            <i class="fa fa-info-circle mr-1"></i> <strong>Showroom Đối tác đang trong quá trình bàn giao hoặc phục vụ khách.</strong><br>
                            Admin chỉ tiến hành chuyển khoản hoàn cọc ký quỹ Escrow cho khách hàng sau khi Showroom Đối tác nhận lại xe, kiểm tra ODO/xăng và gửi biên bản đề xuất hoàn cọc lên sàn.
                        </div>
                    @endif

                    <!-- THÔNG TIN TÀI KHOẢN NGÂN HÀNG NHẬN HOÀN CỦA KHÁCH -->
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="font-weight-bold text-dark mb-2"><i class="fa fa-university text-primary mr-1"></i> Tài khoản ngân hàng nhận hoàn cọc của khách hàng:</div>
                        <div class="row small">
                            <div class="col-sm-4 text-muted font-weight-bold">Ngân hàng:</div>
                            <div class="col-sm-8 font-weight-bold text-dark">{{ $rental->refund_bank_name ?: 'Khách chưa điền' }}</div>

                            <div class="col-sm-4 text-muted font-weight-bold mt-1">Số tài khoản:</div>
                            <div class="col-sm-8 font-weight-bold text-primary mt-1" style="font-size: 15px;">
                                {{ $rental->refund_account_number ?: 'Chưa có' }}
                                @if($rental->refund_account_number)
                                    <button type="button" class="btn btn-xs btn-outline-secondary ml-2" onclick="navigator.clipboard.writeText('{{ $rental->refund_account_number }}'); alert('Đã sao chép STK!');">
                                        <i class="fa fa-copy"></i> Sao chép
                                    </button>
                                @endif
                            </div>

                            <div class="col-sm-4 text-muted font-weight-bold mt-1">Chủ tài khoản:</div>
                            <div class="col-sm-8 font-weight-bold text-dark mt-1">{{ $rental->refund_account_holder ?: ($rental->customer_name) }}</div>

                            <div class="col-sm-4 text-muted font-weight-bold mt-1">Tiền cọc gốc Sàn giữ:</div>
                            <div class="col-sm-8 font-weight-bold text-danger mt-1">{{ number_format($rental->deposit_amount) }} VNĐ</div>
                        </div>
                    </div>

                    <!-- FORM ADMIN DUYỆT & XÁC NHẬN CHUYỂN TIỀN HOÀN CỌC -->
                    <form action="{{ route('admin.rentals.refund', $rental->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold text-success">Số tiền Admin chuyển hoàn cọc cho khách (VNĐ):</label>
                                <input type="number" name="refund_amount" class="form-control font-weight-bold text-success" 
                                    value="{{ $rental->refund_amount > 0 ? $rental->refund_amount : $rental->deposit_amount }}" required>
                                <small class="text-muted">Đề xuất của đối tác: {{ number_format($rental->refund_amount ?: $rental->deposit_amount) }} đ</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold text-warning">Giữ lại kiểm tra phạt nguội (VNĐ):</label>
                                <input type="number" name="refund_holding_fee" class="form-control font-weight-bold text-warning" 
                                    value="{{ $rental->refund_holding_fee ?: 0 }}">
                                <small class="text-muted">Khoản giữ đối chiếu camera giao thông</small>
                            </div>
                            <div class="col-12 form-group">
                                <label class="small font-weight-bold text-muted">Ghi chú đối soát hoàn cọc:</label>
                                <input type="text" name="refund_notes" class="form-control" 
                                    value="{{ $rental->refund_notes ?: 'Admin sàn đã chuyển khoản hoàn cọc cho khách hàng' }}" 
                                    placeholder="VD: Đã chuyển khoản hoàn cọc qua Internet Banking...">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block font-weight-bold py-2 shadow-sm" 
                            onclick="return confirm('Xác nhận bạn đã chuyển tiền hoàn cọc cho khách hàng vào tài khoản {{ $rental->refund_account_number }}?');">
                            <i class="fa fa-paper-plane mr-1"></i> XÁC NHẬN ĐÃ CHUYỂN KHOẢN HOÀN CỌC CHO KHÁCH
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cột phải: Hợp đồng chi tiết & Điều phối giám sát -->
        <div class="col-lg-5">
            <!-- Hợp đồng & Thông tin nhận xe -->
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-file-text-o text-info mr-1"></i> Hợp đồng & Lịch trình</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless small mb-0">
                        <tr>
                            <td class="text-muted font-weight-bold" style="width: 35%;">Hình thức:</td>
                            <td>
                                @if($rental->rental_type === 'with_driver')
                                    <span class="badge badge-primary px-2 py-1 font-weight-bold"><i class="fa fa-user-circle"></i> Có tài xế</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1 font-weight-bold"><i class="fa fa-key"></i> Tự lái</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">Khách hàng:</td>
                            <td><strong class="text-dark">{{ $rental->customer_name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">Số điện thoại:</td>
                            <td><a href="tel:{{ $rental->customer_phone }}" class="text-success font-weight-bold"><i class="fa fa-phone"></i> {{ $rental->customer_phone }}</a></td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">Thời gian thuê:</td>
                            <td>
                                Từ <strong>{{ date('d/m/Y', strtotime($rental->start_date)) }}</strong> 
                                đến <strong>{{ date('d/m/Y', strtotime($rental->end_date)) }}</strong>
                                (<strong>{{ $rental->total_days }} ngày</strong>)
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">Địa chỉ giao xe:</td>
                            <td>{{ $rental->customer_address }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted font-weight-bold">Tiền thuê xe:</td>
                            <td>{{ number_format($rental->total_rental_fee) }} VNĐ</td>
                        </tr>
                        @if($rental->total_driver_fee > 0)
                        <tr>
                            <td class="text-muted font-weight-bold">Phí dịch vụ tài xế:</td>
                            <td>{{ number_format($rental->total_driver_fee) }} VNĐ</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted font-weight-bold">Tiền cọc giữ xe:</td>
                            <td><strong class="text-danger">{{ number_format($rental->deposit_amount) }} VNĐ</strong></td>
                        </tr>
                        <tr class="border-top">
                            <td class="font-weight-bold text-dark" style="font-size: 15px;">Tổng chi phí:</td>
                            <td><strong class="text-danger font-weight-bold" style="font-size: 17px;">{{ number_format($rental->total_amount) }} VNĐ</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Card Điều phối & Xử lý trạng thái -->
            <div class="card border-0 shadow-sm rounded-lg sticky-top" style="top: 80px;">
                <div class="card-header bg-white py-3">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-cogs text-warning mr-1"></i> Điều phối & Quản trị đơn</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border small text-muted mb-3">
                        <i class="fa fa-info-circle text-primary mr-1"></i> <strong>Lưu ý:</strong> Tiến trình giao xe và nghiệm thu trả xe thuộc thẩm quyền cập nhật của Showroom Đối tác trực tiếp giữ xe. Admin theo dõi tiến trình và thực hiện hoàn cọc ký quỹ.
                    </div>

                    @if($rental->payment_status === 'unpaid')
                        <div class="card border-warning mb-3 shadow-sm" style="background-color: #fff9e6;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fa fa-exclamation-triangle text-warning fa-2x mr-2"></i>
                                    <div>
                                        <h6 class="font-weight-bold text-dark mb-0">Khách chưa được cấp mã nhận xe (Chưa cọc)</h6>
                                        <small class="text-muted">Tiền cọc yêu cầu: <strong class="text-danger">{{ number_format($rental->deposit_amount) }} đ</strong></small>
                                    </div>
                                </div>
                                <p class="small text-muted mb-2">
                                    Nếu khách hàng thanh toán trễ qua MoMo hoặc chuyển khoản ngân hàng ngoài giờ, Admin có quyền bấm xác nhận để hệ thống cấp ngay mã OTP đối chiếu 6 số cho khách tránh mất tiền.
                                </p>
                                <form action="{{ route('admin.rentals.confirmDeposit', $rental->id) }}" method="POST"
                                    onsubmit="return confirm('Xác nhận bạn đã nhận đủ tiền cọc {{ number_format($rental->deposit_amount) }}đ từ khách hàng? Hệ thống sẽ tạo mã OTP đối chiếu nhận xe cho khách ngay lập tức.');">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm btn-block font-weight-bold shadow-sm py-2">
                                        <i class="fa fa-check-circle mr-1"></i> XÁC NHẬN ĐÃ NHẬN CỌC (CẤP MÃ OTP CHO KHÁCH)
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('admin.rentals.updateStatus', $rental->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <!-- Gán tài xế nếu là loại thuê có tài xế -->
                        @if($rental->rental_type === 'with_driver')
                            <div class="p-3 bg-light rounded border mb-3">
                                <h6 class="font-weight-bold text-primary mb-2"><i class="fa fa-id-card-o mr-1"></i> Thông tin tài xế phục vụ:</h6>
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold small text-muted">Họ tên tài xế:</label>
                                    <input type="text" name="driver_name" value="{{ $rental->driver_name }}" class="form-control form-control-sm" placeholder="VD: Nguyễn Văn Tài">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small text-muted">Số điện thoại tài xế:</label>
                                    <input type="tel" name="driver_phone" value="{{ $rental->driver_phone }}" class="form-control form-control-sm" placeholder="VD: 0988xxxxxx">
                                </div>
                            </div>
                        @endif

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-muted">Trạng thái hợp đồng thuê xe:</label>
                            <select name="rental_status" class="form-control font-weight-bold" style="height: 42px;">
                                <option value="pending" {{ $rental->rental_status === 'pending' ? 'selected' : '' }}>⏳ Chờ đối tác giao xe</option>
                                <option value="confirmed" {{ $rental->rental_status === 'confirmed' ? 'selected' : '' }}>✓ Đã duyệt (Sẵn sàng xe)</option>
                                <option value="in_progress" {{ $rental->rental_status === 'in_progress' ? 'selected' : '' }}>🚗 Đang phục vụ / Khách đang đi</option>
                                <option value="returned" {{ $rental->rental_status === 'returned' ? 'selected' : '' }}>🏆 Đã trả xe về showroom</option>
                                <option value="cancelled" {{ $rental->rental_status === 'cancelled' ? 'selected' : '' }}>✗ Đã hủy đơn thuê</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-muted">Trạng thái thanh toán phí thuê:</label>
                            <select name="payment_status" class="form-control font-weight-bold">
                                <option value="unpaid" {{ $rental->payment_status === 'unpaid' ? 'selected' : '' }}>Chưa thanh toán cọc (Không cấp mã OTP)</option>
                                <option value="deposit_paid" {{ $rental->payment_status === 'deposit_paid' ? 'selected' : '' }}>✓ Đã nộp cọc ký quỹ Escrow (Tự sinh mã OTP)</option>
                                <option value="fully_paid" {{ $rental->payment_status === 'fully_paid' ? 'selected' : '' }}>🏆 Đã thanh toán đầy đủ 100% (Tự sinh mã OTP)</option>
                            </select>
                            <small class="text-muted d-block mt-1"><i class="fa fa-info-circle mr-1"></i> Admin có toàn quyền điều chỉnh trạng thái cọc để tránh khách thanh toán trễ bị mất quyền nhận xe.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-muted">Ghi chú quản trị:</label>
                            <textarea name="admin_note" class="form-control form-control-sm" rows="2" placeholder="Ghi chú điều phối...">{{ $rental->admin_note }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-outline-danger btn-block font-weight-bold py-2">
                            <i class="fa fa-save mr-1"></i> Lưu cập nhật
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
