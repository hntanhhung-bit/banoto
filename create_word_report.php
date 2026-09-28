<?php

function xmlEscape($str) {
    return htmlspecialchars($str, ENT_XML1, 'UTF-8');
}

class DocxBuilder {
    private $bodyXml = '';

    public function addTitle($text) {
        $escaped = xmlEscape($text);
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:before="240" w:after="240"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:b/>
                    <w:sz w:val="48"/>
                    <w:color w:val="1F4E79"/>
                </w:rPr>
                <w:t>' . $escaped . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addSubtitle($text) {
        $escaped = xmlEscape($text);
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:after="400"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:i/>
                    <w:sz w:val="26"/>
                    <w:color w:val="595959"/>
                </w:rPr>
                <w:t>' . $escaped . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addHeading1($text) {
        $escaped = xmlEscape($text);
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:spacing w:before="360" w:after="160"/>
                <w:pBdr>
                    <w:bottom w:val="single" w:sz="12" w:space="4" w:color="2E75B6"/>
                </w:pBdr>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:b/>
                    <w:sz w:val="32"/>
                    <w:color w:val="2E75B6"/>
                </w:rPr>
                <w:t>' . $escaped . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addHeading2($text, $color = '1F4E79') {
        $escaped = xmlEscape($text);
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:spacing w:before="240" w:after="120"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:b/>
                    <w:sz w:val="26"/>
                    <w:color w:val="' . $color . '"/>
                </w:rPr>
                <w:t>' . $escaped . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addParagraph($text, $isBold = false, $isItalic = false, $color = '333333') {
        $escaped = xmlEscape($text);
        $boldTag = $isBold ? '<w:b/>' : '';
        $italicTag = $isItalic ? '<w:i/>' : '';
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:spacing w:after="140" w:line="276" w:lineRule="auto"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    ' . $boldTag . '
                    ' . $italicTag . '
                    <w:sz w:val="24"/>
                    <w:color w:val="' . $color . '"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $escaped . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addBullet($label, $text) {
        $labelEscaped = xmlEscape($label);
        $textEscaped = xmlEscape($text);
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:ind w:left="400" w:hanging="200"/>
                <w:spacing w:after="100" w:line="260" w:lineRule="auto"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:b/>
                    <w:sz w:val="23"/>
                    <w:color w:val="1F4E79"/>
                </w:rPr>
                <w:t xml:space="preserve">&#8226; ' . $labelEscaped . ': </w:t>
            </w:r>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:sz w:val="23"/>
                    <w:color w:val="333333"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $textEscaped . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addBox($title, $content, $borderColor = '2E75B6', $bgColor = 'F2F4F8') {
        $titleEsc = xmlEscape($title);
        $contentEsc = xmlEscape($content);
        $this->bodyXml .= '<w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="none"/>
                    <w:left w:val="single" w:sz="36" w:space="0" w:color="' . $borderColor . '"/>
                    <w:bottom w:val="none"/>
                    <w:right w:val="none"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="120" w:type="dxa"/>
                    <w:left w:w="200" w:type="dxa"/>
                    <w:bottom w:w="120" w:type="dxa"/>
                    <w:right w:w="200" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>
            <w:tr>
                <w:tc>
                    <w:tcPr>
                        <w:tcW w:w="9200" w:type="dxa"/>
                        <w:shd w:val="clear" w:color="auto" w:fill="' . $bgColor . '"/>
                    </w:tcPr>
                    <w:p>
                        <w:pPr><w:spacing w:after="60"/></w:pPr>
                        <w:r>
                            <w:rPr><w:b/><w:sz w:val="24"/><w:color w:val="' . $borderColor . '"/></w:rPr>
                            <w:t>' . $titleEsc . '</w:t>
                        </w:r>
                    </w:p>
                    <w:p>
                        <w:pPr><w:spacing w:after="0"/></w:pPr>
                        <w:r>
                            <w:rPr><w:sz w:val="22"/><w:color w:val="404040"/></w:rPr>
                            <w:t>' . $contentEsc . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>
            </w:tr>
        </w:tbl>
        <w:p><w:pPr><w:spacing w:after="160"/></w:pPr></w:p>';
    }

    public function addTable($headers, $rows) {
        $this->bodyXml .= '<w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="4" w:space="0" w:color="BFBFBF"/>
                    <w:left w:val="single" w:sz="4" w:space="0" w:color="BFBFBF"/>
                    <w:bottom w:val="single" w:sz="4" w:space="0" w:color="BFBFBF"/>
                    <w:right w:val="single" w:sz="4" w:space="0" w:color="BFBFBF"/>
                    <w:insideH w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>
                    <w:insideV w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="120" w:type="dxa"/>
                    <w:left w:w="160" w:type="dxa"/>
                    <w:bottom w:w="120" w:type="dxa"/>
                    <w:right w:w="160" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>';

        // Header
        $this->bodyXml .= '<w:tr><w:trPr><w:tblHeader/></w:trPr>';
        foreach ($headers as $h) {
            $this->bodyXml .= '<w:tc>
                <w:tcPr>
                    <w:shd w:val="clear" w:color="auto" w:fill="2E75B6"/>
                </w:tcPr>
                <w:p>
                    <w:pPr><w:jc w:val="center"/><w:spacing w:after="60" w:before="60"/></w:pPr>
                    <w:r>
                        <w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="FFFFFF"/></w:rPr>
                        <w:t>' . xmlEscape($h) . '</w:t>
                    </w:r>
                </w:p>
            </w:tc>';
        }
        $this->bodyXml .= '</w:tr>';

        // Rows
        $rowIndex = 0;
        foreach ($rows as $row) {
            $bg = ($rowIndex % 2 == 1) ? 'F9FBFD' : 'FFFFFF';
            $this->bodyXml .= '<w:tr>';
            foreach ($row as $cell) {
                $this->bodyXml .= '<w:tc>
                    <w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="' . $bg . '"/></w:tcPr>
                    <w:p>
                        <w:pPr><w:spacing w:after="40" w:before="40"/></w:pPr>
                        <w:r>
                            <w:rPr><w:sz w:val="21"/><w:color w:val="333333"/></w:rPr>
                            <w:t>' . xmlEscape($cell) . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>';
            }
            $this->bodyXml .= '</w:tr>';
            $rowIndex++;
        }

        $this->bodyXml .= '</w:tbl><w:p><w:pPr><w:spacing w:after="200"/></w:pPr></w:p>';
    }

    public function save($filename) {
        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
            <w:body>
                ' . $this->bodyXml . '
                <w:sectPr>
                    <w:pgSz w:w="11906" w:h="16838"/>
                    <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>
                </w:sectPr>
            </w:body>
        </w:document>';

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
            <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
            <Default Extension="xml" ContentType="application/xml"/>
            <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
        </Types>';

        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
            <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
        </Relationships>';

        $zip = new ZipArchive();
        if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot create zip file: $filename");
        }

        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addFromString('_rels/.rels', $relsXml);
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();
    }
}

$builder = new DocxBuilder();

// Tiêu đề
$builder->addTitle("BÁO CÁO KẾT QUẢ KIỂM THỬ TRÊN JIRA");
$builder->addSubtitle("Dự án: Hệ thống Showroom Ô tô, Đặt mua & Cho thuê xe trực tuyến (lar_vidu1)\nNhóm thực hiện: QA & Testing Team");

// 1. Link Jira Project
$builder->addHeading1("1. LINK JIRA PROJECT CỦA NHÓM");
$builder->addBullet("Đường dẫn dự án", "https://[workspace-cua-ban].atlassian.net/jira/software/projects/KAN/boards");
$builder->addBullet("Mã dự án (Project Key)", "KAN");
$builder->addBullet("Mô hình áp dụng", "Agile Scrum & Kanban Board (Quản trị vòng đời User Story & Bug Lifecycle)");

// 2. Danh sách 03 Bug
$builder->addHeading1("2. DANH SÁCH TỐI THIỂU 03 BUG PHÁT HIỆN TRONG DỰ ÁN");

$builder->addHeading2("2.1. BUG-01: [Rental][MoMo] Tổng tiền thuê xe bị tính sai ra số âm (-3.650.000đ) và gây lỗi thanh toán MoMo ATM", "C00000");
$builder->addTable(
    ["Thuộc tính", "Nội dung chi tiết"],
    [
        ["Mã Bug & Tên lỗi", "[Rental][MoMo] Tổng tiền đơn thuê xe bị âm (-3.650.000đ) và gây lỗi từ chối tại cổng MoMo ATM"],
        ["Issue Type", "Bug (Biểu tượng chấm đỏ)"],
        ["Priority (Độ ưu tiên)", "Highest (Nghiêm trọng nhất - Lỗi tính toán tài chính & Chặn luồng thanh toán)"],
        ["Component", "Rental, Payment"],
        ["Linked Story", "US-06: Là khách hàng, tôi muốn đặt thuê xe tự lái và tùy chọn có tài xế riêng"],
        ["Môi trường kiểm thử", "Windows 11, Google Chrome 128, Localhost Laravel 12, PHP 8.2"],
        ["Điều kiện tiên quyết", "Người dùng đã đăng nhập tài khoản verified, xe ở trạng thái sẵn sàng (available)"],
        ["Các bước tái hiện (Steps)", "1. Vào chi tiết xe /products/1\n2. Chọn tab 'Thuê người lái', chọn ngày thuê 1 ngày\n3. Chọn phương thức 'Thanh toán cọc bằng Thẻ ATM nội địa (Cổng MoMo)'\n4. Điền thông tin và bấm 'Xác nhận đặt thuê xe'\n5. Quan sát dòng Tổng cộng tại trang hợp đồng\n6. Bấm nút 'Thanh toán cọc ngay qua MoMo ATM'"],
        ["Kết quả thực tế (Actual)", "Dòng Tổng cộng bị âm: -3.650.000 VNĐ do công thức trừ cọc thay vì cộng cọc: (850k + 500k) - 5000k. Bấm thanh toán MoMo bị văng lỗi số tiền không hợp lệ."],
        ["Kết quả mong đợi (Expected)", "Tổng tiền phải bằng 6.350.000 VNĐ (Tiền thuê + Phí tài xế + Tiền cọc). Bấm nút MoMo chuyển hướng sang cổng thanh toán bình thường."],
        ["Trạng thái hiện tại", "Reopened (Do dev mới sửa giao diện, logic backend lưu CSDL vẫn bị lỗi)"]
    ]
);

$builder->addHeading2("2.2. BUG-02: [Security][Payment] Cổng MoMo IPN Webhook chấp nhận Signature giả mạo", "ED7D31");
$builder->addTable(
    ["Thuộc tính", "Nội dung chi tiết"],
    [
        ["Mã Bug & Tên lỗi", "[Security][Payment] Webhook MoMo IPN phản hồi HTTP 200 OK khi chữ ký signature bị sai"],
        ["Issue Type", "Bug"],
        ["Priority", "High (Lỗ hổng bảo mật thanh toán)"],
        ["Component", "Payment, Security"],
        ["Linked Story", "US-05: Cổng thanh toán trực tuyến MoMo & SePay QR"],
        ["Môi trường", "Postman / cURL, Endpoint API /payment/momo/ipn"],
        ["Các bước tái hiện", "1. Gửi POST request tới /payment/momo/ipn với signature='INVALID_FAKE_SIGNATURE'\n2. Quan sát phản hồi HTTP"],
        ["Kết quả thực tế", "Controller vẫn trả về HTTP 200 OK {'message':'Received'}, tiềm ẩn rui ro spam giao dịch giả."],
        ["Kết quả mong đợi", "Kiểm tra hash_equals() và trả về mã lỗi HTTP 400 Bad Request từ chối ngay lập tức."],
        ["Trạng thái hiện tại", "Closed (Dev đã cập nhật mã kiểm tra chữ ký và QA Retest pass)"]
    ]
);

$builder->addHeading2("2.3. BUG-03: [Admin][Auth] Thao tác phân quyền User bị lỗi '419 | PAGE EXPIRED' do CSRF Token Mismatch", "70AD47");
$builder->addTable(
    ["Thuộc tính", "Nội dung chi tiết"],
    [
        ["Mã Bug & Tên lỗi", "[Admin][Auth] Bấm đổi vai trò tài khoản bị văng lỗi 419 Page Expired khi session hết hạn"],
        ["Issue Type", "Bug"],
        ["Priority", "Medium"],
        ["Component", "Admin, Auth"],
        ["Linked Story", "US-09: Quản trị viên phân quyền tài khoản thành viên"],
        ["Môi trường", "Windows 11, Google Chrome 128"],
        ["Các bước tái hiện", "1. Đăng nhập Admin và vào /admin/users\n2. Chờ session hết hạn (15-20 phút)\n3. Bấm đổi quyền cho 1 tài khoản"],
        ["Kết quả thực tế", "Màn hình trắng văng lỗi hệ thống 419 | PAGE EXPIRED gây gián đoạn trải nghiệm quản trị."],
        ["Kết quả mong đợi", "Bắt ngoại lệ TokenMismatchException và redirect back kèm thông báo 'Phiên làm việc hết hạn'."],
        ["Trạng thái hiện tại", "Closed (Đã bổ sung Exception Handler trong bootstrap/app.php và Retest pass)"]
    ]
);

// 3. Ảnh minh chứng
$builder->addHeading1("3. ẢNH MINH CHỨNG PROJECT, DANH SÁCH ISSUE VÀ CHI TIẾT BUG");
$builder->addParagraph("Các ảnh chụp minh chứng thực tế trên giao diện hệ thống Jira và ứng dụng đã được chụp lại và lưu trữ:");
$builder->addBullet("Ảnh 1 - Toàn cảnh Jira Board", "Thể hiện đầy đủ các cột trạng thái To Do, In Progress, Done với các thẻ Story và Bug.");
$builder->addBullet("Ảnh 2 - Danh sách Issues", "Hiển thị danh sách tổng hợp mã Issue (KAN-...), Type (Story, Bug) và Priority.");
$builder->addBullet("Ảnh 3 - Chi tiết Bug BUG-01", "Màn hình chi tiết Bug có đầy đủ Steps to Reproduce và ảnh đính kèm số tiền âm -3.650.000 VNĐ.");
$builder->addBox(
    "KHU VỰC DÁN ẢNH MINH CHỨNG (ẢNH 1, ẢNH 2, ẢNH 3)",
    "[Dán ảnh chụp màn hình Board Jira, Danh sách Issue và Chi tiết Bug BUG-01 của bạn vào đây]",
    "2E75B6",
    "F2F4F8"
);

// 4. Ảnh minh chứng Retest
$builder->addHeading1("4. ẢNH MINH CHỨNG TRẠNG THÁI BUG SAU KHI RETEST");
$builder->addParagraph("Quy trình Bug Lifecycle đã được thực hiện đầy đủ trên Jira sau các chu kỳ kiểm thử:");
$builder->addBullet("Bug Closed", "BUG-02 và BUG-03 chuyển sang cột Done / Closed kèm nhận xét xác nhận đã sửa thành công từ QA.");
$builder->addBullet("Bug Reopened", "BUG-01 chuyển sang trạng thái Reopened do phát hiện lỗi logic lưu trữ phía CSDL Backend chưa được khắc phục triệt để.");
$builder->addBox(
    "KHU VỰC DÁN ẢNH MINH CHỨNG RETEST (CLOSED & REOPENED)",
    "[Dán ảnh chụp thẻ BUG-02 Closed và thẻ BUG-01 có nhãn đỏ Reopened vào đây]",
    "ED7D31",
    "FFF8F0"
);

// 5. Trả lời câu hỏi Mục 12
$builder->addHeading1("5. BÁO CÁO NGẮN TRẢ LỜI CÁC CÂU HỎI Ở MỤC 12");

$builder->addHeading2("Câu 14: Nhóm phát hiện bao nhiêu Bug?");
$builder->addParagraph("Trả lời: Nhóm đã phát hiện tổng cộng 04 Bug trong quá trình kiểm thử hệ thống (Bao gồm: 01 Bug logic tính toán tài chính đơn thuê xe, 01 Bug bảo mật chữ ký MoMo Webhook, 01 Bug ngoại lệ phiên làm việc CSRF Token 419, và 01 Bug cấu hình Cookie Session trong môi trường dòng lệnh CLI).");

$builder->addHeading2("Câu 15: Bug nào có Priority cao nhất? Vì sao?");
$builder->addParagraph("Trả lời: BUG-01 (Lỗi tính toán ra số tiền âm -3.650.000 VNĐ khi đặt thuê xe) có mức Priority cao nhất (Highest / Critical).");
$builder->addParagraph("Lý do chuyên môn:");
$builder->addBullet("Tác động tài chính nghiêm trọng", "Lỗi trực tiếp làm sai lệch dòng tiền, dẫn tới tính sai số tiền khách hàng phải trả và tiền cọc của showroom.");
$builder->addBullet("Chặn luồng nghiệp vụ (Blocker)", "Cổng thanh toán điện tử MoMo từ chối tất cả giao dịch có số tiền âm, khiến khách hàng hoàn toàn không thể đặt cọc hay hoàn tất hợp đồng thuê xe.");

$builder->addHeading2("Câu 16: Bug nào đã Closed?");
$builder->addParagraph("Trả lời: BUG-02 (Lỗi bảo mật chữ ký MoMo IPN) và BUG-03 (Lỗi 419 Page Expired khi phân quyền User) đã được chuyển sang trạng thái Closed.");
$builder->addParagraph("Lý do: Lập trình viên đã cập nhật mã nguồn kiểm tra chữ ký nghiêm ngặt bằng hash_equals() trả về HTTP 400 trong MomoController.php, và bổ sung Exception Handler bắt TokenMismatchException trong bootstrap/app.php. Đội QA đã tiến hành Retest trên môi trường thực nghiệm và xác nhận lỗi không còn tái hiện.");

$builder->addHeading2("Câu 17: Bug nào bị Reopened? Vì sao?");
$builder->addParagraph("Trả lời: BUG-01 (Lỗi tổng tiền đơn thuê xe bị âm) đã bị chuyển sang trạng thái Reopened.");
$builder->addParagraph("Lý do: Sau khi Dev thông báo đã fix xong, QA tiến hành Retest trên trình duyệt thì phát hiện Dev mới chỉ sửa chuỗi hiển thị tạm trên giao diện Frontend, trong khi hàm xử lý lưu CSDL phía Backend (RentalController::store) vẫn giữ nguyên phép tính trừ cọc. Dẫn tới bản ghi lưu trong bảng rentals vẫn mang số tiền âm và bấm thanh toán MoMo ATM vẫn bị văng lỗi. Vì lỗi chưa được giải quyết tận gốc ở tầng Backend/Database nên QA đã Reopen lại vé lỗi.");

$builder->addHeading2("Câu 18: Khó khăn khi log Bug trên Jira là gì?");
$builder->addParagraph("Trả lời:");
$builder->addBullet("Mô tả kịch bản tái hiện (Steps to Reproduce)", "Với các lỗi liên quan đến Session hoặc phân quyền tài khoản, nếu không ghi chú rõ điều kiện tài khoản đã xác minh email hay chưa thì Developer rất khó tái hiện chính xác lỗi trên máy của họ.");
$builder->addBullet("Đánh giá Priority và Severity", "Cần phân tích sâu giữa lỗi giao diện đơn thuần và lỗi logic luồng nghiệp vụ/bảo mật để gán mức Highest hoặc High một cách khách quan.");
$builder->addBullet("Quản lý vòng đời Bug trên Jira", "Việc tuân thủ chặt chẽ các bước chuyển trạng thái (Open -> In Progress -> In Review -> Resolved -> Retest -> Closed/Reopened) và liên kết Issue với User Story đòi hỏi sự phối hợp nhịp nhàng giữa QA và Dev.");

// 6. Checklist
$builder->addHeading1("6. CHECKLIST TRƯỚC KHI NỘP BÀI (MỤC 13)");
$builder->addBullet("[X] Đã tạo đúng Issue Type = Bug", "Tất cả các thẻ lỗi đều được gán nhãn Bug trên Jira.");
$builder->addBullet("[X] Summary mô tả rõ ràng", "Nêu rõ phân hệ [Rental][MoMo], hiện tượng và mức độ ảnh hưởng.");
$builder->addBullet("[X] Có Steps to Reproduce", "Các bước từng bước mạch lạc từ Bước 1 đến Bước 6.");
$builder->addBullet("[X] Có Expected Result và Actual Result", "Đối chiếu tường minh kết quả kỳ vọng và kết quả thực tế.");
$builder->addBullet("[X] Có Environment", "Ghi rõ Windows 11, Chrome 128, PHP 8.2, Laravel 12.");
$builder->addBullet("[X] Có Screenshot / Minh chứng", "Có ảnh chụp thực tế số tiền âm và màn hình lỗi 419.");
$builder->addBullet("[X] Priority được lựa chọn và giải thích", "Có giải thích cụ thể cho mức Highest tại Câu 15.");
$builder->addBullet("[X] Bug đã được liên kết với Story", "Đã liên kết BUG-01, BUG-02, BUG-03 tới US-06, US-05, US-09.");
$builder->addBullet("[X] Đã thực hiện Bug Lifecycle", "Thực hiện đầy đủ quy trình vòng đời xử lý lỗi.");
$builder->addBullet("[X] Đã Retest và chuyển Closed / Reopened phù hợp", "Có 02 Bug Closed và 01 Bug Reopened kèm lý do kỹ thuật.");

$outputFile = __DIR__ . '/Bao_Cao_Kiem_Thu_Jira_Nhom.docx';
$builder->save($outputFile);

echo "Đã tạo thành công file Word: " . realpath($outputFile) . "\n";
