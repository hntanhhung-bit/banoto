# BÁO CÁO BÀI TẬP LỚN - PHÁT TRIỂN HỆ THỐNG THƯƠNG MẠI ĐIỆN TỬ
## ĐỀ TÀI: XÂY DỰNG HỆ THỐNG WEBSITE THƯƠNG MẠI ĐIỆN TỬ BÁN VÀ CHO THUÊ Ô TÔ TRỰC TUYẾN

> **TRƯỜNG ĐẠI HỌC TÀI NGUYÊN VÀ MÔI TRƯỜNG HÀ NỘI**  
> **KHOA CÔNG NGHỆ THÔNG TIN**  
> **Học phần:** Phát triển hệ thống thương mại điện tử  
> **Giảng viên hướng dẫn:** ThS. Phạm Hồng Hải  
> **Lớp:** DH13C2  
> **Hà Nội – 2026**

---

### THÀNH VIÊN NHÓM THỰC HIỆN
| STT | Họ và tên | Mã sinh viên | Lớp | Vai trò & Nhiệm vụ chính |
| :---: | :--- | :---: | :---: | :--- |
| 1 | **Hoàng Ngọc Thi** | *Trưởng nhóm* | DH13C2 | Kiến trúc hệ thống, Quản trị Admin, Cổng thanh toán SePay/MoMo, Viết báo cáo tổng hợp |
| 2 | **Nguyễn Việt Hòa** | Thành viên | DH13C2 | Phân hệ Khách hàng, Giỏ hàng & Checkout GHN Express, Xác thực OTP Email, Viết báo cáo |
| 3 | **Nguyễn Quốc Anh** | Thành viên | DH13C2 | Đặt lịch lái thử Showroom, Dịch vụ thuê xe ô tô có tài xế/tự lái, Bàn giao cọc xe, Live Chat |
| 4 | **Nguyễn Viết Hoàng Anh**| Thành viên | DH13C2 | Phân hệ Đối tác ký gửi Partner, Báo cáo biểu đồ doanh thu, Kiểm thử Test Cases, Tối ưu bảo mật |

---

### 1. GIỚI THIỆU DỰ ÁN
Website Thương mại điện tử Bán và Cho thuê Ô tô trực tuyến là nền tảng số hóa toàn diện quy trình kinh doanh của showroom ô tô hiện đại kết hợp mô hình kinh tế chia sẻ (ký gửi xe):
- **Bán xe trực tuyến:** Trưng bày các dòng xe ô tô (Sedan, SUV, Crossover, MPV...), hiển thị thông số chi tiết, tùy chọn màu sắc xe thực tế, đặt mua trực tuyến.
- **Dịch vụ Lái thử (Test-Drive Appointment):** Khách hàng đặt lịch hẹn trải nghiệm xe trực tiếp tại showroom với nhân viên tư vấn.
- **Dịch vụ Cho thuê xe (Car Rental):** Hỗ trợ thuê xe tự lái hoặc có tài xế riêng, tính số ngày linh hoạt, quản lý tiền đặt cọc và quy trình bàn giao xe (Handover Security).
- **Mô hình Đối tác ký gửi (Partner Consignment):** Cá nhân hoặc doanh nghiệp có xe nhàn rỗi có thể đăng ký làm đối tác, ký gửi xe để bán hoặc cho thuê, hưởng hoa hồng minh bạch.
- **Tích hợp dịch vụ bên thứ ba hiện đại:**
  - **Giao Hàng Nhanh (GHN Express):** Đồng bộ địa giới hành chính Việt Nam (Tỉnh/Huyện/Xã), tự động tính cước vận chuyển giao xe tận nhà.
  - **Cổng thanh toán MoMo:** Thanh toán quét mã QR qua ví điện tử MoMo với chữ ký số bảo mật.
  - **Cổng thanh toán SePay (VietQR):** Tự động sinh mã VietQR động theo đơn hàng, nhận Webhook ngân hàng khớp tiền ngay lập tức.
  - **Google Mailer OAuth & Mailtrap:** Gửi mã xác thực OTP kích hoạt tài khoản và hóa đơn điện tử tự động.
  - **Hệ thống Chat trực tuyến:** Trao đổi, tư vấn kỹ thuật xe trực tiếp giữa Khách hàng và Showroom.

---

### 2. CÔNG NGHỆ SỬ DỤNG
- **Backend:** Laravel 11 Framework, PHP 8.2 (kiến trúc MVC chuẩn mực, Eloquent ORM, Middleware, Service Container).
- **Frontend:** Blade Template Engine, HTML5, CSS3, JavaScript (ES6+), Bootstrap 5, AJAX realtime.
- **Cơ sở dữ liệu:** MySQL 8.0+ / MariaDB.
- **Công cụ phát triển:** Visual Studio Code, Git/GitHub, Composer, XAMPP, Postman.

---

### 3. CẤU TRÚC THƯ MỤC NỘP BÀI
Theo đúng quy định của môn học, sản phẩm nộp của nhóm gồm đầy đủ:
- **`BaoCao.pdf`:** Bản báo cáo bài tập lớn chính thức (đúng khổ A4, lề 3-2-2-2 cm, Font Times New Roman 13, giãn dòng 1.35, dung lượng 25-40 trang, đầy đủ hình và bảng được nhận xét chi tiết).
- **`BaoCao.docx`:** File Word gốc chuẩn định dạng để giảng viên và hội đồng thẩm định.
- **`Slide.pdf` (và `Slide.pptx`):** Slide thuyết trình bảo vệ đề tài trực quan, chuyên nghiệp.
- **`SourceCode/`:** Toàn bộ mã nguồn dự án Laravel 11.
- **`Data/`:** Cơ sở dữ liệu `ecommerce2024.sql` và tài liệu từ điển dữ liệu `DATA_DICTIONARY.md`.
- **`Output/`:** Ảnh chụp màn hình kết quả thực tế các chức năng, biểu đồ báo cáo, chứng từ hóa đơn và kết quả kiểm thử.
- **`README.md`:** Bản hướng dẫn này.

---

### 4. HƯỚNG DẪN CÀI ĐẶT VÀ KHỞI CHẠY HỆ THỐNG

#### Bước 1: Yêu cầu môi trường
- PHP >= 8.2 (đã bật extension: `pdo_mysql`, `openssl`, `mbstring`, `curl`, `zip`)
- Composer >= 2.x
- MySQL >= 8.0 hoặc MariaDB (khuyến nghị chạy qua XAMPP)
- Web Server: Apache (XAMPP) hoặc PHP Built-in Server

#### Bước 2: Clone hoặc mở thư mục mã nguồn
```bash
cd c:\xampp\htdocs\lar_vidu1
```

#### Bước 3: Cài đặt các gói phụ thuộc (Composer)
```bash
composer install
```

#### Bước 4: Cấu hình tệp môi trường `.env`
Sao chép `.env.example` thành `.env` (nếu chưa có) và cập nhật thông số kết nối CSDL:
```env
APP_NAME="Oto.com.vn System"
APP_ENV=local
APP_KEY=base64:zTA8+h0yBbinXQenO3NtXay/kqCV+miWAZCbDIGzMgg=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307          # Lưu ý: Cấu hình cổng MySQL của bạn (3306 hoặc 3307)
DB_DATABASE=ecommerce2024
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=hntanhhung@gmail.com
MAIL_PASSWORD=cwjubvygdpjnvmbe
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=hntanhhung@gmail.com
MAIL_FROM_NAME="Oto.com.vn System"
```

#### Bước 5: Nhập cơ sở dữ liệu
- Mở **phpMyAdmin** (hoặc MySQL Workbench / Navicat), tạo database có tên: `ecommerce2024`
- Import tệp dữ liệu có sẵn tại: `Data/ecommerce2024.sql` (hoặc chạy lệnh: `php artisan migrate --seed`)

#### Bước 6: Khởi chạy máy chủ ảo
```bash
php artisan serve
```
Hệ thống sẽ chạy tại địa chỉ: `http://127.0.0.1:8000`

---

### 5. DANH SÁCH TÀI KHOẢN TRẢI NGHIỆM HỆ THỐNG
Hệ thống đã phân quyền 3 nhóm người dùng rõ rệt:

| Vai trò | Email đăng nhập | Mật khẩu mặc định | Quyền hạn và phạm vi sử dụng |
| :--- | :--- | :---: | :--- |
| **Quản trị viên (Admin)** | `admin@gmail.com` | `123456` | Toàn quyền kiểm soát danh mục xe, đơn hàng, hợp đồng thuê xe, lịch lái thử, duyệt đối tác, đối soát SePay/MoMo, xem báo cáo doanh thu |
| **Đối tác ký gửi (Partner)**| `partner@gmail.com` | `123456` | Quản lý danh sách xe ký gửi, đăng ký cho thuê/bán xe, xem báo cáo doanh thu & hoa hồng đối tác |
| **Khách hàng (User)** | `khachhang@gmail.com` | `123456` | Tìm kiếm, xem chi tiết xe, chọn màu sắc, đặt lịch lái thử, đặt thuê xe kèm tài xế, thanh toán VietQR / MoMo, chat tư vấn |

---

### 6. CÁC TÍNH NĂNG CHÍNH ĐÃ HOÀN THIỆN
1. **Phân hệ Khách hàng:**
   - Đăng ký tài khoản an toàn với xác thực OTP qua Email.
   - Tìm kiếm xe đa tiêu chí: hãng xe, dòng xe, mức giá, tình trạng xe mới/cũ.
   - Xem thông số kỹ thuật xe chuẩn mực, chọn màu sắc xe tương ứng.
   - Đặt lịch hẹn lái thử xe tại showroom gần nhất.
   - Thuê xe ô tô tự lái hoặc thuê có tài xế riêng, quản lý tiền cọc và phiếu voucher nhận xe.
   - Giỏ hàng & thanh toán online tự động: MoMo QR code hoặc SePay VietQR (nhận diện tiền vào tức thì không cần can thiệp thủ công).
   - Tích hợp vận chuyển GHN: Tính phí ship xe tận nhà theo từng địa bàn xã/huyện.
   - Tra cứu hành trình đơn hàng và lịch sử thuê xe.
   - Chat trực tiếp với tư vấn viên Showroom.

2. **Phân hệ Đối tác (Partner):**
   - Đăng ký trở thành đối tác, upload ảnh CCCD và Giấy phép lái xe.
   - Đăng xe ký gửi (bán hoặc cho thuê) kèm hình ảnh, màu sắc, giá thuê, phụ phí tài xế.
   - Quản lý trạng thái các xe đã duyệt và chờ duyệt.
   - Theo dõi hợp đồng thuê xe của đối tác, xác nhận bàn giao và nhận tiền hoa hồng giới thiệu.
   - Xem báo cáo doanh thu đối tác trực quan theo tuần/tháng/năm.

3. **Phân hệ Quản trị viên (Admin):**
   - Dashboard tổng quan: Doanh số bán xe, doanh thu cho thuê, tổng số xe, khách hàng mới.
   - Quản lý danh mục và kho xe ô tô: cập nhật giá, tồn kho, màu sắc.
   - Quản lý đơn hàng: xác nhận, tạo mã vận đơn GHN, xử lý hoàn tiền (Refund).
   - Quản lý lịch hẹn lái thử xe: phân công nhân viên chăm sóc, ghi nhận kết quả trải nghiệm.
   - Quản lý hợp đồng cho thuê xe: kiểm tra tình trạng xe khi xuất và nhận (Handover Security), hoàn trả tiền cọc.
   - Duyệt hồ sơ đối tác ký gửi xe và kiểm định chất lượng xe ký gửi.
   - Quản lý đối soát giao dịch thanh toán MoMo và SePay Webhook.
   - Hệ thống báo cáo thống kê và biểu đồ phân tích trực quan.
