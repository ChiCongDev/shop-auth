@extends('layouts.app')
@section('title', 'Hàng được phép order')

@section('content')
@include('doiTac.orderHang._nav')
<div id="san-pham-duoc-phep-order-doi-tac" class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide" style="color:#d4af37">Danh mục sản phẩm order</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Hàng được phép order</h1>
            <p class="mt-1 text-sm text-gray-500">Chỉ xem các sản phẩm đang được bật order. Việc bật/tắt và chỉnh giá vẫn được quản lý ở hệ thống bán hàng.</p>
        </div>
        <a href="/doi-tac/order-hang/tao" class="inline-flex items-center justify-center rounded-xl px-4 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90"
           style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
            Tạo đơn order
        </a>
    </div>

    <section class="rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 p-4">
            <input id="input-search-sp-duoc-phep" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                   placeholder="Tìm tên sản phẩm, SKU, mã vạch">
        </div>

        <div id="grid-sp-duoc-phep" class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4"></div>

        <div id="loading-sp-duoc-phep" class="py-12 text-center text-sm text-gray-500">Đang tải danh mục hàng order...</div>
        <div id="empty-sp-duoc-phep" class="hidden py-12 text-center text-sm text-gray-500">Không có sản phẩm phù hợp.</div>
        <div id="pagination-sp-duoc-phep" class="hidden px-4 py-7">
            <div class="flex flex-col items-center justify-center gap-3 md:flex-row">
                <span id="pagination-sp-info" class="sr-only"></span>
                <div id="pagination-sp-buttons" class="inline-flex items-center overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm"></div>
            </div>
        </div>
    </section>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[70] min-w-80 rounded-xl border border-gray-100 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-gray-900"></p>
    <p id="toast-message" class="mt-1 text-sm text-gray-600"></p>
</div>

@push('scripts')
@vite('resources/js/doiTac/orderHang/sanPhamDuocPhepOrder.js')
@endpush
@endsection
