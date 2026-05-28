<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSuDonOrderHang extends Model
{
    protected $table = 'lich_su_don_order_hangs';

    protected $fillable = [
        'don_order_hang_id',
        'hanh_dong',
        'trang_thai_cu',
        'trang_thai_moi',
        'mo_ta',
        'nguoi_thuc_hien',
        'nhan_vien_id',
        'du_lieu_them',
    ];

    protected $casts = [
        'du_lieu_them' => 'array',
    ];

    public function donOrderHang()
    {
        return $this->belongsTo(DonOrderHang::class, 'don_order_hang_id');
    }

    public static function ghi($donOrderHangId, $hanhDong, $trangThaiCu = null, $trangThaiMoi = null, $moTa = null, $duLieuThem = null)
    {
        return self::create([
            'don_order_hang_id' => $donOrderHangId,
            'hanh_dong' => $hanhDong,
            'trang_thai_cu' => $trangThaiCu,
            'trang_thai_moi' => $trangThaiMoi,
            'mo_ta' => $moTa,
            'nguoi_thuc_hien' => session('doi_tac_ten') ?? session('tenDangNhap') ?? 'He thong',
            'nhan_vien_id' => session('doi_tac_id'),
            'du_lieu_them' => $duLieuThem,
        ]);
    }
}
