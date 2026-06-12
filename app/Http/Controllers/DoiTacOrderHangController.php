<?php

namespace App\Http\Controllers;

use App\Http\Service\DoiTacDonBanNoiBoService;
use App\Http\Service\DoiTacGioOrderHangService;
use App\Http\Service\DoiTacOrderHangService;
use App\Http\Service\DoiTacPhieuTraHangNoiBoService;
use App\Models\ChiTietDonHang;
use App\Models\KhachHang;
use App\Models\NhomKhachHang;
use App\Models\NhanVien;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

////// doitac
class DoiTacOrderHangController extends Controller
{
    public function __construct(
        private DoiTacOrderHangService $orderHangService,
        private DoiTacDonBanNoiBoService $donBanNoiBoService,
        private DoiTacGioOrderHangService $gioOrderHangService,
        private DoiTacPhieuTraHangNoiBoService $phieuTraHangNoiBoService
    ) {}

    public function hienThiTaoDonOrder()
    {
        return view('doiTac.orderHang.taoDonOrder');
    }

    public function hienThiDanhSach()
    {
        return view('doiTac.orderHang.danhSachDonOrder');
    }

    public function hienThiHangOrderVe()
    {
        return view('doiTac.orderHang.hangOrderVe');
    }

    public function hienThiSanPhamDuocPhep()
    {
        return view('doiTac.orderHang.sanPhamDuocPhepOrder');
    }

    public function hienThiChiTietSanPhamDuocPhep(string $maChung)
    {
        return view('doiTac.orderHang.chiTietSanPhamDuocPhepOrder', ['maChung' => $maChung]);
    }

    public function hienThiGioOrder()
    {
        return view('doiTac.orderHang.gioOrderHang');
    }

    public function hienThiDanhSachKhachHangOrder()
    {
        return view('doiTac.orderHang.danhSachKhachHangOrder');
    }

    public function hienThiChiTiet(int $id)
    {
        return view('doiTac.orderHang.chiTietDonOrder', ['orderId' => $id]);
    }

    public function hienThiChiTietDonBan(int $id)
    {
        $duLieu = $this->orderHangService->layChiTietDonBanTuOrder($id, (int) session('doi_tac_id'), session('doi_tac_quyen'));
        if (!$duLieu) {
            return redirect('/doi-tac/order-hang/danh-sach')
                ->with('loi', 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn này.');
        }

        return view('doiTac.orderHang.chiTietDonBanOrder', $duLieu);
    }

    public function hienThiDoiTraHang(int $id)
    {
        $duLieu = $this->orderHangService->layChiTietDonBanTuOrder($id, (int) session('doi_tac_id'), session('doi_tac_quyen'));
        if (!$duLieu || !$this->coQuyenQuanLyPhieuTraHang()) {
            return redirect('/doi-tac/order-hang/danh-sach')
                ->with('loi', 'Không tìm thấy đơn hàng hoặc bạn không có quyền đổi/trả hàng order.');
        }

        return view('doiTac.orderHang.doiTraHangOrder', $duLieu);
    }

    public function hienThiDanhSachPhieuTraHang()
    {
        if (!$this->coQuyenQuanLyPhieuTraHang()) {
            return redirect('/doi-tac/order-hang/danh-sach')
                ->with('loi', 'Bạn không có quyền xem danh sách phiếu trả hàng order.');
        }

        return view('doiTac.orderHang.danhSachPhieuTraHangOrder');
    }

    public function apiTimKiemKhachHang(Request $request)
    {
        try {
            $tuKhoa = trim($request->input('tu_khoa', ''));
            if ($tuKhoa === '') {
                return response()->json(['success' => true, 'data' => []]);
            }

            $nhanVienId = (int) session('doi_tac_id');
            $query = KhachHang::select('id', 'ten', 'sdt', 'ma_khach_hang', 'email', 'nhom_khach_hang_id')
                ->with([
                    'nhomKhachHang:id,ten,chinh_sach_gia_id',
                    'nhomKhachHang.chinhSachGia:id,loai_gia,code',
                    'nhanViens:id,ten,quyen',
                ])
                ->where(function ($q) use ($tuKhoa) {
                    $q->where('ten', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('sdt', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('ma_khach_hang', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('email', 'like', '%' . $tuKhoa . '%');
                });

            if (in_array(session('doi_tac_quyen'), ['nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2', 'thu_kho'], true)) {
                $query->whereHas('nhanViens', function ($q) use ($nhanVienId) {
                    $q->where('nhan_viens.id', $nhanVienId);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $query->orderBy('ten')->limit(20)->get(),
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tìm khách order đối tác: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiKhachHangMacDinh()
    {
        try {
            if (!in_array(session('doi_tac_quyen'), ['nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'], true)) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'auto_select' => false,
                ]);
            }

            $nhanVienId = (int) session('doi_tac_id');
            $khachHangs = KhachHang::select('id', 'ten', 'sdt', 'ma_khach_hang', 'email', 'nhom_khach_hang_id')
                ->with([
                    'nhomKhachHang:id,ten,chinh_sach_gia_id',
                    'nhomKhachHang.chinhSachGia:id,loai_gia,code',
                    'nhanViens:id,ten,quyen',
                ])
                ->whereHas('nhanViens', function ($q) use ($nhanVienId) {
                    $q->where('nhan_viens.id', $nhanVienId);
                })
                ->orderBy('ten')
                ->limit(2)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $khachHangs->count() === 1 ? $khachHangs->first() : null,
                'auto_select' => $khachHangs->count() === 1,
            ]);
        } catch (\Exception $e) {
            Log::error('Loi lay khach order mac dinh doi tac: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiTimKiemSanPhamOrder(Request $request)
    {
        try {
            $tuKhoa = trim($request->input('tu_khoa', ''));
            $perPage = min((int) $request->input('per_page', 20), 100);

            $query = SanPham::select(
                'id',
                'ten',
                'ten_chung',
                'ma_chung',
                'ma_sku',
                'ma_vach',
                'nhan_hieu',
                'anh_san_pham',
                'gia_ban_le',
                'gia_ban_buon',
                'gia_cong_tac_vien',
                'gia_order',
                'gia_nhap',
                'don_vi_tinh',
                'duoc_phep_order',
                'order_listed_at'
            )
                ->with(['sanPhamGias:id,san_pham_id,chinh_sach_gia_id,gia'])
                ->where('duoc_phep_order', true);

            if ($tuKhoa !== '') {
                $query->where(function ($q) use ($tuKhoa) {
                    $q->where('ten', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('ten_chung', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('ma_sku', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('ma_vach', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('ma_chung', 'like', '%' . $tuKhoa . '%');
                });
            }

            $data = $query->orderByDesc('order_listed_at')
                ->orderByDesc('created_at')
                ->paginate($perPage);

            $items = $this->ganTonKhoVaGia($data->items());

            return response()->json([
                'success' => true,
                'data' => $items,
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tìm sản phẩm order đối tác: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiLaySanPhamOrder(int $id)
    {
        $sanPham = SanPham::select(
            'id',
            'ten',
            'ten_chung',
            'ma_chung',
            'ma_sku',
            'ma_vach',
            'nhan_hieu',
            'anh_san_pham',
            'gia_ban_le',
            'gia_ban_buon',
            'gia_cong_tac_vien',
            'gia_order',
            'gia_nhap',
            'don_vi_tinh',
            'duoc_phep_order',
            'order_listed_at'
        )
            ->with(['sanPhamGias:id,san_pham_id,chinh_sach_gia_id,gia'])
            ->where('duoc_phep_order', true)
            ->find($id);

        if (!$sanPham) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không còn được phép order hoặc không tồn tại.',
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $this->ganTonKhoVaGia([$sanPham])[0]]);
    }

    public function apiDanhSachSanPhamDuocPhep(Request $request)
    {
        try {
            $tuKhoa = trim($request->input('tu_khoa', ''));
            $perPage = min((int) $request->input('per_page', 12), 100);

            $query = DB::table('san_phams as sp')
                ->selectRaw('sp.ma_chung, MIN(sp.id) as san_pham_dai_dien_id, COUNT(*) as so_phien_ban, MIN(NULLIF(sp.gia_order, 0)) as gia_order_thap_nhat, MIN(NULLIF(sp.gia_ban_le, 0)) as gia_ban_le_thap_nhat, MAX(sp.order_listed_at) as order_listed_at_moi_nhat')
                ->where('sp.duoc_phep_order', true)
                ->whereNotNull('sp.ma_chung')
                ->where('sp.ma_chung', '!=', '')
                ->groupBy('sp.ma_chung')
                ->orderByDesc(DB::raw('MAX(sp.order_listed_at)'))
                ->orderByDesc(DB::raw('MIN(sp.id)'));

            if ($tuKhoa !== '') {
                $query->where(function ($q) use ($tuKhoa) {
                    $q->where('sp.ten', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('sp.ten_chung', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('sp.ma_sku', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('sp.ma_vach', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('sp.ma_chung', 'like', '%' . $tuKhoa . '%')
                        ->orWhere('sp.nhan_hieu', 'like', '%' . $tuKhoa . '%');
                });
            }

            $data = $query->paginate($perPage);
            $rows = collect($data->items());
            $items = $this->dinhDangNhomSanPhamOrder($rows);

            return response()->json([
                'success' => true,
                'data' => $items,
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tải danh sách sản phẩm order đối tác: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiChiTietSanPhamDuocPhep(string $maChung)
    {
        try {
            $phienBans = SanPham::select(
                'id',
                'ten',
                'ten_chung',
                'ma_chung',
                'ma_sku',
                'ma_vach',
                'nhan_hieu',
                'anh_san_pham',
                'gia_ban_le',
                'gia_ban_buon',
                'gia_cong_tac_vien',
                'gia_order',
                'don_vi_tinh',
                'duoc_phep_order',
                'order_listed_at'
            )
                ->with(['thuocTinhs:id,san_pham_id,ten_thuoc_tinh,gia_tri', 'sanPhamGias:id,san_pham_id,chinh_sach_gia_id,gia'])
                ->where('ma_chung', $maChung)
                ->where('duoc_phep_order', true)
                ->orderBy('id')
                ->get();

            if ($phienBans->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không còn được phép order hoặc không tồn tại.',
                ], 404);
            }

            $ids = $phienBans->pluck('id')->values();
            $tonKhoTheoSanPham = DB::table('kho_hang_san_phams')
                ->select('san_pham_id', DB::raw('SUM(so_luong_ton) as ton_kho'))
                ->whereIn('san_pham_id', $ids)
                ->groupBy('san_pham_id')
                ->pluck('ton_kho', 'san_pham_id');

            $soLuongDangGiuTheoSanPham = ChiTietDonHang::whereIn('san_pham_id', $ids)
                ->whereHas('donHang', fn($q) => $q->where('trang_thai', 'cho_xu_ly'))
                ->select('san_pham_id', DB::raw('SUM(so_luong) as so_luong_dang_giu'))
                ->groupBy('san_pham_id')
                ->pluck('so_luong_dang_giu', 'san_pham_id');

            $dau = $phienBans->first();
            $anh = $phienBans
                ->flatMap(fn($sp) => $this->layDanhSachAnh($sp->anh_san_pham))
                ->filter()
                ->unique()
                ->values();

            $items = $phienBans->map(function ($sanPham) use ($tonKhoTheoSanPham, $soLuongDangGiuTheoSanPham) {
                $thuocTinh = $sanPham->thuocTinhs
                    ->mapWithKeys(fn($tt) => [$tt->ten_thuoc_tinh => $tt->gia_tri])
                    ->all();
                $tenPhienBan = !empty($thuocTinh) ? implode(' / ', array_values($thuocTinh)) : $sanPham->ten;
                $tonKho = (int) ($tonKhoTheoSanPham[$sanPham->id] ?? 0);
                $soLuongDangGiu = (int) ($soLuongDangGiuTheoSanPham[$sanPham->id] ?? 0);

                return [
                    'id' => $sanPham->id,
                    'ten' => $tenPhienBan,
                    'ten_day_du' => $sanPham->ten,
                    'ma_sku' => $sanPham->ma_sku,
                    'ma_vach' => $sanPham->ma_vach,
                    'gia_order' => (float) $sanPham->gia_order,
                    'gia_ban_le' => (float) $sanPham->gia_ban_le,
                    'don_vi_tinh' => $sanPham->don_vi_tinh,
                    'ton_kho' => $tonKho,
                    'co_the_ban' => max(0, $tonKho - $soLuongDangGiu),
                    'order_listed_at' => $sanPham->order_listed_at,
                    'anh_chinh' => $this->layAnhChinh($sanPham->anh_san_pham),
                    'thuoc_tinh' => $thuocTinh,
                    'chinh_sach_gias' => $sanPham->sanPhamGias
                        ->map(fn($gia) => [
                            'chinh_sach_gia_id' => $gia->chinh_sach_gia_id,
                            'gia' => (float) $gia->gia,
                        ])
                        ->values(),
                ];
            })->values();

            $sanPhamLienQuan = $this->laySanPhamLienQuan($maChung, $dau->nhan_hieu);

            return response()->json([
                'success' => true,
                'data' => [
                    'ma_chung' => $maChung,
                    'ten_chung' => $dau->ten_chung ?: $dau->ten,
                    'nhan_hieu' => $dau->nhan_hieu,
                    'anh' => $anh,
                    'gia_order_thap' => (float) ($items->where('gia_order', '>', 0)->min('gia_order') ?: 0),
                    'gia_order_cao' => (float) ($items->where('gia_order', '>', 0)->max('gia_order') ?: 0),
                    'gia_ban_le_thap' => (float) ($items->where('gia_ban_le', '>', 0)->min('gia_ban_le') ?: 0),
                    'gia_ban_le_cao' => (float) ($items->where('gia_ban_le', '>', 0)->max('gia_ban_le') ?: 0),
                    'tong_ton_kho' => (int) $items->sum('ton_kho'),
                    'so_phien_ban' => $items->count(),
                    'phien_bans' => $items,
                    'san_pham_lien_quan' => $sanPhamLienQuan,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tải chi tiết sản phẩm order đối tác: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiTaoDonOrder(Request $request)
    {
        try {
            $request->validate([
                'khach_hang_id' => 'required|exists:khach_hangs,id',
                'nhan_vien_ban_hang_id' => 'nullable|exists:nhan_viens,id',
                'san_phams' => 'required|array|min:1',
                'san_phams.*.san_pham_id' => 'required|exists:san_phams,id',
                'san_phams.*.so_luong' => 'required|integer|min:1',
                'san_phams.*.gia_ban_du_kien' => 'nullable|numeric|min:0',
                'san_phams.*.nguon_hang' => 'nullable|in:ton_kho,doi_nhap',
                'xac_nhan_ton_kho' => 'sometimes|boolean',
                'xoa_gio_order' => 'sometimes|boolean',
            ]);

            $nhanVienId = (int) session('doi_tac_id');
            if (!$this->orderHangService->kiemTraQuyenKhachHang((int) $request->khach_hang_id, $nhanVienId, session('doi_tac_quyen'))) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền tạo order cho khách hàng này.'], 403);
            }

            $loiNhanVienPhuTrach = $this->kiemTraNhanVienBanHangPhuTrach(
                (int) $request->khach_hang_id,
                $request->input('nhan_vien_ban_hang_id') !== null ? (int) $request->input('nhan_vien_ban_hang_id') : null,
                session('doi_tac_quyen')
            );

            if ($loiNhanVienPhuTrach) {
                return response()->json(['success' => false, 'message' => $loiNhanVienPhuTrach], 422);
            }

            $sanPhams = $this->apDungGiaDuKienHeThong($request->input('san_phams'), (int) $request->khach_hang_id);

            $payload = [
                'khach_hang_id' => (int) $request->khach_hang_id,
                'nhan_vien_ban_hang_id' => $request->input('nhan_vien_ban_hang_id'),
                'ghi_chu' => $request->input('ghi_chu'),
                'xac_nhan_ton_kho' => $request->boolean('xac_nhan_ton_kho'),
                'san_phams' => $sanPhams,
            ];

            $ketQua = $this->donBanNoiBoService->guiTaoDonOrder($nhanVienId, $payload);

            if (($ketQua['success'] ?? false) && $request->boolean('xoa_gio_order')) {
                $this->gioOrderHangService->xoaTatCa($nhanVienId);
            }

            return response()->json([
                'success' => $ketQua['success'],
                'message' => $ketQua['message'],
                'data' => $ketQua['data'] ?? null,
                'can_confirm_stock' => $ketQua['raw']['can_confirm_stock'] ?? false,
            ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo đơn order đối tác: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiLayGioOrder()
    {
        $gioOrder = $this->gioOrderHangService->layGioOrder((int) session('doi_tac_id'));

        return response()->json([
            'success' => true,
            'data' => $this->gioOrderHangService->dinhDangGioOrder($gioOrder),
        ]);
    }

    public function apiThemGioOrder(Request $request)
    {
        try {
            $request->validate([
                'san_pham_id' => 'required|exists:san_phams,id',
                'so_luong' => 'required|integer|min:1',
            ]);

            $gioOrder = $this->gioOrderHangService->themSanPham(
                (int) session('doi_tac_id'),
                (int) $request->san_pham_id,
                (int) $request->so_luong
            );

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ order.',
                'data' => $this->gioOrderHangService->dinhDangGioOrder($gioOrder),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi thêm giỏ order đối tác: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiCapNhatGioOrder(Request $request)
    {
        try {
            $request->validate([
                'chi_tiet_id' => 'required|integer|min:1',
                'so_luong' => 'required|integer|min:1',
            ]);

            $gioOrder = $this->gioOrderHangService->capNhatSoLuong(
                (int) session('doi_tac_id'),
                (int) $request->chi_tiet_id,
                (int) $request->so_luong
            );

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật giỏ order.',
                'data' => $this->gioOrderHangService->dinhDangGioOrder($gioOrder),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật giỏ order đối tác: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiXoaGioOrder(Request $request)
    {
        try {
            $request->validate([
                'chi_tiet_id' => 'required|integer|min:1',
            ]);

            $gioOrder = $this->gioOrderHangService->xoaSanPham(
                (int) session('doi_tac_id'),
                (int) $request->chi_tiet_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ order.',
                'data' => $this->gioOrderHangService->dinhDangGioOrder($gioOrder),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa giỏ order đối tác: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiXoaTatCaGioOrder()
    {
        $gioOrder = $this->gioOrderHangService->xoaTatCa((int) session('doi_tac_id'));

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ order.',
            'data' => $this->gioOrderHangService->dinhDangGioOrder($gioOrder),
        ]);
    }

    public function apiLayDanhSachKhachHangOrder(Request $request)
    {
        try {
            $perPage = min((int) $request->input('per_page', 15), 100);
            $search = trim($request->input('search', ''));
            $nhomId = $request->input('nhom_id');
            $nhanVienId = (int) session('doi_tac_id');
            $quyen = session('doi_tac_quyen');

            $query = KhachHang::select('id', 'ten', 'sdt', 'email', 'ma_khach_hang', 'nhom_khach_hang_id', 'created_at')
                ->with([
                    'nhomKhachHang:id,ten',
                    'diaChis:id,khach_hang_id,dia_chi_cu_the,phuong_xa,khu_vuc',
                    'nhanViens:id,ten,quyen',
                ])
                ->withCount('donOrderHangs');

            $this->apDungPhamViKhachHang($query, $nhanVienId, $quyen);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('ten', 'like', '%' . $search . '%')
                        ->orWhere('sdt', 'like', '%' . $search . '%')
                        ->orWhere('ma_khach_hang', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            if ($nhomId) {
                $query->where('nhom_khach_hang_id', $nhomId);
            }

            $statsQuery = KhachHang::query();
            $this->apDungPhamViKhachHang($statsQuery, $nhanVienId, $quyen);

            $nhomQuery = KhachHang::query();
            $this->apDungPhamViKhachHang($nhomQuery, $nhanVienId, $quyen);
            $nhomIds = $nhomQuery->whereNotNull('nhom_khach_hang_id')
                ->distinct()
                ->pluck('nhom_khach_hang_id');

            $data = $query->orderByDesc('created_at')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => collect($data->items())->map(function (KhachHang $khachHang) {
                    $diaChi = $khachHang->diaChis->first();

                    return [
                        'id' => $khachHang->id,
                        'ten' => $khachHang->ten,
                        'sdt' => $khachHang->sdt,
                        'email' => $khachHang->email,
                        'ma_khach_hang' => $khachHang->ma_khach_hang,
                        'created_at' => $khachHang->created_at,
                        'nhom_khach_hang' => $khachHang->nhomKhachHang ? [
                            'id' => $khachHang->nhomKhachHang->id,
                            'ten' => $khachHang->nhomKhachHang->ten,
                        ] : null,
                        'dia_chi' => $diaChi ? $diaChi->dia_chi_day_du : null,
                        'nhan_viens' => $khachHang->nhanViens->map(fn($nhanVien) => [
                            'id' => $nhanVien->id,
                            'ten' => $nhanVien->ten,
                            'quyen' => $nhanVien->quyen,
                        ])->values(),
                        'so_don_order' => (int) ($khachHang->don_order_hangs_count ?? 0),
                    ];
                })->values(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ],
                'stats' => [
                    'tong_khach_hang' => (clone $statsQuery)->count(),
                    'thang_nay' => (clone $statsQuery)->whereBetween('created_at', [now('Asia/Ho_Chi_Minh')->startOfMonth(), now('Asia/Ho_Chi_Minh')->endOfMonth()])->count(),
                    'co_don_order' => (clone $statsQuery)->whereHas('donOrderHangs')->count(),
                    'so_nhom' => $nhomIds->count(),
                ],
                'groups' => NhomKhachHang::whereIn('id', $nhomIds)
                    ->orderBy('ten')
                    ->get(['id', 'ten']),
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tải danh sách khách hàng order đối tác: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiLayDanhSach(Request $request)
    {
        try {
            $data = $this->orderHangService->layDanhSach(
                min((int) $request->input('per_page', 10), 100),
                trim($request->input('search', '')),
                $request->input('trang_thai'),
                (int) session('doi_tac_id'),
                session('doi_tac_quyen')
            );

            return response()->json([
                'success' => true,
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiLayDanhSachDonBanTuOrder(Request $request)
    {
        try {
            $boLoc = [
                'page' => $request->input('page', 1),
                'per_page' => $request->input('per_page', 15),
                'search' => trim($request->input('search', '')),
                'ngay_tao' => $request->input('ngay_tao', ''),
                'tu_ngay' => $request->input('tu_ngay'),
                'den_ngay' => $request->input('den_ngay'),
                'trang_thai' => $request->input('trang_thai', ''),
                'khach_hang' => trim($request->input('khach_hang', '')),
                'san_pham' => trim($request->input('san_pham', '')),
            ];

            $nhanVienId = (int) session('doi_tac_id');
            $quyen = session('doi_tac_quyen');
            $data = $this->orderHangService->layDanhSachDonBanTuOrder($boLoc, $nhanVienId, $quyen);

            return response()->json([
                'success' => true,
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ],
                'stats' => $this->orderHangService->thongKeDonBanTuOrder($boLoc, $nhanVienId, $quyen),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiLayChiTietDonBanTuOrder(int $id)
    {
        $duLieu = $this->orderHangService->layChiTietDonBanTuOrder($id, (int) session('doi_tac_id'), session('doi_tac_quyen'));
        if (!$duLieu) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn này.'], 404);
        }

        return response()->json(['success' => true, 'data' => $duLieu]);
    }
    

    public function apiLayDanhSachPhieuTraHang(Request $request)
    {
        if (!$this->coQuyenQuanLyPhieuTraHang()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xem danh sách phiếu trả hàng order.'], 403);
        }

        $ketQua = $this->phieuTraHangNoiBoService->layDanhSach((int) session('doi_tac_id'), [
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
            'pagination' => $ketQua['pagination'] ?? null,
            'stats' => $ketQua['stats'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiLayChiTietPhieuTraHang(int $id)
    {
        if (!$this->coQuyenQuanLyPhieuTraHang()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xem phiếu trả hàng order.'], 403);
        }

        $ketQua = $this->phieuTraHangNoiBoService->layChiTiet($id, (int) session('doi_tac_id'));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiLaySoLuongDaTra(int $donHangId)
    {
        if (!$this->coQuyenQuanLyPhieuTraHang()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền kiểm tra số lượng đã trả.'], 403);
        }

        $ketQua = $this->phieuTraHangNoiBoService->laySoLuongDaTra($donHangId, (int) session('doi_tac_id'));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? [],
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiTaoPhieuTraHang(Request $request)
    {
        if (!$this->coQuyenQuanLyPhieuTraHang()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền tạo phiếu trả hàng order.'], 403);
        }

        $request->validate([
            'don_hang_id' => 'required|integer',
            'san_phams' => 'required|array|min:1',
            'san_phams.*.san_pham_id' => 'required|integer',
            'san_phams.*.so_luong' => 'required|integer|min:1',
            'san_phams.*.gia_tra' => 'required|numeric|min:0',
        ]);

        $ketQua = $this->phieuTraHangNoiBoService->taoPhieu((int) session('doi_tac_id'), $request->only([
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
        if (!in_array(session('doi_tac_quyen'), ['admin', 'quan_ly_order'], true)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền hoàn tiền phiếu trả hàng.'], 403);
        }

        $request->validate([
            'so_tien' => 'nullable|numeric|min:1',
        ]);

        $ketQua = $this->phieuTraHangNoiBoService->hoanTien($id, (int) session('doi_tac_id'), [
            'so_tien' => $request->input('so_tien'),
        ]);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiThaoTacDonBanTuOrder(Request $request, int $id, string $hanhDong)
    {
        if (!$this->coQuyenThaoTacDonBan($hanhDong)) {
            return response()->json(['success' => false, 'message' => 'Ban khong co quyen thuc hien thao tac nay.'], 403);
        }

        $nhanVienId = (int) session('doi_tac_id');
        $duLieu = $this->orderHangService->layChiTietDonBanTuOrder($id, $nhanVienId, session('doi_tac_quyen'));

        if (!$duLieu) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền thao tác đơn này.',
            ], 404);
        }

        $payload = [];
        if ($hanhDong === 'lay-hang-trong-kho') {
            $payload = $request->validate([
                'san_phams' => 'required|array|min:1',
                'san_phams.*.san_pham_id' => 'required|integer|min:1',
                'san_phams.*.so_luong' => 'required|integer|min:1',
            ]);
        }

        $ketQua = $this->donBanNoiBoService->guiThaoTac($id, $nhanVienId, $hanhDong, $payload);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiKiemTraLayHangTrongKho(int $id)
    {
        if (!$this->coQuyenThaoTacDonBan('lay-hang-trong-kho')) {
            return response()->json(['success' => false, 'message' => 'Ban khong co quyen lay hang trong kho.'], 403);
        }

        $nhanVienId = (int) session('doi_tac_id');
        $duLieu = $this->orderHangService->layChiTietDonBanTuOrder($id, $nhanVienId, session('doi_tac_quyen'));

        if (!$duLieu) {
            return response()->json([
                'success' => false,
                'message' => 'KhÃ´ng tÃ¬m tháº¥y Ä‘Æ¡n hÃ ng hoáº·c báº¡n khÃ´ng cÃ³ quyá»n thao tÃ¡c Ä‘Æ¡n nÃ y.',
            ], 404);
        }

        $ketQua = $this->donBanNoiBoService->kiemTraLayHangTrongKho($id, $nhanVienId);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiLayHangOrderVe(Request $request)
    {
        try {
            $data = $this->orderHangService->layDanhSachHangOrderVe(
                min((int) $request->input('per_page', 10), 100),
                trim($request->input('search', '')),
                $request->input('trang_thai'),
                (int) session('doi_tac_id'),
                session('doi_tac_quyen')
            );

            return response()->json([
                'success' => true,
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiThongKeTrangThai()
    {
        return response()->json([
            'success' => true,
            'data' => $this->orderHangService->thongKeTrangThai((int) session('doi_tac_id'), session('doi_tac_quyen')),
        ]);
    }

    public function apiLayChiTiet(int $id)
    {
        $donOrder = $this->orderHangService->layChiTiet($id, (int) session('doi_tac_id'), session('doi_tac_quyen'));
        if (!$donOrder) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn order.'], 404);
        }

        return response()->json(['success' => true, 'data' => $donOrder]);
    }

    public function apiChuyenDonBan(Request $request, int $id)
    {
        if (!$this->coQuyenThaoTacDonOrder('chuyen-don-ban')) {
            return response()->json(['success' => false, 'message' => 'Ban khong co quyen tao don ban tu don order.'], 403);
        }

        $donOrder = $this->orderHangService->layChiTiet($id, (int) session('doi_tac_id'), session('doi_tac_quyen'));
        if (!$donOrder) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn order hoặc bạn không có quyền thao tác đơn này.'], 404);
        }

        $ketQua = $this->donBanNoiBoService->guiChuyenDonBan($id, (int) session('doi_tac_id'), [
            'chi_tiet_ids' => $request->input('chi_tiet_ids', []),
        ]);

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiHuyDonOrder(int $id)
    {
        if (!$this->coQuyenThaoTacDonOrder('huy-don-order')) {
            return response()->json(['success' => false, 'message' => 'Ban khong co quyen huy don order.'], 403);
        }

        $donOrder = $this->orderHangService->layChiTiet($id, (int) session('doi_tac_id'), session('doi_tac_quyen'));
        if (!$donOrder) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn order hoặc bạn không có quyền hủy đơn này.'], 404);
        }

        $ketQua = $this->donBanNoiBoService->guiHuyDonOrder($id, (int) session('doi_tac_id'));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    public function apiHuyChiTietDonOrder(int $id)
    {
        if (!$this->coQuyenThaoTacDonOrder('huy-chi-tiet-order')) {
            return response()->json(['success' => false, 'message' => 'Ban khong co quyen huy dong order.'], 403);
        }

        $ketQua = $this->donBanNoiBoService->guiHuyChiTietDonOrder($id, (int) session('doi_tac_id'));

        return response()->json([
            'success' => $ketQua['success'],
            'message' => $ketQua['message'],
            'data' => $ketQua['data'] ?? null,
        ], $ketQua['status'] ?? ($ketQua['success'] ? 200 : 500));
    }

    private function ganTonKhoVaGia($sanPhams): array
    {
        $itemsCollection = collect($sanPhams);
        $ids = $itemsCollection->pluck('id')->values();

        $tonKhoTheoSanPham = DB::table('kho_hang_san_phams')
            ->select('san_pham_id', DB::raw('SUM(so_luong_ton) as ton_kho'))
            ->whereIn('san_pham_id', $ids)
            ->groupBy('san_pham_id')
            ->pluck('ton_kho', 'san_pham_id');

        $soLuongDangGiuTheoSanPham = ChiTietDonHang::whereIn('san_pham_id', $ids)
            ->whereHas('donHang', fn($q) => $q->where('trang_thai', 'cho_xu_ly'))
            ->select('san_pham_id', DB::raw('SUM(so_luong) as so_luong_dang_giu'))
            ->groupBy('san_pham_id')
            ->pluck('so_luong_dang_giu', 'san_pham_id');

        return $itemsCollection->map(function ($sanPham) use ($tonKhoTheoSanPham, $soLuongDangGiuTheoSanPham) {
            $sanPham->anh_chinh = $this->layAnhChinh($sanPham->anh_san_pham);
            $tonKho = (int) ($tonKhoTheoSanPham[$sanPham->id] ?? 0);
            $soLuongDangGiu = (int) ($soLuongDangGiuTheoSanPham[$sanPham->id] ?? 0);
            $sanPham->ton_kho = $tonKho;
            $sanPham->co_the_ban = max(0, $tonKho - $soLuongDangGiu);
            $sanPham->chinh_sach_gias = $sanPham->sanPhamGias
                ->map(fn($gia) => [
                    'chinh_sach_gia_id' => $gia->chinh_sach_gia_id,
                    'gia' => (float) $gia->gia,
                ])
                ->values();
            $sanPham->unsetRelation('sanPhamGias');
            return $sanPham;
        })->all();
    }

    private function coQuyenQuanLyPhieuTraHang(): bool
    {
        return in_array(session('doi_tac_quyen'), ['admin', 'thu_kho', 'quan_ly_order'], true);
    }

    private function coQuyenThaoTacDonBan(string $hanhDong): bool
    {
        $quyen = session('doi_tac_quyen');
        $quyenTheoHanhDong = [
            'duyet' => ['admin', 'thu_kho', 'quan_ly_order'],
            'bao-hang-ve' => ['admin', 'thu_kho', 'quan_ly_order'],
            'lay-hang-trong-kho' => ['admin', 'thu_kho', 'quan_ly_order'],
            'xuat-kho' => ['admin', 'thu_kho', 'quan_ly_order'],
            'dong-goi' => ['admin', 'thu_kho', 'quan_ly_order', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
            'van-chuyen' => ['admin', 'thu_kho', 'quan_ly_order', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
            'tu-van-chuyen-ntq' => ['admin', 'thu_kho', 'quan_ly_order', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
            'hoan-thanh' => ['admin', 'thu_kho', 'quan_ly_order', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
        ];

        return in_array($quyen, $quyenTheoHanhDong[$hanhDong] ?? [], true);
    }

    private function coQuyenThaoTacDonOrder(string $hanhDong): bool
    {
        $quyen = session('doi_tac_quyen');
        $quyenTheoHanhDong = [
            'chuyen-don-ban' => ['admin', 'thu_kho', 'quan_ly_order', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
            'huy-don-order' => ['admin', 'quan_ly_order', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
            'huy-chi-tiet-order' => ['admin', 'quan_ly_order', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'],
        ];

        return in_array($quyen, $quyenTheoHanhDong[$hanhDong] ?? [], true);
    }

    private function kiemTraNhanVienBanHangPhuTrach(int $khachHangId, ?int $nhanVienBanHangId, ?string $quyen): ?string
    {
        if (!in_array($quyen, ['admin', 'thu_kho', 'quan_ly_order'], true)) {
            return null;
        }

        if (!$nhanVienBanHangId) {
            return 'Khách hàng này chưa có nhân viên bán hàng phụ trách hoặc bạn chưa chọn nhân viên phụ trách.';
        }

        $hopLe = NhanVien::where('id', $nhanVienBanHangId)
            ->whereIn('quyen', ['nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'])
            ->whereHas('khachHangDuocGan', function ($khachHang) use ($khachHangId) {
                $khachHang->where('khach_hangs.id', $khachHangId);
            })
            ->exists();

        if (!$hopLe) {
            return 'Nhân viên phụ trách không hợp lệ hoặc không được gán với khách hàng này.';
        }

        return null;
    }

    private function apDungPhamViKhachHang($query, int $nhanVienId, ?string $quyen): void
    {
        if (in_array($quyen, ['nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2', 'thu_kho'], true)) {
            $query->whereHas('nhanViens', function ($nhanVien) use ($nhanVienId) {
                $nhanVien->where('nhan_viens.id', $nhanVienId);
            });
        }
    }

    private function dinhDangNhomSanPhamOrder($rows)
    {
        $maChungs = $rows->pluck('ma_chung')->filter()->values();
        $idsDaiDien = $rows->pluck('san_pham_dai_dien_id')->filter()->values();

        $sanPhamDaiDien = SanPham::select('id', 'ten', 'ten_chung', 'ma_chung', 'nhan_hieu', 'anh_san_pham', 'gia_ban_le', 'gia_order', 'order_listed_at')
            ->whereIn('id', $idsDaiDien)
            ->get()
            ->keyBy('id');

        $phienBans = SanPham::select('id', 'ma_chung')
            ->whereIn('ma_chung', $maChungs)
            ->where('duoc_phep_order', true)
            ->get();
        $phienBanIds = $phienBans->pluck('id')->values();

        $tonKhoTheoSanPham = DB::table('kho_hang_san_phams')
            ->select('san_pham_id', DB::raw('SUM(so_luong_ton) as ton_kho'))
            ->whereIn('san_pham_id', $phienBanIds)
            ->groupBy('san_pham_id')
            ->pluck('ton_kho', 'san_pham_id');

        return $rows->map(function ($row) use ($sanPhamDaiDien, $phienBans, $tonKhoTheoSanPham) {
            $daiDien = $sanPhamDaiDien->get($row->san_pham_dai_dien_id);
            $cacPhienBan = $phienBans->where('ma_chung', $row->ma_chung);

            return [
                'ma_chung' => $row->ma_chung,
                'ten_chung' => $daiDien?->ten_chung ?: $daiDien?->ten,
                'nhan_hieu' => $daiDien?->nhan_hieu,
                'anh_chinh' => $this->layAnhChinh($daiDien?->anh_san_pham),
                'gia_order' => (float) ($row->gia_order_thap_nhat ?: 0),
                'gia_ban_le' => (float) ($row->gia_ban_le_thap_nhat ?: 0),
                'so_phien_ban' => (int) $row->so_phien_ban,
                'ton_kho' => (int) $cacPhienBan->sum(fn($sp) => (int) ($tonKhoTheoSanPham[$sp->id] ?? 0)),
                'order_listed_at' => $row->order_listed_at_moi_nhat,
            ];
        })->values();
    }

    private function laySanPhamLienQuan(string $maChung, ?string $nhanHieu)
    {
        $rowsLienQuan = DB::table('san_phams as sp')
            ->selectRaw('sp.ma_chung, MIN(sp.id) as san_pham_dai_dien_id, COUNT(*) as so_phien_ban, MIN(NULLIF(sp.gia_order, 0)) as gia_order_thap_nhat, MIN(NULLIF(sp.gia_ban_le, 0)) as gia_ban_le_thap_nhat, MAX(sp.order_listed_at) as order_listed_at_moi_nhat')
            ->where('sp.duoc_phep_order', true)
            ->where('sp.ma_chung', '!=', $maChung)
            ->when($nhanHieu, fn($q) => $q->where('sp.nhan_hieu', $nhanHieu))
            ->whereNotNull('sp.ma_chung')
            ->where('sp.ma_chung', '!=', '')
            ->groupBy('sp.ma_chung')
            ->orderByDesc(DB::raw('MAX(sp.order_listed_at)'))
            ->limit(4)
            ->get();

        if ($rowsLienQuan->count() < 4) {
            $maChungDaCo = $rowsLienQuan->pluck('ma_chung')->push($maChung)->values();
            $boSung = DB::table('san_phams as sp')
                ->selectRaw('sp.ma_chung, MIN(sp.id) as san_pham_dai_dien_id, COUNT(*) as so_phien_ban, MIN(NULLIF(sp.gia_order, 0)) as gia_order_thap_nhat, MIN(NULLIF(sp.gia_ban_le, 0)) as gia_ban_le_thap_nhat, MAX(sp.order_listed_at) as order_listed_at_moi_nhat')
                ->where('sp.duoc_phep_order', true)
                ->whereNotIn('sp.ma_chung', $maChungDaCo)
                ->whereNotNull('sp.ma_chung')
                ->where('sp.ma_chung', '!=', '')
                ->groupBy('sp.ma_chung')
                ->orderByDesc(DB::raw('MAX(sp.order_listed_at)'))
                ->limit(4 - $rowsLienQuan->count())
                ->get();
            $rowsLienQuan = $rowsLienQuan->concat($boSung)->values();
        }

        return $this->dinhDangNhomSanPhamOrder($rowsLienQuan);
    }

    private function layAnhChinh($anhSanPham): ?string
    {
        if (empty($anhSanPham)) {
            return null;
        }

        $decoded = json_decode($anhSanPham, true);
        return is_array($decoded) && count($decoded) > 0 ? $decoded[0] : $anhSanPham;
    }

    private function layDanhSachAnh($anhSanPham): array
    {
        if (empty($anhSanPham)) {
            return [];
        }

        $decoded = json_decode($anhSanPham, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded));
        }

        return [$anhSanPham];
    }

    private function apDungGiaDuKienHeThong(array $sanPhams, int $khachHangId): array
    {
        $sanPhamIds = collect($sanPhams)->pluck('san_pham_id')->unique()->values();
        $sanPhamTheoId = SanPham::select('id', 'ten', 'ma_sku', 'gia_order')
            ->whereIn('id', $sanPhamIds)
            ->get()
            ->keyBy('id');

        return collect($sanPhams)->map(function ($item) use ($sanPhamTheoId) {
            $sanPham = $sanPhamTheoId->get($item['san_pham_id']);
            if ($sanPham) {
                $giaOrder = (float) $sanPham->gia_order;
                if ($giaOrder <= 0) {
                    $tenSanPham = $sanPham->ma_sku ?: $sanPham->ten ?: ('ID ' . $sanPham->id);
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'san_phams' => ["San pham {$tenSanPham} chua co gia order hop le."],
                    ]);
                }

                $item['gia_ban_du_kien'] = $giaOrder;
            }
            return $item;
        })->all();
    }

}
