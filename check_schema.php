<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Xem schema bảng san_phams
$cols = DB::select('DESCRIBE san_phams');
echo "=== CỘT BẢNG san_phams ===\n";
foreach ($cols as $c) {
    echo "  " . $c->Field . " [" . $c->Type . "]\n";
}

echo "\n=== CỘT BẢNG kho_hang_san_phams ===\n";
$cols2 = DB::select('DESCRIBE kho_hang_san_phams');
foreach ($cols2 as $c) {
    echo "  " . $c->Field . " [" . $c->Type . "]\n";
}

// Sample 3 rows
echo "\n=== SAMPLE san_phams (5 rows) ===\n";
$rows = DB::table('san_phams')->limit(5)->get();
foreach ($rows as $r) {
    print_r((array)$r);
}
