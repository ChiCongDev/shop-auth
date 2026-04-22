<?php

namespace App\Http\Controllers;

use App\Http\Service\SanPhamService;
use Illuminate\Http\Request;

/**
 * Controller sản phẩm — PUBLIC (không cần đăng nhập)
 * Đọc từ DB chung với hệ thống nội bộ → tự động đồng bộ realtime
 */
class SanPhamController extends Controller
{
    public function __construct(private SanPhamService $sanPhamService) {}

    // =========================================================
    // TRANG CHỦ
    // =========================================================

    /** Trang chủ — PUBLIC */
    public function trangChu()
    {
        $sanPhamNoiBat = $this->sanPhamService->laySanPhamNoiBat(8);
        $sanPhamMoi    = $this->sanPhamService->laySanPhamMoiNhat(8);

        return view('trangChu', compact('sanPhamNoiBat', 'sanPhamMoi'));
    }

    /** Trang Hàng Mới Về — PUBLIC */
    public function hangMoiVe(Request $request)
    {
        $nhanHieu  = $request->input('nhan_hieu', '');
        $sapXep    = $request->input('sap_xep', 'moi_nhat');

        $danhSach       = $this->sanPhamService->layHangMoiVe(16, $nhanHieu, $sapXep);
        $danhSachNhanHieu = $this->sanPhamService->layDanhSachNhanHieu(nhapKhoOnly: true);

        return view('sanPham.hangMoiVe', compact('danhSach', 'danhSachNhanHieu', 'nhanHieu', 'sapXep'));
    }

    // =========================================================
    // DANH SÁCH SẢN PHẨM
    // =========================================================

    /** Danh sách sản phẩm — PUBLIC */
    public function hienThiDanhSach(Request $request)
    {
        $loai   = (string) $request->get('loai', '');
        $search = (string) $request->get('search', '');

        $danhSach  = $this->sanPhamService->layDanhSach(
            perPage:  12,
            search:   $search,
            loai:     $loai,
            nhanHieu: (string) $request->get('nhan_hieu', ''),
            sapXep:   (string) $request->get('sap_xep', 'moi_nhat'),
        );

        $danhSachLoai     = $this->sanPhamService->layDanhSachLoai();
        // Thương hiệu chỉ hiện brand có SP trong danh mục/search hiện tại
        $danhSachNhanHieu = $this->sanPhamService->layDanhSachNhanHieu(
            loai:   $loai,
            search: $search,
        );

        return view('sanPham.danhSach', compact('danhSach', 'danhSachLoai', 'danhSachNhanHieu'));
    }

    // =========================================================
    // CHI TIẾT SẢN PHẨM
    // =========================================================

    /** Chi tiết sản phẩm — PUBLIC */
    public function hienThiChiTiet(string $maChung)
    {
        $chiTiet = $this->sanPhamService->layChiTietSanPham($maChung);

        if (!$chiTiet) {
            abort(404, 'Không tìm thấy sản phẩm.');
        }

        // Lấy sản phẩm liên quan (cùng loại)
        $sanPhamLienQuan = $this->sanPhamService->laySanPhamLienQuan(
            maChung: $maChung,
            loai:    $chiTiet['loai'] ?? '',
            soLuong: 4
        );

        return view('sanPham.chiTiet', compact('chiTiet', 'sanPhamLienQuan'));
    }

    // =========================================================
    // API (AJAX)
    // =========================================================

    /** API tìm kiếm nhanh (thanh search header) — PUBLIC */
    public function apiTimKiem(Request $request)
    {
        $keyword  = $request->get('search', '');
        $ketQua   = $this->sanPhamService->timKiemNhanh($keyword, 8);

        return response()->json([
            'success' => true,
            'data'    => $ketQua,
            'count'   => count($ketQua),
        ]);
    }

    /** API danh sách sản phẩm (AJAX phân trang) — PUBLIC */
    public function apiDanhSach(Request $request)
    {
        $danhSach = $this->sanPhamService->layDanhSach(
            perPage:  $request->get('per_page', 12),
            search:   $request->get('search', ''),
            loai:     $request->get('loai', ''),
            nhanHieu: $request->get('nhan_hieu', ''),
            sapXep:   $request->get('sap_xep', 'moi_nhat'),
        );

        return response()->json([
            'success'    => true,
            'data'       => $danhSach->items(),
            'pagination' => [
                'current_page' => $danhSach->currentPage(),
                'last_page'    => $danhSach->lastPage(),
                'total'        => $danhSach->total(),
            ],
        ]);
    }
}
