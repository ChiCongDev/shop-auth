<?php

namespace Tests\Feature;

use App\Http\Service\DoiTacDonBanNoiBoService;
use App\Http\Controllers\DoiTacOrderHangController;
use App\Http\Service\DoiTacGioOrderHangService;
use App\Http\Service\DoiTacOrderHangService;
use App\Http\Service\DoiTacPhieuTraHangNoiBoService;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Mockery;
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

    public function test_tao_don_thuong_an_chiet_khau_va_hen_giao_an_toan(): void
    {
        $view = file_get_contents(resource_path('views/doiTac/donHang/taoDonHang.blade.php'));
        $script = file_get_contents(resource_path('js/doiTac/donHang/taoDonHang.js'));

        $this->assertStringContainsString('id="chiet-khau-don-hang" type="hidden" value="0"', $view);
        $this->assertStringContainsString('id="hen-giao-don-hang" type="hidden" value=""', $view);
        $this->assertStringContainsString('label:has(+ #hen-giao-don-hang)', $view);
        $this->assertStringNotContainsString('type="date"', $view);
        $this->assertStringContainsString("document.getElementById('chiet-khau-don-hang')?.value || 0", $script);
        $this->assertStringContainsString("document.getElementById('hen-giao-don-hang')?.value || null", $script);
    }

    public function test_nav_desktop_an_menu_don_hang_thuong_nhung_giu_route_va_dropdown(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $routes = file_get_contents(base_path('routes/web.php'));

        $this->assertStringContainsString('data-desktop-normal-order-nav', $layout);
        $this->assertStringContainsString('class="group relative hidden shrink-0"', $layout);
        $this->assertStringContainsString("Route::prefix('doi-tac/don-hang')", $routes);
        $this->assertStringContainsString("Route::get('/tao', [DoiTacDonHangController::class, 'hienThiTaoDonHang'])", $routes);
        $this->assertStringContainsString("Route::get('/', [DoiTacDonHangController::class, 'hienThiDanhSach'])", $routes);
        $this->assertStringContainsString('group/normal-order relative', $layout);
        $this->assertStringContainsString('<span>Đơn hàng thường</span>', $layout);
        $this->assertStringContainsString('href="/doi-tac/don-hang/tao" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50"', $layout);
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

    public function test_chi_tiet_don_thuong_hien_ghi_chu_don_hang_va_an_block_vtp(): void
    {
        $view = file_get_contents(resource_path('views/doiTac/donHang/chiTiet.blade.php'));

        $this->assertStringContainsString('$ghiChuDonHang = trim(preg_replace', $view);
        $this->assertStringContainsString("/\\s*\\[VTP:[^\\]]+\\]/", $view);
        $this->assertStringContainsString("id=\"boxGhiChuDonHang\"", $view);
        $this->assertStringContainsString("Ghi chú đơn hàng", $view);
        $this->assertStringContainsString("nl2br(e(\$ghiChuDonHang))", $view);
        $this->assertSame(1, substr_count($view, 'id="boxGhiChuDonHang"'));

        $infoSectionPosition = strpos($view, '<section class="sell-info-row">');
        $notePosition = strpos($view, 'id="boxGhiChuDonHang"');
        $productSectionPosition = strpos($view, '<h2 class="sell-product-title">Thông tin sản phẩm</h2>');

        $this->assertNotFalse($infoSectionPosition);
        $this->assertNotFalse($notePosition);
        $this->assertNotFalse($productSectionPosition);
        $this->assertLessThan($notePosition, $infoSectionPosition);
        $this->assertLessThan($productSectionPosition, $notePosition);
    }

    public function test_thao_tac_lay_hang_trong_kho_gui_san_phams_sang_sell(): void
    {
        config([
            'services.sell_internal.enabled' => true,
            'services.sell_internal.url' => 'https://sell.test',
            'services.sell_internal.token' => 'test-token',
            'services.sell_internal.timeout' => 5,
        ]);

        Http::fake([
            'https://sell.test/api/noi-bo/don-order/123/lay-hang-trong-kho' => Http::response([
                'thanh_cong' => true,
                'thong_bao' => 'Da lay hang trong kho cho don order',
                'du_lieu' => ['so_luong_cap_nhat' => 2],
            ]),
        ]);

        $ketQua = app(DoiTacDonBanNoiBoService::class)->guiThaoTac(123, 456, 'lay-hang-trong-kho', [
            'san_phams' => [
                ['san_pham_id' => 789, 'so_luong' => 2],
            ],
        ]);

        $this->assertTrue($ketQua['success']);
        $this->assertSame('Da lay hang trong kho cho don order', $ketQua['message']);
        $this->assertSame(2, $ketQua['data']['so_luong_cap_nhat']);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request->url() === 'https://sell.test/api/noi-bo/don-order/123/lay-hang-trong-kho'
                && $request['nhan_vien_id'] === 456
                && $request['nguon'] === 'shop_auth_doi_tac'
                && $request['san_phams'][0]['san_pham_id'] === 789
                && $request['san_phams'][0]['so_luong'] === 2;
        });
    }

    public function test_controller_lay_hang_trong_kho_chuyen_payload_sau_khi_kiem_tra_quyen_don(): void
    {
        session([
            'doi_tac_id' => 456,
            'doi_tac_quyen' => 'thu_kho',
        ]);

        $payload = [
            'san_phams' => [
                ['san_pham_id' => 789, 'so_luong' => 2],
            ],
        ];

        $orderService = Mockery::mock(DoiTacOrderHangService::class);
        $noiBoService = Mockery::mock(DoiTacDonBanNoiBoService::class);

        $orderService->shouldReceive('layChiTietDonBanTuOrder')
            ->once()
            ->with(123, 456, 'thu_kho')
            ->andReturn(['don_hang' => ['id' => 123]]);

        $noiBoService->shouldReceive('guiThaoTac')
            ->once()
            ->with(123, 456, 'lay-hang-trong-kho', $payload)
            ->andReturn([
                'success' => true,
                'message' => 'Da lay hang trong kho cho don order',
                'data' => ['so_luong_cap_nhat' => 2],
                'status' => 200,
            ]);

        $controller = new DoiTacOrderHangController(
            $orderService,
            $noiBoService,
            Mockery::mock(DoiTacGioOrderHangService::class),
            Mockery::mock(DoiTacPhieuTraHangNoiBoService::class)
        );

        $request = Request::create('/api/doi-tac/order-hang/don-ban/123/lay-hang-trong-kho', 'POST', $payload);
        $response = $controller->apiThaoTacDonBanTuOrder($request, 123, 'lay-hang-trong-kho');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'message' => 'Da lay hang trong kho cho don order',
            'data' => ['so_luong_cap_nhat' => 2],
        ], $response->getData(true));
    }

    public function test_controller_thao_tac_thuong_khong_chuyen_san_phams_du_request_co_gui_len(): void
    {
        session([
            'doi_tac_id' => 456,
            'doi_tac_quyen' => 'thu_kho',
        ]);

        $orderService = Mockery::mock(DoiTacOrderHangService::class);
        $noiBoService = Mockery::mock(DoiTacDonBanNoiBoService::class);

        $orderService->shouldReceive('layChiTietDonBanTuOrder')
            ->once()
            ->with(123, 456, 'thu_kho')
            ->andReturn(['don_hang' => ['id' => 123]]);

        $noiBoService->shouldReceive('guiThaoTac')
            ->once()
            ->with(123, 456, 'duyet', [])
            ->andReturn([
                'success' => true,
                'message' => 'Da duyet don order',
                'status' => 200,
            ]);

        $controller = new DoiTacOrderHangController(
            $orderService,
            $noiBoService,
            Mockery::mock(DoiTacGioOrderHangService::class),
            Mockery::mock(DoiTacPhieuTraHangNoiBoService::class)
        );

        $request = Request::create('/api/doi-tac/order-hang/don-ban/123/duyet', 'POST', [
            'san_phams' => [
                ['san_pham_id' => 789, 'so_luong' => 2],
            ],
        ]);
        $response = $controller->apiThaoTacDonBanTuOrder($request, 123, 'duyet');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_thao_tac_don_ban_thuong_khong_gui_san_phams_sang_sell(): void
    {
        config([
            'services.sell_internal.enabled' => true,
            'services.sell_internal.url' => 'https://sell.test',
            'services.sell_internal.token' => 'test-token',
            'services.sell_internal.timeout' => 5,
        ]);

        Http::fake([
            'https://sell.test/api/noi-bo/don-order/123/duyet' => Http::response([
                'thanh_cong' => true,
                'thong_bao' => 'Da duyet don order',
            ]),
        ]);

        $ketQua = app(DoiTacDonBanNoiBoService::class)->guiThaoTac(123, 456, 'duyet');

        $this->assertTrue($ketQua['success']);
        $this->assertSame('Da duyet don order', $ketQua['message']);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request->url() === 'https://sell.test/api/noi-bo/don-order/123/duyet'
                && $request['nhan_vien_id'] === 456
                && $request['nguon'] === 'shop_auth_doi_tac'
                && !array_key_exists('san_phams', $request->data());
        });
    }

    public function test_payload_khong_the_ghi_de_nhan_vien_va_nguon_noi_bo(): void
    {
        config([
            'services.sell_internal.enabled' => true,
            'services.sell_internal.url' => 'https://sell.test',
            'services.sell_internal.token' => 'test-token',
            'services.sell_internal.timeout' => 5,
        ]);

        Http::fake([
            'https://sell.test/api/noi-bo/don-order/123/lay-hang-trong-kho' => Http::response([
                'thanh_cong' => true,
                'thong_bao' => 'Da lay hang trong kho cho don order',
            ]),
        ]);

        app(DoiTacDonBanNoiBoService::class)->guiThaoTac(123, 456, 'lay-hang-trong-kho', [
            'nhan_vien_id' => 999,
            'nguon' => 'khong_hop_le',
            'san_phams' => [
                ['san_pham_id' => 789, 'so_luong' => 2],
            ],
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sell.test/api/noi-bo/don-order/123/lay-hang-trong-kho'
                && $request['nhan_vien_id'] === 456
                && $request['nguon'] === 'shop_auth_doi_tac'
                && $request['san_phams'][0]['san_pham_id'] === 789;
        });
    }

    public function test_thao_tac_lay_hang_trong_kho_tra_loi_loi_validate_tu_sell(): void
    {
        config([
            'services.sell_internal.enabled' => true,
            'services.sell_internal.url' => 'https://sell.test',
            'services.sell_internal.token' => 'test-token',
            'services.sell_internal.timeout' => 5,
        ]);

        Http::fake([
            'https://sell.test/api/noi-bo/don-order/123/lay-hang-trong-kho' => Http::response([
                'thanh_cong' => false,
                'thong_bao' => 'Du lieu khong hop le',
                'errors' => [
                    'san_phams' => ['Vui long chon san pham'],
                ],
            ], 422),
        ]);

        $ketQua = app(DoiTacDonBanNoiBoService::class)->guiThaoTac(123, 456, 'lay-hang-trong-kho', [
            'san_phams' => [
                ['san_pham_id' => 789, 'so_luong' => 2],
            ],
        ]);

        $this->assertFalse($ketQua['success']);
        $this->assertSame('Du lieu khong hop le', $ketQua['message']);
        $this->assertSame(422, $ketQua['status']);
    }

    public function test_kiem_tra_lay_hang_trong_kho_doc_dung_du_lieu_items_tu_sell(): void
    {
        config([
            'services.sell_internal.enabled' => true,
            'services.sell_internal.url' => 'https://sell.test',
            'services.sell_internal.token' => 'test-token',
            'services.sell_internal.timeout' => 5,
        ]);

        Http::fake([
            'https://sell.test/api/noi-bo/don-order/123/lay-hang-trong-kho*' => Http::response([
                'thanh_cong' => true,
                'du_lieu' => [
                    'items' => [
                        [
                            'san_pham_id' => 789,
                            'so_luong_con_thieu' => 3,
                            'co_the_ban' => 2,
                            'co_the_lay' => 2,
                        ],
                    ],
                    'trang_thai_hang_order' => 've_mot_phan',
                ],
            ]),
        ]);

        $ketQua = app(DoiTacDonBanNoiBoService::class)->kiemTraLayHangTrongKho(123, 456);

        $this->assertTrue($ketQua['success']);
        $this->assertSame('ve_mot_phan', $ketQua['data']['trang_thai_hang_order']);
        $this->assertSame(789, $ketQua['data']['items'][0]['san_pham_id']);
        $this->assertSame(2, $ketQua['data']['items'][0]['co_the_lay']);

        Http::assertSent(function ($request) {
            return $request->method() === 'GET'
                && $request->url() === 'https://sell.test/api/noi-bo/don-order/123/lay-hang-trong-kho?nhan_vien_id=456&nguon=shop_auth_doi_tac';
        });
    }

    public function test_thao_tac_khong_hop_le_khong_goi_sang_sell(): void
    {
        config([
            'services.sell_internal.enabled' => true,
            'services.sell_internal.url' => 'https://sell.test',
            'services.sell_internal.token' => 'test-token',
        ]);

        Http::fake();

        $ketQua = app(DoiTacDonBanNoiBoService::class)->guiThaoTac(123, 456, 'khong-hop-le');

        $this->assertFalse($ketQua['success']);
        $this->assertSame(422, $ketQua['status']);
        Http::assertNothingSent();
    }
}
