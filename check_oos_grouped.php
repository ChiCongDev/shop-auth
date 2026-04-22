<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$nhoms = DB::table('san_phams as sp')
    ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
    ->selectRaw('sp.ten_chung, sp.nhan_hieu, sp.loai_san_pham, COUNT(DISTINCT sp.id) as so_bien_the, COALESCE(SUM(kh.so_luong_ton), 0) as tong_ton_nhom')
    ->groupBy('sp.ten_chung', 'sp.nhan_hieu', 'sp.loai_san_pham')
    ->having('tong_ton_nhom', '=', 0)
    ->orderBy('sp.loai_san_pham')
    ->orderBy('sp.ten_chung')
    ->get();

echo "Tổng nhóm hoàn toàn hết hàng: " . $nhoms->count() . "\n";

$grouped = $nhoms->groupBy('loai_san_pham');
foreach ($grouped as $loai => $items) {
    echo "\n[" . ($loai ?: 'Không phân loại') . "] — " . $items->count() . " nhóm\n";
    foreach ($items as $i) {
        echo "  - " . $i->ten_chung . " [" . $i->nhan_hieu . "] (" . $i->so_bien_the . " biến thể)\n";
    }
}
