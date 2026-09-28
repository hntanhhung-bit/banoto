<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hệ Thống Quản Trị - Admin Panel</title>
    
    <!-- Bootstrap 4 & FontAwesome -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body { background-color: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        
        /* Navbar Admin */
        .admin-navbar {
            background-color: #1e293b;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            padding: 10px 0;
        }
        .admin-brand {
            font-size: 20px;
            font-weight: 800;
            color: #f8fafc !important;
            letter-spacing: 0.5px;
        }
        .admin-nav-link {
            color: #cbd5e1 !important;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px !important;
            border-radius: 6px;
            margin-right: 5px;
            transition: all 0.2s ease;
        }
        .admin-nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .admin-nav-link.active {
            color: #ffffff !important;
            background-color: #005fb7;
            box-shadow: 0 2px 6px rgba(0,95,183,0.4);
        }

        .content-wrapper { padding: 25px 0; min-height: 82vh; }
        .card { border-radius: 10px; }

        /* ADMIN LIVECHAT (LAB 7) */
        .admin-chat-toggle {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #fff;
            padding: 12px 20px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.4);
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            border: 1px solid #334155;
        }
        .admin-chat-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.6);
            color: #38bdf8;
        }
        .admin-chat-popup {
            position: fixed;
            bottom: 85px;
            right: 25px;
            width: 760px;
            height: 520px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 12px 35px rgba(0,0,0,0.25);
            z-index: 9999;
            display: flex;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            animation: adminChatFade 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes adminChatFade {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .admin-chat-sidebar {
            width: 270px;
            background: #fff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }
        .admin-chat-sidebar-header {
            background: #f8fafc;
            padding: 12px 16px;
            font-weight: bold;
            color: #1e293b;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-user-list {
            flex: 1;
            overflow-y: auto;
        }
        .admin-user-item {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background 0.15s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-user-item:hover {
            background: #f1f5f9;
        }
        .admin-user-item.active {
            background: #e0f2fe;
            border-left: 4px solid #005fb7;
        }
        .admin-user-info {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 190px;
        }
        .admin-user-name {
            font-weight: 600;
            font-size: 13.5px;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .admin-user-sub {
            font-size: 11.5px;
            color: #64748b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .admin-chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
        }
        .admin-chat-header {
            background: #1e293b;
            color: #fff;
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-chat-header .close-btn {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 20px;
            cursor: pointer;
            line-height: 1;
            transition: color 0.2s;
        }
        .admin-chat-header .close-btn:hover { color: #fff; }
        .admin-chat-body {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .admin-chat-footer {
            display: flex;
            padding: 10px;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            align-items: center;
        }
        .admin-msg-bubble {
            max-width: 80%;
            padding: 8px 14px;
            border-radius: 16px;
            font-size: 13px;
            line-height: 1.4;
            word-break: break-word;
            margin-bottom: 6px;
        }
        .admin-msg-bubble.me {
            align-self: flex-end;
            background: #005fb7;
            color: #fff;
            border-bottom-right-radius: 4px;
            margin-left: auto;
        }
        .admin-msg-bubble.other {
            align-self: flex-start;
            background: #e2e8f0;
            color: #1e293b;
            border-bottom-left-radius: 4px;
            margin-right: auto;
        }
        .admin-msg-time {
            font-size: 10px;
            opacity: 0.75;
            margin-top: 3px;
            display: block;
        }
        .admin-msg-bubble.me .admin-msg-time {
            text-align: right;
            color: #dbeafe;
        }
        .admin-msg-bubble.other .admin-msg-time {
            text-align: left;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- THANH ĐIỀU HƯỚNG CHÍNH CỦA ADMIN -->
    <nav class="navbar navbar-expand-lg admin-navbar sticky-top">
        <div class="container-fluid px-4">
            <!-- Logo Admin -->
            <a class="navbar-brand admin-brand" href="{{ route('admin.dashboard') }}">
                <i class="fa fa-tachometer text-warning mr-1"></i> OTO ADMIN
            </a>
            
            <button class="navbar-toggler text-white" type="button" data-toggle="collapse" data-target="#adminNavbar">
                <span class="fa fa-bars text-white"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="adminNavbar">
                <!-- Menu chức năng -->
                <ul class="navbar-nav mr-auto ml-lg-4">
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="fa fa-tachometer"></i> Tổng quan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                            <i class="fa fa-shopping-cart text-warning"></i> Đơn mua xe
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}" style="{{ request()->routeIs('admin.payments.*') ? 'background-color: #a50064 !important;' : '' }}">
                            <i class="fa fa-qrcode" style="color: #ff69b4;"></i> Báo cáo MoMo & Thu chi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}">
                            <i class="fa fa-calendar-check-o text-success"></i> Lịch hẹn xem xe
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.rentals.*') ? 'active' : '' }}" href="{{ route('admin.rentals.index') }}">
                            <i class="fa fa-key text-danger"></i> Đơn thuê xe & Tài xế
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.partners.*') ? 'active' : '' }}" href="{{ route('admin.partners.index') }}">
                            <i class="fa fa-handshake-o text-warning"></i> Duyệt Đối tác
                            @php
                                $pendingPartnersCount = \App\Models\User::where('partner_status', 'pending')->count();
                            @endphp
                            @if($pendingPartnersCount > 0)
                                <span class="badge badge-danger ml-1">{{ $pendingPartnersCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                            <i class="fa fa-car text-info"></i> Quản lý Kho xe
                            @php
                                $pendingCarsCount = \App\Models\Product::whereNotNull('partner_id')->where('approval_status', 'pending')->count();
                            @endphp
                            @if($pendingCarsCount > 0)
                                <span class="badge badge-warning text-dark ml-1 font-weight-bold">{{ $pendingCarsCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                            <i class="fa fa-tags"></i> Hãng xe
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="fa fa-users"></i> Người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                            <i class="fa fa-line-chart text-success"></i> Thống kê Doanh thu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link admin-nav-link {{ request()->routeIs('admin.gmail.*') ? 'active' : '' }}" href="{{ route('admin.gmail.index') }}">
                            <i class="fa fa-envelope text-danger"></i> Cấu hình Gmail API
                        </a>
                    </li>
                </ul>
                
                <!-- Menu thông tin tài khoản -->
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item mr-2">
                        <a href="{{ route('welcome') }}" class="btn btn-sm btn-outline-info font-weight-bold px-3">
                            <i class="fa fa-globe"></i> Xem Website
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link text-white font-weight-bold dropdown-toggle d-flex align-items-center" href="#" id="adminUserDropdown" data-toggle="dropdown">
                            <i class="fa fa-user-circle mr-1" style="font-size: 18px;"></i> {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                            <div class="dropdown-header font-weight-bold">{{ Auth::user()->email }}</div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger font-weight-bold" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out mr-2"></i> Đăng xuất
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- THÔNG BÁO FLASH MESSAGE TOÀN TRANG ADMIN -->
    <div class="container-fluid px-4 mt-3">
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
    </div>

    <!-- KHU VỰC NỘI DUNG CHÍNH -->
    <div class="content-wrapper">
        @yield('content')
    </div>

    <!-- Thư viện Javascript cần thiết cho Bootstrap (dùng full jQuery thay vì slim để hỗ trợ AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <!-- ADMIN LIVECHAT (LAB 7 NÂNG CAO) -->
    <!-- Nút toggle mở chat ở góc dưới màn hình -->
    <div id="admin-chat-toggle" class="admin-chat-toggle shadow">
        <i class="fa fa-comments mr-2" style="font-size: 18px;"></i>
        <span>Hỗ trợ Khách hàng</span>
        <span class="badge badge-danger ml-2" id="admin-chat-unread-badge" style="display: none;">0</span>
    </div>

    <!-- Khung chat popup đa cửa sổ dành cho Admin -->
    <div id="admin-chat-popup" class="admin-chat-popup" style="display: none;">
        <!-- Cột trái: Danh sách User đã tương tác & Tìm kiếm khách hàng mới -->
        <div class="admin-chat-sidebar">
            <div class="admin-chat-sidebar-header">
                <div><i class="fa fa-users text-primary mr-1"></i> Khách hàng</div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 mr-1" id="admin-user-all-toggle" title="Nhắn tin với khách hàng bất kỳ">
                        <i class="fa fa-plus"></i> Tin mới
                    </button>
                    <button type="button" class="btn btn-sm btn-light border py-0 px-2" id="admin-user-refresh" title="Làm mới">
                        <i class="fa fa-refresh"></i>
                    </button>
                </div>
            </div>

            <!-- Ô tìm kiếm khách hàng theo tên, email, SĐT -->
            <div class="p-2 border-bottom bg-light">
                <input type="text" id="admin-user-search" class="form-control form-control-sm" placeholder="🔍 Tìm tên, email, SĐT...">
            </div>

            <!-- Tabs chuyển đổi: Đã nhắn tin / Tất cả khách hàng -->
            <div class="d-flex border-bottom bg-white small font-weight-bold text-center" id="admin-chat-tabs">
                <a href="javascript:void(0)" class="flex-fill py-2 text-primary border-bottom border-primary font-weight-bold" id="btn-tab-recent" style="text-decoration: none;">
                    Hộp thư gần đây
                </a>
                <a href="javascript:void(0)" class="flex-fill py-2 text-muted" id="btn-tab-all" style="text-decoration: none;">
                    Tất cả khách hàng
                </a>
            </div>

            <div class="admin-user-list" id="admin-user-list">
                <div class="text-center text-muted p-4">
                    <i class="fa fa-spinner fa-spin mb-2"></i>
                    <div>Đang tải danh sách...</div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Khung hội thoại tin nhắn -->
        <div class="admin-chat-main">
            <div class="admin-chat-header">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white d-inline-flex justify-content-center align-items-center mr-2" style="width: 34px; height: 34px; font-size: 14px;">
                        <i class="fa fa-user"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold" id="admin-active-user-name" style="font-size: 14px;">Chọn khách hàng để trò chuyện</div>
                        <small class="text-info" id="admin-active-user-sub" style="display: none;"><i class="fa fa-circle text-success"></i> Sẵn sàng nhắn tin</small>
                    </div>
                </div>
                <button type="button" class="close-btn" id="admin-chat-close" title="Đóng">&times;</button>
            </div>
            <div class="admin-chat-body" id="admin-chat-messages">
                <div class="text-center text-muted m-auto p-4">
                    <i class="fa fa-comments-o fa-3x mb-3 text-secondary"></i>
                    <h6 class="font-weight-bold text-dark">Hỗ trợ & Trực chat Khách hàng</h6>
                    <p class="small text-muted mb-0">Bấm chọn một khách hàng ở danh sách bên trái hoặc bấm nút <strong>"+ Tin mới"</strong> để chủ động nhắn tin tư vấn cho khách hàng bất kỳ.</p>
                </div>
            </div>
            <div class="admin-chat-footer">
                <input type="text" id="admin-chat-input" class="form-control form-control-sm" placeholder="Nhập tin nhắn..." autocomplete="off" disabled>
                <button class="btn btn-primary btn-sm ml-2 px-3" id="admin-chat-send" title="Gửi" disabled>
                    <i class="fa fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var isAdminChatOpen = false;
            var activeUserId = null;
            var activeUserName = '';
            var activeUserEmail = '';
            var currentTab = 'recent'; // 'recent' hoặc 'all'
            var searchTimeout = null;
            var adminPollInterval = null;
            var unreadPollInterval = null;
            var currentAdminId = {{ Auth::id() }};
            var lastAdminMsgCount = 0;

            function formatTime(isoString) {
                if (!isoString) return '';
                var d = new Date(isoString);
                var h = ('0' + d.getHours()).slice(-2);
                var m = ('0' + d.getMinutes()).slice(-2);
                return h + ':' + m;
            }

            function scrollToAdminBottom() {
                var body = document.getElementById('admin-chat-messages');
                if (body) {
                    body.scrollTop = body.scrollHeight;
                }
            }

            function loadUserList() {
                var searchQuery = $.trim($('#admin-user-search').val());
                $.ajax({
                    url: "{{ route('admin.chat.users') }}",
                    method: "GET",
                    data: {
                        tab: currentTab,
                        q: searchQuery
                    },
                    dataType: "json",
                    success: function(users) {
                        var html = '';
                        var totalUnread = 0;

                        if (!users || users.length === 0) {
                            html = '<div class="text-center text-muted p-4 small">Không tìm thấy khách hàng nào.</div>';
                        } else {
                            users.forEach(function(u) {
                                totalUnread += (u.unread_count || 0);
                                var isActive = (activeUserId == u.id) ? 'active' : '';
                                var unreadBadge = '';
                                if (u.unread_count > 0) {
                                    unreadBadge = '<span class="badge badge-danger badge-pill">' + u.unread_count + '</span>';
                                } else if (!u.has_chatted) {
                                    unreadBadge = '<span class="badge badge-light border text-muted" style="font-size: 10px;">Chưa chat</span>';
                                }

                                var lastSnippet = u.last_message ? u.last_message : (u.phone ? u.phone + ' • ' + u.email : u.email);

                                html += '<div class="admin-user-item ' + isActive + '" data-id="' + u.id + '" data-name="' + $('<div>').text(u.name).html() + '" data-email="' + $('<div>').text(u.email || '').html() + '">';
                                html += '  <div class="admin-user-info">';
                                html += '    <div class="admin-user-name">' + $('<div>').text(u.name).html() + '</div>';
                                html += '    <div class="admin-user-sub">' + $('<div>').text(lastSnippet).html() + '</div>';
                                html += '  </div>';
                                html += '  <div class="ml-2">' + unreadBadge + '</div>';
                                html += '</div>';
                            });
                        }

                        $('#admin-user-list').html(html);

                        // Cập nhật badge tổng số tin chưa đọc
                        if (totalUnread > 0) {
                            $('#admin-chat-unread-badge').text(totalUnread).show();
                        } else {
                            $('#admin-chat-unread-badge').hide();
                        }
                    },
                    error: function(err) {
                        console.error('Lỗi tải danh sách user chat:', err);
                    }
                });
            }

            function loadMessagesForUser(userId, shouldScroll) {
                if (!userId) return;

                $.ajax({
                    url: "{{ url('admin/chat/messages') }}/" + userId,
                    method: "GET",
                    dataType: "json",
                    success: function(messages) {
                        var html = '';
                        if (!messages || messages.length === 0) {
                            html = '<div class="text-center text-muted m-auto p-4">';
                            html += '  <div class="rounded-circle bg-light d-inline-flex p-3 mb-2"><i class="fa fa-paper-plane fa-2x text-primary"></i></div>';
                            html += '  <h6 class="font-weight-bold text-dark">Bắt đầu trò chuyện với ' + $('<div>').text(activeUserName).html() + '</h6>';
                            html += '  <p class="small text-muted mb-0">Khách hàng chưa có tin nhắn nào. Bạn có thể chủ động nhắn tin tư vấn, báo giá hoặc chăm sóc khách hàng ngay bây giờ!</p>';
                            html += '</div>';
                        } else {
                            messages.forEach(function(msg) {
                                var isMe = (msg.sender_id == currentAdminId);
                                var bubbleClass = isMe ? 'me' : 'other';
                                var timeStr = formatTime(msg.created_at);

                                html += '<div class="admin-msg-bubble ' + bubbleClass + '">';
                                html += $('<div>').text(msg.content).html();
                                html += '<span class="admin-msg-time">' + timeStr + '</span>';
                                html += '</div>';
                            });
                        }

                        $('#admin-chat-messages').html(html);

                        if (shouldScroll || messages.length !== lastAdminMsgCount) {
                            scrollToAdminBottom();
                        }
                        lastAdminMsgCount = messages.length;
                    },
                    error: function(err) {
                        console.error('Lỗi tải tin nhắn với user ' + userId, err);
                    }
                });
            }

            function sendAdminMessage() {
                if (!activeUserId) return;
                var content = $.trim($('#admin-chat-input').val());
                if (!content) return;

                $('#admin-chat-input').val('');
                $('#admin-chat-send').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.chat.send') }}",
                    method: "POST",
                    data: {
                        user_id: activeUserId,
                        receiver_id: activeUserId,
                        message: content,
                        content: content
                    },
                    dataType: "json",
                    success: function(res) {
                        $('#admin-chat-send').prop('disabled', false);
                        loadMessagesForUser(activeUserId, true);
                        loadUserList();
                    },
                    error: function(err) {
                        $('#admin-chat-send').prop('disabled', false);
                        alert('Không thể gửi tin nhắn. Vui lòng thử lại!');
                    }
                });
            }

            function selectUserToChat(userId, userName, userEmail) {
                activeUserId = userId;
                activeUserName = userName;
                activeUserEmail = userEmail || '';

                $('.admin-user-item').removeClass('active');
                $('.admin-user-item[data-id="' + userId + '"]').addClass('active');

                var displayTitle = userName;
                if (userEmail) {
                    displayTitle += ' (' + userEmail + ')';
                }
                $('#admin-active-user-name').text(displayTitle);
                $('#admin-active-user-sub').show();
                $('#admin-chat-input').prop('disabled', false).focus();
                $('#admin-chat-send').prop('disabled', false);

                loadMessagesForUser(activeUserId, true);
                loadUserList();
            }

            // Click chọn user trong danh sách
            $(document).on('click', '.admin-user-item', function() {
                var userId = $(this).data('id');
                var userName = $(this).data('name');
                var userEmail = $(this).data('email');
                selectUserToChat(userId, userName, userEmail);
            });

            // Tab "Hộp thư gần đây"
            $('#btn-tab-recent').click(function() {
                currentTab = 'recent';
                $('#btn-tab-recent').addClass('text-primary border-bottom border-primary font-weight-bold').removeClass('text-muted');
                $('#btn-tab-all').removeClass('text-primary border-bottom border-primary font-weight-bold').addClass('text-muted');
                loadUserList();
            });

            // Tab "Tất cả khách hàng"
            $('#btn-tab-all, #admin-user-all-toggle').click(function() {
                currentTab = 'all';
                $('#btn-tab-all').addClass('text-primary border-bottom border-primary font-weight-bold').removeClass('text-muted');
                $('#btn-tab-recent').removeClass('text-primary border-bottom border-primary font-weight-bold').addClass('text-muted');
                loadUserList();
            });

            // Tìm kiếm người dùng có debounce 300ms
            $('#admin-user-search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    loadUserList();
                }, 300);
            });

            // Toggle mở/đóng popup
            $('#admin-chat-toggle').click(function() {
                isAdminChatOpen = !isAdminChatOpen;
                if (isAdminChatOpen) {
                    $('#admin-chat-popup').fadeIn(200);
                    loadUserList();
                    if (activeUserId) {
                        loadMessagesForUser(activeUserId, true);
                    }
                    if (!adminPollInterval) {
                        adminPollInterval = setInterval(function() {
                            if (isAdminChatOpen) {
                                loadUserList();
                                if (activeUserId) {
                                    loadMessagesForUser(activeUserId, false);
                                }
                            }
                        }, 3000);
                    }
                } else {
                    $('#admin-chat-popup').fadeOut(200);
                }
            });

            $('#admin-chat-close').click(function() {
                isAdminChatOpen = false;
                $('#admin-chat-popup').fadeOut(200);
            });

            $('#admin-user-refresh').click(function() {
                loadUserList();
                if (activeUserId) {
                    loadMessagesForUser(activeUserId, false);
                }
            });

            $('#admin-chat-send').click(function() {
                sendAdminMessage();
            });

            $('#admin-chat-input').keypress(function(e) {
                if (e.which === 13) {
                    sendAdminMessage();
                }
            });

            // Hàm toàn cục cho phép mở chat với khách hàng bất kỳ từ bất kỳ trang nào
            window.openAdminChatWith = function(userId, userName, userEmail) {
                isAdminChatOpen = true;
                $('#admin-chat-popup').fadeIn(200);
                selectUserToChat(userId, userName, userEmail);
            };

            // Polling ngầm để cập nhật badge tin nhắn chưa đọc khi popup đóng
            unreadPollInterval = setInterval(function() {
                if (!isAdminChatOpen) {
                    loadUserList();
                }
            }, 8000);

            // Tải số lượng ban đầu
            loadUserList();
        });
    </script>
</body>
</html>