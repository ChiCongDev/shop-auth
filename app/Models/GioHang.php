<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GioHang extends Model
{
    protected $table = 'gio_hangs';

    protected $fillable = ['khach_hang_id'];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    public function chiTiets()
    {
        return $this->hasMany(GioHangChiTiet::class, 'gio_hang_id');
    }

    /**
     * Tổng số sản phẩm trong giỏ
     */
    public function getTongSoLuongAttribute(): int
    {
        return $this->chiTiets->sum('so_luong');
    }

    /**
     * Tổng tiền giỏ hàng
     */
    public function getTongTienAttribute(): float
    {
        return $this->chiTiets->sum(fn($item) => $item->so_luong * $item->gia);
    }
}
