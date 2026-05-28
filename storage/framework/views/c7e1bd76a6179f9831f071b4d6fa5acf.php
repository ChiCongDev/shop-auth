<?php $__env->startSection('title', 'Đăng nhập'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center px-4 py-12" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)">
    <div class="w-full max-w-md">
        <div class="overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="p-2" style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                <div class="rounded-2xl bg-white p-8">
                    <div class="mb-8 text-center">
                        <a href="/" class="mb-6 inline-flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                                <span class="font-bold text-white">DT</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900">Khu <span style="color:#d4af37">Đối tác</span></span>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">Đăng nhập</h1>
                        <p class="mt-1 text-sm text-gray-500">Dành cho tài khoản nội bộ được cấp quyền</p>
                    </div>

                    <form method="POST" action="/doi-tac/dang-nhap" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-yellow-400"
                                placeholder="email@example.com">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Mật khẩu</label>
                            <input type="password" name="mat_khau" required
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-yellow-400"
                                placeholder="********">
                        </div>
                        <button type="submit" class="mt-2 w-full rounded-xl py-3 font-semibold text-white transition-all hover:opacity-90 hover:shadow-lg"
                            style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                            Đăng nhập
                        </button>
                    </form>

                    <div class="mt-6 flex items-center justify-between text-sm">
                        <a href="/dang-nhap" class="font-semibold text-gray-500 hover:text-gray-900">Đăng nhập khách hàng</a>
                        <a href="/" class="font-semibold hover:opacity-80" style="color:#d4af37">Về trang chủ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\workspace\projects-company\shop-auth 21-4\shop-auth\resources\views/doiTac/dangNhap.blade.php ENDPATH**/ ?>