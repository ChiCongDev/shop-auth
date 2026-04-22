<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$s = new App\Http\Service\SanPhamService();
$r = $s->laySanPhamMoiNhat(8);
echo "Count: " . $r->count() . "\n";
foreach ($r as $sp) {
    echo "  " . ($sp->ten_chung ?? $sp->ten) . " | nhap: " . ($sp->ngay_nhap ?? 'NULL') . "\n";
}
