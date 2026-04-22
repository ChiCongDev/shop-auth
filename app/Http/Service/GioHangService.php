<?php

namespace App\Http\Service;

use App\Models\GioHang;
use App\Models\GioHangChiTiet;
use App\Models\SanPham;
use App\Models\KhoHangSanPham;

/**
 * Service xử lý giỏ hàng
 */
class GioHangService
{
    /**
     * Lấy hoặc tạo giỏ hàng cho khách hàng
     */
    public function layHoacTaoGioHang(int $khachHangId): GioHang
    {
        return GioHang::firstOrCreate(['khach_hang_id' => $khachHangId]);
    }

    /**
     * Lấy giỏ hàng kèm chi tiết sản phẩm
     */
    public function layGioHang(int $khachHangId): ?GioHang
    {
        return GioHang::with(['chiTiets.sanPham.khoHangSanPhams'])
            ->where('khach_hang_id', $khachHangId)
            ->first();
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function themVaoGio(int $khachHangId, int $sanPhamId, int $soLuong = 1): array
    {
        // Kiểm tra sản phẩm tồn tại
        $sanPham = SanPham::find($sanPhamId);
        if (!$sanPham) {
            throw new \Exception('Sản phẩm không tồn tại.');
        }

        // Kiểm tra tồn kho (dùng đúng tên cột thật: so_luong_ton)
        $tonKho = KhoHangSanPham::where('san_pham_id', $sanPhamId)->sum('so_luong_ton');
        if ($tonKho <= 0) {
            throw new \Exception('Sản phẩm đã hết hàng.');
        }

        $gioHang = $this->layHoacTaoGioHang($khachHangId);

        // Nếu sản phẩm đã có trong giỏ → cộng thêm số lượng
        $chiTiet = GioHangChiTiet::where('gio_hang_id', $gioHang->id)
            ->where('san_pham_id', $sanPhamId)
            ->first();

        if ($chiTiet) {
            $soLuongMoi = $chiTiet->so_luong + $soLuong;
            if ($soLuongMoi > $tonKho) {
                throw new \Exception("Chỉ còn {$tonKho} sản phẩm trong kho.");
            }
            $chiTiet->update(['so_luong' => $soLuongMoi]);
        } else {
            if ($soLuong > $tonKho) {
                throw new \Exception("Chỉ còn {$tonKho} sản phẩm trong kho.");
            }
            GioHangChiTiet::create([
                'gio_hang_id' => $gioHang->id,
                'san_pham_id' => $sanPhamId,
                'so_luong'    => $soLuong,
                'gia'         => $sanPham->gia_ban_le,
            ]);
        }

        return $this->layThongTinGioHang($khachHangId);
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ
     */
    public function capNhat(int $khachHangId, int $chiTietId, int $soLuong): array
    {
        $gioHang = GioHang::where('khach_hang_id', $khachHangId)->firstOrFail();
        $chiTiet = GioHangChiTiet::where('id', $chiTietId)
            ->where('gio_hang_id', $gioHang->id)
            ->firstOrFail();

        if ($soLuong <= 0) {
            $chiTiet->delete();
        } else {
            $tonKho = KhoHangSanPham::where('san_pham_id', $chiTiet->san_pham_id)->sum('so_luong_ton');
            if ($soLuong > $tonKho) {
                throw new \Exception("Chỉ còn {$tonKho} sản phẩm trong kho.");
            }
            $chiTiet->update(['so_luong' => $soLuong]);
        }

        return $this->layThongTinGioHang($khachHangId);
    }

    /**
     * Xóa sản phẩm khỏi giỏ
     */
    public function xoa(int $khachHangId, int $chiTietId): array
    {
        $gioHang = GioHang::where('khach_hang_id', $khachHangId)->firstOrFail();
        GioHangChiTiet::where('id', $chiTietId)
            ->where('gio_hang_id', $gioHang->id)
            ->delete();

        return $this->layThongTinGioHang($khachHangId);
    }

    /**
     * Xóa toàn bộ giỏ hàng sau khi đặt hàng
     */
    public function xoaGioHang(int $khachHangId): void
    {
        $gioHang = GioHang::where('khach_hang_id', $khachHangId)->first();
        if ($gioHang) {
            $gioHang->chiTiets()->delete();
        }
    }

    /**
     * Lấy thông tin tổng giỏ hàng (trả về cho API)
     */
    public function layThongTinGioHang(int $khachHangId): array
    {
        $gioHang = $this->layGioHang($khachHangId);
        if (!$gioHang) {
            return ['tong_so_luong' => 0, 'tong_tien' => 0, 'so_san_pham' => 0];
        }

        return [
            'tong_so_luong' => $gioHang->chiTiets->sum('so_luong'),
            'tong_tien'     => $gioHang->chiTiets->sum(fn($item) => $item->so_luong * $item->gia),
            'so_san_pham'   => $gioHang->chiTiets->count(),
        ];
    }
}
