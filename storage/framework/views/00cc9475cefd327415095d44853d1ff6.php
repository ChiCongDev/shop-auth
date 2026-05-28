<?php $__env->startSection('title', 'Danh sách đơn order'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('doiTac.orderHang._nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div id="danh-sach-don-order-ban" class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide" style="color:#d4af37">Theo dõi order</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Danh sách đơn order</h1>
            <p class="mt-1 text-sm text-gray-500">Các đơn bán được tạo từ đơn order của khách hàng được phân công.</p>
        </div>
        <a href="/doi-tac/order-hang/tao" class="inline-flex items-center justify-center rounded-xl px-4 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90"
            style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
            Tạo đơn order
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <?php $__currentLoopData = [
            ['ma' => 'cho_xu_ly', 'ten' => 'Chờ xử lý', 'lop' => 'bg-yellow-100 text-yellow-700'],
            ['ma' => 'xuat_kho', 'ten' => 'Xuất kho', 'lop' => 'bg-green-100 text-green-700'],
            ['ma' => 'dong_goi', 'ten' => 'Đóng gói', 'lop' => 'bg-emerald-100 text-emerald-700'],
            ['ma' => 'van_chuyen', 'ten' => 'Shipper đã lấy hàng', 'lop' => 'bg-purple-100 text-purple-700'],
            ['ma' => 'hoan_thanh', 'ten' => 'Khách đã nhận hàng', 'lop' => 'bg-green-100 text-green-700'],
            ['ma' => 'huy', 'ten' => 'Đã hủy', 'lop' => 'bg-red-100 text-red-700'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button type="button" data-status-card="<?php echo e($item['ma']); ?>" class="stat-card rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-yellow-300 hover:shadow-md">
            <div class="flex items-center justify-between gap-3">
                <div class="text-sm font-medium text-gray-500"><?php echo e($item['ten']); ?></div>
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full <?php echo e($item['lop']); ?>">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div id="stat-<?php echo e($item['ma']); ?>" class="mt-3 text-2xl font-bold text-gray-900">0</div>
            <div id="stat-<?php echo e($item['ma']); ?>-tien" class="mt-1 text-sm font-medium text-gray-500">0</div>
        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200">
            <button type="button" class="border-b-2 px-6 py-3 text-sm font-bold" style="border-color:#d4af37;color:#1a1a2e">
                Tất cả đơn hàng
            </button>
        </div>

        <div class="border-b border-gray-100 p-4">
            <div class="flex flex-col gap-3 xl:flex-row">
                <div class="relative flex-1">
                    <input id="input-search-order" class="w-full rounded-lg border border-gray-200 py-3 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Tìm kiếm theo mã đơn hàng, mã order, tên, SĐT khách hàng">
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select id="filter-ngay-tao" class="rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 xl:w-44">
                    <option value="">Tất cả thời gian</option>
                    <option value="today">Hôm nay</option>
                    <option value="yesterday">Hôm qua</option>
                    <option value="7days">7 ngày qua</option>
                    <option value="this_week">Tuần này</option>
                    <option value="30days">30 ngày qua</option>
                    <option value="this_month">Tháng này</option>
                    <option value="this_year">Năm nay</option>
                    <option value="custom">Tùy chỉnh</option>
                </select>
                <select id="filter-trang-thai" class="rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 xl:w-52">
                    <option value="">Trạng thái</option>
                    <option value="cho_xu_ly">Chờ xử lý</option>
                    <option value="xuat_kho">Xuất kho</option>
                    <option value="dong_goi">Đóng gói</option>
                    <option value="van_chuyen">Shipper đã lấy hàng</option>
                    <option value="tu_van_chuyen">Tự vận chuyển</option>
                    <option value="hoan_thanh">Khách đã nhận hàng</option>
                    <option value="huy">Đã hủy</option>
                </select>
                <input id="filter-khach-hang" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 xl:w-44" placeholder="Mã/tên khách hàng">
                <input id="filter-san-pham" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 xl:w-44" placeholder="Mã SKU/tên SP">
            </div>
            <div id="custom-date-range" class="mt-3 hidden flex-col gap-3 sm:flex-row sm:items-center">
                <input type="date" id="filter-tu-ngay" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <span class="text-sm text-gray-400">đến</span>
                <input type="date" id="filter-den-ngay" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <button id="btn-xoa-bo-loc" type="button" class="rounded-lg px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-50">Xóa lọc</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1040px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-4 py-3 text-left">Mã đơn hàng</th>
                        <th class="px-4 py-3 text-left">Ngày tạo đơn</th>
                        <th class="px-4 py-3 text-left">Tên khách hàng</th>
                        <th class="px-4 py-3 text-left">Trạng thái đơn hàng</th>
                        <th class="px-4 py-3 text-right">Khách phải trả</th>
                        <th class="w-10 px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody id="tbody-order" class="divide-y divide-gray-100 bg-white"></tbody>
            </table>
        </div>

        <div id="loading-order" class="py-12 text-center text-sm text-gray-500">Đang tải danh sách đơn order...</div>
        <div id="empty-order" class="hidden py-12 text-center text-sm text-gray-500">Không có đơn order phù hợp.</div>
        <div id="pagination-order" class="hidden border-t border-gray-100 bg-gray-50 px-4 py-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <span id="pagination-info" class="text-sm text-gray-500"></span>
                <div id="pagination-buttons" class="flex flex-wrap items-center justify-end gap-1.5"></div>
            </div>
        </div>
    </div>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[70] min-w-80 rounded-xl border border-gray-100 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-gray-900"></p>
    <p id="toast-message" class="mt-1 text-sm text-gray-600"></p>
</div>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/doiTac/orderHang/danhSachDonOrder.js'); ?>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\workspace\projects-company\shop-auth 21-4\shop-auth\resources\views/doiTac/orderHang/danhSachDonOrder.blade.php ENDPATH**/ ?>