# Hướng Dẫn Nghiệp Vụ & Báo Cáo Hoàn Thành: Duyệt Đối Tác, Kiểm Định Xe & Hoa Hồng 10% Sàn

Hệ thống đã triển khai và kiểm thử hoàn tất 3 phân hệ nghiệp vụ theo đúng yêu cầu:
1. **Duyệt hồ sơ Đối tác (Partner Approval):** Người dùng muốn lên Partner phải nộp hồ sơ pháp lý (Đại diện, CCCD, MST, ảnh GPKD & ảnh Showroom). Admin thẩm định và duyệt.
2. **Kiểm định & Duyệt thông tin xe cho thuê (Car Inspection Approval):** Đối tác khai báo Biển số (BKS), Năm sản xuất, Tình trạng kỹ thuật, Đăng kiểm & Bảo hiểm. Xe ở trạng thái Chờ duyệt (Pending) và chỉ hiển thị trên sàn khi Admin kiểm tra đạt chuẩn.
3. **Nghĩa vụ chuyển 10% hoa hồng sàn trước khi nghiệm thu trả xe (10% Platform Fee):** Đối tác Showroom bắt buộc phải quét mã VietQR chuyển 10% tổng tiền thuê xe cho sàn và nhập mã giao dịch ngân hàng trước khi hệ thống cho phép hoàn tất nghiệm thu và gửi đề xuất hoàn cọc cho khách.

---

## I. Chi Tiết Các Thay Đổi & Cấu Trúc Đã Triển Khai

### 1. Cơ sở dữ liệu (Migrations & Models)
- **`database/migrations/2026_09_21_150000_add_partner_verification_and_car_approval.php`**:
  - Bảng `users`: Bổ sung `partner_status` (`pending`, `approved`, `rejected`), `representative_name`, `id_card_number`, `tax_code`, `business_license_image`, `id_card_image`, `partner_applied_at`, `partner_approved_at`, `partner_reject_reason`.
  - Bảng `products`: Bổ sung `approval_status` (`pending`, `approved`, `rejected`), `car_plate`, `car_year`, `car_condition`, `admin_feedback`, `approved_at`.
  - Bảng `rentals`: Bổ sung `partner_commission_fee`, `partner_commission_status`, `partner_commission_proof`, `partner_commission_paid_at`.
- **`database/migrations/2026_09_21_160000_change_message_to_text_in_payment_transactions.php`**: Chuyển cột `message` sang `TEXT` để lưu trữ nhật ký đối soát không giới hạn độ dài.
- **Models:**
  - `App\Models\User`: Đã cập nhật `$fillable`, phương thức `isPartner()` (kiểm tra `partner_status === 'approved'`), và `isPendingPartner()`.
  - `App\Models\Product`: Đã cập nhật `$fillable` cho `car_plate`, `car_year`, `car_condition`, `approval_status`, `admin_feedback`.
  - `App\Models\Rental`: Đã cập nhật `$fillable` cho `partner_commission_fee`, `partner_commission_status`, `partner_commission_proof`.

---

### 2. Phân hệ 1: Quy trình Đăng ký & Thẩm định Đối tác (Partner Approval)

#### 2.1. Đăng ký & Nộp hồ sơ đối tác
- **Giao diện:** [`resources/views/partner/register.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/partner/register.blade.php)
  - Đã bổ sung các trường:
    - Họ và tên người đại diện pháp luật (`representative_name`).
    - Số CMND / CCCD người đại diện (`id_card_number`).
    - Mã số thuế / GPKD (`tax_code`).
    - Tải lên ảnh chụp Giấy phép đăng ký kinh doanh xe (`business_license_image`).
    - Tải lên ảnh chụp CCCD hoặc ảnh mặt bằng Showroom/Bãi xe (`id_card_image`).
- **Xử lý đăng ký:** [`PartnerDashboardController@processRegister`](file:///c:/xampp/htdocs/lar_vidu1/app/Http/Controllers/Partner/PartnerDashboardController.php)
  - Lưu file tài liệu vào thư mục `public/uploads/partner_docs/`.
  - Khởi tạo tài khoản với `role = 'user'`, `partner_status = 'pending'`, `partner_applied_at = now()`.
  - Chưa cấp quyền Partner ngay mà chuyển hướng đến trang [`partner.pending`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/partner/pending.blade.php).
- **Middleware bảo vệ:** [`PartnerMiddleware`](file:///c:/xampp/htdocs/lar_vidu1/app/Http/Middleware/PartnerMiddleware.php)
  - Nếu tài khoản có `partner_status = 'pending'`, tự động chuyển hướng về trang `partner.pending` thông báo hồ sơ đang chờ xét duyệt.
  - Nếu bị từ chối (`rejected`), hiển thị lý do từ chối kèm hướng dẫn bổ sung.
  - Chỉ khi `partner_status = 'approved'` mới được vào Kênh Quản Trị Đối tác.

#### 2.2. Admin thẩm định & Duyệt đối tác
- **Controller:** [`App\Http\Controllers\Admin\PartnerApprovalController`](file:///c:/xampp/htdocs/lar_vidu1/app/Http/Controllers/Admin/PartnerApprovalController.php)
  - `index`: Danh sách hồ sơ kèm bộ lọc Tabs (Tất cả, ⏳ Chờ duyệt, ✓ Đã duyệt, ✗ Bị từ chối).
  - `approve`: Chuyển `partner_status = 'approved'`, nâng cấp `role = 'partner'`, ghi nhận thời gian duyệt `partner_approved_at = now()`.
  - `reject`: Chuyển `partner_status = 'rejected'`, ghi nhận lý do từ chối `partner_reject_reason`.
- **Giao diện:** [`resources/views/admin/partners/index.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/admin/partners/index.blade.php)
  - Bảng danh sách chi tiết: Tên Showroom, Người đại diện, Số CCCD, MST, Nút xem nhanh ảnh GPKD và ảnh CCCD.
  - Nút "Duyệt Đối Tác" và Modal "Từ chối" kèm ô nhập lý do.
- **Menu điều hướng Admin:** [`resources/views/layouts/admin.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/layouts/admin.blade.php) đã tích hợp menu "Duyệt Đối tác" kèm badge số lượng hồ sơ đang chờ duyệt.

---

### 3. Phân hệ 2: Kiểm định & Duyệt thông tin xe cho thuê (Car Inspection Approval)

#### 3.1. Đối tác đăng & chỉnh sửa xe
- **Giao diện:**
  - [`resources/views/partner/cars/create.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/partner/cars/create.blade.php)
  - [`resources/views/partner/cars/edit.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/partner/cars/edit.blade.php)
  - Bổ sung các trường bắt buộc:
    - Biển số xe (`car_plate`, VD: 30K-888.88).
    - Năm sản xuất / Đời xe (`car_year`, VD: 2024).
    - Tình trạng kỹ thuật, Đăng kiểm & Bảo hiểm (`car_condition`).
- **Logic lưu:** [`PartnerDashboardController@storeCar`](file:///c:/xampp/htdocs/lar_vidu1/app/Http/Controllers/Partner/PartnerDashboardController.php)
  - Xe mới tạo mặc định có `approval_status = 'pending'`.
  - Thông báo rõ ràng: "Xe đang ở trạng thái Chờ kiểm định. Ban Quản Trị sàn sẽ phê duyệt trước khi hiển thị cho khách thuê."
- **Kênh đối tác:** [`resources/views/partner/cars.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/partner/cars.blade.php)
  - Gắn badge trạng thái kiểm duyệt: `Chờ Admin duyệt` (vàng), `Đã duyệt sàn` (xanh), `Bị từ chối` (đỏ kèm lý do).
- **Bộ lọc công khai khách hàng:**
  - [`WelcomeController.php`](file:///c:/xampp/htdocs/lar_vidu1/app/Http/Controllers/WelcomeController.php) và [`HomeController.php`](file:///c:/xampp/htdocs/lar_vidu1/app/Http/Controllers/HomeController.php) chỉ hiển thị xe thỏa mãn: `whereNull('partner_id')->orWhere('approval_status', 'approved')`.
  - [`ProductController@show_normal`](file:///c:/xampp/htdocs/lar_vidu1/app/Http/Controllers/ProductController.php): Chặn khách vãng lai truy cập xem chi tiết xe của đối tác khi xe chưa được duyệt.

#### 3.2. Admin kiểm tra tình trạng xe & Phê duyệt
- **Giao diện:** [`resources/views/admin/products/index.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/admin/products/index.blade.php)
  - Tab lọc: "Xe đối tác chờ kiểm định & duyệt (X)" với badge đỏ cảnh báo khi có xe mới.
  - Hiển thị Biển số xe (BKS), Đời xe và nút "Xem tình trạng kỹ thuật" mở Modal chi tiết.
  - Thao tác trực tiếp: Nút "Duyệt xe" (POST `admin.products.approveCar`) và Modal "Từ chối xe" (POST `admin.products.rejectCar` kèm ô nhập lý do phản hồi cho đối tác).

---

### 4. Phân hệ 3: Nghĩa vụ chuyển 10% hoa hồng sàn trước khi nghiệm thu xe

#### 4.1. Modal Nghiệm thu trả xe của Đối tác
- **Giao diện:** [`resources/views/partner/rentals.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/partner/rentals.blade.php)
  - Trong Modal "Biên bản nghiệm thu trả xe & Đề xuất hoàn cọc":
    - Tính toán tự động: Tổng tiền thuê = `$totalRentalFee`.
    - Phí hoa hồng sàn 10% = `round($totalRentalFee * 0.10)`.
    - Showroom thực thu 90% = `$totalRentalFee - commission`.
    - **Hộp thanh toán VietQR chuyển khoản hoa hồng sàn:**
      - Mã QR VietQR tự động sinh sẵn số tiền và cú pháp chuyển: `HH10 [Mã hợp đồng]`.
      - STK nhận hoa hồng: **0348270102** - Ngân hàng **MB Bank** - Chủ TK: **SÀN AUTOCAR VIỆT NAM**.
    - **Ô nhập bắt buộc:** `commission_payment_proof` ("Mã giao dịch ngân hàng / Mã FT...").
    - **Hộp kiểm xác nhận bắt buộc:** `confirm_commission_paid` ("Tôi xác nhận đã chuyển khoản đủ 10% hoa hồng sàn...").
- **Xử lý kiểm tra:** [`PartnerDashboardController@refund`](file:///c:/xampp/htdocs/lar_vidu1/app/Http/Controllers/Partner/PartnerDashboardController.php)
  - Bắt buộc phải có `commission_payment_proof` và `confirm_commission_paid`.
  - Cập nhật hợp đồng:
    - `partner_commission_fee = $commission10`
    - `partner_commission_status = 'paid'`
    - `partner_commission_proof = $request->commission_payment_proof`
    - `partner_commission_paid_at = now()`
    - `platform_fee = $commission10`
    - `partner_payout = $totalRentalFee - $commission10`
  - Ghi nhận `PaymentTransaction` loại `partner_commission_10pct` với số tiền 10% hoa hồng.

#### 4.2. Giám sát bên Admin
- **Giao diện Admin:**
  - [`resources/views/admin/rentals/index.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/admin/rentals/index.blade.php):
    - Cột 5 hiển thị badge: `✓ Đã nộp 10% HH: [Số tiền] (Mã GD: [Mã])`.
    - Trong Modal hoàn cọc của Admin hiển thị thông báo đối soát tình trạng nộp hoa hồng của đối tác trước khi Admin bấm chuyển cọc cho khách.
  - [`resources/views/admin/rentals/show.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/admin/rentals/show.blade.php): Hiển thị bảng đối soát 10% hoa hồng sàn và 90% chi trả Showroom kèm mã chứng từ.

---

## II. Kết Quả Kiểm Thử Tự Động (Automated Verification)

Đã khởi chạy kịch bản kiểm thử toàn diện tại [`scratch/test_partner_workflow.php`](file:///c:/xampp/htdocs/lar_vidu1/scratch/test_partner_workflow.php) với kết quả đạt 100%:

```
=== KIỂM THỬ 1: QUY TRÌNH ĐĂNG KÝ VÀ DUYỆT ĐỐI TÁC SHOWROOM ===
1.1. Đã tạo hồ sơ đăng ký đối tác (ID: 10)
     - Trạng thái partner_status: pending
     - Vai trò role: user
     - isPartner(): NO (Chờ duyệt)
     - isPendingPartner(): YES
1.2. Admin đã phê duyệt hồ sơ đối tác:
     - Trạng thái partner_status: approved
     - Vai trò role: partner
     - isPartner(): YES (ĐÃ ĐƯỢC CẤP QUYỀN PARTNER)
==> KẾT QUẢ 1: THÀNH CÔNG!

=== KIỂM THỬ 2: QUY TRÌNH ĐĂNG XE & KIỂM ĐỊNH XE CỦA ĐỐI TÁC ===
2.1. Đối tác đăng xe mới:
     - Tên xe: Toyota Camry 2.5Q 2024 Test
     - BKS: 30K-999.88 | Năm SX: 2024
     - Tình trạng: ODO 12,000 km, Hạn đăng kiểm 2027, Bảo hiểm 2 chiều
     - approval_status: pending
2.2. Kiểm tra hiển thị khách hàng ngoài trang chủ: ẨN (CHÍNH XÁC: Xe chờ duyệt không hiển thị cho khách)
2.3. Admin kiểm định và duyệt xe:
     - approval_status: approved
     - approved_at: 2026-09-21 08:30:02
2.4. Kiểm tra hiển thị khách hàng sau khi duyệt: HIỂN THỊ CÔNG KHAI (CHÍNH XÁC)
==> KẾT QUẢ 2: THÀNH CÔNG!

=== KIỂM THỬ 3: NGHĨA VỤ CHUYỂN 10% HOA HỒNG SÀN TRƯỚC KHI NGHIỆM THU ===
3.1. Đơn thuê xe: TEST_1789979402
     - Tổng tiền thuê: 2,400,000 đ
     - Hoa hồng sàn 10% bắt buộc: 240,000 đ
3.2. Đối tác đã nghiệm thu và nộp hoa hồng sàn:
     - partner_commission_status: paid
     - partner_commission_fee: 240,000 đ
     - partner_commission_proof: MB_TRANSFER_889922
     - platform_fee (10%): 240,000 đ
     - partner_payout (90%): 2,160,000 đ
     - rental_status: returned
     - refund_status: waiting_admin
3.3. Log giao dịch hoa hồng sàn: ĐÃ GHI NHẬN (Đối tác Showroom đã chuyển khoản đủ 10% hoa hồng sàn: 240,000 VNĐ)
==> KẾT QUẢ 3: THÀNH CÔNG!
```

---

## IV. Báo Cáo Triển Khai Lab 7: Hệ Thống Tin Nhắn – Livechat (Khách Hàng & Quản Trị Viên)

Theo yêu cầu ưu tiên của bài thực hành Lab 7, hệ thống đã hoàn tất xây dựng tính năng Tin nhắn - Livechat trực tuyến hai chiều giữa Khách hàng và Quản trị viên (Admin):

### 1. Cơ sở dữ liệu & Model (Messages)
- **Migration:** `database/migrations/2026_09_21_180000_create_messages_table.php` tạo bảng `messages`:
  - `id`: Khóa chính
  - `sender_id`: ID người gửi (liên kết bảng `users`)
  - `receiver_id`: ID người nhận (liên kết bảng `users`)
  - `content`: Nội dung tin nhắn dạng text
  - `is_read`: Boolean (mặc định `false`) đánh dấu trạng thái đã xem
  - `created_at`, `updated_at`
- **Model:** [`App\Models\Message`](file:///c:/xampp/htdocs/lar_vidu1/app/Models/Message.php) với quan hệ `sender()` và `receiver()` trỏ tới `User`.

### 2. Bộ điều khiển & Định tuyến (Controllers & Routes)
- **Routes:** Đăng ký trong [`routes/web.php`](file:///c:/xampp/htdocs/lar_vidu1/routes/web.php):
  - Phía User (yêu cầu đăng nhập `auth`):
    - `POST user/chat/send` (`user.chat.send`)
    - `GET user/chat/messages` (`user.chat.messages`)
  - Phía Admin (yêu cầu quyền `admin`):
    - `GET admin/chat/users` (`admin.chat.users`)
    - `GET admin/chat/messages/{userId}` (`admin.chat.messages`)
    - `POST admin/chat/send` (`admin.chat.send`)
- **Controllers:**
  - [`App\Http\Controllers\User\ChatController`](file:///c:/xampp/htdocs/lar_vidu1/app/Http/Controllers/User/ChatController.php): Gửi tin nhắn đến Admin và tải toàn bộ lịch sử hội thoại 2 chiều.
  - [`App\Http\Controllers\Admin\ChatController`](file:///c:/xampp/htdocs/lar_vidu1/app/Http/Controllers/Admin/ChatController.php):
    - `getUsers()`: Truy xuất danh sách khách hàng đã nhắn tin, tự động đính kèm số lượng tin nhắn chưa đọc (`unread_count`) và đoạn trích tin nhắn cuối cùng (`last_message`).
    - `getMessages($userId)`: Lấy toàn bộ hội thoại với 1 khách hàng và tự động cập nhật `is_read = true`.
    - `send()`: Gửi tin nhắn phản hồi đến khách hàng.

### 3. Giao diện Người Dùng & Quản Trị Viên (Views)
- **User Side ([`resources/views/layouts/app.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/layouts/app.blade.php)):**
  - Đã tích hợp nút tròn Livechat cố định góc dưới phải màn hình (`#chat-toggle`).
  - Khung hội thoại popup (`#chat-popup`) gồm header hỗ trợ, body cuộn tin nhắn (`#chat-messages`), ô nhập tin nhắn (`#chat-input`) và nút gửi (`#chat-send`).
  - Tự động Polling định kỳ 3 giây/lần khi popup đang mở để cập nhật tin nhắn mới theo thời gian thực.
  - Phân biệt giao diện tin nhắn của mình (`.user-msg`, màu xanh) và tin nhắn phản hồi của Admin (`.admin-msg`, màu xám).
- **Admin Side ([`resources/views/layouts/admin.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/layouts/admin.blade.php)):**
  - Tích hợp nút dock cố định góc dưới phải (`#admin-chat-toggle`) hiển thị số lượng tin nhắn chưa đọc từ tất cả khách hàng (`#admin-chat-unread-badge`).
  - Khung chat đa cửa sổ phân 2 cột (`#admin-chat-popup`):
    - Cột trái: Danh sách các khách hàng đang tương tác kèm badge tin nhắn chưa đọc.
    - Cột phải: Cửa sổ hội thoại chi tiết với khách hàng được chọn, hiển thị tên khách hàng và ô phản hồi tin nhắn.
    - Tự động Polling 3s/lần khi mở chat và 8s/lần ngầm khi đóng chat để cập nhật thông báo.

### 4. Kết quả kiểm thử tự động
Đã khởi chạy kịch bản [`scratch/test_chat_lab7.php`](file:///c:/xampp/htdocs/lar_vidu1/scratch/test_chat_lab7.php) xác thực toàn bộ luồng gửi/nhận tin nhắn giữa User và Admin:
- User gửi tin nhắn `Xin chào Admin, tôi muốn hỏi thủ tục thuê xe tự lái!`.
- Admin nhận diện hội thoại, `unread_count = 1`.
- Admin mở hộp thoại, tin nhắn được đánh dấu `ĐÃ ĐỌC (is_read = true)`.
- Admin phản hồi: `Chào bạn, thủ tục thuê xe gồm CCCD gắn chip và giấy phép lái xe nhé!`.
- Phía User nhận được phản hồi ngay lập tức với đầy đủ thông tin thời gian.

### 5. Nâng cấp: Admin chủ động nhắn tin cho Khách hàng bất kỳ (Không cần đợi khách nhắn)
Theo yêu cầu nâng cao, hệ thống đã hỗ trợ Admin hoàn toàn chủ động kết nối:
1. **Tìm kiếm & Chọn khách hàng bất kỳ:**
   - Trong cửa sổ chat Admin bổ sung Tab **"Tất cả khách hàng"**, nút **"+ Tin mới"** và ô **Tìm kiếm khách hàng theo Tên / Email**.
   - Admin có thể bấm chọn bất kỳ ai trong cơ sở dữ liệu. Ngay cả khi người đó chưa từng nhắn tin, cửa sổ chat vẫn hiển thị đầy đủ và mở sẵn ô nhập để Admin gửi lời chào hoặc tư vấn.
2. **Nút "💬 Chat" trực tiếp trong trang Quản lý thành viên ([`admin/users/index.blade.php`](file:///c:/xampp/htdocs/lar_vidu1/resources/views/admin/users/index.blade.php)):**
   - Bấm nút "Chat" cạnh tài khoản bất kỳ sẽ lập tức mở popup hội thoại với người đó.
3. **Thông báo tin nhắn mới phía khách hàng:**
   - Khi Admin gửi tin, tin nhắn được đánh dấu `is_read = false`.
   - Phía khách hàng có cơ chế kiểm tra ngầm (polling mỗi 8 giây). Nút chat của khách sẽ tự động hiện Badge đỏ số lượng tin nhắn chưa đọc từ Admin.
   - Khi khách hàng mở xem, tin nhắn tự động chuyển sang `ĐÃ ĐỌC (is_read = true)`.
- Kịch bản kiểm thử độc lập [`scratch/test_proactive_chat.php`](file:///c:/xampp/htdocs/lar_vidu1/scratch/test_proactive_chat.php) đã kiểm thử thành công 100%.

