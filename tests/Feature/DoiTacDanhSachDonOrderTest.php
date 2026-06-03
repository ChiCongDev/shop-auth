<?php

namespace Tests\Feature;

use Tests\TestCase;

class DoiTacDanhSachDonOrderTest extends TestCase
{
    public function test_danh_sach_don_order_hien_bo_loc_va_o_thong_ke_trang_thai_order(): void
    {
        $view = file_get_contents(resource_path('views/doiTac/orderHang/danhSachDonOrder.blade.php'));

        $this->assertStringContainsString("'ma' => 'duyet_don_order'", $view);
        $this->assertStringContainsString("'ma' => 'bao_hang_ve_order'", $view);
        $this->assertStringContainsString("'ma' => 'bao_hang_ve_order_mot_phan'", $view);
        $this->assertStringContainsString('value="duyet_don_order"', $view);
        $this->assertStringContainsString('value="bao_hang_ve_order"', $view);
        $this->assertStringContainsString('value="bao_hang_ve_order_mot_phan"', $view);
    }

    public function test_tao_don_order_doi_tac_co_api_va_js_tu_chon_khach_mac_dinh(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $controller = file_get_contents(app_path('Http/Controllers/DoiTacOrderHangController.php'));
        $script = file_get_contents(resource_path('js/doiTac/orderHang/taoDonOrder.js'));
        $quickOrderScript = file_get_contents(resource_path('js/doiTac/orderHang/chiTietSanPhamDuocPhepOrder.js'));
        $cartOrderScript = file_get_contents(resource_path('js/doiTac/orderHang/gioOrderHang.js'));

        $this->assertStringContainsString('/khach-hang-mac-dinh', $routes);
        $this->assertStringContainsString('apiKhachHangMacDinh', $routes);
        $this->assertStringContainsString('function apiKhachHangMacDinh()', $controller);
        $this->assertStringContainsString('nhan_vien_ban_hang_cap_1', $controller);
        $this->assertStringContainsString('nhan_vien_ban_hang_cap_2', $controller);
        $this->assertStringContainsString('limit(2)', $controller);
        $this->assertStringContainsString('auto_select', $controller);
        $this->assertStringContainsString('tuChonKhachMacDinhNeuCo', $script);
        $this->assertStringContainsString('/api/doi-tac/order-hang/khach-hang-mac-dinh', $script);
        $this->assertStringContainsString('window.chonKhachHang(khachHang.id)', $script);
        $this->assertStringContainsString('tuChonKhachMacDinhTaoNhanhNeuCo', $quickOrderScript);
        $this->assertStringContainsString('/api/doi-tac/order-hang/khach-hang-mac-dinh', $quickOrderScript);
        $this->assertStringContainsString('window.chonKhachHangTaoNhanh(khachHang.id)', $quickOrderScript);
        $this->assertStringContainsString('tuChonKhachMacDinhGioOrderNeuCo', $cartOrderScript);
        $this->assertStringContainsString('/api/doi-tac/order-hang/khach-hang-mac-dinh', $cartOrderScript);
        $this->assertStringContainsString('window.chonKhachHangGioOrder(khachHang.id)', $cartOrderScript);
    }

    public function test_san_pham_order_doi_tac_chi_hien_thi_va_tao_bang_gia_order(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/DoiTacOrderHangController.php'));
        $cartService = file_get_contents(app_path('Http/Service/DoiTacGioOrderHangService.php'));
        $listScript = file_get_contents(resource_path('js/doiTac/orderHang/sanPhamDuocPhepOrder.js'));
        $detailScript = file_get_contents(resource_path('js/doiTac/orderHang/chiTietSanPhamDuocPhepOrder.js'));
        $createScript = file_get_contents(resource_path('js/doiTac/orderHang/taoDonOrder.js'));
        $cartScript = file_get_contents(resource_path('js/doiTac/orderHang/gioOrderHang.js'));

        $this->assertStringNotContainsString('item.gia_order || item.gia_ban_le', $listScript);
        $this->assertStringNotContainsString('item.gia_order || item.gia_ban_le', $detailScript);
        $this->assertStringNotContainsString('phienBanDangChon.gia_order || phienBanDangChon.gia_ban_le', $detailScript);
        $this->assertStringNotContainsString('data.gia_order_thap || data.gia_ban_le_thap', $detailScript);
        $this->assertStringContainsString('formatCurrency(item.gia_order)', $listScript);
        $this->assertStringContainsString('formatCurrency(phienBanDangChon.gia_order)', $detailScript);
        $this->assertStringContainsString('gia_ban_du_kien: Number(phienBanDangChon.gia_order || 0)', $detailScript);
        $this->assertStringContainsString('return Number(sp.gia_order || 0) > 0 ? Number(sp.gia_order) : 0', $createScript);
        $this->assertStringContainsString('San pham {$tenSanPham} chua co gia order hop le.', $controller);
        $this->assertStringContainsString('ValidationException::withMessages', $controller);
        $this->assertStringContainsString('San pham order chua co gia order hop le.', $cartService);
        $this->assertStringContainsString('return 0;', $cartService);
        $this->assertStringContainsString('gia_order_tam_tinh || 0) <= 0', $cartScript);
    }

    public function test_chi_tiet_san_pham_order_hien_toast_giua_man_hinh_va_an_gia_ban_le(): void
    {
        $view = file_get_contents(resource_path('views/doiTac/orderHang/chiTietSanPhamDuocPhepOrder.blade.php'));
        $script = file_get_contents(resource_path('js/doiTac/orderHang/chiTietSanPhamDuocPhepOrder.js'));

        $this->assertStringContainsString('fixed left-1/2 top-1/2 z-[100]', $view);
        $this->assertStringNotContainsString("document.getElementById('pb-gia-ban-le').textContent", $script);
        $this->assertStringContainsString("document.getElementById('pb-gia-ban-le')?.closest('div')?.classList.add('hidden')", $script);
    }

    public function test_chi_tiet_don_ban_tu_order_hien_ghi_chu_order(): void
    {
        $view = file_get_contents(resource_path('views/doiTac/orderHang/chiTietDonBanOrder.blade.php'));

        $this->assertStringContainsString('@if(filled($don_order->ghi_chu))', $view);
        $this->assertStringContainsString('id="boxGhiChuOrder"', $view);
        $this->assertStringContainsString('Ghi chu don order', $view);
        $this->assertStringContainsString('nl2br(e($don_order->ghi_chu))', $view);
        $this->assertSame(1, substr_count($view, 'id="boxGhiChuOrder"'));

        $infoSectionPosition = strpos($view, '2xl:grid-cols-[1.1fr_1.1fr_0.8fr]');
        $notePosition = strpos($view, 'id="boxGhiChuOrder"');
        $productSectionPosition = strpos($view, 'overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm');
        $historyPosition = strpos($view, '<article class="relative flex gap-4 pb-5 last:pb-0">');

        $this->assertNotFalse($infoSectionPosition);
        $this->assertNotFalse($notePosition);
        $this->assertNotFalse($productSectionPosition);
        $this->assertNotFalse($historyPosition);
        $this->assertLessThan($notePosition, $infoSectionPosition);
        $this->assertLessThan($productSectionPosition, $notePosition);
        $this->assertLessThan($historyPosition, $notePosition);
    }
}
