<?php

namespace App\Http\Service;

use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\GioHang;
use App\Models\DiaChi;
use Illuminate\Support\Facades\DB;

/**
 * Service xử lý đặt hàng và thanh toán
 * Đơn hàng ghi vào bảng chung → nội bộ thấy ngay
 */
class ThanhToanService
{
    public function __construct(private GioHangService $gioHangService) {}

    /**
     * Xử lý đặt hàng — đây là hàm quan trọng nhất
     * Tạo DonHang trong bảng chung → hệ thống nội bộ thấy ngay
     */
    public function xuLyDatHang(int $khachHangId, array $data): DonHang
    {
        $gioHang = GioHang::with(['chiTiets.sanPham'])->where('khach_hang_id', $khachHangId)->first();

        if (!$gioHang || $gioHang->chiTiets->isEmpty()) {
            throw new \Exception('Giỏ hàng trống.');
        }

        DB::beginTransaction();
        try {
            // Tính tổng tiền
            $tongTien = $gioHang->chiTiets->sum(fn($item) => $item->so_luong * $item->gia);

            // Xử lý ghi chú thanh toán
            $ghiChu = match ($data['hinh_thuc_thanh_toan']) {
                'cod'           => 'Thanh toán khi nhận hàng (COD)',
                'chuyen_khoan' => 'Chuyển khoản ngân hàng - chờ xác nhận',
                'cong_no'      => 'Ghi công nợ - thanh toán sau',
                default        => $data['ghi_chu'] ?? '',
            };

            if (!empty($data['ghi_chu_them'])) {
                $ghiChu .= ' | Ghi chú: ' . $data['ghi_chu_them'];
            }

            // Tạo đơn hàng trong bảng chung
            $donHang = DonHang::create([
                'ma_don_hang'          => DonHang::taoMaDonHang(),
                'khach_hang_id'        => $khachHangId,
                'nhan_vien_id'         => null,  // Đơn web — không có nhân viên xử lý
                'trang_thai'           => 'cho_xu_ly',
                'tong_tien'            => $tongTien,
                'chiet_khau'           => 0,
                'tien_giam'            => 0,
                'tien_thanh_toan'      => $tongTien,
                'da_thanh_toan'        => 0,
                'ghi_chu'              => $ghiChu,
                'ngay_dat'             => now(),
                'cach_thuc_nhan_hang'  => 'van_chuyen',  // ENUM: nhan_tai_quay | van_chuyen | tu_van_chuyen
                'dia_chi_giao_hang'    => json_encode([
                    'ten'        => $data['ten_nguoi_nhan'],
                    'sdt'        => $data['sdt_nguoi_nhan'],
                    'dia_chi'    => $data['dia_chi'],
                    'phuong_xa'  => $data['phuong_xa'] ?? '',
                    'quan_huyen' => $data['quan_huyen'] ?? '',
                    'tinh_thanh' => $data['tinh_thanh'] ?? '',
                ]),
            ]);

            // Tạo chi tiết đơn hàng
            foreach ($gioHang->chiTiets as $item) {
                ChiTietDonHang::create([
                    'don_hang_id' => $donHang->id,
                    'san_pham_id' => $item->san_pham_id,
                    'so_luong'    => $item->so_luong,
                    'gia_ban'     => $item->gia,
                    'thanh_tien'  => $item->so_luong * $item->gia,
                ]);
            }

            // Xóa giỏ hàng sau khi đặt thành công
            $this->gioHangService->xoaGioHang($khachHangId);

            DB::commit();
            return $donHang;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Lấy danh sách đơn hàng của khách
     */
    public function layDonHangCuaKhach(int $khachHangId, int $perPage = 10)
    {
        return DonHang::with('chiTietDonHangs.sanPham')
            ->where('khach_hang_id', $khachHangId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Lấy chi tiết 1 đơn hàng (chỉ của khách đang đăng nhập)
     */
    public function layChiTietDonHang(int $donHangId, int $khachHangId): DonHang
    {
        return DonHang::with(['chiTietDonHangs.sanPham', 'lichSuDonHangs'])
            ->where('id', $donHangId)
            ->where('khach_hang_id', $khachHangId)
            ->firstOrFail();
    }
}
