<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kênh Dành Cho Đối Tác Showroom & Nhà Xe - AutoCar Partner</title>

    <!-- Bootstrap 4 & FontAwesome -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
        }

        /* Navbar Partner */
        .partner-navbar {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);
            box-shadow: 0 4px 20px rgba(6, 78, 59, 0.2);
            padding: 12px 0;
        }

        .partner-brand {
            font-size: 19px;
            font-weight: 800;
            color: #ffffff !important;
            letter-spacing: 0.3px;
        }

        .partner-nav-link {
            color: #d1fae5 !important;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px !important;
            border-radius: 8px;
            margin-right: 6px;
            transition: all 0.2s ease;
        }

        .partner-nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }

        .partner-nav-link.active {
            color: #ffffff !important;
            background-color: #059669;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
        }

        .content-wrapper {
            padding: 30px 0;
            min-height: 85vh;
        }

        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .stat-card {
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body>

    <!-- THANH ĐIỀU HƯỚNG CHÍNH CỦA ĐỐI TÁC -->
    <nav class="navbar navbar-expand-lg partner-navbar sticky-top">
        <div class="container-fluid px-4">
            <!-- Logo Partner -->
            <a class="navbar-brand partner-brand d-flex align-items-center" href="{{ route('partner.dashboard') }}">
                <span class="badge badge-light text-success mr-2 px-2 py-1 font-weight-bold"
                    style="font-size: 12px;">PARTNER</span>
                <i class="fa fa-handshake-o text-warning mr-2"></i> KÊNH ĐỐI TÁC SHOWROOM & NHÀ XE
            </a>

            <button class="navbar-toggler text-white border-0" type="button" data-toggle="collapse"
                data-target="#partnerNavbar">
                <i class="fa fa-bars"></i>
            </button>

            <div class="collapse navbar-collapse" id="partnerNavbar">
                <!-- Menu chức năng bên trái -->
                <ul class="navbar-nav mr-auto mt-2 mt-lg-0 ml-lg-3">
                    @if(Auth::check() && (Auth::user()->role === 'admin' || (Auth::user()->role === 'partner' && Auth::user()->partner_status === 'approved')))
                        <li class="nav-item">
                            <a class="nav-link partner-nav-link {{ request()->routeIs('partner.dashboard') ? 'active' : '' }}"
                                href="{{ route('partner.dashboard') }}">
                                <i class="fa fa-tachometer mr-1"></i> Tổng quan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link partner-nav-link {{ request()->routeIs('partner.cars*') ? 'active' : '' }}"
                                href="{{ route('partner.cars') }}">
                                <i class="fa fa-car mr-1"></i> Xe của tôi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link partner-nav-link {{ request()->routeIs('partner.appointments*') ? 'active' : '' }}"
                                href="{{ route('partner.appointments') }}">
                                <i class="fa fa-calendar-check-o mr-1"></i> Lịch hẹn xem xe
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link partner-nav-link {{ request()->routeIs('partner.rentals*') ? 'active' : '' }}"
                                href="{{ route('partner.rentals') }}">
                                <i class="fa fa-key mr-1"></i> Đơn thuê xe & Ký quỹ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link partner-nav-link {{ request()->routeIs('partner.reports*') ? 'active' : '' }}"
                                href="{{ route('partner.reports.index') }}">
                                <i class="fa fa-line-chart mr-1"></i> Thống kê doanh thu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link partner-nav-link {{ request()->routeIs('partner.payouts*') ? 'active' : '' }}"
                                href="{{ route('partner.payouts') }}">
                                <i class="fa fa-money mr-1"></i> Đối soát hợp đồng
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <span class="badge badge-warning text-dark font-weight-bold px-3 py-2" style="font-size: 13px;">
                                <i class="fa fa-clock-o mr-1"></i> Hồ sơ đối tác đang chờ Ban Quản Trị thẩm định
                            </span>
                        </li>
                    @endif
                </ul>

                <!-- Thông tin tài khoản bên phải -->
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item mr-3">
                        <a href="{{ route('welcome') }}" class="btn btn-sm btn-outline-light font-weight-bold"
                            style="border-radius: 20px;">
                            <i class="fa fa-globe mr-1"></i> Xem Website Sàn
                        </a>
                    </li>
                    @if(Auth::user()->role === 'admin')
                        <li class="nav-item mr-3">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-warning font-weight-bold"
                                style="border-radius: 20px;">
                                <i class="fa fa-shield mr-1"></i> Trang Quản trị Admin
                            </a>
                        </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white font-weight-bold" href="#" id="partnerDropdown"
                            role="button" data-toggle="dropdown">
                            <i class="fa fa-building-o text-warning mr-1"></i> {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 mt-2"
                            style="border-radius: 8px;">
                            <div class="dropdown-item-text small text-muted">
                                <strong>Đối tác:</strong> {{ Auth::user()->email }}<br>
                                <span class="badge badge-success mt-1">Đã xác minh (Verified)</span>
                            </div>
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('logout') }}" method="POST" class="px-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm btn-block font-weight-bold">
                                    <i class="fa fa-sign-out mr-1"></i> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- NỘI DUNG CHÍNH -->
    <div class="content-wrapper">
        <div class="container-fluid px-4">
            <!-- Thông báo flash -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert"
                    style="border-left: 4px solid #10b981 !important; border-radius: 8px;">
                    <i class="fa fa-check-circle mr-1"></i> <strong>Thành công:</strong> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert"
                    style="border-left: 4px solid #ef4444 !important; border-radius: 8px;">
                    <i class="fa fa-exclamation-triangle mr-1"></i> <strong>Lỗi:</strong> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-white py-3 border-top text-center text-muted small">
        <div class="container-fluid">
            &copy; {{ date('Y') }} AutoCar Portal - Cổng kết nối Đối tác Showroom & Nhà xe Uy tín toàn quốc.
        </div>
    </footer>

    <!-- Bootstrap 4 JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    @yield('scripts')
</body>

</html>