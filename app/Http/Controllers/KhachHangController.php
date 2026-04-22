<?php

namespace App\Http\Controllers;

use App\Http\Service\KhachHangService;
use Illuminate\Http\Request;

/**
 * Controller xử lý đăng ký / đăng nhập
 * Giống pattern NhanVienController của hệ thống nội bộ
 */
class KhachHangController extends Controller
{
    public function __construct(private KhachHangService $khachHangService) {}

    /** Hiện form đăng nhập */
    public function dangNhap()
    {
        if (session('khach_hang_id')) {
            return redirect('/');
        }
        return view('auth.dangNhap');
    }

    /** Xử lý đăng nhập — giống dangNhap() nội bộ */
    public function xuLyDangNhap(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'mat_khau' => 'required|min:6',
        ], [
            'email.required'    => 'Vui lòng nhập email.',
            'email.email'       => 'Email không đúng định dạng.',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu.',
            'mat_khau.min'      => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        $thanhCong = $this->khachHangService->dangNhapTaiKhoan(
            $request->email,
            $request->mat_khau
        );

        if ($thanhCong) {
            return redirect()->intended('/')->with('thongBao', 'Đăng nhập thành công! Chào mừng bạn trở lại 👋');
        }

        return back()->with('loi', 'Email hoặc mật khẩu không đúng.')->withInput(['email' => $request->email]);
    }

    /** Hiển thị form đăng ký */
    public function dangKy()
    {
        if (session('khach_hang_id')) {
            return redirect('/');
        }
        return view('auth.dangKy');
    }

    /** Xử lý đăng ký */
    public function xuLyDangKy(Request $request)
    {
        $request->validate([
            'ten'            => 'required|string|max:255',
            'sdt'            => 'required|string|max:20',
            'email'          => 'required|email',
            'mat_khau'       => 'required|min:6',
            'xac_nhan'       => 'required|same:mat_khau',
        ], [
            'ten.required'      => 'Vui lòng nhập họ tên.',
            'sdt.required'      => 'Vui lòng nhập số điện thoại.',
            'email.required'    => 'Vui lòng nhập email.',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu.',
            'mat_khau.min'      => 'Mật khẩu ít nhất 6 ký tự.',
            'xac_nhan.same'     => 'Mật khẩu xác nhận không khớp.',
        ]);

        try {
            $khachHang = $this->khachHangService->dangKyTaiKhoan(
                $request->ten,
                $request->sdt,
                $request->email,
                $request->mat_khau
            );

            // Tự động đăng nhập sau khi đăng ký
            session([
                'khach_hang_id' => $khachHang->id,
                'tenDangNhap'   => $khachHang->ten,
                'emailDangNhap' => $khachHang->email,
            ]);

            return redirect('/')->with('thongBao', 'Đăng ký thành công! Chào mừng ' . $khachHang->ten . ' 🎉');

        } catch (\Exception $e) {
            return back()->with('loi', $e->getMessage())->withInput($request->except('mat_khau', 'xac_nhan'));
        }
    }

    /** Đăng xuất — giống dangXuat() nội bộ */
    public function dangXuat()
    {
        $this->khachHangService->dangXuat();
        return redirect('/')->with('thongBao', 'Bạn đã đăng xuất thành công.');
    }
}
