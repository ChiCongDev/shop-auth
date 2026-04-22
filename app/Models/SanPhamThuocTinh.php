<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model bảng san_pham_thuoc_tinhs (dùng chung với nội bộ)
 * Cột tên thuộc tính: ten_thuoc_tinh (không phải ten)
 */
class SanPhamThuocTinh extends Model
{
    protected $table = 'san_pham_thuoc_tinhs';

    protected $fillable = ['san_pham_id', 'ten_thuoc_tinh', 'gia_tri'];
}
