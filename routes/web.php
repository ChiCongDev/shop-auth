<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SanPhamController;
use App\Http\Controllers\KhachHangController;
use App\Http\Controllers\GioHangController;
use App\Http\Controllers\ThanhToanController;
use App\Http\Controllers\TaiKhoanController;

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
