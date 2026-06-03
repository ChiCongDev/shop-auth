@extends('layouts.app')
@section('title', 'Giỏ hàng order')
@section('hideFooter', true)

@section('content')
@include('doiTac.orderHang._nav')

<div id="gio-order-doi-tac" class="mx-auto max-w-7xl px-4 pb-10 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide" style="color:#d4af37">Quản lý hàng order</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Giỏ hàng order</h1>
            <p class="mt-1 text-sm text-gray-500">Danh sách sản phẩm order đã lưu theo tài khoản đối tác đang đăng nhập.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="/doi-tac/order-hang/san-pham-duoc-phep" class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Deal Order</a>
            <button id="btn-xoa-gio-order" type="button" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-100">Xóa giỏ</button>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="font-bold text-gray-900">Sản phẩm đã chọn</h2>
                    <p class="mt-1 text-sm text-gray-500">Có thể sửa số lượng hoặc xóa từng dòng trước khi tạo đơn.</p>
                </div>
                <span id="gio-order-so-san-pham" class="rounded-full bg-gray-50 px-3 py-1 text-xs font-bold text-gray-600">0 sản phẩm</span>
            </div>

            <div id="loading-gio-order" class="py-12 text-center text-sm text-gray-500">Đang tải giỏ order...</div>
            <div id="empty-gio-order" class="hidden py-12 text-center">
                <p class="font-semibold text-gray-900">Giỏ order đang trống</p>
                <a href="/doi-tac/order-hang/san-pham-duoc-phep" class="mt-3 inline-flex rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Chọn sản phẩm order</a>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table id="table-gio-order" class="hidden w-full min-w-[960px] text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="min-w-[420px] px-4 py-3 text-left">Sản phẩm</th>
                            <th class="w-32 px-4 py-3 text-center">Số lượng</th>
                            <th class="w-40 px-4 py-3 text-right">Giá order</th>
                            <th class="w-40 px-4 py-3 text-right">Thành tiền</th>
                            <th class="w-28 px-4 py-3 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-gio-order" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm lg:sticky lg:top-24">
                <h2 class="text-base font-bold text-gray-900">Tạo đơn từ giỏ</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Số sản phẩm</span><span id="tong-sp-gio-order" class="font-bold text-gray-900">0</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Tổng số lượng</span><span id="tong-sl-gio-order" class="font-bold text-gray-900">0</span></div>
                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex justify-between"><span class="font-bold text-gray-700">Tổng tiền order</span><span id="tong-tien-gio-order" class="font-bold" style="color:#d4af37">0</span></div>
                    </div>
                </div>

                <div class="mt-5">
                    <label class="block text-sm font-semibold text-gray-700">Khách hàng</label>
                    <div class="relative mt-2">
                        <input id="input-tim-khach-gio-order" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Tìm tên, SĐT, mã khách hàng">
                        <div id="dropdown-khach-gio-order" class="hidden absolute z-50 mt-2 max-h-80 w-full overflow-y-auto rounded-2xl border border-gray-100 bg-white shadow-xl"></div>
                    </div>
                    <div id="khach-gio-order-da-chon" class="mt-3 hidden rounded-xl border border-yellow-200 bg-yellow-50 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p id="khach-gio-order-ten" class="truncate font-bold text-gray-900"></p>
                                <p id="khach-gio-order-info" class="mt-1 text-xs text-gray-600"></p>
                            </div>
                            <button id="btn-xoa-khach-gio-order" type="button" class="text-xs font-bold text-red-600">Xóa</button>
                        </div>
                    </div>
                </div>

                @if(in_array(session('doi_tac_quyen'), ['admin', 'thu_kho', 'quan_ly_order']))
                <div id="box-chon-nhan-vien-gio-order" class="mt-4 hidden rounded-xl border border-blue-100 bg-blue-50 p-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Nhân viên phụ trách</p>
                    <div id="noi-dung-chon-nhan-vien-gio-order" class="mt-2 space-y-2"></div>
                </div>
                @endif

                <label class="mt-5 block text-sm font-semibold text-gray-700">Ghi chú order</label>
                <textarea id="ghi-chu-gio-order" rows="4" class="mt-2 w-full rounded-xl border border-gray-200 p-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Ghi chú cho đơn order"></textarea>

                <button id="btn-tao-order-tu-gio" type="button" class="mt-5 w-full rounded-xl px-4 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60" style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                    Tạo đơn order
                </button>
            </section>
        </aside>
    </div>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[90] min-w-80 rounded-xl border border-gray-100 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-gray-900"></p>
    <p id="toast-message" class="mt-1 text-sm text-gray-600"></p>
</div>

@push('scripts')
<script>
    window.coTheChonNhanVienOrder = {{ in_array(session('doi_tac_quyen'), ['admin', 'thu_kho', 'quan_ly_order'], true) ? 'true' : 'false' }};
</script>
@vite('resources/js/doiTac/orderHang/gioOrderHang.js')
@endpush
@endsection
