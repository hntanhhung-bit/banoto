@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5">
    <h1 class="font-weight-bold text-dark mb-4">Giỏ hàng của bạn</h1>

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

    @if(count($cart) > 0)
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-bordered table-hover m-0 align-middle">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th>Tên sản phẩm</th>
                            <th class="text-center" style="width: 200px;">Số lượng</th>
                            <th class="text-center">Giá</th>
                            <th class="text-center">Thành tiền</th>
                            <th class="text-center">Danh mục</th>
                            <th class="text-center" style="width: 120px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach($cart as $id => $details)
                            @php $total += $details['price'] * $details['quantity']; @endphp
                            <tr>
                                <!-- Tên sản phẩm & Hình ảnh -->
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(!empty($details['image']))
                                            <img src="{{ asset('images/'.$details['image']) }}" alt="{{ $details['name'] }}" class="rounded shadow-sm mr-3" style="width: 75px; height: 55px; object-fit: cover;">
                                        @endif
                                        <div>
                                            <a href="{{ route('products.show', $id) }}" class="font-weight-bold text-dark text-decoration-none" style="font-size: 15px;">
                                                {{ $details['name'] }}
                                            </a>
                                            @if(!empty($details['color']))
                                                <div class="small text-muted mt-1">
                                                    <i class="fa fa-paint-brush text-primary"></i> Màu sắc: <strong class="text-dark">{{ $details['color'] }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Cập nhật số lượng -->
                                <td class="text-center align-middle">
                                    @php
                                        $productModel = \App\Models\Product::find($id);
                                        $stock = $productModel ? $productModel->quantity : 0;
                                    @endphp
                                    <form action="{{ route('cart.update', $id) }}" method="POST" style="display: inline-flex; align-items: center; flex-direction: column;">
                                        @csrf
                                        @method('PATCH')
                                        <div class="d-flex align-items-center">
                                            <input type="number" name="quantity" value="{{ $details['quantity'] }}" min="1" max="{{ $stock }}" class="form-control form-control-sm text-center mr-2 font-weight-bold" style="width: 70px;" required>
                                            <button type="submit" class="btn btn-sm btn-secondary font-weight-bold">
                                                Cập nhật
                                            </button>
                                        </div>
                                        <small class="mt-1 font-weight-bold {{ $stock > 0 ? 'text-success' : 'text-danger' }}">
                                            <i class="fa fa-cubes"></i> Kho: {{ $stock }} xe
                                        </small>
                                    </form>
                                </td>

                                <!-- Giá -->
                                <td class="text-center align-middle font-weight-bold text-dark">
                                    {{ number_format($details['price']) }} đ
                                </td>

                                <!-- Thành tiền -->
                                <td class="text-center align-middle font-weight-bold text-danger" style="font-size: 16px;">
                                    {{ number_format($details['price'] * $details['quantity']) }} đ
                                </td>

                                <!-- Danh mục -->
                                <td class="text-center align-middle">
                                    <span class="badge badge-info px-2 py-1">{{ is_array($details['category']) ? ($details['category']['name'] ?? 'N/A') : ($details['category'] ?? 'N/A') }}</span>
                                </td>

                                <!-- Nút Xoá -->
                                <td class="text-center align-middle">
                                    <form action="{{ route('cart.remove', $id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xoá sản phẩm này khỏi giỏ hàng?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger font-weight-bold">
                                            <i class="fa fa-trash"></i> Xoá
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tổng tiền & Nút Đặt hàng -->
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 p-3 bg-white rounded shadow-sm">
            <a href="{{ route('welcome') }}" class="btn btn-outline-secondary font-weight-bold px-4 py-2" style="border-radius: 6px;">
                <i class="fa fa-arrow-left mr-1"></i> Tiếp tục mua sắm
            </a>
            <div class="d-flex align-items-center mt-2 mt-sm-0">
                <h4 class="font-weight-bold text-dark mb-0 mr-3">
                    Tổng tiền: <span class="text-danger">{{ number_format($total) }} VNĐ</span>
                </h4>
                <a href="{{ route('payment.index') }}" class="btn btn-danger font-weight-bold px-4 py-2 shadow-sm" style="border-radius: 6px; font-size: 16px;">
                    <i class="fa fa-credit-card mr-1"></i> Tiến hành Đặt hàng & Thanh toán &rarr;
                </a>
            </div>
        </div>
    @else
        <div class="alert alert-light border py-5 text-center mb-4 shadow-sm" style="border-radius: 10px;">
            <i class="fa fa-shopping-cart text-muted mb-3" style="font-size: 50px; opacity: 0.3;"></i>
            <p class="text-muted mb-3" style="font-size: 16px;">Giỏ hàng của bạn đang trống.</p>
            <a href="{{ route('welcome') }}" class="btn btn-primary font-weight-bold px-4 py-2" style="border-radius: 6px;">
                <i class="fa fa-car mr-1"></i> Tiếp tục xem xe
            </a>
        </div>
    @endif
</div>
@endsection
