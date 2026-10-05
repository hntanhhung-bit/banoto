@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">
        <!-- Tiêu đề -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div>
                <h3 class="font-weight-bold text-dark mb-1">
                    <i class="fa fa-key text-danger mr-2"></i> QUẢN LÝ ĐƠN THUÊ XE & KÝ QUỸ HOÀN CỌC
                </h3>
                <p class="text-muted small mb-0">
                    Giám sát tiến trình bàn giao xe của Showroom Đối tác và thực hiện hoàn cọc ký quỹ cho khách khi xe đã được nghiệm thu trả.
                </p>
            </div>
        </div>

        <!-- CHUYỂN TAB ĐƠN MUA XE / ĐƠN THUÊ XE -->
        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link font-weight-bold bg-white text-primary border shadow-sm"
                    href="{{ route('admin.orders.index') }}">
                    <i class="fa fa-shopping-cart mr-1"></i> Đơn Mua Xe ({{ \App\Models\Order::count() }})
                </a>
            </li>
            <li class="nav-item ml-2">
                <a class="nav-link active font-weight-bold bg-danger" href="{{ route('admin.rentals.index') }}">
                    <i class="fa fa-key mr-1"></i> Đơn Thuê Xe & Đặt Cọc MoMo ({{ \App\Models\Rental::count() }})
                </a>
            </li>
        </ul>

        <!-- Bộ lọc tìm kiếm -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('admin.rentals.index') }}" method="GET">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm"
                                placeholder="Mã đơn, tên khách, số điện thoại...">
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <select name="rental_type" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả loại hình --</option>
                                <option value="self_drive" {{ request('rental_type') == 'self_drive' ? 'selected' : '' }}>Thuê tự lái</option>
                                <option value="with_driver" {{ request('rental_type') == 'with_driver' ? 'selected' : '' }}>Có tài xế riêng</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <select name="progress_filter" class="form-control form-control-sm font-weight-bold text-danger" onchange="this.form.submit()">
                                <option value="">-- Tiến trình đối tác & Hoàn cọc --</option>
                                <option value="waiting_refund" {{ request('progress_filter') == 'waiting_refund' ? 'selected' : '' }}>
                                    🚨 Đơn chờ Admin thanh toán hoàn cọc
                                </option>
                                <option value="in_progress" {{ request('progress_filter') == 'in_progress' ? 'selected' : '' }}>
                                    🚗 Đối tác đã bàn giao / Đang lưu thông
                                </option>
                                <option value="pending_handover" {{ request('progress_filter') == 'pending_handover' ? 'selected' : '' }}>
                                    ⏳ Chờ đối tác giao xe (chưa OTP)
                                </option>
                                <option value="refunded" {{ request('progress_filter') == 'refunded' ? 'selected' : '' }}>
                                    ✓ Sàn đã hoàn cọc cho khách
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <select name="rental_status" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">-- Trạng thái đơn --</option>
                                <option value="pending" {{ request('rental_status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="confirmed" {{ request('rental_status') == 'confirmed' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="in_progress" {{ request('rental_status') == 'in_progress' ? 'selected' : '' }}>Đang phục vụ</option>
                                <option value="returned" {{ request('rental_status') == 'returned' ? 'selected' : '' }}>Đã trả xe</option>
                                <option value="cancelled" {{ request('rental_status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-right">
                            <button type="submit" class="btn btn-primary font-weight-bold btn-sm"><i class="fa fa-filter"></i> Lọc</button>
                            @if(request()->hasAny(['keyword', 'rental_type', 'rental_status', 'progress_filter']))
                                <a href="{{ route('admin.rentals.index') }}" class="btn btn-outline-secondary font-weight-bold btn-sm ml-1">
                                    <i class="fa fa-refresh"></i> Xóa
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- THANH CÔNG CỤ THAO TÁC HÀNG LOẠT (BULK ACTIONS BAR) -->
        <div class="card border-0 shadow-sm mb-3 bg-white" style="border-radius: 10px;">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center flex-wrap mb-2 mb-md-0">
                    <span class="badge badge-light border px-2 py-1 font-weight-bold text-dark mr-3" style="font-size: 13px;">
                        <i class="fa fa-check-square text-primary mr-1"></i> Đã chọn: 
                        <span id="selectedRentalCount" class="text-danger font-weight-bold">0</span> đơn thuê xe
                    </span>
                    
                    <div class="d-flex align-items-center mr-2 mb-1 mb-md-0">
                        <span class="small font-weight-bold text-muted mr-1">Trạng thái đơn:</span>
                        <select id="bulkRentalStatus" class="custom-select custom-select-sm" style="min-width: 140px;">
                            <option value="">-- Giữ nguyên --</option>
                            <option value="pending">⏳ Chờ duyệt</option>
                            <option value="confirmed">✓ Đã duyệt</option>
                            <option value="in_progress">🚗 Đang phục vụ</option>
                            <option value="returned">★ Đã trả xe</option>
                            <option value="cancelled">✕ Đã hủy</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center mr-2 mb-1 mb-md-0">
                        <span class="small font-weight-bold text-muted mr-1">Thanh toán & Cọc:</span>
                        <select id="bulkRentalPayment" class="custom-select custom-select-sm" style="min-width: 150px;">
                            <option value="">-- Giữ nguyên --</option>
                            <option value="deposit_paid">✓ Đã nhận cọc (Cấp OTP)</option>
                            <option value="fully_paid">✅ Đã tất toán đủ</option>
                            <option value="unpaid">⌛ Chưa cọc/Chưa trả</option>
                        </select>
                    </div>

                    <button type="button" id="btnBulkRentalSubmit" class="btn btn-sm btn-primary font-weight-bold shadow-sm" disabled>
                        <i class="fa fa-refresh mr-1"></i> Cập nhật hàng loạt
                    </button>
                </div>
                
                <div class="small text-muted">
                    <i class="fa fa-info-circle text-info"></i> Tích chọn các ô để thay đổi trạng thái cùng lúc
                </div>
            </div>
        </div>

            <!-- Danh sách đơn thuê xe -->
            <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-uppercase text-secondary font-weight-bold" style="font-size: 12px;">
                            <tr>
                                <th class="py-3 text-center align-middle" width="40px">
                                    <input type="checkbox" id="checkAllRentals" style="transform: scale(1.2); cursor: pointer;" title="Chọn tất cả">
                                </th>
                                <th class="py-3 px-3">Hợp đồng & Khách</th>
                                <th class="py-3">Mẫu xe & Showroom Đối tác</th>
                                <th class="py-3">Thời gian thuê</th>
                                <th class="py-3 text-center" style="min-width: 210px;">Tiến trình Showroom Đối tác</th>
                                <th class="py-3" style="min-width: 220px;">Tài khoản & Ký quỹ hoàn cọc</th>
                                <th class="py-3 text-right px-3" style="min-width: 170px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rentals as $rental)
                                @php
                                    $isDriver = ($rental->rental_type === 'with_driver');
                                    $isWaitingRefund = ($rental->refund_status === 'waiting_admin' || ($rental->rental_status === 'returned' && $rental->refund_status !== 'refunded'));
                                    $showroomName = $rental->partner?->company_name ?: ($rental->partner?->name ?: ($rental->product?->partner?->company_name ?? ($rental->product?->partner?->name ?? 'Showroom AutoCar')));
                                @endphp
                                <tr class="{{ $isWaitingRefund ? 'table-warning' : '' }}">
                                    <!-- Cột Checkbox -->
                                    <td class="text-center align-middle">
                                        <input type="checkbox" name="rental_ids[]" value="{{ $rental->id }}" class="rental-checkbox" style="transform: scale(1.2); cursor: pointer;">
                                    </td>
                                    <!-- Cột 1: Hợp đồng & Khách -->
                                    <td class="px-3">
                                        <a href="{{ route('admin.rentals.show', $rental->id) }}"
                                            class="text-primary font-weight-bold text-decoration-none" style="font-size: 14px;">
                                            #{{ $rental->rental_code }}
                                        </a>
                                    @if($isDriver)
                                        <span class="badge badge-primary d-inline-block mt-1"><i class="fa fa-user-circle"></i> Có tài xế</span>
                                    @else
                                        <span class="badge badge-danger d-inline-block mt-1"><i class="fa fa-key"></i> Tự lái</span>
                                    @endif
                                    <div class="font-weight-bold text-dark mt-1">{{ $rental->customer_name }}</div>
                                    <small class="text-muted d-block"><i class="fa fa-phone text-success"></i> {{ $rental->customer_phone }}</small>
                                    <div class="small text-muted font-weight-normal mt-1">{{ $rental->created_at->format('d/m/Y H:i') }}</div>
                                </td>

                                <!-- Cột 2: Mẫu xe & Showroom Đối tác -->
                                <td>
                                    <div class="d-flex align-items-center mb-1">
                                        <img src="{{ $rental->product ? $rental->product->image_url : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=100&auto=format&fit=crop&q=60' }}" 
                                             class="rounded mr-2 border" style="width: 55px; height: 40px; object-fit: cover;" 
                                             alt="{{ $rental->product->name ?? 'Xe' }}"
                                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=100&auto=format&fit=crop&q=60';">
                                        <div>
                                            <strong class="text-dark d-block">{{ $rental->product->name ?? 'Xe' }}</strong>
                                            <small class="text-muted">{{ $rental->selected_color ?: 'Màu tiêu chuẩn' }}</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-light border text-success font-weight-bold" title="Showroom Đối tác trực tiếp giữ và quản lý xe">
                                            <i class="fa fa-building-o mr-1"></i> {{ $showroomName }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Cột 3: Thời gian thuê & Lịch trình -->
                                <td>
                                    <div><strong>Từ:</strong> {{ date('d/m/Y', strtotime($rental->start_date)) }}</div>
                                    <div><strong>Đến:</strong> {{ date('d/m/Y', strtotime($rental->end_date)) }}</div>
                                    <small class="badge badge-light border text-muted">{{ $rental->total_days }} ngày</small>
                                    <div class="small text-muted text-truncate mt-1" style="max-width: 170px;" title="{{ $rental->customer_address }}">
                                        <i class="fa fa-map-marker text-danger"></i> {{ $rental->customer_address }}
                                    </div>
                                </td>

                                <!-- Cột 4: Tiến trình Showroom Đối tác (Giám sát) -->
                                <td class="text-center align-middle">
                                    @if($rental->refund_status === 'refunded')
                                        <div class="p-2 rounded bg-light border border-success text-success text-center">
                                            <div class="font-weight-bold"><i class="fa fa-check-circle"></i> ĐÃ HOÀN TẤT HỢP ĐỒNG</div>
                                            <small class="text-muted d-block">Xe đã trả về kho & Sàn đã hoàn cọc</small>
                                            @if($rental->return_odo)
                                                <small class="text-dark">ODO trả: {{ number_format($rental->return_odo) }} km</small>
                                            @endif
                                        </div>
                                    @elseif($isWaitingRefund)
                                        <div class="p-2 rounded bg-white border border-danger text-danger text-center shadow-sm">
                                            <div class="font-weight-bold text-danger">
                                                <i class="fa fa-bell text-danger"></i> ĐỐI TÁC ĐÃ NHẬN LẠI XE
                                            </div>
                                            <div class="small text-dark mt-1 font-weight-bold">
                                                Yêu cầu Admin hoàn cọc:
                                            </div>
                                            <div class="text-danger font-weight-bold" style="font-size: 15px;">
                                                {{ number_format($rental->refund_amount ?: $rental->deposit_amount) }} đ
                                            </div>
                                            @if($rental->return_odo)
                                                <div class="text-muted small" style="font-size: 10px;">
                                                    ODO trả: {{ number_format($rental->return_odo) }} km | Xăng: {{ $rental->return_fuel }}%
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($rental->rental_status === 'in_progress')
                                        <div class="p-2 rounded bg-light border border-primary text-primary text-center">
                                            <div class="font-weight-bold"><i class="fa fa-road"></i> XE ĐANG LƯU THÔNG</div>
                                            <div class="small text-muted font-weight-normal mt-1">
                                                Mã OTP đối chiếu: <strong class="text-dark font-monospace">{{ $rental->handover_code }}</strong>
                                            </div>
                                            @if($rental->handover_odo)
                                                <div class="small text-muted" style="font-size: 10px;">
                                                    ODO giao: {{ number_format($rental->handover_odo) }} km
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($rental->rental_status === 'confirmed')
                                        <div class="p-2 rounded bg-light border border-info text-info text-center">
                                            <div class="font-weight-bold"><i class="fa fa-check"></i> ĐÃ DUYỆT ĐƠN</div>
                                            <small class="text-muted d-block">Showroom đã chuẩn bị xe sẵn sàng</small>
                                        </div>
                                    @elseif($rental->rental_status === 'pending')
                                        <div class="p-2 rounded bg-light border border-warning text-dark text-center">
                                            <div class="font-weight-bold text-warning"><i class="fa fa-clock-o"></i> CHỜ ĐỐI TÁC GIAO XE</div>
                                            <div class="small mt-1">
                                                @if($rental->handover_code)
                                                    OTP: <strong class="text-success font-monospace">{{ $rental->handover_code }}</strong>
                                                @else
                                                    <span class="badge badge-secondary font-weight-normal">Chưa cấp OTP (Chờ cọc)</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1 font-weight-bold">Đã hủy đơn</span>
                                    @endif
                                </td>

                                <!-- Cột 5: Tài khoản & Ký quỹ hoàn cọc -->
                                <td>
                                    <div class="p-2 bg-light rounded border">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Cọc Ký quỹ:</span>
                                            <strong class="text-danger font-weight-bold">{{ number_format($rental->deposit_amount) }} đ</strong>
                                        </div>
                                        <div class="border-top pt-1 text-muted" style="font-size: 11px;">
                                            <div>NH: <strong>{{ $rental->refund_bank_name ?: 'Chưa cung cấp' }}</strong></div>
                                            <div>STK: <strong class="text-primary" id="stk_{{ $rental->id }}">{{ $rental->refund_account_number ?: 'Chưa có' }}</strong>
                                                @if($rental->refund_account_number)
                                                    <button type="button" class="btn btn-xs btn-link p-0 ml-1" onclick="navigator.clipboard.writeText('{{ $rental->refund_account_number }}'); alert('Đã sao chép STK: {{ $rental->refund_account_number }}');" title="Sao chép STK">
                                                        <i class="fa fa-copy"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            <div>Tên: <strong>{{ $rental->refund_account_holder ?: $rental->customer_name }}</strong></div>
                                        </div>
                                        <div class="mt-1">
                                            @if($rental->partner_id)
                                                @if($rental->partner_commission_status === 'paid')
                                                    <span class="badge badge-success btn-block mb-1 font-weight-bold" title="Mã GD: {{ $rental->partner_commission_proof }}">
                                                        <i class="fa fa-check-circle"></i> Đã nộp 10% HH: {{ number_format($rental->partner_commission_fee) }}đ
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning btn-block mb-1 text-dark font-weight-bold" style="background-color: #fff3cd; border: 1px solid #ffeeba;" title="Chưa thể hoàn cọc cho đến khi đối tác nộp 10% hoa hồng sàn">
                                                        <i class="fa fa-exclamation-triangle text-danger mr-1"></i> Chưa nộp 10% hoa hồng sàn
                                                    </span>
                                                @endif
                                            @endif

                                            @if($rental->refund_status === 'refunded')
                                                <span class="badge badge-success btn-block py-1 font-weight-bold"><i class="fa fa-check-circle"></i> Sàn đã hoàn cọc: {{ number_format($rental->refund_amount) }}đ</span>
                                            @elseif($isWaitingRefund)
                                                <span class="badge badge-danger btn-block font-weight-bold py-1 animate-pulse">
                                                    <i class="fa fa-exclamation-triangle"></i> CẦN THANH TOÁN HOÀN CỌC
                                                </span>
                                            @elseif($rental->payment_status === 'deposit_paid' || $rental->payment_status === 'fully_paid')
                                                <span class="badge badge-success btn-block py-1 font-weight-bold" style="font-size: 11px;">
                                                    <i class="fa fa-check-circle"></i> ĐÃ NHẬN ĐƯỢC CỌC ({{ number_format($rental->deposit_amount) }}đ)
                                                </span>
                                                <small class="text-muted d-block text-center mt-1" style="font-size: 10px;">
                                                    <i class="fa fa-shield text-info"></i> Sàn giữ cọc Escrow
                                                </small>
                                            @else
                                                <span class="badge badge-secondary btn-block py-1 font-weight-bold">
                                                    <i class="fa fa-clock-o"></i> Chưa nộp tiền cọc
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Cột 6: Thao tác -->
                                <td class="text-right px-3 align-middle">
                                    @if($isWaitingRefund)
                                        @php
                                            $canRefund = (!$rental->partner_id || $rental->partner_commission_status === 'paid');
                                            $comm10Amount = (int) ($rental->partner_commission_fee ?: round(($rental->total_rental_fee + $rental->total_driver_fee) * 0.10));
                                        @endphp

                                        <!-- NÚT HOÀN CỌC DÀNH CHO ADMIN -->
                                        @if($canRefund)
                                            <button type="button" class="btn btn-warning btn-sm font-weight-bold btn-block mb-1 shadow-sm text-dark"
                                                data-toggle="modal" data-target="#adminRefundModal{{ $rental->id }}">
                                                <i class="fa fa-money mr-1"></i> Thanh toán hoàn cọc
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-outline-warning btn-sm font-weight-bold btn-block mb-1 shadow-sm text-dark"
                                                data-toggle="modal" data-target="#adminRefundModal{{ $rental->id }}" title="Đối tác chưa nộp 10% hoa hồng sàn">
                                                <i class="fa fa-ban mr-1 text-danger"></i> Chờ đối tác nộp 10% HH
                                            </button>
                                        @endif

                                        <!-- MODAL ADMIN THANH TOÁN HOÀN CỌC NHANH -->
                                        <div class="modal fade text-left" id="adminRefundModal{{ $rental->id }}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg">
                                                    <div class="modal-header {{ $canRefund ? 'bg-warning text-dark' : 'bg-secondary text-white' }} font-weight-bold">
                                                        <h6 class="modal-title font-weight-bold">
                                                            <i class="fa fa-money mr-1"></i> THANH TOÁN HOÀN CỌC CHO KHÁCH #{{ $rental->rental_code }}
                                                        </h6>
                                                        <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form action="{{ route('admin.rentals.refund', $rental->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            @if(!$canRefund)
                                                                <!-- CẢNH BÁO CHẶN HOÀN CỌC NẾU ĐỐI TÁC CHƯA NỘP 10% HOA HỒNG -->
                                                                <div class="alert alert-danger font-weight-bold mb-3 shadow-sm border-danger p-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="fa fa-ban fa-2x mr-2 text-danger"></i>
                                                                        <div>
                                                                            <div style="font-size: 14px;">⛔ CHẶN HOÀN CỌC: ĐỐI TÁC CHƯA NỘP 10% HOA HỒNG SÀN</div>
                                                                            <div class="small font-weight-normal mt-1 text-dark">
                                                                                Showroom đối tác (<strong>{{ $showroomName }}</strong>) chưa thanh toán <strong>10% phí hoa hồng sàn ({{ number_format($comm10Amount) }} VNĐ)</strong> cho đơn thuê này.
                                                                                <br><br>
                                                                                <strong class="text-danger">Quy định Sàn:</strong> Hệ thống chỉ cho phép Admin xác nhận hoàn cọc ký quỹ cho khách hàng sau khi Đối tác đã thanh toán đủ 10% hoa hồng sàn.
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="alert alert-success small mb-3">
                                                                    <i class="fa fa-check-circle mr-1"></i> <strong>Showroom đối tác ({{ $showroomName }}) đã nghiệm thu xe xong!</strong><br>
                                                                    Admin tiến hành chuyển khoản trả lại tiền cọc ký quỹ Escrow cho khách theo thông tin tài khoản dưới đây.
                                                                </div>
                                                                @if($rental->partner_id)
                                                                    <div class="alert alert-success small py-2 mb-3">
                                                                        <i class="fa fa-check-circle mr-1"></i>
                                                                        <strong>Nghĩa vụ 10% hoa hồng sàn của Đối tác:</strong><br>
                                                                        <span class="text-success font-weight-bold">✓ Đối tác đã nộp đủ {{ number_format($rental->partner_commission_fee) }} VNĐ (Mã GD ngân hàng: <code>{{ $rental->partner_commission_proof }}</code>).</span>
                                                                    </div>
                                                                @endif
                                                            @endif

                                                            <!-- KHỐI THÔNG TIN TÀI KHOẢN NGÂN HÀNG CỦA KHÁCH -->
                                                            <div class="p-3 bg-light rounded border mb-3 small">
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted font-weight-bold">Khách nhận:</span>
                                                                    <strong class="text-dark">{{ $rental->customer_name }} ({{ $rental->customer_phone }})</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted font-weight-bold">Ngân hàng:</span>
                                                                    <strong class="text-dark">{{ $rental->refund_bank_name ?: 'Chưa cung cấp' }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted font-weight-bold">Số tài khoản:</span>
                                                                    <span>
                                                                        <strong class="text-primary font-weight-bold" style="font-size: 15px;">{{ $rental->refund_account_number ?: 'Chưa có' }}</strong>
                                                                        @if($rental->refund_account_number)
                                                                            <button type="button" class="btn btn-xs btn-outline-secondary ml-1" onclick="navigator.clipboard.writeText('{{ $rental->refund_account_number }}'); alert('Đã sao chép STK!');">Sao chép</button>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted font-weight-bold">Chủ tài khoản:</span>
                                                                    <strong class="text-uppercase text-dark">{{ $rental->refund_account_holder ?: $rental->customer_name }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1 border-top pt-1">
                                                                    <span class="text-muted font-weight-bold">Tiền cọc gốc Sàn giữ:</span>
                                                                    <strong class="text-danger">{{ number_format($rental->deposit_amount) }} VNĐ</strong>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 form-group">
                                                                    <label class="small font-weight-bold text-success">Số tiền hoàn cọc chuyển khách (VNĐ):</label>
                                                                    <input type="number" name="refund_amount" class="form-control font-weight-bold text-success"
                                                                        value="{{ $rental->refund_amount > 0 ? $rental->refund_amount : $rental->deposit_amount }}" {{ !$canRefund ? 'disabled' : 'required' }}>
                                                                    <small class="text-muted">Theo đề xuất đối tác</small>
                                                                </div>
                                                                <div class="col-md-6 form-group">
                                                                    <label class="small font-weight-bold text-warning">Giữ lại phạt nguội (VNĐ):</label>
                                                                    <input type="number" name="refund_holding_fee" class="form-control font-weight-bold text-warning"
                                                                        value="{{ $rental->refund_holding_fee ?: 0 }}" {{ !$canRefund ? 'disabled' : '' }}>
                                                                    <small class="text-muted">Nếu có thỏa thuận</small>
                                                                </div>
                                                            </div>

                                                            <div class="form-group mb-0">
                                                                <label class="small font-weight-bold text-muted">Ghi chú đối soát hoàn cọc:</label>
                                                                <input type="text" name="refund_notes" class="form-control form-control-sm"
                                                                    value="{{ $rental->refund_notes ?: 'Admin đã chuyển khoản hoàn cọc cho khách hàng' }}"
                                                                    placeholder="VD: Đã chuyển khoản qua Internet Banking..." {{ !$canRefund ? 'disabled' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal">Đóng</button>
                                                            @if($canRefund)
                                                                <button type="submit" class="btn btn-success btn-sm font-weight-bold"
                                                                    onclick="return confirm('Xác nhận bạn đã chuyển khoản hoàn cọc cho khách hàng vào tài khoản {{ $rental->refund_account_number }}?');">
                                                                    <i class="fa fa-paper-plane mr-1"></i> XÁC NHẬN ĐÃ CHUYỂN TIỀN HOÀN CỌC
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-secondary btn-sm font-weight-bold" disabled>
                                                                    <i class="fa fa-lock mr-1"></i> KHÔNG THỂ HOÀN CỌC (CHỜ ĐỐI TÁC NỘP 10% HH)
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- NÚT XÁC NHẬN NHẬN CỌC (KÈM MODAL HỎI LẠI ĐỂ XÁC NHẬN) -->
                                    @if($rental->payment_status === 'unpaid')
                                        <button type="button" class="btn btn-success btn-sm btn-block font-weight-bold shadow-sm mb-1"
                                            data-toggle="modal" data-target="#confirmDepositModal{{ $rental->id }}"
                                            title="Bấm để xác nhận đã nhận cọc và cấp mã nhận xe">
                                            <i class="fa fa-check-circle mr-1"></i> Xác nhận nhận cọc
                                        </button>

                                        <!-- MODAL XÁC NHẬN ĐÃ NHẬN TIỀN CỌC KÈM HỎI LẠI XÁC NHẬN -->
                                        <div class="modal fade text-left" id="confirmDepositModal{{ $rental->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg">
                                                    <div class="modal-header bg-success text-white">
                                                        <h6 class="modal-title font-weight-bold">
                                                            <i class="fa fa-shield mr-1"></i> XÁC NHẬN ĐÃ NHẬN ĐƯỢC TIỀN CỌC
                                                        </h6>
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form action="{{ route('admin.rentals.confirmDeposit', $rental->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body p-4">
                                                            <div class="alert alert-info small mb-3">
                                                                <i class="fa fa-info-circle mr-1"></i> Bạn đang thực hiện thao tác thủ công xác nhận tiền cọc cho khách hàng thanh toán ngoài hoặc chuyển khoản trễ.
                                                            </div>

                                                            <!-- THÔNG TIN ĐƠN & TIỀN CỌC -->
                                                            <div class="p-3 bg-light rounded border mb-3 small">
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted">Mã hợp đồng:</span>
                                                                    <strong class="text-primary font-weight-bold">#{{ $rental->rental_code }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted">Khách hàng:</span>
                                                                    <strong class="text-dark">{{ $rental->customer_name }} ({{ $rental->customer_phone }})</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted">Mẫu xe thuê:</span>
                                                                    <strong class="text-dark">{{ $rental->product->name ?? 'Xe tự lái' }}</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1 border-top pt-2 mt-2">
                                                                    <span class="text-dark font-weight-bold">Số tiền cọc cần thu:</span>
                                                                    <strong class="text-danger font-weight-bold" style="font-size: 17px;">
                                                                        {{ number_format($rental->deposit_amount) }} VNĐ
                                                                    </strong>
                                                                </div>
                                                            </div>

                                                            <!-- CẢNH BÁO HỎI LẠI ĐỂ XÁC NHẬN (DOUBLE CONFIRMATION) -->
                                                            <div class="alert alert-warning border-warning p-3 mb-0">
                                                                <div class="d-flex align-items-start">
                                                                    <i class="fa fa-question-circle fa-2x text-warning mr-2 mt-1"></i>
                                                                    <div>
                                                                        <strong class="text-dark" style="font-size: 13px;">HỎI LẠI ĐỂ XÁC NHẬN:</strong>
                                                                        <p class="small text-dark mb-1 mt-1">
                                                                            Bạn có chắc chắn tài khoản ngân hàng của Sàn đã thực tế nhận đủ số tiền <strong>{{ number_format($rental->deposit_amount) }} VNĐ</strong> từ khách hàng chưa?
                                                                        </p>
                                                                        <div class="small text-muted font-italic">
                                                                            Khi bấm xác nhận, hệ thống sẽ tự động cập nhật trạng thái đơn thành: <strong class="text-success">"ĐÃ NHẬN ĐƯỢC CỌC"</strong> và lập tức kích hoạt mã OTP 6 số để khách nhận xe.
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light py-2">
                                                            <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal">
                                                                Hủy bỏ / Chưa nhận được
                                                            </button>
                                                            <button type="submit" class="btn btn-success btn-sm font-weight-bold px-3 shadow-sm"
                                                                    onclick="return confirm('XÁC NHẬN LẦN CUỐI:\n\nBạn chắc chắn 100% đã nhận được ' + '{{ number_format($rental->deposit_amount) }}' + ' VNĐ tiền cọc vào tài khoản?');">
                                                                <i class="fa fa-check-circle mr-1"></i> TÔI XÁC NHẬN: ĐÃ NHẬN ĐƯỢC CỌC
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <a href="{{ route('admin.rentals.show', $rental->id) }}"
                                        class="btn btn-outline-primary btn-sm btn-block font-weight-bold mb-1"
                                        title="Xem tiến trình & Chi tiết">
                                        <i class="fa fa-eye mr-1"></i> Xem tiến trình
                                    </a>

                                    <form action="{{ route('admin.rentals.destroy', $rental->id) }}" method="POST"
                                        class="d-inline btn-block"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa đơn thuê xe này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-block" title="Xóa">
                                            <i class="fa fa-trash mr-1"></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa fa-key fa-3x mb-2 text-muted"></i>
                                    <p class="mb-0">Không tìm thấy đơn thuê xe nào.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($rentals->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $rentals->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
</div>

<!-- FORM ẨN ĐỂ SUBMIT BULK ACTION (TRÁNH LỖI NESTED FORM) -->
<form id="bulkRentalForm" action="{{ route('admin.rentals.bulkStatus') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="rental_status" id="hiddenRentalStatus" value="">
    <input type="hidden" name="payment_status" id="hiddenRentalPayment" value="">
    <div id="hiddenRentalIdsContainer"></div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAllRentals');
    const checkboxes = document.querySelectorAll('.rental-checkbox');
    const badge = document.getElementById('selectedRentalCount');
    const btnSubmit = document.getElementById('btnBulkRentalSubmit');
    const selectStatus = document.getElementById('bulkRentalStatus');
    const selectPayment = document.getElementById('bulkRentalPayment');
    const hiddenForm = document.getElementById('bulkRentalForm');
    const hiddenStatus = document.getElementById('hiddenRentalStatus');
    const hiddenPayment = document.getElementById('hiddenRentalPayment');
    const hiddenContainer = document.getElementById('hiddenRentalIdsContainer');

    function updateState() {
        const checked = document.querySelectorAll('.rental-checkbox:checked');
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

    if (btnSubmit) {
        btnSubmit.addEventListener('click', function () {
            const checked = document.querySelectorAll('.rental-checkbox:checked');
            if (checked.length === 0) {
                alert('Vui lòng chọn ít nhất một đơn thuê xe!');
                return;
            }
            if (!confirm('Bạn có chắc muốn cập nhật trạng thái cho các đơn thuê xe đã chọn?')) {
                return;
            }

            hiddenStatus.value = selectStatus.value;
            hiddenPayment.value = selectPayment.value;
            hiddenContainer.innerHTML = '';

            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'rental_ids[]';
                input.value = cb.value;
                hiddenContainer.appendChild(input);
            });

            hiddenForm.submit();
        });
    }
});
</script>
@endsection