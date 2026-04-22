<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaChi extends Model
{
    protected $table = 'dia_chis';

    /**
     * Tên cột khớp với bảng thật (do sell tạo):
     * khu_vuc, phuong_xa, dia_chi_cu_the, khach_hang_id
     */
    protected $fillable = [
        'khach_hang_id',
        'dia_chi_cu_the',
        'phuong_xa',
        'khu_vuc',
    ];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    /**
     * Địa chỉ đầy đủ — giống sell
     */
    public function getDiaChiDayDuAttribute(): string
    {
        return implode(', ', array_filter([
            $this->dia_chi_cu_the,
            $this->phuong_xa,
            $this->khu_vuc,
        ]));
    }
}

