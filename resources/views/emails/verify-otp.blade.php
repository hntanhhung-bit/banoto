<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác thực OTP - Auto Car</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin: auto;">
        <!-- Header -->
        <tr>
            <td align="center" style="background: linear-gradient(135deg, #005fb7, #0084ff); padding: 30px 20px;">
                <h1 style="color: #ffffff; margin: 0; font-size: 26px; text-transform: uppercase; letter-spacing: 1px;">
                    🚗 AUTO CAR VIETNAM
                </h1>
                <p style="color: #dbeafe; margin: 8px 0 0; font-size: 14px;">BÊN THỨ 3 • ĐẶT HỘ XEM & THUÊ XE AN TOÀN</p>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding: 35px 30px;">
                <h2 style="color: #1e293b; font-size: 20px; margin-top: 0;">Xin chào {{ $userName }},</h2>
                <p style="color: #475569; font-size: 15px; line-height: 1.6;">
                    Cảm ơn bạn đã đăng ký tài khoản tại <strong>Auto Car</strong>. Để hoàn tất quy trình kích hoạt và bảo mật tài khoản, vui lòng sử dụng mã xác thực (OTP) dưới đây:
                </p>

                <!-- OTP Code Display -->
                <div style="background-color: #f0f7ff; border: 2px dashed #005fb7; border-radius: 10px; padding: 20px; text-align: center; margin: 25px 0;">
                    <span style="display: block; font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; margin-bottom: 8px;">
                        MÃ XÁC THỰC OTP CỦA BẠN
                    </span>
                    <span style="font-size: 38px; font-weight: bold; letter-spacing: 8px; color: #005fb7; font-family: 'Courier New', Courier, monospace;">
                        {{ $otp }}
                    </span>
                    <span style="display: block; font-size: 12px; color: #ef4444; margin-top: 10px; font-weight: bold;">
                        ⏰ Mã có hiệu lực trong vòng 15 phút
                    </span>
                </div>

                <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                    <strong>Hướng dẫn:</strong> Vui lòng quay lại trình duyệt web và nhập 6 chữ số này vào ô xác thực tài khoản để bắt đầu sử dụng đầy đủ các dịch vụ thuê xe, đặt lịch xem xe.
                </p>

                <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 15px; border-radius: 4px; margin-top: 20px;">
                    <p style="color: #991b1b; font-size: 13px; margin: 0; line-height: 1.5;">
                        ⚠️ <strong>Lưu ý bảo mật:</strong> Không cung cấp mã OTP này cho bất kỳ ai, kể cả nhân viên hỗ trợ để tránh rủi ro mất tài khoản.
                    </p>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td align="center" style="background-color: #f8fafc; padding: 20px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px;">
                <p style="margin: 0 0 5px;">Hệ thống Sàn giao dịch & Dịch vụ Ô tô Auto Car</p>
                <p style="margin: 0;">Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.</p>
            </td>
        </tr>
    </table>
</body>
</html>
