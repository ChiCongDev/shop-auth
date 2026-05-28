<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SanPhamGia extends Model
{
    protected $table = 'san_pham_gias';

    protected $fillable = ['san_pham_id', 'chinh_sach_gia_id', 'gia'];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_id');
    }

    public function chinhSachGia()
    {
        return $this->belongsTo(ChinhSachGia::class, 'chinh_sach_gia_id');
    }
}
