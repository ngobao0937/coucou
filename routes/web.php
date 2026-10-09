<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\OrderController;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/api/check-new-orders', function (Request $request) {
        $enabledAt = $request->query('enabled_at');

        $query = Order::with(['table', 'items.product'])
            ->where('status', 'pending')
            ->where('is_printed', false);

        if ($enabledAt) {
            $query->where('created_at', '>=', date('Y-m-d H:i:s', strtotime($enabledAt)));
        }

        $order = $query->oldest()->first();

        if ($order) {
            // ĐÁNH DẤU ĐÃ IN NGAY TẠI ĐÂY để giải thoát vòng lặp vô tận
            $order->update(['is_printed' => true]);

            return response()->json([
                'has_new_order' => true,
                'order' => $order
            ]);
        }

        return response()->json(['has_new_order' => false]);
    });

    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

    Route::get('/', fn() => redirect('/dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');
    Route::get('/files/{file}/stream', [FileController::class, 'stream'])->name('files.stream');

    Route::get('/nguoi-dung', [UserController::class, 'index'])->name('users.index');
    Route::post('/nguoi-dung', [UserController::class, 'store'])->name('users.store');
    Route::put('/nguoi-dung/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/nguoi-dung/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/nhat-ky-dang-nhap', [AuthLogController::class, 'index'])->name('auth-logs.index');

    // Quản lý Bàn
    Route::get('/quan-ly-ban', [TableController::class, 'index'])->name('tables.index');
    Route::post('/quan-ly-ban', [TableController::class, 'store'])->name('tables.store');
    Route::put('/quan-ly-ban/{id}', [TableController::class, 'update'])->name('tables.update');
    Route::delete('/quan-ly-ban/{id}', [TableController::class, 'destroy'])->name('tables.destroy');

    // Quản lý Danh Mục
    Route::get('/danh-muc', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/danh-muc', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/danh-muc/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/danh-muc/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Quản lý Món Nước
    Route::get('/mon-nuoc', [ProductController::class, 'index'])->name('products.index');
    Route::post('/mon-nuoc', [ProductController::class, 'store'])->name('products.store');
    Route::put('/mon-nuoc/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/mon-nuoc/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Quản lý Đơn Hàng (Order Management)
    Route::get('/don-hang', [OrderController::class, 'index'])->name('orders.index');
    Route::put('/don-hang/{id}', [OrderController::class, 'update'])->name('orders.update');
    Route::post('/don-hang/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // POS Order
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/table/{table}', [PosController::class, 'order'])->name('pos.order');
    Route::post('/pos/table/{table}/save', [PosController::class, 'saveOrder'])->name('pos.saveOrder');
    Route::post('/pos/table/{table}/complete', [PosController::class, 'completeOrder'])->name('pos.completeOrder');
});
