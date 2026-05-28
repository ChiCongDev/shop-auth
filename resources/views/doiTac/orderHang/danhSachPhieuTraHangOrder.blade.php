@extends('layouts.app')
@section('title', 'Khách trả hàng order')
@section('hideFooter', true)

@section('content')
@include('doiTac.orderHang._nav')

<div id="danh-sach-phieu-tra-order" data-doi-tac-quyen="{{ session('doi_tac_quyen') }}" class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide" style="color:#d4af37">Quản lý hàng order</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Khách trả hàng order</h1>
            <p class="mt-1 text-sm text-gray-500">Danh sách phiếu đổi/trả phát sinh từ các đơn bán được tạo từ order.</p>
        </div>
        <a href="/doi-tac/order-hang/danh-sach" class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">
            Danh sách đơn order
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['ma' => 'tong', 'ten' => 'Tổng phiếu', 'lop' => 'bg-slate-100 text-slate-700'],
            ['ma' => 'da_nhan_hang', 'ten' => 'Đã nhận hàng', 'lop' => 'bg-blue-100 text-blue-700'],
            ['ma' => 'da_hoan_tien', 'ten' => 'Đã hoàn tiền', 'lop' => 'bg-emerald-100 text-emerald-700'],
            ['ma' => 'huy', 'ten' => 'Đã hủy', 'lop' => 'bg-red-100 text-red-700'],
        ] as $item)
            <button type="button" data-return-status-card="{{ $item['ma'] }}" class="rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-yellow-300 hover:shadow-md">
                <div class="text-sm font-semibold text-gray-500">{{ $item['ten'] }}</div>
                <div id="stat-return-{{ $item['ma'] }}" class="mt-3 text-2xl font-bold text-gray-900">0</div>
                <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item['lop'] }}">Order</span>
            </button>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 p-4">
            <div class="flex flex-col gap-3 lg:flex-row">
                <input id="input-search-return" class="flex-1 rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Tìm theo mã phiếu, mã đơn, tên hoặc SĐT khách hàng">
                <select id="filter-return-status" class="rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 lg:w-48">
                    <option value="">Tất cả trạng thái</option>
                    <option value="da_nhan_hang">Đã nhận hàng</option>
                    <option value="da_hoan_tien">Đã hoàn tiền</option>
                    <option value="huy">Đã hủy</option>
                </select>
                <input id="filter-return-from" type="date" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <input id="filter-return-to" type="date" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1120px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Mã phiếu</th>
                        <th class="px-4 py-3 text-left">Đơn hàng</th>
                        <th class="px-4 py-3 text-left">Khách hàng</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-right">Tiền trả</th>
                        <th class="px-4 py-3 text-left">Ngày tạo</th>
                        <th class="min-w-[220px] px-4 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="tbody-return" class="divide-y divide-gray-100 bg-white"></tbody>
            </table>
        </div>
        <div id="loading-return" class="py-12 text-center text-sm text-gray-500">Đang tải danh sách phiếu trả hàng...</div>
        <div id="empty-return" class="hidden py-12 text-center text-sm text-gray-500">Không có phiếu trả hàng order phù hợp.</div>
        <div id="pagination-return" class="hidden border-t border-gray-100 bg-gray-50 px-4 py-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <span id="pagination-return-info" class="text-sm text-gray-500"></span>
                <div id="pagination-return-buttons" class="flex flex-wrap items-center justify-end gap-1.5"></div>
            </div>
        </div>
    </div>
</div>

<div id="return-detail-modal" class="fixed inset-0 z-[80] hidden bg-black/40 p-4">
    <div class="mx-auto mt-8 max-h-[88vh] max-w-4xl overflow-hidden rounded-xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4">
            <div>
                <h2 id="modal-return-title" class="text-lg font-bold text-gray-950">Chi tiết phiếu trả</h2>
                <p id="modal-return-subtitle" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <button type="button" id="btn-close-return-modal" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100">Đóng</button>
        </div>
        <div id="modal-return-body" class="max-h-[calc(88vh-76px)] overflow-y-auto p-5"></div>
    </div>
</div>

<div id="return-refund-modal" class="fixed inset-0 z-[85] hidden bg-black/40 p-4">
    <div class="mx-auto mt-12 max-w-lg overflow-hidden rounded-xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-gray-950">Hoàn tiền cho khách</h2>
                <p id="refund-modal-subtitle" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <button type="button" id="btn-close-refund-modal" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100">Đóng</button>
        </div>
        <div class="space-y-4 p-5">
            <input type="hidden" id="refund-phieu-id">
            <div class="grid gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Tổng tiền cần hoàn</span>
                    <span id="refund-tong-tien" class="font-semibold text-gray-950">0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Đã hoàn</span>
                    <span id="refund-da-hoan" class="font-semibold text-emerald-700">0</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-3">
                    <span class="font-semibold text-gray-800">Còn cần hoàn</span>
                    <span id="refund-con-lai" class="font-bold text-red-600">0</span>
                </div>
            </div>
            <label class="block text-sm font-semibold text-gray-700">
                Số tiền hoàn
                <input id="refund-so-tien" class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-3 text-right text-sm font-bold focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </label>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-refund-percent="25" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">25%</button>
                <button type="button" data-refund-percent="50" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">50%</button>
                <button type="button" data-refund-percent="100" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Hoàn đủ</button>
            </div>
            <button id="btn-confirm-refund" type="button" class="w-full rounded-lg bg-yellow-600 px-4 py-3 text-sm font-bold text-white hover:bg-yellow-700 disabled:cursor-not-allowed disabled:opacity-60">
                Xác nhận hoàn tiền
            </button>
        </div>
    </div>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[90] min-w-80 rounded-xl border border-gray-100 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-gray-900"></p>
    <p id="toast-message" class="mt-1 text-sm text-gray-600"></p>
</div>
@endsection

@push('scripts')
    @vite('resources/js/doiTac/orderHang/danhSachPhieuTraHangOrder.js')
@endpush
