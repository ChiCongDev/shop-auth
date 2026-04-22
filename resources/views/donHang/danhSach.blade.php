@extends('layouts.app')
@section('title', 'Đơn hàng của tôi')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/" class="hover:text-gray-900">Trang chủ</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Đơn hàng của tôi</span>
    </nav>

    <h1 class="text-2xl font-bold text-gray-900 mb-8">Đơn hàng của tôi</h1>

    @if($donHangs->isEmpty())
        <div class="text-center py-20 bg-white rounded-3xl border border-gray-100">
            <div class="text-6xl mb-4">📦</div>
            <p class="text-lg font-semibold text-gray-700 mb-2">Chưa có đơn hàng nào</p>
            <a href="/san-pham" class="mt-4 inline-block px-8 py-3 rounded-full font-semibold text-white transition-all hover:opacity-90"
               style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                Mua sắm ngay →
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($donHangs as $dh)
            @php
                $mauMap = ['cho_xu_ly'=>'yellow','xuat_kho'=>'blue','dong_goi'=>'blue','van_chuyen'=>'purple','tu_van_chuyen'=>'purple','hoan_thanh'=>'green','huy'=>'red'];
                $mau = $mauMap[$dh->trang_thai] ?? 'gray';
                $tenTT = match($dh->trang_thai) {
                    'cho_xu_ly'=>'Chờ xử lý','xuat_kho'=>'Xuất kho','dong_goi'=>'Đang đóng gói','van_chuyen','tu_van_chuyen'=>'Đang giao','hoan_thanh'=>'Hoàn thành','huy'=>'Đã hủy',default=>$dh->trang_thai
                };
            @endphp
            <a href="/don-hang/{{ $dh->id }}" class="block bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:border-gray-200 transition-all">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div class="font-bold text-gray-900 text-base">{{ $dh->ma_don_hang }}</div>
                        <div class="text-sm text-gray-500 mt-0.5">{{ $dh->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        {{ $mau == 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $mau == 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $mau == 'purple' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $mau == 'green' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $mau == 'red' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $mau == 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                        {{ $tenTT }}
                    </span>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    @foreach($dh->chiTietDonHangs->take(3) as $ct)
                    @php
                        $anhRaw3 = $ct->sanPham?->anh_san_pham;
                        $anhArr3 = is_string($anhRaw3) ? (json_decode($anhRaw3, true) ?? [$anhRaw3]) : (is_array($anhRaw3) ? $anhRaw3 : []);
                        $anh3 = $anhArr3[0] ?? null;
                    @endphp
                    <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                        @if($anh3)
                            <img src="{{ asset('storage/uploads/sanpham/' . $anh3) }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-lg text-gray-300">👕</div>
                        @endif
                    </div>
                    @endforeach
                    @if($dh->chiTietDonHangs->count() > 3)
                        <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-xs font-semibold text-gray-500">
                            +{{ $dh->chiTietDonHangs->count() - 3 }}
                        </div>
                    @endif
                    <div class="ml-auto text-right">
                        <div class="text-xs text-gray-500">{{ $dh->chiTietDonHangs->sum('so_luong') }} sản phẩm</div>
                        <div class="font-bold" style="color:#1a1a2e">{{ number_format($dh->tien_thanh_toan, 0, ',', '.') }}đ</div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-8">{{ $donHangs->links() }}</div>
    @endif
</div>
@endsection
