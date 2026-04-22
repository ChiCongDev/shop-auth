<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gio_hang_chi_tiets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gio_hang_id');
            $table->unsignedBigInteger('san_pham_id');  // map sang bảng san_phams chung
            $table->integer('so_luong')->default(1);
            $table->decimal('gia', 15, 2)->default(0); // giá tại thời điểm thêm vào giỏ
            $table->timestamps();

            $table->index('gio_hang_id');
            $table->index('san_pham_id');
            $table->unique(['gio_hang_id', 'san_pham_id']); // 1 sản phẩm chỉ xuất hiện 1 lần
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gio_hang_chi_tiets');
    }
};
