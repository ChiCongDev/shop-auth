<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoiTacGioOrderHang extends Model
{
    protected $table = 'doi_tac_gio_order_hangs';

    protected $fillable = ['doi_tac_id'];

    public function chiTiets()
    {
        return $this->hasMany(DoiTacGioOrderChiTiet::class, 'doi_tac_gio_order_hang_id');
    }
}
