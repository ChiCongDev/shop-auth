@extends('layouts.app')
@section('title', 'Chi tiết hàng được phép order')

@section('content')
@include('doiTac.orderHang._nav')
<div id="chi-tiet-sp-duoc-phep-order" data-ma-chung="{{ $maChung }}" class="mx-auto max-w-7xl px-4 pb-10 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500">
        <a href="/doi-tac/order-hang/san-pham-duoc-phep" class="hover:text-gray-900">Hàng được phép order</a>
        <span>/</span>
        <span id="breadcrumb-ten-sp" class="font-medium text-gray-900">Chi tiết sản phẩm</span>
    </nav>

    <div id="loading-chi-tiet-sp" class="rounded-2xl border border-gray-100 bg-white py-16 text-center text-sm text-gray-500 shadow-sm">
        Đang tải chi tiết sản phẩm order...
    </div>

    <div id="empty-chi-tiet-sp" class="hidden rounded-2xl border border-gray-100 bg-white py-16 text-center shadow-sm">
        <p class="font-semibold text-gray-900">Không tìm thấy sản phẩm được phép order</p>
        <a href="/doi-tac/order-hang/san-pham-duoc-phep" class="mt-3 inline-flex rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Quay lại danh sách</a>
    </div>

    <div id="noi-dung-chi-tiet-sp" class="hidden space-y-6">
        <section class="grid grid-cols-1 gap-8 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm lg:grid-cols-2 lg:p-8">
            <div>
                <div class="aspect-square overflow-hidden rounded-2xl bg-gray-50">
                    <img id="anh-chinh-sp-order" src="/favicon.ico" alt="Sản phẩm" class="h-full w-full object-cover">
                </div>
                <div id="ds-anh-sp-order" class="mt-3 grid grid-cols-5 gap-2"></div>
            </div>

            <div>
                <div id="nhan-hieu-sp-order" class="mb-3 inline-flex rounded-full px-3 py-1 text-xs font-bold" style="background:rgba(212,175,55,0.15); color:#d4af37"></div>
                <h1 id="ten-sp-order" class="text-2xl font-bold leading-tight text-gray-900 md:text-3xl"></h1>
                <div id="gia-sp-order" class="mt-4 text-3xl font-extrabold" style="color:#1a1a2e"></div>
                <p class="mt-3 text-sm text-gray-500">Chỉ hiển thị các phiên bản đang được bật order. Giá và tồn kho được đọc từ dữ liệu hiện có của hệ thống.</p>

                <hr class="my-6 border-gray-100">

                <div>
                    <p class="mb-3 text-sm font-semibold text-gray-700">Phiên bản được phép order</p>
                    <div id="ds-phien-ban-order" class="flex flex-wrap gap-2"></div>
                </div>

                <div id="thong-tin-phien-ban-order" class="mt-5 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Phiên bản đang chọn</div>
                            <div id="pb-ten" class="mt-1 text-lg font-bold text-gray-900">-</div>
                        </div>
                        <span id="pb-ton" class="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-bold text-gray-700">Tồn 0</span>
                    </div>
                    <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-400">SKU</div>
                            <div id="pb-sku" class="mt-1 font-semibold text-gray-900">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-400">Mã vạch</div>
                            <div id="pb-ma-vach" class="mt-1 font-semibold text-gray-900">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-400">Giá order</div>
                            <div id="pb-gia-order" class="mt-1 font-bold text-gray-900">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-400">Giá bán lẻ</div>
                            <div id="pb-gia-ban-le" class="mt-1 font-semibold text-gray-900">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-400">Có thể bán</div>
                            <div id="pb-co-the-ban" class="mt-1 font-semibold text-gray-900">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-gray-400">Bật order</div>
                            <div id="pb-order-listed-at" class="mt-1 font-semibold text-gray-900">-</div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                        <div class="text-xs font-bold uppercase text-gray-500">Số phiên bản</div>
                        <div id="tong-phien-ban-order" class="mt-1 text-2xl font-bold text-gray-900">0</div>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                        <div class="text-xs font-bold uppercase text-gray-500">Tổng tồn</div>
                        <div id="tong-ton-order" class="mt-1 text-2xl font-bold text-gray-900">0</div>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <label class="block text-sm font-semibold text-gray-700">Số lượng order</label>
                    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input id="so-luong-order-nhanh" type="number" min="1" value="1" class="h-12 w-full rounded-xl border border-gray-200 px-4 text-center text-sm font-bold focus:outline-none focus:ring-2 focus:ring-yellow-400 sm:w-32">
                        <button id="btn-them-gio-order" type="button" class="inline-flex h-12 items-center justify-center rounded-xl border border-yellow-300 bg-yellow-50 px-5 text-sm font-bold text-yellow-700 transition hover:bg-yellow-100">
                            Thêm vào giỏ order
                        </button>
                        <button id="btn-mo-tao-order-nhanh" type="button" class="inline-flex h-12 items-center justify-center rounded-xl px-5 text-sm font-bold text-white transition hover:opacity-90"
                           style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                            Tạo đơn order
                        </button>
                        <a href="/doi-tac/order-hang/gio-order" class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-200 bg-white px-5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Xem giỏ order
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="section-san-pham-lien-quan" class="hidden">
            <h2 class="mb-5 text-2xl font-bold text-gray-900">Sản phẩm liên quan</h2>
            <div id="grid-san-pham-lien-quan" class="grid grid-cols-2 gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4"></div>
        </section>
    </div>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[70] min-w-80 rounded-xl border border-gray-100 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-gray-900"></p>
    <p id="toast-message" class="mt-1 text-sm text-gray-600"></p>
</div>

<div id="modal-tao-order-nhanh" class="fixed inset-0 z-[80] hidden bg-black/40 p-4">
    <div class="mx-auto mt-8 max-h-[88vh] max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-gray-950">Tạo đơn order</h2>
                <p id="tao-order-nhanh-san-pham" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <button id="btn-dong-tao-order-nhanh" type="button" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100">Đóng</button>
        </div>
        <div class="max-h-[calc(88vh-76px)] overflow-y-auto p-5">
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                <div class="grid gap-3 text-sm sm:grid-cols-3">
                    <div>
                        <div class="text-xs font-bold uppercase text-gray-400">Phiên bản</div>
                        <div id="tao-order-nhanh-phien-ban" class="mt-1 font-bold text-gray-950">-</div>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase text-gray-400">Số lượng</div>
                        <div id="tao-order-nhanh-so-luong" class="mt-1 font-bold text-gray-950">1</div>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase text-gray-400">Giá tạm tính</div>
                        <div id="tao-order-nhanh-gia" class="mt-1 font-bold text-gray-950">0</div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <label class="block text-sm font-semibold text-gray-700">Khách hàng</label>
                <div class="relative mt-2">
                    <input id="input-tim-khach-tao-nhanh" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Tìm tên, SĐT, mã khách hàng">
                    <div id="dropdown-khach-tao-nhanh" class="hidden absolute z-50 mt-2 max-h-80 w-full overflow-y-auto rounded-2xl border border-gray-100 bg-white shadow-xl"></div>
                </div>
                <div id="khach-tao-nhanh-da-chon" class="mt-3 hidden rounded-xl border border-yellow-200 bg-yellow-50 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p id="khach-tao-nhanh-ten" class="truncate font-bold text-gray-900"></p>
                            <p id="khach-tao-nhanh-info" class="mt-1 text-xs text-gray-600"></p>
                        </div>
                        <button id="btn-xoa-khach-tao-nhanh" type="button" class="text-xs font-bold text-red-600">Xóa</button>
                    </div>
                </div>
            </div>

            @if(in_array(session('doi_tac_quyen'), ['admin', 'thu_kho', 'quan_ly_order']))
            <div id="box-chon-nhan-vien-tao-nhanh" class="mt-4 hidden rounded-xl border border-blue-100 bg-blue-50 p-3">
                <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Nhân viên phụ trách</p>
                <div id="noi-dung-chon-nhan-vien-tao-nhanh" class="mt-2 space-y-2"></div>
            </div>
            @endif

            <label class="mt-5 block text-sm font-semibold text-gray-700">Ghi chú order</label>
            <textarea id="ghi-chu-tao-nhanh" rows="4" class="mt-2 w-full rounded-xl border border-gray-200 p-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Ghi chú cho đơn order"></textarea>

            <button id="btn-xac-nhan-tao-order-nhanh" type="button" class="mt-5 w-full rounded-xl px-4 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60" style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                Xác nhận tạo đơn order
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.coTheChonNhanVienOrder = {{ in_array(session('doi_tac_quyen'), ['admin', 'thu_kho', 'quan_ly_order'], true) ? 'true' : 'false' }};
</script>
@vite('resources/js/doiTac/orderHang/chiTietSanPhamDuocPhepOrder.js')
@endpush
@endsection
