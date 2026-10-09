<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Tiếp nhận tham số bộ lọc thời gian (Default: tháng này)
        $filterType = $request->input('filter_type', 'this_month');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $now = Carbon::now();

        // Xác định khoảng ngày lọc
        switch ($filterType) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'this_week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            case 'custom':
                $start = $fromDate ? Carbon::parse($fromDate)->startOfDay() : $now->copy()->startOfMonth();
                $end = $toDate ? Carbon::parse($toDate)->endOfDay() : $now->copy()->endOfDay();
                break;
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
        }

        // Query cơ sở các đơn hàng hoàn thành trong khoảng thời gian
        $completedOrdersQuery = Order::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end]);

        // 2. Tính các chỉ số KPI tổng quan
        $totalRevenue = (clone $completedOrdersQuery)->sum('total_amount');
        $totalOrders = (clone $completedOrdersQuery)->count();
        $totalItemsSold = OrderItem::whereIn('order_id', (clone $completedOrdersQuery)->pluck('id'))->sum('quantity');
        $averageOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;

        // 3. Lấy dữ liệu biểu đồ doanh thu theo ngày
        $chartData = (clone $completedOrdersQuery)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('COUNT(id) as order_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // 4. Thống kê Top 5 sản phẩm bán chạy nhất trong kỳ
        $topProducts = OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(price * quantity) as total_sales')
            )
            ->whereIn('order_id', (clone $completedOrdersQuery)->pluck('id'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // 5. Danh sách 10 đơn hàng mới nhất
        $recentOrders = Order::with(['table', 'user'])
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'filters' => [
                'filter_type' => $filterType,
                'from_date' => $start->format('Y-m-d'),
                'to_date' => $end->format('Y-m-d'),
            ],
            'kpi' => [
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'total_items_sold' => $totalItemsSold,
                'average_order_value' => $averageOrderValue,
            ],
            'chartData' => $chartData,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
        ]);
    }
}
