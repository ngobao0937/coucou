<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo Tài khoản Admin / Nhân viên
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Quản Trị Viên',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12348765'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['username' => 'nhanvien1'],
            [
                'name' => 'Nhân Viên Order 01',
                'email' => 'nhanvien1@gmail.com',
                'password' => Hash::make('12348765'),
                'role' => 'user',
            ]
        );

        // 2. Tạo Danh mục món nước
        $catCaphe    = Category::create(['name' => 'Cà Phê']);
        $catColdBrew = Category::create(['name' => 'Cold Brew']);
        $catTra      = Category::create(['name' => 'Trà']);
        $catMatcha   = Category::create(['name' => 'Matcha']);
        $catTraSua   = Category::create(['name' => 'Trà Sữa']);
        $catChocolat = Category::create(['name' => 'Sô-cô-la']);

        // 3. Món nước: CÀ PHÊ
        Product::create(['category_id' => $catCaphe->id, 'name' => 'Cà Phê Đen', 'price' => 20000, 'is_available' => true]);
        Product::create(['category_id' => $catCaphe->id, 'name' => 'Cà Phê Sữa', 'price' => 22000, 'is_available' => true]);
        Product::create(['category_id' => $catCaphe->id, 'name' => 'Cà Phê Sữa Tươi', 'price' => 25000, 'is_available' => true]);
        Product::create(['category_id' => $catCaphe->id, 'name' => 'Cà Phê Bánh Biscoff', 'price' => 45000, 'is_available' => false]);
        Product::create(['category_id' => $catCaphe->id, 'name' => 'Cà Phê Caramel Marshmallow', 'price' => 37000, 'is_available' => true]);
        Product::create(['category_id' => $catCaphe->id, 'name' => 'Cà Phê Hạnh Nhân', 'price' => 27000, 'is_available' => true]);

        // 4. Món nước: COLD BREW
        Product::create(['category_id' => $catColdBrew->id, 'name' => 'Cold Brew Yuzu', 'price' => 37000, 'is_available' => true]);
        Product::create(['category_id' => $catColdBrew->id, 'name' => 'Cold Brew Cam', 'price' => 37000, 'is_available' => true]);
        Product::create(['category_id' => $catColdBrew->id, 'name' => 'Cold Brew Bưởi Hồng', 'price' => 37000, 'is_available' => true]);
        Product::create(['category_id' => $catColdBrew->id, 'name' => 'Cold Brew Việt Quất', 'price' => 37000, 'is_available' => true]);
        Product::create(['category_id' => $catColdBrew->id, 'name' => 'Cold Brew Trái Mọng', 'price' => 37000, 'is_available' => true]);
        Product::create(['category_id' => $catColdBrew->id, 'name' => 'Cold Brew Truyền Thống', 'price' => 35000, 'is_available' => true]);

        // 5. Món nước: TRÀ
        Product::create(['category_id' => $catTra->id, 'name' => 'Trà Dâu Chanh Vàng', 'price' => 30000, 'is_available' => true]);
        Product::create(['category_id' => $catTra->id, 'name' => 'Trà Xoài', 'price' => 30000, 'is_available' => true]);
        Product::create(['category_id' => $catTra->id, 'name' => 'Trà Coucou', 'price' => 30000, 'is_available' => true]);
        Product::create(['category_id' => $catTra->id, 'name' => 'Trà Ổi', 'price' => 30000, 'is_available' => true]);
        Product::create(['category_id' => $catTra->id, 'name' => 'Trà Bưởi Hồng', 'price' => 30000, 'is_available' => true]);
        Product::create(['category_id' => $catTra->id, 'name' => 'Trà Việt Quất', 'price' => 30000, 'is_available' => true]);
        Product::create(['category_id' => $catTra->id, 'name' => 'Trà Yuzu', 'price' => 30000, 'is_available' => true]);
        Product::create(['category_id' => $catTra->id, 'name' => 'Trà Hồng Kông', 'price' => 30000, 'is_available' => true]);

        // 6. Món nước: MATCHA
        Product::create(['category_id' => $catMatcha->id, 'name' => 'Matcha Latte', 'price' => 35000, 'is_available' => true]);
        Product::create(['category_id' => $catMatcha->id, 'name' => 'Matcha Latte Dâu', 'price' => 37000, 'is_available' => true]);
        Product::create(['category_id' => $catMatcha->id, 'name' => 'Matcha Latte Xoài', 'price' => 37000, 'is_available' => true]);
        Product::create(['category_id' => $catMatcha->id, 'name' => 'Matcha Latte Bánh Biscoff', 'price' => 45000, 'is_available' => false]);
        Product::create(['category_id' => $catMatcha->id, 'name' => 'Matcha Latte Oreo', 'price' => 37000, 'is_available' => true]);
        Product::create(['category_id' => $catMatcha->id, 'name' => 'Matcha Latte Kem Hồng', 'price' => 37000, 'is_available' => false]);

        // 7. Món nước: TRÀ SỮA
        Product::create(['category_id' => $catTraSua->id, 'name' => 'Trà Sữa Hương Hoa', 'price' => 37000, 'is_available' => false]);
        Product::create(['category_id' => $catTraSua->id, 'name' => 'Trà Sữa Lam Nho', 'price' => 37000, 'is_available' => false]);
        Product::create(['category_id' => $catTraSua->id, 'name' => 'Trà Sữa Vani', 'price' => 32000, 'is_available' => true]);

        // 8. Món nước: SÔ-CÔ-LA
        Product::create(['category_id' => $catChocolat->id, 'name' => 'Sô-cô-la Marshmallow', 'price' => 37000, 'is_available' => true]);
        Product::create(['category_id' => $catChocolat->id, 'name' => 'Sô-cô-la Latte', 'price' => 32000, 'is_available' => true]);

        // 9. Tạo sơ đồ bàn
        for ($i = 1; $i <= 20; $i++) {
            Table::create([
                'name' => 'Bàn ' . sprintf('%02d', $i),
                'area' => 'Sảnh',
                'status' => 'empty',
            ]);
        }
    }
}
