<?php

namespace App\Http\Controllers;

use App\Http\Service\ThanhToanService;
use App\Http\Service\KhachHangService;
use App\Models\KhachHang;
use Illuminate\Http\Request;

/**
 * Controller thanh toán & đơn hàng — YÊU CẦU ĐĂNG NHẬP
 */
class ThanhToanController extends Controller
{
    public function __construct(
        private ThanhToanService $thanhToanService,
        private KhachHangService $khachHangService,
    ) {}

    /** Trang thanh toán */
    public function hienThi()
    {
        $khachHang = KhachHang::with(['diaChis'])->find(session('khach_hang_id'));
        return view('thanhToan.index', compact('khachHang'));
    }

    /** Xử lý đặt hàng */
    public function xuLyDatHang(Request $request)
    {
        $request->validate([
            'ten_nguoi_nhan' => 'required|string',
            'sdt_nguoi_nhan' => 'required|string',
            'dia_chi'        => 'required|string',
            'tinh_thanh'     => 'required|string',
            'hinh_thuc_thanh_toan' => 'required|in:cod,chuyen_khoan,cong_no',
        ], [
            'ten_nguoi_nhan.required' => 'Vui lòng nhập tên người nhận.',
            'sdt_nguoi_nhan.required' => 'Vui lòng nhập số điện thoại.',
            'dia_chi.required'        => 'Vui lòng nhập địa chỉ.',
            'tinh_thanh.required'     => 'Vui lòng chọn tỉnh/thành.',
            'hinh_thuc_thanh_toan.required' => 'Vui lòng chọn hình thức thanh toán.',
        ]);

        try {
            $donHang = $this->thanhToanService->xuLyDatHang(
                session('khach_hang_id'),
                $request->all()
            );

            return redirect('/dat-hang-thanh-cong/' . $donHang->ma_don_hang)
                ->with('thongBao', 'Đặt hàng thành công! Mã đơn: ' . $donHang->ma_don_hang);

        } catch (\Exception $e) {
            return back()->with('loi', $e->getMessage());
        }
    }

    /** Trang xác nhận đơn hàng thành công */
    public function thanhCong(string $maDonHang)
    {
        return view('thanhToan.thanhCong', compact('maDonHang'));
    }

    /** Danh sách đơn hàng của khách */
    public function danhSachDonHang()
    {
        $donHangs = $this->thanhToanService->layDonHangCuaKhach(session('khach_hang_id'));
        return view('donHang.danhSach', compact('donHangs'));
    }

    /** Chi tiết đơn hàng */
    public function chiTietDonHang(int $id)
    {
        try {
            $donHang = $this->thanhToanService->layChiTietDonHang($id, session('khach_hang_id'));
            return view('donHang.chiTiet', compact('donHang'));
        } catch (\Exception $e) {
            return redirect('/don-hang')->with('loi', 'Không tìm thấy đơn hàng.');
        }
    }
}
