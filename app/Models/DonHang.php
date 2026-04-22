<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model map bảng don_hangs (dùng chung với hệ thống nội bộ)
 * Khách đặt hàng trên web → ghi vào đây → nội bộ thấy ngay
 */
class DonHang extends Model
{
    protected $table = 'don_hangs';

    protected $fillable = [
        'ma_don_hang',
        'trang_thai',
        'tong_tien',
        'chiet_khau',
        'tien_giam',
        'tien_thanh_toan',
        'da_thanh_toan',
        'ghi_chu',
        'ngay_dat',
        'ngay_giao_du_kien',
        'khach_hang_id',
        'nhan_vien_id',
        'dia_chi_id',
        'cach_thuc_nhan_hang',
        'dia_chi_giao_hang',
    ];

    protected $casts = [
        'ngay_dat'          => 'date',
        'ngay_giao_du_kien' => 'date',
        'da_thanh_toan'     => 'decimal:2',
    ];

    // ========== RELATIONSHIPS ==========

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    public function chiTietDonHangs()
    {
        return $this->hasMany(ChiTietDonHang::class, 'don_hang_id');
    }

    public function lichSuDonHangs()
    {
        return $this->hasMany(LichSuDonHang::class, 'don_hang_id');
    }

    // ========== ACCESSORS ==========

    public function getTrangThaiTextAttribute(): string
    {
        return match($this->trang_thai) {
            'cho_xu_ly'     => 'Chờ xử lý',
            'xuat_kho'      => 'Đang chuẩn bị',
            'dong_goi'      => 'Đang đóng gói',
            'van_chuyen'    => 'Đang giao hàng',
            'tu_van_chuyen' => 'Đang giao hàng',
            'hoan_thanh'    => 'Hoàn thành',
            'huy'           => 'Đã hủy',
            default         => $this->trang_thai,
        };
    }

    public function getTrangThaiMauAttribute(): string
    {
        return match($this->trang_thai) {
            'cho_xu_ly'              => 'yellow',
            'xuat_kho', 'dong_goi'   => 'blue',
            'van_chuyen', 'tu_van_chuyen' => 'purple',
            'hoan_thanh'             => 'green',
            'huy'                    => 'red',
            default                  => 'gray',
        };
    }

    public function getConPhaiTraAttribute(): float
    {
        return max(0, $this->tien_thanh_toan - $this->da_thanh_toan);
    }

    // Tạo mã đơn hàng tự động — giống hệt nội bộ
    public static function taoMaDonHang(): string
    {
        $prefix = 'WEB';
        $date   = now()->format('ymd');

        $lastOrder = self::where('ma_don_hang', 'like', $prefix . $date . '%')
            ->orderBy('ma_don_hang', 'desc')
            ->first();

        $newNumber = $lastOrder
            ? ((int) substr($lastOrder->ma_don_hang, -4)) + 1
            : 1;

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
