<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
echo "=== TẤT CẢ BẢNG TRONG DATABASE ===\n";
foreach ($tables as $t) {
    $arr = (array)$t;
    $name = reset($arr);
    $cols = DB::select("DESCRIBE `{$name}`");
    $colNames = array_map(fn($c) => $c->Field, $cols);
    echo "  {$name}: [" . implode(', ', $colNames) . "]\n";
}
