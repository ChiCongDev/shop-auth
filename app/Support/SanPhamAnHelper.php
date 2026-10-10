<?php

namespace App\Support;

use App\Models\SanPham;
use Illuminate\Support\Facades\DB;

/**
 * Sản phẩm bị Sell (hệ thống nội bộ) ẨN thì web bán lẻ / web đối tác không bán nữa.
 *
 * Sell ghi bảng nhom_san_pham_ans (khóa theo ma_chung); shop-auth CHỈ ĐỌC bảng này.
 * Cùng quy tắc với App\Support\SanPhamAnHelper bên Sell — đổi quy tắc thì đổi cả hai.
 *
 * Đơn hàng / đơn order đã có không bị ảnh hưởng: chỉ lọc danh sách bán và chặn thêm vào giỏ / đặt hàng MỚI.
 */
class SanPhamAnHelper
{
    public const BANG = 'nhom_san_pham_ans';

    /**
     * Bỏ các nhóm đã ẩn khỏi truy vấn.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $cotMaChung  cột ma_chung của truy vấn ngoài, ghi đủ tên bảng/alias (vd 'sp.ma_chung')
     */
    public static function boNhomDaAn($query, string $cotMaChung)
    {
        return $query->whereNotExists(function ($q) use ($cotMaChung) {
            $q->select(DB::raw(1))
                ->from(self::BANG)
                ->whereColumn(self::BANG . '.ma_chung', $cotMaChung);
        });
    }

    public static function nhomDaAn(?string $maChung): bool
    {
        if ($maChung === null || $maChung === '') {
            return false;
        }

        return DB::table(self::BANG)->where('ma_chung', $maChung)->exists();
    }

    /**
     * Id các sản phẩm (trong danh sách id) đã bị ẩn; rỗng nếu không có.
     *
     * @return int[]
     */
    public static function idSanPhamDaAn(iterable $sanPhamIds): array
    {
        return self::sanPhamDaAn($sanPhamIds)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Tên các sản phẩm (trong danh sách id) đã bị ẩn; rỗng nếu không có.
     *
     * @return string[]
     */
    public static function tenSanPhamDaAn(iterable $sanPhamIds): array
    {
        return self::sanPhamDaAn($sanPhamIds)->map(fn ($sp) => $sp->ten ?: $sp->ma_sku)->all();
    }

    private static function sanPhamDaAn(iterable $sanPhamIds): \Illuminate\Support\Collection
    {
        $ids = collect($sanPhamIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return SanPham::query()
            ->whereIn('san_phams.id', $ids)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from(self::BANG)
                    ->whereColumn(self::BANG . '.ma_chung', 'san_phams.ma_chung');
            })
            ->orderBy('san_phams.id')
            ->get(['san_phams.id', 'san_phams.ten', 'san_phams.ma_sku'])
            ->toBase();
    }
}
