<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khach_hang_mat_khau', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khach_hang_id')->unique();
            $table->string('mat_khau');
            $table->rememberToken();
            $table->timestamps();

            // Không dùng foreign key constraint để tránh conflict với hệ thống nội bộ
            $table->index('khach_hang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khach_hang_mat_khau');
    }
};
