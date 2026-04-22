<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model map bảng san_phams (dùng chung với hệ thống nội bộ - chỉ đọc)
 */
class SanPham extends Model
{
    protected $table = 'san_phams';

    protected $fillable = [];

    protected $casts = [
        // anh_san_pham KHÔNG cast 'array' vì DB lưu cả string thô lẫn JSON array
        // Accessor getDanhSachAnhAttribute() và getAnhDauTienAttribute() xử lý thủ công
        'gia_ban_le'   => 'decimal:0',
        'gia_ban_buon' => 'decimal:0',
    ];

    /**
     * Lấy ảnh đầu tiên
     */
    public function getAnhDauTienAttribute(): ?string
    {
        $raw = $this->getRawOriginal('anh_san_pham');
        if (empty($raw) || $raw === 'null' || $raw === '[]') return null;

        // Thử decode JSON
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            foreach ($decoded as $anh) {
                if (!empty($anh) && $anh !== 'null') return $anh;
            }
            return null;
        }

        // Là string thô
        return $raw;
    }

    /**
     * Lấy tất cả ảnh dạng array
     */
    public function getDanhSachAnhAttribute(): array
    {
        $raw = $this->getRawOriginal('anh_san_pham');
        if (empty($raw) || $raw === 'null' || $raw === '[]') return [];

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, fn($a) => !empty($a) && $a !== 'null'));
        }

        return [$raw];
    }

    /**
     * Quan hệ với tồn kho (bảng chung)
     */
    public function khoHangSanPhams()
    {
        return $this->hasMany(KhoHangSanPham::class, 'san_pham_id');
    }

    /**
     * Tổng tồn kho tất cả kho
     */
    public function getTonKhoAttribute(): int
    {
        // Dùng đúng tên cột thật: so_luong_ton (không phải alias ton_kho)
        return $this->khoHangSanPhams->sum('so_luong_ton') ?? 0;
    }

    /**
     * Scope: chỉ lấy các sản phẩm còn hàng
     */
    public function scopeConHang($query)
    {
        return $query->whereHas('khoHangSanPhams', function ($q) {
            $q->where('so_luong_ton', '>', 0);  // so_luong_ton là tên cột thật
        });
    }

    /**
     * Quan hệ với chi tiết đơn hàng
     */
    public function chiTietDonHangs()
    {
        return $this->hasMany(ChiTietDonHang::class, 'san_pham_id');
    }

    /**
     * Quan hệ với thuộc tính sản phẩm
     */
    public function thuocTinhs()
    {
        return $this->hasMany(SanPhamThuocTinh::class, 'san_pham_id');
    }
}
