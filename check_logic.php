<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Đếm nhóm ma_chung theo từng trường hợp
$result = DB::select("
    SELECT
        SUM(CASE WHEN kh_sum.tong IS NULL THEN 1 ELSE 0 END) as chua_khai_kho,
        SUM(CASE WHEN kh_sum.tong = 0 THEN 1 ELSE 0 END) as da_khai_0,
        SUM(CASE WHEN kh_sum.tong > 0 THEN 1 ELSE 0 END) as con_hang
    FROM (
        SELECT sp.ma_chung, SUM(kh.so_luong_ton) as tong
        FROM san_phams sp
        LEFT JOIN kho_hang_san_phams kh ON sp.id = kh.san_pham_id
        GROUP BY sp.ma_chung
    ) kh_sum
");

echo "=== PHÂN TÍCH THEO NHÓM ma_chung ===\n";
foreach ($result as $r) {
    echo "Chưa khai kho (NULL):  " . $r->chua_khai_kho . " nhóm → COALESCE = 1 → HIỆN trên web\n";
    echo "Đã khai kho, tồn = 0: " . $r->da_khai_0    . " nhóm → COALESCE = 0 → ẨN trên web\n";
    echo "Còn hàng (tồn > 0):   " . $r->con_hang     . " nhóm → HIỆN trên web\n";
}

// Hiện tại web đang hiện bao nhiêu nhóm?
$hienTren = DB::table('san_phams as sp')
    ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
    ->selectRaw('MIN(sp.id) as min_id')
    ->groupBy('sp.ma_chung')
    ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 1) > 0')
    ->get();

echo "\n=== WEB ĐANG HIỂN THỊ: " . $hienTren->count() . " nhóm sản phẩm ===\n";

// Nếu đổi sang COALESCE = 0 thì còn bao nhiêu?
$hienTrenStrict = DB::table('san_phams as sp')
    ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
    ->selectRaw('MIN(sp.id) as min_id')
    ->groupBy('sp.ma_chung')
    ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0')
    ->get();

echo "Nếu đổi COALESCE=0 (nghiêm ngặt hơn): " . $hienTrenStrict->count() . " nhóm\n";
