@extends('layouts.app')
@section('title', 'Giỏ hàng của tôi')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/" class="hover:text-gray-900">Trang chủ</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Giỏ hàng</span>
    </nav>

    <h1 class="text-2xl font-bold text-gray-900 mb-8">Giỏ hàng của tôi</h1>

    @php
        $chiTiets = $gioHang?->chiTiets?->load('sanPham') ?? collect();
        $tongTien = $chiTiets->sum(fn($item) => $item->so_luong * $item->gia);
    @endphp

    @if($chiTiets->isEmpty())
        <div class="text-center py-24 bg-white rounded-3xl border border-gray-100">
            <div class="text-7xl mb-4">🛒</div>
            <p class="text-xl font-semibold text-gray-700 mb-2">Giỏ hàng trống</p>
            <p class="text-gray-400 mb-6">Hãy thêm sản phẩm vào giỏ để tiếp tục</p>
            <a href="/san-pham" class="inline-block px-8 py-3 rounded-full font-semibold text-white transition-all hover:opacity-90"
               style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                Mua sắm ngay →
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- DANH SÁCH SẢN PHẨM --}}
            <div class="lg:col-span-2 space-y-4" id="danh-sach-gio-hang">
                @foreach($chiTiets as $item)
                @php
                    $anhRaw = $item->sanPham?->anh_san_pham;
                    $anhArr = is_string($anhRaw) ? (json_decode($anhRaw, true) ?? [$anhRaw]) : (is_array($anhRaw) ? $anhRaw : []);
                    $anh = $anhArr[0] ?? null;
                @endphp
                <div class="item-gio-hang bg-white rounded-2xl p-4 flex gap-4 shadow-sm border border-gray-100" data-id="{{ $item->id }}">
                    {{-- Ảnh --}}
                    <a href="/san-pham/{{ $item->sanPham?->ma_chung }}" class="shrink-0">
                        <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-50">
                            @if($anh)
                                <img src="{{ asset('storage/uploads/sanpham/' . $anh) }}" alt="{{ $item->sanPham?->ten }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-2xl text-gray-200">👕</div>
                            @endif
                        </div>
                    </a>

                    {{-- Thông tin --}}
                    <div class="flex-1 min-w-0">
                        <a href="/san-pham/{{ $item->sanPham?->ma_chung }}" class="font-semibold text-gray-900 hover:text-yellow-600 line-clamp-2 text-sm">
                            {{ $item->sanPham?->ten ?? 'Sản phẩm' }}
                        </a>
                        <div class="text-sm font-bold mt-1" style="color:#1a1a2e">
                            {{ number_format($item->gia, 0, ',', '.') }}đ
                        </div>

                        <div class="flex items-center justify-between mt-3">
                            {{-- Số lượng --}}
                            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                <button onclick="capNhatSoLuong({{ $item->id }}, -1)"
                                    class="px-3 py-1.5 text-gray-500 hover:bg-gray-50 transition-colors text-sm font-bold">−</button>
                                <span id="sl-{{ $item->id }}" class="px-3 py-1.5 text-sm font-semibold text-gray-900 min-w-[2.5rem] text-center">{{ $item->so_luong }}</span>
                                <button onclick="capNhatSoLuong({{ $item->id }}, 1)"
                                    class="px-3 py-1.5 text-gray-500 hover:bg-gray-50 transition-colors text-sm font-bold">+</button>
                            </div>

                            {{-- Thành tiền + xóa --}}
                            <div class="flex items-center gap-3">
                                <span id="tt-{{ $item->id }}" class="font-bold text-sm" style="color:#1a1a2e">
                                    {{ number_format($item->so_luong * $item->gia, 0, ',', '.') }}đ
                                </span>
                                <button onclick="xoaKhoiGio({{ $item->id }})"
                                    class="p-1.5 text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- TỔNG ĐƠN HÀNG --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">
                    <h2 class="font-bold text-gray-900 mb-5 text-lg">Tóm tắt đơn hàng</h2>

                    <div class="space-y-3 mb-5">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tạm tính</span>
                            <span id="tong-tam-tinh" class="font-medium">{{ number_format($tongTien, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Phí vận chuyển</span>
                            <span class="text-green-600 font-medium">Miễn phí</span>
                        </div>
                        <hr class="border-gray-100">
                        <div class="flex justify-between font-bold text-gray-900">
                            <span>Tổng cộng</span>
                            <span id="tong-cuoi" class="text-lg" style="color:#1a1a2e">{{ number_format($tongTien, 0, ',', '.') }}đ</span>
                        </div>
                    </div>

                    <a href="/thanh-toan" class="block w-full py-3.5 rounded-xl font-bold text-white text-center transition-all hover:opacity-90 hover:shadow-lg"
                       style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                        Tiến hành thanh toán →
                    </a>
                    <a href="/san-pham" class="block text-center text-sm text-gray-500 hover:text-gray-700 mt-3 transition-colors">
                        ← Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
@vite('resources/js/gioHang/index.js')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function capNhatSoLuong(chiTietId, delta) {
    const slEl = document.getElementById('sl-' + chiTietId);
    const slHienTai = parseInt(slEl.textContent);
    const slMoi = Math.max(0, slHienTai + delta);

    if (slMoi === 0) { xoaKhoiGio(chiTietId); return; }

    fetch('/api/gio-hang/cap-nhat', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ chi_tiet_id: chiTietId, so_luong: slMoi })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    })
    .catch(() => alert('Lỗi cập nhật giỏ hàng'));
}

function xoaKhoiGio(chiTietId) {
    if (!confirm('Xóa sản phẩm này khỏi giỏ?')) return;

    fetch('/api/gio-hang/xoa', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ chi_tiet_id: chiTietId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
}
</script>
@endpush
