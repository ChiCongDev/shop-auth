<?php $__env->startSection('title', 'Thanh toán'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/" class="hover:text-gray-900">Trang chủ</a>
        <span>/</span>
        <a href="/gio-hang" class="hover:text-gray-900">Giỏ hàng</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Thanh toán</span>
    </nav>

    <h1 class="text-2xl font-bold text-gray-900 mb-8">Thanh toán</h1>

    <form method="POST" action="/dat-hang" id="form-dat-hang">
        <?php echo csrf_field(); ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            
            <div class="lg:col-span-2 space-y-6">

                
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full text-white text-xs flex items-center justify-center font-bold" style="background:#1a1a2e">1</span>
                        Thông tin người nhận
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Họ tên người nhận <span class="text-red-500">*</span></label>
                            <input type="text" name="ten_nguoi_nhan" required
                                value="<?php echo e(session('tenDangNhap')); ?>"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                            <input type="tel" name="sdt_nguoi_nhan" required
                                value="<?php echo e(session('sdtDangNhap')); ?>"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                    </div>
                </div>

                
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full text-white text-xs flex items-center justify-center font-bold" style="background:#1a1a2e">2</span>
                        Địa chỉ giao hàng
                    </h2>

                    
                    <?php if($khachHang->diaChis->isNotEmpty()): ?>
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Địa chỉ đã lưu:</p>
                        <div class="space-y-2">
                            <?php $__currentLoopData = $khachHang->diaChis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-start gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all hover:border-yellow-300 border-gray-200">
                                <input type="radio" name="_dia_chi_id" value="<?php echo e($dc->id); ?>"
                                    <?php echo e($loop->first ? 'checked' : ''); ?>

                                    class="mt-0.5" onchange="dienDiaChi('<?php echo e($dc->dia_chi_cu_the); ?>', '<?php echo e($dc->phuong_xa); ?>', '', '<?php echo e($dc->khu_vuc); ?>')">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900"><?php echo e($dc->dia_chi_cu_the); ?></div>
                                    <div class="text-gray-500"><?php echo e(implode(', ', array_filter([$dc->phuong_xa, $dc->khu_vuc]))); ?></div>
                                </div>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center gap-3 p-3 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-gray-400">
                                <input type="radio" name="_dia_chi_id" value="moi" onchange="dienDiaChi('','','','')">
                                <span class="text-sm text-gray-600">+ Sử dụng địa chỉ khác</span>
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ cụ thể <span class="text-red-500">*</span></label>
                            <input type="text" id="dia-chi" name="dia_chi" required
                                value="<?php echo e($khachHang->diaChis->first()?->dia_chi_cu_the); ?>"
                                placeholder="Số nhà, tên đường..."
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phường/Xã</label>
                            <input type="text" id="phuong-xa" name="phuong_xa"
                                value="<?php echo e($khachHang->diaChis->first()?->phuong_xa); ?>"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quận/Huyện</label>
                            <input type="text" id="quan-huyen" name="quan_huyen"
                                value="<?php echo e($khachHang->diaChis->first()?->quan_huyen); ?>"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành phố <span class="text-red-500">*</span></label>
                            <input type="text" id="tinh-thanh" name="tinh_thanh" required
                                value="<?php echo e($khachHang->diaChis->first()?->khu_vuc); ?>"
                                placeholder="VD: TP. Hồ Chí Minh"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                    </div>
                </div>

                
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full text-white text-xs flex items-center justify-center font-bold" style="background:#1a1a2e">3</span>
                        Hình thức thanh toán
                    </h2>
                    <div class="space-y-3">
                        <?php $__currentLoopData = [
                            ['gia_tri' => 'cod',           'icon' => '🚚', 'tieu_de' => 'Thanh toán khi nhận hàng (COD)',     'mo_ta' => 'Trả tiền mặt khi nhận hàng'],
                            ['gia_tri' => 'chuyen_khoan',  'icon' => '🏦', 'tieu_de' => 'Chuyển khoản ngân hàng',              'mo_ta' => 'Chuyển khoản trước, chúng tôi xác nhận và giao hàng'],
                            ['gia_tri' => 'cong_no',       'icon' => '📋', 'tieu_de' => 'Ghi công nợ',                         'mo_ta' => 'Thanh toán sau — áp dụng cho khách hàng thân thiết'],
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $httt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-start gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-yellow-300 has-[:checked]:border-yellow-400 has-[:checked]:bg-yellow-50">
                            <input type="radio" name="hinh_thuc_thanh_toan" value="<?php echo e($httt['gia_tri']); ?>"
                                <?php echo e($i === 0 ? 'checked' : ''); ?> class="mt-1">
                            <div>
                                <div class="font-semibold text-gray-900 text-sm"><?php echo e($httt['icon']); ?> <?php echo e($httt['tieu_de']); ?></div>
                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($httt['mo_ta']); ?></div>
                            </div>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú đơn hàng (không bắt buộc)</label>
                    <textarea name="ghi_chu_them" rows="3" placeholder="Ghi chú về đơn hàng, địa điểm giao hàng..."
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 resize-none"></textarea>
                </div>
            </div>

            
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">
                    <h2 class="font-bold text-gray-900 mb-5">Đơn hàng</h2>

                    <?php
                        $gioHang = \App\Models\GioHang::with('chiTiets.sanPham')->where('khach_hang_id', session('khach_hang_id'))->first();
                        $chiTiets = $gioHang?->chiTiets ?? collect();
                        $tongTien = $chiTiets->sum(fn($i) => $i->so_luong * $i->gia);
                    ?>

                    <div class="space-y-3 mb-5 max-h-60 overflow-y-auto">
                        <?php $__currentLoopData = $chiTiets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden shrink-0">
                                <?php
                                    $anhRaw2 = $item->sanPham?->anh_san_pham;
                                    $anhArr2 = is_string($anhRaw2) ? (json_decode($anhRaw2, true) ?? [$anhRaw2]) : (is_array($anhRaw2) ? $anhRaw2 : []);
                                    $anh2 = $anhArr2[0] ?? null;
                                ?>
                                <?php if($anh2): ?>
                                    <img src="<?php echo e(asset('storage/uploads/sanpham/' . $anh2)); ?>" alt="" class="w-full h-full object-cover">
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-900 line-clamp-2"><?php echo e($item->sanPham?->ten); ?></p>
                                <p class="text-xs text-gray-500">SL: <?php echo e($item->so_luong); ?></p>
                            </div>
                            <div class="text-xs font-bold text-gray-900 shrink-0"><?php echo e(number_format($item->so_luong * $item->gia, 0, ',', '.')); ?>đ</div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <hr class="border-gray-100 mb-4">

                    <div class="space-y-2 mb-5">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tạm tính</span>
                            <span><?php echo e(number_format($tongTien, 0, ',', '.')); ?>đ</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Vận chuyển</span>
                            <span class="text-green-600 font-medium">Miễn phí</span>
                        </div>
                        <hr class="border-gray-100">
                        <div class="flex justify-between font-bold text-gray-900">
                            <span>Tổng cộng</span>
                            <span class="text-lg" style="color:#1a1a2e"><?php echo e(number_format($tongTien, 0, ',', '.')); ?>đ</span>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-xl font-bold text-white transition-all hover:opacity-90 hover:shadow-lg"
                        style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                        🎉 Đặt hàng ngay
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function dienDiaChi(dc, px, qh, tt) {
    document.getElementById('dia-chi').value = dc;
    document.getElementById('phuong-xa').value = px;
    document.getElementById('quan-huyen').value = qh;
    document.getElementById('tinh-thanh').value = tt;
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\workspace\projects-company\shop-auth 21-4\shop-auth\resources\views/thanhToan/index.blade.php ENDPATH**/ ?>