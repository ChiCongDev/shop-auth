<?php

namespace Tests\Feature;

use App\Http\Service\DoiTacService;
use App\Models\NhanVien;
use Tests\TestCase;

class DoiTacKhoaDangNhapTest extends TestCase
{
    public function test_model_nhan_vien_co_co_khoa_dang_nhap_doi_tac(): void
    {
        $nhanVien = new NhanVien(['da_khoa_dang_nhap' => true]);

        $this->assertTrue($nhanVien->taiKhoanBiKhoa());
    }

    public function test_doi_tac_service_chan_tai_khoan_bi_khoa_va_chi_xoa_session_doi_tac(): void
    {
        $service = file_get_contents(app_path('Http/Service/DoiTacService.php'));

        $this->assertStringContainsString('$nhanVien->taiKhoanBiKhoa()', $service);
        $this->assertStringContainsString('public static function layNhanVienDoiTacHopLeTheoSession()', $service);
        $this->assertStringContainsString('public static function xoaSessionDoiTac(): void', $service);
        $this->assertStringContainsString("session()->forget(['doi_tac_id', 'doi_tac_ten', 'doi_tac_email', 'doi_tac_quyen'])", $service);
        $this->assertStringNotContainsString('session()->flush()', $service);
    }

    public function test_xoa_session_doi_tac_khong_lam_mat_session_khach_hang(): void
    {
        session([
            'khach_hang_id' => 10,
            'tenDangNhap' => 'Khach hang',
            'doi_tac_id' => 20,
            'doi_tac_ten' => 'Doi tac',
            'doi_tac_email' => 'doi-tac@example.test',
            'doi_tac_quyen' => 'admin',
        ]);

        DoiTacService::xoaSessionDoiTac();

        $this->assertSame(10, session('khach_hang_id'));
        $this->assertSame('Khach hang', session('tenDangNhap'));
        $this->assertNull(session('doi_tac_id'));
        $this->assertNull(session('doi_tac_quyen'));
    }

    public function test_luu_session_doi_tac_doc_du_lieu_tu_model_nhan_vien(): void
    {
        $nhanVien = new NhanVien([
            'ten' => 'Nhan vien',
            'email' => 'nhan-vien@example.test',
            'quyen' => 'thu_kho',
        ]);
        $nhanVien->id = 30;

        DoiTacService::luuSessionDoiTac($nhanVien);

        $this->assertSame(30, session('doi_tac_id'));
        $this->assertSame('Nhan vien', session('doi_tac_ten'));
        $this->assertSame('nhan-vien@example.test', session('doi_tac_email'));
        $this->assertSame('thu_kho', session('doi_tac_quyen'));
    }

    public function test_middleware_doi_tac_doc_lai_db_va_global_web_don_session_cu(): void
    {
        $middleware = file_get_contents(app_path('Http/Middleware/KiemTraDoiTacDangNhap.php'));
        $sessionSync = file_get_contents(app_path('Http/Middleware/DongBoSessionDoiTac.php'));
        $bootstrap = file_get_contents(base_path('bootstrap/app.php'));

        $this->assertStringContainsString('DoiTacService::layNhanVienDoiTacHopLeTheoSession()', $middleware);
        $this->assertStringContainsString('DoiTacService::xoaSessionDoiTac()', $middleware);
        $this->assertStringContainsString('DoiTacService::layNhanVienDoiTacHopLeTheoSession()', $sessionSync);
        $this->assertStringContainsString('DoiTacService::xoaSessionDoiTac()', $sessionSync);
        $this->assertStringContainsString("appendToGroup('web', \\App\\Http\\Middleware\\DongBoSessionDoiTac::class)", $bootstrap);
    }
}
