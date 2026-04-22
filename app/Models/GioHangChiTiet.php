<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GioHangChiTiet extends Model
{
    protected $table = 'gio_hang_chi_tiets';

    protected $fillable = [
        'gio_hang_id',
        'san_pham_id',
        'so_luong',
        'gia',
    ];

    public function gioHang()
    {
        return $this->belongsTo(GioHang::class, 'gio_hang_id');
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    /**
     * Thành tiền = số lượng × giá
     */
    public function getThanhTienAttribute(): float
    {
        return $this->so_luong * $this->gia;
    }
}
