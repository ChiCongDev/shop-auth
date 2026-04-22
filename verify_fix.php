<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Service\SanPhamService;

$service = new SanPhamService();

// Test danh sách
$danhSach = $service->layDanhSach(perPage: 100);
echo "=== DANH SÁCH SẢN PHẨM TRÊN WEB SAU KHI SỬA ===\n";
echo "Tổng số nhóm hiển thị: " . $danhSach->total() . "\n\n";

foreach ($danhSach as $sp) {
    $ton = $sp->khoHangSanPhams->sum('so_luong_ton');
    echo sprintf("  %-45s | Tồn: %d\n",
        mb_substr($sp->ten_chung ?? $sp->ten, 0, 45),
        $ton
    );
}

echo "\n=== Nhãn hiệu còn hàng (sidebar filter) ===\n";
$nhanHieus = $service->layDanhSachNhanHieu();
echo implode(', ', $nhanHieus) . "\n";
