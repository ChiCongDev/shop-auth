<?php $__env->startSection('title', 'Hàng Mới Về'); ?>
<?php $__env->startSection('description', 'Những sản phẩm mới nhất vừa được nhập về kho — cập nhật liên tục tại Shop Auth.'); ?>

<?php $__env->startSection('content'); ?>


<div class="relative overflow-hidden py-8 px-4"
     style="background: linear-gradient(135deg, #1a1a2e 0%, #2d1b3d 50%, #ff4d4f 100%)">
    
    <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10"
         style="background:#ff7a45; transform:translate(30%,-30%)"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full opacity-10"
         style="background:#d4af37; transform:translate(-30%,30%)"></div>

    <div class="relative max-w-7xl mx-auto">
        <div class="flex items-center gap-3 mb-2">
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold text-white border border-white/30 backdrop-blur-sm"
                  style="background:rgba(255,255,255,0.15)">
                🆕 MỚI CẬP NHẬT
            </span>
        </div>
        <h1 class="text-2xl sm:text-4xl font-bold text-white mb-1">Hàng Mới Về</h1>
        <p class="text-white/70 text-sm sm:text-base">
            Cập nhật tự động từ đơn nhập kho mới nhất · <?php echo e($danhSach->total()); ?> sản phẩm
        </p>
    </div>
</div>


<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 lg:py-8">
    <div class="flex gap-8">

        
        <aside class="hidden lg:block w-64 shrink-0">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 sticky top-24">
                <h3 class="font-bold text-gray-900 mb-5 text-base">Bộ lọc</h3>

                <form id="form-loc-moi" method="GET" action="/hang-moi-ve">

                    
                    <?php if(count($danhSachNhanHieu)): ?>
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                                Thương hiệu
                            </label>
                            <?php if($nhanHieu): ?>
                            <button type="button"
                                onclick="document.querySelector('[name=nhan_hieu]:checked').checked=false; document.getElementById('form-loc-moi').submit()"
                                class="text-xs text-red-500 hover:text-red-700 font-medium">✕ Bỏ lọc</button>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $danhSachNhanHieu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $active = $nhanHieu == $nh; ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="nhan_hieu" value="<?php echo e($nh); ?>"
                                    <?php echo e($active ? 'checked' : ''); ?>

                                    class="sr-only" onchange="this.form.submit()">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold
                                             border transition-all select-none
                                             <?php echo e($active ? 'text-white shadow-sm' : 'text-gray-700 border-gray-200 bg-gray-50 hover:border-red-300 hover:bg-red-50'); ?>"
                                      style="<?php echo e($active ? 'background:linear-gradient(135deg,#ff4d4f,#ff7a45); border-color:transparent' : ''); ?>">
                                    <?php if($active): ?>✓ <?php endif; ?><?php echo e($nh); ?>

                                </span>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                            Sắp xếp
                        </label>
                        <select name="sap_xep" onchange="this.form.submit()"
                                class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2
                                       focus:outline-none focus:ring-2 focus:ring-red-400 bg-white">
                            <option value="moi_nhat" <?php echo e($sapXep=='moi_nhat'?'selected':''); ?>>🆕 Mới nhập nhất</option>
                            <option value="gia_tang"  <?php echo e($sapXep=='gia_tang'?'selected':''); ?>>↑ Giá thấp</option>
                            <option value="gia_giam"  <?php echo e($sapXep=='gia_giam'?'selected':''); ?>>↓ Giá cao</option>
                            <option value="ten_az"    <?php echo e($sapXep=='ten_az'?'selected':''); ?>>A→Z Tên</option>
                        </select>
                    </div>

                    <?php if($nhanHieu || $sapXep !== 'moi_nhat'): ?>
                    <a href="/hang-moi-ve"
                       class="block text-center text-sm text-red-500 hover:text-red-700 hover:underline mt-3 py-1">
                        ✕ Xoá bộ lọc
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </aside>

        
        <div class="flex-1 min-w-0">

            
            <?php if(count($danhSachNhanHieu)): ?>
            <div class="lg:hidden flex overflow-x-auto scroll-hide gap-2 pb-3 mb-2">
                <a href="/hang-moi-ve"
                   class="flex-none px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap
                          <?php echo e(!$nhanHieu ? 'text-white' : 'text-gray-600 border border-gray-200 bg-white'); ?>"
                   style="<?php echo e(!$nhanHieu ? 'background:linear-gradient(135deg,#ff4d4f,#ff7a45)' : ''); ?>">
                    🏷️ Tất cả
                </a>
                <?php $__currentLoopData = $danhSachNhanHieu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $active = $nhanHieu == $nh; ?>
                <a href="<?php echo e($active ? '/hang-moi-ve' : '/hang-moi-ve?nhan_hieu='.urlencode($nh)); ?>"
                   class="flex-none px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap
                          <?php echo e($active ? 'text-white' : 'text-gray-600 border border-gray-200 bg-white'); ?>"
                   style="<?php echo e($active ? 'background:linear-gradient(135deg,#ff4d4f,#ff7a45)' : ''); ?>">
                    <?php if($active): ?>✓ <?php endif; ?><?php echo e($nh); ?>

                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>

            
            <div class="hidden lg:flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm text-gray-500">
                        <span class="font-semibold text-gray-800"><?php echo e($danhSach->total()); ?></span> sản phẩm mới về
                        <?php if($nhanHieu): ?><span class="ml-1 text-xs font-semibold px-2 py-0.5 rounded-full text-white"
                             style="background:linear-gradient(135deg,#ff4d4f,#ff7a45)"><?php echo e($nhanHieu); ?></span><?php endif; ?>
                    </p>
                </div>
            </div>

            
            <?php if($danhSach->isEmpty()): ?>
                <div class="text-center py-20 text-gray-400">
                    <div class="text-5xl mb-4">📦</div>
                    <p class="text-base font-semibold text-gray-600">Chưa có hàng mới về.</p>
                    <?php if($nhanHieu): ?>
                    <p class="text-sm text-gray-400 mt-1 mb-4">Không có sản phẩm của "<?php echo e($nhanHieu); ?>"</p>
                    <a href="/hang-moi-ve"
                       class="inline-block text-sm font-semibold px-4 py-2 rounded-full text-white mt-2"
                       style="background:linear-gradient(135deg,#ff4d4f,#ff7a45)">Xem tất cả</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <?php $__currentLoopData = $danhSach; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $anhRaw  = $sp->anh_san_pham;
                    $decoded = json_decode($anhRaw, true);
                    $anhHienThi = is_array($decoded)
                        ? ($decoded[0] ?? null)
                        : (is_string($anhRaw) && $anhRaw ? $anhRaw : null);
                ?>
                <a href="/san-pham/<?php echo e($sp->ma_chung); ?>"
                   class="group bg-white rounded-2xl overflow-hidden shadow-sm
                          hover:shadow-lg active:scale-[0.98] transition-all duration-200 border border-gray-100">
                    <div class="relative aspect-square bg-gray-50 overflow-hidden">
                        <?php if($anhHienThi): ?>
                        <img src="<?php echo e(asset('storage/uploads/sanpham/' . $anhHienThi)); ?>"
                             alt="<?php echo e($sp->ten_chung); ?>"
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-5xl text-gray-200\'>👕</div>'">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-5xl text-gray-200">👕</div>
                        <?php endif; ?>
                        
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-xs font-bold text-white shadow-sm"
                              style="background:linear-gradient(135deg,#ff4d4f,#ff7a45)">NEW</span>
                    </div>
                    <div class="p-2.5 sm:p-3">
                        <?php if($sp->nhan_hieu): ?>
                        <div class="text-xs font-bold mb-1 truncate" style="color:#d4af37"><?php echo e($sp->nhan_hieu); ?></div>
                        <?php endif; ?>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-900 line-clamp-2 leading-snug mb-1.5">
                            <?php echo e($sp->ten_chung ?? $sp->ten); ?>

                        </h3>
                        <div class="flex items-center justify-between gap-1">
                            <div class="font-bold text-sm sm:text-base" style="color:#1a1a2e">
                                <?php echo e(number_format($sp->gia_ban_le, 0, ',', '.')); ?>đ
                            </div>
                            <span class="text-xs text-gray-400">
                                <?php echo e(($sp->ngay_nhap ?? $sp->created_at)->diffForHumans(['short' => true])); ?>

                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="mt-8 mb-4 flex justify-center">
                <?php echo e($danhSach->withQueryString()->links()); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\chungpc\Downloads\shop-auth\resources\views/sanPham/hangMoiVe.blade.php ENDPATH**/ ?>