<?php

namespace App\Http\Controllers;

use App\Http\Service\GioHangService;
use Illuminate\Http\Request;

/**
 * Controller giỏ hàng — YÊU CẦU ĐĂNG NHẬP
 */
class GioHangController extends Controller
{
    public function __construct(private GioHangService $gioHangService) {}

    /** Hiển thị giỏ hàng */
    public function hienThi()
    {
        $khachHangId = session('khach_hang_id');
        $gioHang     = $this->gioHangService->layGioHang($khachHangId);

        return view('gioHang.index', compact('gioHang'));
    }

    /** API: Thêm sản phẩm vào giỏ */
    public function apiThemVao(Request $request)
    {
        try {
            $request->validate([
                'san_pham_id' => 'required|integer',
                'so_luong'    => 'required|integer|min:1',
            ]);

            $thongTin = $this->gioHangService->themVaoGio(
                session('khach_hang_id'),
                $request->san_pham_id,
                $request->so_luong,
            );

            return response()->json([
                'success'  => true,
                'message'  => 'Đã thêm vào giỏ hàng!',
                'gio_hang' => $thongTin,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /** API: Cập nhật số lượng */
    public function apiCapNhat(Request $request)
    {
        try {
            $thongTin = $this->gioHangService->capNhat(
                session('khach_hang_id'),
                $request->chi_tiet_id,
                $request->so_luong,
            );

            return response()->json(['success' => true, 'gio_hang' => $thongTin]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /** API: Xóa sản phẩm khỏi giỏ */
    public function apiXoa(Request $request)
    {
        try {
            $thongTin = $this->gioHangService->xoa(
                session('khach_hang_id'),
                $request->chi_tiet_id,
            );

            return response()->json(['success' => true, 'gio_hang' => $thongTin]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /** API: Lấy thông tin giỏ hàng */
    public function apiLayGioHang()
    {
        $khachHangId = session('khach_hang_id');
        if (!$khachHangId) {
            return response()->json(['tong_so_luong' => 0, 'tong_tien' => 0]);
        }

        return response()->json($this->gioHangService->layThongTinGioHang($khachHangId));
    }
}
