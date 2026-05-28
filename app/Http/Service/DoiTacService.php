<?php

namespace App\Http\Service;

use App\Models\NhanVien;
use Illuminate\Support\Facades\Hash;

class DoiTacService
{
    public const QUYEN_DANG_NHAP_DOI_TAC = [
        'nhan_vien_ban_hang_cap_1',
        'nhan_vien_ban_hang_cap_2',
        'admin',
        'thu_kho',
        'quan_ly_order',
    ];

    public static function coQuyenDangNhapDoiTac(?string $quyen): bool
    {
        return in_array($quyen, self::QUYEN_DANG_NHAP_DOI_TAC, true);
    }

    public function dangNhapTaiKhoan(string $email, string $matKhau): bool
    {
        $nhanVien = NhanVien::where('email', $email)->first();

        if (!$nhanVien || !Hash::check($matKhau, $nhanVien->mat_khau)) {
            return false;
        }

        if (!self::coQuyenDangNhapDoiTac($nhanVien->quyen)) {
            return false;
        }

        session([
            'doi_tac_id' => $nhanVien->id,
            'doi_tac_ten' => $nhanVien->ten,
            'doi_tac_email' => $nhanVien->email,
            'doi_tac_quyen' => $nhanVien->quyen,
        ]);

        return true;
    }

    public function dangXuat(): void
    {
        session()->forget(['doi_tac_id', 'doi_tac_ten', 'doi_tac_email', 'doi_tac_quyen']);
    }
}
