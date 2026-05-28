<?php $__env->startSection('title', 'Chi tiết đơn order'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('doiTac.orderHang._nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div id="chi-tiet-order-doi-tac" data-order-id="<?php echo e($orderId); ?>" data-role="<?php echo e(session('doi_tac_quyen')); ?>" class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="/doi-tac/order-hang/danh-sach" class="text-sm font-semibold text-gray-500 hover:text-gray-900">Quay lại danh sách</a>
            <h1 id="order-title" class="mt-2 text-2xl font-bold text-gray-900">Chi tiết đơn order</h1>
            <p id="order-subtitle" class="mt-1 text-sm text-gray-500">Đang tải thông tin...</p>
        </div>
        <div id="order-action-bar" class="flex flex-wrap gap-2"></div>
    </div>

    <div id="order-loading" class="rounded-2xl border border-gray-100 bg-white p-10 text-center text-sm text-gray-500 shadow-sm">Đang tải chi tiết order...</div>
    <div id="order-error" class="hidden rounded-2xl border border-red-200 bg-red-50 p-5 text-sm font-semibold text-red-700"></div>

    <div id="order-content" class="hidden space-y-5">
        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">
                <span id="order-status-slot"></span>
                <span id="product-summary" class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700"></span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-gray-50 p-4">
                    <p class="text-xs font-bold uppercase text-gray-400">Khách hàng</p>
                    <p id="info-khach-hang" class="mt-1 font-bold text-gray-900">-</p>
                    <p id="info-sdt" class="mt-1 text-sm text-gray-500">-</p>
                </div>
                <div class="rounded-2xl bg-gray-50 p-4">
                    <p class="text-xs font-bold uppercase text-gray-400">Nhân viên phụ trách</p>
                    <p id="info-nhan-vien" class="mt-1 font-bold text-gray-900">-</p>
                </div>
                <div class="rounded-2xl bg-gray-50 p-4">
                    <p class="text-xs font-bold uppercase text-gray-400">Giá trị dự kiến</p>
                    <p id="stat-gia-tri" class="mt-1 font-bold text-gray-900">0</p>
                </div>
            </div>
            <div id="order-state-note" class="mt-5"></div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="font-bold text-gray-900">Sản phẩm trong đơn</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[940px] text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left">Sản phẩm</th>
                            <th class="px-4 py-3 text-center">SL order</th>
                            <th class="px-4 py-3 text-center">Đã về</th>
                            <th class="px-4 py-3 text-center">Đã chuyển</th>
                            <th class="px-4 py-3 text-right">Giá dự kiến</th>
                            <th class="px-4 py-3 text-center">Trạng thái</th>
                            <th class="px-5 py-3 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-order-detail" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-bold text-gray-900">Lịch sử xử lý</h2>
            <div id="order-history" class="mt-4 grid gap-3 md:grid-cols-2"></div>
        </section>
    </div>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[70] min-w-80 rounded-xl border border-gray-100 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-gray-900"></p>
    <p id="toast-message" class="mt-1 text-sm text-gray-600"></p>
</div>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/doiTac/orderHang/chiTietDonOrder.js'); ?>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\workspace\projects-company\shop-auth 21-4\shop-auth\resources\views/doiTac/orderHang/chiTietDonOrder.blade.php ENDPATH**/ ?>