<?php $__env->startSection('title', 'Hàng order về'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('doiTac.orderHang._nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div id="hang-order-ve-doi-tac" class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide" style="color:#d4af37">Theo dõi order</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Hàng order về</h1>
            <p class="mt-1 text-sm text-gray-500">Theo dõi trạng thái đơn order của khách hàng được phân công.</p>
        </div>
        <a href="/doi-tac/order-hang/tao" class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:opacity-90"
            style="background:#4f63f1">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Đặt đơn order
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <?php $__currentLoopData = [
            ['ma' => 'dat_truoc', 'ten' => 'Đặt trước', 'lop' => 'bg-blue-100 text-blue-700', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['ma' => 've_mot_phan', 'ten' => 'Về một phần', 'lop' => 'bg-yellow-100 text-yellow-700', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
            ['ma' => 'hang_da_ve', 'ten' => 'Hàng đã về', 'lop' => 'bg-green-100 text-green-700', 'icon' => 'M5 13l4 4L19 7'],
            ['ma' => 'san_sang_tao_don_ban', 'ten' => 'Sẵn sàng tạo đơn bán', 'lop' => 'bg-emerald-100 text-emerald-700', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z'],
            ['ma' => 'da_chuyen_don_ban', 'ten' => 'Đã chuyển đơn bán', 'lop' => 'bg-slate-100 text-slate-700', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ['ma' => 'da_huy', 'ten' => 'Đã hủy', 'lop' => 'bg-red-100 text-red-700', 'icon' => 'M6 18L18 6M6 6l12 12'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button type="button" data-order-status-card="<?php echo e($item['ma']); ?>" class="order-status-card rounded-lg border border-gray-200 bg-white p-4 text-left transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div class="text-sm text-gray-500"><?php echo e($item['ten']); ?></div>
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full <?php echo e($item['lop']); ?>">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($item['icon']); ?>"/>
                    </svg>
                </div>
            </div>
            <div id="stat-order-<?php echo e($item['ma']); ?>" class="text-2xl font-bold text-gray-900">0</div>
            <div class="mt-1 text-sm text-gray-600">Lọc nhanh</div>
        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200">
            <button type="button" class="border-b-2 border-blue-600 px-6 py-3 text-sm font-bold text-gray-900">
                Tất cả đơn order
            </button>
        </div>

        <div class="border-b border-gray-200 p-4">
            <div class="flex items-center gap-3 max-md:flex-col max-md:gap-2">
                <div class="relative flex-1 max-md:w-full">
                    <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input id="input-search-hang-ve" class="w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-4 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-transparent focus:ring-2 focus:ring-blue-500" placeholder="Tìm kiếm theo mã order, tên, SĐT khách hàng">
                </div>
                <div class="w-64 max-md:w-full">
                    <select id="select-trang-thai-hang-ve" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-transparent focus:ring-2 focus:ring-blue-500">
                        <option value="">Tất cả trạng thái</option>
                        <option value="dat_truoc">Đặt trước</option>
                        <option value="ve_mot_phan">Về một phần</option>
                        <option value="hang_da_ve">Hàng đã về</option>
                        <option value="san_sang_tao_don_ban">Sẵn sàng tạo đơn bán</option>
                        <option value="da_chuyen_don_ban">Đã chuyển đơn bán</option>
                        <option value="da_huy">Đã hủy</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold">Mã order</th>
                        <th class="px-4 py-3 text-left font-bold">Khách hàng</th>
                        <th class="px-4 py-3 text-left font-bold">Nhân viên</th>
                        <th class="px-4 py-3 text-center font-bold">Số dòng</th>
                        <th class="px-4 py-3 text-center font-bold">Trạng thái</th>
                        <th class="px-4 py-3 text-center font-bold">Ngày tạo</th>
                        <th class="px-5 py-3 text-right font-bold">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="tbody-hang-order-ve" class="divide-y divide-gray-100 bg-white"></tbody>
            </table>
        </div>

        <div id="empty-hang-order-ve" class="hidden py-16 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-sm font-semibold text-gray-500">OD</div>
            <p class="font-medium text-gray-900">Không có đơn order</p>
            <p class="mt-1 text-sm text-gray-500">Thử đổi từ khóa hoặc bộ lọc trạng thái.</p>
        </div>
        <div id="loading-hang-order-ve" class="py-16 text-center text-sm text-gray-500">Đang tải danh sách order...</div>

        <div id="pagination-hang-order-ve" class="hidden border-t border-slate-200 bg-slate-50 px-4 py-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <span id="pagination-hang-ve-info" class="text-sm text-gray-500"></span>
                <div id="pagination-hang-ve-buttons" class="flex flex-wrap items-center justify-end gap-1.5"></div>
            </div>
        </div>
    </div>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[70] min-w-80 rounded-lg border border-slate-200 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-slate-800"></p>
    <p id="toast-message" class="mt-1 text-sm text-slate-600"></p>
</div>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/doiTac/orderHang/hangOrderVe.js'); ?>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\workspace\projects-company\shop-auth 21-4\shop-auth\resources\views/doiTac/orderHang/hangOrderVe.blade.php ENDPATH**/ ?>