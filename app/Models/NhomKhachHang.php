<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhomKhachHang extends Model
{
    protected $table = 'nhom_khach_hangs';

    protected $fillable = ['ten', 'mo_ta', 'chiet_khau', 'chinh_sach_gia_id'];
}
