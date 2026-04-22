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
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
