<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PosController extends Controller
{
    public function index()
    {
        $tables = Table::with(['currentOrder.items.product'])->get();

        return Inertia::render('Pos/Tables', [
            'tables' => $tables,
        ]);
    }

    public function order(Table $table)
    {
        $categories = Category::with(['products' => function($q) {
            $q->where('is_available', true);
        }])->get();

        $table->load(['currentOrder.items.product']);

        return Inertia::render('Pos/Order', [
            'table' => $table,
            'categories' => $categories,
            'currentOrder' => $table->currentOrder,
        ]);
    }

    public function saveOrder(Request $request, Table $table)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        DB::transaction(function () use ($request, $table) {
            // Kiểm tra xem bàn đã có đơn hàng pending chưa
            $order = Order::where('table_id', $table->id)
                ->where('status', 'pending')
                ->first();

            // Nếu chưa có đơn hàng thì tạo mới và tự động sinh cột 'code' dựa trên Timestamp
            if (!$order) {
                // Tạo mã đơn dựa theo ngày giờ hiện tại: ORD + YmdHis + số ngẫu nhiên 3 chữ số để tránh trùng lặp nếu thao tác cùng giây
                $generatedCode = 'DH' . date('YmdHis') . rand(100, 999);

                $order = Order::create([
                    'table_id' => $table->id,
                    'user_id' => auth()->id(),
                    'code' => $generatedCode,
                    'status' => 'pending',
                    'total_amount' => 0,
                    'is_printed' => false,
                ]);
            }

            // Xóa danh sách món cũ để cập nhật danh sách món mới nhất
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

            // Cập nhật tổng tiền và đặt is_printed = false để Laptop quầy nhận biết đơn cần in
            $order->update([
                'total_amount' => $total,
                'is_printed' => false,
            ]);

            $table->update(['status' => 'occupied']);
        });

        return back()->with('success', 'Đã lưu đơn hàng!');
    }

    public function completeOrder(Table $table)
    {
        DB::transaction(function () use ($table) {
            $order = $table->currentOrder;
            if ($order) {
                $order->update(['status' => 'completed']);
            }
            $table->update(['status' => 'empty']);
        });

        return redirect()->route('pos.index')->with('success', 'Đã thanh toán & giải phóng bàn!');
    }
}
