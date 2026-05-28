<?php

namespace App\Http\Controllers;

use App\Http\Service\DoiTacService;
use Illuminate\Http\Request;

class DoiTacController extends Controller
{
    public function __construct(private DoiTacService $doiTacService) {}

    public function dangNhap()
    {
        if (session('doi_tac_id')) {
            return redirect('/doi-tac/order-hang/danh-sach');
        }

        return view('doiTac.dangNhap');
    }

    public function xuLyDangNhap(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required|min:6',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email khong dung dinh dang.',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu.',
            'mat_khau.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        $thanhCong = $this->doiTacService->dangNhapTaiKhoan($request->email, $request->mat_khau);

        if ($thanhCong) {
            return redirect()->intended('/doi-tac/order-hang/danh-sach')
            ->with('thongBao', 'Đăng nhập khu đối tác thành công.');
        }

        return back()->with('loi', 'Tai khoan hoac mat khau khong dung, hoac tai khoan khong co quyen doi tac.')
            ->withInput(['email' => $request->email]);
    }

    public function dangXuat()
    {
        $this->doiTacService->dangXuat();

        return redirect('/')->with('thongBao', 'Đã đăng xuất khu đối tác.');
    }
}
