<?php $__env->startSection('title', 'Danh sách khách hàng'); ?>

<?php $__env->startSection('content'); ?>
<div id="danh-sach-khach-hang-order" class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide" style="color:#d4af37">Quản lý hàng order</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Danh sách khách hàng</h1>
            <p class="mt-1 text-sm text-gray-500">Theo dõi khách hàng phục vụ tạo đơn order theo phạm vi được phân quyền.</p>
        </div>
        <a href="/doi-tac/order-hang/tao" class="inline-flex items-center justify-center rounded-xl px-4 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90"
            style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
            Tạo đơn order
        </a>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-gray-500">Tổng khách hàng</div>
            <div id="stat-tong-khach-hang" class="mt-2 text-2xl font-bold text-gray-900">0</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-gray-500">Tháng này</div>
            <div id="stat-thang-nay" class="mt-2 text-2xl font-bold text-gray-900">0</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-gray-500">Nhóm khách hàng</div>
            <div id="stat-so-nhom" class="mt-2 text-2xl font-bold text-gray-900">0</div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200">
            <button type="button" class="border-b-2 px-6 py-3 text-sm font-bold" style="border-color:#d4af37;color:#1a1a2e">
                Tất cả khách hàng
            </button>
        </div>

        <div class="border-b border-gray-100 p-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="relative flex-1">
                    <input id="input-search-khach-hang" class="w-full rounded-lg border border-gray-200 py-3 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Tìm kiếm theo tên, SĐT, mã KH, email">
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select id="filter-nhom-khach-hang" class="rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 md:w-64">
                    <option value="">Tất cả nhóm khách hàng</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[940px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Mã KH</th>
                        <th class="px-4 py-3 text-left">Khách hàng</th>
                        <th class="px-4 py-3 text-left">Liên hệ</th>
                        <th class="px-4 py-3 text-left">Địa chỉ</th>
                        <th class="px-4 py-3 text-left">Nhóm KH</th>
                        <th class="px-4 py-3 text-center">Đơn order</th>
                    </tr>
                </thead>
                <tbody id="tbody-khach-hang-order" class="divide-y divide-gray-100 bg-white"></tbody>
            </table>
        </div>

        <div id="loading-khach-hang-order" class="py-12 text-center text-sm text-gray-500">Đang tải danh sách khách hàng...</div>
        <div id="empty-khach-hang-order" class="hidden py-12 text-center text-sm text-gray-500">Không có khách hàng phù hợp.</div>
        <div id="pagination-khach-hang-order" class="hidden border-t border-gray-100 bg-gray-50 px-4 py-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <span id="pagination-khach-hang-info" class="text-sm text-gray-500"></span>
                <div id="pagination-khach-hang-buttons" class="flex flex-wrap items-center justify-end gap-1.5"></div>
            </div>
        </div>
    </div>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[70] min-w-80 rounded-xl border border-gray-100 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-gray-900"></p>
    <p id="toast-message" class="mt-1 text-sm text-gray-600"></p>
</div>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/doiTac/orderHang/danhSachKhachHangOrder.js'); ?>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\workspace\projects-company\shop-auth 21-4\shop-auth\resources\views/doiTac/orderHang/danhSachKhachHangOrder.blade.php ENDPATH**/ ?>