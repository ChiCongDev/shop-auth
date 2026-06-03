<?php

namespace App\Http\Service;

use App\Models\DoiTacGioOrderChiTiet;
use App\Models\DoiTacGioOrderHang;
use App\Models\SanPham;

class DoiTacGioOrderHangService
{
    public function layHoacTaoGioOrder(int $doiTacId): DoiTacGioOrderHang
    {
        return DoiTacGioOrderHang::firstOrCreate(['doi_tac_id' => $doiTacId]);
    }

    public function layGioOrder(int $doiTacId): DoiTacGioOrderHang
    {
        return $this->layHoacTaoGioOrder($doiTacId)
            ->load(['chiTiets.sanPham']);
    }

    public function themSanPham(int $doiTacId, int $sanPhamId, int $soLuong = 1): DoiTacGioOrderHang
    {
        $sanPham = SanPham::where('duoc_phep_order', true)->findOrFail($sanPhamId);
        $giaOrder = $this->layGiaOrderTamTinh($sanPham);
        if ($giaOrder <= 0) {
            throw new \RuntimeException('San pham order chua co gia order hop le.');
        }

        $gioOrder = $this->layHoacTaoGioOrder($doiTacId);

        $chiTiet = DoiTacGioOrderChiTiet::firstOrNew([
            'doi_tac_gio_order_hang_id' => $gioOrder->id,
            'san_pham_id' => $sanPham->id,
        ]);

        $chiTiet->so_luong = max(1, (int) ($chiTiet->exists ? $chiTiet->so_luong : 0) + $soLuong);
        $chiTiet->gia_order_tam_tinh = $giaOrder;
        $chiTiet->save();

        return $this->layGioOrder($doiTacId);
    }

    public function capNhatSoLuong(int $doiTacId, int $chiTietId, int $soLuong): DoiTacGioOrderHang
    {
        $gioOrder = $this->layHoacTaoGioOrder($doiTacId);

        DoiTacGioOrderChiTiet::where('doi_tac_gio_order_hang_id', $gioOrder->id)
            ->where('id', $chiTietId)
            ->update(['so_luong' => max(1, $soLuong)]);

        return $this->layGioOrder($doiTacId);
    }

    public function xoaSanPham(int $doiTacId, int $chiTietId): DoiTacGioOrderHang
    {
        $gioOrder = $this->layHoacTaoGioOrder($doiTacId);

        DoiTacGioOrderChiTiet::where('doi_tac_gio_order_hang_id', $gioOrder->id)
            ->where('id', $chiTietId)
            ->delete();

        return $this->layGioOrder($doiTacId);
    }

    public function xoaTatCa(int $doiTacId): DoiTacGioOrderHang
    {
        $gioOrder = $this->layHoacTaoGioOrder($doiTacId);
        $gioOrder->chiTiets()->delete();

        return $this->layGioOrder($doiTacId);
    }

    public function dinhDangGioOrder(DoiTacGioOrderHang $gioOrder): array
    {
        $items = $gioOrder->chiTiets
            ->filter(fn($item) => $item->sanPham && $item->sanPham->duoc_phep_order)
            ->map(function ($item) {
                $sanPham = $item->sanPham;
                $gia = $this->layGiaOrderTamTinh($sanPham);

                return [
                    'id' => $item->id,
                    'san_pham_id' => $sanPham->id,
                    'ten' => $sanPham->ten,
                    'ten_chung' => $sanPham->ten_chung,
                    'ma_chung' => $sanPham->ma_chung,
                    'ma_sku' => $sanPham->ma_sku,
                    'ma_vach' => $sanPham->ma_vach,
                    'anh_chinh' => $sanPham->anh_dau_tien ?: $this->layAnhChinh($sanPham->anh_san_pham),
                    'so_luong' => (int) $item->so_luong,
                    'gia_order_tam_tinh' => $gia,
                    'thanh_tien_tam_tinh' => $gia * (int) $item->so_luong,
                ];
            })
            ->values();

        return [
            'id' => $gioOrder->id,
            'items' => $items,
            'tong_so_luong' => $items->sum('so_luong'),
            'so_san_pham' => $items->count(),
            'tong_tien_tam_tinh' => $items->sum('thanh_tien_tam_tinh'),
        ];
    }

    private function layGiaOrderTamTinh(SanPham $sanPham): float
    {
        if ((float) $sanPham->gia_order > 0) {
            return (float) $sanPham->gia_order;
        }

        return 0;
    }

    private function layAnhChinh($anhSanPham): ?string
    {
        $anh = is_array($anhSanPham) ? $anhSanPham : json_decode($anhSanPham ?: '[]', true);

        return is_array($anh) ? ($anh[0] ?? null) : null;
    }
}
