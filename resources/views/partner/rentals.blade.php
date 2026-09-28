@extends('partner.layout')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-key text-success mr-2"></i> QUẢN LÝ ĐƠN THUÊ XE & KÝ QUỸ HOÀN CỌC
            </h3>
            <p class="text-muted small mb-0">Theo dõi hợp đồng thuê, điều phối xe/tài xế, kiểm tra biên bản bàn giao và xác
                nhận hoàn cọc ký quỹ cho khách</p>
        </div>
    </div>

        <!-- TABS PHÂN LOẠI TIẾN ĐỘ HỢP ĐỒNG -->
    <ul class="nav nav-pills mb-3 flex-wrap">
        <li class="nav-item mb-1">
            <a class="nav-link font-weight-bold {{ $status === 'all' ? 'active bg-dark' : 'bg-white text-dark border' }}" 
               href="{{ route('partner.rentals', array_merge(request()->except('rental_status', 'page'), ['rental_status' => 'all'])) }}">
                Tất cả hợp đồng ({{ $countTotal }})
            </a>
        </li>
        <li class="nav-item ml-2 mb-1">
            <a class="nav-link font-weight-bold {{ $status === 'pending' ? 'active bg-warning text-dark' : 'bg-white text-warning border' }}" 
               href="{{ route('partner.rentals', array_merge(request()->except('rental_status', 'page'), ['rental_status' => 'pending'])) }}">
                <i class="fa fa-clock-o mr-1"></i> Chờ đối chiếu / duyệt ({{ $countPending }})
                @if($countPending > 0)
                    <span class="badge badge-danger ml-1">{{ $countPending }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item ml-2 mb-1">
            <a class="nav-link font-weight-bold {{ $status === 'confirmed' ? 'active bg-info text-white' : 'bg-white text-info border' }}" 
               href="{{ route('partner.rentals', array_merge(request()->except('rental_status', 'page'), ['rental_status' => 'confirmed'])) }}">
                <i class="fa fa-check-circle mr-1"></i> Đã sẵn sàng xe ({{ $countConfirmed }})
            </a>
        </li>
        <li class="nav-item ml-2 mb-1">
            <a class="nav-link font-weight-bold {{ $status === 'in_progress' ? 'active bg-primary' : 'bg-white text-primary border' }}" 
               href="{{ route('partner.rentals', array_merge(request()->except('rental_status', 'page'), ['rental_status' => 'in_progress'])) }}">
                <i class="fa fa-car mr-1"></i> Đang phục vụ / Đang đi ({{ $countInProgress }})
            </a>
        </li>
        <li class="nav-item ml-2 mb-1">
            <a class="nav-link font-weight-bold {{ $status === 'returned' ? 'active bg-success' : 'bg-white text-success border' }}" 
               href="{{ route('partner.rentals', array_merge(request()->except('rental_status', 'page'), ['rental_status' => 'returned'])) }}">
                <i class="fa fa-trophy mr-1"></i> Khách đã trả xe ({{ $countReturned }})
            </a>
        </li>
        <li class="nav-item ml-2 mb-1">
            <a class="nav-link font-weight-bold {{ $status === 'cancelled' ? 'active bg-danger' : 'bg-white text-danger border' }}" 
               href="{{ route('partner.rentals', array_merge(request()->except('rental_status', 'page'), ['rental_status' => 'cancelled'])) }}">
                <i class="fa fa-times-circle mr-1"></i> Đã hủy ({{ $countCancelled }})
            </a>
        </li>
    </ul>

    <!-- BỘ LỌC TÌM KIẾM & THANH TOÁN -->
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="{{ route('partner.rentals') }}" method="GET" class="row align-items-center">
                <input type="hidden" name="rental_status" value="{{ $status }}">
                <div class="col-md-5 mb-2 mb-md-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control"
                               placeholder="Tìm theo mã hợp đồng #TULAI..., tên khách, SĐT, tên xe...">
                    </div>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <select name="payment_status" class="form-control form-control-sm">
                        <option value="">-- Tất cả thanh toán --</option>
                        <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                        <option value="deposit_paid" {{ request('payment_status') === 'deposit_paid' ? 'selected' : '' }}>Đã nộp cọc ký quỹ</option>
                        <option value="fully_paid" {{ request('payment_status') === 'fully_paid' ? 'selected' : '' }}>Đã thanh toán đủ</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold mr-1">
                        <i class="fa fa-filter"></i> Lọc dữ liệu
                    </button>
                    @if(request()->filled('keyword') || request()->filled('payment_status'))
                        <a href="{{ route('partner.rentals', ['rental_status' => $status]) }}" class="btn btn-outline-secondary btn-sm font-weight-bold">
                            <i class="fa fa-times"></i> Xóa lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- THANH CÔNG CỤ THAO TÁC HÀNG LOẠT (BULK ACTIONS BAR) -->
    <div class="card border-0 shadow-sm mb-3 bg-white">
        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center mb-2 mb-md-0 flex-wrap">
                <span class="badge badge-light border px-2 py-1 font-weight-bold text-dark mr-3 mb-1" style="font-size: 13px;">
                    <i class="fa fa-check-square text-primary mr-1"></i> Đã chọn: 
                    <span id="selectedCountBadge" class="text-danger font-weight-bold">0</span> hợp đồng
                </span>

                <span class="small font-weight-bold text-muted mr-2 mb-1">Đổi nhanh tiến độ:</span>

                <!-- Nút 1: Đã sẵn sàng xe -->
                <button type="button" class="btn btn-sm btn-outline-info font-weight-bold mr-2 mb-1 btn-bulk-action" data-status="confirmed" disabled>
                    <i class="fa fa-check-circle mr-1"></i> Sẵn sàng xe
                </button>

                <!-- Nút 2: Đang phục vụ / Khách đang đi -->
                <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold mr-2 mb-1 btn-bulk-action" data-status="in_progress" disabled>
                    <i class="fa fa-car mr-1"></i> Đang phục vụ
                </button>

                <!-- Nút 3: Khách đã trả xe -->
                <button type="button" class="btn btn-sm btn-outline-success font-weight-bold mr-2 mb-1 btn-bulk-action" data-status="returned" disabled>
                    <i class="fa fa-trophy mr-1"></i> Đã trả xe
                </button>

                <!-- Nút 4: Hủy đơn -->
                <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold mr-2 mb-1 btn-bulk-action" data-status="cancelled" disabled>
                    <i class="fa fa-ban mr-1"></i> Hủy đơn
                </button>

                <!-- Dropdown thao tác khác -->
                <div class="input-group input-group-sm d-inline-flex w-auto mb-1">
                    <select id="bulkSelectAction" class="custom-select custom-select-sm" style="min-width: 170px;">
                        <option value="">-- Chọn tiến độ khác --</option>
                        <option value="pending">⏳ Chờ đối chiếu / duyệt</option>
                        <option value="confirmed">✓ Đã sẵn sàng xe</option>
                        <option value="in_progress">🚗 Đang phục vụ / Đang đi</option>
                        <option value="returned">🏆 Khách đã trả xe</option>
                        <option value="cancelled">✗ Hủy đơn</option>
                    </select>
                    <div class="input-group-append">
                        <button type="button" id="btnBulkApply" class="btn btn-secondary font-weight-bold" disabled>
                            Áp dụng
                        </button>
                    </div>
                </div>
            </div>

            <div class="text-muted small">
                <i class="fa fa-info-circle text-info mr-1"></i> Tích chọn các ô vuông để cập nhật tiến độ nhiều hợp đồng cùng lúc.
            </div>
        </div>
    </div>

    <!-- DANH SÁCH HỢP ĐỒNG THUÊ XE -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light font-weight-bold text-muted text-uppercase">
                        <tr>
                            <th class="text-center px-2" style="width: 40px;">
                                <input type="checkbox" id="selectAll" title="Chọn tất cả trên trang này" style="cursor: pointer; width: 16px; height: 16px;">
                            </th>
                            <th>Hợp đồng & Khách</th>
                            <th>Thông tin Xe & Lịch trình</th>
                            <th>Ký quỹ & Doanh thu</th>
                            <th>Trạng thái & Tiến độ</th>
                            <th>Xử lý biên bản & Hoàn cọc</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rentals as $rental)
                            <tr>
                                <td class="text-center px-2">
                                    <input type="checkbox" class="rental-checkbox" value="{{ $rental->id }}" style="cursor: pointer; width: 16px; height: 16px;">
                                </td>
                                <!-- Cột 1: Hợp đồng & Khách -->
                                <td style="min-width: 190px;">
                                    <div class="font-weight-bold text-primary" style="font-size: 14px;">
                                        #{{ $rental->rental_code }}</div>
                                    <div class="mt-1 font-weight-bold text-dark">{{ $rental->customer_name }}</div>
                                    <div><a href="tel:{{ $rental->customer_phone }}" class="text-success font-weight-bold"><i
                                                class="fa fa-phone"></i> {{ $rental->customer_phone }}</a></div>
                                    @if($rental->rental_type === 'with_driver')
                                        <span class="badge badge-primary px-2 py-1 mt-1"><i class="fa fa-id-badge"></i> Kèm tài xế
                                            riêng</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1 mt-1"><i class="fa fa-key"></i> Tự lái</span>
                                    @endif
                                    <div class="mt-2">
                                        <a href="{{ route('partner.rentals.voucher', $rental->id) }}" target="_blank"
                                            class="btn btn-sm btn-outline-info btn-block font-weight-bold"
                                            style="font-size: 11px;">
                                            <i class="fa fa-file-text-o mr-1"></i> Xem Phiếu Đơn & QR
                                        </a>
                                    </div>
                                </td>

                                <!-- Cột 2: Xe & Lịch trình -->
                                <td style="min-width: 220px;">
                                    <div class="font-weight-bold text-dark" style="font-size: 14px;">
                                        {{ $rental->product->name ?? 'Xe' }}</div>
                                    <div class="text-muted"><i class="fa fa-paint-brush"></i>
                                        {{ $rental->selected_color ?: 'Màu chuẩn' }}</div>
                                    <div class="text-danger font-weight-bold mt-1">
                                        <i class="fa fa-calendar"></i> {{ date('d/m/Y', strtotime($rental->start_date)) }} -
                                        {{ date('d/m/Y', strtotime($rental->end_date)) }}
                                        ({{ $rental->total_days }} ngày)
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="fa fa-map-marker text-danger"></i> {{ $rental->customer_address }}
                                    </div>
                                </td>

                                <!-- Cột 3: Ký quỹ & Doanh thu 90% (Phí sàn 10%) -->
                                <td style="min-width: 180px;">
                                    @php
                                        $feeTotal = $rental->total_rental_fee + $rental->total_driver_fee;
                                        $comm10 = round($feeTotal * 0.10);
                                        $pNet = max(0, $feeTotal - $comm10);
                                    @endphp
                                    <div class="p-2 bg-light rounded">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Tổng tiền thuê:</span>
                                            <strong class="text-dark">{{ number_format($feeTotal) }} đ</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Phí sàn (10%):</span>
                                            <strong class="text-danger font-weight-bold">-{{ number_format($comm10) }} đ</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1 border-top pt-1">
                                            <span class="text-muted">Nhận về (90%):</span>
                                            <strong class="text-success font-weight-bold">{{ number_format($pNet) }} đ</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1 border-top pt-1">
                                            <span class="text-muted">Cọc Ký quỹ:</span>
                                            <strong class="text-danger font-weight-bold">{{ number_format($rental->deposit_amount) }} đ</strong>
                                        </div>

                                        <div class="mt-2 text-center">
                                            @if($rental->partner_commission_status === 'paid')
                                                <span class="badge badge-success d-block mb-1" title="Mã GD: {{ $rental->partner_commission_proof }}">
                                                    <i class="fa fa-check-circle"></i> Đã nộp 10% hoa hồng sàn
                                                </span>
                                            @else
                                                <span class="badge badge-warning text-dark d-block mb-1" style="background-color: #ffeeba; border: 1px solid #f5c6cb;">
                                                    <i class="fa fa-clock-o text-danger"></i> Chưa nộp 10% hoa hồng
                                                </span>
                                                <a href="{{ route('partner.rentals.payCommissionMomo', ['rental' => $rental->id, 'type' => 'captureWallet']) }}" 
                                                   class="btn btn-sm btn-danger btn-block font-weight-bold text-white mb-1 py-1" 
                                                   style="font-size: 11px; background: #a50064; border-color: #a50064; border-radius: 4px;" 
                                                   title="Nộp ngay 10% hoa hồng sàn qua Cổng MoMo Test">
                                                    <i class="fa fa-bolt mr-1"></i> Nộp MoMo Test
                                                </a>
                                            @endif

                                            @if($rental->refund_status === 'refunded')
                                                <span class="badge badge-success d-block"><i class="fa fa-check-circle"></i> Sàn đã hoàn cọc cho khách</span>
                                            @elseif($rental->refund_status === 'waiting_admin')
                                                <span class="badge badge-warning text-dark font-weight-bold d-block" style="background-color: #ffeeba; border: 1px solid #f5c6cb;"><i class="fa fa-clock-o text-danger"></i> Chờ Admin chuyển cọc</span>
                                            @elseif($rental->payment_status === 'deposit_paid' || $rental->payment_status === 'fully_paid')
                                                <span class="badge badge-info d-block"><i class="fa fa-shield"></i> Sàn đang giữ Escrow</span>
                                            @else
                                                <span class="badge badge-secondary d-block">Chưa thanh toán cọc</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Cột 4: Đối chiếu bàn giao & Trạng thái -->
                                <td style="min-width: 210px;">
                                    <!-- ĐỐI CHIẾU MÃ BẢO MẬT NHẬN XE -->
                                    @if($rental->handover_status === 'verified' || in_array($rental->rental_status, ['in_progress', 'returned']))
                                        <div
                                            class="p-2 rounded bg-light border border-success mb-2 text-success font-weight-bold small">
                                            <i class="fa fa-check-circle mr-1"></i> ĐÃ ĐỐI CHIẾU MÃ BẢO MẬT
                                            <div class="text-muted font-weight-normal" style="font-size: 10px;">
                                                {{ $rental->handover_verified_at ? $rental->handover_verified_at->format('d/m/Y H:i') : 'Đã duyệt' }}
                                                @if($rental->handover_odo) | Odo: {{ number_format($rental->handover_odo) }}km
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($rental->payment_status === 'unpaid' || !$rental->handover_code)
                                        <button type="button"
                                            class="btn btn-sm btn-secondary btn-block font-weight-bold mb-1" disabled
                                            title="Khách chưa thanh toán tiền cọc hoặc cọc đang chờ Admin xác nhận. Mã đối chiếu chưa cấp.">
                                            <i class="fa fa-lock mr-1"></i> Khách chưa nộp cọc (Khóa)
                                        </button>
                                        <small class="text-danger d-block text-center font-weight-bold" style="font-size: 10px;">
                                            Chờ khách nộp cọc hoặc Admin xác nhận
                                        </small>
                                    @else
                                        <button type="button"
                                            class="btn btn-sm btn-success btn-block font-weight-bold mb-2 shadow-sm"
                                            data-toggle="modal" data-target="#verifyModal{{ $rental->id }}">
                                            <i class="fa fa-shield mr-1"></i> ĐỐI CHIẾU MÃ & GIAO XE
                                        </button>

                                        <!-- MODAL ĐỐI CHIẾU MÃ BẢO MẬT BÀN GIAO XE -->
                                        <div class="modal fade text-left" id="verifyModal{{ $rental->id }}" tabindex="-1"
                                            role="dialog">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-success text-white font-weight-bold">
                                                        <h6 class="modal-title font-weight-bold"><i class="fa fa-shield mr-1"></i>
                                                            ĐỐI CHIẾU MÃ BẢO MẬT BÀN GIAO XE #{{ $rental->rental_code }}</h6>
                                                        <button type="button" class="close text-white"
                                                            data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form action="{{ route('partner.rentals.verifyHandover', $rental->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="alert alert-info small mb-3">
                                                                <i class="fa fa-info-circle mr-1"></i> Khách hàng có <strong>Phiếu
                                                                    Đơn Thuê Xe Điện Tử</strong> chứa Mã bảo mật 6 số. Vui lòng yêu
                                                                cầu khách đọc hoặc xuất trình mã để đối chiếu trước khi bàn giao xe.
                                                            </div>

                                                            <div class="p-2 bg-light rounded mb-3 small">
                                                                <div><strong>Khách nhận xe:</strong> {{ $rental->customer_name }}
                                                                    ({{ $rental->customer_phone }})</div>
                                                                <div><strong>Xe bàn giao:</strong>
                                                                    {{ $rental->product->name ?? 'Xe' }}
                                                                    ({{ $rental->selected_color }})</div>
                                                                <div><strong>Thời gian thuê:</strong>
                                                                    {{ date('d/m/Y', strtotime($rental->start_date)) }} -
                                                                    {{ date('d/m/Y', strtotime($rental->end_date)) }}</div>
                                                            </div>

                                                            <div class="form-group text-center">
                                                                <label
                                                                    class="small font-weight-bold text-danger text-uppercase">Nhập
                                                                    Mã bảo mật đối chiếu (6 chữ số):</label>
                                                                <input type="text" name="handover_code"
                                                                    class="form-control text-center font-weight-bold text-primary"
                                                                    style="font-size: 24px; letter-spacing: 5px;"
                                                                    placeholder="VD: 849201" required maxlength="10">
                                                                <small class="text-muted">Hệ thống chỉ kích hoạt giao xe khi mã
                                                                    trùng khớp 100%.</small>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div class="form-group">
                                                                        <label class="small font-weight-bold">Số ODO lúc giao
                                                                            (km):</label>
                                                                        <input type="number" name="handover_odo"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="VD: 25400">
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="form-group">
                                                                        <label class="small font-weight-bold">Mức nhiên liệu
                                                                            (%):</label>
                                                                        <input type="number" name="handover_fuel" value="100"
                                                                            min="0" max="100" class="form-control form-control-sm">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group mb-0">
                                                                <label class="small font-weight-bold">Ghi chú kiểm tra ngoại thất &
                                                                    giấy tờ:</label>
                                                                <textarea name="handover_notes" class="form-control form-control-sm"
                                                                    rows="2"
                                                                    placeholder="VD: Xe không trầy xước, giao đủ 02 chìa khóa và giấy đăng kiểm..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary btn-sm"
                                                                data-dismiss="modal">Đóng</button>
                                                            <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                                                                <i class="fa fa-check mr-1"></i> XÁC NHẬN ĐỐI CHIẾU & TRAO XE
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- FORM CẬP NHẬT TRẠNG THÁI TIẾN ĐỘ -->
                                    <form action="{{ route('partner.rentals.updateStatus', $rental->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="form-group mb-1">
                                            <label class="small font-weight-bold text-muted mb-0">Tiến độ hợp đồng:</label>
                                            <select name="rental_status" class="form-control form-control-sm font-weight-bold">
                                                <option value="pending" {{ $rental->rental_status === 'pending' ? 'selected' : '' }}>Chờ đối chiếu / duyệt</option>
                                                <option value="confirmed" {{ $rental->rental_status === 'confirmed' ? 'selected' : '' }}>✓ Đã sẵn sàng xe</option>
                                                <option value="in_progress" {{ $rental->rental_status === 'in_progress' ? 'selected' : '' }}>🚗 Đang phục vụ / Đang đi</option>
                                                <option value="returned" {{ $rental->rental_status === 'returned' ? 'selected' : '' }}>🏆 Khách đã trả xe</option>
                                                <option value="cancelled" {{ $rental->rental_status === 'cancelled' ? 'selected' : '' }}>✗ Hủy đơn</option>
                                            </select>
                                        </div>

                                        @if($rental->rental_type === 'with_driver')
                                            <div class="form-group mb-1">
                                                <input type="text" name="driver_name" value="{{ $rental->driver_name }}"
                                                    class="form-control form-control-sm" placeholder="Tên tài xế...">
                                            </div>
                                            <div class="form-group mb-1">
                                                <input type="tel" name="driver_phone" value="{{ $rental->driver_phone }}"
                                                    class="form-control form-control-sm" placeholder="SĐT tài xế...">
                                            </div>
                                        @endif

                                        <button type="submit"
                                            class="btn btn-sm btn-outline-primary btn-block font-weight-bold mb-1">
                                            <i class="fa fa-refresh"></i> Lưu tiến độ
                                        </button>
                                    </form>
                                </td>

                                <!-- Cột 5: Xử lý Hoàn cọc Ký quỹ cho khách -->
                                <td style="min-width: 230px;">
                                    <div class="p-2 border rounded bg-white">
                                        <div class="font-weight-bold text-dark mb-1"><i
                                                class="fa fa-university text-primary"></i> Tài khoản hoàn cọc:</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            NH: <strong>{{ $rental->refund_bank_name ?: 'Chưa cung cấp' }}</strong><br>
                                            STK: <strong
                                                class="text-primary">{{ $rental->refund_account_number ?: 'Chưa có' }}</strong><br>
                                            Tên: <strong>{{ $rental->refund_account_holder ?: $rental->customer_name }}</strong>
                                        </div>

                                        @if($rental->refund_status === 'refunded')
                                            <div class="alert alert-success p-2 text-center small mb-0 mt-2 font-weight-bold">
                                                <i class="fa fa-check-circle text-success" style="font-size: 16px;"></i><br>
                                                <span>SÀN AUTOCAR ĐÃ HOÀN CỌC</span><br>
                                                <span class="text-dark" style="font-size: 12px;">Đã chuyển: <strong class="text-success">{{ number_format($rental->refund_amount) }} đ</strong></span><br>
                                                <span class="text-muted" style="font-size: 10px;">{{ $rental->refunded_at ? $rental->refunded_at->format('d/m/Y H:i') : '' }}</span>
                                            </div>
                                        @elseif($rental->refund_status === 'waiting_admin')
                                            <div class="alert alert-warning p-2 text-center small mb-2 mt-2 font-weight-bold border-warning">
                                                <i class="fa fa-clock-o text-danger" style="font-size: 16px;"></i><br>
                                                <span class="text-danger">ĐÃ NGHIỆM THU TRẢ XE</span><br>
                                                <span class="text-dark" style="font-size: 11px;">Đề xuất Admin hoàn cọc:</span><br>
                                                <strong class="text-danger" style="font-size: 14px;">{{ number_format($rental->refund_amount) }} đ</strong><br>
                                                <small class="text-muted font-weight-normal">Đang chờ Admin sàn chuyển tiền cho khách</small>
                                            </div>
                                            <button type="button" class="btn btn-xs btn-outline-secondary btn-block font-weight-bold"
                                                data-toggle="modal" data-target="#refundModal{{ $rental->id }}">
                                                <i class="fa fa-pencil mr-1"></i> Cập nhật biên bản trả xe
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-warning btn-block font-weight-bold mt-2 shadow-sm"
                                                data-toggle="modal" data-target="#refundModal{{ $rental->id }}">
                                                <i class="fa fa-clipboard mr-1"></i> Nghiệm thu xe & Đề xuất hoàn cọc
                                            </button>
                                        @endif

                                        <!-- MODAL BIÊN BẢN NGHIỆM THU TRẢ XE & ĐỀ XUẤT HOÀN CỌC -->
                                        <div class="modal fade text-left" id="refundModal{{ $rental->id }}" tabindex="-1"
                                            role="dialog">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-warning text-dark font-weight-bold">
                                                        <h6 class="modal-title font-weight-bold"><i
                                                                class="fa fa-clipboard mr-1"></i> BIÊN BẢN NGHIỆM THU TRẢ XE & ĐỀ XUẤT HOÀN CỌC
                                                            #{{ $rental->rental_code }}</h6>
                                                        <button type="button" class="close"
                                                            data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form action="{{ route('partner.rentals.refund', $rental->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="alert alert-info small mb-3">
                                                                <i class="fa fa-info-circle mr-1"></i> <strong>Showroom đối tác vui lòng kiểm tra xe thực tế:</strong> Chốt số km ODO, mức xăng và tình trạng ngoại thất. Sau khi bạn xác nhận, mẫu xe sẽ được <strong>đưa về kho sẵn sàng cho thuê lại</strong> và hệ thống sẽ gửi đề xuất để <strong>Ban Quản Trị (Admin) sàn thực hiện chuyển khoản hoàn cọc</strong> cho khách.
                                                            </div>

                                                            <div class="p-3 bg-light rounded mb-3 small">
                                                                <div><strong>Khách hàng:</strong> {{ $rental->customer_name }}
                                                                    ({{ $rental->customer_phone }})</div>
                                                                <div><strong>Ngân hàng nhận hoàn:</strong>
                                                                    {{ $rental->refund_bank_name ?: 'Chưa cung cấp' }} - STK:
                                                                    <strong class="text-primary">{{ $rental->refund_account_number ?: 'Chưa có' }}</strong></div>
                                                                <div><strong>Chủ tài khoản:</strong> {{ $rental->refund_account_holder ?: $rental->customer_name }}</div>
                                                                <div class="mt-1"><strong>Tiền cọc gốc đã nộp Sàn:</strong> <span
                                                                        class="text-danger font-weight-bold">{{ number_format($rental->deposit_amount) }}
                                                                        VNĐ</span></div>
                                                            </div>

                                                            @php
                                                                $totalRentalFee = (float) ($rental->total_rental_fee + $rental->total_driver_fee);
                                                                $commission10 = round($totalRentalFee * 0.10);
                                                                $partnerNet90 = max(0, $totalRentalFee - $commission10);
                                                                $adminBankAcc = config('services.sepay.bank_acc', '12325072005');
                                                                $adminBankName = config('services.sepay.bank_name', 'TPBank');
                                                                $adminAccName = config('services.sepay.acc_name', 'HOANG NGOC THI');
                                                                $qrInfo = 'HH10 ' . $rental->rental_code;
                                                                $sepayQrUrl = "https://qr.sepay.vn/img?acc={$adminBankAcc}&bank={$adminBankName}&amount={$commission10}&des=" . urlencode($qrInfo) . "&template=compact";
                                                                $isCommissionPaid = ($rental->partner_commission_status === 'paid');
                                                            @endphp

                                                            @if($isCommissionPaid)
                                                                <!-- ĐÃ NỘP 10% HOA HỒNG SÀN THÀNH CÔNG -->
                                                                <div class="card border-success mb-3 shadow-sm" style="border: 2px solid #28a745 !important; background-color: #f4fbf6;">
                                                                    <div class="card-header bg-success text-white font-weight-bold py-2 d-flex justify-content-between align-items-center">
                                                                        <span><i class="fa fa-check-circle mr-1"></i> 10% HOA HỒNG SÀN: ĐÃ NỘP THÀNH CÔNG</span>
                                                                        <span class="badge badge-light text-success px-2 py-1 font-weight-bold" style="font-size: 13px;">{{ number_format($rental->partner_commission_fee ?: $commission10) }} VNĐ</span>
                                                                    </div>
                                                                    <div class="card-body p-3 small">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <div>Trạng thái: <strong class="text-success"><i class="fa fa-check"></i> Đã thanh toán cho Ban Quản Trị Sàn</strong></div>
                                                                                <div>Mã chứng từ / Giao dịch: <code class="text-primary font-weight-bold">{{ $rental->partner_commission_proof }}</code></div>
                                                                                @if($rental->partner_commission_paid_at)
                                                                                    <div>Thời điểm thanh toán: <strong>{{ \Carbon\Carbon::parse($rental->partner_commission_paid_at)->format('H:i d/m/Y') }}</strong></div>
                                                                                @endif
                                                                            </div>
                                                                            <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 13px;"><i class="fa fa-shield"></i> ĐÃ HOÀN TẤT</span>
                                                                        </div>
                                                                        <input type="hidden" name="confirm_commission_paid" value="1">
                                                                        <input type="hidden" name="commission_payment_proof" value="{{ $rental->partner_commission_proof }}">
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <!-- CHƯA NỘP: HIỂN THỊ 2 PHƯƠNG THỨC THANH TOÁN (MOMO TEST & NGÂN HÀNG ADMIN TPBANK) -->
                                                                <div class="card border-warning mb-3 shadow-sm" style="border: 2px solid #ffc107 !important; background-color: #fffdf5;">
                                                                    <div class="card-header bg-warning text-dark font-weight-bold py-2 d-flex justify-content-between align-items-center">
                                                                        <span><i class="fa fa-university mr-1"></i> BẮT BUỘC: NỘP 10% HOA HỒNG SÀN</span>
                                                                        <span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 13px;">{{ number_format($commission10) }} VNĐ</span>
                                                                    </div>
                                                                    <div class="card-body p-3">
                                                                        <div class="alert alert-warning p-2 small mb-3 border-warning text-dark">
                                                                            <i class="fa fa-info-circle text-danger mr-1"></i>
                                                                            <strong>Quy chế vận hành:</strong> Đối tác phải chuyển đúng <strong>10% số tiền cho thuê xe ({{ number_format($commission10) }} đ)</strong> cho Ban Quản Trị (Admin) sàn AutoCar mới được phép hoàn tất nghiệm thu và gửi lệnh hoàn cọc cho khách.
                                                                        </div>

                                                                        <!-- 2 LỰA CHỌN THANH TOÁN -->
                                                                        <ul class="nav nav-pills nav-fill mb-3" id="commPayTab{{ $rental->id }}" role="tablist">
                                                                            <li class="nav-item">
                                                                                <a class="nav-link active font-weight-bold py-2 small" id="momo-tab{{ $rental->id }}" data-toggle="pill" href="#momoPay{{ $rental->id }}" role="tab">
                                                                                    <i class="fa fa-bolt text-danger mr-1"></i> Cổng MoMo Test (Khuyên dùng)
                                                                                </a>
                                                                            </li>
                                                                            <li class="nav-item">
                                                                                <a class="nav-link font-weight-bold py-2 small" id="bank-tab{{ $rental->id }}" data-toggle="pill" href="#bankPay{{ $rental->id }}" role="tab">
                                                                                    <i class="fa fa-university text-primary mr-1"></i> Chuyển khoản Ngân hàng Admin
                                                                                </a>
                                                                            </li>
                                                                        </ul>

                                                                        <div class="tab-content" id="commPayTabContent{{ $rental->id }}">
                                                                            <!-- TAB 1: THANH TOÁN QUA CỔNG MOMO TEST -->
                                                                            <div class="tab-pane fade show active" id="momoPay{{ $rental->id }}" role="tabpanel">
                                                                                <div class="p-3 bg-white rounded border border-danger mb-2">
                                                                                    <div class="d-flex align-items-center mb-2">
                                                                                        <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo" style="height: 28px;" class="mr-2">
                                                                                        <div>
                                                                                            <strong class="text-dark">Thanh toán 10% hoa hồng qua Cổng MoMo Test</strong>
                                                                                            <span class="badge badge-danger ml-2">Tự động 100%</span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <p class="text-muted small mb-3">
                                                                                        Bấm nút thanh toán bên dưới để chuyển sang Cổng MoMo Sandbox (Quét mã Ví MoMo Test hoặc nhập Thẻ ATM nội địa Napas Test). Sau khi hoàn tất, hệ thống tự động ghi nhận hoa hồng sàn ngay lập tức.
                                                                                    </p>
                                                                                    <div class="d-flex flex-wrap">
                                                                                        <a href="{{ route('partner.rentals.payCommissionMomo', ['rental' => $rental->id, 'type' => 'captureWallet']) }}" 
                                                                                           class="btn btn-sm font-weight-bold text-white shadow-sm mr-2 mb-1" 
                                                                                           style="background: #a50064; border-color: #a50064; border-radius: 6px; padding: 8px 14px;">
                                                                                            <i class="fa fa-qrcode mr-1"></i> Quét mã Ví MoMo Test ({{ number_format($commission10) }}đ)
                                                                                        </a>
                                                                                        <a href="{{ route('partner.rentals.payCommissionMomo', ['rental' => $rental->id, 'type' => 'payWithATM']) }}" 
                                                                                           class="btn btn-sm btn-outline-danger font-weight-bold shadow-sm mb-1" 
                                                                                           style="border-radius: 6px; padding: 8px 14px;">
                                                                                            <i class="fa fa-credit-card mr-1"></i> Thẻ ATM Nội Địa Test (MoMo)
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <!-- TAB 2: CHUYỂN KHOẢN NGÂN HÀNG ADMIN (TPBANK) -->
                                                                            <div class="tab-pane fade" id="bankPay{{ $rental->id }}" role="tabpanel">
                                                                                <div class="p-3 bg-white rounded border mb-2">
                                                                                    <div class="row align-items-center mb-3">
                                                                                        <div class="col-sm-5 text-center mb-2 mb-sm-0">
                                                                                            <img src="{{ $sepayQrUrl }}" alt="SePay TPBank 10% hoa hồng" class="img-fluid rounded border shadow-sm" style="max-height: 180px;">
                                                                                            <small class="text-muted d-block mt-1 font-weight-bold"><i class="fa fa-qrcode"></i> Quét mã QR chuyển nhanh</small>
                                                                                        </div>
                                                                                        <div class="col-sm-7 small">
                                                                                            <div class="p-2 bg-light rounded border">
                                                                                                <div class="text-muted small mb-1"><i class="fa fa-university text-primary mr-1"></i> Tài khoản Admin nhận tiền của Sàn AutoCar:</div>
                                                                                                <div>Ngân hàng: <strong class="text-dark">TPBank (Ngân hàng Tiên Phong)</strong></div>
                                                                                                <div class="d-flex align-items-center my-1">
                                                                                                    <span>Số tài khoản: </span>
                                                                                                    <code class="text-primary font-weight-bold ml-1" style="font-size: 15px;">{{ $adminBankAcc }}</code>
                                                                                                </div>
                                                                                                <div>Chủ tài khoản: <strong class="text-dark font-weight-bold">{{ $adminAccName }}</strong></div>
                                                                                                <div>Số tiền (10%): <strong class="text-danger font-weight-bold" style="font-size: 14px;">{{ number_format($commission10) }} VNĐ</strong></div>
                                                                                                <div class="mt-1">Cú pháp CK: <code class="text-dark bg-white px-1 border rounded font-weight-bold">{{ $qrInfo }}</code></div>
                                                                                            </div>
                                                                                            <div class="mt-2 text-muted" style="font-size: 11px;">
                                                                                                (Tổng tiền thuê xe: {{ number_format($totalRentalFee) }}đ &bull; Showroom giữ 90%: {{ number_format($partnerNet90) }}đ)
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="form-group mb-2">
                                                                                        <label class="font-weight-bold small text-danger text-uppercase">
                                                                                            <i class="fa fa-barcode mr-1"></i> Mã giao dịch ngân hàng đã chuyển 10% hoa hồng:
                                                                                        </label>
                                                                                        <input type="text" name="commission_payment_proof" class="form-control form-control-sm font-weight-bold text-primary" 
                                                                                               placeholder="VD: FT2609218849 hoặc mã giao dịch ngân hàng TPBank" 
                                                                                               value="{{ old('commission_payment_proof', $rental->partner_commission_proof) }}">
                                                                                        <small class="text-muted">Nhập mã giao dịch để Ban Quản Trị đối soát và giải ngân hoàn cọc cho khách.</small>
                                                                                    </div>

                                                                                    <div class="custom-control custom-checkbox mt-2">
                                                                                        <input type="checkbox" class="custom-control-input" id="confirmComm{{ $rental->id }}" name="confirm_commission_paid" value="1">
                                                                                        <label class="custom-control-label small font-weight-bold text-dark" for="confirmComm{{ $rental->id }}">
                                                                                            Tôi xác nhận đã chuyển đủ {{ number_format($commission10) }}đ (10% hoa hồng) vào tài khoản Admin TPBank.
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div class="form-group">
                                                                        <label class="small font-weight-bold">Số ODO lúc trả
                                                                            (km):</label>
                                                                        <input type="number" name="return_odo"
                                                                            value="{{ $rental->return_odo }}"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="VD: 25680">
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="form-group">
                                                                        <label class="small font-weight-bold">Mức xăng lúc trả
                                                                            (%):</label>
                                                                        <input type="number" name="return_fuel"
                                                                            value="{{ $rental->return_fuel ?: 100 }}" min="0"
                                                                            max="100" class="form-control form-control-sm">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="small font-weight-bold text-success">Số tiền đề xuất Admin chuyển hoàn cho khách (VNĐ):</label>
                                                                <input type="number" name="refund_amount"
                                                                    value="{{ $rental->refund_amount > 0 ? $rental->refund_amount : $rental->deposit_amount }}"
                                                                    class="form-control font-weight-bold text-success" required>
                                                                <small class="text-muted">Mặc định hoàn 100% cọc gốc nếu xe nguyên vẹn.</small>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="small font-weight-bold text-warning">Giữ lại đối soát phạt
                                                                    nguội (VNĐ, nếu có):</label>
                                                                <input type="number" name="refund_holding_fee" value="{{ $rental->refund_holding_fee ?: 0 }}"
                                                                    class="form-control font-weight-bold text-warning">
                                                                <small class="text-muted">Khoản giữ đối chiếu camera giao thông (nếu thỏa thuận với khách).</small>
                                                            </div>

                                                            <div class="form-group mb-0">
                                                                <label class="small font-weight-bold">Ghi chú biên bản kiểm tra ngoại thất &
                                                                    hiện trạng:</label>
                                                                <textarea name="refund_notes"
                                                                    class="form-control form-control-sm" rows="2"
                                                                    placeholder="VD: Xe nguyên vẹn không trầy xước, xăng đầy đủ, đề xuất hoàn cọc 100% cho khách..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary btn-sm"
                                                                data-dismiss="modal">Đóng</button>
                                                            @if($isCommissionPaid)
                                                                <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                                                                    <i class="fa fa-check-circle mr-1"></i> HOÀN TẤT BIÊN BẢN NGHIỆM THU & GỬI YÊU CẦU HOÀN CỌC
                                                                </button>
                                                            @else
                                                                <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                                                                    <i class="fa fa-paper-plane mr-1"></i> XÁC NHẬN ĐÃ CHUYỂN KHOẢN NGÂN HÀNG & NGHIỆM THU
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có hợp đồng thuê xe nào.</td>
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

    <!-- FORM ẨN ĐỂ SUBMIT BULK ACTION (TRÁNH LỖI NESTED FORM) -->
    <form id="formBulkStatus" action="{{ route('partner.rentals.bulkStatus') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="rental_status" id="inputBulkRentalStatus" value="">
        <div id="hiddenRentalIdsContainer"></div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var selectAll = document.getElementById('selectAll');
        var checkboxes = document.querySelectorAll('.rental-checkbox');
        var selectedCountBadge = document.getElementById('selectedCountBadge');
        var bulkButtons = document.querySelectorAll('.btn-bulk-action');
        var btnBulkApply = document.getElementById('btnBulkApply');
        var bulkSelectAction = document.getElementById('bulkSelectAction');

        var formBulkStatus = document.getElementById('formBulkStatus');
        var inputBulkRentalStatus = document.getElementById('inputBulkRentalStatus');
        var hiddenRentalIdsContainer = document.getElementById('hiddenRentalIdsContainer');

        var statusNames = {
            'pending': 'Chờ đối chiếu / duyệt',
            'confirmed': 'Đã sẵn sàng xe',
            'in_progress': 'Đang phục vụ / Khách đang đi',
            'returned': 'Khách đã trả xe',
            'cancelled': 'Hủy đơn'
        };

        function updateBulkUI() {
            var checkedBoxes = document.querySelectorAll('.rental-checkbox:checked');
            var count = checkedBoxes.length;

            selectedCountBadge.innerText = count;

            var isEnabled = (count > 0);
            bulkButtons.forEach(function(btn) {
                btn.disabled = !isEnabled;
                var st = btn.getAttribute('data-status');
                var title = '';
                if (st === 'confirmed') title = 'Sẵn sàng xe';
                else if (st === 'in_progress') title = 'Đang phục vụ';
                else if (st === 'returned') title = 'Đã trả xe';
                else if (st === 'cancelled') title = 'Hủy đơn';

                if (count > 0) {
                    btn.innerHTML = '<i class="fa fa-check mr-1"></i> ' + title + ' (' + count + ')';
                } else {
                    btn.innerHTML = title;
                }
            });

            if (btnBulkApply) {
                btnBulkApply.disabled = !isEnabled;
            }

            if (selectAll && checkboxes.length > 0) {
                selectAll.checked = (checkedBoxes.length === checkboxes.length);
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                var isChecked = this.checked;
                checkboxes.forEach(function(cb) {
                    cb.checked = isChecked;
                });
                updateBulkUI();
            });
        }

        checkboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                updateBulkUI();
            });
        });

        function submitBulk(newStatus) {
            var checkedBoxes = document.querySelectorAll('.rental-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Vui lòng tích chọn ít nhất 1 hợp đồng để thực hiện.');
                return;
            }

            var label = statusNames[newStatus] || newStatus;
            if (!confirm('Bạn có chắc chắn muốn chuyển ' + checkedBoxes.length + ' hợp đồng đã chọn sang tiến độ: ' + label + '?')) {
                return;
            }

            hiddenRentalIdsContainer.innerHTML = '';
            checkedBoxes.forEach(function(cb) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'rental_ids[]';
                input.value = cb.value;
                hiddenRentalIdsContainer.appendChild(input);
            });

            inputBulkRentalStatus.value = newStatus;
            formBulkStatus.submit();
        }

        bulkButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var st = this.getAttribute('data-status');
                submitBulk(st);
            });
        });

        if (btnBulkApply) {
            btnBulkApply.addEventListener('click', function() {
                var st = bulkSelectAction.value;
                if (!st) {
                    alert('Vui lòng chọn một trạng thái tiến độ từ danh sách.');
                    return;
                }
                submitBulk(st);
            });
        }
    });
    </script>
@endsection