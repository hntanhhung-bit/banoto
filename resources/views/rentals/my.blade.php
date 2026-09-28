@extends('layouts.app')

@section('content')
<div class="container my-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-key text-danger mr-2"></i> Đơn thuê xe đặt hộ & Bảo lãnh cọc của tôi
            </h3>
            <p class="text-muted small mb-0">Nền tảng Bên thứ ba bảo lãnh tiền cọc, điều phối với Nhà xe đối tác và đảm bảo quyền lợi khách thuê</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('welcome', ['service' => 'rent_self']) }}" class="btn btn-outline-danger font-weight-bold mr-2">
                <i class="fa fa-key mr-1"></i> Thuê tự lái
            </a>
            <a href="{{ route('welcome', ['service' => 'rent_driver']) }}" class="btn btn-outline-primary font-weight-bold">
                <i class="fa fa-user-circle mr-1"></i> Thuê tài xế
            </a>
        </div>
    </div>

    @if($rentals->count() > 0)
        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase text-secondary font-weight-bold" style="font-size: 13px;">
                        <tr>
                            <th class="py-3 px-4">Mã đơn & Loại hình</th>
                            <th class="py-3">Mẫu xe & Nhà xe</th>
                            <th class="py-3">Thời gian thuê</th>
                            <th class="py-3">Địa điểm / Lộ trình</th>
                            <th class="py-3">Tổng chi phí & Bảo lãnh cọc</th>
                            <th class="py-3 text-center">Tiến độ điều phối</th>
                            <th class="py-3 text-right px-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rentals as $rental)
                            @php
                                $isDriver = ($rental->rental_type === 'with_driver');
                            @endphp
                            <tr>
                                <td class="px-4 font-weight-bold">
                                    <span class="text-primary d-block font-weight-bold" style="font-size: 14px;">#{{ $rental->rental_code }}</span>
                                    @if($isDriver)
                                        <span class="badge badge-primary px-2 py-1 mt-1"><i class="fa fa-user-circle"></i> Có tài xế riêng</span>
                                    @else
                                        <span class="badge badge-danger px-2 py-1 mt-1"><i class="fa fa-key"></i> Thuê tự lái</span>
                                    @endif
                                    <span class="badge badge-light border text-danger d-block small font-weight-normal mt-1"><i class="fa fa-shield"></i> Bên thứ 3 bảo lãnh</span>
                                    <div class="small text-muted font-weight-normal mt-1">{{ $rental->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $rental->product ? $rental->product->image_url : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=100&auto=format&fit=crop&q=60' }}" 
                                             class="rounded mr-3" style="width: 60px; height: 45px; object-fit: cover;" 
                                             alt="{{ $rental->product->name ?? 'Xe' }}"
                                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=100&auto=format&fit=crop&q=60';">
                                        <div>
                                            <strong class="text-dark d-block">
                                                <a href="{{ route('products.show', $rental->product_id) }}" class="text-dark text-decoration-none">
                                                    {{ $rental->product->name ?? 'Xe đã gỡ' }}
                                                </a>
                                            </strong>
                                            <span class="badge badge-secondary px-2 py-0 small"><i class="fa fa-paint-brush"></i> Màu: {{ $rental->selected_color ?: 'Trắng ngọc trai' }}</span>
                                            @if($rental->product && $rental->product->partner_showroom)
                                                <small class="text-danger font-weight-bold d-block mt-1">
                                                    <i class="fa fa-building"></i> {{ $rental->product->partner_showroom->name }}
                                                </small>
                                            @endif
                                            @if($isDriver && $rental->driver_name)
                                                <small class="text-success font-weight-bold d-block">
                                                    <i class="fa fa-id-card-o"></i> TX: {{ $rental->driver_name }} ({{ $rental->driver_phone }})
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><strong>Từ:</strong> {{ date('d/m/Y', strtotime($rental->start_date)) }}</div>
                                    <div><strong>Đến:</strong> {{ date('d/m/Y', strtotime($rental->end_date)) }}</div>
                                    <small class="badge badge-light border text-muted">Tổng: {{ $rental->total_days }} ngày</small>
                                </td>
                                <td>
                                    <div class="small mb-1">
                                        <strong>Điểm đón:</strong> {{ $rental->customer_address }}
                                    </div>
                                    @if($isDriver && $rental->destination_address)
                                        <div class="small text-muted">
                                            <strong>Lộ trình:</strong> {{ $rental->destination_address }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-weight-bold text-danger" style="font-size: 15px;">
                                        {{ number_format($rental->total_amount) }} VNĐ
                                    </div>
                                    <small class="text-muted d-block">Tiền cọc: {{ number_format($rental->deposit_amount) }} VNĐ</small>
                                    @if($rental->refund_status === 'refunded')
                                        <span class="badge badge-success px-2 py-1 mt-1"><i class="fa fa-check-circle"></i> Đã hoàn cọc: {{ number_format($rental->refund_amount) }}đ</span>
                                    @elseif($rental->refund_status === 'waiting_admin')
                                        <span class="badge badge-warning text-dark px-2 py-1 mt-1 font-weight-bold" style="background-color: #ffeeba;"><i class="fa fa-clock-o text-danger"></i> Đã trả xe - Chờ Admin hoàn cọc</span>
                                    @elseif($rental->payment_status === 'deposit_paid' || $rental->payment_status === 'fully_paid')
                                        <span class="badge badge-success px-2 py-1 mt-1"><i class="fa fa-shield"></i> Cọc được Ký quỹ an toàn</span>
                                    @else
                                        <span class="badge badge-warning px-2 py-1 mt-1"><i class="fa fa-clock-o"></i> Chưa thanh toán cọc</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($rental->rental_status === 'pending')
                                        <span class="badge badge-warning px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                            <i class="fa fa-hourglass-half"></i> Chờ điều phối xe
                                        </span>
                                        <div class="small text-muted mt-1">Bên thứ 3 liên hệ nhà xe</div>
                                    @elseif($rental->rental_status === 'confirmed')
                                        <span class="badge badge-info px-3 py-2 font-weight-bold text-white" style="font-size: 12px;">
                                            <i class="fa fa-check"></i> Nhà xe đã nhận đơn
                                        </span>
                                        <div class="small text-info mt-1 font-weight-bold">Sắp giao nhận xe</div>
                                    @elseif($rental->rental_status === 'in_progress')
                                        <span class="badge badge-primary px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                            <i class="fa fa-road"></i> Đang phục vụ
                                        </span>
                                    @elseif($rental->rental_status === 'returned')
                                        @if($rental->refund_status === 'refunded')
                                            <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                                <i class="fa fa-flag-checkered"></i> Đã hoàn tất & Trả cọc
                                            </span>
                                        @else
                                            <span class="badge badge-info px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                                <i class="fa fa-check-circle"></i> Đã trả xe - Chờ hoàn cọc
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                            <i class="fa fa-times"></i> Đã hủy
                                        </span>
                                    @endif

                                    @if($rental->admin_note)
                                        <div class="small text-info mt-1 font-italic">
                                            <i class="fa fa-commenting-o"></i> {{ $rental->admin_note }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right px-4">
                                    <div class="mb-2 text-center p-1 bg-light rounded border">
                                        <span class="text-muted d-block" style="font-size: 11px;"><i class="fa fa-shield text-warning"></i> Mã nhận xe:</span>
                                        @if($rental->handover_code)
                                            <strong class="text-success font-weight-bold" style="font-size: 14px; letter-spacing: 2px; font-family: monospace;">
                                                {{ $rental->handover_code }}
                                            </strong>
                                        @else
                                            <span class="badge badge-secondary font-weight-normal" style="font-size: 10px;">Chờ nộp cọc</span>
                                        @endif
                                    </div>

                                    <a href="{{ route('rentals.voucher', $rental->id) }}" target="_blank" class="btn btn-warning btn-sm font-weight-bold mb-1 d-block text-dark shadow-sm">
                                        <i class="fa fa-file-text-o mr-1"></i> Phiếu Nhận Xe & QR
                                    </a>

                                    <a href="{{ route('rentals.success', $rental->rental_code) }}" class="btn btn-outline-info btn-sm font-weight-bold mb-1 d-block" title="Xem chi tiết & thanh toán cọc">
                                        <i class="fa fa-credit-card"></i> Chi tiết / Đặt cọc
                                    </a>

                                    @if($rental->payment_status !== 'deposit_paid' && $rental->rental_status !== 'cancelled')
                                        @if($rental->payment_method === 'sepay')
                                            <a href="{{ route('rentals.sepay.pay', $rental->id) }}" class="btn btn-primary btn-sm font-weight-bold mb-1 d-block">
                                                <i class="fa fa-qrcode"></i> Quét QR Cọc
                                            </a>
                                        @elseif($rental->payment_method === 'momo')
                                            <a href="{{ route('rentals.momo.pay', ['rental' => $rental->id, 'type' => 'payWithATM']) }}" class="btn btn-danger btn-sm font-weight-bold mb-1 d-block" style="background-color: #a50064; border-color: #a50064;">
                                                <i class="fa fa-credit-card"></i> Cọc Thẻ ATM MoMo
                                            </a>
                                        @endif
                                    @endif

                                    @if($rental->rental_status === 'pending')
                                        <form action="{{ route('rentals.cancel', $rental->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn thuê xe này không?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold btn-block">
                                                <i class="fa fa-times"></i> Hủy đơn
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($rentals->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $rentals->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-5 bg-white rounded shadow-sm border">
            <i class="fa fa-key text-muted fa-4x mb-3"></i>
            <h5 class="text-secondary font-weight-bold">Bạn chưa có đơn thuê xe hoặc thuê tài xế nào</h5>
            <p class="text-muted small">Hãy lựa chọn mẫu xe ưng ý để phục vụ cho các chuyến đi công tác, du lịch hay gia đình.</p>
            <a href="{{ route('welcome', ['service' => 'rent_self']) }}" class="btn btn-danger px-4 font-weight-bold mr-2">
                <i class="fa fa-key mr-1"></i> Thuê xe tự lái
            </a>
            <a href="{{ route('welcome', ['service' => 'rent_driver']) }}" class="btn btn-primary px-4 font-weight-bold">
                <i class="fa fa-user-circle mr-1"></i> Thuê có tài xế
            </a>
        </div>
    @endif
</div>
@endsection
