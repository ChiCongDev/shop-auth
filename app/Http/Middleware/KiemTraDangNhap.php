<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware kiểm tra đăng nhập — giống pattern hệ thống nội bộ
 * Chỉ áp dụng cho: giỏ hàng, thanh toán, tài khoản, đơn hàng
 * Trang chủ & sản phẩm thì KHÔNG cần đăng nhập
 */
class KiemTraDangNhap
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('khach_hang_id')) {
            return redirect('/dang-nhap')->with('thongBao', 'Vui lòng đăng nhập để tiếp tục.');
        }

        return $next($request);
    }
}
