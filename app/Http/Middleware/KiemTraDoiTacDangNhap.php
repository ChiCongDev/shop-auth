<?php

namespace App\Http\Middleware;

use App\Http\Service\DoiTacService;
use Closure;
use Illuminate\Http\Request;

class KiemTraDoiTacDangNhap
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('doi_tac_id')) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                'message' => 'Vui lòng đăng nhập khu đối tác để tiếp tục.',
                ], 401);
            }

        return redirect('/doi-tac/dang-nhap')->with('thongBao', 'Vui lòng đăng nhập khu đối tác để tiếp tục.');
        }

        if (!DoiTacService::coQuyenDangNhapDoiTac(session('doi_tac_quyen'))) {
            session()->forget(['doi_tac_id', 'doi_tac_ten', 'doi_tac_email', 'doi_tac_quyen']);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                'message' => 'Tài khoản không có quyền truy cập khu đối tác.',
                ], 403);
            }

        return redirect('/doi-tac/dang-nhap')->with('loi', 'Tài khoản không có quyền truy cập khu đối tác.');
        }

        return $next($request);
    }
}
