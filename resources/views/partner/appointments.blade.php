@extends('partner.layout')

@section('content')
<style>
    .appointment-card {
        transition: all 0.2s ease-in-out;
    }
    .badge-locked {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .copy-btn {
        transition: all 0.15s ease-in-out;
    }
    .copy-btn:hover {
        transform: scale(1.05);
    }
    .sepay-qr-box {
        background: #f8fafc;
        border: 2px dashed #0284c7;
        border-radius: 12px;
        padding: 12px;
    }
</style>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h3 class="font-weight-bold text-dark mb-1">
            <i class="fa fa-calendar-check-o text-success mr-2"></i> TIẾP NHẬN LỊCH HẸN XEM XE & LÁI THỬ
        </h3>
        <p class="text-muted small mb-0">Khách hàng đặt lịch xem xe qua Sàn. Đối tác phối hợp đón tiếp khách và nộp 1% hoa hồng Sàn khi bán xe thành công.</p>
    </div>
    <div class="mt-2 mt-md-0">
        <a href="{{ route('partner.cars') }}" class="btn btn-outline-success font-weight-bold btn-sm shadow-sm mr-1">
            <i class="fa fa-car mr-1"></i> Danh sách xe Showroom
        </a>
        <a href="{{ route('partner.dashboard') }}" class="btn btn-secondary font-weight-bold btn-sm shadow-sm">
            <i class="fa fa-dashboard mr-1"></i> Dashboard
        </a>
    </div>
</div>

<!-- THÔNG BÁO QUY ĐỊNH KHÓA TRẠNG THÁI & NỘP 1% HOA HỒNG SÀN -->
<div class="alert alert-info border-info shadow-sm mb-4">
    <div class="d-flex align-items-center">
        <i class="fa fa-shield fa-2x text-info mr-3"></i>
        <div class="small">
            <strong class="text-dark" style="font-size: 13px;">QUY ĐỊNH QUẢN TRỊ TRẠNG THÁI & THANH TOÁN HOA HỒNG SÀN:</strong>
            <ul class="mb-0 pl-3 mt-1">
                <li><strong>Khách mua xe thành công:</strong> Nhà xe phải thanh toán đúng <strong>1% giá trị xe</strong> cho Ban Quản Trị Sàn qua cổng <strong>VietQR SePay</strong> hoặc <strong>Ví MoMo</strong>. Sau khi hoàn tất nộp hoa hồng, hồ sơ giao dịch sẽ được <strong>KHÓA VĨNH VIỄN</strong> (không thể chỉnh sửa hay hủy).</li>
                <li><strong>Khách không mua / Hủy lịch hẹn:</strong> Khi chuyển sang trạng thái <strong>Hủy mua</strong>, hệ thống sẽ <strong>KHÓA VĨNH VIỄN</strong> lịch hẹn đó để đảm bảo tính minh bạch, không cho phép phục hồi hoặc chuyển đổi trạng thái nữa.</li>
            </ul>
        </div>
    </div>
</div>

<!-- TABS PHÂN LOẠI TRẠNG THÁI -->
<ul class="nav nav-pills mb-3 flex-wrap">
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ (!$dealStatus && (!$status || $status === 'all')) ? 'active bg-dark text-white' : 'bg-white text-dark border' }}" 
           href="{{ route('partner.appointments') }}">
            Tất cả lịch hẹn ({{ $totalAppointments }})
        </a>
    </li>
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ ($status === 'pending' && !$dealStatus) ? 'active bg-warning text-dark' : 'bg-white text-warning border' }}" 
           href="{{ route('partner.appointments', ['status' => 'pending']) }}">
            <i class="fa fa-clock-o mr-1"></i> Chờ tiếp nhận ({{ $countPending }})
        </a>
    </li>
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ ($status === 'confirmed' && !$dealStatus) ? 'active bg-primary text-white' : 'bg-white text-primary border' }}" 
           href="{{ route('partner.appointments', ['status' => 'confirmed']) }}">
            <i class="fa fa-handshake-o mr-1"></i> Đã xác nhận đón khách ({{ $countConfirmed }})
        </a>
    </li>
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ $dealStatus === 'deal_won' ? 'active bg-success text-white' : 'bg-white text-success border' }}" 
           href="{{ route('partner.appointments', ['deal_status' => 'deal_won']) }}">
            <i class="fa fa-trophy mr-1"></i> 🏆 Khách đã mua xe (Nộp 1%) ({{ $countDealWon }})
        </a>
    </li>
    <li class="nav-item mr-2 mb-2">
        <a class="nav-link font-weight-bold {{ $dealStatus === 'deal_lost' ? 'active bg-danger text-white' : 'bg-white text-danger border' }}" 
           href="{{ route('partner.appointments', ['deal_status' => 'deal_lost']) }}">
            <i class="fa fa-times-circle mr-1"></i> ❌ Đã hủy / Không mua (Đã khóa) ({{ $countLostOrCancelled }})
        </a>
    </li>
</ul>

<!-- BỘ LỌC TÌM KIẾM CHI TIẾT -->
<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('partner.appointments') }}" method="GET" class="row align-items-center">
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                    </div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control font-weight-bold"
                           placeholder="Tìm mã hẹn #HEN..., tên khách, SĐT, tên xe...">
                </div>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select name="status" class="form-control form-control-sm">
                    <option value="">-- Tất cả tiến độ tiếp đón --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ tiếp nhận</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận tiếp đón</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã xem & lái thử xong</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select name="deal_status" class="form-control form-control-sm">
                    <option value="">-- Tất cả kết quả chốt xe --</option>
                    <option value="negotiating" {{ request('deal_status') === 'negotiating' ? 'selected' : '' }}>Đang tư vấn đàm phán</option>
                    <option value="deal_won" {{ request('deal_status') === 'deal_won' ? 'selected' : '' }}>🏆 Khách đã mua xe thành công</option>
                    <option value="deal_lost" {{ request('deal_status') === 'deal_lost' ? 'selected' : '' }}>❌ Khách không mua / Hủy ý định</option>
                </select>
            </div>
            <div class="col-md-2 d-flex">
                <button type="submit" class="btn btn-sm btn-primary font-weight-bold flex-grow-1 mr-1">
                    <i class="fa fa-filter"></i> Lọc
                </button>
                @if(request()->anyFilled(['keyword', 'status', 'deal_status']))
                    <a href="{{ route('partner.appointments') }}" class="btn btn-sm btn-outline-secondary" title="Xóa bộ lọc">
                        <i class="fa fa-refresh"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- DANH SÁCH LỊCH HẸN -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light small font-weight-bold text-muted text-uppercase">
                    <tr>
                        <th style="width: 140px;">Mã lịch hẹn</th>
                        <th style="width: 180px;">Thông tin khách</th>
                        <th style="width: 200px;">Xe lái thử & Giá</th>
                        <th style="width: 180px;">Thời gian & Địa điểm</th>
                        <th style="width: 220px;">Trạng thái & Hoa hồng</th>
                        <th style="min-width: 320px;">Ghi chú & Thao tác / Thanh toán</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($appointments as $app)
                        @php
                            $isPaid = ($app->deal_status === 'deal_won' && $app->commission_status === 'paid');
                            $isCancelledOrLost = ($app->deal_status === 'deal_lost' || $app->status === 'cancelled');
                            $isLocked = ($isPaid || $isCancelledOrLost);
                            $dealPrice = (float)($app->deal_price ?: ($app->product?->price ?: 500000000));
                            $commissionAmount = (int)($app->commission_amount ?: round($dealPrice * 0.01));
                        @endphp
                        <tr class="{{ $isPaid ? 'bg-light' : ($isCancelledOrLost ? 'text-muted' : '') }}">
                            <!-- CỘT 1: MÃ LỊCH HẸN -->
                            <td>
                                <strong class="text-primary font-weight-bold" style="font-size: 13px;">#{{ $app->appointment_code }}</strong>
                                <div class="text-muted" style="font-size: 11px;">
                                    <i class="fa fa-clock-o"></i> {{ $app->created_at ? $app->created_at->format('d/m/Y H:i') : '---' }}
                                </div>
                                @if($isLocked)
                                    <div class="mt-2">
                                        <span class="badge badge-locked px-2 py-1 font-weight-bold">
                                            <i class="fa fa-lock text-danger"></i> ĐÃ KHÓA
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- CỘT 2: THÔNG TIN KHÁCH -->
                            <td>
                                <div class="font-weight-bold text-dark" style="font-size: 14px;">
                                    {{ $app->customer_name }}
                                </div>
                                <div>
                                    <a href="tel:{{ $app->customer_phone }}" class="text-success font-weight-bold">
                                        <i class="fa fa-phone"></i> {{ $app->customer_phone }}
                                    </a>
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    <i class="fa fa-envelope-o"></i> {{ $app->customer_email ?: 'Chưa cung cấp email' }}
                                </div>
                                @if($app->note)
                                    <div class="mt-1 p-1 bg-light border rounded font-italic text-muted" style="font-size: 10px;">
                                        <strong>Yêu cầu:</strong> {{ $app->note }}
                                    </div>
                                @endif
                            </td>

                            <!-- CỘT 3: XE LÁI THỬ & GIÁ -->
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($app->product && $app->product->image)
                                        <img src="{{ asset('images/' . $app->product->image) }}" 
                                             alt="{{ $app->product->name }}" 
                                             class="rounded mr-2 border shadow-sm" 
                                             style="width: 55px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded border mr-2 d-flex align-items-center justify-content-center text-muted" style="width: 55px; height: 40px;">
                                            <i class="fa fa-car"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $app->product->name ?? 'Xe xem thực tế' }}</div>
                                        <div class="text-danger font-weight-bold" style="font-size: 12px;">
                                            {{ number_format($app->product?->price ?: $dealPrice) }} đ
                                        </div>
                                        <span class="badge badge-dark px-2 py-0" style="font-size: 10px;">
                                            <i class="fa fa-paint-brush"></i> {{ $app->selected_color ?: 'Màu cơ bản' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- CỘT 4: THỜI GIAN & ĐỊA ĐIỂM -->
                            <td>
                                <div class="font-weight-bold text-danger">
                                    <i class="fa fa-calendar text-danger"></i> {{ date('d/m/Y', strtotime($app->appointment_date)) }}
                                </div>
                                <div class="font-weight-bold text-dark">
                                    <i class="fa fa-clock-o text-muted"></i> {{ $app->appointment_time }}
                                </div>
                                <div class="text-muted mt-1 small" style="max-width: 220px; line-height: 1.3;">
                                    <i class="fa fa-map-marker text-danger"></i> {{ $app->address }}
                                </div>
                            </td>

                            <!-- CỘT 5: TRẠNG THÁI & HOA HỒNG -->
                            <td>
                                <!-- Tiến độ tiếp đón -->
                                <div class="mb-1">
                                    <span class="text-muted small font-weight-bold">Tiếp đón:</span>
                                    @if($app->status === 'pending')
                                        <span class="badge badge-warning px-2 py-1 font-weight-bold">Chờ tiếp nhận</span>
                                    @elseif($app->status === 'confirmed')
                                        <span class="badge badge-primary px-2 py-1 font-weight-bold">Đã xác nhận đón</span>
                                    @elseif($app->status === 'completed')
                                        <span class="badge badge-info px-2 py-1 font-weight-bold">Đã xem xe xong</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1 font-weight-bold">Đã hủy</span>
                                    @endif
                                </div>

                                <!-- Kết quả mua bán xe -->
                                <div class="pt-1 border-top">
                                    <span class="text-muted small font-weight-bold d-block mb-1">Kết quả bán xe:</span>
                                    @if($app->deal_status === 'deal_won')
                                        <span class="badge badge-success px-2 py-1 font-weight-bold d-inline-block mb-1" style="font-size: 11px;">
                                            <i class="fa fa-trophy"></i> KHÁCH ĐÃ MUA XE
                                        </span>
                                        <div class="small text-dark font-weight-bold">
                                            Giá chốt: <span class="text-primary">{{ number_format($app->deal_price ?: $dealPrice) }} đ</span>
                                        </div>
                                        <div class="small font-weight-bold text-danger">
                                            Hoa hồng Sàn (1%): {{ number_format($commissionAmount) }} đ
                                        </div>

                                        <!-- Trạng thái thanh toán 1% hoa hồng -->
                                        @if($isPaid)
                                            <div class="mt-1">
                                                <span class="badge badge-success px-2 py-1 font-weight-bold">
                                                    <i class="fa fa-check-circle"></i> ĐÃ NỘP 1% HOA HỒNG
                                                </span>
                                            </div>
                                            <div class="small text-success font-weight-bold mt-1" style="font-size: 11px;">
                                                <i class="fa fa-lock"></i> ĐÃ KHÓA TRẠNG THÁI
                                            </div>
                                            @if($app->commission_proof)
                                                <div class="text-muted" style="font-size: 10px;">
                                                    Mã GD: <code>{{ $app->commission_proof }}</code>
                                                </div>
                                            @endif
                                        @else
                                            <div class="mt-1">
                                                <span class="badge badge-danger px-2 py-1 font-weight-bold animate__animated animate__pulse animate__infinite">
                                                    <i class="fa fa-exclamation-triangle"></i> CHƯA NỘP HOA HỒNG (1%)
                                                </span>
                                            </div>
                                            <div class="small text-danger font-weight-bold mt-1" style="font-size: 10px;">
                                                Cần nộp hoa hồng để khóa hồ sơ
                                            </div>
                                        @endif

                                    @elseif($app->deal_status === 'deal_lost' || $app->status === 'cancelled')
                                        <span class="badge badge-secondary px-2 py-1 font-weight-bold">
                                            <i class="fa fa-ban"></i> KHÁCH KHÔNG MUA / ĐÃ HỦY
                                        </span>
                                        <div class="small text-danger font-weight-bold mt-1" style="font-size: 11px;">
                                            <i class="fa fa-lock"></i> ĐÃ KHÓA TRẠNG THÁI VĨNH VIỄN
                                        </div>
                                    @else
                                        <span class="badge badge-light border text-primary px-2 py-1 font-weight-bold">
                                            <i class="fa fa-comments-o"></i> Đang tư vấn đàm phán
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- CỘT 6: GHI CHÚ & THAO TÁC / THANH TOÁN HOA HỒNG -->
                            <td>
                                {{-- TRƯỜNG HỢP 1: ĐÃ NỘP 1% HOA HỒNG SÀN -> KHÓA CỨNG HOÀN TOÀN --}}
                                @if($isPaid)
                                    <div class="alert alert-success border-success mb-0 p-3 rounded shadow-sm">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fa fa-lock fa-lg text-success mr-2"></i>
                                            <strong class="text-success" style="font-size: 13px;">HỒ SƠ ĐÃ HOÀN TẤT & KHÓA TRẠNG THÁI</strong>
                                        </div>
                                        <p class="text-dark small mb-1">
                                            Showroom đã nộp đủ <strong>1% hoa hồng Sàn ({{ number_format($commissionAmount) }} đ)</strong> cho Admin. Lịch hẹn và kết quả bán xe này đã được chốt và <strong>khóa vĩnh viễn</strong>.
                                        </p>
                                        <div class="small text-muted border-top pt-1 mt-1">
                                            <div><i class="fa fa-check text-success"></i> Mã đối soát: <strong class="text-primary">{{ $app->commission_proof ?: 'Hệ thống tự động ghi nhận' }}</strong></div>
                                            @if($app->commission_paid_at)
                                                <div><i class="fa fa-clock-o text-muted"></i> Thời gian xác nhận: {{ $app->commission_paid_at->format('d/m/Y H:i:s') }}</div>
                                            @endif
                                            @if($app->admin_note)
                                                <div class="text-secondary mt-1"><em>Ghi chú: {{ $app->admin_note }}</em></div>
                                            @endif
                                        </div>
                                    </div>

                                {{-- TRƯỜNG HỢP 2: ĐÃ HỦY / KHÁCH KHÔNG MUA -> KHÓA CỨNG HOÀN TOÀN --}}
                                @elseif($isCancelledOrLost)
                                    <div class="alert alert-secondary border-secondary mb-0 p-3 rounded shadow-sm">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fa fa-ban fa-lg text-danger mr-2"></i>
                                            <strong class="text-danger" style="font-size: 13px;">ĐÃ HỦY MUA & KHÓA TRẠNG THÁI</strong>
                                        </div>
                                        <p class="text-muted small mb-1">
                                            Lịch hẹn này đã bị hủy hoặc khách hàng từ chối mua xe. Theo quy chế quản trị Sàn, trạng thái đã được <strong>khóa vĩnh viễn</strong>, không cho phép phục hồi hoặc chuyển đổi trạng thái nữa.
                                        </p>
                                        @if($app->admin_note)
                                            <div class="small text-secondary border-top pt-1 mt-1">
                                                <em>Ghi chú: {{ $app->admin_note }}</em>
                                            </div>
                                        @endif
                                    </div>

                                {{-- TRƯỜNG HỢP 3: CHỌN KHÁCH ĐÃ MUA XE NHƯNG CHƯA NỘP 1% HOA HỒNG -> BẢNG THANH TOÁN QR SEPAY & MOMO --}}
                                @elseif($app->deal_status === 'deal_won' && !$isPaid)
                                    <div class="card border-danger shadow-sm">
                                        <div class="card-header bg-danger text-white py-2 px-3 d-flex justify-content-between align-items-center">
                                            <strong style="font-size: 12px;"><i class="fa fa-credit-card mr-1"></i> THANH TOÁN 1% HOA HỒNG SÀN</strong>
                                            <span class="badge badge-light text-danger font-weight-bold" style="font-size: 13px;">
                                                {{ number_format($commissionAmount) }} đ
                                            </span>
                                        </div>
                                        <div class="card-body p-3 bg-white">
                                            <div class="small text-muted mb-2">
                                                Khách đã đồng ý mua xe! Nhà xe vui lòng chuyển khoản thanh toán đúng <strong>1% hoa hồng Sàn ({{ number_format($commissionAmount) }} VNĐ)</strong> qua một trong các cổng sau để hoàn tất và khóa hồ sơ:
                                            </div>

                                            <div class="row no-gutters mb-2">
                                                <!-- CỔNG 1: Quét mã VietQR SePay -->
                                                <div class="col-12 mb-2">
                                                    <button type="button" 
                                                            class="btn btn-primary btn-sm btn-block font-weight-bold text-left d-flex justify-content-between align-items-center shadow-sm py-2"
                                                            data-toggle="modal" 
                                                            data-target="#sepayModal{{ $app->id }}"
                                                            onclick="startSepayPolling('{{ $app->appointment_code }}', {{ $app->id }})">
                                                        <span><i class="fa fa-qrcode mr-2 fa-lg"></i> Quét mã VietQR SePay (TPBank)</span>
                                                        <span class="badge badge-light text-primary font-weight-bold">Tự động nhận diện <i class="fa fa-arrow-right ml-1"></i></span>
                                                    </button>
                                                </div>

                                                <!-- CỔNG 2: Ví MoMo Test -->
                                                <div class="col-sm-6 pr-sm-1 mb-2 mb-sm-0">
                                                    <a href="{{ route('partner.appointments.payCommissionMomo', ['appointment' => $app->id, 'type' => 'captureWallet']) }}"
                                                       class="btn btn-sm btn-block font-weight-bold text-white shadow-sm py-2"
                                                       style="background-color: #a50064; border-color: #a50064; font-size: 11px;">
                                                        <i class="fa fa-mobile fa-lg mr-1"></i> Ví MoMo Test (QR)
                                                    </a>
                                                </div>

                                                <!-- CỔNG 3: Thẻ ATM Napas qua MoMo -->
                                                <div class="col-sm-6 pl-sm-1">
                                                    <a href="{{ route('partner.appointments.payCommissionMomo', ['appointment' => $app->id, 'type' => 'payWithATM']) }}"
                                                       class="btn btn-sm btn-block btn-outline-secondary font-weight-bold shadow-sm py-2"
                                                       style="font-size: 11px;">
                                                        <i class="fa fa-credit-card mr-1"></i> Thẻ ATM Napas
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- NHẬP MÃ XÁC NHẬN CHUYỂN KHOẢN NGÂN HÀNG THỦ CÔNG -->
                                            <div class="border-top pt-2 mt-2">
                                                <a class="small font-weight-bold text-secondary cursor-pointer d-flex justify-content-between align-items-center" 
                                                   data-toggle="collapse" 
                                                   href="#manualConfirmBox{{ $app->id }}" 
                                                   role="button">
                                                    <span><i class="fa fa-university mr-1 text-primary"></i> Đã chuyển khoản ngoài? Nhập mã GD xác nhận</span>
                                                    <i class="fa fa-caret-down"></i>
                                                </a>
                                                <div class="collapse mt-2" id="manualConfirmBox{{ $app->id }}">
                                                    <form action="{{ route('partner.appointments.confirmCommission', $app->id) }}" method="POST" class="bg-light p-2 border rounded">
                                                        @csrf
                                                        <label class="small font-weight-bold text-dark mb-1" style="font-size: 11px;">
                                                            Mã giao dịch ngân hàng / Nội dung CK:
                                                        </label>
                                                        <div class="input-group input-group-sm mb-2">
                                                            <input type="text" name="payment_proof" class="form-control font-weight-bold" 
                                                                   placeholder="VD: FT2409... hoặc HH1 {{ $app->appointment_code }}" required>
                                                            <div class="input-group-append">
                                                                <button type="submit" class="btn btn-success font-weight-bold">
                                                                    <i class="fa fa-check"></i> Xác nhận nộp
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <small class="text-muted font-italic" style="font-size: 10px;">
                                                            Hệ thống sẽ đối chiếu và khóa vĩnh viễn trạng thái sau khi bạn xác nhận.
                                                        </small>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                {{-- TRƯỜNG HỢP 4: ĐANG TƯ VẤN / ĐÀM PHÁN BÌNH THƯỜNG -> FORM CHỌN TRẠNG THÁI --}}
                                @else
                                    <form action="{{ route('partner.appointments.updateStatus', $app->id) }}" 
                                          method="POST" 
                                          id="updateForm{{ $app->id }}" 
                                          onsubmit="return handleFormSubmit(event, {{ $app->id }})">
                                        @csrf
                                        @method('PATCH')
                                        
                                        <!-- Tiến độ tiếp đón -->
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold text-muted mb-0">Tiến độ tiếp đón:</label>
                                            <select name="status" id="statusSelect{{ $app->id }}" class="form-control form-control-sm font-weight-bold" onchange="handleSelectChange({{ $app->id }})">
                                                <option value="pending" {{ $app->status === 'pending' ? 'selected' : '' }}>Chờ tiếp nhận</option>
                                                <option value="confirmed" {{ $app->status === 'confirmed' ? 'selected' : '' }}>✓ Xác nhận tiếp đón</option>
                                                <option value="completed" {{ $app->status === 'completed' ? 'selected' : '' }}>Đã xem & lái thử xong</option>
                                                <option value="cancelled" {{ $app->status === 'cancelled' ? 'selected' : '' }}>✗ Khách hủy hẹn (Khóa vĩnh viễn)</option>
                                            </select>
                                        </div>

                                        <!-- Kết quả chốt bán xe -->
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold text-success mb-0">
                                                <i class="fa fa-handshake-o"></i> Chốt kết quả bán xe:
                                            </label>
                                            <select name="deal_status" 
                                                    id="dealSelect{{ $app->id }}"
                                                    class="form-control form-control-sm font-weight-bold text-success border-success"
                                                    onchange="handleSelectChange({{ $app->id }})">
                                                <option value="negotiating" {{ $app->deal_status === 'negotiating' ? 'selected' : '' }}>Đang tư vấn đàm phán</option>
                                                <option value="deal_won" {{ $app->deal_status === 'deal_won' ? 'selected' : '' }}>🏆 KHÁCH ĐÃ MUA XE (Nộp 1% hoa hồng)</option>
                                                <option value="deal_lost" {{ $app->deal_status === 'deal_lost' ? 'selected' : '' }}>❌ Khách không mua / Hủy mua (Khóa vĩnh viễn)</option>
                                            </select>
                                        </div>

                                        <!-- Ô nhập giá bán thực tế (Hiện khi chọn deal_won) -->
                                        <div class="form-group mb-2 deal-price-box" id="dealPriceBox{{ $app->id }}" style="display: none;">
                                            <div class="p-2 border border-success rounded bg-light">
                                                <label class="small font-weight-bold text-dark mb-0">Giá bán xe chốt thực tế (VNĐ):</label>
                                                <input type="number" 
                                                       name="deal_price" 
                                                       id="dealPriceInput{{ $app->id }}"
                                                       value="{{ $app->deal_price ?: ($app->product?->price ?: 500000000) }}"
                                                       class="form-control form-control-sm font-weight-bold text-danger"
                                                       placeholder="VD: 650000000"
                                                       oninput="recalcCommission({{ $app->id }})">
                                                <div class="small font-weight-bold text-danger mt-1" style="font-size: 11px;">
                                                    <i class="fa fa-money"></i> Hoa hồng sàn thu 1%: 
                                                    <span id="commissionPreview{{ $app->id }}">{{ number_format(round(($app->deal_price ?: ($app->product?->price ?: 500000000)) * 0.01)) }}</span> đ
                                                </div>
                                                <small class="text-muted" style="font-size: 10px;">
                                                    Bấm "Cập nhật" hệ thống sẽ mở cổng VietQR SePay & MoMo để nộp 1% hoa hồng. Nộp xong sẽ khóa trạng thái.
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Ghi chú Showroom -->
                                        <div class="form-group mb-2">
                                            <input type="text" name="admin_note" value="{{ $app->admin_note }}"
                                                   class="form-control form-control-sm"
                                                   placeholder="Ghi chú phản hồi của Showroom...">
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-success btn-block font-weight-bold shadow-sm" id="btnSubmit{{ $app->id }}">
                                            <i class="fa fa-save"></i> Cập nhật kết quả
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        {{-- MODAL THANH TOÁN VIETQR SEPAY CHO TỪNG LỊCH HẸN DEAL_WON CHƯA NỘP HOA HỒNG --}}
                        @if($app->deal_status === 'deal_won' && !$isPaid)
                            <div class="modal fade" id="sepayModal{{ $app->id }}" tabindex="-1" role="dialog" aria-labelledby="sepayModalLabel{{ $app->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-header text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                                            <h5 class="modal-title font-weight-bold" id="sepayModalLabel{{ $app->id }}">
                                                <i class="fa fa-qrcode mr-2"></i> THANH TOÁN 1% HOA HỒNG SÀN QUA VIETQR SEPAY
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="stopSepayPolling({{ $app->id }})">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="alert alert-info border-0 rounded-lg text-center py-2 mb-3" style="background-color: #f0f9ff; color: #0369a1;">
                                                <i class="fa fa-spinner fa-spin mr-1"></i> Hệ thống đang tự động nhận diện biến động số dư TPBank qua SePay. Sau khi chuyển tiền, trang sẽ <strong>tự động nhận diện và khóa hồ sơ</strong>.
                                            </div>

                                            <div class="row align-items-center">
                                                <!-- CỘT 1: ẢNH MÃ QR SEPAY -->
                                                <div class="col-md-5 text-center mb-3 mb-md-0 border-right">
                                                    <div class="sepay-qr-box d-inline-block shadow-sm">
                                                        <img src="https://qr.sepay.vn/img?acc=12325072005&bank=TPBank&amount={{ $commissionAmount }}&des={{ urlencode('HH1 ' . $app->appointment_code) }}&template=compact"
                                                             alt="Mã QR SePay 1% Hoa Hồng" 
                                                             class="img-fluid rounded" 
                                                             style="max-width: 230px;"
                                                             id="qrImg{{ $app->id }}">
                                                    </div>
                                                    <div class="mt-2 small text-muted font-weight-bold">
                                                        <i class="fa fa-camera text-primary mr-1"></i> Mở App Ngân hàng bất kỳ để quét mã VietQR
                                                    </div>
                                                    <div class="mt-2" id="pollBadge{{ $app->id }}">
                                                        <span class="badge badge-warning text-dark px-3 py-1 font-weight-bold">
                                                            <i class="fa fa-clock-o mr-1"></i> Đang chờ nhận biến động số dư...
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- CỘT 2: THÔNG TIN CHUYỂN KHOẢN CHI TIẾT -->
                                                <div class="col-md-7 pl-md-4">
                                                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                                                        <i class="fa fa-university text-primary mr-1"></i> Tài khoản nhận hoa hồng Sàn (TPBank):
                                                    </h6>
                                                    <ul class="list-group list-group-flush small mb-3">
                                                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                                            <span class="text-muted">Mã lịch hẹn bán xe:</span>
                                                            <strong class="text-dark">#{{ $app->appointment_code }}</strong>
                                                        </li>
                                                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                                            <span class="text-muted">Ngân hàng thụ hưởng:</span>
                                                            <strong class="text-dark">TPBank (Ngân hàng Tiên Phong)</strong>
                                                        </li>
                                                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                                            <span class="text-muted">Số tài khoản:</span>
                                                            <div>
                                                                <strong class="text-primary font-weight-bold" style="font-size: 16px;">12325072005</strong>
                                                                <button class="btn btn-sm btn-outline-secondary ml-1 py-0 px-2 copy-btn" onclick="copyValue('12325072005', 'Số tài khoản')">
                                                                    <i class="fa fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                                            <span class="text-muted">Tên chủ tài khoản:</span>
                                                            <strong class="text-dark font-weight-bold">HOANG NGOC THI</strong>
                                                        </li>
                                                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                                            <span class="text-muted">Số tiền nộp (1% giá trị xe):</span>
                                                            <div>
                                                                <strong class="text-danger font-weight-bold" style="font-size: 17px;">{{ number_format($commissionAmount) }} VNĐ</strong>
                                                                <button class="btn btn-sm btn-outline-secondary ml-1 py-0 px-2 copy-btn" onclick="copyValue('{{ $commissionAmount }}', 'Số tiền')">
                                                                    <i class="fa fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                                            <span class="text-muted">Nội dung chuyển khoản chuẩn:</span>
                                                            <div>
                                                                <span class="bg-warning text-dark font-weight-bold px-2 py-1 rounded">HH1 {{ $app->appointment_code }}</span>
                                                                <button class="btn btn-sm btn-outline-secondary ml-1 py-0 px-2 copy-btn" onclick="copyValue('HH1 {{ $app->appointment_code }}', 'Nội dung chuyển khoản')">
                                                                    <i class="fa fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </li>
                                                    </ul>

                                                    <div class="alert alert-warning py-2 px-3 small border mb-0">
                                                        <i class="fa fa-exclamation-triangle text-danger mr-1"></i>
                                                        <strong>Lưu ý:</strong> Giữ đúng nội dung <strong>HH1 {{ $app->appointment_code }}</strong> để SePay tự động khớp giao dịch và chuyển trạng thái sang đã nộp hoa hồng ngay lập tức.
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Form nộp mã xác nhận trong modal -->
                                            <div class="mt-4 pt-3 border-top">
                                                <form action="{{ route('partner.appointments.confirmCommission', $app->id) }}" method="POST" class="row align-items-center">
                                                    @csrf
                                                    <div class="col-md-7 mb-2 mb-md-0">
                                                        <input type="text" name="payment_proof" class="form-control form-control-sm font-weight-bold" 
                                                               placeholder="Hoặc nhập mã giao dịch ngân hàng (nếu quét QR chưa kịp nhận)..." required>
                                                    </div>
                                                    <div class="col-md-5 d-flex">
                                                        <button type="button" class="btn btn-sm btn-info font-weight-bold flex-grow-1 mr-1" onclick="checkStatusNow('{{ $app->appointment_code }}', {{ $app->id }})">
                                                            <i class="fa fa-refresh mr-1"></i> Kiểm tra ngay
                                                        </button>
                                                        <button type="submit" class="btn btn-sm btn-success font-weight-bold">
                                                            <i class="fa fa-check mr-1"></i> Nộp mã GD
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="modal-footer py-2 bg-light">
                                            <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal" onclick="stopSepayPolling({{ $app->id }})">
                                                Đóng cửa sổ
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa fa-calendar-times-o fa-3x text-muted mb-2 d-block"></i>
                                <div class="font-weight-bold">Không tìm thấy lịch hẹn xem xe nào phù hợp.</div>
                                <div class="small">Vui lòng thử lại với các tiêu chí tìm kiếm hoặc trạng thái khác.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $appointments->links() }}
</div>

<script>
    // Xử lý thay đổi lựa chọn tiến độ & kết quả chốt bán xe
    function handleSelectChange(appId) {
        var statusSelect = document.getElementById('statusSelect' + appId);
        var dealSelect = document.getElementById('dealSelect' + appId);
        var dealPriceBox = document.getElementById('dealPriceBox' + appId);
        var btnSubmit = document.getElementById('btnSubmit' + appId);

        if (!statusSelect || !dealSelect || !btnSubmit) return;

        var statusVal = statusSelect.value;
        var dealVal = dealSelect.value;

        // Nếu chọn Khách đã mua xe
        if (dealVal === 'deal_won') {
            if (dealPriceBox) dealPriceBox.style.display = 'block';
            recalcCommission(appId);
            btnSubmit.className = 'btn btn-sm btn-warning btn-block font-weight-bold shadow-sm text-dark';
            btnSubmit.innerHTML = '<i class="fa fa-credit-card mr-1"></i> Chốt mua & Nộp 1% hoa hồng sàn';
            return;
        }

        // Nếu chọn Khách không mua hoặc Khách hủy hẹn -> Cảnh báo khóa vĩnh viễn
        if (dealVal === 'deal_lost' || statusVal === 'cancelled') {
            if (dealPriceBox) dealPriceBox.style.display = 'none';
            btnSubmit.className = 'btn btn-sm btn-danger btn-block font-weight-bold shadow-sm';
            btnSubmit.innerHTML = '<i class="fa fa-lock mr-1"></i> Xác nhận hủy & KHÓA VĨNH VIỄN';
            return;
        }

        // Bình thường: Đang tư vấn đàm phán
        if (dealPriceBox) dealPriceBox.style.display = 'none';
        btnSubmit.className = 'btn btn-sm btn-success btn-block font-weight-bold shadow-sm';
        btnSubmit.innerHTML = '<i class="fa fa-save mr-1"></i> Cập nhật kết quả';
    }

    // Tự động tính 1% hoa hồng sàn khi nhập giá bán thực tế
    function recalcCommission(appId) {
        var input = document.getElementById('dealPriceInput' + appId);
        var preview = document.getElementById('commissionPreview' + appId);
        if (!input || !preview) return;

        var price = parseFloat(input.value) || 0;
        var comm = Math.round(price * 0.01);
        preview.textContent = new Intl.NumberFormat('vi-VN').format(comm);
    }

    // Xác nhận cảnh báo trước khi submit form cập nhật
    function handleFormSubmit(event, appId) {
        var statusSelect = document.getElementById('statusSelect' + appId);
        var dealSelect = document.getElementById('dealSelect' + appId);

        var statusVal = statusSelect ? statusSelect.value : '';
        var dealVal = dealSelect ? dealSelect.value : '';

        // Trường hợp 1: Chọn hủy / không mua -> Cảnh báo khóa vĩnh viễn
        if (dealVal === 'deal_lost' || statusVal === 'cancelled') {
            var msg = "⚠️ CẢNH BÁO QUAN TRỌNG VỀ KHÓA TRẠNG THÁI:\n\n" +
                      "Bạn đang chọn trạng thái KHÁCH KHÔNG MUA / HỦY LỊCH HẸN.\n" +
                      "Theo quy định sàn, sau khi lưu, trạng thái này sẽ bị KHÓA VĨNH VIỄN và KHÔNG THỂ CHUYỂN ĐỔI LẠI ĐƯỢC NỮA!\n\n" +
                      "Bạn có chắc chắn muốn xác nhận không?";
            return confirm(msg);
        }

        // Trường hợp 2: Chọn khách mua xe -> Cảnh báo nộp 1% hoa hồng
        if (dealVal === 'deal_won') {
            var priceInput = document.getElementById('dealPriceInput' + appId);
            var priceVal = priceInput ? parseFloat(priceInput.value) || 0 : 0;
            var comm = Math.round(priceVal * 0.01);
            var commText = new Intl.NumberFormat('vi-VN').format(comm);

            var msg = "🏆 XÁC NHẬN KHÁCH ĐÃ ĐỒNG Ý MUA XE:\n\n" +
                      "Giá bán chốt: " + new Intl.NumberFormat('vi-VN').format(priceVal) + " VNĐ\n" +
                      "Hoa hồng sàn thu 1%: " + commText + " VNĐ\n\n" +
                      "Nhà xe cần thanh toán 1% hoa hồng này qua VietQR SePay hoặc MoMo. Sau khi nộp hoa hồng xong, hồ sơ sẽ được KHÓA HOÀN TẤT.\n\n" +
                      "Bấm OK để tiếp tục mở bước nộp hoa hồng?";
            return confirm(msg);
        }

        return true;
    }

    // Sao chép nội dung vào Clipboard
    function copyValue(text, label) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                alert("✅ Đã sao chép " + label + ": " + text);
            }).catch(function() {
                prompt("Sao chép thủ công:", text);
            });
        } else {
            prompt("Sao chép thủ công:", text);
        }
    }

    // Quản lý Polling SePay tự động mỗi 3 giây
    var sepayPollingIntervals = {};

    function startSepayPolling(appointmentCode, appId) {
        stopSepayPolling(appId);

        var checkUrl = "{{ url('/payment/sepay/check/appointment') }}/" + appointmentCode;

        sepayPollingIntervals[appId] = setInterval(function() {
            fetch(checkUrl)
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data && data.paid) {
                        clearInterval(sepayPollingIntervals[appId]);
                        var badge = document.getElementById('pollBadge' + appId);
                        if (badge) {
                            badge.innerHTML = '<span class="badge badge-success px-3 py-2 font-weight-bold"><i class="fa fa-check-circle mr-1"></i> ĐÃ THANH TOÁN THÀNH CÔNG! ĐANG TẢI LẠI...</span>';
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 1200);
                    }
                })
                .catch(function(err) {
                    console.log('SePay Polling Check:', err);
                });
        }, 3000);
    }

    function stopSepayPolling(appId) {
        if (sepayPollingIntervals[appId]) {
            clearInterval(sepayPollingIntervals[appId]);
            delete sepayPollingIntervals[appId];
        }
    }

    function checkStatusNow(appointmentCode, appId) {
        var checkUrl = "{{ url('/payment/sepay/check/appointment') }}/" + appointmentCode;
        var badge = document.getElementById('pollBadge' + appId);
        if (badge) {
            badge.innerHTML = '<span class="badge badge-info px-3 py-1"><i class="fa fa-spinner fa-spin mr-1"></i> Đang đối soát với TPBank...</span>';
        }

        fetch(checkUrl)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.paid) {
                    alert("✅ Chúc mừng! Hệ thống đã nhận được 1% hoa hồng chuyển khoản qua SePay. Hồ sơ đang được khóa lại vĩnh viễn.");
                    window.location.reload();
                } else {
                    alert("⏳ Hệ thống chưa nhận được biến động số dư cho mã " + appointmentCode + ".\nNếu vừa chuyển, vui lòng chờ 5-10 giây để ngân hàng gửi tin nhắn biến động.");
                    if (badge) {
                        badge.innerHTML = '<span class="badge badge-warning text-dark px-3 py-1 font-weight-bold"><i class="fa fa-clock-o mr-1"></i> Đang chờ nhận biến động số dư...</span>';
                    }
                }
            })
            .catch(function() {
                alert("Không thể kiểm tra lúc này. Vui lòng thử lại sau.");
            });
    }
</script>
@endsection