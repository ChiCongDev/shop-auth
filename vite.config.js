import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/trangChu.js',
                'resources/js/sanPham/danhSach.js',
                'resources/js/sanPham/chiTiet.js',
                'resources/js/gioHang/index.js',
                'resources/js/thanhToan/index.js',
                'resources/js/donHang/danhSach.js',
                'resources/js/donHang/chiTiet.js',
                'resources/js/taiKhoan/index.js',
                'resources/js/doiTac/orderHang/taoDonOrder.js',
                'resources/js/doiTac/orderHang/danhSachDonOrder.js',
                'resources/js/doiTac/orderHang/danhSachKhachHangOrder.js',
                'resources/js/doiTac/orderHang/hangOrderVe.js',
                'resources/js/doiTac/orderHang/chiTietDonOrder.js',
                'resources/js/doiTac/orderHang/chiTietDonBanOrder.js',
                'resources/js/doiTac/orderHang/doiTraHangOrder.js',
                'resources/js/doiTac/orderHang/danhSachPhieuTraHangOrder.js',
                'resources/js/doiTac/orderHang/sanPhamDuocPhepOrder.js',
                'resources/js/doiTac/orderHang/chiTietSanPhamDuocPhepOrder.js',
                'resources/js/doiTac/orderHang/gioOrderHang.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
