<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

// Sản phẩm mới nhập kho từ phiếu nhập
echo "=== SP MỚI NHẬP QUA phieu_nhap_chi_tiets ===\n";
$rows = DB::table('phieu_nhap_chi_tiets as pnct')
    ->join('phieu_nhaps as pn', 'pnct.phieu_nhap_id', '=', 'pn.id')
    ->join('san_phams as sp', 'pnct.san_pham_id', '=', 'sp.id')
    ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
    ->where('pn.trang_thai', 'da_nhap')
    ->select('sp.ma_chung', 'sp.ten_chung', 'sp.ten', 'pnct.san_pham_id', 'pn.created_at as ngay_nhap', DB::raw('COALESCE(SUM(kh.so_luong_ton),0) as ton_kho'))
    ->groupBy('sp.ma_chung', 'sp.ten_chung', 'sp.ten', 'pnct.san_pham_id', 'pn.created_at')
    ->orderByDesc('pn.created_at')
    ->limit(20)
    ->get();

foreach ($rows as $r) {
    echo sprintf("  ma_chung:%-25s | ten:%-40s | ngay_nhap:%s | ton:%d\n",
        $r->ma_chung, mb_substr($r->ten_chung ?? $r->ten, 0, 40), $r->ngay_nhap, $r->ton_kho
    );
}

// Lấy 1 đại diện mỗi nhóm ma_chung theo đơn nhập mới nhất, còn hàng
echo "\n=== NHÓM SP MỚI NHẬP (đại diện 1 per ma_chung, còn hàng) ===\n";
$nhoms = DB::table('phieu_nhap_chi_tiets as pnct')
    ->join('phieu_nhaps as pn', 'pnct.phieu_nhap_id', '=', 'pn.id')
    ->join('san_phams as sp', 'pnct.san_pham_id', '=', 'sp.id')
    ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
    ->where('pn.trang_thai', 'da_nhap')
    ->selectRaw('sp.ma_chung, MAX(pn.created_at) as ngay_nhap_moi_nhat, COALESCE(SUM(kh.so_luong_ton),0) as ton_kho')
    ->groupBy('sp.ma_chung')
    ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0')
    ->orderByDesc('ngay_nhap_moi_nhat')
    ->limit(10)
    ->get();

foreach ($nhoms as $n) {
    echo sprintf("  ma_chung:%-25s | ngay_nhap:%s | ton:%d\n",
        $n->ma_chung, $n->ngay_nhap_moi_nhat, $n->ton_kho
    );
}
