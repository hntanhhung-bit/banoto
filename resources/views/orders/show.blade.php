@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white shadow-sm">
            <li class="breadcrumb-item"><a href="{{ route('welcome') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.my') }}">Lịch sử đơn hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết đơn #{{ $order->order_code ?? $order->id }}</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 12px;">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3 flex-wrap">
            <h4 class="font-weight-bold text-dark mb-0">
                <i class="fa fa-file-text-o text-primary mr-2"></i> Chi tiết đơn hàng #{{ $order->order_code ?? $order->id }}
            </h4>
            <div class="mt-2 mt-sm-0">
                @if(in_array($order->shipping_status ?? 'pending', ['pending', 'ready_to_pick', 'not_shipped']) && ($order->order_status ?? '') !== 'cancelled')
                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger font-weight-bold">
                            <i class="fa fa-times-circle mr-1"></i> Hủy đơn hàng
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <p class="mb-1"><strong>Người nhận:</strong> {{ $order->customer_name ?? $order->name }}</p>
                <p class="mb-1"><strong>Số điện thoại:</strong> {{ $order->customer_phone ?? $order->phone }}</p>
                <p class="mb-1"><strong>Địa chỉ:</strong> {{ $order->customer_address ?? $order->address }}</p>
                <p class="mb-1"><strong>Ngày thực hiện / Ngày mua:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                @if($order->partner)
                    <div class="mt-2 p-2 bg-light rounded border border-success">
                        <strong class="text-success"><i class="fa fa-building mr-1"></i> Showroom Đối tác bán xe:</strong>
                        <div class="font-weight-bold text-dark">{{ $order->partner->partner_showroom_name ?: ($order->partner->showroom_name ?: $order->partner->name) }}</div>
                        <div class="small text-muted">{{ $order->partner->partner_showroom_address ?: ($order->partner->showroom_address ?: 'Tại Showroom đối tác') }}</div>
                        @if($order->appointment)
                            <div class="small text-primary mt-1">Lịch hẹn: #{{ $order->appointment->appointment_code }}</div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="col-md-6 mb-3">
                <p class="mb-2"><strong>Trạng thái đơn:</strong> 
                    @if($order->order_status === 'confirmed')
                        <span class="badge badge-primary px-2 py-1">✓ Đã xác nhận đơn mua xe</span>
                    @elseif($order->order_status === 'completed')
                        <span class="badge badge-success px-2 py-1">★ Đã hoàn tất bàn giao</span>
                    @elseif($order->order_status === 'shipping')
                        <span class="badge badge-info px-2 py-1">🚚 Đang giao xe</span>
                    @elseif($order->order_status === 'cancelled')
                        <span class="badge badge-danger px-2 py-1">✕ Đã hủy</span>
                    @else
                        <span class="badge badge-warning text-dark px-2 py-1">⏳ Chờ xử lý</span>
                    @endif
                </p>
                <p class="mb-2"><strong>Thanh toán:</strong>
                    @if($order->payment_status === 'paid')
                        <span class="badge badge-success px-2 py-1"><i class="fa fa-check"></i> Đã thanh toán</span>
                    @else
                        <span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-clock-o"></i> Chưa thanh toán</span>
                    @endif
                    <span class="ml-1">
                        @if($order->payment_method === 'momo')
                            <span class="badge" style="background-color: #a50064; color: #fff;">Ví MoMo</span>
                        @elseif($order->payment_method === 'bank_transfer')
                            <span class="badge badge-info">Chuyển khoản VietQR</span>
                        @elseif($order->payment_method === 'showroom')
                            <span class="badge badge-warning text-dark">Tiền mặt / Showroom</span>
                        @else
                            <span class="badge badge-secondary">COD / Showroom</span>
                        @endif
                    </span>
                </p>
                @if($order->ghn_order_code)
                    <p class="mb-1"><strong>Mã vận đơn GHN:</strong> <code class="font-weight-bold text-danger">{{ $order->ghn_order_code }}</code></p>
                @endif
                @if($order->ghn_total_fee > 0)
                    <p class="mb-1"><strong>Phí giao hàng:</strong> {{ number_format($order->ghn_total_fee) }} đ</p>
                @endif
                <p class="mb-1"><strong>Tổng giá trị xe đã chốt:</strong> <span class="font-weight-bold text-danger" style="font-size: 18px;">{{ number_format($order->total_amount ?? $order->total_price ?? 0) }} đ</span></p>
            </div>
        </div>

        <h5 class="font-weight-bold text-dark mt-3 mb-3">Danh sách sản phẩm</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="text-center">Số lượng</th>
                        <th class="text-right">Đơn giá</th>
                        <th class="text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name ?? ($item->product->name ?? 'Sản phẩm') }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ number_format($item->price) }} đ</td>
                            <td class="text-right font-weight-bold">{{ number_format($item->price * $item->quantity) }} đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
