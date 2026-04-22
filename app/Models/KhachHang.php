<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model map bảng khach_hangs (dùng chung với hệ thống nội bộ)
 * KHÔNG thêm cột mới vào bảng này — mật khẩu lưu ở bảng khach_hang_mat_khau
 */
class KhachHang extends Model
{
    protected $table = 'khach_hangs';

    protected $fillable = [
        'ten',
        'sdt',
        'email',
        'nhom_khach_hang_id',
        'ma_khach_hang',
        'nhan_vien_id',
    ];

    /**
     * Quan hệ với bảng mật khẩu (riêng của web bán lẻ)
     */
    public function matKhau()
    {
        return $this->hasOne(KhachHangMatKhau::class, 'khach_hang_id');
    }

    /**
     * Quan hệ với giỏ hàng
     */
    public function gioHang()
    {
        return $this->hasOne(GioHang::class, 'khach_hang_id');
    }

    /**
     * Quan hệ với đơn hàng (bảng chung)
     */
    public function donHangs()
    {
        return $this->hasMany(DonHang::class, 'khach_hang_id');
    }

    /**
     * Quan hệ với địa chỉ (bảng chung)
     */
    public function diaChis()
    {
        return $this->hasMany(DiaChi::class, 'khach_hang_id');
    }

    /**
     * Quan hệ với nhóm khách hàng (bảng chung)
     */
    public function nhomKhachHang()
    {
        return $this->belongsTo(NhomKhachHang::class, 'nhom_khach_hang_id');
    }
}
