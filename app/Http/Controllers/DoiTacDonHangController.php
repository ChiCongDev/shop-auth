<?php

namespace App\Http\Controllers;

use App\Http\Service\DoiTacDonHangNoiBoService;
use Illuminate\Http\Request;

class DoiTacDonHangController extends Controller
{
    public function __construct(private DoiTacDonHangNoiBoService $donHangNoiBoService) {}

    public function hienThiDanhSach()
    {
        if (!$this->coQuyenXemDonHangThuong()) {
            return redirect('/doi-tac/order-hang/danh-sach')
                ->with('loi', 'Tai khoan nay khong co quyen xem don hang thuong.');
        }

        return view('doiTac.donHang.danhSach');
    }

    public function hienThiTaoDonHang()
    {
        if (!$this->coQuyenTaoDonHangThuong()) {
            return redirect('/doi-tac/order-hang/danh-sach')
                ->with('loi', 'Tai khoan nay khong co quyen tao don hang thuong.');
        }

        return view('doiTac.donHang.taoDonHang');
    }

    public function hienThiChiTiet(int $id)
    {
        if (!$this->coQuyenXemDonHangThuong()) {
            return redirect('/doi-tac/order-hang/danh-sach')
                ->with('loi', 'Tai khoan nay khong co quyen xem don hang thuong.');
        }

        $ketQua = $this->donHangNoiBoService->layChiTiet($id, (int) session('doi_tac_id'));
        if (!$ketQua['success']) {
            return redirect('/doi-tac/don-hang')
                ->with('loi', $ketQua['message'] ?? 'Khong tim thay don hang.');
        }

        return view('doiTac.donHang.chiTiet', [
            'donHang' => $ketQua['data'] ?? [],
        ]);
    }

    public function hienThiDoiTraHang(int $id)
    {
        if (!$this->coQuyenQuanLyTraHangThuong()) {
            return redirect('/doi-tac/don-hang')
                ->with('loi', 'Tai khoan nay khong co quyen doi tra don hang thuong.');
        }

        $ketQua = $this->donHangNoiBoService->layChiTiet($id, (int) session('doi_tac_id'));
        if (!$ketQua['success']) {
            return redirect('/doi-tac/don-hang')
                ->with('loi', $ketQua['message'] ?? 'Khong tim thay don hang.');
        }

        return view('doiTac.donHang.doiTraHang', [
            'donHang' => $ketQua['data'] ?? [],
        ]);
    }

    public function hienThiDanhSachPhieuTraHang()
    {
        if (!$this->coQuyenQuanLyTraHangThuong()) {
            return redirect('/doi-tac/don-hang')
                ->with('loi', 'Tai khoan nay khong co quyen xem phieu tra don hang thuong.');
        }

        return view('doiTac.donHang.danhSachPhieuTraHang');
    }

    public function apiLayDanhSach(Request $request)
    {
        if (!$this->coQuyenXemDonHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen xem don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->layDanhSach((int) session('doi_tac_id'), [
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', 15),
            'search' => trim($request->input('search', '')),
            'ngay_tao' => $request->input('ngay_tao', ''),
            'tu_ngay' => $request->input('tu_ngay'),
            'den_ngay' => $request->input('den_ngay'),
            'trang_thai' => $request->input('trang_thai', ''),
            'khach_hang' => trim($request->input('khach_hang', '')),
            'san_pham' => trim($request->input('san_pham', '')),
        ]);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? [],
            'pagination' => $this->chuanHoaPhanTrang($ketQua['pagination'] ?? null),
            'stats' => $ketQua['stats'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiLayChiTiet(int $id)
    {
        if (!$this->coQuyenXemDonHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen xem don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->layChiTiet($id, (int) session('doi_tac_id'));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiKhachHangMacDinh()
    {
        if (!$this->coQuyenTaoDonHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen tao don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->layKhachHangMacDinh((int) session('doi_tac_id'));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
            'auto_select' => (bool) ($ketQua['raw']['tu_chon'] ?? false),
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiTimKiemKhachHang(Request $request)
    {
        if (!$this->coQuyenTaoDonHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen tao don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->timKiemKhachHang((int) session('doi_tac_id'), [
            'tu_khoa' => trim($request->input('tu_khoa', '')),
            'so_luong' => $request->input('so_luong', 20),
            'hien_thi_gan_day' => $request->boolean('hien_thi_gan_day'),
        ]);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? [],
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiLayNhanVienDuocGan(int $khachHangId)
    {
        if (!$this->coQuyenTaoDonHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen tao don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->layNhanVienDuocGan((int) session('doi_tac_id'), $khachHangId);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? [],
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiTimKiemSanPham(Request $request)
    {
        if (!$this->coQuyenTaoDonHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen tao don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->timKiemSanPham((int) session('doi_tac_id'), [
            'tu_khoa' => trim($request->input('tu_khoa', '')),
            'so_luong' => $request->input('so_luong', 20),
            'chinh_sach_gia_id' => $request->input('chinh_sach_gia_id'),
        ]);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? [],
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiLayGiaNhap(Request $request)
    {
        if (!$this->coQuyenTaoDonHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen tao don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->layGiaNhap((int) session('doi_tac_id'), (string) $request->input('ids', ''));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? [],
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiLayGiaTheoChinhSach(Request $request)
    {
        if (!$this->coQuyenTaoDonHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen tao don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->layGiaTheoChinhSach((int) session('doi_tac_id'), [
            'san_pham_ids' => $request->input('san_pham_ids', []),
            'chinh_sach_gia_id' => $request->input('chinh_sach_gia_id'),
        ]);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? [],
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiTaoDonHang(Request $request)
    {
        if (!$this->coQuyenTaoDonHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen tao don hang thuong.',
            ], 403);
        }

        $request->validate([
            'khach_hang_id' => 'required|integer',
            'dia_chi_id' => 'nullable|integer',
            'nhan_vien_ban_hang_id' => 'nullable|integer',
            'san_phams' => 'required|array|min:1',
            'san_phams.*.san_pham_id' => 'required|integer',
            'san_phams.*.so_luong' => 'required|integer|min:1',
            'san_phams.*.don_gia' => 'required|numeric|min:0',
            'san_phams.*.chiet_khau' => 'nullable|numeric|min:0',
            'chiet_khau' => 'nullable|numeric|min:0',
            'ghi_chu' => 'nullable|string',
            'hen_giao' => 'nullable|date',
        ]);

        $ketQua = $this->donHangNoiBoService->taoDonHang((int) session('doi_tac_id'), $request->only([
            'khach_hang_id',
            'dia_chi_id',
            'nhan_vien_ban_hang_id',
            'san_phams',
            'chiet_khau',
            'ghi_chu',
            'hen_giao',
        ]));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiLayDanhSachPhieuTraHang(Request $request)
    {
        if (!$this->coQuyenQuanLyTraHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen xem phieu tra don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->layDanhSachPhieuTra((int) session('doi_tac_id'), [
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', 15),
            'search' => trim($request->input('search', '')),
            'trang_thai' => $request->input('trang_thai'),
            'tu_ngay' => $request->input('tu_ngay'),
            'den_ngay' => $request->input('den_ngay'),
        ]);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? [],
            'pagination' => $this->chuanHoaPhanTrang($ketQua['pagination'] ?? null),
            'stats' => $ketQua['stats'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiLayChiTietPhieuTraHang(int $id)
    {
        if (!$this->coQuyenQuanLyTraHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen xem phieu tra don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->layChiTietPhieuTra($id, (int) session('doi_tac_id'));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiThaoTac(int $id, string $hanhDong)
    {
        if (!$this->coQuyenThaoTacDonHangThuong($hanhDong)) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen thuc hien thao tac nay.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->guiThaoTac($id, (int) session('doi_tac_id'), $hanhDong);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiLaySoLuongDaTra(int $donHangId)
    {
        if (!$this->coQuyenQuanLyTraHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen xem so luong tra don hang thuong.',
            ], 403);
        }

        $ketQua = $this->donHangNoiBoService->laySoLuongDaTra($donHangId, (int) session('doi_tac_id'));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? [],
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiTaoPhieuTraHang(Request $request)
    {
        if (!$this->coQuyenQuanLyTraHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen tao phieu tra don hang thuong.',
            ], 403);
        }

        $request->validate([
            'don_hang_id' => 'required|integer',
            'san_phams' => 'required|array|min:1',
            'san_phams.*.san_pham_id' => 'required|integer',
            'san_phams.*.so_luong' => 'required|integer|min:1',
            'san_phams.*.gia_tra' => 'required|numeric|min:0',
        ]);

        $ketQua = $this->donHangNoiBoService->taoPhieuTra((int) session('doi_tac_id'), $request->only([
            'don_hang_id',
            'san_phams',
            'ly_do_tra',
            'ghi_chu',
            'chi_nhanh',
            'tham_chieu',
        ]));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiHoanTienPhieuTraHang(Request $request, int $id)
    {
        if (!$this->coQuyenHoanTienTraHangThuong()) {
            return response()->json([
                'success' => false,
                'message' => 'Tai khoan nay khong co quyen hoan tien phieu tra don hang thuong.',
            ], 403);
        }

        $request->validate([
            'so_tien' => 'nullable|numeric|min:1',
        ]);

        $ketQua = $this->donHangNoiBoService->hoanTienPhieuTra($id, (int) session('doi_tac_id'), [
            'so_tien' => $request->input('so_tien'),
        ]);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    private function coQuyenXemDonHangThuong(): bool
    {
        return session('doi_tac_id')
            && in_array(session('doi_tac_quyen'), ['admin', 'thu_kho', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'], true);
    }

    private function coQuyenTaoDonHangThuong(): bool
    {
        return $this->coQuyenXemDonHangThuong();
    }

    private function coQuyenQuanLyTraHangThuong(): bool
    {
        return session('doi_tac_id')
            && in_array(session('doi_tac_quyen'), ['admin', 'thu_kho'], true);
    }

    private function coQuyenHoanTienTraHangThuong(): bool
    {
        return session('doi_tac_id')
            && session('doi_tac_quyen') === 'admin';
    }

    private function coQuyenThaoTacDonHangThuong(string $hanhDong): bool
    {
        $quyen = session('doi_tac_quyen');
        $quyenTheoHanhDong = [
            'xuat-kho' => ['admin', 'thu_kho'],
            'dong-goi' => ['admin', 'thu_kho', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
            'van-chuyen' => ['admin', 'thu_kho', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
            'tu-van-chuyen-ntq' => ['admin', 'thu_kho', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
            'hoan-thanh' => ['admin', 'thu_kho', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
        ];

        return in_array($quyen, $quyenTheoHanhDong[$hanhDong] ?? [], true);
    }

    private function chuanHoaPhanTrang(?array $phanTrang): ?array
    {
        if (!$phanTrang) {
            return null;
        }

        $currentPage = (int) ($phanTrang['current_page'] ?? $phanTrang['trang_hien_tai'] ?? 1);
        $lastPage = (int) ($phanTrang['last_page'] ?? $phanTrang['tong_trang'] ?? 1);
        $total = (int) ($phanTrang['total'] ?? $phanTrang['tong_so'] ?? 0);
        $perPage = (int) ($phanTrang['per_page'] ?? 15);

        return [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $phanTrang['from'] ?? $phanTrang['first_item'] ?? ($total ? (($currentPage - 1) * $perPage) + 1 : null),
            'to' => $phanTrang['to'] ?? $phanTrang['last_item'] ?? ($total ? min($currentPage * $perPage, $total) : null),
        ];
    }
}
