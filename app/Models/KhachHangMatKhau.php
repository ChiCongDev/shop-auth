<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bảng mật khẩu riêng của web bán lẻ
 * Tách riêng để không ảnh hưởng hệ thống nội bộ
 */
class KhachHangMatKhau extends Model
{
    protected $table = 'khach_hang_mat_khau';

    protected $fillable = [
        'khach_hang_id',
        'mat_khau',
        'remember_token',
    ];

    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }
}
