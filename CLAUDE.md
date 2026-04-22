# CLAUDE.md — Hướng Dẫn Phát Triển Shop Auth

## Tổng Quan

**Shop Auth** là web bán lẻ thời trang chính hãng, chạy trên port 9000.  
Dùng chung MySQL `sellPro2026-demo` với hệ thống quản lý nội bộ (port 8080).

---

## Quy Tắc Quan Trọng

> ⚠️ **Không xóa file cũ**. Chỉ thêm file mới hoặc sửa file hiện có khi cần thiết.

---

## Kiến Trúc: Controller → Service → Model

Giống hệt hệ thống nội bộ — DEV quen với project nội bộ sẽ không cần học lại.

```
Request → Route → Controller → Service → Model → Database
                      ↓
                   Blade View
```

### Ví dụ Controller
```php
class SanPhamController extends Controller
{
    public function __construct(private SanPhamService $sanPhamService) {}

    public function hienThiDanhSach(Request $request)
    {
        $danhSach = $this->sanPhamService->layDanhSach(12);
        return view('sanPham.danhSach', compact('danhSach'));
    }
}
```

---

## Cấu Trúc Thư Mục

```
app/
├── Http/
│   ├── Controllers/        # Nhận request, trả view hoặc JSON
│   ├── Service/            # Logic nghiệp vụ
│   └── Middleware/
│       └── KiemTraDangNhap.php
└── Models/                 # Map bảng DB

resources/
├── views/
│   ├── layouts/app.blade.php   # Layout chính
│   ├── auth/                   # Đăng nhập, đăng ký
│   ├── sanPham/                # Trang sản phẩm
│   ├── gioHang/                # Giỏ hàng
│   ├── thanhToan/              # Thanh toán
│   └── donHang/                # Lịch sử đơn hàng
└── js/
    ├── trangChu.js
    ├── sanPham/danhSach.js
    ├── sanPham/chiTiet.js
    ├── gioHang/index.js
    └── thanhToan/index.js
```

---

## Session Keys (Auth)

```php
session('khach_hang_id')    // ID khách hàng đang đăng nhập
session('tenDangNhap')       // Tên hiển thị
session('emailDangNhap')     // Email
session('sdtDangNhap')       // Số điện thoại
```

---

## Bảng DB Chung Với Nội Bộ (Chỉ Đọc / Ghi Cẩn Thận)

| Bảng | Dùng để | Ghi từ web bán lẻ |
|------|---------|-------------------|
| `san_phams` | Hiển thị sản phẩm | ❌ Không |
| `kho_hang_san_phams` | Kiểm tra tồn kho | ❌ Không |
| `khach_hangs` | Tài khoản khách | ✅ Tạo mới khi đăng ký |
| `don_hangs` | Đơn hàng | ✅ Tạo khi đặt hàng |
| `chi_tiet_don_hangs` | Chi tiết đơn | ✅ Tạo khi đặt hàng |
| `dia_chis` | Địa chỉ khách | ✅ Tạo/sửa |

## Bảng Riêng Của Web Bán Lẻ

| Bảng | Mục đích |
|------|---------|
| `khach_hang_mat_khau` | Mật khẩu đăng nhập web |
| `gio_hangs` | Giỏ hàng |
| `gio_hang_chi_tiets` | Chi tiết giỏ hàng |

---

## Middleware

```php
Route::middleware('kiemTraDangNhap')->group(function () {
    // Chỉ áp dụng cho: giỏ hàng, thanh toán, đơn hàng
});
```

---

## Cách Thêm Tính Năng Mới

1. Tạo **Service**: `app/Http/Service/TenService.php`
2. Tạo **Controller**: `app/Http/Controllers/TenController.php`
3. Thêm **Route** vào `routes/web.php`
4. Tạo **View** tại `resources/views/ten/index.blade.php`
5. Tạo **JS** tại `resources/js/ten/index.js`
6. Đăng ký JS trong `vite.config.js`

---

## Quy Ước Đặt Tên

- **Method**: camelCase tiếng Việt — `hienThiDanhSach`, `xuLyDatHang`, `layThongTin`
- **View**: camelCase — `danhSach.blade.php`, `chiTiet.blade.php`
- **Route name**: camelCase — `danhSachSanPham`, `datHang`
- **Variable**: camelCase tiếng Việt — `$donHang`, `$khachHang`, `$gioHang`

---

## Ảnh Sản Phẩm

Ảnh được lưu trong hệ thống nội bộ. Trong Blade:
```blade
{{ asset('storage/uploads/sanpham/' . $anh) }}
```

---

## Thanh Toán

Ba hình thức: `cod`, `chuyen_khoan`, `cong_no`

Khi đặt hàng → `DonHang::taoMaDonHang()` tạo mã `WEByydddd0001`  
Đơn được ghi vào bảng `don_hangs` → Nội bộ thấy ngay ✅

---

## Khởi Động Server

```bash
# Web bán lẻ (port 9000)
php artisan serve --port=9000

# Build CSS/JS
npm run dev     # Development (hot reload)
npm run build   # Production
```
