<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhanVien extends Model
{
    protected $table = 'nhan_viens';

    protected $fillable = ['ten', 'email', 'mat_khau', 'quyen', 'da_khoa_dang_nhap', 'vtp_account_id'];

    protected $hidden = ['mat_khau'];

    protected $casts = [
        'da_khoa_dang_nhap' => 'boolean',
    ];

    public function taiKhoanBiKhoa(): bool
    {
        return (bool) ($this->da_khoa_dang_nhap ?? false);
    }

    public function khachHangDuocGan()
    {
        return $this->belongsToMany(KhachHang::class, 'khach_hang_nhan_vien')
            ->withPivot('created_at');
    }

    public function donOrderHangs()
    {
        return $this->hasMany(DonOrderHang::class, 'nhan_vien_id');
    }
}
