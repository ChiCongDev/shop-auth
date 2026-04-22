<?php $__env->startSection('title', request('search') ? 'Tìm: ' . request('search') : (request('loai') ? request('loai') : 'Sản phẩm')); ?>

<?php $__env->startSection('content'); ?>


<?php if(count($danhSachNhanHieu)): ?>
<div class="lg:hidden bg-white border-b border-gray-100 sticky top-14 z-30">

    
    <div class="flex overflow-x-auto scroll-hide gap-2 px-4 pt-3 pb-2.5">

        
        <a href="<?php echo e(request()->fullUrlWithoutQuery(['nhan_hieu', 'page'])); ?>"
           class="flex-none flex items-center gap-1.5 px-4 py-2 rounded-full border text-xs font-bold
                  whitespace-nowrap transition-all active:scale-95
                  <?php echo e(!request('nhan_hieu')
                        ? 'text-white shadow-md'
                        : 'text-gray-600 border-gray-200 bg-white'); ?>"
           style="<?php echo e(!request('nhan_hieu') ? 'background: linear-gradient(135deg,#1a1a2e,#d4af37); border-color: transparent' : ''); ?>">
            🏷️ Tất cả
        </a>

        
        <?php $__currentLoopData = $danhSachNhanHieu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $active = request('nhan_hieu') == $nh; ?>
        <a href="<?php echo e($active
                    ? request()->fullUrlWithoutQuery(['nhan_hieu', 'page'])
                    : request()->fullUrlWithoutQuery(['nhan_hieu', 'page']) . '&nhan_hieu=' . urlencode($nh)); ?>"
           class="flex-none flex items-center gap-1.5 px-4 py-2 rounded-full border text-xs font-bold
                  whitespace-nowrap transition-all active:scale-95
                  <?php echo e($active
                        ? 'text-white shadow-md'
                        : 'text-gray-700 border-gray-200 bg-white hover:border-gray-400'); ?>"
           style="<?php echo e($active ? 'background: linear-gradient(135deg,#1a1a2e,#d4af37); border-color: transparent' : ''); ?>">
            <?php if($active): ?><span>✓</span><?php endif; ?>
            <?php echo e($nh); ?>

        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="flex items-center justify-between px-4 py-2 border-t border-gray-50">
        <div class="text-xs text-gray-500 font-medium">
            <span class="font-bold text-gray-800"><?php echo e($danhSach->total()); ?></span> sản phẩm
            <?php if(request('nhan_hieu')): ?>
                <span class="mx-1">·</span>
                <a href="<?php echo e(request()->fullUrlWithoutQuery(['nhan_hieu', 'page'])); ?>"
                   class="text-red-500 font-semibold">✕ Bỏ lọc</a>
            <?php endif; ?>
        </div>
        <form method="GET" action="/san-pham" id="form-sapxep-mobile">
            <?php $__currentLoopData = request()->except(['sap_xep', 'page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <select name="sap_xep" onchange="this.form.submit()"
                    class="text-xs border border-gray-200 rounded-xl px-3 py-1.5 bg-white
                           text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <option value="moi_nhat" <?php echo e(request('sap_xep','moi_nhat')=='moi_nhat'?'selected':''); ?>>✨ Mới nhất</option>
                <option value="gia_tang"  <?php echo e(request('sap_xep')=='gia_tang'?'selected':''); ?>>↑ Giá thấp</option>
                <option value="gia_giam"  <?php echo e(request('sap_xep')=='gia_giam'?'selected':''); ?>>↓ Giá cao</option>
                <option value="ten_az"    <?php echo e(request('sap_xep')=='ten_az'?'selected':''); ?>>A→Z Tên</option>
            </select>
        </form>
    </div>
</div>
<?php endif; ?>


<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 lg:py-8">

    
    <nav class="flex items-center flex-wrap gap-1.5 text-sm text-gray-500 mb-4 lg:mb-6">
        <a href="/" class="hover:text-gray-900 transition-colors">Trang chủ</a>
        <span class="text-gray-300">/</span>
        <?php if(request('loai')): ?>
            <a href="/san-pham" class="hover:text-gray-900 transition-colors">Sản phẩm</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-900 font-semibold"><?php echo e(request('loai')); ?></span>
        <?php else: ?>
            <span class="text-gray-900 font-semibold">Sản phẩm</span>
        <?php endif; ?>
        <?php if(request('nhan_hieu')): ?>
            <span class="text-gray-300">/</span>
            <span class="inline-flex items-center gap-1 bg-gray-100 px-2.5 py-0.5 rounded-full text-xs font-bold text-gray-700">
                <?php echo e(request('nhan_hieu')); ?>

                <a href="<?php echo e(request()->fullUrlWithoutQuery(['nhan_hieu', 'page'])); ?>"
                   class="text-gray-400 hover:text-red-500 ml-0.5">✕</a>
            </span>
        <?php endif; ?>
    </nav>

    <div class="flex gap-8">

        
        <aside class="hidden lg:block w-64 shrink-0">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 sticky top-24">
                <h3 class="font-bold text-gray-900 mb-5 text-base">Bộ lọc</h3>

                <form id="form-loc" method="GET" action="/san-pham">

                    
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                            Tìm kiếm
                        </label>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                            placeholder="Tên sản phẩm..."
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl
                                   focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all">
                    </div>

                    
                    <?php if(count($danhSachNhanHieu)): ?>
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                                Thương hiệu
                            </label>
                            <?php if(request('nhan_hieu')): ?>
                            <button type="button"
                                onclick="document.querySelector('[name=nhan_hieu]:checked').checked=false; document.getElementById('form-loc').submit()"
                                class="text-xs text-red-500 hover:text-red-700 font-medium">✕ Bỏ lọc</button>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $danhSachNhanHieu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $active = request('nhan_hieu') == $nh; ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="nhan_hieu" value="<?php echo e($nh); ?>"
                                    <?php echo e($active ? 'checked' : ''); ?>

                                    class="sr-only" onchange="this.form.submit()">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold
                                             border transition-all select-none
                                             <?php echo e($active
                                                 ? 'text-white shadow-sm'
                                                 : 'text-gray-700 border-gray-200 bg-gray-50 hover:bg-yellow-50 hover:border-yellow-300'); ?>"
                                      style="<?php echo e($active ? 'background: linear-gradient(135deg,#1a1a2e,#d4af37); border-color:transparent' : ''); ?>">
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
                                       focus:outline-none focus:ring-2 focus:ring-yellow-400 bg-white">
                            <option value="moi_nhat" <?php echo e(request('sap_xep','moi_nhat')=='moi_nhat'?'selected':''); ?>>Mới nhất</option>
                            <option value="gia_tang"  <?php echo e(request('sap_xep')=='gia_tang'?'selected':''); ?>>Giá tăng dần</option>
                            <option value="gia_giam"  <?php echo e(request('sap_xep')=='gia_giam'?'selected':''); ?>>Giá giảm dần</option>
                            <option value="ten_az"    <?php echo e(request('sap_xep')=='ten_az'?'selected':''); ?>>Tên A→Z</option>
                        </select>
                    </div>

                    <?php if(request()->hasAny(['search','nhan_hieu','sap_xep'])): ?>
                    <a href="/san-pham<?php echo e(request('loai') ? '?loai='.request('loai') : ''); ?>"
                       class="block text-center text-sm text-red-500 hover:text-red-700 hover:underline mt-3 py-1">
                        ✕ Xoá tất cả bộ lọc
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </aside>

        
        <div class="flex-1 min-w-0">

            
            <div class="hidden lg:flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <?php if(request('search')): ?>
                            Kết quả: "<?php echo e(request('search')); ?>"
                        <?php elseif(request('loai')): ?>
                            <?php echo e(request('loai')); ?>

                        <?php else: ?>
                            Tất cả sản phẩm
                        <?php endif; ?>
                        <?php if(request('nhan_hieu')): ?>
                            <span class="text-gray-400 font-normal text-lg">· <?php echo e(request('nhan_hieu')); ?></span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        <span class="font-semibold text-gray-800"><?php echo e($danhSach->total()); ?></span> sản phẩm
                    </p>
                </div>
            </div>

            
            <div class="lg:hidden mb-3">
                <h1 class="text-lg font-bold text-gray-900">
                    <?php if(request('search')): ?>
                        Kết quả: "<?php echo e(request('search')); ?>"
                    <?php elseif(request('loai')): ?>
                        <?php echo e(request('loai')); ?>

                    <?php else: ?>
                        Tất cả sản phẩm
                    <?php endif; ?>
                </h1>
            </div>

            
            <?php if($danhSach->isEmpty()): ?>
                <div class="text-center py-16 text-gray-400">
                    <div class="text-5xl mb-4">🔍</div>
                    <p class="text-base font-semibold text-gray-600">Không tìm thấy sản phẩm nào.</p>
                    <?php if(request('nhan_hieu')): ?>
                    <p class="text-sm text-gray-400 mt-1 mb-4">không có sản phẩm của "<?php echo e(request('nhan_hieu')); ?>"</p>
                    <a href="<?php echo e(request()->fullUrlWithoutQuery(['nhan_hieu', 'page'])); ?>"
                       class="inline-block text-sm font-semibold px-4 py-2 rounded-full"
                       style="color: #d4af37; border: 1.5px solid #d4af37">Xem thương hiệu khác</a>
                    <?php else: ?>
                    <a href="/san-pham"
                       class="mt-3 inline-block text-sm font-semibold text-yellow-600 hover:underline">
                        Xem tất cả sản phẩm →
                    </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <?php $__currentLoopData = $danhSach; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $anhRaw = $sp->anh_san_pham;
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
                    </div>
                    <div class="p-2.5 sm:p-3">
                        <?php if($sp->nhan_hieu): ?>
                        <div class="text-xs font-bold mb-1 truncate" style="color:#d4af37"><?php echo e($sp->nhan_hieu); ?></div>
                        <?php endif; ?>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-900 line-clamp-2 leading-snug mb-1.5">
                            <?php echo e($sp->ten_chung); ?>

                        </h3>
                        <div class="font-bold text-sm sm:text-base" style="color:#1a1a2e">
                            <?php echo e(number_format($sp->gia_ban_le, 0, ',', '.')); ?>đ
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

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/sanPham/danhSach.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\workspace\projects-company\shop-auth 21-4\shop-auth\resources\views/sanPham/danhSach.blade.php ENDPATH**/ ?>