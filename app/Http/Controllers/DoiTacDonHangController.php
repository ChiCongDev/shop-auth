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

    private function coQuyenXemDonHangThuong(): bool
    {
        return session('doi_tac_id')
            && in_array(session('doi_tac_quyen'), ['admin', 'thu_kho', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'], true);
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
