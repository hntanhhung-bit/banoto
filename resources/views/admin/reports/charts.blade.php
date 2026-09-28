@extends('layouts.admin')

@section('title', 'Biểu đồ Báo cáo doanh thu')

@section('content')
<style>
    .chart-wrap { min-height: 360px; position: relative; }
    .chart-wrap canvas { width: 100% !important; height: 360px !important; }
</style>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-bar-chart text-primary mr-2"></i> BIỂU ĐỒ BÁO CÁO DOANH THU (CHART.JS)
            </h3>
            <p class="text-muted small mb-0">
                Dữ liệu trực quan hóa đồng bộ từ sổ nhật ký thanh toán thực nhận (MoMo & VietQR), chỉ tính các giao dịch đã hoàn tất thành công.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary font-weight-bold">
                <i class="fa fa-table mr-1"></i> Xem Bảng Số Liệu Chi Tiết &rarr;
            </a>
        </div>
    </div>

    <!-- TABS ĐIỀU HƯỚNG BÁO CÁO -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link font-weight-bold bg-white text-primary border shadow-sm" href="{{ route('admin.reports.index') }}">
                <i class="fa fa-table mr-1"></i> Bảng số liệu chi tiết
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link active font-weight-bold bg-primary shadow-sm" href="{{ route('admin.reports.charts') }}">
                <i class="fa fa-pie-chart mr-1"></i> Biểu đồ trực quan (Chart.js)
            </a>
        </li>
    </ul>

    <!-- KPI TỔNG HỢP NHANH TRÊN TRANG BIỂU ĐỒ -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #10b981 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Tổng doanh thu thực nhận</span>
                        <div class="h3 font-weight-bold text-success mb-0 mt-1">{{ number_format($totalRevenue ?? 0, 0, ',', '.') }} đ</div>
                        <small class="text-muted">Đã đối soát 100% cổng thanh toán</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #ecfdf5; color: #10b981;">
                        <i class="fa fa-money fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #a50064 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Doanh thu qua MoMo</span>
                        <div class="h3 font-weight-bold mb-0 mt-1" style="color: #a50064;">{{ number_format($totalMomoRevenue ?? 0, 0, ',', '.') }} đ</div>
                        <small class="text-muted">Giao dịch quét mã MoMo ATM/QR</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #fdf2f8; color: #a50064;">
                        <i class="fa fa-mobile-phone fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #0284c7 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Doanh thu VietQR / NH</span>
                        <div class="h3 font-weight-bold text-info mb-0 mt-1">{{ number_format($totalBankRevenue ?? 0, 0, ',', '.') }} đ</div>
                        <small class="text-muted">Chuyển khoản SePay & Ngân hàng</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #f0f9ff; color: #0284c7;">
                        <i class="fa fa-bank fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card bg-white p-3 border-left h-100 shadow-sm" style="border-left: 5px solid #3b82f6 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Tổng giao dịch khớp lệnh</span>
                        <div class="h3 font-weight-bold text-primary mb-0 mt-1">{{ number_format($totalPaidTx ?? 0) }} <span style="font-size: 15px; font-weight: normal;">giao dịch</span></div>
                        <small class="text-muted">Thanh toán hoàn tất thành công</small>
                    </div>
                    <div class="rounded-circle p-3" style="background-color: #eff6ff; color: #3b82f6;">
                        <i class="fa fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="report-chart-error" class="alert alert-warning d-none" role="alert">
        <i class="fa fa-exclamation-triangle mr-1"></i> Không tải được thư viện biểu đồ Chart.js từ mạng Internet. Bạn có thể xem số liệu chi tiết tại trang <a href="{{ route('admin.reports.index') }}" class="font-weight-bold text-primary">Bảng số liệu</a>.
    </div>

    <!-- HÀNG 1: DOANH THU THEO DANH MỤC & DOANH THU THEO NGÀY (30 NGÀY) -->
    <div class="row">
        <!-- Biểu đồ 1: Doanh thu theo danh mục / hãng xe -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100 rounded-lg">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark"><i class="fa fa-tags text-primary mr-1"></i> Doanh thu theo danh mục / Hãng xe</span>
                    <span class="badge badge-light border text-muted">Cột (Bar)</span>
                </div>
                <div class="card-body chart-wrap">
                    <canvas id="categoryRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Biểu đồ 2: Doanh thu theo ngày (30 ngày gần nhất) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100 rounded-lg">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark"><i class="fa fa-line-chart text-success mr-1"></i> Doanh thu theo ngày (30 ngày gần nhất)</span>
                    <span class="badge badge-light border text-muted">Đường (Line)</span>
                </div>
                <div class="card-body chart-wrap">
                    <canvas id="revenueByDateChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- HÀNG 2: DOANH THU THEO THÁNG & THEO NĂM -->
    <div class="row">
        <!-- Biểu đồ 3: Doanh thu theo tháng (12 tháng gần nhất) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100 rounded-lg">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark"><i class="fa fa-calendar text-info mr-1"></i> Doanh thu theo tháng (12 tháng)</span>
                    <span class="badge badge-light border text-muted">Cột (Bar)</span>
                </div>
                <div class="card-body chart-wrap">
                    <canvas id="revenueByMonthChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Biểu đồ 4: Doanh thu theo năm -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100 rounded-lg">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark"><i class="fa fa-calendar-check-o text-warning mr-1"></i> Doanh thu theo năm tài chính</span>
                    <span class="badge badge-light border text-muted">Cột (Bar)</span>
                </div>
                <div class="card-body chart-wrap">
                    <canvas id="revenueByYearChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- HÀNG 3: DOANH THU THEO PHƯƠNG THỨC THANH TOÁN (PIE CHART) -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold text-dark"><i class="fa fa-pie-chart text-danger mr-1"></i> Tỷ trọng Doanh thu theo Cổng thanh toán (MoMo vs Ngân hàng vs Tiền mặt)</span>
                    <span class="badge badge-light border text-muted">Tròn (Pie)</span>
                </div>
                <div class="card-body chart-wrap" style="height: 380px;">
                    <canvas id="revenueByPaymentMethodChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DỮ LIỆU JSON ĐƯỢC TRUYỀN TỪ BLADE VÀO SCRIPT AN TOÀN -->
<script id="report-chart-data" type="application/json">{!! json_encode([
    'catLabels' => $catLabels ?? [],
    'catRevenue' => $catRevenue ?? [],
    'revDateLabels' => $revDateLabels ?? [],
    'revDateData' => $revDateData ?? [],
    'revMonthLabels' => $revMonthLabels ?? [],
    'revMonthData' => $revMonthData ?? [],
    'revYearLabels' => $revYearLabels ?? [],
    'revYearData' => $revYearData ?? [],
    'paymentMethodLabels' => $paymentMethodLabels ?? [],
    'paymentMethodRevenue' => $paymentMethodRevenue ?? [],
]) !!}</script>

<!-- THƯ VIỆN CHART.JS (VỚI DỰ PHÒNG CDNJS) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
if (typeof Chart === 'undefined') {
    document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"><\/script>');
}
</script>

<script>
window.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') {
        const errEl = document.getElementById('report-chart-error');
        if (errEl) errEl.classList.remove('d-none');
        return;
    }

    const dataEl = document.getElementById('report-chart-data');
    if (!dataEl) return;
    
    const reportData = JSON.parse(dataEl.textContent);

    const catLabels = reportData.catLabels || [];
    const catRevenue = (reportData.catRevenue || []).map(Number);
    const revDateLabels = reportData.revDateLabels || [];
    const revDateData = (reportData.revDateData || []).map(Number);
    const revMonthLabels = reportData.revMonthLabels || [];
    const revMonthData = (reportData.revMonthData || []).map(Number);
    const revYearLabels = reportData.revYearLabels || [];
    const revYearData = (reportData.revYearData || []).map(Number);
    const payLabels = reportData.paymentMethodLabels || [];
    const payRevenue = (reportData.paymentMethodRevenue || []).map(Number);

    // Bảng màu đẹp mắt
    const palette = [
        '#3b82f6', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6',
        '#06b6d4', '#f97316', '#14b8a6', '#6366f1', '#ef4444'
    ];

    // Cấu hình chung cho trục Y hiển thị số tiền VND
    const yAxisConfig = {
        beginAtZero: true,
        ticks: {
            callback: function(value) {
                if (value >= 1000000) {
                    return (value / 1000000).toLocaleString('vi-VN') + ' tr';
                }
                return value.toLocaleString('vi-VN') + ' đ';
            }
        }
    };

    // 1. Biểu đồ Danh mục / Hãng xe (Cột nhiều màu)
    const catCanvas = document.getElementById('categoryRevenueChart');
    if (catCanvas) {
        new Chart(catCanvas, {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: catRevenue,
                    backgroundColor: catLabels.map((_, i) => palette[i % palette.length]),
                    borderRadius: 6,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: yAxisConfig },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return (context.label || '') + ': ' + Number(context.raw).toLocaleString('vi-VN') + ' VNĐ';
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Biểu đồ theo Ngày (30 ngày gần nhất - Line)
    const dateCanvas = document.getElementById('revenueByDateChart');
    if (dateCanvas) {
        new Chart(dateCanvas, {
            type: 'line',
            data: {
                labels: revDateLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revDateData,
                    fill: true,
                    backgroundColor: 'rgba(16, 185, 129, 0.12)',
                    borderColor: '#10b981',
                    borderWidth: 2.5,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#10b981',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: yAxisConfig },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + Number(context.raw).toLocaleString('vi-VN') + ' VNĐ';
                            }
                        }
                    }
                }
            }
        });
    }

    // 3. Biểu đồ theo Tháng (12 tháng gần nhất - Bar)
    const monthCanvas = document.getElementById('revenueByMonthChart');
    if (monthCanvas) {
        new Chart(monthCanvas, {
            type: 'bar',
            data: {
                labels: revMonthLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revMonthData,
                    backgroundColor: '#0284c7',
                    borderRadius: 6,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: yAxisConfig },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Tháng ' + context.label + ': ' + Number(context.raw).toLocaleString('vi-VN') + ' VNĐ';
                            }
                        }
                    }
                }
            }
        });
    }

    // 4. Biểu đồ theo Năm (Bar)
    const yearCanvas = document.getElementById('revenueByYearChart');
    if (yearCanvas) {
        new Chart(yearCanvas, {
            type: 'bar',
            data: {
                labels: revYearLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revYearData,
                    backgroundColor: '#f59e0b',
                    borderRadius: 6,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: yAxisConfig },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Năm ' + context.label + ': ' + Number(context.raw).toLocaleString('vi-VN') + ' VNĐ';
                            }
                        }
                    }
                }
            }
        });
    }

    // 5. Biểu đồ Phương thức thanh toán (Pie)
    const payCanvas = document.getElementById('revenueByPaymentMethodChart');
    if (payCanvas) {
        new Chart(payCanvas, {
            type: 'pie',
            data: {
                labels: payLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: payRevenue,
                    backgroundColor: ['#a50064', '#0284c7', '#64748b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: { size: 13, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const val = Number(context.raw);
                                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ' ' + context.label + ': ' + val.toLocaleString('vi-VN') + ' đ (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
