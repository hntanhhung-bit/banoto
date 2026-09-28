@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <span class="badge badge-success px-3 py-2 font-weight-bold text-uppercase mb-2" style="font-size: 13px; letter-spacing: 0.5px;">
                <i class="fa fa-handshake-o mr-1"></i> KÊNH HỢP TÁC SHOWROOM & NHÀ XE
            </span>
            <h1 class="font-weight-bold text-dark display-5 mb-3">
                Tiếp Cận Hàng Triệu Khách Hàng Mua & Thuê Xe Mỗi Ngày
            </h1>
            <p class="lead text-muted mb-4" style="font-size: 16px;">
                AutoCar là nền tảng trung gian giới thiệu (Car Broker) uy tín. Chúng tôi kết nối người mua và khách thuê xe chất lượng đến tận cửa Showroom của bạn.
            </p>

            <div class="row">
                <div class="col-sm-6 mb-3">
                    <div class="d-flex align-items-start">
                        <div class="rounded-circle p-2 bg-success text-white mr-3" style="width: 36px; height: 36px; text-align: center;">
                            <i class="fa fa-check"></i>
                        </div>
                        <div>
                            <strong class="text-dark d-block">Không lo tìm khách</strong>
                            <small class="text-muted">Chúng tôi làm marketing và mang khách thật đến showroom.</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 mb-3">
                    <div class="d-flex align-items-start">
                        <div class="rounded-circle p-2 bg-success text-white mr-3" style="width: 36px; height: 36px; text-align: center;">
                            <i class="fa fa-shield"></i>
                        </div>
                        <div>
                            <strong class="text-dark d-block">Bảo lãnh cọc 100%</strong>
                            <small class="text-muted">Sàn thu cọc trước qua QR tự động, bảo vệ nhà xe tối đa.</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 mb-3">
                    <div class="d-flex align-items-start">
                        <div class="rounded-circle p-2 bg-success text-white mr-3" style="width: 36px; height: 36px; text-align: center;">
                            <i class="fa fa-money"></i>
                        </div>
                        <div>
                            <strong class="text-dark d-block">Nhận 90% - 99% Doanh thu</strong>
                            <small class="text-muted">Chiết khấu sàn cực thấp, minh bạch từng hợp đồng.</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 mb-3">
                    <div class="d-flex align-items-start">
                        <div class="rounded-circle p-2 bg-success text-white mr-3" style="width: 36px; height: 36px; text-align: center;">
                            <i class="fa fa-tachometer"></i>
                        </div>
                        <div>
                            <strong class="text-dark d-block">Dashboard riêng</strong>
                            <small class="text-muted">Hệ thống quản trị đội xe và đối soát tiền chuyên nghiệp.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM ĐĂNG KÝ ĐỐI TÁC -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg rounded-lg p-4 bg-white" style="border-top: 5px solid #10b981 !important;">
                <div class="card-body p-0">
                    <h4 class="font-weight-bold text-dark mb-1 text-center">
                        <i class="fa fa-building-o text-success mr-1"></i> Đăng Ký Trở Thành Đối Tác
                    </h4>
                    <p class="text-muted small text-center mb-4">Hoàn toàn miễn phí đăng ký • Xét duyệt & kích hoạt ngay</p>

                    @if ($errors->any())
                        <div class="alert alert-danger p-2 small">
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('partner.register.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-muted">Tên Showroom / Doanh nghiệp / Nhà xe: <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="VD: Showroom Ô tô Thủ Đô" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted">Họ tên Người đại diện pháp luật: <span class="text-danger">*</span></label>
                                <input type="text" name="representative_name" class="form-control" value="{{ old('representative_name') }}" placeholder="VD: Nguyễn Văn Thắng" required>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted">Số CCCD / CMND người đại diện: <span class="text-danger">*</span></label>
                                <input type="text" name="id_card_number" class="form-control" value="{{ old('id_card_number') }}" placeholder="VD: 00120100xxxx" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted">Mã số thuế / Số ĐKKD: <span class="text-danger">*</span></label>
                                <input type="text" name="tax_code" class="form-control" value="{{ old('tax_code') }}" placeholder="VD: 010899xxxx" required>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted">Số điện thoại liên hệ (Zalo): <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="0904xxxxxx" required>
                            </div>
                        </div>

                        <!-- KHỐI TẢI LÊN GIẤY TỜ KINH DOANH -->
                        <div class="p-3 bg-light rounded border mb-3">
                            <div class="font-weight-bold small text-dark mb-2">
                                <i class="fa fa-id-card text-success mr-1"></i> Hồ sơ chứng nhận kinh doanh xe (Bắt buộc gửi cho Admin duyệt):
                            </div>

                            <div class="form-group mb-2">
                                <label class="font-weight-bold small text-danger">Ảnh Giấy phép đăng ký kinh doanh xe (GPKD): <span class="text-danger">*</span></label>
                                <input type="file" name="business_license_image" class="form-control-file" accept="image/*,application/pdf" required>
                                <small class="text-muted">Chụp rõ nét bản gốc hoặc bản scan GPKD của showroom/nhà xe.</small>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold small text-danger">Ảnh CCCD hoặc Ảnh mặt tiền Showroom/Bãi xe: <span class="text-danger">*</span></label>
                                <input type="file" name="id_card_image" class="form-control-file" accept="image/*" required>
                                <small class="text-muted">Ảnh xác thực cơ sở kinh doanh xe thực tế của đối tác.</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted">Email đăng nhập quản lý: <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="showroom@example.com" required>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted">Địa chỉ Showroom / Bãi xe: <span class="text-danger">*</span></label>
                                <input type="text" name="showroom_address" class="form-control" value="{{ old('showroom_address') }}" placeholder="Số nhà, đường, quận/huyện, tỉnh..." required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted">Mật khẩu: <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted">Xác nhận mật khẩu: <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-7 mb-3">
                                <label class="font-weight-bold small text-muted">Lĩnh vực hợp tác: <span class="text-danger">*</span></label>
                                <select name="service_type" class="form-control">
                                    <option value="all" selected>Cả Bán xe & Cho thuê xe</option>
                                    <option value="sale">Chỉ Mua bán xe ô tô</option>
                                    <option value="rental_self">Chỉ Cho thuê xe tự lái</option>
                                    <option value="rental_driver">Cho thuê xe kèm tài xế</option>
                                </select>
                            </div>
                            <div class="form-group col-md-5 mb-3">
                                <label class="font-weight-bold small text-muted">Số lượng xe dự kiến:</label>
                                <input type="number" name="car_count" class="form-control" value="{{ old('car_count', 5) }}" min="1">
                            </div>
                        </div>

                        <div class="alert alert-info py-2 px-3 small border mb-3">
                            <i class="fa fa-shield mr-1"></i> <strong>Quy trình kiểm định:</strong> Sau khi nộp hồ sơ, Ban Quản Trị (Admin) sẽ đối chiếu giấy phép kinh doanh xe và phê duyệt tài khoản trong vòng 24 giờ.
                        </div>

                        <button type="submit" class="btn btn-success btn-block py-2 font-weight-bold shadow-sm" style="font-size: 16px; border-radius: 8px;">
                            <i class="fa fa-paper-plane mr-1"></i> NỘP HỒ SƠ ĐỐI TÁC CHO ADMIN DUYỆT
                        </button>
                    </form>

                    <div class="text-center mt-3 small text-muted">
                        Đã có tài khoản đối tác? <a href="{{ route('login') }}" class="font-weight-bold text-success">Đăng nhập tại đây</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
