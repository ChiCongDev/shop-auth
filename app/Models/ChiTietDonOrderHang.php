<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietDonOrderHang extends Model
{
    protected $table = 'chi_tiet_don_order_hangs';

    protected $fillable = [
        'don_order_hang_id',
        'san_pham_id',
        'so_luong',
        'so_luong_da_ve',
        'so_luong_da_chuyen',
        'gia_ban_du_kien',
        'trang_thai',
        'don_hang_id',
        'ghi_chu',
    ];

    protected $casts = [
        'so_luong' => 'integer',
        'so_luong_da_ve' => 'integer',
        'so_luong_da_chuyen' => 'integer',
        'gia_ban_du_kien' => 'decimal:0',
    ];

    public function donOrderHang()
    {
        return $this->belongsTo(DonOrderHang::class, 'don_order_hang_id');
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'don_hang_id');
    }
}
