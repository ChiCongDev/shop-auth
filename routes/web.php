<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SanPhamController;
use App\Http\Controllers\KhachHangController;
use App\Http\Controllers\GioHangController;
use App\Http\Controllers\ThanhToanController;
use App\Http\Controllers\TaiKhoanController;
use App\Http\Controllers\DoiTacController;
use App\Http\Controllers\DoiTacDonHangController;
use App\Http\Controllers\DoiTacOrderHangController;

// ============================================================
// 🌐 ROUTE CÔNG KHAI — Không cần đăng nhập
// Ai cũng có thể xem: trang chủ, sản phẩm
// ============================================================

/** Trang chủ */
Route::get('/', [SanPhamController::class, 'trangChu'])->name('trangChu');

/** Danh sách sản phẩm */
Route::get('/san-pham', [SanPhamController::class, 'hienThiDanhSach'])->name('danhSachSanPham');

/** Chi tiết sản phẩm */
Route::get('/san-pham/{maChung}', [SanPhamController::class, 'hienThiChiTiet'])->name('chiTietSanPham');

/** Hàng mới về */
Route::get('/hang-moi-ve', [SanPhamController::class, 'hangMoiVe'])->name('hangMoiVe');

/** API tìm kiếm sản phẩm (AJAX search header) */
Route::get('/api/tim-kiem-san-pham', [SanPhamController::class, 'apiTimKiem'])->name('apiTimKiemSanPham');

/** API danh sách sản phẩm (AJAX) */
Route::get('/api/san-pham', [SanPhamController::class, 'apiDanhSach'])->name('apiDanhSachSanPham');

/** API lấy số lượng giỏ hàng (hiển thị trên header, không cần login) */
Route::get('/api/gio-hang/thong-tin', [GioHangController::class, 'apiLayGioHang'])->name('apiLayGioHang');

// ============================================================
// 🔐 ROUTE AUTH — Đăng ký / Đăng nhập / Đăng xuất
// ============================================================

/** Trang đăng nhập */
Route::get('/dang-nhap', [KhachHangController::class, 'dangNhap'])->name('dangNhap');
Route::post('/dang-nhap', [KhachHangController::class, 'xuLyDangNhap'])->name('xuLyDangNhap');

/** Trang đăng ký */
Route::get('/dang-ky', [KhachHangController::class, 'dangKy'])->name('dangKy');
Route::post('/dang-ky', [KhachHangController::class, 'xuLyDangKy'])->name('xuLyDangKy');

/** Đăng xuất */
Route::get('/dang-xuat', [KhachHangController::class, 'dangXuat'])
    ->middleware('kiemTraDangNhap')
    ->name('dangXuat');

// ============================================================
// KHU DOI TAC - NHAN VIEN BAN HANG
// ============================================================

if (config('services.doi_tac_order.enabled', false)) {
    Route::get('/doi-tac/dang-nhap', [DoiTacController::class, 'dangNhap'])->name('doiTac.dangNhap');
    Route::post('/doi-tac/dang-nhap', [DoiTacController::class, 'xuLyDangNhap'])->name('doiTac.xuLyDangNhap');
    Route::get('/doi-tac/dang-xuat', [DoiTacController::class, 'dangXuat'])
        ->middleware('kiemTraDoiTac')
        ->name('doiTac.dangXuat');

    Route::get('/doi-tac/order-hang/san-pham-duoc-phep', [DoiTacOrderHangController::class, 'hienThiSanPhamDuocPhep'])
        ->name('doiTac.orderHang.sanPhamDuocPhep');
    Route::get('/doi-tac/order-hang/san-pham-duoc-phep/{maChung}', [DoiTacOrderHangController::class, 'hienThiChiTietSanPhamDuocPhep'])
        ->name('doiTac.orderHang.sanPhamDuocPhepChiTiet');
    Route::get('/api/doi-tac/order-hang/san-pham-duoc-phep', [DoiTacOrderHangController::class, 'apiDanhSachSanPhamDuocPhep']);
    Route::get('/api/doi-tac/order-hang/san-pham-duoc-phep/{maChung}', [DoiTacOrderHangController::class, 'apiChiTietSanPhamDuocPhep']);

    Route::prefix('doi-tac/don-hang')->middleware('kiemTraDoiTac')->group(function () {
        Route::get('/', [DoiTacDonHangController::class, 'hienThiDanhSach'])->name('doiTac.donHang.danhSach');
        Route::get('/tao', [DoiTacDonHangController::class, 'hienThiTaoDonHang'])->name('doiTac.donHang.tao');
        Route::get('/khach-tra-hang', [DoiTacDonHangController::class, 'hienThiDanhSachPhieuTraHang'])
            ->name('doiTac.donHang.khachTraHang');
        Route::get('/{id}/doi-tra', [DoiTacDonHangController::class, 'hienThiDoiTraHang'])
            ->where('id', '[0-9]+')
            ->name('doiTac.donHang.doiTraHang');
        Route::get('/{id}', [DoiTacDonHangController::class, 'hienThiChiTiet'])
            ->where('id', '[0-9]+')
            ->name('doiTac.donHang.chiTiet');
    });

    Route::prefix('doi-tac/order-hang')->middleware('kiemTraDoiTac')->group(function () {
        Route::get('/tao', [DoiTacOrderHangController::class, 'hienThiTaoDonOrder'])->name('doiTac.orderHang.tao');
        Route::get('/danh-sach', [DoiTacOrderHangController::class, 'hienThiDanhSach'])->name('doiTac.orderHang.danhSach');
        Route::get('/hang-order-ve', [DoiTacOrderHangController::class, 'hienThiHangOrderVe'])->name('doiTac.orderHang.hangOrderVe');
        Route::get('/gio-order', [DoiTacOrderHangController::class, 'hienThiGioOrder'])->name('doiTac.orderHang.gioOrder');
        Route::get('/danh-sach-khach-hang', [DoiTacOrderHangController::class, 'hienThiDanhSachKhachHangOrder'])->name('doiTac.orderHang.danhSachKhachHang');
        Route::get('/don-ban/{id}', [DoiTacOrderHangController::class, 'hienThiChiTietDonBan'])
            ->where('id', '[0-9]+')
            ->name('doiTac.orderHang.donBanChiTiet');
        Route::get('/don-ban/{id}/doi-tra', [DoiTacOrderHangController::class, 'hienThiDoiTraHang'])
            ->where('id', '[0-9]+')
            ->name('doiTac.orderHang.doiTraHang');
        Route::get('/khach-tra-hang-order', [DoiTacOrderHangController::class, 'hienThiDanhSachPhieuTraHang'])
            ->name('doiTac.orderHang.khachTraHangOrder');
        Route::get('/chi-tiet/{id}', [DoiTacOrderHangController::class, 'hienThiChiTiet'])
            ->where('id', '[0-9]+')
            ->name('doiTac.orderHang.chiTiet');
    });

    Route::prefix('api/doi-tac/order-hang')->middleware('kiemTraDoiTac')->group(function () {
        Route::get('/khach-hang-mac-dinh', [DoiTacOrderHangController::class, 'apiKhachHangMacDinh']);
        Route::get('/tim-khach-hang', [DoiTacOrderHangController::class, 'apiTimKiemKhachHang']);
        Route::get('/tim-san-pham-order', [DoiTacOrderHangController::class, 'apiTimKiemSanPhamOrder']);
        Route::get('/san-pham-order/{id}', [DoiTacOrderHangController::class, 'apiLaySanPhamOrder'])->where('id', '[0-9]+');
        Route::post('/tao', [DoiTacOrderHangController::class, 'apiTaoDonOrder']);
        Route::get('/gio-order', [DoiTacOrderHangController::class, 'apiLayGioOrder']);
        Route::post('/gio-order/them', [DoiTacOrderHangController::class, 'apiThemGioOrder']);
        Route::put('/gio-order/cap-nhat', [DoiTacOrderHangController::class, 'apiCapNhatGioOrder']);
        Route::delete('/gio-order/xoa', [DoiTacOrderHangController::class, 'apiXoaGioOrder']);
        Route::delete('/gio-order/xoa-tat-ca', [DoiTacOrderHangController::class, 'apiXoaTatCaGioOrder']);
        Route::get('/danh-sach-khach-hang', [DoiTacOrderHangController::class, 'apiLayDanhSachKhachHangOrder']);
        Route::get('/danh-sach', [DoiTacOrderHangController::class, 'apiLayDanhSach']);
        Route::get('/danh-sach-don-order', [DoiTacOrderHangController::class, 'apiLayDanhSachDonBanTuOrder']);
        Route::get('/don-ban/{id}', [DoiTacOrderHangController::class, 'apiLayChiTietDonBanTuOrder'])->where('id', '[0-9]+');
        Route::get('/don-ban/{id}/lay-hang-trong-kho', [DoiTacOrderHangController::class, 'apiKiemTraLayHangTrongKho'])->where('id', '[0-9]+');
        Route::get('/phieu-tra-hang/danh-sach', [DoiTacOrderHangController::class, 'apiLayDanhSachPhieuTraHang']);
        Route::get('/phieu-tra-hang/so-luong-da-tra/{donHangId}', [DoiTacOrderHangController::class, 'apiLaySoLuongDaTra'])
            ->where('donHangId', '[0-9]+');
        Route::get('/phieu-tra-hang/{id}', [DoiTacOrderHangController::class, 'apiLayChiTietPhieuTraHang'])
            ->where('id', '[0-9]+');
        Route::post('/phieu-tra-hang/tao', [DoiTacOrderHangController::class, 'apiTaoPhieuTraHang']);
        Route::post('/phieu-tra-hang/{id}/hoan-tien', [DoiTacOrderHangController::class, 'apiHoanTienPhieuTraHang'])
            ->where('id', '[0-9]+');
        Route::post('/don-ban/{id}/{hanhDong}', [DoiTacOrderHangController::class, 'apiThaoTacDonBanTuOrder'])
            ->where('id', '[0-9]+')
            ->whereIn('hanhDong', ['duyet', 'bao-hang-ve', 'lay-hang-trong-kho', 'xuat-kho', 'dong-goi', 'van-chuyen', 'tu-van-chuyen-ntq', 'hoan-thanh']);
        Route::get('/hang-order-ve', [DoiTacOrderHangController::class, 'apiLayHangOrderVe']);
        Route::get('/thong-ke-trang-thai', [DoiTacOrderHangController::class, 'apiThongKeTrangThai']);
        Route::post('/chi-tiet/{id}/huy', [DoiTacOrderHangController::class, 'apiHuyChiTietDonOrder'])->where('id', '[0-9]+');
        Route::get('/{id}', [DoiTacOrderHangController::class, 'apiLayChiTiet'])->where('id', '[0-9]+');
        Route::post('/{id}/chuyen-don-ban', [DoiTacOrderHangController::class, 'apiChuyenDonBan'])->where('id', '[0-9]+');
        Route::post('/{id}/huy', [DoiTacOrderHangController::class, 'apiHuyDonOrder'])->where('id', '[0-9]+');
    });

    Route::prefix('api/doi-tac/don-hang')->middleware('kiemTraDoiTac')->group(function () {
        Route::get('/danh-sach', [DoiTacDonHangController::class, 'apiLayDanhSach']);
        Route::get('/khach-hang-mac-dinh', [DoiTacDonHangController::class, 'apiKhachHangMacDinh']);
        Route::get('/tim-khach-hang', [DoiTacDonHangController::class, 'apiTimKiemKhachHang']);
        Route::get('/khach-hang/{khachHangId}/nhan-vien-duoc-gan', [DoiTacDonHangController::class, 'apiLayNhanVienDuocGan'])
            ->where('khachHangId', '[0-9]+');
        Route::get('/tim-san-pham', [DoiTacDonHangController::class, 'apiTimKiemSanPham']);
        Route::get('/lay-gia-nhap', [DoiTacDonHangController::class, 'apiLayGiaNhap']);
        Route::post('/lay-gia-theo-chinh-sach', [DoiTacDonHangController::class, 'apiLayGiaTheoChinhSach']);
        Route::post('/tao', [DoiTacDonHangController::class, 'apiTaoDonHang']);
        Route::get('/phieu-tra-hang/danh-sach', [DoiTacDonHangController::class, 'apiLayDanhSachPhieuTraHang']);
        Route::get('/phieu-tra-hang/so-luong-da-tra/{donHangId}', [DoiTacDonHangController::class, 'apiLaySoLuongDaTra'])
            ->where('donHangId', '[0-9]+');
        Route::get('/phieu-tra-hang/{id}', [DoiTacDonHangController::class, 'apiLayChiTietPhieuTraHang'])
            ->where('id', '[0-9]+');
        Route::post('/phieu-tra-hang/tao', [DoiTacDonHangController::class, 'apiTaoPhieuTraHang']);
        Route::post('/phieu-tra-hang/{id}/hoan-tien', [DoiTacDonHangController::class, 'apiHoanTienPhieuTraHang'])
            ->where('id', '[0-9]+');
        Route::get('/{id}', [DoiTacDonHangController::class, 'apiLayChiTiet'])
            ->where('id', '[0-9]+');
        Route::post('/{id}/{hanhDong}', [DoiTacDonHangController::class, 'apiThaoTac'])
            ->where('id', '[0-9]+')
            ->whereIn('hanhDong', ['xuat-kho', 'dong-goi', 'van-chuyen', 'tu-van-chuyen-ntq', 'hoan-thanh']);
    });
}

// ============================================================
// 🔒 ROUTE RIÊNG TƯ — Yêu cầu đăng nhập
// Giỏ hàng, thanh toán, tài khoản, đơn hàng
// ============================================================

Route::middleware('kiemTraDangNhap')->group(function () {

    /** Giỏ hàng */
    Route::get('/gio-hang', [GioHangController::class, 'hienThi'])->name('gioHang');

    /** API Giỏ hàng (AJAX) */
    Route::post('/api/gio-hang/them', [GioHangController::class, 'apiThemVao'])->name('apiThemVaoGio');
    Route::put('/api/gio-hang/cap-nhat', [GioHangController::class, 'apiCapNhat'])->name('apiCapNhatGio');
    Route::delete('/api/gio-hang/xoa', [GioHangController::class, 'apiXoa'])->name('apiXoaGio');

    /** Thanh toán */
    Route::get('/thanh-toan', [ThanhToanController::class, 'hienThi'])->name('thanhToan');
    Route::post('/dat-hang', [ThanhToanController::class, 'xuLyDatHang'])->name('datHang');
    Route::get('/dat-hang-thanh-cong/{maDonHang}', [ThanhToanController::class, 'thanhCong'])->name('datHangThanhCong');

    /** Đơn hàng của tôi */
    Route::get('/don-hang', [ThanhToanController::class, 'danhSachDonHang'])->name('danhSachDonHang');
    Route::get('/don-hang/{id}', [ThanhToanController::class, 'chiTietDonHang'])->name('chiTietDonHang');

    /** Tài khoản cá nhân */
    Route::get('/taiKhoan', [TaiKhoanController::class, 'hienThi'])->name('taiKhoan');
    Route::put('/taiKhoan/cap-nhat', [TaiKhoanController::class, 'capNhatThongTin'])->name('capNhatTaiKhoan');
    Route::post('/taiKhoan/doi-mat-khau', [TaiKhoanController::class, 'doiMatKhau'])->name('doiMatKhau');
    Route::post('/taiKhoan/dia-chi', [TaiKhoanController::class, 'themDiaChi'])->name('themDiaChi');
    Route::delete('/taiKhoan/dia-chi/{id}/xoa', [TaiKhoanController::class, 'xoaDiaChi'])->name('xoaDiaChi');

});
