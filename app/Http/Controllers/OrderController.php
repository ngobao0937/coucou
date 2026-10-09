<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    // Danh sách tất cả đơn hàng
    public function index(Request $request)
    {
        $query = Order::with(['table', 'user', 'items.product'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('table', function ($tblQuery) use ($search) {
                    $tblQuery->where('name', 'like', "%{$search}%");
                })->orWhere('id', $search);
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'search']), // Đã sửa lỗi tại đây
        ]);
    }

    // Cập nhật món / chi tiết trong đơn hàng
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        DB::transaction(function () use ($request, $order) {
            $order->items()->delete();

            $total = 0;
            foreach ($request->items as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'note' => $item['note'] ?? null,
                ]);
            }

            $order->update(['total_amount' => $total]);
        });

        return back()->with('success', 'Cập nhật đơn hàng thành công!');
    }

    // Hủy đơn hàng
    public function cancel($id)
    {
        DB::transaction(function () use ($id) {
            $order = Order::findOrFail($id);
            $order->update(['status' => 'cancelled']);

            // Giải phóng bàn nếu đơn đang phục vụ
            if ($order->table) {
                $order->table->update(['status' => 'empty']);
            }
        });

        return back()->with('success', 'Đã hủy đơn hàng!');
    }
}
