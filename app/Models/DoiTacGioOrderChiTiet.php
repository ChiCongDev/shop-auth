<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoiTacGioOrderChiTiet extends Model
{
    protected $table = 'doi_tac_gio_order_chi_tiets';

    protected $fillable = [
        'doi_tac_gio_order_hang_id',
        'san_pham_id',
        'so_luong',
        'gia_order_tam_tinh',
    ];

    public function gioOrder()
    {
        return $this->belongsTo(DoiTacGioOrderHang::class, 'doi_tac_gio_order_hang_id');
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }
}
