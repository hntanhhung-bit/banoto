@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4">
                    <i class="fa fa-qrcode fa-3x mb-2"></i>
                    <h3 class="font-weight-bold mb-1">THANH TOÁN QUÉT MÃ QR SEPAY</h3>
                    <p class="mb-0">Đơn mua xe: <strong class="badge badge-light text-dark px-3 py-1 font-weight-bold" style="font-size: 15px;">#{{ $order->order_code }}</strong></p>
                </div>

                <div class="card-body p-4">
                    <div class="alert alert-info border-0 rounded-lg text-center">
                        <i class="fa fa-spinner fa-spin mr-1"></i> Hệ thống đang tự động theo dõi biến động tài khoản. Đơn hàng sẽ <strong>tự động hoàn tất</strong> ngay sau khi bạn quét mã chuyển khoản thành công.
                    </div>

                    <div class="row align-items-center mt-4">
                        <!-- Cột Mã QR SePay -->
                        <div class="col-md-5 text-center mb-4 mb-md-0 border-md-right">
                            <div class="p-2 border rounded shadow-sm bg-white d-inline-block">
                                <img src="{{ $qrUrl }}" alt="SePay VietQR" class="img-fluid rounded" style="max-width: 240px;" id="qrImage">
                            </div>
                            <div class="mt-2 small text-muted">
                                <i class="fa fa-mobile-phone fa-lg text-primary mr-1"></i> Mở App Ngân hàng bất kỳ để quét mã QR
                            </div>
                            <div class="mt-3">
                                <span class="badge badge-warning text-dark px-3 py-2 font-weight-bold" id="statusBadge">
                                    <i class="fa fa-clock-o mr-1"></i> Đang chờ chuyển khoản...
                                </span>
                            </div>
                        </div>

                        <!-- Cột Thông tin chuyển khoản -->
                        <div class="col-md-7 pl-md-4">
                            <h5 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
                                <i class="fa fa-university text-primary mr-1"></i> Thông tin tài khoản thụ hưởng (SePay):
                            </h5>

                            <ul class="list-group list-group-flush mb-3 small">
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Ngân hàng:</span>
                                    <strong class="text-dark">{{ $bankInfo['bank_full_name'] }}</strong>
                                </li>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Số tài khoản:</span>
                                    <div>
                                        <strong class="text-primary font-weight-bold" style="font-size: 17px;" id="accNum">{{ $bankInfo['account_number'] }}</strong>
                                        <button class="btn btn-sm btn-outline-secondary ml-1 py-0 px-2" onclick="copyText('{{ $bankInfo['account_number'] }}')" title="Sao chép số tài khoản">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                    </div>
                                </li>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Chủ tài khoản:</span>
                                    <strong class="text-dark font-weight-bold">{{ $bankInfo['account_holder'] }}</strong>
                                </li>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Số tiền thanh toán:</span>
                                    <strong class="text-danger font-weight-bold" style="font-size: 18px;">{{ number_format($order->total_amount) }} VNĐ</strong>
                                </li>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Nội dung chuyển khoản:</span>
                                    <div>
                                        <span class="bg-warning text-dark font-weight-bold px-2 py-1 rounded" id="transferDesc">{{ $description }}</span>
                                        <button class="btn btn-sm btn-outline-secondary ml-1 py-0 px-2" onclick="copyText('{{ $description }}')" title="Sao chép nội dung">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                    </div>
                                </li>
                            </ul>

                            <div class="alert alert-warning py-2 px-3 small border mb-0">
                                <i class="fa fa-exclamation-triangle text-danger mr-1"></i>
                                <strong>Lưu ý:</strong> Vui lòng giữ nguyên <strong>nội dung chuyển khoản</strong> để hệ thống SePay tự động nhận diện và kích hoạt đơn hàng ngay tức thì.
                            </div>
                        </div>
                    </div>

                    <!-- Nút thao tác -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <a href="{{ route('orders.my') }}" class="btn btn-outline-secondary font-weight-bold">
                            <i class="fa fa-arrow-left mr-1"></i> Lịch sử đơn hàng
                        </a>
                        <button type="button" class="btn btn-success font-weight-bold px-4 shadow-sm" id="btnManualCheck" onclick="checkPaymentStatus(true)">
                            <i class="fa fa-refresh mr-1"></i> Tôi đã chuyển khoản xong
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyText(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Đã sao chép: ' + text);
        }).catch(function() {
            prompt('Sao chép thủ công:', text);
        });
    }

    let isChecking = false;
    function checkPaymentStatus(manual = false) {
        if (isChecking) return;
        isChecking = true;

        if (manual) {
            let btn = document.getElementById('btnManualCheck');
            btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Đang kiểm tra...';
            btn.disabled = true;
        }

        fetch("{{ route('sepay.check', ['type' => 'order', 'code' => $order->order_code]) }}")
            .then(res => res.json())
            .then(data => {
                isChecking = false;
                if (manual) {
                    let btn = document.getElementById('btnManualCheck');
                    btn.innerHTML = '<i class="fa fa-refresh mr-1"></i> Tôi đã chuyển khoản xong';
                    btn.disabled = false;
                }

                if (data.paid && data.redirect_url) {
                    let badge = document.getElementById('statusBadge');
                    badge.className = 'badge badge-success text-white px-3 py-2 font-weight-bold';
                    badge.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Thanh toán thành công! Đang chuyển hướng...';
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1200);
                } else if (manual) {
                    alert('Hệ thống chưa nhận được tiền từ ngân hàng cho nội dung "{{ $description }}". Quý khách vui lòng đợi trong giây lát hoặc kiểm tra lại lịch sử giao dịch trên App ngân hàng!');
                }
            })
            .catch(err => {
                isChecking = false;
                if (manual) {
                    let btn = document.getElementById('btnManualCheck');
                    btn.innerHTML = '<i class="fa fa-refresh mr-1"></i> Tôi đã chuyển khoản xong';
                    btn.disabled = false;
                }
            });
    }

    // Tự động kiểm tra định kỳ mỗi 3.5 giây
    setInterval(() => {
        checkPaymentStatus(false);
    }, 3500);
</script>
@endsection
