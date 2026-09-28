@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="font-weight-bold text-uppercase mb-0" style="border-left: 4px solid #f59e0b; padding-left: 12px;">
            <i class="fa fa-file-text-o text-warning"></i> Chi tiết đơn hàng #{{ $order->order_code }}
        </h2>
        <a class="btn btn-secondary font-weight-bold" href="{{ route('admin.orders.index') }}">
            <i class="fa fa-arrow-left"></i> Quay lại danh sách đơn
        </a>
    </div>

    <div class="row">
        <!-- Cột trái: Thông tin khách hàng & Chi tiết xe -->
        <div class="col-lg-8 mb-4">
            <!-- Thông tin khách hàng & Địa chỉ GPS -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white py-3">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-user text-primary mr-1"></i> Thông tin khách hàng & Địa chỉ nhận xe
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small text-uppercase font-weight-bold">Họ và tên khách:</span>
                            <div class="font-weight-bold text-dark" style="font-size: 16px;">{{ $order->customer_name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small text-uppercase font-weight-bold">Số điện thoại:</span>
                            <div class="font-weight-bold text-dark" style="font-size: 16px;">
                                <a href="tel:{{ $order->customer_phone }}"><i class="fa fa-phone"></i> {{ $order->customer_phone }}</a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small text-uppercase font-weight-bold">Email:</span>
                            <div class="text-muted">{{ $order->customer_email ?: 'Không cung cấp' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small text-uppercase font-weight-bold">Tài khoản đặt hàng:</span>
                            <div class="text-dark">{{ $order->user ? $order->user->name . ' (' . $order->user->email . ')' : 'Khách vãng lai' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small text-uppercase font-weight-bold">Hình thức nhận xe:</span>
                            <div class="mt-1">
                                @if($order->delivery_type === 'garage_delivery')
                                    <span class="badge badge-info text-white px-2 py-1" style="font-size: 13px;">
                                        <i class="fa fa-truck mr-1"></i> Gara mang xe qua tận nơi
                                    </span>
                                    <span class="text-primary font-weight-bold ml-1">(Cước bàn giao: +{{ number_format($order->ghn_total_fee) }} đ)</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1" style="font-size: 13px;">
                                        <i class="fa fa-building mr-1"></i> Nhận trực tiếp tại Showroom (0 đ)
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <span class="text-muted small text-uppercase font-weight-bold">Mã điều phối / Vận đơn:</span>
                            <div class="mt-1">
                                @if($order->ghn_order_code)
                                    <span class="badge badge-danger text-white px-2 py-1" style="font-size: 13px;">
                                        {{ $order->ghn_order_code }}
                                    </span>
                                @else
                                    <span class="text-muted font-italic">Chưa khởi tạo mã điều phối</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <span class="text-muted small text-uppercase font-weight-bold">Địa chỉ nhận xe chi tiết:</span>
                            <div class="font-weight-bold text-dark bg-light p-3 rounded mt-1 border">
                                <i class="fa fa-map-marker text-danger mr-1"></i> {{ $order->customer_address }}
                            </div>
                        </div>
                        @if($order->latitude && $order->longitude)
                            <div class="col-md-12 mb-3">
                                <span class="text-muted small text-uppercase font-weight-bold">Tọa độ định vị GPS:</span>
                                <div class="mt-1">
                                    <span class="badge badge-danger px-3 py-2" style="font-size: 13px;">
                                        <i class="fa fa-crosshairs"></i> Vĩ độ: {{ $order->latitude }}, Kinh độ: {{ $order->longitude }}
                                    </span>
                                    <a href="https://www.google.com/maps?q={{ $order->latitude }},{{ $order->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary ml-2 font-weight-bold">
                                        <i class="fa fa-external-link"></i> Mở trên Google Maps
                                    </a>
                                </div>
                            </div>
                        @endif
                        @if($order->note)
                            <div class="col-md-12">
                                <span class="text-muted small text-uppercase font-weight-bold">Ghi chú từ khách hàng:</span>
                                <div class="font-italic text-muted mt-1 bg-light p-2 rounded">{{ $order->note }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bảng danh sách xe đặt mua -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px; overflow: hidden;">
                <div class="card-header bg-white py-3">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-car text-primary mr-1"></i> Danh sách xe trong đơn hàng
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped m-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Hình ảnh</th>
                                    <th>Tên mẫu xe</th>
                                    <th>Màu sắc</th>
                                    <th>Phân loại</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td class="align-middle">
                                        @if($item->image)
                                            <img src="{{ asset('images/' . $item->image) }}" class="rounded shadow-sm" style="width: 60px; height: 45px; object-fit: cover;">
                                        @else
                                            <span class="badge badge-secondary">Không có ảnh</span>
                                        @endif
                                    </td>
                                    <td class="align-middle font-weight-bold text-dark">{{ $item->product_name }}</td>
                                    <td class="align-middle">
                                        @if($item->color)
                                            <span class="badge badge-light border text-dark">
                                                <i class="fa fa-paint-brush text-primary"></i> {{ $item->color }}
                                            </span>
                                        @else
                                            <span class="text-muted">Mặc định</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-muted">{{ $item->category_name ?: 'N/A' }}</td>
                                    <td class="text-center align-middle font-weight-bold">x{{ $item->quantity }}</td>
                                    <td class="text-center align-middle">{{ number_format($item->price) }} đ</td>
                                    <td class="text-center align-middle font-weight-bold text-danger">{{ number_format($item->price * $item->quantity) }} đ</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                @if($order->ghn_total_fee > 0)
                                    <tr>
                                        <td colspan="6" class="text-right text-muted font-weight-bold">Cước bàn giao xe tận nơi (Gara):</td>
                                        <td class="text-center font-weight-bold text-primary">
                                            + {{ number_format($order->ghn_total_fee) }} VNĐ
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="6" class="text-right text-muted font-weight-bold">Cước bàn giao xe:</td>
                                        <td class="text-center font-weight-bold text-success">
                                            Miễn phí (Nhận tại Showroom)
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="6" class="text-right font-weight-bold text-dark" style="font-size: 16px;">TỔNG CỘNG THANH TOÁN:</td>
                                    <td class="text-center font-weight-bold text-danger" style="font-size: 18px;">
                                        {{ number_format($order->total_amount) }} VNĐ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Bảng Nhật ký giao dịch tài chính (Payment Transactions) từ tài liệu hướng dẫn -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px; overflow: hidden;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-credit-card text-success mr-1"></i> Nhật ký giao dịch tài chính (Payment Transactions)
                    </h5>
                    <span class="badge badge-light border text-muted">
                        {{ $order->paymentTransactions->count() }} lần thử thanh toán
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($order->paymentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped m-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Thời gian</th>
                                        <th>Cổng thanh toán</th>
                                        <th>Mã GD Cổng (Trans ID)</th>
                                        <th class="text-center">Số tiền</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th>Thông điệp / Mã lỗi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->paymentTransactions as $pt)
                                    <tr>
                                        <td class="small text-muted align-middle">{{ $pt->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td class="align-middle">
                                            @if($pt->gateway === 'momo')
                                                <span class="badge" style="background-color: #a50064; color: #fff;">
                                                    <i class="fa fa-qrcode mr-1"></i> MoMo
                                                </span>
                                            @elseif($pt->gateway === 'bank_transfer')
                                                <span class="badge badge-info">VietQR</span>
                                            @else
                                                <span class="badge badge-secondary">COD</span>
                                            @endif
                                        </td>
                                        <td class="align-middle font-weight-bold text-dark small">
                                            {{ $pt->transaction_id ?: ($pt->gateway_order_id ?: 'N/A') }}
                                        </td>
                                        <td class="text-center align-middle font-weight-bold text-danger">
                                            {{ number_format($pt->amount) }} đ
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($pt->status === 'paid')
                                                <span class="badge badge-success px-2 py-1"><i class="fa fa-check"></i> Thành công</span>
                                            @elseif($pt->status === 'failed')
                                                <span class="badge badge-danger px-2 py-1"><i class="fa fa-times"></i> Thất bại</span>
                                            @else
                                                <span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-clock-o"></i> Đang chờ</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted align-middle">
                                            {{ $pt->message ?: 'N/A' }}
                                            @if(!is_null($pt->result_code))
                                                <span class="badge badge-light border ml-1">Code: {{ $pt->result_code }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-3 text-center text-muted">
                            <em>Chưa có nhật ký giao dịch nào cho đơn hàng này.</em>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cột phải: Cập nhật trạng thái đơn hàng -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 p-4 sticky-top" style="top: 90px; border-radius: 10px;">
                <h5 class="font-weight-bold text-dark border-bottom pb-3 mb-3">
                    <i class="fa fa-cogs text-primary mr-1"></i> Xử lý đơn hàng
                </h5>

                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <!-- Trạng thái đơn hàng -->
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">Trạng thái đơn hàng:</label>
                        <select name="order_status" class="form-control font-weight-bold">
                            <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>⏳ Chờ xử lý</option>
                            <option value="confirmed" {{ $order->order_status === 'confirmed' ? 'selected' : '' }}>✅ Đã xác nhận cọc / đơn</option>
                            <option value="shipping" {{ $order->order_status === 'shipping' ? 'selected' : '' }}>🚚 Đang giao xe cho khách</option>
                            <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>🎉 Đã hoàn tất giao xe</option>
                            <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>❌ Đã hủy đơn</option>
                        </select>
                    </div>

                    <!-- Trạng thái thanh toán -->
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark">Trạng thái thanh toán:</label>
                        <select name="payment_status" class="form-control font-weight-bold">
                            <option value="unpaid" {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>🔴 Chưa thanh toán</option>
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>🟢 Đã thanh toán</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm">
                        <i class="fa fa-save mr-1"></i> Cập nhật trạng thái
                    </button>
                </form>

                <hr>

                <!-- Tóm tắt thanh toán -->
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Phương thức thanh toán:</span>
                        <strong class="text-dark">{{ $order->payment_method === 'bank_transfer' ? 'Chuyển khoản VietQR' : 'Showroom / COD' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ngày đặt:</span>
                        <strong class="text-dark">{{ $order->created_at->format('d/m/Y H:i:s') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Cập nhật lần cuối:</span>
                        <strong class="text-dark">{{ $order->updated_at->format('d/m/Y H:i:s') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
