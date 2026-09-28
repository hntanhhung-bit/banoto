@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Thẻ thông báo thành công -->
            <div class="card border-0 shadow-sm p-4 text-center mb-4" style="border-radius: 12px; background: #ffffff;">
                <div class="mb-3">
                    <i class="fa fa-check-circle text-success" style="font-size: 75px;"></i>
                </div>
                <h3 class="font-weight-bold text-dark mb-2">ĐẶT MUA XE THÀNH CÔNG!</h3>
                <p class="text-muted mb-3" style="font-size: 16px;">
                    Cảm ơn bạn đã tin tưởng lựa chọn dịch vụ của <strong>OTO.COM.VN</strong>. Mã đơn hàng của bạn là:
                </p>
                <div class="d-inline-block bg-light py-2 px-4 rounded border mb-2 font-weight-bold text-primary mr-2" style="font-size: 18px; letter-spacing: 1px;">
                    Mã đơn hàng: #{{ $order->order_code }}
                </div>
                @if($order->ghn_order_code)
                    <div class="d-inline-block bg-danger text-white py-2 px-4 rounded shadow-sm mb-2 font-weight-bold" style="font-size: 18px; letter-spacing: 1px;">
                        <i class="fa fa-truck mr-1"></i> Mã vận đơn GHN: {{ $order->ghn_order_code }}
                    </div>
                @endif
                <p class="small text-muted mb-0">Thời gian tạo: {{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <!-- Nếu chọn Ví MoMo -->
            @if($order->payment_method === 'momo')
                <div class="card border-0 shadow-sm p-4 mb-4 text-center" style="border-radius: 12px; border-left: 5px solid #a50064 !important; background: #fff5f8;">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <span class="badge px-3 py-2 mr-2" style="background-color: #a50064; color: #fff; font-size: 16px;">
                            <i class="fa fa-credit-card mr-1"></i> MoMo ATM Payment
                        </span>
                        @if($order->payment_status === 'paid')
                            <span class="badge badge-success px-3 py-2" style="font-size: 15px;">
                                <i class="fa fa-check-circle mr-1"></i> Đã thanh toán thành công
                            </span>
                        @else
                            <span class="badge badge-warning text-dark px-3 py-2" style="font-size: 15px;">
                                <i class="fa fa-clock-o mr-1"></i> Đang chờ thanh toán
                            </span>
                        @endif
                    </div>
                    @if($order->payment_status === 'paid')
                        <p class="text-success font-weight-bold mb-1" style="font-size: 16px;">
                            Giao dịch thanh toán qua MoMo đã được xác nhận thành công!
                        </p>
                        <p class="small text-muted mb-0">
                            Hệ thống đã tự động chuyển giao thông tin sang bộ phận kỹ thuật để chuẩn bị và rửa xe trước khi bàn giao.
                        </p>
                    @else
                        <p class="text-danger font-weight-bold mb-2">
                            Đơn hàng chưa hoàn tất thanh toán trên cổng MoMo.
                        </p>
                        <a href="{{ route('orders.momo.pay', $order->id) }}" class="btn btn-danger font-weight-bold px-4 py-2 shadow-sm" style="background-color: #a50064; border-color: #a50064;">
                            <i class="fa fa-refresh mr-1"></i> Bấm vào đây để thanh toán lại qua MoMo
                        </a>
                    @endif
                </div>
            @endif

            <!-- Nếu chọn SePay QR -->
            @if($order->payment_method === 'sepay')
                <div class="card border-0 shadow-sm p-4 mb-4 text-center" style="border-radius: 12px; border-left: 5px solid #005fb7 !important; background: #f0f7ff;">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <span class="badge badge-primary px-3 py-2 mr-2" style="font-size: 16px;">
                            <i class="fa fa-qrcode mr-1"></i> SePay QR Payment
                        </span>
                        @if($order->payment_status === 'paid')
                            <span class="badge badge-success px-3 py-2" style="font-size: 15px;">
                                <i class="fa fa-check-circle mr-1"></i> Đã thanh toán thành công
                            </span>
                        @else
                            <span class="badge badge-warning text-dark px-3 py-2" style="font-size: 15px;">
                                <i class="fa fa-clock-o mr-1"></i> Đang chờ thanh toán
                            </span>
                        @endif
                    </div>
                    @if($order->payment_status === 'paid')
                        <p class="text-success font-weight-bold mb-1" style="font-size: 16px;">
                            Giao dịch thanh toán qua SePay (TPBank) đã được xác nhận thành công!
                        </p>
                        <p class="small text-muted mb-0">
                            Hệ thống đã tự động chuyển giao thông tin sang bộ phận kỹ thuật để chuẩn bị và bàn giao xe.
                        </p>
                    @else
                        <p class="text-danger font-weight-bold mb-2">
                            Đơn hàng chưa hoàn tất thanh toán chuyển khoản qua SePay.
                        </p>
                        <a href="{{ route('orders.sepay.pay', $order->id) }}" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm">
                            <i class="fa fa-qrcode mr-1"></i> Mở lại trang quét mã QR SePay
                        </a>
                    @endif
                </div>
            @endif

            <!-- Nếu chọn Chuyển khoản ngân hàng: Hiển thị mã QR thanh toán VietQR -->
            @if($order->payment_method === 'bank_transfer')
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 12px; border-left: 5px solid #005fb7 !important;">
                    <div class="row align-items-center">
                        <div class="col-md-5 text-center mb-4 mb-md-0 border-md-right">
                            <h6 class="font-weight-bold text-primary text-uppercase mb-3">
                                <i class="fa fa-qrcode mr-1"></i> Quét mã VietQR để thanh toán
                            </h6>
                            @if($qrUrl)
                                <img src="{{ $qrUrl }}" alt="VietQR" class="img-fluid rounded shadow-sm border p-2 bg-white" style="max-width: 230px;">
                            @endif
                            <p class="small text-muted mt-2 mb-0">Mở app Ngân hàng và quét mã để thanh toán tự động</p>
                        </div>
                        <div class="col-md-7 pl-md-4">
                            <h5 class="font-weight-bold text-dark mb-3">Thông tin tài khoản thụ hưởng:</h5>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                     <span class="text-muted">Ngân hàng:</span>
                                     <strong class="text-dark">TPBank (Ngân hàng Tiên Phong)</strong>
                                 </li>
                                 <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                     <span class="text-muted">Số tài khoản:</span>
                                     <strong class="text-primary font-weight-bold" style="font-size: 18px; letter-spacing: 1px;">1232 5072 005</strong>
                                 </li>
                                 <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                     <span class="text-muted">Chủ tài khoản:</span>
                                     <strong class="text-dark">HOANG NGOC THI</strong>
                                 </li>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                    <span class="text-muted">Số tiền:</span>
                                    <strong class="text-danger font-weight-bold" style="font-size: 18px;">{{ number_format($order->total_amount) }} VNĐ</strong>
                                </li>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                    <span class="text-muted">Nội dung chuyển khoản:</span>
                                    <strong class="text-dark bg-warning px-2 py-1 rounded">THANH TOAN {{ $order->order_code }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Chi tiết người nhận & Đơn hàng -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 12px;">
                <h5 class="font-weight-bold text-dark border-bottom pb-3 mb-3">
                    <i class="fa fa-info-circle text-primary mr-1"></i> Chi tiết đơn hàng
                </h5>

                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <span class="text-muted">Người nhận:</span>
                        <strong class="text-dark ml-1">{{ $order->customer_name }}</strong>
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-muted">Số điện thoại:</span>
                        <strong class="text-dark ml-1">{{ $order->customer_phone }}</strong>
                    </div>
                    <div class="col-md-12 mb-2">
                        <span class="text-muted">Hình thức & Địa chỉ nhận xe:</span>
                        @if($order->delivery_type === 'garage_delivery')
                            <span class="badge badge-info text-white ml-1"><i class="fa fa-truck"></i> Gara mang xe qua tận nơi</span>
                        @else
                            <span class="badge badge-secondary ml-1"><i class="fa fa-building"></i> Nhận trực tiếp tại Showroom</span>
                        @endif
                        <strong class="text-dark ml-2">{{ $order->customer_address }}</strong>
                        @if($order->latitude && $order->longitude)
                            <a href="https://www.google.com/maps?q={{ $order->latitude }},{{ $order->longitude }}" target="_blank" class="badge badge-danger ml-2 px-2 py-1 text-white">
                                <i class="fa fa-map-marker"></i> Xem tọa độ GPS
                            </a>
                        @endif
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-muted">Phương thức thanh toán:</span>
                        <strong class="text-dark ml-1">
                            @if($order->payment_method === 'momo')
                                Cổng thanh toán MoMo (Thẻ ATM nội địa)
                            @elseif($order->payment_method === 'sepay')
                                Quét mã QR SePay (TPBank)
                            @elseif($order->payment_method === 'bank_transfer')
                                Chuyển khoản Ngân hàng
                            @else
                                Thanh toán khi nhận bàn giao xe (COD / Tiền mặt / Thẻ)
                            @endif
                        </strong>
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-muted">Tình trạng bàn giao xe:</span>
                        @if($order->delivery_type === 'garage_delivery')
                            <span class="badge badge-primary ml-1">
                                <i class="fa fa-truck"></i> {{ $order->shipping_status === 'ready_to_deliver' ? 'Gara đang điều phối xe bàn giao tận nơi' : ($order->shipping_status ?? 'Chờ tiếp nhận') }}
                            </span>
                        @else
                            <span class="badge badge-success ml-1">
                                <i class="fa fa-building"></i> Đang chuẩn bị xe tại Showroom
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Bảng danh sách xe -->
                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Tên mẫu xe</th>
                                <th>Màu sắc</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-center">Đơn giá</th>
                                <th class="text-center">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="font-weight-bold">{{ $item->product_name }}</td>
                                    <td>
                                        <span class="badge badge-light border font-weight-bold px-2 py-1">
                                            <i class="fa fa-paint-brush text-primary"></i> {{ $item->color ?? 'Trắng' }}
                                        </span>
                                    </td>
                                    <td class="text-center font-weight-bold">x{{ $item->quantity }}</td>
                                    <td class="text-center">{{ number_format($item->price) }} đ</td>
                                    <td class="text-center font-weight-bold text-danger">{{ number_format($item->price * $item->quantity) }} đ</td>
                                </tr>
                            @endforeach
                            @if($order->ghn_total_fee > 0)
                                <tr>
                                    <td colspan="4" class="text-right text-muted font-weight-bold">Cước bàn giao xe tận nơi của Gara:</td>
                                    <td class="text-center font-weight-bold text-primary">
                                        + {{ number_format($order->ghn_total_fee) }} VNĐ
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="4" class="text-right text-muted font-weight-bold">Cước bàn giao xe:</td>
                                    <td class="text-center font-weight-bold text-success">
                                        Miễn phí (Nhận tại Showroom)
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="text-right font-weight-bold">Tổng thanh toán:</td>
                                <td class="text-center font-weight-bold text-danger" style="font-size: 17px;">
                                    {{ number_format($order->total_amount) }} VNĐ
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Nút điều hướng -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <a href="{{ route('welcome') }}" class="btn btn-outline-primary font-weight-bold px-4 py-2" style="border-radius: 30px;">
                    <i class="fa fa-home mr-1"></i> Quay lại trang chủ
                </a>
                <a href="{{ route('orders.my') }}" class="btn btn-primary font-weight-bold px-4 py-2" style="border-radius: 30px;">
                    <i class="fa fa-list mr-1"></i> Xem lịch sử đơn hàng của tôi
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
