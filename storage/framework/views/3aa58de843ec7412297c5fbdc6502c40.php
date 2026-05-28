<?php $__env->startSection('title', 'Chi tiết đơn order ' . $don_hang->ma_don_hang); ?>
<?php $__env->startSection('hideFooter', true); ?>

<?php
    $trangThaiDonHang = [
        'cho_xu_ly' => ['Chờ xử lý', 'bg-amber-50 text-amber-700 border-amber-200'],
        'xuat_kho' => ['Xuất kho', 'bg-blue-50 text-blue-700 border-blue-200'],
        'dong_goi' => ['Đóng gói', 'bg-indigo-50 text-indigo-700 border-indigo-200'],
        'van_chuyen' => ['Shipper đã lấy hàng', 'bg-purple-50 text-purple-700 border-purple-200'],
        'tu_van_chuyen' => ['Tự vận chuyển', 'bg-teal-50 text-teal-700 border-teal-200'],
        'hoan_thanh' => ['Khách đã nhận hàng', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'huy' => ['Đã hủy', 'bg-red-50 text-red-700 border-red-200'],
    ];
    [$tenTrangThai, $lopTrangThai] = $trangThaiDonHang[$don_hang->trang_thai] ?? [$don_hang->trang_thai, 'bg-gray-50 text-gray-700 border-gray-200'];

    $lichSu = $don_hang->lichSuDonHangs ?? collect();
    $daDuyetOrder = $lichSu->contains('hanh_dong', 'duyet_don_order');
    $daBaoHangVeOrder = $lichSu->contains('hanh_dong', 'bao_hang_ve_order');
    $tongSoLuong = $don_hang->chiTietDonHangs->sum('so_luong');
    $tongSanPham = $don_hang->chiTietDonHangs->count();
    $tongTien = (float) ($don_hang->tong_tien ?? $don_hang->chiTietDonHangs->sum('thanh_tien'));
    $tienGiam = (float) ($don_hang->tien_giam ?? 0);
    $conPhaiTra = max(0, (float) $don_hang->tien_thanh_toan - (float) $don_hang->da_thanh_toan);
    $diaChiGiaoHang = is_string($don_hang->dia_chi_giao_hang) ? (json_decode($don_hang->dia_chi_giao_hang, true) ?: []) : [];
    $diaChiDayDu = implode(', ', array_filter([
        $diaChiGiaoHang['dia_chi'] ?? '',
        $diaChiGiaoHang['phuong_xa'] ?? '',
        $diaChiGiaoHang['quan_huyen'] ?? '',
        $diaChiGiaoHang['tinh_thanh'] ?? '',
    ]));
    $laNhanTaiQuay = ($don_hang->cach_thuc_nhan_hang ?? '') === 'nhan_tai_quay';
    $nhanHangText = $laNhanTaiQuay ? 'Nhận tại quầy' : 'Vận chuyển';
    $cacBuoc = [
        ['ma' => 'cho_xu_ly', 'ten' => 'Chờ xử lý'],
        ['ma' => 'duyet_don_order', 'ten' => 'Duyệt đơn'],
        ['ma' => 'bao_hang_ve_order', 'ten' => 'Báo hàng về'],
        ['ma' => 'xuat_kho', 'ten' => 'Xuất kho'],
        ['ma' => 'dong_goi', 'ten' => 'Đóng gói'],
        ['ma' => 'van_chuyen', 'ten' => 'Shipper đã lấy hàng'],
        ['ma' => 'hoan_thanh', 'ten' => 'Khách đã nhận hàng'],
    ];
    $buocHienTai = match ($don_hang->trang_thai) {
        'xuat_kho' => 3,
        'dong_goi' => 4,
        'van_chuyen', 'tu_van_chuyen' => 5,
        'hoan_thanh' => 6,
        default => $daBaoHangVeOrder ? 2 : ($daDuyetOrder ? 1 : 0),
    };
    $lichSuHanhDongText = [
        'tao_don' => 'Tạo đơn hàng',
        'duyet_don_order' => 'Duyệt đơn order',
        'bao_hang_ve_order' => 'Báo hàng về',
        'xuat_kho' => 'Xuất kho',
        'dong_goi' => 'Đóng gói',
        'van_chuyen' => 'Shipper đã lấy hàng',
        'tu_van_chuyen' => 'Tự vận chuyển',
        'hoan_thanh' => 'Khách đã nhận hàng',
        'huy' => 'Hủy đơn hàng',
        'thanh_toan' => 'Thanh toán',
        'cap_nhat_dia_chi' => 'Cập nhật địa chỉ giao hàng',
        'gui_viettel_post' => 'Gửi Viettel Post',
        'tao_phieu_tra' => 'Tạo phiếu trả hàng',
        'hoan_tien_tra' => 'Hoàn tiền trả hàng',
        'nhan_hang_tra' => 'Nhận hàng trả lại',
        'huy_phieu_tra' => 'Hủy phiếu trả hàng',
    ];
    $lichSuMoTaMacDinh = [
        'tao_don' => 'Đơn hàng được tạo mới',
        'duyet_don_order' => 'Đơn bán từ order đã được duyệt',
        'bao_hang_ve_order' => 'Đã báo hàng về cho đơn bán từ order',
        'xuat_kho' => 'Đơn hàng đã được xuất kho',
        'dong_goi' => 'Đơn hàng đã được đóng gói',
        'van_chuyen' => 'Shipper đã lấy hàng',
        'tu_van_chuyen' => 'Đơn hàng chuyển sang tự vận chuyển',
        'hoan_thanh' => 'Khách đã nhận hàng',
        'huy' => 'Đơn hàng đã bị hủy',
        'tao_phieu_tra' => 'Đã tạo phiếu trả hàng cho đơn bán từ order',
        'hoan_tien_tra' => 'Đã hoàn tiền cho phiếu trả hàng',
        'nhan_hang_tra' => 'Đã nhận hàng trả lại từ khách',
        'huy_phieu_tra' => 'Đã hủy phiếu trả hàng',
    ];
    $lichSuMoTaChuanHoa = [
        'Don ban tu order da duoc duyet' => 'Đơn bán từ order đã được duyệt',
        'Da bao hang ve cho don ban tu order' => 'Đã báo hàng về cho đơn bán từ order',
        'Don hang da duoc xuat kho' => 'Đơn hàng đã được xuất kho',
        'Don hang da duoc dong goi' => 'Đơn hàng đã được đóng gói',
        'Tu van chuyen' => 'Tự vận chuyển',
        'Tu dong hoan thanh sau tu van chuyen' => 'Tự động hoàn thành sau tự vận chuyển',
        'tu_van_chuyen' => 'Tự vận chuyển',
        'hoan_thanh' => 'Khách đã nhận hàng',
        'van_chuyen' => 'Shipper đã lấy hàng',
        'dong_goi' => 'Đóng gói',
        'xuat_kho' => 'Xuất kho',
    ];
    $lichSuMau = [
        'tao_don' => ['bg-slate-50 text-slate-700 border-slate-200', 'bg-slate-600'],
        'duyet_don_order' => ['bg-cyan-50 text-cyan-700 border-cyan-200', 'bg-cyan-600'],
        'bao_hang_ve_order' => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500'],
        'xuat_kho' => ['bg-blue-50 text-blue-700 border-blue-200', 'bg-blue-600'],
        'dong_goi' => ['bg-indigo-50 text-indigo-700 border-indigo-200', 'bg-indigo-600'],
        'van_chuyen' => ['bg-purple-50 text-purple-700 border-purple-200', 'bg-purple-600'],
        'tu_van_chuyen' => ['bg-teal-50 text-teal-700 border-teal-200', 'bg-teal-600'],
        'hoan_thanh' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-600'],
        'huy' => ['bg-red-50 text-red-700 border-red-200', 'bg-red-600'],
        'tao_phieu_tra' => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-600'],
        'hoan_tien_tra' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-600'],
        'nhan_hang_tra' => ['bg-blue-50 text-blue-700 border-blue-200', 'bg-blue-600'],
        'huy_phieu_tra' => ['bg-rose-50 text-rose-700 border-rose-200', 'bg-rose-600'],
    ];
?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('doiTac.orderHang._nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div
    class="bg-gray-50 px-4 pb-10 pt-6 sm:px-6 sm:pt-8 lg:px-8"
    data-don-ban-order
    data-don-hang-id="<?php echo e($don_hang->id); ?>"
    data-trang-thai="<?php echo e($don_hang->trang_thai); ?>"
    data-da-duyet-order="<?php echo e($daDuyetOrder ? '1' : '0'); ?>"
    data-da-bao-hang-ve-order="<?php echo e($daBaoHangVeOrder ? '1' : '0'); ?>"
    data-cach-thuc-nhan-hang="<?php echo e($don_hang->cach_thuc_nhan_hang ?? ''); ?>"
    data-doi-tac-quyen="<?php echo e(session('doi_tac_quyen')); ?>"
>
    <div class="mx-auto max-w-7xl space-y-5">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <a href="/doi-tac/order-hang/danh-sach" class="text-sm font-semibold text-gray-500 hover:text-gray-900">Quay lại danh sách</a>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold text-gray-950"><?php echo e($don_hang->ma_don_hang); ?></h1>
                        <span class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700">Từ order</span>
                        <span class="rounded-full border px-3 py-1 text-xs font-bold <?php echo e($lopTrangThai); ?>"><?php echo e($tenTrangThai); ?></span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        Đơn order <span class="font-semibold text-gray-800"><?php echo e($don_order->ma_don_order); ?></span>
                        <?php if($don_hang->ngay_dat): ?>
                            · Ngày tạo <?php echo e($don_hang->ngay_dat->format('d/m/Y')); ?>

                        <?php endif; ?>
                    </p>
                </div>

                <div class="flex flex-wrap gap-2" id="don-ban-action-bar">
                    <button type="button" data-action="duyet" class="hidden rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Duyệt đơn</button>
                    <button type="button" data-action="bao-hang-ve" class="hidden rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">Báo hàng về</button>
                    <button type="button" data-action="xuat-kho" class="hidden rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Xuất kho</button>
                    <button type="button" data-action="dong-goi" class="hidden rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Đóng gói</button>
                    <button type="button" data-action="van-chuyen" class="hidden rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700">Shipper đã lấy hàng</button>
                    <button type="button" data-action="tu-van-chuyen-ntq" class="hidden rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Tự vận chuyển</button>
                    <button type="button" data-action="hoan-thanh" class="hidden rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Khách đã nhận hàng</button>
                    <?php if(in_array(session('doi_tac_quyen'), ['admin', 'thu_kho', 'quan_ly_order'], true)): ?>
                        <a href="/doi-tac/order-hang/don-ban/<?php echo e($don_hang->id); ?>/doi-tra" class="hidden rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700" data-return-action>Đổi/trả hàng</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div id="don-ban-message" class="hidden rounded-lg border px-4 py-3 text-sm font-semibold"></div>

        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="-mx-2 overflow-x-auto px-2">
                <div class="flex min-w-[920px] items-start lg:min-w-0">
                    <?php $__currentLoopData = $cacBuoc; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $buoc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $daHoanThanh = $index <= $buocHienTai;
                            $laHienTai = $index === $buocHienTai;
                        ?>
                        <div class="flex min-w-[104px] flex-1 flex-col items-center text-center lg:min-w-0">
                            <div class="flex w-full items-center">
                                <?php if($index > 0): ?>
                                    <div class="h-0.5 flex-1 <?php echo e($index <= $buocHienTai ? 'bg-green-500' : 'bg-gray-200'); ?>"></div>
                                <?php else: ?>
                                    <div class="h-0.5 flex-1 bg-transparent"></div>
                                <?php endif; ?>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold <?php echo e($daHoanThanh ? 'bg-green-600 text-white' : 'border border-gray-300 bg-white text-gray-400'); ?>">
                                    <?php if($daHoanThanh): ?>
                                        ✓
                                    <?php else: ?>
                                        <?php echo e($index + 1); ?>

                                    <?php endif; ?>
                                </span>
                                <?php if($index < count($cacBuoc) - 1): ?>
                                    <div class="h-0.5 flex-1 <?php echo e($index < $buocHienTai ? 'bg-green-500' : 'bg-gray-200'); ?>"></div>
                                <?php else: ?>
                                    <div class="h-0.5 flex-1 bg-transparent"></div>
                                <?php endif; ?>
                            </div>
                            <span class="mt-2 block max-w-[96px] text-xs font-semibold leading-tight lg:max-w-[112px] <?php echo e($laHienTai ? 'text-gray-950' : ($daHoanThanh ? 'text-green-700' : 'text-gray-400')); ?>"><?php echo e($buoc['ten']); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <?php if($don_hang->trang_thai === 'cho_xu_ly'): ?>
            <section class="rounded-lg border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-medium text-amber-800">
                Nhân viên bán hàng chỉ theo dõi đơn ở bước này. Các thao tác duyệt đơn, báo hàng về và xuất kho được xử lý trong hệ thống quản lý nội bộ theo đúng phân quyền.
            </section>
        <?php endif; ?>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-gray-500">Sản phẩm</p>
                <p class="mt-2 text-2xl font-bold text-gray-950"><?php echo e($tongSanPham); ?></p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-gray-500">Tổng số lượng</p>
                <p class="mt-2 text-2xl font-bold text-gray-950"><?php echo e(number_format($tongSoLuong, 0, ',', '.')); ?></p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-gray-500">Đã thanh toán</p>
                <p class="mt-2 text-2xl font-bold text-gray-950"><?php echo e(number_format($don_hang->da_thanh_toan, 0, ',', '.')); ?>đ</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-gray-500">Còn phải trả</p>
                <p class="mt-2 text-2xl font-bold text-gray-950"><?php echo e(number_format($conPhaiTra, 0, ',', '.')); ?>đ</p>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-[1.1fr_1.1fr_0.8fr]">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="border-b border-gray-200 pb-3 text-sm font-bold uppercase text-gray-800">Thông tin khách hàng</h2>
                <div class="mt-4 grid gap-2 text-sm text-gray-600 sm:grid-cols-2">
                    <p><span class="font-semibold text-gray-950">Tên:</span> <?php echo e($don_hang->khachHang?->ten ?? $don_order->khachHang?->ten ?? '-'); ?></p>
                    <p><span class="font-semibold text-gray-950">SĐT:</span> <?php echo e($don_hang->khachHang?->sdt ?? $don_order->khachHang?->sdt ?? '-'); ?></p>
                    <p><span class="font-semibold text-gray-950">Mã KH:</span> <?php echo e($don_hang->khachHang?->ma_khach_hang ?? $don_order->khachHang?->ma_khach_hang ?? '-'); ?></p>
                    <p><span class="font-semibold text-gray-950">Email:</span> <?php echo e($don_hang->khachHang?->email ?? $don_order->khachHang?->email ?? '-'); ?></p>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="border-b border-gray-200 pb-3 text-sm font-bold uppercase text-gray-800">Cách thức nhận hàng</h2>
                <div class="mt-4 grid gap-2 text-sm text-gray-600 sm:grid-cols-2">
                    <p><span class="font-semibold text-gray-950">Hình thức:</span> <?php echo e($nhanHangText); ?></p>
                    <p><span class="font-semibold text-gray-950">Người nhận:</span> <?php echo e($diaChiGiaoHang['ten'] ?? '-'); ?></p>
                    <p><span class="font-semibold text-gray-950">SĐT:</span> <?php echo e($diaChiGiaoHang['sdt'] ?? '-'); ?></p>
                    <p class="sm:col-span-2"><span class="font-semibold text-gray-950">Địa chỉ:</span> <?php echo e($diaChiDayDu ?: '-'); ?></p>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="border-b border-gray-200 pb-3 text-sm font-bold uppercase text-gray-800">Thông tin đơn hàng</h2>
                <div class="mt-4 space-y-2 text-sm text-gray-600">
                    <p><span class="font-semibold text-gray-950">Mã đơn hàng:</span> <?php echo e($don_hang->ma_don_hang); ?></p>
                    <p><span class="font-semibold text-gray-950">Mã order:</span> <?php echo e($don_order->ma_don_order); ?></p>
                    <p><span class="font-semibold text-gray-950">Nhân viên:</span> <?php echo e($don_order->nhanVien?->ten ?? '-'); ?></p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-bold text-gray-950">Thông tin sản phẩm</h2>
                <span class="text-sm font-semibold text-gray-500"><?php echo e(number_format($tongSoLuong, 0, ',', '.')); ?> sản phẩm</span>
            </div>
            <div class="-mx-1 overflow-x-auto px-1">
                <table class="w-full min-w-[960px] text-sm xl:min-w-0">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="w-16 px-5 py-3 text-center">STT</th>
                            <th class="w-20 px-3 py-3 text-left">Ảnh</th>
                            <th class="px-3 py-3 text-left">Tên sản phẩm</th>
                            <th class="w-28 px-3 py-3 text-center">Số lượng</th>
                            <th class="w-36 px-3 py-3 text-right">Giá bán</th>
                            <th class="w-32 px-3 py-3 text-right">Chiết khấu</th>
                            <th class="w-40 px-5 py-3 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $don_hang->chiTietDonHangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chiTiet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $anhRaw = $chiTiet->sanPham?->anh_san_pham;
                                $anhArr = is_string($anhRaw) ? (json_decode($anhRaw, true) ?: [$anhRaw]) : (is_array($anhRaw) ? $anhRaw : []);
                                $anh = $anhArr[0] ?? null;
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 text-center text-gray-500"><?php echo e($loop->iteration); ?></td>
                                <td class="px-3 py-4">
                                    <div class="h-14 w-14 overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                        <?php if($anh): ?>
                                            <img src="<?php echo e(asset('storage/uploads/sanpham/' . $anh)); ?>" alt="" class="h-full w-full object-cover">
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-3 py-4">
                                    <p class="font-semibold text-gray-950"><?php echo e($chiTiet->sanPham?->ten ?? 'Sản phẩm'); ?></p>
                                    <p class="mt-1 text-xs text-gray-500"><?php echo e($chiTiet->sanPham?->ma_sku ?? '-'); ?></p>
                                </td>
                                <td class="px-3 py-4 text-center font-semibold text-gray-800"><?php echo e(number_format($chiTiet->so_luong, 0, ',', '.')); ?></td>
                                <td class="px-3 py-4 text-right font-semibold text-gray-800"><?php echo e(number_format($chiTiet->gia_ban, 0, ',', '.')); ?>đ</td>
                                <td class="px-3 py-4 text-right text-gray-600"><?php echo e(number_format($chiTiet->chiet_khau ?? 0, 0, ',', '.')); ?>%</td>
                                <td class="px-5 py-4 text-right font-bold text-gray-950"><?php echo e(number_format($chiTiet->thanh_tien, 0, ',', '.')); ?>đ</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 bg-gray-50 px-5 py-4">
                <div class="ml-auto w-full max-w-sm space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Tổng tiền (<?php echo e(number_format($tongSoLuong, 0, ',', '.')); ?> sản phẩm)</span>
                        <span class="font-semibold text-gray-950"><?php echo e(number_format($tongTien, 0, ',', '.')); ?>đ</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Chiết khấu</span>
                        <span class="font-semibold text-red-600"><?php echo e(number_format($tienGiam, 0, ',', '.')); ?>đ</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-950">
                        <span>Tiền thanh toán</span>
                        <span><?php echo e(number_format($don_hang->tien_thanh_toan, 0, ',', '.')); ?>đ</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-2 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-bold text-gray-950">Lịch sử đơn hàng</h2>
                    <p class="mt-1 text-sm text-gray-500">Theo dõi các mốc xử lý của đơn bán từ order.</p>
                </div>
                <span class="rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-600">
                    <?php echo e($don_hang->lichSuDonHangs->count()); ?> mốc xử lý
                </span>
            </div>
            <div class="mt-5">
                <?php $__empty_1 = true; $__currentLoopData = $don_hang->lichSuDonHangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $hanhDong = $item->hanh_dong ?? '';
                        $rawMoTa = trim((string) ($item->mo_ta ?: data_get($item, 'ghi_chu', '')));
                        $moTaDaChuanHoa = $lichSuMoTaChuanHoa[$rawMoTa] ?? $rawMoTa;
                        $tieuDeLichSu = $lichSuHanhDongText[$hanhDong] ?? ($lichSuMoTaChuanHoa[$hanhDong] ?? $hanhDong);
                        $moTaLichSu = $moTaDaChuanHoa ?: ($lichSuMoTaMacDinh[$hanhDong] ?? '');
                        if ($moTaLichSu === $tieuDeLichSu || $moTaLichSu === $hanhDong) {
                            $moTaLichSu = $lichSuMoTaMacDinh[$hanhDong] ?? '';
                        }
                        [$lopChipLichSu, $lopChamLichSu] = $lichSuMau[$hanhDong] ?? ['bg-gray-50 text-gray-700 border-gray-200', 'bg-gray-500'];
                    ?>
                    <article class="relative flex gap-4 pb-5 last:pb-0">
                        <div class="flex flex-col items-center">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full <?php echo e($lopChamLichSu); ?> text-xs font-bold text-white shadow-sm">
                                <?php echo e($loop->remaining + 1); ?>

                            </span>
                            <?php if(!$loop->last): ?>
                                <span class="mt-2 h-full min-h-8 w-px bg-gray-200"></span>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0 flex-1 rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-semibold text-gray-950"><?php echo e($tieuDeLichSu); ?></h3>
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold <?php echo e($lopChipLichSu); ?>">
                                            <?php echo e($loop->first ? 'Mới nhất' : 'Đã ghi nhận'); ?>

                                        </span>
                                    </div>
                                    <?php if($moTaLichSu): ?>
                                        <p class="mt-1 text-sm leading-6 text-gray-600"><?php echo e($moTaLichSu); ?></p>
                                    <?php endif; ?>
                                </div>
                                <time class="shrink-0 text-xs font-medium text-gray-500"><?php echo e($item->created_at?->format('H:i d/m/Y')); ?></time>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                <span>Người thao tác: <span class="font-semibold text-gray-700"><?php echo e($item->nguoi_thuc_hien ?: 'Hệ thống'); ?></span></span>
                                <span class="text-gray-300">•</span>
                                <span><?php echo e($item->created_at?->diffForHumans()); ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">
                        Chưa có lịch sử đơn hàng.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/doiTac/orderHang/chiTietDonBanOrder.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\workspace\projects-company\shop-auth 21-4\shop-auth\resources\views/doiTac/orderHang/chiTietDonBanOrder.blade.php ENDPATH**/ ?>