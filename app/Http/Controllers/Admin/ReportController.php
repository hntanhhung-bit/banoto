<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rental;
use App\Models\Product;
use App\Models\Category;
use App\Models\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Lấy query toàn bộ giao dịch thanh toán thành công (Nguồn thu thực tế của hệ thống)
     */
    private function paidTransactionsQuery(): Builder
    {
        return PaymentTransaction::query()->where('status', 'paid');
    }

    /**
     * Thống kê doanh thu theo danh mục sản phẩm / hãng xe (tổng hợp từ Mua xe & Thuê xe qua Giao dịch)
     */
    private function categoryRevenue(): Collection
    {
        $transactions = $this->paidTransactionsQuery()
            ->with(['order.items.product.category', 'rental.product.category'])
            ->get();

        $catStats = [];

        foreach ($transactions as $tx) {
            $categoryName = null;
            $categoryId = null;

            if ($tx->order && $tx->order->items && $tx->order->items->first()) {
                $firstItem = $tx->order->items->first();
                $categoryName = $firstItem->category_name ?: ($firstItem->product?->category?->name ?? 'Xe nguyên chiếc');
                $categoryId = $firstItem->product?->category_id ?? 1;
            } elseif ($tx->rental && $tx->rental->product) {
                $categoryName = $tx->rental->product->category?->name ?? 'Thuê xe theo hãng';
                $categoryId = $tx->rental->product->category_id ?? 1;
            } else {
                $categoryName = 'Giao dịch Dịch vụ & Phí sàn';
                $categoryId = 999;
            }

            if (!isset($catStats[$categoryName])) {
                $catStats[$categoryName] = [
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'total_revenue' => 0,
                    'total_qty' => 0,
                ];
            }
            $catStats[$categoryName]['total_revenue'] += (float) $tx->amount;
            $catStats[$categoryName]['total_qty'] += 1;
        }

        return collect($catStats)->sortByDesc('total_revenue')->values()->map(fn($item) => (object) $item);
    }

    /**
     * Thống kê doanh thu theo ngày từ các giao dịch thanh toán thực nhận
     */
    private function dailyRevenue(): Collection
    {
        return $this->paidTransactionsQuery()
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total_revenue, COUNT(*) as order_count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();
    }

    /**
     * Tổng hợp từ dữ liệu theo ngày sang tháng hoặc năm (tương thích MySQL & SQLite)
     */
    private function periodRevenue(Collection $days, string $period): Collection
    {
        return $days->groupBy(fn ($day) => substr($day->date, 0, $period === 'month' ? 7 : 4))
            ->map(fn (Collection $rows, $key) => (object) [
                $period => (string) $key,
                'total_revenue' => $rows->sum('total_revenue'),
                'order_count' => $rows->sum('order_count'),
            ])->values();
    }

    /**
     * Bảng số liệu báo cáo doanh thu tổng hợp (Lab 8 - B4 View Index)
     */
    public function index()
    {
        $categoryRevenue = $this->categoryRevenue();
        $totalOrders = Order::where('created_at', '<=', now())->count();
        $totalCustomers = DB::table('users')->where('role', '!=', 'admin')->count();
        $revenueByDate = $this->dailyRevenue();
        $revenueByMonth = $this->periodRevenue($revenueByDate, 'month');
        $revenueByYear = $this->periodRevenue($revenueByDate, 'year');
        
        // Tổng doanh thu thực nhận đồng bộ chính xác với Báo cáo thanh toán MoMo & Thu chi
        $totalRevenue = (float) $this->paidTransactionsQuery()->sum('amount');
        $totalMomoRevenue = (float) $this->paidTransactionsQuery()->where('gateway', 'momo')->sum('amount');
        $totalBankRevenue = (float) $this->paidTransactionsQuery()->whereIn('gateway', ['sepay', 'bank_transfer'])->sum('amount');
        $totalCodRevenue = (float) $this->paidTransactionsQuery()->where('gateway', 'cod')->sum('amount');
        $totalPaidTx = $this->paidTransactionsQuery()->count();

        // Thống kê mảng dịch vụ thuê xe
        $totalRentals = Rental::count();
        $paidRentals = Rental::where('payment_status', '!=', 'unpaid')->get();
        $totalRentalRevenue = $paidRentals->sum('total_rental_fee') + $paidRentals->sum('total_driver_fee');
        $totalPlatformRentalFee = $paidRentals->sum('platform_fee') ?: round($totalRentalRevenue * 0.10);

        return view('admin.reports.index', compact(
            'categoryRevenue',
            'totalOrders',
            'totalCustomers',
            'totalRevenue',
            'revenueByDate',
            'revenueByMonth',
            'revenueByYear',
            'totalRentals',
            'totalRentalRevenue',
            'totalPlatformRentalFee',
            'totalMomoRevenue',
            'totalBankRevenue',
            'totalCodRevenue',
            'totalPaidTx'
        ));
    }

    /**
     * Dữ liệu biểu đồ Chart.js (Lab 8 - B4 View Charts)
     */
    public function charts()
    {
        $categories = $this->categoryRevenue();
        $catLabels = $categories->map(fn ($row) => $row->category_name ?? 'Hãng #'.$row->category_id)->all();
        $catRevenue = $categories->pluck('total_revenue')->map(fn ($value) => (float) $value)->all();

        $daily = $this->dailyRevenue();
        $byDate = $daily->keyBy('date');
        $byMonth = $this->periodRevenue($daily, 'month')->keyBy('month');
        $byYear = $this->periodRevenue($daily, 'year');

        $startDay = Carbon::now()->startOfDay()->subDays(29);
        $startMonth = Carbon::now()->startOfMonth()->subMonths(11);

        $revDateLabels = [];
        $revDateData = [];
        $revMonthLabels = [];
        $revMonthData = [];

        // 30 ngày gần nhất
        for ($i = 0; $i < 30; $i++) {
            $date = $startDay->copy()->addDays($i)->toDateString();
            $revDateLabels[] = date('d/m', strtotime($date));
            $revDateData[] = (float) ($byDate->get($date)?->total_revenue ?? 0);
        }

        // 12 tháng gần nhất
        for ($i = 0; $i < 12; $i++) {
            $month = $startMonth->copy()->addMonths($i);
            $revMonthLabels[] = $month->format('m/Y');
            $revMonthData[] = (float) ($byMonth->get($month->format('Y-m'))?->total_revenue ?? 0);
        }

        // Theo các năm
        $revYearLabels = $byYear->pluck('year')->all();
        if (empty($revYearLabels)) {
            $revYearLabels = [date('Y')];
            $revYearData = [(float) $this->paidTransactionsQuery()->sum('amount')];
        } else {
            $revYearData = $byYear->pluck('total_revenue')->map(fn ($value) => (float) $value)->all();
        }

        // Theo phương thức thanh toán
        $momoRev = (float) $this->paidTransactionsQuery()->where('gateway', 'momo')->sum('amount');
        $bankRev = (float) $this->paidTransactionsQuery()->whereIn('gateway', ['sepay', 'bank_transfer'])->sum('amount');
        $codRev = (float) $this->paidTransactionsQuery()->where('gateway', 'cod')->sum('amount');

        $totalRevenue = (float) $this->paidTransactionsQuery()->sum('amount');
        $totalMomoRevenue = $momoRev;
        $totalBankRevenue = $bankRev;
        $totalPaidTx = $this->paidTransactionsQuery()->count();

        $paymentMethodLabels = ['Ví MoMo ATM/QR', 'Chuyển khoản VietQR / SePay', 'Tiền mặt / Showroom (COD)'];
        $paymentMethodRevenue = [$momoRev, $bankRev, $codRev];

        return view('admin.reports.charts', compact(
            'catLabels',
            'catRevenue',
            'revDateLabels',
            'revDateData',
            'revMonthLabels',
            'revMonthData',
            'revYearLabels',
            'revYearData',
            'paymentMethodLabels',
            'paymentMethodRevenue',
            'totalRevenue',
            'totalMomoRevenue',
            'totalBankRevenue',
            'totalPaidTx'
        ));
    }
}

