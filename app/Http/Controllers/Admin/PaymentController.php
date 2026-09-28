<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Báo cáo thống kê và danh sách giao dịch thanh toán (MoMo, VietQR, COD)
     */
    public function index(Request $request)
    {
        $query = PaymentTransaction::with(['order.user', 'rental.user']);

        // 1. Lọc theo cổng thanh toán (momo, bank_transfer, cod)
        if ($request->filled('gateway')) {
            $query->where('gateway', $request->input('gateway'));
        }

        // 2. Lọc theo trạng thái giao dịch (paid, pending, failed)
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 3. Tìm kiếm theo mã giao dịch MoMo, mã đơn hàng, mã đơn thuê, thông điệp
        if ($request->filled('keyword')) {
            $kw = trim($request->input('keyword'));
            $query->where(function ($q) use ($kw) {
                $q->where('transaction_id', 'like', "%{$kw}%")
                  ->orWhere('gateway_order_id', 'like', "%{$kw}%")
                  ->orWhere('message', 'like', "%{$kw}%")
                  ->orWhereHas('order', function ($oq) use ($kw) {
                      $oq->where('order_code', 'like', "%{$kw}%")
                         ->orWhere('customer_name', 'like', "%{$kw}%")
                         ->orWhere('customer_phone', 'like', "%{$kw}%");
                  })
                  ->orWhereHas('rental', function ($rq) use ($kw) {
                      $rq->where('rental_code', 'like', "%{$kw}%")
                         ->orWhere('customer_name', 'like', "%{$kw}%")
                         ->orWhere('customer_phone', 'like', "%{$kw}%");
                  });
            });
        }

        // 4. Lọc theo ngày
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Phân trang
        $transactions = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // 5. Thống kê báo cáo KPI tổng thể MoMo và các cổng thanh toán
        $totalMomoRevenue = PaymentTransaction::where('gateway', 'momo')->where('status', 'paid')->sum('amount');
        $totalMomoCount = PaymentTransaction::where('gateway', 'momo')->count();
        $totalMomoSuccess = PaymentTransaction::where('gateway', 'momo')->where('status', 'paid')->count();
        $totalMomoFailed = PaymentTransaction::where('gateway', 'momo')->where('status', 'failed')->count();
        $totalMomoPending = PaymentTransaction::where('gateway', 'momo')->where('status', 'pending')->count();

        // Doanh thu các cổng khác
        $totalBankRevenue = PaymentTransaction::where('gateway', 'bank_transfer')->where('status', 'paid')->sum('amount');
        $totalCodRevenue = PaymentTransaction::where('gateway', 'cod')->where('status', 'paid')->sum('amount');
        $grandTotalRevenue = PaymentTransaction::where('status', 'paid')->sum('amount');

        $momoSuccessRate = $totalMomoCount > 0 ? round(($totalMomoSuccess / $totalMomoCount) * 100, 1) : 0;

        return view('admin.payments.index', compact(
            'transactions',
            'totalMomoRevenue',
            'totalMomoCount',
            'totalMomoSuccess',
            'totalMomoFailed',
            'totalMomoPending',
            'totalBankRevenue',
            'totalCodRevenue',
            'grandTotalRevenue',
            'momoSuccessRate'
        ));
    }
}
