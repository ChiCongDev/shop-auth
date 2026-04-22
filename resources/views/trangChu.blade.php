@extends('layouts.app')

@section('title', 'Trang chủ')
@section('description', 'Shop Auth - Thời trang chính hãng. Quần áo, giày dép chất lượng cao.')

@section('content')

{{-- ══ DANH MỤC — Scroll ngang trên mobile ══ --}}
<section class="py-4 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center gap-2 px-4 mb-3">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Danh mục</h2>
            <div class="flex-1 h-px bg-gray-100"></div>
        </div>

        {{-- Scroll ngang không lộ scrollbar, padding hai bên --}}
        <div class="flex overflow-x-auto scroll-hide gap-2.5 px-4 pb-1">
            @foreach([
                ['href' => '/san-pham',              'icon' => '🛍️', 'label' => 'Tất cả'],
                ['href' => '/san-pham?loai=quan-ao', 'icon' => '👕', 'label' => 'Quần Áo'],
                ['href' => '/san-pham?loai=giay-dep','icon' => '👟', 'label' => 'Giày Dép'],
                ['href' => '/san-pham?loai=Mũ',      'icon' => '🧢', 'label' => 'Mũ'],
                ['href' => '/san-pham?loai=balo-tui', 'icon' => '🎒', 'label' => 'Balo'],
            ] as $dm)
            <a href="{{ $dm['href'] }}"
               class="flex-none flex flex-col items-center gap-1.5 py-2.5 px-4 bg-gray-50
                      rounded-2xl border border-gray-100 hover:bg-yellow-50 hover:border-yellow-200
                      active:scale-95 transition-all duration-150">
                <span class="text-2xl leading-none">{{ $dm['icon'] }}</span>
                <span class="text-xs font-semibold text-gray-700 whitespace-nowrap">{{ $dm['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ SẢN PHẨM NỔI BẬT ══ --}}
<section class="py-5 sm:py-8 px-4 bg-gray-50">
    <div class="max-w-7xl mx-auto">

        {{-- Header section — tối ưu cho iPhone nhỏ --}}
        <div class="flex items-center justify-between mb-4 sm:mb-8">
            <div class="min-w-0 flex-1 mr-3">
                <h2 class="text-xl sm:text-3xl font-bold text-gray-900 leading-tight">Sản Phẩm Nổi Bật</h2>
                <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Được yêu thích nhất tuần này</p>
            </div>
            <a href="/san-pham"
               class="shrink-0 text-xs sm:text-sm font-semibold px-3 py-1.5 sm:px-5 sm:py-2
                      rounded-full border-2 whitespace-nowrap active:scale-95 transition-all"
               style="color: #1a1a2e; border-color: #1a1a2e">
                Xem tất cả →
            </a>
        </div>

        @if($sanPhamNoiBat->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <div class="text-5xl mb-4">📦</div>
                <p class="text-sm">Chưa có sản phẩm nào.</p>
            </div>
        @else
        {{-- Grid 2 cột mobile, 3 cột tablet, 4 cột desktop --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            @foreach($sanPhamNoiBat as $sp)
            <a href="/san-pham/{{ $sp->ma_chung }}"
               class="group bg-white rounded-2xl overflow-hidden shadow-sm
                      hover:shadow-xl active:scale-98 transition-all duration-300
                      border border-gray-100">
                {{-- Ảnh sản phẩm --}}
                <div class="relative aspect-square bg-gray-100 overflow-hidden">
                    @if($sp->anh_dau_tien)
                        <img src="{{ asset('storage/uploads/sanpham/' . $sp->anh_dau_tien) }}"
                             alt="{{ $sp->ten_chung ?? $sp->ten }}"
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-4xl text-gray-300\'>👕</div>'">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl text-gray-300">👕</div>
                    @endif
                </div>
                {{-- Thông tin --}}
                <div class="p-2.5 sm:p-3 md:p-4">
                    @if($sp->nhan_hieu)
                        <div class="text-xs font-semibold mb-1 truncate" style="color: #d4af37">
                            {{ $sp->nhan_hieu }}
                        </div>
                    @endif
                    <h3 class="font-semibold text-gray-900 text-xs sm:text-sm leading-snug line-clamp-2 mb-1.5">
                        {{ $sp->ten_chung ?? $sp->ten }}
                    </h3>
                    <div class="font-bold text-sm sm:text-base" style="color: #1a1a2e">
                        {{ number_format($sp->gia_ban_le, 0, ',', '.') }}đ
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Nút xem thêm mobile --}}
        <div class="mt-6 text-center md:hidden">
            <a href="/san-pham"
               class="inline-flex items-center gap-2 px-6 py-3 rounded-full font-semibold text-sm
                      text-white active:scale-95 transition-all shadow-md hover:shadow-lg"
               style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                Xem tất cả sản phẩm
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endif
    </div>
</section>

{{-- ══ HÀNG MỚI VỀ ══ --}}
<section id="hang-moi-ve" class="py-5 sm:py-8 px-4 bg-white">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-4 sm:mb-8">
            <div class="min-w-0 flex-1 mr-3">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold text-white"
                          style="background: linear-gradient(135deg, #ff4d4f, #ff7a45)">
                        🆕 MỚI
                    </span>
                </div>
                <h2 class="text-xl sm:text-3xl font-bold text-gray-900 leading-tight">Hàng Mới Về</h2>
                <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Cập nhật liên tục — tươi nhất hôm nay</p>
            </div>
            <a href="/hang-moi-ve"
               class="shrink-0 text-xs sm:text-sm font-semibold px-3 py-1.5 sm:px-5 sm:py-2
                      rounded-full text-white whitespace-nowrap active:scale-95 transition-all shadow-sm hover:shadow-md"
               style="background: linear-gradient(135deg, #ff4d4f, #ff7a45)">
                Xem tất cả →
            </a>
        </div>

        @if($sanPhamMoi->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <div class="text-5xl mb-4">📦</div>
                <p class="text-sm">Chưa có hàng mới về.</p>
            </div>
        @else
        {{-- Grid 2 cột mobile, 3 cột tablet, 4 cột desktop — giống Sản Phẩm Nổi Bật --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            @foreach($sanPhamMoi as $sp)
            <a href="/san-pham/{{ $sp->ma_chung }}"
               class="group bg-white rounded-2xl overflow-hidden
                      border border-gray-100 shadow-sm hover:shadow-xl active:scale-[0.98]
                      transition-all duration-300">
                {{-- Ảnh --}}
                <div class="relative aspect-square bg-gray-50 overflow-hidden">
                    @if($sp->anh_dau_tien)
                        <img src="{{ asset('storage/uploads/sanpham/' . $sp->anh_dau_tien) }}"
                             alt="{{ $sp->ten_chung ?? $sp->ten }}"
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-4xl text-gray-300\'>👕</div>'">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl text-gray-300">👕</div>
                    @endif
                    {{-- Badge NEW --}}
                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-xs font-bold text-white shadow-sm"
                          style="background: linear-gradient(135deg, #ff4d4f, #ff7a45)">
                        NEW
                    </span>
                </div>
                {{-- Info --}}
                <div class="p-2.5 sm:p-3">
                    @if($sp->nhan_hieu)
                        <div class="text-xs font-semibold mb-1 truncate" style="color: #d4af37">
                            {{ $sp->nhan_hieu }}
                        </div>
                    @endif
                    <h3 class="font-semibold text-gray-900 text-xs sm:text-sm leading-snug line-clamp-2 mb-1.5">
                        {{ $sp->ten_chung ?? $sp->ten }}
                    </h3>
                    <div class="flex items-center justify-between gap-1">
                        <div class="font-bold text-sm sm:text-base" style="color: #1a1a2e">
                            {{ number_format($sp->gia_ban_le, 0, ',', '.') }}đ
                        </div>
                        <span class="text-xs text-gray-400">{{ ($sp->ngay_nhap ?? $sp->created_at)->diffForHumans(['short' => true]) }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Nút xem thêm mobile --}}
        <div class="mt-6 text-center sm:hidden">
            <a href="/hang-moi-ve"
               class="inline-flex items-center gap-2 px-6 py-3 rounded-full font-semibold text-sm
                      text-white active:scale-95 transition-all shadow-md hover:shadow-lg"
               style="background: linear-gradient(135deg, #ff4d4f, #ff7a45)">
                Xem tất cả hàng mới
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endif
    </div>
</section>

{{-- ══ CAM KẾT ══ --}}
<section class="py-8 px-4 bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach([
                ['icon' => '✅', 'tieu_de' => 'Hàng Chính Hãng',  'mo_ta' => 'Cam kết 100% hàng thật'],
                ['icon' => '🚚', 'tieu_de' => 'Giao Hàng Nhanh',   'mo_ta' => 'Giao trong 1-3 ngày'],
                ['icon' => '↩️', 'tieu_de' => 'Đổi Trả Dễ Dàng',  'mo_ta' => '7 ngày đổi trả miễn phí'],
                ['icon' => '🛡️', 'tieu_de' => 'Bảo Mật An Toàn',  'mo_ta' => 'Thanh toán an toàn'],
            ] as $item)
            <div class="text-center p-3">
                <div class="text-2xl sm:text-3xl mb-2">{{ $item['icon'] }}</div>
                <div class="font-semibold text-gray-900 text-xs sm:text-sm">{{ $item['tieu_de'] }}</div>
                <div class="text-xs text-gray-500 mt-0.5 hidden sm:block">{{ $item['mo_ta'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection

@push('scripts')
@vite('resources/js/trangChu.js')
@endpush
