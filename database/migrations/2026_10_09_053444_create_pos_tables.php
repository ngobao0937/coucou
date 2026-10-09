<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Danh mục
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 2. Món nước
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 12, 0);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // 3. Bàn
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // VD: Bàn 01, Bàn 02
            $table->string('area')->default('Tầng trệt'); // Tầng trệt, Sân thượng...
            $table->enum('status', ['empty', 'occupied'])->default('empty'); // Trạng thái bàn hiện tại
            $table->timestamps();
        });

        // 4. Đơn hàng
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->foreignId('table_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Nhân viên tạo
            $table->decimal('total_amount', 12, 0)->default(0);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending'); // pending: đang phục vụ, completed: hoàn thành, cancelled: hủy
            $table->boolean('is_printed')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // 5. Chi tiết đơn hàng
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 12, 0);
            $table->string('note')->nullable(); // Ghi chú riêng cho món (VD: ít đường, 50% đá)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
