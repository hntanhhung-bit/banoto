# TỪ ĐIỂN DỮ LIỆU CƠ SỞ DỮ LIỆU (DATA DICTIONARY)
## Hệ Thống Website Thương Mại Điện Tử Bán Và Cho Thuê Ô Tô Trực Tuyến
- **Hệ quản trị CSDL:** MySQL 8.0+ / MariaDB 10.4+
- **Charset / Collation:** `utf8mb4_unicode_ci`
- **Database Name:** `ecommerce2024`

---

### 1. Bảng `users` (Quản lý người dùng, tài khoản và phân quyền)
| Tên cột | Kiểu dữ liệu | Khóa / Ràng buộc | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Định danh duy nhất người dùng |
| `name` | VARCHAR(255) | NOT NULL | Họ và tên người dùng |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | Địa chỉ email đăng nhập |
| `email_verified_at` | TIMESTAMP | NULLABLE | Thời điểm xác thực email thành công |
| `verification_otp` | VARCHAR(6) | NULLABLE | Mã OTP gồm 6 chữ số gửi qua email |
| `verification_otp_expires_at` | TIMESTAMP | NULLABLE | Thời gian hết hạn của mã OTP (5 phút) |
| `password` | VARCHAR(255) | NOT NULL | Mật khẩu băm Bcrypt an toàn |
| `role` | ENUM | NOT NULL, DEFAULT 'user' | Phân quyền: `user` (Khách hàng), `partner` (Đối tác), `admin` (Quản trị viên) |
| `phone` | VARCHAR(20) | NULLABLE | Số điện thoại liên hệ |
| `address` | TEXT | NULLABLE | Địa chỉ liên hệ / giao hàng mặc định |
| `partner_status` | ENUM | NULLABLE | Trạng thái đối tác: `pending`, `approved`, `rejected` |
| `cccd_number` | VARCHAR(20) | NULLABLE | Số Căn cước công dân của đối tác ký gửi |
| `driving_license` | VARCHAR(255) | NULLABLE | Đường dẫn ảnh giấy phép lái xe đối tác |
| `created_at`, `updated_at` | TIMESTAMP | NULLABLE | Thời gian khởi tạo và cập nhật bản ghi |

---

### 2. Bảng `categories` (Danh mục phân loại dòng xe ô tô)
| Tên cột | Kiểu dữ liệu | Khóa / Ràng buộc | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Mã định danh danh mục |
| `name` | VARCHAR(255) | NOT NULL | Tên dòng xe (Sedan, SUV, Hatchback, MPV, Bán tải) |
| `slug` | VARCHAR(255) | UNIQUE, NOT NULL | Đường dẫn thân thiện SEO |
| `description` | TEXT | NULLABLE | Mô tả chi tiết về phân khúc và đặc điểm dòng xe |
| `created_at`, `updated_at` | TIMESTAMP | NULLABLE | Thời gian tạo và cập nhật |

---

### 3. Bảng `products` (Thông tin ô tô thương mại và cho thuê)
| Tên cột | Kiểu dữ liệu | Khóa / Ràng buộc | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Mã định danh xe ô tô |
| `category_id` | BIGINT UNSIGNED | FK -> `categories(id)` | Khóa ngoại liên kết danh mục dòng xe |
| `name` | VARCHAR(255) | NOT NULL | Tên thương mại của xe (VD: Toyota Camry 2.5Q) |
| `brand` | VARCHAR(100) | NOT NULL | Hãng sản xuất xe (Toyota, Honda, Mazda, Hyundai...) |
| `price` | DECIMAL(15,2) | NOT NULL | Giá niêm yết bán xe (VNĐ) |
| `quantity` | INT | NOT NULL, DEFAULT 0 | Số lượng xe tồn kho sẵn sàng bàn giao |
| `description` | LONGTEXT | NULLABLE | Chi tiết thông số động cơ, hộp số, nội ngoại thất |
| `image` | VARCHAR(255) | NULLABLE | Ảnh đại diện chính thức của xe |
| `is_rental` | TINYINT(1) | DEFAULT 0 | Cờ đánh dấu xe có phục vụ cho thuê hay không |
| `rental_price_per_day` | DECIMAL(15,2) | NULLABLE | Đơn giá cho thuê xe theo ngày (VNĐ/ngày) |
| `has_driver_service` | TINYINT(1) | DEFAULT 0 | Hỗ trợ tùy chọn có tài xế riêng phục vụ |
| `driver_fee_per_day` | DECIMAL(15,2) | NULLABLE | Phụ phí tài xế riêng tính theo ngày |
| `partner_id` | BIGINT UNSIGNED | FK -> `users(id)`, NULLABLE | Khóa ngoại xác định xe thuộc đối tác ký gửi nào |
| `approval_status` | ENUM | DEFAULT 'approved' | Trạng thái duyệt: `pending`, `approved`, `rejected` |
| `created_at`, `updated_at` | TIMESTAMP | NULLABLE | Thời gian tạo và cập nhật |

---

### 4. Bảng `product_colors` (Tùy chọn phiên bản màu sắc xe)
| Tên cột | Kiểu dữ liệu | Khóa / Ràng buộc | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Mã tùy chọn màu |
| `product_id` | BIGINT UNSIGNED | FK -> `products(id)` | Khóa ngoại trỏ đến xe ô tô |
| `color_name` | VARCHAR(100) | NOT NULL | Tên màu sắc (Trắng Ngọc Trai, Đen Ánh Kim, Đỏ Ruby...) |
| `color_code` | VARCHAR(20) | NULLABLE | Mã mã màu HEX (VD: `#FFFFFF`, `#000000`, `#B91C1C`) |
| `image` | VARCHAR(255) | NULLABLE | Hình ảnh xe tương ứng với màu sắc lựa chọn |
| `created_at`, `updated_at` | TIMESTAMP | NULLABLE | Thời gian tạo và cập nhật |

---

### 5. Bảng `orders` (Quản lý đơn hàng mua xe và giao vận)
| Tên cột | Kiểu dữ liệu | Khóa / Ràng buộc | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Mã đơn hàng trong hệ thống |
| `order_code` | VARCHAR(50) | UNIQUE, NOT NULL | Mã tra cứu đơn hàng (VD: `ORD-2026-XXXX`) |
| `user_id` | BIGINT UNSIGNED | FK -> `users(id)` | Khách hàng thực hiện đặt mua |
| `total_amount` | DECIMAL(15,2) | NOT NULL | Tổng giá trị đơn hàng (xe + phí ship) |
| `payment_method` | ENUM | NOT NULL | Phương thức: `cod`, `momo`, `sepay_qr` |
| `payment_status` | ENUM | DEFAULT 'unpaid' | Trạng thái thanh toán: `unpaid`, `paid`, `refunded` |
| `order_status` | ENUM | DEFAULT 'pending' | Trạng thái: `pending`, `confirmed`, `shipping`, `completed`, `cancelled` |
| `delivery_type` | ENUM | DEFAULT 'showroom' | Hình thức nhận xe: `showroom` hoặc `delivery` (giao tận nhà) |
| `ghn_order_code` | VARCHAR(100) | NULLABLE | Mã vận đơn được cấp bởi API Giao Hàng Nhanh (GHN) |
| `shipping_fee` | DECIMAL(12,2) | DEFAULT 0.00 | Cước phí vận chuyển tính từ GHN API |
| `recipient_name` | VARCHAR(255) | NOT NULL | Tên người nhận xe |
| `recipient_phone` | VARCHAR(20) | NOT NULL | Số điện thoại nhận xe |
| `recipient_address` | TEXT | NOT NULL | Địa chỉ chi tiết nhận xe (Tỉnh, Huyện, Xã) |
| `created_at`, `updated_at` | TIMESTAMP | NULLABLE | Thời gian tạo đơn và cập nhật trạng thái |

---

### 6. Bảng `order_items` (Chi tiết các xe trong đơn hàng)
| Tên cột | Kiểu dữ liệu | Khóa / Ràng buộc | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Mã chi tiết đơn hàng |
| `order_id` | BIGINT UNSIGNED | FK -> `orders(id)` | Khóa ngoại liên kết đơn hàng chính |
| `product_id` | BIGINT UNSIGNED | FK -> `products(id)` | Khóa ngoại liên kết xe ô tô đã mua |
| `color_id` | BIGINT UNSIGNED | FK -> `product_colors(id)` | Phiên bản màu sắc khách hàng đã lựa chọn |
| `price` | DECIMAL(15,2) | NOT NULL | Đơn giá thực tế tại thời điểm mua |
| `quantity` | INT | NOT NULL, DEFAULT 1 | Số lượng xe đặt mua |
| `created_at`, `updated_at` | TIMESTAMP | NULLABLE | Thời gian tạo và cập nhật |

---

### 7. Bảng `appointments` (Quản lý lịch hẹn xem xe và lái thử tại Showroom)
| Tên cột | Kiểu dữ liệu | Khóa / Ràng buộc | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Mã lịch hẹn lái thử |
| `user_id` | BIGINT UNSIGNED | FK -> `users(id)` | Khách hàng đăng ký lái thử |
| `product_id` | BIGINT UNSIGNED | FK -> `products(id)` | Dòng xe khách hàng muốn trải nghiệm lái thử |
| `appointment_date` | DATETIME | NOT NULL | Ngày và giờ hẹn trải nghiệm tại showroom |
| `showroom_location`| VARCHAR(255) | NOT NULL | Địa điểm showroom hẹn tiếp đón |
| `status` | ENUM | DEFAULT 'pending' | Trạng thái: `pending`, `confirmed`, `completed`, `cancelled` |
| `notes` | TEXT | NULLABLE | Yêu cầu đặc biệt của khách (tư vấn trả góp, thủ tục ra biển...) |
| `staff_notes` | TEXT | NULLABLE | Ghi chú của nhân viên tư vấn sau khi khách lái thử |
| `referral_commission`| DECIMAL(12,2)| DEFAULT 0.00 | Hoa hồng ghi nhận nếu xe ký gửi được khách chốt mua |
| `created_at`, `updated_at` | TIMESTAMP | NULLABLE | Thời gian tạo và cập nhật |

---

### 8. Bảng `rentals` (Quản lý hợp đồng và quy trình cho thuê xe ô tô)
| Tên cột | Kiểu dữ liệu | Khóa / Ràng buộc | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Mã hợp đồng thuê xe |
| `rental_code` | VARCHAR(50) | UNIQUE, NOT NULL | Mã hợp đồng / phiếu thuê xe (VD: `RNT-2026-XXXX`) |
| `user_id` | BIGINT UNSIGNED | FK -> `users(id)` | Khách hàng thuê xe |
| `product_id` | BIGINT UNSIGNED | FK -> `products(id)` | Xe ô tô được thuê |
| `start_date` | DATE | NOT NULL | Ngày bắt đầu nhận xe |
| `end_date` | DATE | NOT NULL | Ngày dự kiến trả xe |
| `total_days` | INT | NOT NULL | Tổng số ngày thuê xe |
| `with_driver` | TINYINT(1) | DEFAULT 0 | 1: Thuê có tài xế riêng; 0: Thuê tự lái |
| `daily_rate` | DECIMAL(15,2) | NOT NULL | Giá thuê xe / ngày tại thời điểm tạo đơn |
| `driver_fee` | DECIMAL(15,2) | DEFAULT 0.00 | Tổng chi phí phụ thu tài xế (nếu có) |
| `security_deposit` | DECIMAL(15,2) | NOT NULL | Tiền đặt cọc thế chân trách nhiệm tài sản |
| `total_cost` | DECIMAL(15,2) | NOT NULL | Tổng chi phí thuê = (Giá xe + Phí tài xế) * Số ngày + Cọc |
| `status` | ENUM | DEFAULT 'pending' | Trạng thái: `pending`, `confirmed`, `active`, `returned`, `cancelled` |
| `handover_status` | ENUM | DEFAULT 'pending' | Kiểm tra bàn giao: `pending`, `handed_over`, `inspected_ok`, `damaged` |
| `handover_notes` | TEXT | NULLABLE | Ghi chú tình trạng xe lúc giao (odo, xước sơn, mức xăng...) |
| `deposit_refunded`| TINYINT(1) | DEFAULT 0 | Đã hoàn trả tiền đặt cọc cho khách hay chưa |
| `created_at`, `updated_at` | TIMESTAMP | NULLABLE | Thời gian tạo và cập nhật hợp đồng |

---

### 9. Bảng `payment_transactions` (Nhật ký giao dịch tài chính & webhook)
| Tên cột | Kiểu dữ liệu | Khóa / Ràng buộc | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Mã giao dịch tài chính |
| `transaction_code` | VARCHAR(100) | UNIQUE, NOT NULL | Mã giao dịch bên cổng thanh toán (MoMo TransID hoặc SePay ID) |
| `order_id` | BIGINT UNSIGNED | FK -> `orders(id)`, NULLABLE | Khóa ngoại liên kết đơn hàng mua xe |
| `rental_id` | BIGINT UNSIGNED | FK -> `rentals(id)`, NULLABLE | Khóa ngoại liên kết đơn thuê xe |
| `amount` | DECIMAL(15,2) | NOT NULL | Số tiền thực tế được thanh toán |
| `payment_gateway` | ENUM | NOT NULL | Cổng xử lý: `momo`, `sepay_vietqr`, `cash_cod` |
| `status` | ENUM | DEFAULT 'pending' | Kết quả: `pending`, `successful`, `failed`, `refunded` |
| `payload` | LONGTEXT | NULLABLE | Dữ liệu Webhook IPN nguyên bản phục vụ đối soát |
| `created_at`, `updated_at` | TIMESTAMP | NULLABLE | Thời gian ghi nhận giao dịch |

---

### 10. Bảng `messages` (Trò chuyện trực tuyến thời gian thực giữa Khách và Showroom)
| Tên cột | Kiểu dữ liệu | Khóa / Ràng buộc | Mô tả chức năng |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Mã định danh tin nhắn |
| `sender_id` | BIGINT UNSIGNED | FK -> `users(id)` | Người gửi tin nhắn |
| `receiver_id` | BIGINT UNSIGNED | FK -> `users(id)` | Người nhận tin nhắn (Khách hoặc Admin) |
| `message` | TEXT | NOT NULL | Nội dung trao đổi, tư vấn xe |
| `is_read` | TINYINT(1) | DEFAULT 0 | Đánh dấu tin nhắn đã được đọc hay chưa |
| `created_at`, `updated_at` | TIMESTAMP | NULLABLE | Thời gian gửi tin nhắn |
