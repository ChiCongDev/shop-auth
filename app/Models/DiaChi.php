<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaChi extends Model
{
    protected $table = 'dia_chis';

    protected $fillable = [
        'khach_hang_id',
        'dia_chi',
        'phuong_xa',
        'quan_huyen',
        'tinh_thanh',
        'la_mac_dinh',
    ];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    /**
     * Địa chỉ đầy đủ
     */
    public function getDiaChiDayDuAttribute(): string
    {
        return implode(', ', array_filter([
            $this->dia_chi,
            $this->phuong_xa,
            $this->quan_huyen,
            $this->tinh_thanh,
        ]));
    }
}
