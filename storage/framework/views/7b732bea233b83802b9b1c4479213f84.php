<?php $__env->startSection('title', $chiTiet['ten_chung']); ?>
<?php $__env->startSection('description', 'Mua ' . $chiTiet['ten_chung'] . ' chính hãng tại Shop Auth'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/" class="hover:text-gray-900">Trang chủ</a>
        <span>/</span>
        <a href="/san-pham" class="hover:text-gray-900">Sản phẩm</a>
        <span>/</span>
        <span class="text-gray-900 font-medium"><?php echo e($chiTiet['ten_chung']); ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">

        
        <div>
            
            <div class="aspect-square rounded-2xl overflow-hidden bg-gray-50 mb-3 cursor-pointer" onclick="moLightboxHienTai()">
                <?php $anhChinh = $chiTiet['anh'][0] ?? null; ?>
                <?php if($anhChinh): ?>
                    <img id="anh-chinh" src="<?php echo e(asset('storage/uploads/sanpham/' . $anhChinh)); ?>"
                         alt="<?php echo e($chiTiet['ten_chung']); ?>"
                         class="w-full h-full object-cover"
                         onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-8xl text-gray-200\'>👕</div>'">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-8xl text-gray-200">👕</div>
                <?php endif; ?>
            </div>
            
            <?php if(count($chiTiet['anh']) > 1): ?>
            <div class="grid grid-cols-5 gap-2">
                <?php $__currentLoopData = $chiTiet['anh']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $anh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button onclick="document.getElementById('anh-chinh').src='<?php echo e(asset('storage/uploads/sanpham/' . $anh)); ?>'"
                    class="aspect-square rounded-lg overflow-hidden border-2 transition-all <?php echo e($i == 0 ? 'border-yellow-400' : 'border-gray-100 hover:border-gray-300'); ?>">
                    <img src="<?php echo e(asset('storage/uploads/sanpham/' . $anh)); ?>"
                         alt=""
                         class="w-full h-full object-cover"
                         onerror="this.parentElement.innerHTML='<div class=\'w-full h-full bg-gray-100\'></div>'">
                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
        </div>

        
        <div>
            <?php if($chiTiet['nhan_hieu']): ?>
            <div class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-3" style="background:rgba(212,175,55,0.15); color:#d4af37">
                <?php echo e($chiTiet['nhan_hieu']); ?>

            </div>
            <?php endif; ?>

            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight mb-2">
                <?php echo e($chiTiet['ten_chung']); ?>

            </h1>

            
            <div class="my-4">
                <?php if($chiTiet['gia_thap'] == $chiTiet['gia_cao']): ?>
                    <div id="gia-hien-thi" class="text-3xl font-extrabold" style="color:#1a1a2e">
                        <?php echo e(number_format($chiTiet['gia_thap'], 0, ',', '.')); ?>đ
                    </div>
                <?php else: ?>
                    <div id="gia-hien-thi" class="text-3xl font-extrabold" style="color:#1a1a2e">
                        <?php echo e(number_format($chiTiet['gia_thap'], 0, ',', '.')); ?>đ
                        <span class="text-lg text-gray-400 font-normal ml-2">–</span>
                        <?php echo e(number_format($chiTiet['gia_cao'], 0, ',', '.')); ?>đ
                    </div>
                <?php endif; ?>
            </div>

            <hr class="border-gray-100 my-5">

            
            <?php if(count($chiTiet['phien_bans']) > 1): ?>
            <div class="mb-6">
                <p class="text-sm font-semibold text-gray-700 mb-3">Chọn phiên bản:</p>
                <div class="flex flex-wrap gap-2" id="ds-phien-ban">
                    <?php $__currentLoopData = $chiTiet['phien_bans']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $pb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $btnClass = 'phien-ban-btn px-4 py-2 text-sm rounded-xl border-2 transition-all font-medium';
                        $btnClass .= $i == 0 ? ' border-yellow-400 bg-yellow-50 text-gray-900' : ' border-gray-200 text-gray-600 hover:border-gray-400';
                        $btnClass .= $pb['ton_kho'] <= 0 ? ' opacity-40 cursor-not-allowed line-through' : '';
                    ?>
                    <button class="<?php echo e($btnClass); ?>"
                            data-id="<?php echo e($pb['id']); ?>"
                            data-gia="<?php echo e($pb['gia']); ?>"
                            data-ton="<?php echo e($pb['ton_kho']); ?>"
                            <?php if($pb['ton_kho'] <= 0): ?> disabled <?php endif; ?>
                            onclick="chonPhienBan(this)">
                        <?php echo e($pb['ten']); ?>

                        <?php if($pb['ton_kho'] <= 0): ?><span class="text-xs">(Hết)</span><?php endif; ?>
                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            
            <?php $pbDau = $chiTiet['phien_bans'][0] ?? null; ?>
            <div class="flex items-center gap-2 mb-5" id="trang-thai-ton-kho">
                <?php if($pbDau): ?>
                    <?php $ton = $pbDau['ton_kho']; ?>
                    <?php if($ton <= 0): ?>
                        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-red-600 bg-red-50 px-3 py-1.5 rounded-full border border-red-200">
                            <span class="w-2 h-2 bg-red-500 rounded-full inline-block animate-pulse"></span>Hết hàng
                        </span>
                    <?php elseif($ton <= 5): ?>
                        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-orange-600 bg-orange-50 px-3 py-1.5 rounded-full border border-orange-200">
                            <span class="w-2 h-2 bg-orange-500 rounded-full inline-block animate-pulse"></span>&nbsp;Còn&nbsp;<strong><?php echo e($ton); ?></strong>&nbsp;sản phẩm
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-700 bg-green-50 px-3 py-1.5 rounded-full border border-green-200">
                            <span class="w-2 h-2 bg-green-500 rounded-full inline-block"></span>&nbsp;Còn&nbsp;<strong><?php echo e($ton); ?></strong>&nbsp;sản phẩm
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            
            <div class="flex items-center gap-4 mb-6">
                <div class="flex items-center border-2 border-gray-200 rounded-xl overflow-hidden">
                    <button onclick="doiSoLuong(-1)" class="px-4 py-3 text-gray-600 hover:bg-gray-50 text-lg font-bold transition-colors">−</button>
                    <input id="so-luong" type="number" value="1" min="1" max="99"
                        class="w-14 text-center text-sm font-semibold text-gray-900 focus:outline-none border-none py-3">
                    <button onclick="doiSoLuong(1)" class="px-4 py-3 text-gray-600 hover:bg-gray-50 text-lg font-bold transition-colors">+</button>
                </div>

                <?php if(session('khach_hang_id')): ?>
                    <button id="btn-them-gio"
                        onclick="themVaoGio()"
                        class="flex-1 py-3.5 rounded-xl font-bold text-white transition-all hover:opacity-90 hover:shadow-lg flex items-center justify-center gap-2"
                        style="background: linear-gradient(135deg, #1a1a2e, #d4af37)"
                        data-san-pham-id="<?php echo e($pbDau['id'] ?? ''); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Thêm vào giỏ
                    </button>
                <?php else: ?>
                    <a href="/dang-nhap?redirect=/san-pham/<?php echo e($chiTiet['ma_chung']); ?>"
                        class="flex-1 py-3.5 rounded-xl font-bold text-white text-center transition-all hover:opacity-90"
                        style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                        Đăng nhập để mua
                    </a>
                <?php endif; ?>
            </div>

            
            <div id="toast-them-gio" class="hidden p-3 rounded-xl text-sm font-medium mb-4"></div>

            
            <div class="grid grid-cols-2 gap-3 mt-4">
                <?php $__currentLoopData = ['✅ Hàng chính hãng 100%', '🚚 Giao hàng nhanh', '↩️ Đổi trả trong 7 ngày', '🛡️ Bảo hành chính hãng']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ck): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2"><?php echo e($ck); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <?php if($sanPhamLienQuan->isNotEmpty()): ?>
    <div class="mt-14">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Sản phẩm liên quan</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <?php $__currentLoopData = $sanPhamLienQuan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $anhLq = $sp->anh_san_pham;
                $anhLqArr = is_string($anhLq) ? (json_decode($anhLq, true) ?? [$anhLq]) : (is_array($anhLq) ? $anhLq : []);
                $anhLqHt = $anhLqArr[0] ?? null;
            ?>
            <a href="/san-pham/<?php echo e($sp->ma_chung); ?>" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all border border-gray-100">
                <div class="aspect-square bg-gray-50 overflow-hidden">
                    <?php if($anhLqHt): ?>
                        <img src="<?php echo e(asset('storage/uploads/sanpham/' . $anhLqHt)); ?>" alt="<?php echo e($sp->ten_chung); ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-4xl text-gray-200">👕</div>
                    <?php endif; ?>
                </div>
                <div class="p-3">
                    <p class="text-sm font-semibold text-gray-900 line-clamp-2 mb-1"><?php echo e($sp->ten_chung); ?></p>
                    <p class="font-bold text-sm" style="color:#1a1a2e"><?php echo e(number_format($sp->gia_ban_le, 0, ',', '.')); ?>đ</p>
                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/sanPham/chiTiet.js'); ?>
<script>
    let sanPhamIdHienTai = <?php echo e(isset($chiTiet['phien_bans'][0]['id']) ? $chiTiet['phien_bans'][0]['id'] : 'null'); ?>;

    function doiSoLuong(delta) {
        const input = document.getElementById('so-luong');
        const val = Math.max(1, Math.min(99, parseInt(input.value) + delta));
        input.value = val;
    }

    function chonPhienBan(btn) {
        document.querySelectorAll('.phien-ban-btn').forEach(b => {
            b.classList.remove('border-yellow-400', 'bg-yellow-50');
            b.classList.add('border-gray-200');
        });
        btn.classList.add('border-yellow-400', 'bg-yellow-50');
        btn.classList.remove('border-gray-200');

        sanPhamIdHienTai = btn.dataset.id;
        // Null check: btn-them-gio không tồn tại khi chưa đăng nhập
        const _btnGioRef = document.getElementById('btn-them-gio');
        if (_btnGioRef) _btnGioRef.dataset.sanPhamId = btn.dataset.id;

        const ton = parseInt(btn.dataset.ton);
        const ttEl = document.getElementById('trang-thai-ton-kho');

        // Hiển thị số tồn kho chính xác
        if (ton <= 0) {
            ttEl.innerHTML = '<span class="inline-flex items-center gap-1.5 text-sm font-semibold text-red-600 bg-red-50 px-3 py-1.5 rounded-full border border-red-200">' +
                '<span class="w-2 h-2 bg-red-500 rounded-full inline-block animate-pulse"></span>Hết hàng</span>';
        } else if (ton <= 5) {
            ttEl.innerHTML = '<span class="inline-flex items-center gap-1.5 text-sm font-semibold text-orange-600 bg-orange-50 px-3 py-1.5 rounded-full border border-orange-200">' +
                '<span class="w-2 h-2 bg-orange-500 rounded-full inline-block animate-pulse"></span>\u00a0Còn&nbsp;<strong>' + ton + '</strong>&nbsp;sản phẩm</span>';
        } else {
            ttEl.innerHTML = '<span class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-700 bg-green-50 px-3 py-1.5 rounded-full border border-green-200">' +
                '<span class="w-2 h-2 bg-green-500 rounded-full inline-block"></span>\u00a0Còn&nbsp;<strong>' + ton + '</strong>&nbsp;sản phẩm</span>';
        }

        const gia = parseInt(btn.dataset.gia);
        document.querySelectorAll('#gia-hien-thi').forEach(el => {
            el.textContent = new Intl.NumberFormat('vi-VN').format(gia) + 'đ';
        });

        // Cập nhật max số lượng theo tồn kho
        const inputSL = document.getElementById('so-luong');
        inputSL.max = ton > 0 ? ton : 0;
        if (parseInt(inputSL.value) > ton) inputSL.value = Math.max(1, ton);

        // Disable nút thêm giỏ nếu hết hàng
        const btnGio = document.getElementById('btn-them-gio');
        if (btnGio) {
            btnGio.disabled = ton <= 0;
            btnGio.style.opacity = ton <= 0 ? '0.5' : '1';
            btnGio.style.cursor = ton <= 0 ? 'not-allowed' : 'pointer';
        }
    }

    function themVaoGio() {
        const btn = document.getElementById('btn-them-gio');
        const id  = btn.dataset.sanPhamId || sanPhamIdHienTai;
        const sl  = parseInt(document.getElementById('so-luong').value);
        const toast = document.getElementById('toast-them-gio');

        if (!id) { showToast('Vui lòng chọn phiên bản!', false); return; }

        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin">⏳</span> Đang thêm...';

        fetch('/api/gio-hang/them', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ san_pham_id: id, so_luong: sl })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('✅ Đã thêm vào giỏ hàng!', true);
            } else {
                showToast('❌ ' + data.message, false);
            }
        })
        .catch(() => showToast('❌ Lỗi kết nối, thử lại!', false))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Thêm vào giỏ';
        });
    }

    function showToast(msg, success) {
        const toast = document.getElementById('toast-them-gio');
        toast.textContent = msg;
        toast.className = `p-3 rounded-xl text-sm font-medium mb-4 ${success ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'}`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    // ==============================
    // LIGHTBOX - Phóng to ảnh sản phẩm
    // ==============================
    const danhSachAnhLightbox = <?php echo json_encode($chiTiet['anh'], 15, 512) ?>;
    const baseAnhUrl = '<?php echo e(asset('storage/uploads/sanpham')); ?>/';
    let lightboxIndex = 0;

    function moLightboxHienTai() {
        const img = document.getElementById('anh-chinh');
        if (!img) return moLightbox(0);
        const src = img.src;
        // Tìm index ảnh đang hiển thị
        const idx = danhSachAnhLightbox.findIndex(a => src.includes(a));
        moLightbox(idx >= 0 ? idx : 0);
    }

    function moLightbox(index) {
        if (!danhSachAnhLightbox.length) return;
        lightboxIndex = index;
        const lb = document.getElementById('lightbox-sp');
        lb.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        capNhatAnhLightbox();
    }

    function dongLightbox() {
        document.getElementById('lightbox-sp').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function lightboxDi(delta) {
        lightboxIndex = (lightboxIndex + delta + danhSachAnhLightbox.length) % danhSachAnhLightbox.length;
        capNhatAnhLightbox();
    }

    function capNhatAnhLightbox() {
        const img = document.getElementById('lightbox-img');
        img.src = baseAnhUrl + danhSachAnhLightbox[lightboxIndex];
        const counter = document.getElementById('lightbox-counter');
        if (counter) {
            counter.textContent = (lightboxIndex + 1) + ' / ' + danhSachAnhLightbox.length;
        }
    }

    // Đóng lightbox bằng phím ESC, chuyển ảnh bằng phím mũi tên
    document.addEventListener('keydown', function(e) {
        const lb = document.getElementById('lightbox-sp');
        if (lb.classList.contains('hidden')) return;
        if (e.key === 'Escape') dongLightbox();
        if (e.key === 'ArrowLeft') lightboxDi(-1);
        if (e.key === 'ArrowRight') lightboxDi(1);
    });
</script>


<div id="lightbox-sp" class="hidden fixed inset-0 z-[9999] flex items-center justify-center"
     style="background:rgba(0,0,0,0.85); backdrop-filter:blur(8px)">

    
    <div class="absolute inset-0" onclick="dongLightbox()"></div>

    
    <button onclick="dongLightbox()"
            class="absolute top-4 right-4 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors"
            style="font-size:20px">✕</button>

    
    <?php if(count($chiTiet['anh']) > 1): ?>
    <button onclick="event.stopPropagation(); lightboxDi(-1)"
            class="absolute left-3 md:left-6 z-10 w-11 h-11 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/25 text-white transition-colors text-xl font-bold">‹</button>
    <?php endif; ?>

    
    <div class="relative z-10 max-w-[90vw] max-h-[85vh] flex items-center justify-center" onclick="event.stopPropagation()">
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl"
             style="animation: lbFadeIn .2s ease">
    </div>

    
    <?php if(count($chiTiet['anh']) > 1): ?>
    <button onclick="event.stopPropagation(); lightboxDi(1)"
            class="absolute right-3 md:right-6 z-10 w-11 h-11 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/25 text-white transition-colors text-xl font-bold">›</button>
    <?php endif; ?>

    
    <?php if(count($chiTiet['anh']) > 1): ?>
    <div id="lightbox-counter" class="absolute bottom-5 left-1/2 -translate-x-1/2 z-10 text-white/70 text-sm font-medium bg-black/30 px-3 py-1 rounded-full"></div>
    <?php endif; ?>
</div>

<style>
    @keyframes lbFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: scale(1); }
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\workspace\projects-company\shop-auth 21-4\shop-auth\resources\views/sanPham/chiTiet.blade.php ENDPATH**/ ?>