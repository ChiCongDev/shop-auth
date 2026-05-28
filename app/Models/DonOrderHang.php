<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonOrderHang extends Model
{
    protected $table = 'don_order_hangs';

    protected $fillable = [
        'ma_don_order',
        'trang_thai',
        'ghi_chu',
        'khach_hang_id',
        'nhan_vien_id',
        'don_hang_id',
    ];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    public function nhanVien()
    {
        return $this->belongsTo(NhanVien::class, 'nhan_vien_id');
    }

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'don_hang_id');
    }

    public function chiTiets()
    {
        return $this->hasMany(ChiTietDonOrderHang::class, 'don_order_hang_id');
    }

    public function lichSus()
    {
        return $this->hasMany(LichSuDonOrderHang::class, 'don_order_hang_id');
    }
}
