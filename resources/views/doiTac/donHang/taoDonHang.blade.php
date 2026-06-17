@extends('layouts.app')
@section('title', 'Tạo đơn hàng')

@push('styles')
<style>
    #tao-don-hang-doi-tac label:has(+ #hen-giao-don-hang) {
        display: none;
    }
</style>
@endpush

@section('content')
<div id="tao-don-hang-doi-tac" class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide" style="color:#d4af37">Đơn hàng</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Tạo đơn hàng</h1>
            <p class="mt-1 text-sm text-gray-500">Chọn khách hàng được phân công và sản phẩm có thể bán.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="/doi-tac/don-hang" class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Danh sách đơn hàng</a>
            <button id="btn-tao-don-hang" onclick="taoDonHangThuong()" class="rounded-xl px-4 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90"
                style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                Tạo đơn hàng
            </button>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-5">
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-gray-900">Khách hàng</h2>
                <p class="mt-1 text-sm text-gray-500">Nhân viên bán hàng và thủ kho chỉ tìm được khách hàng đang được phân công.</p>
                <div class="relative mt-4">
                    <input id="input-tim-khach-don-hang" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Tìm tên, SĐT, mã khách hàng">
                    <div id="dropdown-khach-don-hang" class="hidden absolute z-50 mt-2 max-h-80 w-full overflow-y-auto rounded-2xl border border-gray-100 bg-white shadow-xl"></div>
                </div>
                <div id="khach-don-hang-da-chon" class="mt-4 hidden rounded-2xl border border-yellow-200 bg-yellow-50 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-yellow-700">Khách đã chọn</p>
                            <p id="khach-don-hang-ten" class="mt-1 truncate text-base font-bold text-gray-900"></p>
                            <p id="khach-don-hang-info" class="mt-1 text-sm text-gray-600"></p>
                        </div>
                        <button onclick="xoaKhachDonHangDaChon()" class="rounded-xl border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600">Xóa</button>
                    </div>
                </div>
                @if(in_array(session('doi_tac_quyen'), ['admin', 'thu_kho'], true))
                <div id="box-chon-nhan-vien-don-hang" class="mt-4 hidden rounded-2xl border border-blue-100 bg-blue-50 p-4">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Nhân viên phụ trách</p>
                            <p class="mt-1 text-sm text-blue-700">Chọn nhân viên bán hàng đang được gán với khách hàng này.</p>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-blue-700 ring-1 ring-blue-100">Bắt buộc</span>
                    </div>
                    <div id="noi-dung-chon-nhan-vien-don-hang" class="space-y-2"></div>
                </div>
                @endif
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-gray-900">Sản phẩm</h2>
                <p class="mt-1 text-sm text-gray-500">Giá bán được lấy theo chính sách giá của khách hàng đã chọn.</p>
                <div class="relative mt-4">
                    <input id="input-tim-sp-don-hang" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Tìm tên sản phẩm, SKU, mã vạch">
                    <div id="dropdown-sp-don-hang" class="hidden absolute z-40 mt-2 max-h-96 w-full overflow-y-auto rounded-2xl border border-gray-100 bg-white shadow-xl"></div>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Sản phẩm</th>
                                <th class="w-28 px-4 py-3 text-center">SL</th>
                                <th class="w-40 px-4 py-3 text-right">Giá bán</th>
                                <th class="w-36 px-4 py-3 text-right">Thành tiền</th>
                                <th class="w-24 px-4 py-3 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-sp-don-hang" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
                <div id="empty-sp-don-hang" class="py-12 text-center text-sm text-gray-500">Chưa có sản phẩm.</div>
            </section>
        </div>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm lg:sticky lg:top-24">
                <h2 class="text-base font-bold text-gray-900">Tóm tắt đơn</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Số sản phẩm</span><span id="tong-sp-don-hang" class="font-bold text-gray-900">0</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Tổng số lượng</span><span id="tong-sl-don-hang" class="font-bold text-gray-900">0</span></div>
                    <div class="hidden">
                        <label for="chiet-khau-don-hang" class="text-gray-500">Chiết khấu đơn (%)</label>
                        <input id="chiet-khau-don-hang" type="hidden" value="0">
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex justify-between"><span class="font-bold text-gray-700">Tổng tiền</span><span id="tong-tien-don-hang" class="font-bold" style="color:#d4af37">0</span></div>
                    </div>
                </div>
                <label class="mt-5 block text-sm font-semibold text-gray-700">Hẹn giao</label>
                <input id="hen-giao-don-hang" type="hidden" value="">
                <label class="mt-5 block text-sm font-semibold text-gray-700">Ghi chú</label>
                <textarea id="ghi-chu-don-hang" rows="5" class="mt-2 w-full rounded-xl border border-gray-200 p-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Ghi chú cho đơn hàng"></textarea>
            </section>
        </aside>
    </div>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[70] min-w-80 rounded-xl border border-gray-100 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-gray-900"></p>
    <p id="toast-message" class="mt-1 text-sm text-gray-600"></p>
</div>

@push('scripts')
<script>
    window.coTheChonNhanVienDonHang = {{ in_array(session('doi_tac_quyen'), ['admin', 'thu_kho'], true) ? 'true' : 'false' }};
</script>
@vite('resources/js/doiTac/donHang/taoDonHang.js')
@endpush
@endsection
