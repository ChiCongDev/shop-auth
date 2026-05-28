<?php

namespace App\Http\Service;

use App\Models\ChiTietDonHang;
use App\Models\ChiTietDonOrderHang;
use App\Models\DonHang;
use App\Models\DonOrderHang;
use App\Models\KhachHang;
use App\Models\LichSuDonOrderHang;
use App\Models\SanPham;
use Illuminate\Support\Facades\DB;

class DoiTacOrderHangService
{
    public function taoMaDonOrder(): string
    {
        $prefix = 'OD';
        $date = now('Asia/Ho_Chi_Minh')->format('ymd');

        $donCuoi = DonOrderHang::where('ma_don_order', 'like', $prefix . $date . '%')
            ->orderBy('ma_don_order', 'desc')
            ->lockForUpdate()
            ->first();

        $soTiepTheo = $donCuoi ? ((int) substr($donCuoi->ma_don_order, -4)) + 1 : 1;

        return $prefix . $date . str_pad($soTiepTheo, 4, '0', STR_PAD_LEFT);
    }

    public function kiemTraQuyenKhachHang(int $khachHangId, int $nhanVienId, ?string $quyen = null): bool
    {
        if (!in_array($quyen, ['nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2', 'thu_kho'], true)) {
            return true;
        }

        return KhachHang::where('id', $khachHangId)
            ->whereHas('nhanViens', function ($query) use ($nhanVienId) {
                $query->where('nhan_viens.id', $nhanVienId);
            })
            ->exists();
    }

    public function taoDonOrder(array $data, array $sanPhams): DonOrderHang
    {
        return DB::transaction(function () use ($data, $sanPhams) {
            $ids = collect($sanPhams)->pluck('san_pham_id')->unique()->values();
            $soSanPhamDuocPhep = SanPham::whereIn('id', $ids)
                ->where('duoc_phep_order', true)
                ->count();

            if ($soSanPhamDuocPhep !== $ids->count()) {
                throw new \Exception('Có sản phẩm chưa được phép order.');
            }

            $donOrder = DonOrderHang::create([
                'ma_don_order' => $this->taoMaDonOrder(),
                'trang_thai' => 'dat_truoc',
                'khach_hang_id' => $data['khach_hang_id'],
                'nhan_vien_id' => $data['nhan_vien_id'],
                'ghi_chu' => $data['ghi_chu'] ?? null,
            ]);

            $soLuongHangCoSan = collect($sanPhams)
                ->filter(fn($sanPham) => ($sanPham['nguon_hang'] ?? '') === 'ton_kho')
                ->groupBy('san_pham_id')
                ->map(fn($items) => $items->sum(fn($sanPham) => (int) $sanPham['so_luong']));

            if ($soLuongHangCoSan->isNotEmpty()) {
                $tonKhoTheoSanPham = DB::table('kho_hang_san_phams')
                    ->select('san_pham_id', DB::raw('SUM(so_luong_ton) as ton_kho'))
                    ->whereIn('san_pham_id', $soLuongHangCoSan->keys()->values())
                    ->groupBy('san_pham_id')
                    ->pluck('ton_kho', 'san_pham_id');

                $soLuongDangGiuTheoSanPham = ChiTietDonHang::whereIn('san_pham_id', $soLuongHangCoSan->keys()->values())
                    ->whereHas('donHang', fn($query) => $query->where('trang_thai', 'cho_xu_ly'))
                    ->select('san_pham_id', DB::raw('SUM(so_luong) as so_luong_dang_giu'))
                    ->groupBy('san_pham_id')
                    ->pluck('so_luong_dang_giu', 'san_pham_id');

                foreach ($soLuongHangCoSan as $sanPhamId => $soLuongCanLay) {
                    $tonKho = (int) ($tonKhoTheoSanPham[$sanPhamId] ?? 0);
                    $soLuongDangGiu = (int) ($soLuongDangGiuTheoSanPham[$sanPhamId] ?? 0);
                    $coTheBan = max(0, $tonKho - $soLuongDangGiu);

                    if ($soLuongCanLay > $coTheBan) {
                        throw new \Exception('Số lượng hàng có sẵn không đủ để tạo đơn order từ tồn kho.');
                    }
                }
            }

            foreach ($sanPhams as $sanPham) {
                $laHangCoSan = ($sanPham['nguon_hang'] ?? '') === 'ton_kho';
                $soLuong = (int) $sanPham['so_luong'];

                ChiTietDonOrderHang::create([
                    'don_order_hang_id' => $donOrder->id,
                    'san_pham_id' => $sanPham['san_pham_id'],
                    'so_luong' => $soLuong,
                    'so_luong_da_ve' => $laHangCoSan ? $soLuong : 0,
                    'gia_ban_du_kien' => $sanPham['gia_ban_du_kien'] ?? 0,
                    'trang_thai' => $laHangCoSan ? 'hang_co_san' : 'dat_truoc',
                    'ghi_chu' => $sanPham['ghi_chu'] ?? null,
                ]);
            }

            $this->capNhatTrangThaiDon($donOrder);

            LichSuDonOrderHang::ghi(
                $donOrder->id,
                'tao_don_order',
                null,
                $donOrder->trang_thai,
                'Tạo đơn order từ khu đối tác',
                ['nguon_tao' => 'shop_auth_doi_tac']
            );

            return $donOrder->fresh(['chiTiets.sanPham', 'khachHang', 'nhanVien']);
        });
    }

    public function layDanhSach(int $perPage = 10, string $search = '', ?string $trangThai = null, int $nhanVienId = 0, ?string $quyen = null)
    {
        $query = DonOrderHang::with(['khachHang:id,ten,sdt', 'nhanVien:id,ten'])
            ->withCount('chiTiets')
            ->withSum('chiTiets as tong_so_luong', 'so_luong')
            ->orderBy('created_at', 'desc');

        $this->apDungPhamViDonOrder($query, $nhanVienId, $quyen);

        if ($trangThai) {
            $query->where('trang_thai', $trangThai);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ma_don_order', 'like', '%' . $search . '%')
                    ->orWhereHas('khachHang', function ($khachHang) use ($search) {
                        $khachHang->where('ten', 'like', '%' . $search . '%')
                            ->orWhere('sdt', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query->paginate($perPage);
    }

    public function layDanhSachDonBanTuOrder(array $boLoc, int $nhanVienId, ?string $quyen = null)
    {
        $query = $this->taoQueryDonBanTuOrder($nhanVienId, $quyen);

        $search = trim($boLoc['search'] ?? '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ma_don_hang', 'like', '%' . $search . '%')
                    ->orWhereHas('khachHang', function ($khachHang) use ($search) {
                        $khachHang->where('ten', 'like', '%' . $search . '%')
                            ->orWhere('sdt', 'like', '%' . $search . '%')
                            ->orWhere('ma_khach_hang', 'like', '%' . $search . '%');
                    })
                    ->orWhereExists(function ($donOrder) use ($search) {
                        $donOrder->select(DB::raw(1))
                            ->from('don_order_hangs')
                            ->whereColumn('don_order_hangs.don_hang_id', 'don_hangs.id')
                            ->where('don_order_hangs.ma_don_order', 'like', '%' . $search . '%');
                    });
            });
        }

        if (!empty($boLoc['trang_thai'])) {
            $query->where('trang_thai', $boLoc['trang_thai']);
        }

        if (!empty($boLoc['khach_hang'])) {
            $tuKhoaKhach = trim($boLoc['khach_hang']);
            $query->whereHas('khachHang', function ($khachHang) use ($tuKhoaKhach) {
                $khachHang->where('ten', 'like', '%' . $tuKhoaKhach . '%')
                    ->orWhere('ma_khach_hang', 'like', '%' . $tuKhoaKhach . '%')
                    ->orWhere('sdt', 'like', '%' . $tuKhoaKhach . '%');
            });
        }

        if (!empty($boLoc['san_pham'])) {
            $tuKhoaSanPham = trim($boLoc['san_pham']);
            $query->whereHas('chiTietDonHangs.sanPham', function ($sanPham) use ($tuKhoaSanPham) {
                $sanPham->where('ten', 'like', '%' . $tuKhoaSanPham . '%')
                    ->orWhere('ma_sku', 'like', '%' . $tuKhoaSanPham . '%')
                    ->orWhere('ma_vach', 'like', '%' . $tuKhoaSanPham . '%');
            });
        }

        $this->apDungLocNgayTao($query, $boLoc);

        $perPage = min((int) ($boLoc['per_page'] ?? 15), 100);
        $data = $query->orderByDesc('created_at')->paginate($perPage);
        $donHangIds = collect($data->items())->pluck('id');
        $donOrderTheoDonHang = DonOrderHang::whereIn('don_hang_id', $donHangIds)
            ->get(['id', 'don_hang_id', 'ma_don_order'])
            ->keyBy('don_hang_id');

        $data->setCollection(collect($data->items())->map(function (DonHang $donHang) use ($donOrderTheoDonHang) {
            $donOrder = $donOrderTheoDonHang->get($donHang->id);

            return [
                'id' => $donHang->id,
                'ma_don_hang' => $donHang->ma_don_hang,
                'don_order_id' => $donOrder?->id,
                'ma_don_order' => $donOrder?->ma_don_order,
                'la_don_tu_order' => true,
                'trang_thai' => $donHang->trang_thai,
                'ngay_dat' => $donHang->ngay_dat,
                'created_at' => $donHang->created_at,
                'tien_thanh_toan' => (float) ($donHang->tien_thanh_toan ?? 0),
                'da_thanh_toan' => (float) ($donHang->da_thanh_toan ?? 0),
                'con_phai_tra' => max(0, (float) ($donHang->tien_thanh_toan ?? 0) - (float) ($donHang->da_thanh_toan ?? 0)),
                'khach_hang' => $donHang->khachHang ? [
                    'id' => $donHang->khachHang->id,
                    'ten' => $donHang->khachHang->ten,
                    'sdt' => $donHang->khachHang->sdt,
                    'ma_khach_hang' => $donHang->khachHang->ma_khach_hang,
                ] : null,
            ];
        }));

        return $data;
    }

    public function thongKeDonBanTuOrder(array $boLoc, int $nhanVienId, ?string $quyen = null): array
    {
        $trangThais = ['cho_xu_ly', 'xuat_kho', 'dong_goi', 'van_chuyen', 'hoan_thanh', 'huy'];
        $ketQua = [];

        foreach ($trangThais as $trangThai) {
            $query = $this->taoQueryDonBanTuOrder($nhanVienId, $quyen)->where('trang_thai', $trangThai);
            $this->apDungLocNgayTao($query, $boLoc);
            $ketQua[$trangThai] = [
                'so_luong' => (int) $query->count(),
                'tong_tien' => (float) $query->sum('tien_thanh_toan'),
            ];
        }

        return $ketQua;
    }

    public function layChiTietDonBanTuOrder(int $donHangId, int $nhanVienId, ?string $quyen = null): ?array
    {
        $donOrderQuery = DonOrderHang::with(['nhanVien:id,ten', 'khachHang:id,ten,sdt,ma_khach_hang,email'])
            ->where('don_hang_id', $donHangId);
        $this->apDungPhamViDonOrder($donOrderQuery, $nhanVienId, $quyen);
        $donOrder = $donOrderQuery->first();

        if (!$donOrder) {
            return null;
        }

        $donHang = DonHang::with([
            'khachHang:id,ten,sdt,ma_khach_hang,email',
            'chiTietDonHangs.sanPham:id,ten,ma_sku,ma_vach,anh_san_pham',
            'lichSuDonHangs' => fn($query) => $query->orderByDesc('created_at'),
        ])->find($donHangId);

        if (!$donHang) {
            return null;
        }

        return [
            'don_hang' => $donHang,
            'don_order' => $donOrder,
        ];
    }

    private function taoQueryDonBanTuOrder(int $nhanVienId, ?string $quyen = null)
    {
        $query = DonHang::with(['khachHang:id,ten,sdt,ma_khach_hang'])
            ->whereExists(function ($query) use ($nhanVienId, $quyen) {
                $query->select(DB::raw(1))
                    ->from('don_order_hangs')
                    ->whereColumn('don_order_hangs.don_hang_id', 'don_hangs.id');

                if ($this->laNhanVienBanHang($quyen)) {
                    $query->where('don_order_hangs.nhan_vien_id', $nhanVienId);
                }
            });

        if ($quyen === 'thu_kho') {
            $query->whereHas('khachHang.nhanViens', function ($nhanVien) use ($nhanVienId) {
                $nhanVien->where('nhan_viens.id', $nhanVienId);
            });
        }

        return $query;
    }

    private function apDungLocNgayTao($query, array $boLoc): void
    {
        $ngayTao = $boLoc['ngay_tao'] ?? '';
        if ($ngayTao === '') {
            return;
        }

        $tz = 'Asia/Ho_Chi_Minh';
        match ($ngayTao) {
            'today' => $query->whereDate('created_at', now($tz)->toDateString()),
            'yesterday' => $query->whereDate('created_at', now($tz)->subDay()->toDateString()),
            '7days' => $query->where('created_at', '>=', now($tz)->subDays(7)->startOfDay()),
            '30days' => $query->where('created_at', '>=', now($tz)->subDays(30)->startOfDay()),
            'this_week' => $query->whereBetween('created_at', [now($tz)->startOfWeek(), now($tz)->endOfWeek()]),
            'this_month' => $query->whereBetween('created_at', [now($tz)->startOfMonth(), now($tz)->endOfMonth()]),
            'this_year' => $query->whereBetween('created_at', [now($tz)->startOfYear(), now($tz)->endOfYear()]),
            'custom' => $this->apDungLocNgayTaoTuyChinh($query, $boLoc),
            default => null,
        };
    }

    private function apDungLocNgayTaoTuyChinh($query, array $boLoc): void
    {
        $tuNgay = $boLoc['tu_ngay'] ?? null;
        $denNgay = $boLoc['den_ngay'] ?? null;

        if ($tuNgay && $denNgay) {
            $query->whereBetween('created_at', [$tuNgay, $denNgay . ' 23:59:59']);
        } elseif ($tuNgay) {
            $query->where('created_at', '>=', $tuNgay);
        }
    }

    public function layDanhSachHangOrderVe(int $perPage = 10, string $search = '', ?string $trangThai = null, int $nhanVienId = 0, ?string $quyen = null)
    {
        $trangThaisHangVe = ['ve_mot_phan', 'hang_da_ve', 'san_sang_tao_don_ban', 'hang_co_san'];

        $query = ChiTietDonOrderHang::with([
            'donOrderHang:id,ma_don_order,khach_hang_id,nhan_vien_id,trang_thai,created_at',
            'donOrderHang.khachHang:id,ten,sdt,ma_khach_hang',
            'sanPham:id,ten,ma_sku,ma_vach,anh_san_pham,gia_order,gia_ban_le',
        ])
            ->whereIn('trang_thai', $trangThaisHangVe)
            ->whereHas('donOrderHang', fn($q) => $this->apDungPhamViDonOrder($q, $nhanVienId, $quyen))
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if ($trangThai && in_array($trangThai, $trangThaisHangVe, true)) {
            $query->where('trang_thai', $trangThai);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('donOrderHang', function ($donOrder) use ($search) {
                    $donOrder->where('ma_don_order', 'like', '%' . $search . '%')
                        ->orWhereHas('khachHang', function ($khachHang) use ($search) {
                            $khachHang->where('ten', 'like', '%' . $search . '%')
                                ->orWhere('sdt', 'like', '%' . $search . '%')
                                ->orWhere('ma_khach_hang', 'like', '%' . $search . '%');
                        });
                })
                    ->orWhereHas('sanPham', function ($sanPham) use ($search) {
                        $sanPham->where('ten', 'like', '%' . $search . '%')
                            ->orWhere('ma_sku', 'like', '%' . $search . '%')
                            ->orWhere('ma_vach', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query->paginate($perPage);
    }

    public function thongKeTrangThai(int $nhanVienId, ?string $quyen = null): array
    {
        $trangThais = [
            'dat_truoc',
            've_mot_phan',
            'hang_da_ve',
            'san_sang_tao_don_ban',
            'da_chuyen_don_ban',
            'da_huy',
        ];

        $query = DonOrderHang::query();
        $this->apDungPhamViDonOrder($query, $nhanVienId, $quyen);

        $soLuong = $query->select('trang_thai', DB::raw('COUNT(*) as tong'))
            ->whereIn('trang_thai', $trangThais)
            ->groupBy('trang_thai')
            ->pluck('tong', 'trang_thai');

        return collect($trangThais)
            ->mapWithKeys(fn($trangThai) => [$trangThai => (int) ($soLuong[$trangThai] ?? 0)])
            ->all();
    }

    public function layChiTiet(int $id, int $nhanVienId, ?string $quyen = null): ?DonOrderHang
    {
        $query = DonOrderHang::with([
            'khachHang:id,ten,sdt,email',
            'nhanVien:id,ten',
            'donHang:id,ma_don_hang',
            'chiTiets.sanPham:id,ten,ma_sku,ma_vach,anh_san_pham,don_vi_tinh,gia_ban_le,gia_order,duoc_phep_order',
            'lichSus' => fn($query) => $query->orderBy('created_at', 'desc'),
        ]);
        $this->apDungPhamViDonOrder($query, $nhanVienId, $quyen);

        return $query->find($id);
    }

    public function huyDonOrder(int $donId, int $nhanVienId, ?string $quyen = null): DonOrderHang
    {
        return DB::transaction(function () use ($donId, $nhanVienId, $quyen) {
            $query = DonOrderHang::with('chiTiets')->lockForUpdate();
            $this->apDungPhamViDonOrder($query, $nhanVienId, $quyen);
            $donOrder = $query->findOrFail($donId);

            if ($donOrder->trang_thai !== 'dat_truoc') {
                throw new \Exception('Chỉ được hủy đơn order khi đang đặt trước.');
            }

            $trangThaiCu = $donOrder->trang_thai;

            foreach ($donOrder->chiTiets as $chiTiet) {
                $chiTiet->update(['trang_thai' => 'da_huy']);
            }

            $donOrder->trang_thai = 'da_huy';
            $donOrder->save();

            LichSuDonOrderHang::ghi($donOrder->id, 'huy_don_order', $trangThaiCu, 'da_huy', 'Hủy đơn order từ khu đối tác');

            return $donOrder->fresh(['chiTiets.sanPham', 'khachHang', 'nhanVien']);
        });
    }

    public function huyChiTietDonOrder(int $chiTietId, int $nhanVienId, ?string $quyen = null): DonOrderHang
    {
        return DB::transaction(function () use ($chiTietId, $nhanVienId, $quyen) {
            $chiTiet = ChiTietDonOrderHang::with('donOrderHang.khachHang.nhanViens:id')
                ->lockForUpdate()
                ->findOrFail($chiTietId);

            if (!$this->coQuyenTruyCapDonOrder($chiTiet->donOrderHang, $nhanVienId, $quyen)) {
                throw new \Exception('Bạn không có quyền hủy dòng order này.');
            }

            if ($chiTiet->trang_thai !== 'dat_truoc') {
                throw new \Exception('Chỉ được hủy dòng order khi đang đặt trước.');
            }

            $donOrder = DonOrderHang::with('chiTiets')->lockForUpdate()->findOrFail($chiTiet->don_order_hang_id);
            $trangThaiCu = $donOrder->trang_thai;

            $chiTiet->update(['trang_thai' => 'da_huy']);
            $donOrder->load('chiTiets');
            $this->capNhatTrangThaiDon($donOrder);

            LichSuDonOrderHang::ghi($donOrder->id, 'huy_chi_tiet_order', $trangThaiCu, $donOrder->trang_thai, 'Hủy dòng hàng order từ khu đối tác', [
                'chi_tiet_id' => $chiTiet->id,
                'san_pham_id' => $chiTiet->san_pham_id,
            ]);

            return $donOrder->fresh(['chiTiets.sanPham', 'khachHang', 'nhanVien']);
        });
    }

    public function capNhatTrangThaiDon(DonOrderHang $donOrder): DonOrderHang
    {
        $donOrder->load('chiTiets');
        $trangThaiConHieuLuc = $donOrder->chiTiets
            ->where('trang_thai', '!=', 'da_huy')
            ->pluck('trang_thai')
            ->unique()
            ->values();

        if ($donOrder->chiTiets->every(fn($chiTiet) => $chiTiet->trang_thai === 'da_chuyen_don_ban')) {
            $trangThaiMoi = 'da_chuyen_don_ban';
        } elseif ($donOrder->chiTiets->every(fn($chiTiet) => $chiTiet->trang_thai === 'da_huy')) {
            $trangThaiMoi = 'da_huy';
        } elseif ($trangThaiConHieuLuc->count() === 1 && $trangThaiConHieuLuc->first() === 'da_chuyen_don_ban') {
            $trangThaiMoi = 'da_chuyen_don_ban';
        } elseif ($trangThaiConHieuLuc->contains('ve_mot_phan')) {
            $trangThaiMoi = 've_mot_phan';
        } elseif ($trangThaiConHieuLuc->contains('dat_truoc')) {
            $trangThaiMoi = $trangThaiConHieuLuc->count() === 1 ? 'dat_truoc' : 've_mot_phan';
        } elseif ($trangThaiConHieuLuc->intersect(['hang_co_san', 'hang_da_ve', 'da_chuyen_don_ban'])->isNotEmpty()) {
            $trangThaiMoi = 'san_sang_tao_don_ban';
        } elseif ($trangThaiConHieuLuc->count() === 1) {
            $trangThaiMoi = $trangThaiConHieuLuc->first();
        } else {
            $trangThaiMoi = 'dat_truoc';
        }

        $donOrder->trang_thai = $trangThaiMoi;
        $donOrder->save();

        return $donOrder;
    }

    private function laNhanVienBanHang(?string $quyen): bool
    {
        return in_array($quyen, ['nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'], true);
    }

    private function apDungPhamViDonOrder($query, int $nhanVienId, ?string $quyen): void
    {
        if ($this->laNhanVienBanHang($quyen)) {
            $query->where('nhan_vien_id', $nhanVienId);
            return;
        }

        if ($quyen === 'thu_kho') {
            $query->whereHas('khachHang.nhanViens', function ($nhanVien) use ($nhanVienId) {
                $nhanVien->where('nhan_viens.id', $nhanVienId);
            });
        }
    }

    private function coQuyenTruyCapDonOrder(?DonOrderHang $donOrder, int $nhanVienId, ?string $quyen): bool
    {
        if (!$donOrder) {
            return false;
        }

        if ($this->laNhanVienBanHang($quyen)) {
            return (int) $donOrder->nhan_vien_id === $nhanVienId;
        }

        if ($quyen === 'thu_kho') {
            return $donOrder->khachHang?->nhanViens?->contains('id', $nhanVienId) ?? false;
        }

        return in_array($quyen, ['admin', 'quan_ly_order'], true);
    }
}
