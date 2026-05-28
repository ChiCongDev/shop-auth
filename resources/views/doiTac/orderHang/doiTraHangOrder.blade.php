@extends('layouts.app')
@section('title', 'Đổi/trả hàng order ' . $don_hang->ma_don_hang)
@section('hideFooter', true)

@php
    $tongSoLuong = $don_hang->chiTietDonHangs->sum('so_luong');
    $trangThaiDonHang = [
        'cho_xu_ly' => ['Chờ xử lý', 'text-amber-700'],
        'xuat_kho' => ['Xuất kho', 'text-blue-700'],
        'dong_goi' => ['Đóng gói', 'text-indigo-700'],
        'van_chuyen' => ['Shipper đã lấy hàng', 'text-purple-700'],
        'tu_van_chuyen' => ['Tự vận chuyển', 'text-teal-700'],
        'hoan_thanh' => ['Khách đã nhận hàng', 'text-emerald-700'],
        'huy' => ['Đã hủy', 'text-red-700'],
    ];
    [$tenTrangThai, $lopTrangThai] = $trangThaiDonHang[$don_hang->trang_thai] ?? [$don_hang->trang_thai, 'text-gray-700'];
@endphp

@section('content')
@include('doiTac.orderHang._nav')

<div class="bg-gray-50 px-4 pb-10 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <a href="/doi-tac/order-hang/don-ban/{{ $don_hang->id }}" class="text-sm font-semibold text-gray-500 hover:text-gray-900">Quay lại chi tiết đơn</a>
                    <h1 class="mt-3 text-2xl font-bold text-gray-950">Đổi/trả hàng order</h1>
                    <p class="mt-2 text-sm text-gray-500">
                        Đơn bán <span class="font-semibold text-gray-900">{{ $don_hang->ma_don_hang }}</span>
                        từ đơn order <span class="font-semibold text-gray-900">{{ $don_order->ma_don_order }}</span>
                    </p>
                </div>
                <a href="/doi-tac/order-hang/khach-tra-hang-order" class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Danh sách phiếu trả
                </a>
            </div>
        </section>

        <div id="doi-tra-message" class="hidden rounded-lg border px-4 py-3 text-sm font-semibold"></div>

        <section class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase text-gray-500">Khách hàng</p>
                <p class="mt-2 font-bold text-gray-950">{{ $don_hang->khachHang?->ten ?? $don_order->khachHang?->ten ?? '-' }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ $don_hang->khachHang?->sdt ?? $don_order->khachHang?->sdt ?? '-' }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase text-gray-500">Số lượng trong đơn</p>
                <p class="mt-2 text-2xl font-bold text-gray-950">{{ number_format($tongSoLuong, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase text-gray-500">Trạng thái</p>
                <p class="mt-2 text-lg font-bold {{ $lopTrangThai }}">{{ $tenTrangThai }}</p>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
            id="doi-tra-order"
            data-don-hang-id="{{ $don_hang->id }}"
        >
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="font-bold text-gray-950">Sản phẩm trả hàng</h2>
                <p class="mt-1 text-sm text-gray-500">Nhập số lượng cần trả cho từng sản phẩm, hệ thống sẽ kiểm tra số lượng đã trả trước khi tạo phiếu.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px] text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="w-16 px-4 py-3 text-center">STT</th>
                            <th class="px-4 py-3 text-left">Sản phẩm</th>
                            <th class="w-28 px-4 py-3 text-center">Đã bán</th>
                            <th class="w-28 px-4 py-3 text-center">Đã trả</th>
                            <th class="w-32 px-4 py-3 text-center">Còn trả</th>
                            <th class="w-32 px-4 py-3 text-center">Số lượng trả</th>
                            <th class="w-36 px-4 py-3 text-right">Giá trả</th>
                            <th class="w-44 px-4 py-3 text-left">Lý do</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($don_hang->chiTietDonHangs as $chiTiet)
                            @php
                                $anhRaw = $chiTiet->sanPham?->anh_san_pham;
                                $anhArr = is_string($anhRaw) ? (json_decode($anhRaw, true) ?: [$anhRaw]) : (is_array($anhRaw) ? $anhRaw : []);
                                $anh = $anhArr[0] ?? null;
                            @endphp
                            <tr
                                data-return-row
                                data-san-pham-id="{{ $chiTiet->san_pham_id }}"
                                data-chi-tiet-don-hang-id="{{ $chiTiet->id }}"
                                data-ten-san-pham="{{ $chiTiet->sanPham?->ten ?? 'Sản phẩm' }}"
                                data-ma-sku="{{ $chiTiet->sanPham?->ma_sku ?? '' }}"
                                data-don-vi-tinh="{{ $chiTiet->sanPham?->don_vi_tinh ?? 'Chiếc' }}"
                                data-so-luong-ban="{{ (int) $chiTiet->so_luong }}"
                                data-gia-ban="{{ (float) $chiTiet->gia_ban }}"
                            >
                                <td class="px-4 py-4 text-center text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-14 w-14 overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                            @if($anh)
                                                <img src="{{ asset('storage/uploads/sanpham/' . $anh) }}" alt="" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-950">{{ $chiTiet->sanPham?->ten ?? 'Sản phẩm' }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $chiTiet->sanPham?->ma_sku ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center font-semibold text-gray-800">{{ number_format($chiTiet->so_luong, 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-center font-semibold text-gray-500" data-da-tra>0</td>
                                <td class="px-4 py-4 text-center font-semibold text-gray-950" data-con-tra>{{ number_format($chiTiet->so_luong, 0, ',', '.') }}</td>
                                <td class="px-4 py-4">
                                    <input type="number" min="0" value="0" data-so-luong-tra class="w-full rounded-lg border border-gray-200 px-3 py-2 text-center text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-yellow-400">
                                </td>
                                <td class="px-4 py-4">
                                    <input type="text" value="{{ number_format($chiTiet->gia_ban, 0, ',', '.') }}" data-gia-tra class="w-full rounded-lg border border-gray-200 px-3 py-2 text-right text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-yellow-400">
                                </td>
                                <td class="px-4 py-4">
                                    <input type="text" data-ly-do class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Lý do trả">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid gap-4 border-t border-gray-100 bg-gray-50 p-5 lg:grid-cols-[1fr_360px]">
                <div class="space-y-3">
                    <label class="block text-sm font-semibold text-gray-700">
                        Lý do chung
                        <input id="ly-do-tra" class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Ví dụ: khách đổi size, trả một phần đơn hàng">
                    </label>
                    <label class="block text-sm font-semibold text-gray-700">
                        Ghi chú
                        <textarea id="ghi-chu-tra" rows="3" class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Thông tin bổ sung nếu có"></textarea>
                    </label>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Tổng số lượng trả</span>
                        <span id="tong-so-luong-tra" class="font-bold text-gray-950">0</span>
                    </div>
                    <div class="mt-3 flex justify-between border-t border-gray-100 pt-3 text-base font-bold text-gray-950">
                        <span>Tổng tiền hoàn dự kiến</span>
                        <span id="tong-tien-tra">0</span>
                    </div>
                    <button id="btn-tao-phieu-tra" type="button" class="mt-5 w-full rounded-lg bg-orange-600 px-4 py-3 text-sm font-bold text-white hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-60">
                        Tạo phiếu trả hàng
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/doiTac/orderHang/doiTraHangOrder.js')
@endpush
