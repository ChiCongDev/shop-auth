<?php

namespace App\Http\Service;

use App\Models\KhachHang;
use App\Models\KhachHangMatKhau;
use App\Models\DiaChi;
use Illuminate\Support\Facades\Hash;

/**
 * Service xử lý đăng ký / đăng nhập khách hàng
 * Giống pattern NhanVienService của hệ thống nội bộ
 */
class KhachHangService
{
    /**
     * Đăng nhập tài khoản — giống dangNhapTaiKhoan() của nội bộ
     */
    public function dangNhapTaiKhoan(string $email, string $matKhau): bool
    {
        $khachHang = KhachHang::where('email', $email)->first();

        if (!$khachHang) return false;

        $matKhauRecord = KhachHangMatKhau::where('khach_hang_id', $khachHang->id)->first();

        if (!$matKhauRecord || !Hash::check($matKhau, $matKhauRecord->mat_khau)) {
            return false;
        }

        // Lưu session — giống hệt pattern nội bộ
        session([
            'khach_hang_id'    => $khachHang->id,
            'tenDangNhap'      => $khachHang->ten,
            'emailDangNhap'    => $khachHang->email,
            'sdtDangNhap'      => $khachHang->sdt,
        ]);

        return true;
    }

    /**
     * Đăng ký tài khoản mới
     */
    public function dangKyTaiKhoan(string $ten, string $sdt, string $email, string $matKhau): KhachHang
    {
        // Kiểm tra email đã tồn tại chưa
        if (KhachHang::where('email', $email)->exists()) {
            throw new \Exception('Email này đã được sử dụng.');
        }

        // Kiểm tra SĐT đã tồn tại chưa
        if (KhachHang::where('sdt', $sdt)->exists()) {
            throw new \Exception('Số điện thoại này đã được sử dụng.');
        }

        // Tạo khách hàng trong bảng chung
        // nhan_vien_id = 0 → quy ước: khách tự đăng ký qua web bán lẻ (không qua nhân viên)
        $khachHang = KhachHang::create([
            'ten'            => $ten,
            'sdt'            => $sdt,
            'email'          => $email,
            'ma_khach_hang'  => 'KH' . now()->format('ymd') . rand(1000, 9999),
            'nhan_vien_id'   => 0,  // 0 = tự đăng ký qua web
        ]);

        // Lưu mật khẩu vào bảng riêng
        KhachHangMatKhau::create([
            'khach_hang_id' => $khachHang->id,
            'mat_khau'      => Hash::make($matKhau),
        ]);

        return $khachHang;
    }

    /**
     * Lấy thông tin khách hàng đang đăng nhập
     */
    public function layKhachHangDangNhap(): ?KhachHang
    {
        $id = session('khach_hang_id');
        if (!$id) return null;
        return KhachHang::find($id);
    }

    /**
     * Lấy tên người đang đăng nhập — giống layTenNguoiDangNhap() của nội bộ
     */
    public function layTenNguoiDangNhap(): ?string
    {
        return session('tenDangNhap');
    }

    /**
     * Đăng xuất
     */
    public function dangXuat(): void
    {
        session()->forget(['khach_hang_id', 'tenDangNhap', 'emailDangNhap', 'sdtDangNhap']);
        session()->flush();
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function capNhatThongTin(int $id, array $data): KhachHang
    {
        $khachHang = KhachHang::findOrFail($id);
        $khachHang->update($data);
        return $khachHang;
    }

    /**
     * Thêm địa chỉ mới
     */
    public function themDiaChi(int $khachHangId, array $data): DiaChi
    {
        // Nếu là mặc định, bỏ mặc định các địa chỉ cũ
        if (!empty($data['la_mac_dinh'])) {
            DiaChi::where('khach_hang_id', $khachHangId)
                ->update(['la_mac_dinh' => false]);
        }

        return DiaChi::create(array_merge($data, ['khach_hang_id' => $khachHangId]));
    }
}
