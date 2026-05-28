<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChinhSachGia extends Model
{
    protected $table = 'chinh_sach_gias';

    protected $fillable = ['loai_gia', 'code', 'mo_ta', 'active'];
}
