@extends('layouts.app')
@section('title', 'Chi tiết đơn hàng ' . $donHang->ma_don_hang)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/" class="hover:text-gray-900">Trang chủ</a>
        <span>/</span>
        <a href="/don-hang" class="hover:text-gray-900">Đơn hàng</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">{{ $donHang->ma_don_hang }}</span>
    </nav>

    {{-- HEADER --}}
    <div class="flex items-start justify-between flex-wrap gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Đơn hàng {{ $donHang->ma_don_hang }}</h1>
            <p class="text-sm text-gray-500 mt-1">Đặt lúc {{ $donHang->created_at->format('H:i, d/m/Y') }}</p>
        </div>
        @php
            $mauMap = ['cho_xu_ly'=>['yellow','Chờ xử lý'],'xuat_kho'=>['blue','Xuất kho'],'dong_goi'=>['blue','Đang đóng gói'],'van_chuyen'=>['purple','Đang giao'],'tu_van_chuyen'=>['purple','Đang giao'],'hoan_thanh'=>['green','Hoàn thành'],'huy'=>['red','Đã hủy']];
            [$mau, $tenTT] = $mauMap[$donHang->trang_thai] ?? ['gray', $donHang->trang_thai];
        @endphp
        <span class="px-4 py-1.5 rounded-full text-sm font-bold
            {{ $mau=='yellow'?'bg-yellow-100 text-yellow-800':'' }}
            {{ $mau=='blue'?'bg-blue-100 text-blue-800':'' }}
            {{ $mau=='purple'?'bg-purple-100 text-purple-800':'' }}
            {{ $mau=='green'?'bg-green-100 text-green-800':'' }}
            {{ $mau=='red'?'bg-red-100 text-red-800':'' }}
            {{ $mau=='gray'?'bg-gray-100 text-gray-800':'' }}">
            {{ $tenTT }}
        </span>
    </div>

    {{-- TIMELINE TRẠNG THÁI --}}
    @php
        $steps = [
            ['key'=>'cho_xu_ly', 'label'=>'Đặt hàng', 'icon'=>'📝'],
            ['key'=>'xuat_kho',  'label'=>'Đang chuẩn bị', 'icon'=>'📦'],
            ['key'=>'van_chuyen','label'=>'Đang giao', 'icon'=>'🚚'],
            ['key'=>'hoan_thanh','label'=>'Hoàn thành', 'icon'=>'✅'],
        ];
        $order = ['cho_xu_ly'=>0,'xuat_kho'=>1,'dong_goi'=>1,'van_chuyen'=>2,'tu_van_chuyen'=>2,'hoan_thanh'=>3,'huy'=>-1];
        $currentStep = $order[$donHang->trang_thai] ?? 0;
    @endphp

    @if($donHang->trang_thai != 'huy')
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
        <div class="flex items-center justify-between relative">
            <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200 z-0 mx-10"></div>
            <div class="absolute top-5 left-0 h-0.5 bg-yellow-400 z-0 mx-10 transition-all"
                 style="width: calc({{ min($currentStep, count($steps)-1) }} / {{ count($steps)-1 }} * (100% - 5rem))"></div>

            @foreach($steps as $i => $step)
            <div class="flex flex-col items-center gap-2 z-10">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg transition-all
                    {{ $i <= $currentStep ? 'shadow-md' : 'bg-gray-100' }}"
                    style="{{ $i <= $currentStep ? 'background: linear-gradient(135deg, #d4af37, #f0d060)' : '' }}">
                    {{ $step['icon'] }}
                </div>
                <span class="text-xs font-medium {{ $i <= $currentStep ? 'text-gray-900' : 'text-gray-400' }} text-center max-w-16">
                    {{ $step['label'] }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- SẢN PHẨM --}}
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 class="font-bold text-gray-900 mb-4">Sản phẩm đã đặt</h2>
            <div class="space-y-4">
                @foreach($donHang->chiTietDonHangs as $ct)
                @php
                    $anhRaw4 = $ct->sanPham?->anh_san_pham;
                    $anhArr4 = is_string($anhRaw4) ? (json_decode($anhRaw4, true) ?? [$anhRaw4]) : (is_array($anhRaw4) ? $anhRaw4 : []);
                    $anh4 = $anhArr4[0] ?? null;
                @endphp
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 shrink-0">
                        @if($anh4)
                            <img src="{{ asset('storage/uploads/sanpham/' . $anh4) }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-2xl text-gray-300">👕</div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 text-sm">{{ $ct->sanPham?->ten ?? 'Sản phẩm' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ number_format($ct->don_gia, 0, ',', '.') }}đ × {{ $ct->so_luong }}</p>
                    </div>
                    <div class="font-bold text-gray-900 text-sm">{{ number_format($ct->thanh_tien, 0, ',', '.') }}đ</div>
                </div>
                @endforeach

                <hr class="border-gray-100">
                <div class="space-y-1.5">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Tạm tính</span>
                        <span>{{ number_format($donHang->tong_tien, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Vận chuyển</span>
                        <span class="text-green-600">Miễn phí</span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-900 pt-1">
                        <span>Tổng cộng</span>
                        <span class="text-lg" style="color:#1a1a2e">{{ number_format($donHang->tien_thanh_toan, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- THÔNG TIN ĐƠN --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-900 mb-3 text-sm">Thông tin giao hàng</h3>
                @php $dcgh = is_string($donHang->dia_chi_giao_hang) ? json_decode($donHang->dia_chi_giao_hang, true) : []; @endphp
                <div class="text-sm space-y-1.5 text-gray-600">
                    <div><span class="font-medium text-gray-900">{{ $dcgh['ten'] ?? '' }}</span></div>
                    <div>📞 {{ $dcgh['sdt'] ?? '' }}</div>
                    <div>📍 {{ implode(', ', array_filter([$dcgh['dia_chi'] ?? '', $dcgh['phuong_xa'] ?? '', $dcgh['quan_huyen'] ?? '', $dcgh['tinh_thanh'] ?? ''])) }}</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-900 mb-3 text-sm">Thanh toán</h3>
                <div class="text-sm text-gray-600">
                    <div class="bg-blue-50 rounded-xl p-3 text-blue-800 text-xs font-medium">
                        💬 {{ $donHang->ghi_chu ?? 'Không có ghi chú' }}
                    </div>
                    @if($donHang->da_thanh_toan > 0)
                    <div class="mt-3 flex justify-between">
                        <span>Đã thanh toán</span>
                        <span class="font-medium text-green-600">{{ number_format($donHang->da_thanh_toan, 0, ',', '.') }}đ</span>
                    </div>
                    @endif
                    @if($donHang->con_phai_tra > 0)
                    <div class="mt-1 flex justify-between">
                        <span>Còn lại</span>
                        <span class="font-medium text-red-600">{{ number_format($donHang->con_phai_tra, 0, ',', '.') }}đ</span>
                    </div>
                    @endif
                </div>
            </div>

            <a href="/don-hang" class="block text-center text-sm text-gray-500 hover:text-gray-700 py-2 transition-colors">
                ← Quay lại danh sách đơn hàng
            </a>
        </div>
    </div>
</div>
@endsection
