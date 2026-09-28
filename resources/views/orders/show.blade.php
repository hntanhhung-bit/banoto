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
                <p class="mb-1"><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <p class="mb-1"><strong>Trạng thái đơn:</strong> 
                    <span class="badge badge-info">{{ $order->order_status ?? $order->status ?? 'pending' }}</span>
                </p>
                <p class="mb-1"><strong>Trạng thái vận chuyển GHN:</strong> 
                    <span class="badge badge-primary">{{ $order->shipping_status ?? 'Chưa giao' }}</span>
                </p>
                @if($order->ghn_order_code)
                    <p class="mb-1"><strong>Mã vận đơn GHN:</strong> <code class="font-weight-bold text-danger">{{ $order->ghn_order_code }}</code></p>
                @endif
                <p class="mb-1"><strong>Phí giao hàng GHN:</strong> {{ number_format($order->ghn_total_fee ?? 0) }} đ</p>
                <p class="mb-1"><strong>Tổng thanh toán:</strong> <span class="font-weight-bold text-danger" style="font-size: 18px;">{{ number_format($order->total_amount ?? $order->total_price ?? 0) }} đ</span></p>
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
