<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Tìm sản phẩm "Smoke Unlocker"
$variants = DB::table('san_phams')
    ->where('ten_chung', 'like', '%Smoke Unlocker%')
    ->get(['id', 'ma_chung', 'ten', 'ten_chung']);

echo "=== VARIANTS ===\n";
foreach ($variants as $v) {
    $kho = DB::table('kho_hang_san_phams')->where('san_pham_id', $v->id)->get();
    $sum = $kho->sum('so_luong_ton');
    echo "ID:{$v->id} | ma_chung:{$v->ma_chung} | ten:{$v->ten} | kho_records:{$kho->count()} | ton_kho:{$sum}\n";
}

// 2. Kiểm tra nhóm ma_chung
if ($variants->isNotEmpty()) {
    $mc = $variants->first()->ma_chung;
    $allVariants = DB::table('san_phams')->where('ma_chung', $mc)->pluck('id');
    echo "\n=== ALL IDs in group {$mc}: " . $allVariants->implode(',') . " ===\n";

    foreach ($allVariants as $id) {
        $kho = DB::table('kho_hang_san_phams')->where('san_pham_id', $id)->get();
        $sum = $kho->sum('so_luong_ton');
        echo "  ID:{$id} => kho_records:{$kho->count()}, ton_kho:{$sum}\n";
    }

    // 3. Test COALESCE query
    $result = DB::table('san_phams as sp')
        ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
        ->selectRaw('sp.ma_chung, MIN(sp.id) as min_id, COALESCE(SUM(kh.so_luong_ton), 1) as stock')
        ->where('sp.ma_chung', $mc)
        ->groupBy('sp.ma_chung')
        ->first();
    echo "\n=== COALESCE QUERY RESULT ===\n";
    echo "ma_chung: {$result->ma_chung} | min_id: {$result->min_id} | stock: {$result->stock}\n";
    echo "Would show: " . ($result->stock > 0 ? 'YES' : 'NO') . "\n";
}
