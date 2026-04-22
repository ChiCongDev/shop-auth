<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSuDonHang extends Model
{
    protected $table = 'lich_su_don_hangs';

    protected $fillable = [
        'don_hang_id',
        'hanh_dong',
        'trang_thai_cu',
        'trang_thai_moi',
        'mo_ta',
        'nguoi_thuc_hien',
        'nhan_vien_id',
        'du_lieu_them',
    ];

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'don_hang_id');
    }
}
