<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "====================================================\n";
echo "   KIỂM TRA TỒN KHO SẢN PHẨM - SHOP AUTH\n";
echo "====================================================\n\n";

// Lấy tất cả sản phẩm cùng tổng tồn kho
$sanPhams = DB::table('san_phams as sp')
    ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
    ->selectRaw('sp.id, sp.ma_sku, sp.ten, sp.ten_chung, sp.ma_chung, sp.nhan_hieu, sp.loai_san_pham, COALESCE(SUM(kh.so_luong_ton), 0) as tong_ton')
    ->groupBy('sp.id', 'sp.ma_sku', 'sp.ten', 'sp.ten_chung', 'sp.ma_chung', 'sp.nhan_hieu', 'sp.loai_san_pham')
    ->orderBy('tong_ton', 'asc')
    ->orderBy('sp.ten_chung')
    ->get();

$hetHang = $sanPhams->filter(fn($r) => $r->tong_ton == 0);
$conHang = $sanPhams->filter(fn($r) => $r->tong_ton > 0);

// ===== HẾT HÀNG =====
echo "❌ BIẾN THỂ HẾT HÀNG (tồn kho = 0) — Có " . $hetHang->count() . " biến thể\n";
echo str_repeat("-", 110) . "\n";
printf("%-6s | %-18s | %-42s | %-25s | %-12s | %s\n",
    "ID", "Mã SKU", "Tên biến thể", "Tên chung (nhóm)", "Nhãn hiệu", "Tồn kho");
echo str_repeat("-", 110) . "\n";

foreach ($hetHang as $sp) {
    printf("%-6s | %-18s | %-42s | %-25s | %-12s | %s\n",
        $sp->id,
        mb_substr($sp->ma_sku ?? '---', 0, 18),
        mb_substr($sp->ten ?? '---', 0, 42),
        mb_substr($sp->ten_chung ?? '---', 0, 25),
        mb_substr($sp->nhan_hieu ?? '---', 0, 12),
        $sp->tong_ton
    );
}

echo "\n";

// ===== CÒN HÀNG =====
echo "✅ BIẾN THỂ CÒN HÀNG — Có " . $conHang->count() . " biến thể\n";
echo str_repeat("-", 110) . "\n";
printf("%-6s | %-18s | %-42s | %-25s | %-12s | %s\n",
    "ID", "Mã SKU", "Tên biến thể", "Tên chung (nhóm)", "Nhãn hiệu", "Tồn kho");
echo str_repeat("-", 110) . "\n";

foreach ($conHang as $sp) {
    printf("%-6s | %-18s | %-42s | %-25s | %-12s | %s\n",
        $sp->id,
        mb_substr($sp->ma_sku ?? '---', 0, 18),
        mb_substr($sp->ten ?? '---', 0, 42),
        mb_substr($sp->ten_chung ?? '---', 0, 25),
        mb_substr($sp->nhan_hieu ?? '---', 0, 12),
        $sp->tong_ton
    );
}

echo "\n";
echo "====================================================\n";
echo "TỔNG KẾT:\n";
echo "  Tổng số biến thể: " . $sanPhams->count() . "\n";
echo "  ❌ Hết hàng:      " . $hetHang->count() . "\n";
echo "  ✅ Còn hàng:      " . $conHang->count() . "\n";

// ===== NHÓM SẢN PHẨM (ten_chung) HOÀN TOÀN HẾT HÀNG =====
echo "\n====================================================\n";
echo "🔴 NHÓM SẢN PHẨM HOÀN TOÀN HẾT HÀNG (tất cả biến thể = 0):\n";
echo str_repeat("-", 90) . "\n";

$nhoms = DB::table('san_phams as sp')
    ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
    ->selectRaw('sp.ten_chung, sp.nhan_hieu, sp.loai_san_pham, COUNT(DISTINCT sp.id) as so_bien_the, COALESCE(SUM(kh.so_luong_ton), 0) as tong_ton_nhom')
    ->groupBy('sp.ten_chung', 'sp.nhan_hieu', 'sp.loai_san_pham')
    ->having('tong_ton_nhom', '=', 0)
    ->orderBy('sp.ten_chung')
    ->get();

if ($nhoms->isEmpty()) {
    echo "  (Không có nhóm nào hoàn toàn hết hàng)\n";
} else {
    printf("%-45s | %-14s | %-15s | %-8s | %s\n",
        "Tên chung (nhóm)", "Nhãn hiệu", "Loại SP", "Biến thể", "Tổng tồn");
    echo str_repeat("-", 90) . "\n";
    foreach ($nhoms as $n) {
        printf("%-45s | %-14s | %-15s | %-8s | %s\n",
            mb_substr($n->ten_chung ?? '---', 0, 45),
            mb_substr($n->nhan_hieu ?? '---', 0, 14),
            mb_substr($n->loai_san_pham ?? '---', 0, 15),
            $n->so_bien_the,
            $n->tong_ton_nhom
        );
    }
}

// ===== NHÓM CÒN MỘT PHẦN HẾT HÀNG =====
echo "\n====================================================\n";
echo "🟡 NHÓM SẢN PHẨM CÓ MỘT SỐ BIẾN THỂ HẾT HÀNG:\n";
echo str_repeat("-", 90) . "\n";

$nhomMixed = DB::table('san_phams as sp')
    ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
    ->selectRaw('sp.ten_chung, sp.nhan_hieu, sp.loai_san_pham,
        COUNT(DISTINCT sp.id) as tong_bien_the,
        SUM(CASE WHEN COALESCE(kh.so_luong_ton,0) = 0 THEN 1 ELSE 0 END) as bien_the_het,
        COALESCE(SUM(kh.so_luong_ton), 0) as tong_ton_nhom')
    ->groupBy('sp.ten_chung', 'sp.nhan_hieu', 'sp.loai_san_pham')
    ->havingRaw('bien_the_het > 0 AND tong_ton_nhom > 0')
    ->orderBy('sp.ten_chung')
    ->get();

if ($nhomMixed->isEmpty()) {
    echo "  (Không có nhóm nào trong tình trạng này)\n";
} else {
    printf("%-45s | %-14s | %-10s | %-8s | %s\n",
        "Tên chung (nhóm)", "Nhãn hiệu", "Biến thể HH", "Tổng BT", "Tổng tồn");
    echo str_repeat("-", 90) . "\n";
    foreach ($nhomMixed as $n) {
        printf("%-45s | %-14s | %-10s | %-8s | %s\n",
            mb_substr($n->ten_chung ?? '---', 0, 45),
            mb_substr($n->nhan_hieu ?? '---', 0, 14),
            $n->bien_the_het . "/" . $n->tong_bien_the,
            $n->tong_bien_the,
            $n->tong_ton_nhom
        );
    }
}

echo "\n====================================================\n";
