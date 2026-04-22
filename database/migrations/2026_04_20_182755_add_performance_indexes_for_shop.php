<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm index tối ưu hiệu suất cho trang bán hàng.
 *
 * Các query chính cần tối ưu:
 *   SELECT MIN(sp.id) FROM san_phams sp
 *   LEFT JOIN kho_hang_san_phams kh ON sp.id = kh.san_pham_id
 *   GROUP BY sp.ma_chung
 *   HAVING COALESCE(SUM(kh.so_luong_ton), 0) > 0
 *   WHERE sp.ten LIKE '...' / sp.loai_san_pham = '...' / sp.nhan_hieu = '...'
 */
return new class extends Migration
{
    public function up(): void
    {
        // === Bảng san_phams ===
        Schema::table('san_phams', function (Blueprint $table) {
            // Index cho GROUP BY ma_chung (dùng ở mọi query danh sách)
            if (!$this->indexExists('san_phams', 'idx_sp_ma_chung')) {
                $table->index('ma_chung', 'idx_sp_ma_chung');
            }
            // Index cho filter loai_san_pham
            if (!$this->indexExists('san_phams', 'idx_sp_loai')) {
                $table->index('loai_san_pham', 'idx_sp_loai');
            }
            // Index cho filter nhan_hieu
            if (!$this->indexExists('san_phams', 'idx_sp_nhan_hieu')) {
                $table->index('nhan_hieu', 'idx_sp_nhan_hieu');
            }
            // Composite index cho filter + group (loai + ma_chung)
            if (!$this->indexExists('san_phams', 'idx_sp_loai_ma_chung')) {
                $table->index(['loai_san_pham', 'ma_chung'], 'idx_sp_loai_ma_chung');
            }
            // Composite index cho filter nhan_hieu + ma_chung
            if (!$this->indexExists('san_phams', 'idx_sp_nhan_hieu_ma_chung')) {
                $table->index(['nhan_hieu', 'ma_chung'], 'idx_sp_nhan_hieu_ma_chung');
            }
            // Index cho ORDER BY created_at DESC (mới nhất)
            if (!$this->indexExists('san_phams', 'idx_sp_created_at')) {
                $table->index('created_at', 'idx_sp_created_at');
            }
        });

        // === Bảng kho_hang_san_phams ===
        Schema::table('kho_hang_san_phams', function (Blueprint $table) {
            // Index chính: JOIN kh.san_pham_id vào sp.id, rồi SUM(so_luong_ton)
            // Covering index: MySQL dùng luôn index để trả về so_luong_ton mà không đọc row
            if (!$this->indexExists('kho_hang_san_phams', 'idx_kho_sp_id_ton')) {
                $table->index(['san_pham_id', 'so_luong_ton'], 'idx_kho_sp_id_ton');
            }
        });
    }

    public function down(): void
    {
        Schema::table('san_phams', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_sp_ma_chung');
            $table->dropIndexIfExists('idx_sp_loai');
            $table->dropIndexIfExists('idx_sp_nhan_hieu');
            $table->dropIndexIfExists('idx_sp_loai_ma_chung');
            $table->dropIndexIfExists('idx_sp_nhan_hieu_ma_chung');
            $table->dropIndexIfExists('idx_sp_created_at');
        });

        Schema::table('kho_hang_san_phams', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_kho_sp_id_ton');
        });
    }

    /** Kiểm tra index đã tồn tại chưa để tránh lỗi khi chạy lại */
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = ?
              AND index_name = ?
        ", [$table, $indexName]);

        return ($result[0]->cnt ?? 0) > 0;
    }
};
