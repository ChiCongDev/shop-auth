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

    public static function xoaSessionDoiTac(): void
    {
        session()->forget(['doi_tac_id', 'doi_tac_ten', 'doi_tac_email', 'doi_tac_quyen']);
    }

    public static function layNhanVienDoiTacHopLeTheoSession(): ?NhanVien
    {
        $doiTacId = session('doi_tac_id');

        if (!$doiTacId) {
            return null;
        }

        $nhanVien = NhanVien::find($doiTacId);

        if (
            !$nhanVien
            || $nhanVien->taiKhoanBiKhoa()
            || !self::coQuyenDangNhapDoiTac($nhanVien->quyen)
        ) {
            return null;
        }

        self::luuSessionDoiTac($nhanVien);

        return $nhanVien;
    }

    public static function luuSessionDoiTac(NhanVien $nhanVien): void
    {
        $sessionData = [
            'doi_tac_id' => $nhanVien->id,
            'doi_tac_ten' => $nhanVien->ten,
            'doi_tac_email' => $nhanVien->email,
            'doi_tac_quyen' => $nhanVien->quyen,
        ];

        if (
            (string) session('doi_tac_id') === (string) $nhanVien->id
            && session('doi_tac_ten') === $nhanVien->ten
            && session('doi_tac_email') === $nhanVien->email
            && session('doi_tac_quyen') === $nhanVien->quyen
        ) {
            return;
        }

        session($sessionData);
    }

    public function dangNhapTaiKhoan(string $email, string $matKhau): bool
    {
        $nhanVien = NhanVien::where('email', $email)->first();

        if (!$nhanVien || $nhanVien->taiKhoanBiKhoa() || !Hash::check($matKhau, $nhanVien->mat_khau)) {
            return false;
        }

        if (!self::coQuyenDangNhapDoiTac($nhanVien->quyen)) {
            return false;
        }

        self::luuSessionDoiTac($nhanVien);

        return true;
    }

    public function dangXuat(): void
    {
        self::xoaSessionDoiTac();
    }
}
