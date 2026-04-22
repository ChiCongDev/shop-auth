<?php

namespace App\Http\Service;

use App\Models\SanPham;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service đọc dữ liệu sản phẩm từ DB chung với hệ thống nội bộ.
 *
 * Nguyên tắc:
 *  - Mỗi dòng trong san_phams = 1 phiên bản (variant) sản phẩm
 *  - Các phiên bản cùng sản phẩm được nhóm bởi ma_chung
 *  - Tồn kho lấy từ kho_hang_san_phams.so_luong_ton
 *  - Thuộc tính (size, màu...) lấy từ san_pham_thuoc_tinhs.ten_thuoc_tinh + gia_tri
 */
class SanPhamService
{
    // =========================================================
    // TRANG CHỦ
    // =========================================================

    /**
     * Lấy sản phẩm nổi bật cho trang chủ
     * Trả về 1 đại diện mỗi nhóm ma_chung, ưu tiên có ảnh và còn hàng
     */
    public function laySanPhamNoiBat(int $soLuong = 8): \Illuminate\Database\Eloquent\Collection
    {
        // LEFT JOIN: ẩn SP đã khai kho nhưng hết hàng, giữ SP chưa khai kho
        $ids = DB::table('san_phams as sp')
            ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
            ->selectRaw('MIN(sp.id) as min_id')
            ->groupBy('sp.ma_chung')
            ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0')
            ->orderByRaw('MAX(sp.created_at) DESC')
            ->limit($soLuong)
            ->pluck('min_id');

        return SanPham::with(['khoHangSanPhams', 'thuocTinhs'])
            ->whereIn('id', $ids)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy sản phẩm mới về — dựa trên phiếu nhập hàng (đơn nhập kho) mới nhất.
     *
     * Logic:
     *  - JOIN phieu_nhap_chi_tiets → phieu_nhaps (trang_thai = 'da_nhap')
     *  - Lấy 1 đại diện mỗi nhóm ma_chung, ưu tiên phiếu nhập mới nhất
     *  - Chỉ hiện nhóm có tổng tồn kho > 0
     *  - Khi nhân viên kho tạo & duyệt đơn nhập mới → SP tự động xuất hiện ở đây
     */
    public function laySanPhamMoiNhat(int $soLuong = 8): \Illuminate\Database\Eloquent\Collection
    {
        // Bước 1: tìm 1 sp_id đại diện mỗi nhóm ma_chung,
        //         sắp xếp theo phiếu nhập mới nhất, chỉ nhóm còn hàng
        $rows = DB::table('phieu_nhap_chi_tiets as pnct')
            ->join('phieu_nhaps as pn', 'pnct.phieu_nhap_id', '=', 'pn.id')
            ->join('san_phams as sp', 'pnct.san_pham_id', '=', 'sp.id')
            ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
            ->where('pn.trang_thai', 'da_nhap')
            ->selectRaw('sp.ma_chung, MIN(sp.id) as min_id, MAX(pn.created_at) as ngay_nhap_moi_nhat')
            ->groupBy('sp.ma_chung')
            ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0')
            ->orderBy('ngay_nhap_moi_nhat', 'desc')
            ->limit($soLuong)
            ->get();

        $ids = $rows->pluck('min_id');

        // Nếu không có phiếu nhập nào (dữ liệu test), fallback về SP mới tạo
        if ($ids->isEmpty()) {
            $ids = DB::table('san_phams as sp')
                ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
                ->selectRaw('MIN(sp.id) as min_id, MAX(sp.created_at) as ngay_tao')
                ->groupBy('sp.ma_chung')
                ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0')
                ->orderBy('ngay_tao', 'desc')
                ->limit($soLuong)
                ->pluck('min_id');
        }

        // Bảo toàn thứ tự phiếu nhập + gán ngay_nhap vào từng model
        $maChungOrder = $rows->pluck('ngay_nhap_moi_nhat', 'min_id');

        return SanPham::with(['khoHangSanPhams'])
            ->whereIn('id', $ids)
            ->get()
            ->each(function ($sp) use ($maChungOrder) {
                // Gán ngay_nhap từ phiếu nhập; fallback về created_at nếu không có
                $sp->ngay_nhap = isset($maChungOrder[$sp->id])
                    ? \Carbon\Carbon::parse($maChungOrder[$sp->id])
                    : $sp->created_at;
            })
            ->sortByDesc(fn($sp) => $sp->ngay_nhap)
            ->values();
    }

    /**
     * Trang /hang-moi-ve — phân trang đầy đủ, có filter nhãn hiệu & sort
     * Lấy SP theo phiếu nhập mới nhất, còn hàng
     */
    public function layHangMoiVe(
        int    $perPage  = 16,
        string $nhanHieu = '',
        string $sapXep   = 'moi_nhat'
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {

        // Sub-query: 1 sp_id đại diện mỗi nhóm ma_chung, theo phiếu nhập mới nhất
        $sub = DB::table('phieu_nhap_chi_tiets as pnct')
            ->join('phieu_nhaps as pn', 'pnct.phieu_nhap_id', '=', 'pn.id')
            ->join('san_phams as sp', 'pnct.san_pham_id', '=', 'sp.id')
            ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
            ->where('pn.trang_thai', 'da_nhap')
            ->selectRaw('sp.ma_chung, MIN(sp.id) as min_id, MAX(pn.created_at) as ngay_nhap')
            ->groupBy('sp.ma_chung')
            ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0');

        if ($nhanHieu !== '') {
            $sub->where('sp.nhan_hieu', $nhanHieu);
        }

        $rows = $sub->get();

        // Fallback nếu chưa có phiếu nhập
        if ($rows->isEmpty()) {
            $fallbackSub = DB::table('san_phams as sp')
                ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
                ->selectRaw('sp.ma_chung, MIN(sp.id) as min_id, MAX(sp.created_at) as ngay_nhap')
                ->groupBy('sp.ma_chung')
                ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0');
            if ($nhanHieu !== '') {
                $fallbackSub->where('sp.nhan_hieu', $nhanHieu);
            }
            $rows = $fallbackSub->get();
        }

        $ids = $rows->pluck('min_id');
        $ngayNhapMap = $rows->pluck('ngay_nhap', 'min_id');

        $query = SanPham::with(['khoHangSanPhams'])
            ->whereIn('id', $ids);

        // Thêm ngay_nhap tạm thời để sort có thể dùng
        match ($sapXep) {
            'gia_tang' => $query->orderBy('gia_ban_le', 'asc'),
            'gia_giam' => $query->orderBy('gia_ban_le', 'desc'),
            'ten_az'   => $query->orderBy('ten_chung', 'asc'),
            default    => $query->orderByRaw(
                // Sắp theo ngày nhập: FIELD(id, ...) theo thứ tự đã sort từ $rows
                $ids->isEmpty() ? 'created_at DESC'
                : 'FIELD(id, ' . $rows->sortByDesc('ngay_nhap')->pluck('min_id')->implode(',') . ')'
            ),
        };

        $paginated = $query->paginate($perPage);

        // Gán ngay_nhap vào từng item
        $paginated->getCollection()->each(function ($sp) use ($ngayNhapMap) {
            $sp->ngay_nhap = isset($ngayNhapMap[$sp->id])
                ? \Carbon\Carbon::parse($ngayNhapMap[$sp->id])
                : $sp->created_at;
        });

        return $paginated;
    }

    // =========================================================
    // DANH SÁCH SẢN PHẨM
    // =========================================================

    /**
     * Danh sách sản phẩm phân trang, 1 thẻ = 1 nhóm ma_chung
     */
    public function layDanhSach(
        int    $perPage   = 12,
        string $search    = '',
        string $loai      = '',
        string $nhanHieu  = '',
        string $sapXep    = 'moi_nhat'
    ): LengthAwarePaginator {

        // LEFT JOIN: ẩn SP đã khai kho nhưng hết hàng, giữ SP chưa khai kho
        $subQuery = DB::table('san_phams as sp_inner')
            ->leftJoin('kho_hang_san_phams as kh', 'sp_inner.id', '=', 'kh.san_pham_id')
            ->selectRaw('MIN(sp_inner.id) as min_id')
            ->groupBy('sp_inner.ma_chung')
            ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0');

        if ($search !== '') {
            $subQuery->where(function ($q) use ($search) {
                $q->where('sp_inner.ten', 'like', "%{$search}%")
                  ->orWhere('sp_inner.ten_chung', 'like', "%{$search}%")
                  ->orWhere('sp_inner.nhan_hieu', 'like', "%{$search}%")
                  ->orWhere('sp_inner.ma_sku', 'like', "%{$search}%");
            });
        }
        if ($loai !== '') {
            if ($loai === 'quan-ao') {
                $subQuery->whereIn('sp_inner.loai_san_pham', ['Áo', 'Quần', 'Váy', 'Set Quần Áo', 'Áo Thun']);
            } elseif ($loai === 'giay-dep') {
                $subQuery->whereIn('sp_inner.loai_san_pham', ['Giày', 'Dép']);
            } elseif ($loai === 'balo-tui') {
                $subQuery->whereIn('sp_inner.loai_san_pham', ['Balo', 'Túi']);
            } else {
                $subQuery->where('sp_inner.loai_san_pham', $loai);
            }
        }
        if ($nhanHieu !== '') {
            $subQuery->where('sp_inner.nhan_hieu', $nhanHieu);
        }

        $ids = $subQuery->pluck('min_id');

        $query = SanPham::with(['khoHangSanPhams'])
            ->whereIn('id', $ids);

        match ($sapXep) {
            'gia_tang'  => $query->orderBy('gia_ban_le', 'asc'),
            'gia_giam'  => $query->orderBy('gia_ban_le', 'desc'),
            'ten_az'    => $query->orderBy('ten_chung', 'asc'),
            default     => $query->orderBy('created_at', 'desc'),
        };

        return $query->paginate($perPage);
    }

    // =========================================================
    // CHI TIẾT SẢN PHẨM
    // =========================================================

    /**
     * Lấy toàn bộ thông tin 1 sản phẩm (tất cả phiên bản)
     * Trả về null nếu không tìm thấy
     */
    public function layChiTietSanPham(string $maChung): ?array
    {
        $phienBans = SanPham::with(['khoHangSanPhams', 'thuocTinhs'])
            ->where('ma_chung', $maChung)
            ->orderBy('id')
            ->get();

        if ($phienBans->isEmpty()) return null;

        $dau = $phienBans->first();

        return [
            'ma_chung'   => $maChung,
            'ten_chung'  => $dau->ten_chung ?: $dau->ten,
            'nhan_hieu'  => $dau->nhan_hieu,
            'loai'       => $dau->loai_san_pham,
            'anh'        => $this->gom_anh($phienBans),
            'gia_thap'   => (float) $phienBans->min('gia_ban_le'),
            'gia_cao'    => (float) $phienBans->max('gia_ban_le'),
            'phien_bans' => $this->format_phien_ban($phienBans),
        ];
    }

    /**
     * Lấy sản phẩm liên quan (cùng loại, khác ma_chung)
     */
    public function laySanPhamLienQuan(string $maChung, string $loai = '', int $soLuong = 4): \Illuminate\Database\Eloquent\Collection
    {
        $subQuery = DB::table('san_phams as sp')
            ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
            ->selectRaw('MIN(sp.id) as min_id')
            ->where('sp.ma_chung', '!=', $maChung)
            ->groupBy('sp.ma_chung')
            ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0');

        if ($loai) {
            $subQuery->where('sp.loai_san_pham', $loai);
        }

        $ids = $subQuery->limit($soLuong)->pluck('min_id');

        if ($ids->count() < $soLuong) {
            $ids = DB::table('san_phams as sp')
                ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
                ->selectRaw('MIN(sp.id) as min_id')
                ->where('sp.ma_chung', '!=', $maChung)
                ->groupBy('sp.ma_chung')
                ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0')
                ->limit($soLuong)
                ->pluck('min_id');
        }

        return SanPham::whereIn('id', $ids)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // =========================================================
    // BỘ LỌC
    // =========================================================

    /**
     * Danh sách loại sản phẩm duy nhất (cho sidebar filter)
     */
    public function layDanhSachLoai(): array
    {
        return SanPham::select('loai_san_pham')
            ->whereNotNull('loai_san_pham')
            ->where('loai_san_pham', '!=', '')
            ->distinct()
            ->orderBy('loai_san_pham')
            ->pluck('loai_san_pham')
            ->toArray();
    }

    /**
     * Danh sách nhãn hiệu — chỉ hiện thương hiệu có sản phẩm trong context hiện tại (loại, search)
     */
    public function layDanhSachNhanHieu(string $loai = '', string $search = '', bool $nhapKhoOnly = false): array
    {
        if ($nhapKhoOnly) {
            // Chỉ lấy nhãn hiệu từ SP đã có phiếu nhập và còn hàng
            $query = DB::table('phieu_nhap_chi_tiets as pnct')
                ->join('phieu_nhaps as pn', 'pnct.phieu_nhap_id', '=', 'pn.id')
                ->join('san_phams as sp', 'pnct.san_pham_id', '=', 'sp.id')
                ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
                ->where('pn.trang_thai', 'da_nhap')
                ->whereNotNull('sp.nhan_hieu')
                ->where('sp.nhan_hieu', '!=', '')
                ->select('sp.nhan_hieu')
                ->groupBy('sp.nhan_hieu')
                ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0')
                ->orderBy('sp.nhan_hieu');

            return $query->pluck('sp.nhan_hieu')->toArray();
        }

        // JOIN kho để chỉ liệt kê nhãn hiệu có ít nhất 1 sản phẩm còn hàng
        $query = DB::table('san_phams as sp')
            ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
            ->select('sp.nhan_hieu')
            ->whereNotNull('sp.nhan_hieu')
            ->where('sp.nhan_hieu', '!=', '')
            ->groupBy('sp.nhan_hieu')
            ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0')
            ->orderBy('sp.nhan_hieu');

        // Lọc theo danh mục đang chọn
        if ($loai !== '') {
            if ($loai === 'quan-ao') {
                $query->whereIn('sp.loai_san_pham', ['Áo', 'Áo Thun', 'Quần', 'Váy', 'Set Quần Áo']);
            } elseif ($loai === 'giay-dep') {
                $query->whereIn('sp.loai_san_pham', ['Giày', 'Dép']);
            } elseif ($loai === 'balo-tui') {
                $query->whereIn('sp.loai_san_pham', ['Balo', 'Túi']);
            } elseif ($loai === 'Mũ') {
                $query->where('sp.loai_san_pham', 'Mũ');
            } else {
                $query->where('sp.loai_san_pham', $loai);
            }
        }

        // Lọc theo từkhóa đang tìm
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('sp.ten', 'like', "%{$search}%")
                  ->orWhere('sp.ten_chung', 'like', "%{$search}%")
                  ->orWhere('sp.nhan_hieu', 'like', "%{$search}%");
            });
        }

        return $query->pluck('sp.nhan_hieu')->toArray();
    }

    // =========================================================
    // API
    // =========================================================

    /**
     * Tìm kiếm nhanh cho ô search (trả về JSON)
     */
    public function timKiemNhanh(string $keyword, int $soLuong = 8): array
    {
        if (strlen(trim($keyword)) < 2) return [];

        $ids = DB::table('san_phams as sp')
            ->leftJoin('kho_hang_san_phams as kh', 'sp.id', '=', 'kh.san_pham_id')
            ->selectRaw('MIN(sp.id) as min_id')
            ->where(function ($q) use ($keyword) {
                $q->where('sp.ten', 'like', "%{$keyword}%")
                  ->orWhere('sp.ten_chung', 'like', "%{$keyword}%")
                  ->orWhere('sp.nhan_hieu', 'like', "%{$keyword}%")
                  ->orWhere('sp.ma_sku', 'like', "%{$keyword}%");
            })
            ->groupBy('sp.ma_chung')
            ->havingRaw('COALESCE(SUM(kh.so_luong_ton), 0) > 0')
            ->limit($soLuong)
            ->pluck('min_id');

        return SanPham::whereIn('id', $ids)
            ->get()
            ->map(fn ($sp) => [
                'id'         => $sp->id,
                'ma_chung'   => $sp->ma_chung,
                'ten_chung'  => $sp->ten_chung ?: $sp->ten,
                'gia'        => number_format($sp->gia_ban_le, 0, ',', '.'),
                'anh'        => $sp->anh_dau_tien,
                'url'        => '/san-pham/' . $sp->ma_chung,
            ])
            ->toArray();
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Gom tất cả ảnh từ các phiên bản, loại trùng lặp
     */
    private function gom_anh($phienBans): array
    {
        $anh = [];
        foreach ($phienBans as $sp) {
            foreach ($sp->danh_sach_anh as $a) {
                if ($a && !in_array($a, $anh)) {
                    $anh[] = $a;
                }
            }
        }
        return $anh;
    }

    /**
     * Format danh sách phiên bản để truyền vào view
     */
    private function format_phien_ban($phienBans): array
    {
        return $phienBans->map(function ($sp) {
            // Tồn kho: sum so_luong_ton từ tất cả kho
            $tonKho = $sp->khoHangSanPhams->sum('so_luong_ton');

            // Thuộc tính: ['Size' => 'L', 'Màu' => 'Đen', ...]
            $thuocTinh = [];
            foreach ($sp->thuocTinhs as $tt) {
                $thuocTinh[$tt->ten_thuoc_tinh] = $tt->gia_tri;
            }

            // Tên hiển thị: dùng thuộc tính nếu có, không thì dùng tên variant
            $tenHienThi = $sp->ten;
            if (!empty($thuocTinh)) {
                $tenHienThi = implode(' / ', array_values($thuocTinh));
            }

            return [
                'id'         => $sp->id,
                'ten'        => $tenHienThi,
                'ma_sku'     => $sp->ma_sku,
                'gia'        => (float) $sp->gia_ban_le,
                'ton_kho'    => (int) $tonKho,
                'anh'        => $sp->anh_dau_tien,
                'thuoc_tinh' => $thuocTinh,
            ];
        })->values()->toArray();
    }
}
