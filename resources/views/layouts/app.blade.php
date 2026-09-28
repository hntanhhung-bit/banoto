<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Thị trường Mua bán Ô tô</title>
    
    <!-- Bootstrap 4 CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        
        /* CSS cho Header */
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); padding: 15px 0; }
        .navbar-brand { font-size: 24px; font-weight: bold; color: #005fb7 !important; }
        .nav-link { font-weight: 600; color: #333 !important; font-size: 15px; margin-right: 15px; transition: color 0.3s;}
        .nav-link:hover { color: #005fb7 !important; }
        .btn-post { background-color: #f26822; color: white; font-weight: bold; border: none; transition: 0.3s; }
        .btn-post:hover { background-color: #d15619; color: white; }
        
        /* CSS cho Footer */
        .footer { background-color: #2b3138; color: #b0b4b8; padding: 50px 0 20px; margin-top: 50px; font-size: 14px;}
        .footer h5 { color: #fff; font-weight: bold; font-size: 16px; margin-bottom: 20px; text-transform: uppercase;}
        .footer a { color: #b0b4b8; text-decoration: none; line-height: 2; transition: color 0.3s;}
        .footer a:hover { color: #fff; }
        .footer .social-icons i { font-size: 20px; margin-right: 10px; color: #fff; }

        /* LIVECHAT POPUP (LAB 7) */
        .chat-toggle {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #005fb7, #0084ff);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 95, 183, 0.4);
            z-index: 9999;
            transition: all 0.25s ease-in-out;
        }
        .chat-toggle:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 20px rgba(0, 95, 183, 0.6);
            color: #fff;
        }
        .chat-toggle .chat-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            border-radius: 50%;
            padding: 4px 7px;
            font-size: 11px;
            border: 2px solid #fff;
        }
        .chat-popup {
            position: fixed;
            bottom: 95px;
            right: 25px;
            width: 360px;
            height: 480px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            z-index: 9999;
            box-shadow: 0 10px 30px rgba(0,0,0,0.22);
            border: 1px solid #e2e8f0;
            animation: chatSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes chatSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .chat-header {
            background: linear-gradient(135deg, #005fb7, #0084ff);
            color: #fff;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            font-size: 15px;
        }
        .chat-header .close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            line-height: 1;
            opacity: 0.85;
            transition: opacity 0.2s;
        }
        .chat-header .close-btn:hover { opacity: 1; }
        .chat-body {
            flex: 1;
            padding: 14px;
            overflow-y: auto;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .chat-footer {
            display: flex;
            padding: 10px;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            align-items: center;
        }
        .chat-msg {
            max-width: 82%;
            padding: 8px 14px;
            border-radius: 16px;
            font-size: 13.5px;
            line-height: 1.45;
            word-break: break-word;
            margin-bottom: 6px;
        }
        .chat-msg.user-msg {
            align-self: flex-end;
            background: #005fb7;
            color: #fff;
            border-bottom-right-radius: 4px;
            margin-left: auto;
        }
        .chat-msg.admin-msg {
            align-self: flex-start;
            background: #e2e8f0;
            color: #1e293b;
            border-bottom-left-radius: 4px;
            margin-right: auto;
        }
        .chat-msg-time {
            font-size: 10px;
            opacity: 0.75;
            margin-top: 3px;
            display: block;
        }
        .chat-msg.user-msg .chat-msg-time {
            text-align: right;
            color: #dbeafe;
        }
        .chat-msg.admin-msg .chat-msg-time {
            text-align: left;
            color: #64748b;
        }
        .chat-empty {
            text-align: center;
            color: #94a3b8;
            margin-top: auto;
            margin-bottom: auto;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <!-- TOP BAR ĐỊNH VỊ BÊN THỨ BA -->
    <div class="py-1 px-3 text-center small text-white font-weight-bold" style="background: linear-gradient(90deg, #0d3b66, #005fb7, #0d3b66); font-size: 12px; letter-spacing: 0.3px;">
        <i class="fa fa-shield text-warning mr-1"></i> NỀN TẢNG BÊN THỨ BA ĐỘC LẬP: Đặt lịch xem xe hộ miễn phí • Cử chuyên viên check xe hộ • Bảo lãnh cọc thuê xe an toàn 100%
    </div>

    <!-- 1. HEADER (Thanh điều hướng) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="{{ route('welcome') }}">
                <span class="p-2 bg-primary text-white rounded mr-2" style="font-size: 18px; line-height: 1;">
                    <i class="fa fa-car"></i>
                </span>
                <div>
                    <span class="text-primary font-weight-bold" style="letter-spacing: -0.5px;">AUTO CAR</span>
                    <small class="d-block text-danger font-weight-bold" style="font-size: 10px; margin-top: -4px;">BÊN THỨ 3 • ĐẶT HỘ XEM & THUÊ XE</small>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Menu giữa tập trung vào 3 dịch vụ cốt lõi của Bên thứ ba -->
                <ul class="navbar-nav mr-auto ml-lg-4">
                    <li class="nav-item">
                        <a class="nav-link {{ !request('service') ? 'text-primary font-weight-bold' : '' }}" href="{{ route('welcome') }}">
                            <i class="fa fa-th-large text-secondary"></i> Tất cả xe
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('service') == 'view' ? 'text-success font-weight-bold' : '' }}" href="{{ route('welcome', ['service' => 'view']) }}">
                            <i class="fa fa-calendar-check-o text-success"></i> Đặt lịch xem xe hộ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('service') == 'rent_self' ? 'text-danger font-weight-bold' : '' }}" href="{{ route('welcome', ['service' => 'rent_self']) }}">
                            <i class="fa fa-key text-danger"></i> Thuê tự lái hộ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('service') == 'rent_driver' ? 'text-warning font-weight-bold' : '' }}" href="{{ route('welcome', ['service' => 'rent_driver']) }}">
                            <i class="fa fa-user-circle text-warning"></i> Thuê tài xế hộ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-success font-weight-bold" href="{{ route('partner.register') }}">
                            <i class="fa fa-handshake-o text-success"></i> Hợp tác Showroom
                        </a>
                    </li>
                </ul>
                
                <!-- Menu phải dành cho người dùng -->
                <ul class="navbar-nav ml-auto align-items-center">
                    @guest
                        <!-- Nếu CHƯA đăng nhập thì hiện Đăng nhập / Đăng ký -->
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold text-primary" href="{{ route('login') }}">
                                <i class="fa fa-sign-in"></i> Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ml-2 font-weight-bold px-3 py-1 shadow-sm" href="{{ route('register') }}" style="border-radius: 20px;">
                                <i class="fa fa-user-plus"></i> Đăng ký
                            </a>
                        </li>
                    @else
                        <!-- KIỂM TRA QUYỀN: Nếu là Admin/Partner thì hiện nút Quản lý tương ứng -->
                        @if(Auth::user()->role === 'partner')
                            <li class="nav-item">
                                <a class="btn btn-success btn-sm px-3 mr-2 font-weight-bold shadow-sm" href="{{ route('partner.dashboard') }}" style="border-radius: 20px;">
                                    <i class="fa fa-handshake-o"></i> Kênh Đối Tác Showroom
                                </a>
                            </li>
                        @elseif(Auth::user()->role === 'admin')
                            <li class="nav-item">
                                <a class="btn btn-success btn-sm px-3 mr-2 font-weight-bold shadow-sm" href="{{ route('partner.dashboard') }}" style="border-radius: 20px;">
                                    <i class="fa fa-handshake-o"></i> Kênh Đối Tác
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-dark btn-sm px-3 mr-2 font-weight-bold shadow-sm" href="{{ route('admin.dashboard') }}" style="border-radius: 20px;">
                                    <i class="fa fa-cogs"></i> Quản trị Admin
                                </a>
                            </li>
                        @else
                            <li class="nav-item dropdown mr-2">
                                <a class="btn btn-outline-primary btn-sm dropdown-toggle font-weight-bold px-3" href="#" id="myServicesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 20px;">
                                    <i class="fa fa-user-circle mr-1"></i> Dịch vụ của tôi
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow border-0" aria-labelledby="myServicesDropdown" style="border-radius: 10px;">
                                    <a class="dropdown-item py-2" href="{{ route('appointments.my') }}">
                                        <i class="fa fa-calendar-check-o text-success mr-2"></i> Lịch hẹn xem xe của tôi
                                    </a>
                                    <a class="dropdown-item py-2" href="{{ route('rentals.my') }}">
                                        <i class="fa fa-key text-danger mr-2"></i> Đơn thuê xe của tôi
                                    </a>
                                </div>
                            </li>
                        @endif

                        <!-- Nút Đăng xuất -->
                        <li class="nav-item">
                            <a class="nav-link text-danger font-weight-bold ml-2" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out"></i> Đăng xuất ({{ Auth::user()->name }})
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- HIỂN THỊ THÔNG BÁO FLASH MESSAGE -->
    <div class="container mt-3">
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

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-info-circle mr-1"></i> {{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-info-circle mr-1"></i> {{ session('info') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    </div>

    <!-- 2. NỘI DUNG CHÍNH (Nơi hiển thị các trang con như index, create, show...) -->
    <div style="min-height: 60vh;">
        @yield('content')
    </div>

    <!-- 3. FOOTER (Chân trang) -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- Cột 1: Hỗ trợ -->
                <div class="col-md-3 col-sm-6 mb-4">
                    <h5>Hỗ trợ khách hàng</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Quy định, chính sách</a></li>
                        <li><a href="#">Điều khoản hoạt động</a></li>
                        <li><a href="#">Câu hỏi thường gặp</a></li>
                        <li><a href="#">Liên hệ</a></li>
                    </ul>
                </div>
                
                <!-- Cột 2: Về chúng tôi -->
                <div class="col-md-3 col-sm-6 mb-4">
                    <h5>Về chúng tôi</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Giới thiệu công ty</a></li>
                        <li><a href="#">Quy chế hoạt động</a></li>
                        <li><a href="#">Báo giá dịch vụ</a></li>
                        <li><a href="#">Sitemap</a></li>
                    </ul>
                </div>
                
                <!-- Cột 3: Liên hệ -->
                <div class="col-md-3 col-sm-6 mb-4">
                    <h5>Kết nối với chúng tôi</h5>
                    <div class="social-icons mb-3">
                        <a href="#"><i class="fa fa-facebook-official"></i></a>
                        <a href="#"><i class="fa fa-youtube-play text-danger"></i></a>
                        <a href="#"><i class="fa fa-instagram text-warning"></i></a>
                    </div>
                    <p class="mb-1"><i class="fa fa-phone"></i> Hotline: <strong class="text-white">0904.xxx.xxx</strong></p>
                    <p><i class="fa fa-envelope-o"></i> Email: hotro@oto.com.vn</p>
                </div>
                
                <!-- Cột 4: Tải App -->
                <div class="col-md-3 col-sm-6 mb-4">
                    <h5>Tải ứng dụng</h5>
                    <a href="#">
                        <img src="https://oto.com.vn/Content/images/ios_app_v2.png" alt="App Store" class="img-fluid mb-2" style="max-width: 140px;">
                    </a><br>
                    <a href="#">
                        <img src="https://oto.com.vn/Content/images/android_app_v2.png" alt="Google Play" class="img-fluid" style="max-width: 140px;">
                    </a>
                </div>
            </div>
            
            <hr style="border-top: 1px solid #454d55;">
            
            <!-- Bản quyền -->
            <div class="row pt-2">
                <div class="col-md-8">
                    <p class="mb-0">© 2026 Bản quyền hệ thống thuộc về Duy Nguyễn. Mọi thông tin giao dịch tuân thủ quy định pháp luật.</p>
                    <p style="font-size: 12px;">Địa chỉ: Phường Trung Hòa, Quận Cầu Giấy, TP. Hà Nội</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Thư viện Javascript cần thiết cho Bootstrap (dùng full jQuery thay vì slim để hỗ trợ AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <!-- LIVECHAT POPUP (LAB 7) -->
    @auth
        <!-- Khung chat popup -->
        <div id="chat-popup" class="chat-popup" style="display: none;">
            <div class="chat-header">
                <div class="d-flex align-items-center">
                    <span class="mr-2"><i class="fa fa-comments"></i></span>
                    <span>Tư vấn Hỗ trợ Trực tuyến</span>
                </div>
                <button type="button" class="close-btn" id="chat-close" title="Đóng">&times;</button>
            </div>
            <div class="chat-body" id="chat-messages">
                <div class="chat-empty">
                    <i class="fa fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
                    <div>Đang tải tin nhắn...</div>
                </div>
            </div>
            <div class="chat-footer">
                <input type="text" id="chat-input" class="form-control form-control-sm" placeholder="Nhập tin nhắn..." autocomplete="off">
                <button class="btn btn-primary btn-sm ml-2 px-3" id="chat-send" title="Gửi"><i class="fa fa-paper-plane"></i></button>
            </div>
        </div>

        <!-- Nút tròn toggle chat ở góc phải dưới -->
        <div id="chat-toggle" class="chat-toggle" title="Chat với hỗ trợ viên">
            <i class="fa fa-comments" style="font-size: 24px;"></i>
            <span class="badge badge-danger chat-badge" id="chat-badge" style="display: none;">0</span>
        </div>

        <script>
            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var isChatOpen = false;
                var chatPollInterval = null;
                var currentUserId = {{ Auth::id() }};
                var lastMessageCount = 0;

                function scrollToBottom() {
                    var chatBody = document.getElementById('chat-messages');
                    if (chatBody) {
                        chatBody.scrollTop = chatBody.scrollHeight;
                    }
                }

                function formatTime(isoString) {
                    if (!isoString) return '';
                    var d = new Date(isoString);
                    var h = ('0' + d.getHours()).slice(-2);
                    var m = ('0' + d.getMinutes()).slice(-2);
                    return h + ':' + m;
                }

                function loadMessages(shouldScroll) {
                    $.ajax({
                        url: "{{ route('user.chat.messages') }}",
                        method: "GET",
                        dataType: "json",
                        success: function(res) {
                            var messages = Array.isArray(res) ? res : (res.messages || []);
                            var html = '';

                            if (messages.length === 0) {
                                html = '<div class="chat-empty"><i class="fa fa-info-circle fa-2x mb-2 text-info"></i><div>Chào bạn! Hãy gửi tin nhắn cho chúng tôi nếu bạn cần giải đáp hoặc hỗ trợ.</div></div>';
                            } else {
                                messages.forEach(function(msg) {
                                    var isMe = (msg.sender_id == currentUserId);
                                    var msgClass = isMe ? 'user-msg' : 'admin-msg';
                                    var timeStr = formatTime(msg.created_at);

                                    html += '<div class="chat-msg ' + msgClass + '">';
                                    html += $('<div>').text(msg.content).html(); // escape HTML
                                    html += '<span class="chat-msg-time">' + timeStr + '</span>';
                                    html += '</div>';
                                });
                            }

                            $('#chat-messages').html(html);

                            if (shouldScroll || messages.length !== lastMessageCount) {
                                scrollToBottom();
                            }
                            lastMessageCount = messages.length;
                        },
                        error: function(err) {
                            console.error('Lỗi tải tin nhắn:', err);
                        }
                    });
                }

                function sendMessage() {
                    var content = $.trim($('#chat-input').val());
                    if (!content) return;

                    $('#chat-input').val('');
                    $('#chat-send').prop('disabled', true);

                    $.ajax({
                        url: "{{ route('user.chat.send') }}",
                        method: "POST",
                        data: { message: content, content: content },
                        dataType: "json",
                        success: function(res) {
                            $('#chat-send').prop('disabled', false);
                            loadMessages(true);
                        },
                        error: function(err) {
                            $('#chat-send').prop('disabled', false);
                            alert('Không thể gửi tin nhắn. Vui lòng thử lại!');
                        }
                    });
                }

                $('#chat-toggle').click(function() {
                    isChatOpen = !isChatOpen;
                    if (isChatOpen) {
                        $('#chat-popup').fadeIn(200);
                        $('#chat-badge').hide();
                        loadMessages(true);
                        if (!chatPollInterval) {
                            chatPollInterval = setInterval(function() {
                                if (isChatOpen) {
                                    loadMessages(false);
                                }
                            }, 3000);
                        }
                        $('#chat-input').focus();
                    } else {
                        $('#chat-popup').fadeOut(200);
                    }
                });

                $('#chat-close').click(function() {
                    isChatOpen = false;
                    $('#chat-popup').fadeOut(200);
                });

                $('#chat-send').click(function() {
                    sendMessage();
                });

                $('#chat-input').keypress(function(e) {
                    if (e.which === 13) {
                        sendMessage();
                    }
                });

                // Kiểm tra số lượng tin nhắn chưa đọc khi chưa mở hộp chat
                function checkUserUnread() {
                    if (!isChatOpen) {
                        $.ajax({
                            url: "{{ route('user.chat.messages') }}",
                            method: "GET",
                            data: { check_only: 1 },
                            dataType: "json",
                            success: function(res) {
                                var count = res.unread_count || 0;
                                if (count > 0) {
                                    $('#chat-badge').text(count).show();
                                } else {
                                    $('#chat-badge').hide();
                                }
                            }
                        });
                    }
                }

                // Kiểm tra ngay khi tải trang và lặp lại mỗi 8 giây
                checkUserUnread();
                setInterval(checkUserUnread, 8000);
            });
        </script>
    @else
        <!-- Nút tròn toggle chat cho khách vãng lai -->
        <div id="chat-toggle" class="chat-toggle" title="Chat với hỗ trợ viên" onclick="if(confirm('Vui lòng đăng nhập để bắt đầu trò chuyện trực tuyến với Quản trị viên! Bạn có muốn đến trang đăng nhập ngay không?')) { window.location.href = '{{ route('login') }}'; }">
            <i class="fa fa-comments" style="font-size: 24px;"></i>
        </div>
    @endauth
</body>
</html>