<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Dùng Tailwind pagination
        Paginator::useTailwind();
        /**
         * Blade directive: lấy URL ảnh sản phẩm từ nội bộ hoặc shop-auth
         * Dùng trong view: @anhSanPham($tenAnh)
         */
        Blade::directive('anhSanPham', function ($tenAnh) {
            return "<?php
                \$_anh = {$tenAnh};
                \$_noiBo = base_path('../storage/app/public/uploads/sanpham/' . \$_anh);
                if (\$_anh && file_exists(\$_noiBo)):
                    echo asset('noi-bo-anh/' . \$_anh);
                else:
                    echo '';
                endif;
            ?>";
        });

        // Chia sẻ thông tin giỏ hàng với tất cả views
        view()->composer('*', function ($view) {
            $tongSoLuong = 0;
            if (session('khach_hang_id')) {
                $gioHang = \App\Models\GioHang::where('khach_hang_id', session('khach_hang_id'))->first();
                if ($gioHang) {
                    $tongSoLuong = \App\Models\GioHangChiTiet::where('gio_hang_id', $gioHang->id)->sum('so_luong');
                }
            }
            $view->with('tongSoLuongGioHang', $tongSoLuong);
        });
    }
}
