<?php

namespace Tests\Feature;

use App\Http\Controllers\DoiTacOrderHangController;
use App\Http\Service\DoiTacGioOrderHangService;
use App\Http\Service\GioHangService;
use App\Http\Service\SanPhamService;
use App\Http\Service\ThanhToanService;
use App\Models\DoiTacGioOrderChiTiet;
use App\Models\DoiTacGioOrderHang;
use App\Models\DonHang;
use App\Models\GioHang;
use App\Models\GioHangChiTiet;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Sản phẩm Sell đã ẨN (bảng nhom_san_pham_ans, theo ma_chung) thì web bán lẻ và web đối tác
 * không bán nữa: không hiện, không thêm vào giỏ, không đặt được. Đơn đã có không bị ảnh hưởng.
 *
 * AN TOÀN: phpunit.xml của shop-auth KHÔNG đổi database cho test, còn .env trỏ vào MySQL dùng chung
 * với Sell. Lớp test này ép SQLite :memory: NGAY SAU khi nạp cấu hình (trước khi bất kỳ provider nào
 * chạy) và xóa mọi kết nối khác — lỡ chạm DB thật là lỗi ngay, không ghi được gì.
 */
class SanPhamAnShopAuthTest extends TestCase
{
    public function createApplication()
    {
        $app = require dirname(__DIR__, 2) . '/bootstrap/app.php';

        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $sqlite = $app['config']->get('database.connections.sqlite');
            $sqlite['url'] = null;
            $sqlite['database'] = ':memory:';
            $sqlite['foreign_key_constraints'] = false;
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections', ['sqlite' => $sqlite]);
        });

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Chốt chặn: không phải SQLite :memory: thì dừng ngay, trước khi tạo bảng hay ghi gì.
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->fail('Test này chỉ được chạy trên SQLite :memory:, không bao giờ trên MySQL thật.');
        }

        $this->taoBang();
    }

    // =====================================================================
    // WEB BÁN LẺ
    // =====================================================================

    public function test_danh_sach_va_tim_kiem_ban_le_khong_hien_san_pham_da_an(): void
    {
        $this->taoNhom('AN01', 'Ao khoac an', 5, 'Nike');
        $this->taoNhom('HIEN01', 'Ao khoac hien', 5, 'Adidas');
        $this->an('AN01');

        $service = app(SanPhamService::class);

        $this->assertSame(['HIEN01'], $service->layDanhSach(50)->getCollection()->pluck('ma_chung')->all());
        $this->assertSame(['HIEN01'], $service->layDanhSach(50, '', 'Áo')->getCollection()->pluck('ma_chung')->all());
        $this->assertSame(['HIEN01'], $service->laySanPhamNoiBat()->pluck('ma_chung')->all());
        $this->assertSame(['HIEN01'], $service->laySanPhamMoiNhat()->pluck('ma_chung')->all());
        $this->assertSame(['HIEN01'], $service->laySanPhamLienQuan('KHAC')->pluck('ma_chung')->all());
        $this->assertSame(['Adidas'], $service->layDanhSachNhanHieu(), 'Nhãn hiệu chỉ có sản phẩm đã ẩn thì không hiện');
    }

    /**
     * Tìm theo từ khóa dùng `LIKE ... ESCAPE '\\'` — cú pháp riêng MySQL, SQLite không chạy được
     * (có từ trước, không phải do thay đổi này). Nên ở đây chỉ dựng câu SQL (không chạy) và kiểm tra
     * bộ lọc "bỏ nhóm đã ẩn" nằm trong đúng truy vấn chọn sản phẩm.
     */
    public function test_tim_kiem_theo_tu_khoa_ban_le_co_bo_loc_nhom_da_an_trong_sql(): void
    {
        $service = app(SanPhamService::class);
        $boLoc = 'not exists (select 1 from "nhom_san_pham_ans" where "nhom_san_pham_ans"."ma_chung" = ';

        $sqlTimNhanh = collect(DB::pretend(fn () => $service->timKiemNhanh('Ao khoac')))->pluck('query');
        $this->assertStringContainsString($boLoc . '"sp"."ma_chung")', $sqlTimNhanh->first(), 'Ô tìm nhanh phải bỏ nhóm đã ẩn');
        $this->assertStringContainsString('LIKE ?', $sqlTimNhanh->first());

        $sqlDanhSach = collect(DB::pretend(fn () => $service->layDanhSach(12, 'Ao khoac')))->pluck('query');
        $this->assertStringContainsString($boLoc . '"sp_inner"."ma_chung")', $sqlDanhSach->first(), 'Trang danh sách khi tìm phải bỏ nhóm đã ẩn');
        $this->assertStringContainsString('LIKE ?', $sqlDanhSach->first());

        $sqlNhanHieu = collect(DB::pretend(fn () => $service->layDanhSachNhanHieu('', 'Ao khoac')))->pluck('query');
        $this->assertStringContainsString($boLoc . '"sp"."ma_chung")', $sqlNhanHieu->first());
    }

    public function test_hang_moi_ve_theo_phieu_nhap_khong_hien_san_pham_da_an(): void
    {
        $an = $this->taoNhom('PNAN', 'Giay an', 5, 'Nike');
        $hien = $this->taoNhom('PNHIEN', 'Giay hien', 5, 'Puma');
        $phieuNhapId = DB::table('phieu_nhaps')->insertGetId(['trang_thai' => 'da_nhap', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('phieu_nhap_chi_tiets')->insert([
            ['phieu_nhap_id' => $phieuNhapId, 'san_pham_id' => $an],
            ['phieu_nhap_id' => $phieuNhapId, 'san_pham_id' => $hien],
        ]);
        $this->an('PNAN');

        $service = app(SanPhamService::class);
        $this->assertSame(['PNHIEN'], $service->laySanPhamMoiNhat()->pluck('ma_chung')->all());
        $this->assertSame(['Puma'], $service->layDanhSachNhanHieu('', '', true));
    }

    public function test_trang_chi_tiet_san_pham_da_an_tra_null_san_pham_thuong_van_mo(): void
    {
        $this->taoNhom('CTAN', 'Tui an', 3);
        $this->taoNhom('CTHIEN', 'Tui hien', 3);
        $this->an('CTAN');

        $service = app(SanPhamService::class);
        $this->assertNull($service->layChiTietSanPham('CTAN'));
        $this->assertSame('CTHIEN', $service->layChiTietSanPham('CTHIEN')['ma_chung']);
    }

    public function test_khong_them_duoc_san_pham_da_an_vao_gio_san_pham_thuong_van_them_duoc(): void
    {
        $an = $this->taoNhom('GIOAN', 'Mu an', 3);
        $hien = $this->taoNhom('GIOHIEN', 'Mu hien', 3);
        $this->an('GIOAN');

        $gioHangService = app(GioHangService::class);

        try {
            $gioHangService->themVaoGio(1, $an, 1);
            $this->fail('Không được thêm sản phẩm đã ẩn vào giỏ');
        } catch (\Exception $e) {
            $this->assertSame('Sản phẩm đã ngừng bán.', $e->getMessage());
        }

        $this->assertSame(1, $gioHangService->themVaoGio(1, $hien, 1)['so_san_pham']);
    }

    public function test_dat_hang_bi_chan_neu_gio_co_san_pham_da_an_va_gio_giu_nguyen(): void
    {
        $an = $this->taoNhom('DATAN', 'Kinh an', 3);
        $hien = $this->taoNhom('DATHIEN', 'Kinh hien', 3);

        // Khách bỏ vào giỏ TRƯỚC khi sản phẩm bị ẩn
        $gioHangService = app(GioHangService::class);
        $gioHangService->themVaoGio(7, $an, 1);
        $gioHangService->themVaoGio(7, $hien, 1);
        $this->an('DATAN');

        try {
            app(ThanhToanService::class)->xuLyDatHang(7, $this->duLieuDatHang());
            $this->fail('Không được đặt hàng khi giỏ có sản phẩm đã ẩn');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Sản phẩm đã ngừng bán: Kinh an M', $e->getMessage());
        }

        $this->assertSame(0, DonHang::count());
        $this->assertSame(2, GioHangChiTiet::count(), 'Giỏ hàng giữ nguyên để khách tự xóa');

        // Khách xóa sản phẩm đã ẩn khỏi giỏ → đặt được như cũ
        $gioHang = GioHang::where('khach_hang_id', 7)->firstOrFail();
        $chiTietAn = GioHangChiTiet::where('gio_hang_id', $gioHang->id)->where('san_pham_id', $an)->firstOrFail();
        $gioHangService->xoa(7, $chiTietAn->id);

        $donHang = app(ThanhToanService::class)->xuLyDatHang(7, $this->duLieuDatHang());
        $this->assertStringStartsWith('WEB', $donHang->ma_don_hang);
        $this->assertSame([$hien], DB::table('chi_tiet_don_hangs')->where('don_hang_id', $donHang->id)->pluck('san_pham_id')->map(fn ($id) => (int) $id)->all());
    }

    // =====================================================================
    // WEB ĐỐI TÁC — order
    // =====================================================================

    public function test_cac_danh_sach_order_doi_tac_khong_hien_san_pham_da_an(): void
    {
        $an = $this->taoNhom('ODAN', 'Balo order an', 0, 'Nike', true);
        $hien = $this->taoNhom('ODHIEN', 'Balo order hien', 0, 'Nike', true);
        $this->an('ODAN');

        $controller = app(DoiTacOrderHangController::class);

        $timKiem = $controller->apiTimKiemSanPhamOrder(Request::create('/', 'GET', ['tu_khoa' => 'Balo']))->getData(true);
        $this->assertSame(['ODHIEN-M'], collect($timKiem['data'])->pluck('ma_sku')->all());

        $this->assertSame(404, $controller->apiLaySanPhamOrder($an)->getStatusCode());
        $this->assertSame(200, $controller->apiLaySanPhamOrder($hien)->getStatusCode());

        $nhom = $controller->apiDanhSachSanPhamDuocPhep(Request::create('/', 'GET', ['tu_khoa' => 'Balo']))->getData(true);
        $this->assertSame(['ODHIEN'], collect($nhom['data'])->pluck('ma_chung')->all());
        $this->assertSame(1, $nhom['pagination']['total']);

        $this->assertSame(404, $controller->apiChiTietSanPhamDuocPhep('ODAN')->getStatusCode());
        $chiTiet = $controller->apiChiTietSanPhamDuocPhep('ODHIEN')->getData(true);
        $this->assertTrue($chiTiet['success']);
        $this->assertNotContains('ODAN', collect($chiTiet['data']['san_pham_lien_quan'])->pluck('ma_chung')->all(), 'Sản phẩm liên quan không được gợi ý nhóm đã ẩn');
    }

    public function test_gio_order_doi_tac_khong_them_duoc_va_khong_tinh_san_pham_da_an(): void
    {
        $an = $this->taoNhom('GOAN', 'Dep order an', 0, 'Nike', true);
        $hien = $this->taoNhom('GOHIEN', 'Dep order hien', 0, 'Nike', true);

        $service = app(DoiTacGioOrderHangService::class);
        $service->themSanPham(9, $an, 2);      // bỏ vào giỏ trước khi bị ẩn
        $service->themSanPham(9, $hien, 1);
        $this->an('GOAN');

        try {
            $service->themSanPham(9, $an, 1);
            $this->fail('Không được thêm sản phẩm đã ẩn vào giỏ order');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('ngung ban', $e->getMessage());
        }

        $gio = $service->dinhDangGioOrder($service->layGioOrder(9));
        $this->assertSame([$hien], collect($gio['items'])->pluck('san_pham_id')->all());
        $this->assertSame(1, $gio['tong_so_luong']);
        $this->assertSame(2, DoiTacGioOrderChiTiet::count(), 'Dòng cũ vẫn nằm trong DB, chỉ không hiện / không tính');
    }

    public function test_khong_co_nhom_nao_bi_an_thi_moi_thu_nhu_cu(): void
    {
        $this->taoNhom('THUONG1', 'Ao thuong mot', 2, 'Nike');
        $this->taoNhom('THUONG2', 'Ao thuong hai', 2, 'Puma');

        $service = app(SanPhamService::class);
        $this->assertEqualsCanonicalizing(['THUONG1', 'THUONG2'], $service->layDanhSach(50)->getCollection()->pluck('ma_chung')->all());
        $this->assertEqualsCanonicalizing(['Nike', 'Puma'], $service->layDanhSachNhanHieu());
        $this->assertNotNull($service->layChiTietSanPham('THUONG1'));
    }

    public function test_ma_chung_long_nhau_khong_an_nham(): void
    {
        $this->taoNhom('AB', 'Ao AB', 2);
        $this->taoNhom('ABC', 'Ao ABC', 2);
        $this->an('AB');

        $this->assertSame(['ABC'], app(SanPhamService::class)->layDanhSach(50)->getCollection()->pluck('ma_chung')->all());
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function an(string $maChung): void
    {
        DB::table('nhom_san_pham_ans')->insert(['ma_chung' => $maChung, 'created_at' => now(), 'updated_at' => now()]);
    }

    /** Tạo nhóm 1 phiên bản (size M); trả về id sản phẩm. */
    private function taoNhom(string $maChung, string $tenChung, int $ton, ?string $nhanHieu = null, bool $duocOrder = false): int
    {
        $id = DB::table('san_phams')->insertGetId([
            'ten' => $tenChung . ' M',
            'ten_chung' => $tenChung,
            'ma_chung' => $maChung,
            'ma_sku' => $maChung . '-M',
            'ma_vach' => 'BAR-' . $maChung,
            'nhan_hieu' => $nhanHieu,
            'loai_san_pham' => 'Áo',
            'gia_ban_le' => 100000,
            'gia_ban_buon' => 90000,
            'gia_cong_tac_vien' => 80000,
            'gia_order' => 120000,
            'gia_nhap' => 50000,
            'don_vi_tinh' => 'chiec',
            'duoc_phep_order' => $duocOrder,
            'order_listed_at' => $duocOrder ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('kho_hang_san_phams')->insert([
            'san_pham_id' => $id, 'kho_hang_id' => 1, 'so_luong_ton' => $ton, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return $id;
    }

    private function duLieuDatHang(): array
    {
        return [
            'ten_nguoi_nhan' => 'Khach test',
            'sdt_nguoi_nhan' => '0900000000',
            'dia_chi' => '1 Duong test',
            'tinh_thanh' => 'Ha Noi',
            'hinh_thuc_thanh_toan' => 'cod',
        ];
    }

    private function taoBang(): void
    {
        Schema::create('san_phams', function (Blueprint $t) {
            $t->id();
            $t->string('ten')->nullable();
            $t->string('ten_chung')->nullable();
            $t->string('ma_chung');
            $t->string('ma_sku')->nullable();
            $t->string('ma_vach')->nullable();
            $t->string('nhan_hieu')->nullable();
            $t->string('loai_san_pham')->nullable();
            $t->text('anh_san_pham')->nullable();
            $t->decimal('gia_ban_le', 30, 8)->default(0);
            $t->decimal('gia_ban_buon', 30, 8)->default(0);
            $t->decimal('gia_cong_tac_vien', 30, 8)->default(0);
            $t->decimal('gia_order', 30, 8)->default(0);
            $t->decimal('gia_nhap', 30, 8)->default(0);
            $t->string('don_vi_tinh')->nullable();
            $t->string('vi_tri')->nullable();
            $t->boolean('duoc_phep_order')->default(false);
            $t->timestamp('order_listed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('kho_hang_san_phams', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('san_pham_id');
            $t->unsignedBigInteger('kho_hang_id')->default(1);
            $t->integer('so_luong_ton')->default(0);
            $t->timestamps();
        });
        Schema::create('san_pham_thuoc_tinhs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('san_pham_id');
            $t->string('ten_thuoc_tinh');
            $t->string('gia_tri');
            $t->timestamps();
        });
        Schema::create('san_pham_gias', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('san_pham_id');
            $t->unsignedBigInteger('chinh_sach_gia_id');
            $t->decimal('gia', 30, 8)->default(0);
            $t->timestamps();
        });
        Schema::create('nhom_san_pham_ans', function (Blueprint $t) {
            $t->id();
            $t->string('ma_chung')->unique();
            $t->unsignedBigInteger('nhan_vien_id')->nullable();
            $t->string('ly_do', 500)->nullable();
            $t->timestamps();
        });
        Schema::create('phieu_nhaps', function (Blueprint $t) {
            $t->id();
            $t->string('trang_thai')->nullable();
            $t->timestamps();
        });
        Schema::create('phieu_nhap_chi_tiets', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('phieu_nhap_id');
            $t->unsignedBigInteger('san_pham_id');
        });
        Schema::create('gio_hangs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('khach_hang_id');
            $t->timestamps();
        });
        Schema::create('gio_hang_chi_tiets', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('gio_hang_id');
            $t->unsignedBigInteger('san_pham_id');
            $t->integer('so_luong');
            $t->decimal('gia', 30, 8)->default(0);
            $t->timestamps();
        });
        Schema::create('doi_tac_gio_order_hangs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('doi_tac_id');
            $t->timestamps();
        });
        Schema::create('doi_tac_gio_order_chi_tiets', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('doi_tac_gio_order_hang_id');
            $t->unsignedBigInteger('san_pham_id');
            $t->integer('so_luong')->default(1);
            $t->decimal('gia_order_tam_tinh', 30, 8)->default(0);
            $t->timestamps();
        });
        Schema::create('don_hangs', function (Blueprint $t) {
            $t->id();
            $t->string('ma_don_hang');
            $t->string('trang_thai')->nullable();
            $t->decimal('tong_tien', 30, 8)->default(0);
            $t->decimal('chiet_khau', 30, 8)->default(0);
            $t->decimal('tien_giam', 30, 8)->default(0);
            $t->decimal('tien_thanh_toan', 30, 8)->default(0);
            $t->decimal('da_thanh_toan', 30, 8)->default(0);
            $t->text('ghi_chu')->nullable();
            $t->dateTime('ngay_dat')->nullable();
            $t->dateTime('ngay_giao_du_kien')->nullable();
            $t->unsignedBigInteger('khach_hang_id')->nullable();
            $t->unsignedBigInteger('nhan_vien_id')->nullable();
            $t->string('cach_thuc_nhan_hang')->nullable();
            $t->text('dia_chi_giao_hang')->nullable();
            $t->timestamps();
        });
        Schema::create('chi_tiet_don_hangs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('don_hang_id');
            $t->unsignedBigInteger('san_pham_id');
            $t->integer('so_luong');
            $t->decimal('gia_ban', 30, 8)->default(0);
            $t->decimal('chiet_khau', 30, 8)->default(0);
            $t->decimal('gia_nhap', 30, 8)->default(0);
            $t->decimal('thanh_tien', 30, 8)->default(0);
            $t->timestamps();
        });
    }
}
