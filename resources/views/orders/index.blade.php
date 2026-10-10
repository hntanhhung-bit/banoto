@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white shadow-sm">
            <li class="breadcrumb-item"><a href="{{ route('welcome') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Lịch sử đơn hàng của tôi</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="font-weight-bold text-dark mb-0">
            <i class="fa fa-history text-primary mr-1"></i> LỊCH SỬ ĐẶT MUA XE CỦA BẠN
        </h3>
        <a href="{{ route('welcome') }}" class="btn btn-outline-primary font-weight-bold">
            <i class="fa fa-car mr-1"></i> Xem thêm xe khác
        </a>
    </div>

    @if($orders->count() > 0)
        <div class="card shadow-sm border-0" style="border-radius: 10px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover table-striped m-0 align-middle">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Mẫu xe đã đặt</th>
                            <th class="text-center">Hình thức nhận</th>
                            <th class="text-center">Tổng thanh toán</th>
                            <th class="text-center">Thanh toán</th>
                            <th class="text-center">Trạng thái xe</th>
                            <th class="text-center" style="min-width: 170px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td class="font-weight-bold text-primary align-middle">
                                <div>#{{ $order->order_code }}</div>
                                @if($order->partner)
                                    <span class="badge badge-success text-white mt-1" style="font-size: 10px;">
                                        <i class="fa fa-handshake-o"></i> Chốt từ Showroom
                                    </span>
                                @endif
                                @if($order->ghn_order_code)
                                    <small class="badge badge-danger text-white mt-1 d-block">
                                        <i class="fa fa-truck"></i> {{ $order->ghn_order_code }}
                                    </small>
                                @endif
                            </td>
                            <td class="align-middle text-muted" style="font-size: 13px;">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="align-middle">
                                @foreach($order->items as $item)
                                    <div class="mb-1 font-weight-bold text-dark">
                                        <i class="fa fa-car text-primary mr-1"></i> {{ $item->product_name }} (x{{ $item->quantity }})
                                        @if(!empty($item->color))
                                            <span class="badge badge-light border text-dark ml-1" style="font-size: 11px;">
                                                <i class="fa fa-paint-brush text-primary"></i> {{ $item->color }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                                @if($order->partner)
                                    <div class="small text-muted mt-1">
                                        <i class="fa fa-building-o text-success mr-1"></i> Showroom: 
                                        <strong class="text-dark">{{ $order->partner->partner_showroom_name ?: ($order->partner->showroom_name ?: $order->partner->name) }}</strong>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                @if($order->delivery_type === 'garage_delivery')
                                    <span class="badge badge-info text-white p-1">
                                        <i class="fa fa-truck"></i> Gara giao tận nơi
                                    </span>
                                    <div class="small text-muted mt-1">+{{ number_format($order->ghn_total_fee) }} đ cước</div>
                                @else
                                    <span class="badge badge-light border text-dark p-1">
                                        <i class="fa fa-building text-primary"></i> Nhận tại Showroom
                                    </span>
                                    <div class="small text-success mt-1">Miễn phí cước</div>
                                @endif
                            </td>
                            <td class="text-center align-middle font-weight-bold text-danger" style="font-size: 15px;">
                                {{ number_format($order->total_amount) }} đ
                            </td>
                            <td class="text-center align-middle">
                                <div class="mb-1">
                                    @if($order->payment_method === 'momo')
                                        <span class="badge" style="background-color: #a50064; color: #fff; font-weight: bold;">
                                            <i class="fa fa-credit-card mr-1"></i> Ví MoMo
                                        </span>
                                    @elseif($order->payment_method === 'bank_transfer')
                                        <span class="badge badge-info">Chuyển khoản QR</span>
                                    @elseif($order->payment_method === 'showroom')
                                        <span class="badge badge-warning text-dark">Tiền mặt / Showroom</span>
                                    @else
                                        <span class="badge badge-secondary">COD / Showroom</span>
                                    @endif
                                </div>
                                @if($order->payment_status === 'paid')
                                    <span class="badge badge-success px-2 py-1">
                                        <i class="fa fa-check-circle"></i> Đã thanh toán
                                    </span>
                                @else
                                    <span class="badge badge-warning text-dark px-2 py-1">
                                        <i class="fa fa-clock-o"></i> Chưa thanh toán
                                    </span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                @if($order->order_status === 'completed')
                                    <span class="badge badge-success px-2 py-1">Đã bàn giao</span>
                                @elseif($order->order_status === 'confirmed')
                                    <span class="badge badge-primary px-2 py-1">Đã xác nhận đơn</span>
                                @elseif($order->order_status === 'shipping')
                                    <span class="badge badge-info px-2 py-1">Đang giao xe</span>
                                @elseif($order->order_status === 'cancelled')
                                    <span class="badge badge-danger px-2 py-1">Đã hủy</span>
                                @else
                                    <span class="badge badge-warning px-2 py-1 text-dark">Chờ tiếp nhận</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary font-weight-bold mb-1">
                                    <i class="fa fa-eye"></i> Xem
                                </a>
                                @if($order->payment_status !== 'paid' && $order->order_status !== 'cancelled')
                                    @if($order->payment_method === 'sepay')
                                        <a href="{{ route('orders.sepay.pay', $order->id) }}" class="btn btn-sm btn-primary font-weight-bold mb-1 shadow-sm" title="Quét mã QR SePay để thanh toán">
                                            <i class="fa fa-qrcode mr-1"></i> Quét QR
                                        </a>
                                    @else
                                        <a href="{{ route('orders.momo.pay', $order->id) }}" class="btn btn-sm btn-danger font-weight-bold mb-1 shadow-sm" style="background-color: #a50064; border-color: #a50064;" title="Thanh toán lại cho đơn hàng này qua MoMo">
                                            <i class="fa fa-refresh mr-1"></i> Thanh toán lại
                                        </a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links('pagination::bootstrap-4') }}
            </div>
        @endif
    @else
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="fa fa-folder-open-o text-muted mb-3" style="font-size: 60px; opacity: 0.3;"></i>
                <h4 class="font-weight-bold text-dark">Bạn chưa có đơn đặt mua xe nào.</h4>
                <p class="text-muted mb-4">Hãy khám phá các dòng xe hấp dẫn và đặt mua chiếc xe yêu thích của bạn!</p>
                <a href="{{ route('welcome') }}" class="btn btn-primary px-5 py-2 font-weight-bold" style="border-radius: 30px;">
                    <i class="fa fa-car mr-1"></i> Khám phá xe ngay
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
