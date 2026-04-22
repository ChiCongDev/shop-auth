<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSuDonHang extends Model
{
    protected $table = 'lich_su_don_hangs';

    protected $fillable = [
        'don_hang_id',
        'trang_thai',
        'ghi_chu',
        'nhan_vien_id',
    ];

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'don_hang_id');
    }
}
