<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model bảng kho_hang_san_phams (dùng chung với nội bộ)
 * Cột tồn kho thực tế: so_luong_ton (không phải ton_kho)
 */
class KhoHangSanPham extends Model
{
    protected $table = 'kho_hang_san_phams';

    protected $fillable = ['san_pham_id', 'kho_hang_id', 'so_luong_ton'];

    /**
     * Alias để code cũ vẫn hoạt động:
     * Khi gọi $kho->ton_kho → trả về so_luong_ton
     */
    public function getTonKhoAttribute(): int
    {
        return (int) ($this->attributes['so_luong_ton'] ?? 0);
    }
}
