<?php

namespace App\Http\Controllers;

use App\Http\Service\KhachHangService;
use App\Models\KhachHang;
use App\Models\KhachHangMatKhau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controller quản lý thông tin cá nhân khách hàng
 */
class TaiKhoanController extends Controller
{
    public function __construct(private KhachHangService $khachHangService) {}

    /** Trang tài khoản — YÊU CẦU ĐĂNG NHẬP */
    public function hienThi()
    {
        $khachHang = KhachHang::with(['diaChis', 'donHangs' => function ($q) {
            $q->latest()->limit(5);
        }])->find(session('khach_hang_id'));

        return view('taiKhoan.index', compact('khachHang'));
    }

    /** Cập nhật thông tin cá nhân */
    public function capNhatThongTin(Request $request)
    {
        $request->validate([
            'ten' => 'required|string|max:255',
            'sdt' => 'required|string|max:20',
        ], [
            'ten.required' => 'Vui lòng nhập họ tên.',
            'sdt.required' => 'Vui lòng nhập số điện thoại.',
        ]);

        try {
            $khachHang = $this->khachHangService->capNhatThongTin(session('khach_hang_id'), [
                'ten' => $request->ten,
                'sdt' => $request->sdt,
            ]);

            // Cập nhật session
            session(['tenDangNhap' => $khachHang->ten]);

            return back()->with('thongBao', 'Cập nhật thông tin thành công!');
        } catch (\Exception $e) {
            return back()->with('loi', 'Cập nhật thất bại: ' . $e->getMessage());
        }
    }

    /** Đổi mật khẩu */
    public function doiMatKhau(Request $request)
    {
        $request->validate([
            'mat_khau_cu'   => 'required',
            'mat_khau_moi'  => 'required|min:6',
            'xac_nhan'      => 'required|same:mat_khau_moi',
        ], [
            'mat_khau_cu.required'  => 'Vui lòng nhập mật khẩu cũ.',
            'mat_khau_moi.required' => 'Vui lòng nhập mật khẩu mới.',
            'mat_khau_moi.min'      => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'xac_nhan.same'         => 'Xác nhận mật khẩu không khớp.',
        ]);

        $matKhauRecord = KhachHangMatKhau::where('khach_hang_id', session('khach_hang_id'))->first();

        if (!$matKhauRecord || !Hash::check($request->mat_khau_cu, $matKhauRecord->mat_khau)) {
            return back()->with('loi_matkhau', 'Mật khẩu cũ không đúng.');
        }

        $matKhauRecord->update(['mat_khau' => Hash::make($request->mat_khau_moi)]);

        return back()->with('thongBao', 'Đổi mật khẩu thành công!');
    }

    /** Thêm địa chỉ mới */
    public function themDiaChi(Request $request)
    {
        $request->validate([
            'dia_chi'    => 'required|string',
            'tinh_thanh' => 'required|string',
        ]);

        try {
            $this->khachHangService->themDiaChi(session('khach_hang_id'), [
                'dia_chi_cu_the' => $request->dia_chi,
                'phuong_xa'      => $request->phuong_xa,
                'khu_vuc'        => $request->tinh_thanh,
            ]);

            return back()->with('thongBao', 'Thêm địa chỉ thành công!');
        } catch (\Exception $e) {
            return back()->with('loi', 'Thêm địa chỉ thất bại.');
        }
    }

    /** Xóa địa chỉ */
    public function xoaDiaChi(int $id)
    {
        \App\Models\DiaChi::where('id', $id)
            ->where('khach_hang_id', session('khach_hang_id'))
            ->delete();

        return back()->with('thongBao', 'Đã xóa địa chỉ.');
    }
}
