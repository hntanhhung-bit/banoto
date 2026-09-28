<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\Appointment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PartnerReportController extends Controller
{
    /**
     * Xác định Partner ID (Nếu là Admin thì xem theo partner được chọn hoặc partner đầu tiên)
     */
    private function getPartnerId(Request $request): int
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            if ($request->filled('partner_id')) {
                return (int) $request->partner_id;
            }
            $firstPartner = User::where('role', 'partner')->first();
            return $firstPartner ? $firstPartner->id : $user->id;
        }
        return $user->id;
    }

    /**
     * Thống kê theo từng mẫu xe của đối tác
     */
    private function carRevenue(int $partnerId): Collection
    {
        $paidRentals = Rental::where('partner_id', $partnerId)
            ->where('payment_status', '!=', 'unpaid')
            ->with(['product.category'])
            ->get();

        $stats = [];
        foreach ($paidRentals as $r) {
            $carName = $r->product?->name ?? 'Mẫu xe #' . $r->product_id;
            $catName = $r->product?->category?->name ?? 'Thuê xe theo hãng';

            if (!isset($stats[$carName])) {
                $stats[$carName] = [
                    'car_name' => $carName,
                    'category_name' => $catName,
                    'total_qty' => 0,
                    'gross_revenue' => 0,
                    'platform_fee' => 0,
                    'partner_net' => 0,
                ];
            }

            $gross = (float) ($r->total_rental_fee + $r->total_driver_fee);
            $plat = (float) ($r->platform_fee ?: round($gross * 0.10));
            $net = (float) ($r->partner_payout ?: ($gross - $plat));

            $stats[$carName]['total_qty'] += 1;
            $stats[$carName]['gross_revenue'] += $gross;
            $stats[$carName]['platform_fee'] += $plat;
            $stats[$carName]['partner_net'] += $net;
        }

        return collect($stats)->sortByDesc('partner_net')->values()->map(fn($item) => (object) $item);
    }

    /**
     * Thống kê theo ngày từ các đơn thuê xe đã thanh toán của đối tác
     */
    private function dailyRevenue(int $partnerId): Collection
    {
        $paidRentals = Rental::where('partner_id', $partnerId)
            ->where('payment_status', '!=', 'unpaid')
            ->get();

        return $paidRentals->groupBy(fn($r) => $r->created_at->format('Y-m-d'))
            ->map(function ($rows, $date) {
                $gross = (float) $rows->sum(fn($r) => $r->total_rental_fee + $r->total_driver_fee);
                $plat = (float) $rows->sum(fn($r) => $r->platform_fee ?: round(($r->total_rental_fee + $r->total_driver_fee) * 0.10));
                $net = (float) $rows->sum(fn($r) => $r->partner_payout ?: (($r->total_rental_fee + $r->total_driver_fee) - ($r->platform_fee ?: round(($r->total_rental_fee + $r->total_driver_fee) * 0.10))));

                return (object) [
                    'date' => (string) $date,
                    'order_count' => $rows->count(),
                    'gross_revenue' => $gross,
                    'platform_fee' => $plat,
                    'partner_net' => $net,
                ];
            })
            ->sortBy('date')
            ->values();
    }

    /**
     * Tổng hợp doanh thu theo tháng hoặc năm từ danh sách ngày
     */
    private function periodRevenue(Collection $days, string $period): Collection
    {
        return $days->groupBy(fn($day) => substr($day->date, 0, $period === 'month' ? 7 : 4))
            ->map(fn(Collection $rows, $key) => (object) [
                $period => (string) $key,
                'order_count' => $rows->sum('order_count'),
                'gross_revenue' => $rows->sum('gross_revenue'),
                'platform_fee' => $rows->sum('platform_fee'),
                'partner_net' => $rows->sum('partner_net'),
            ])->values();
    }

    /**
     * Bảng số liệu chi tiết báo cáo doanh thu đối tác (Mẫu tương tự Admin nhưng lọc theo Partner)
     */
    public function index(Request $request)
    {
        $partnerId = $this->getPartnerId($request);
        $partnerUser = User::find($partnerId) ?: Auth::user();
        $allPartners = Auth::user()->role === 'admin' ? User::where('role', 'partner')->get() : collect();

        // Đơn thuê xe đã thanh toán
        $paidRentals = Rental::where('partner_id', $partnerId)->where('payment_status', '!=', 'unpaid')->get();
        $totalGross = (float) ($paidRentals->sum('total_rental_fee') + $paidRentals->sum('total_driver_fee'));
        $totalPlatformCommission = (float) ($paidRentals->sum('platform_fee') ?: round($totalGross * 0.10));
        $totalPartnerNet = (float) ($paidRentals->sum('partner_payout') ?: ($totalGross - $totalPlatformCommission));

        // Hợp đồng bán xe thành công (Hoa hồng môi giới 1%)
        $wonDeals = Appointment::where('partner_id', $partnerId)->where('deal_status', 'deal_won')->get();
        $totalSalesCommission = (float) $wonDeals->sum('commission_amount');
        $totalCarsSold = $wonDeals->count();

        // Tổng thu nhập ròng của Showroom
        $totalPartnerIncome = $totalPartnerNet + $totalSalesCommission;
        $totalPaidRentals = $paidRentals->count();

        // Bảng phân bổ
        $carRevenue = $this->carRevenue($partnerId);
        $revenueByDate = $this->dailyRevenue($partnerId);
        $revenueByMonth = $this->periodRevenue($revenueByDate, 'month');
        $revenueByYear = $this->periodRevenue($revenueByDate, 'year');

        return view('partner.reports.index', compact(
            'partnerId',
            'partnerUser',
            'allPartners',
            'totalGross',
            'totalPlatformCommission',
            'totalPartnerNet',
            'totalSalesCommission',
            'totalCarsSold',
            'totalPartnerIncome',
            'totalPaidRentals',
            'carRevenue',
            'revenueByDate',
            'revenueByMonth',
            'revenueByYear'
        ));
    }

    /**
     * Biểu đồ trực quan doanh thu đối tác (Chart.js)
     */
    public function charts(Request $request)
    {
        $partnerId = $this->getPartnerId($request);
        $partnerUser = User::find($partnerId) ?: Auth::user();
        $allPartners = Auth::user()->role === 'admin' ? User::where('role', 'partner')->get() : collect();

        // Tổng hợp tài chính
        $paidRentals = Rental::where('partner_id', $partnerId)->where('payment_status', '!=', 'unpaid')->get();
        $totalGross = (float) ($paidRentals->sum('total_rental_fee') + $paidRentals->sum('total_driver_fee'));
        $totalPlatformCommission = (float) ($paidRentals->sum('platform_fee') ?: round($totalGross * 0.10));
        $totalPartnerNet = (float) ($paidRentals->sum('partner_payout') ?: ($totalGross - $totalPlatformCommission));

        $wonDeals = Appointment::where('partner_id', $partnerId)->where('deal_status', 'deal_won')->get();
        $totalSalesCommission = (float) $wonDeals->sum('commission_amount');
        $totalPartnerIncome = $totalPartnerNet + $totalSalesCommission;
        $totalPaidRentals = $paidRentals->count();

        // Dữ liệu biểu đồ theo Mẫu xe
        $cars = $this->carRevenue($partnerId);
        $carLabels = $cars->pluck('car_name')->all();
        $carRevenue = $cars->pluck('partner_net')->map(fn($v) => (float) $v)->all();

        // Dữ liệu biểu đồ theo Ngày (30 ngày gần nhất)
        $daily = $this->dailyRevenue($partnerId);
        $byDate = $daily->keyBy('date');
        $byMonth = $this->periodRevenue($daily, 'month')->keyBy('month');
        $byYear = $this->periodRevenue($daily, 'year');

        $startDay = Carbon::now()->startOfDay()->subDays(29);
        $startMonth = Carbon::now()->startOfMonth()->subMonths(11);

        $revDateLabels = [];
        $revDateData = [];
        $revMonthLabels = [];
        $revMonthData = [];

        // 30 ngày gần nhất (Doanh thu thực nhận Showroom)
        for ($i = 0; $i < 30; $i++) {
            $date = $startDay->copy()->addDays($i)->toDateString();
            $revDateLabels[] = date('d/m', strtotime($date));
            $revDateData[] = (float) ($byDate->get($date)?->partner_net ?? 0);
        }

        // 12 tháng gần nhất
        for ($i = 0; $i < 12; $i++) {
            $month = $startMonth->copy()->addMonths($i);
            $revMonthLabels[] = $month->format('m/Y');
            $revMonthData[] = (float) ($byMonth->get($month->format('Y-m'))?->partner_net ?? 0);
        }

        // Theo các năm tài chính
        $revYearLabels = $byYear->pluck('year')->all();
        if (empty($revYearLabels)) {
            $revYearLabels = [date('Y')];
            $revYearData = [$totalPartnerNet];
        } else {
            $revYearData = $byYear->pluck('partner_net')->map(fn($v) => (float) $v)->all();
        }

        // Cơ cấu nguồn thu nhập đối tác (Pie Chart)
        $breakdownLabels = ['Thuê xe nhận về (90%)', 'Hoa hồng bán xe (1%)', 'Phí sàn khấu trừ (10%)'];
        $breakdownData = [$totalPartnerNet, $totalSalesCommission, $totalPlatformCommission];

        return view('partner.reports.charts', compact(
            'partnerId',
            'partnerUser',
            'allPartners',
            'totalGross',
            'totalPlatformCommission',
            'totalPartnerNet',
            'totalSalesCommission',
            'totalPartnerIncome',
            'totalPaidRentals',
            'carLabels',
            'carRevenue',
            'revDateLabels',
            'revDateData',
            'revMonthLabels',
            'revMonthData',
            'revYearLabels',
            'revYearData',
            'breakdownLabels',
            'breakdownData'
        ));
    }
}
