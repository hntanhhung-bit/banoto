@extends('partner.layout')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-calendar-check-o text-success mr-2"></i> TIẾP NHẬN LỊCH HẸN XEM XE & LÁI THỬ
            </h3>
            <p class="text-muted small mb-0">Khách hàng đặt lịch xem xe qua Sàn. Đối tác phối hợp đón tiếp khách và cử
                chuyên viên tư vấn.</p>
        </div>
    </div>

    <!-- BỘ LỌC TRẠNG THÁI -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('partner.appointments') }}" method="GET" class="form-inline">
                <label class="mr-2 small font-weight-bold text-muted">Lọc theo trạng thái:</label>
                <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ tiếp nhận</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
                @if(request('status'))
                    <a href="{{ route('partner.appointments') }}" class="btn btn-sm btn-outline-secondary">Xóa bộ lọc</a>
                @endif
            </form>
        </div>
    </div>

    <!-- DANH SÁCH LỊCH HẸN -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light small font-weight-bold text-muted text-uppercase">
                        <tr>
                            <th>Mã lịch hẹn</th>
                            <th>Thông tin khách</th>
                            <th>Xe & Màu sắc</th>
                            <th>Thời gian & Địa điểm</th>
                            <th>Trạng thái</th>
                            <th>Ghi chú & Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($appointments as $app)
                            <tr>
                                <td>
                                    <strong class="text-primary font-weight-bold">#{{ $app->appointment_code }}</strong>
                                    <div class="text-muted">{{ $app->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $app->customer_name }}
                                    </div>
                                    <div><a href="tel:{{ $app->customer_phone }}" class="text-success font-weight-bold"><i
                                                class="fa fa-phone"></i> {{ $app->customer_phone }}</a></div>
                                    <div class="text-muted">{{ $app->customer_email ?: 'Không có email' }}</div>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $app->product->name ?? 'Xe' }}</div>
                                    <span class="badge badge-dark px-2 py-1"><i class="fa fa-paint-brush"></i>
                                        {{ $app->selected_color ?: 'Màu cơ bản' }}</span>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-danger">
                                        <i class="fa fa-calendar"></i> {{ date('d/m/Y', strtotime($app->appointment_date)) }} -
                                        {{ $app->appointment_time }}
                                    </div>
                                    <div class="text-muted mt-1" style="max-width: 250px;">
                                        <i class="fa fa-map-marker text-danger"></i> {{ $app->address }}
                                    </div>
                                </td>
                                <td>
                                    @if($app->status === 'pending')
                                        <span class="badge badge-warning px-2 py-1 font-weight-bold">Chờ tiếp nhận</span>
                                    @elseif($app->status === 'confirmed')
                                        <span class="badge badge-primary px-2 py-1 font-weight-bold">Đã xác nhận đón khách</span>
                                    @elseif($app->status === 'completed')
                                        <span class="badge badge-info px-2 py-1 font-weight-bold">Hoàn thành xem xe</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1 font-weight-bold">Đã hủy</span>
                                    @endif

                                    <!-- KẾT QUẢ MUA BÁN XE -->
                                    <div class="mt-2 pt-2 border-top">
                                        <span class="text-muted small font-weight-bold d-block">Kết quả bán xe:</span>
                                        @if($app->deal_status === 'deal_won')
                                            <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                                <i class="fa fa-trophy"></i> ĐÃ MUA THÀNH CÔNG
                                            </span>
                                            <div class="small text-dark mt-1 font-weight-bold">Giá bán:
                                                {{ number_format($app->deal_price ?: ($app->product?->price ?: 0)) }} đ</div>
                                            <div class="small text-danger font-weight-bold">Hoa hồng Sàn (1%):
                                                {{ number_format($app->commission_amount) }} đ</div>
                                        @elseif($app->deal_status === 'deal_lost')
                                            <span class="badge badge-secondary px-2 py-1">Khách chưa mua</span>
                                        @else
                                            <span class="badge badge-light border text-primary px-2 py-1">Đang tư vấn đàm
                                                phán</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="min-width: 230px;">
                                    <form action="{{ route('partner.appointments.updateStatus', $app->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="form-group mb-1">
                                            <label class="small font-weight-bold text-muted mb-0">Tiến độ tiếp đón:</label>
                                            <select name="status" class="form-control form-control-sm font-weight-bold">
                                                <option value="pending" {{ $app->status === 'pending' ? 'selected' : '' }}>Chờ
                                                    tiếp nhận</option>
                                                <option value="confirmed" {{ $app->status === 'confirmed' ? 'selected' : '' }}>✓
                                                    Xác nhận tiếp đón</option>
                                                <option value="completed" {{ $app->status === 'completed' ? 'selected' : '' }}>Đã
                                                    xem & lái thử xong</option>
                                                <option value="cancelled" {{ $app->status === 'cancelled' ? 'selected' : '' }}>✗
                                                    Khách hủy hẹn</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-1">
                                            <label class="small font-weight-bold text-success mb-0"><i
                                                    class="fa fa-handshake-o"></i> Chốt bán xe:</label>
                                            <select name="deal_status"
                                                class="form-control form-control-sm font-weight-bold text-success border-success"
                                                onchange="var p = this.form.querySelector('.deal-price-box'); if(this.value==='deal_won'){p.style.display='block';}else{p.style.display='none';}">
                                                <option value="negotiating" {{ $app->deal_status === 'negotiating' ? 'selected' : '' }}>Đang tư vấn đàm phán</option>
                                                <option value="deal_won" {{ $app->deal_status === 'deal_won' ? 'selected' : '' }}>
                                                    🏆 KHÁCH ĐÃ MUA XE THÀNH CÔNG</option>
                                                <option value="deal_lost" {{ $app->deal_status === 'deal_lost' ? 'selected' : '' }}>Khách chưa mua / Hủy ý định</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-1 deal-price-box"
                                            style="display: {{ $app->deal_status === 'deal_won' ? 'block' : 'none' }};">
                                            <label class="small font-weight-bold text-muted mb-0">Giá bán thực tế (VNĐ):</label>
                                            <input type="number" name="deal_price"
                                                value="{{ $app->deal_price ?: ($app->product?->price ?: 500000000) }}"
                                                class="form-control form-control-sm font-weight-bold"
                                                placeholder="VD: 650000000">
                                            <small class="text-muted" style="font-size: 10px;">Sàn tự tính hoa hồng 1%</small>
                                        </div>

                                        <div class="form-group mb-1">
                                            <input type="text" name="admin_note" value="{{ $app->admin_note }}"
                                                class="form-control form-control-sm"
                                                placeholder="Ghi chú phản hồi của Showroom...">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-success btn-block font-weight-bold">
                                            <i class="fa fa-save"></i> Cập nhật kết quả
                                        </button>
                                    </form>
                                    @if($app->note)
                                        <div class="mt-1 small text-muted font-italic" style="font-size: 11px;">
                                            <strong>Yêu cầu của khách:</strong> {{ $app->note }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Không có lịch hẹn xem xe nào phù hợp.</td>
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
@endsection