@extends('layouts.app')
@section('title', 'Tài khoản của tôi')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-8">Tài khoản của tôi</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- SIDEBAR --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 text-center mb-4">
                <div class="w-20 h-20 rounded-full mx-auto mb-3 flex items-center justify-center text-white text-2xl font-bold"
                     style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                    {{ mb_substr($khachHang->ten, 0, 1) }}
                </div>
                <div class="font-bold text-gray-900 text-lg">{{ $khachHang->ten }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ $khachHang->email }}</div>
                <div class="mt-3 inline-block px-3 py-1 text-xs font-medium rounded-full" style="background:rgba(212,175,55,0.15); color:#d4af37">
                    Khách hàng Shop Auth
                </div>
            </div>

            {{-- Nav links --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                @foreach([
                    ['href'=>'#thong-tin',   'icon'=>'👤', 'label'=>'Thông tin cá nhân'],
                    ['href'=>'#mat-khau',    'icon'=>'🔒', 'label'=>'Đổi mật khẩu'],
                    ['href'=>'#dia-chi',     'icon'=>'📍', 'label'=>'Địa chỉ giao hàng'],
                    ['href'=>'/don-hang',    'icon'=>'📦', 'label'=>'Đơn hàng của tôi'],
                ] as $nav)
                <a href="{{ $nav['href'] }}" class="flex items-center gap-3 px-5 py-3.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0">
                    <span>{{ $nav['icon'] }}</span>
                    <span class="font-medium">{{ $nav['label'] }}</span>
                    <svg class="w-4 h-4 text-gray-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach
            </div>
        </div>

        {{-- NỘI DUNG CHÍNH --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- THÔNG TIN CÁ NHÂN --}}
            <div id="thong-tin" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span>👤</span> Thông tin cá nhân
                </h2>
                <form method="POST" action="/taiKhoan/cap-nhat" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                            <input type="text" name="ten" value="{{ $khachHang->ten }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                            <input type="tel" name="sdt" value="{{ $khachHang->sdt }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" value="{{ $khachHang->email }}" disabled
                                class="w-full px-4 py-3 border border-gray-100 rounded-xl text-sm text-gray-400 bg-gray-50 cursor-not-allowed">
                            <p class="text-xs text-gray-400 mt-1">Email không thể thay đổi</p>
                        </div>
                    </div>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                        style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                        Lưu thay đổi
                    </button>
                </form>
            </div>

            {{-- ĐỔI MẬT KHẨU --}}
            <div id="mat-khau" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span>🔒</span> Đổi mật khẩu
                </h2>
                @if(session('loi_matkhau'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
                    ❌ {{ session('loi_matkhau') }}
                </div>
                @endif
                <form method="POST" action="/taiKhoan/doi-mat-khau" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu hiện tại</label>
                        <input type="password" name="mat_khau_cu" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                            <input type="password" name="mat_khau_moi" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu</label>
                            <input type="password" name="xac_nhan" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                    </div>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-red-600 border-2 border-red-200 hover:bg-red-50 transition-all">
                        Đổi mật khẩu
                    </button>
                </form>
            </div>

            {{-- ĐỊA CHỈ --}}
            <div id="dia-chi" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h2 class="font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <span>📍</span> Địa chỉ giao hàng
                </h2>

                {{-- Danh sách địa chỉ --}}
                @if($khachHang->diaChis->isNotEmpty())
                <div class="space-y-3 mb-5">
                    @foreach($khachHang->diaChis as $dc)
                    <div class="flex items-start justify-between p-4 border-2 {{ $dc->la_mac_dinh ? 'border-yellow-400 bg-yellow-50' : 'border-gray-100' }} rounded-xl gap-4">
                        <div class="text-sm flex-1">
                            @if($dc->la_mac_dinh)
                                <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full mb-2" style="background:rgba(212,175,55,0.15); color:#d4af37">Mặc định</span>
                            @endif
                            <div class="font-medium text-gray-900">{{ $dc->dia_chi }}</div>
                            <div class="text-gray-500 text-xs mt-0.5">
                                {{ implode(', ', array_filter([$dc->phuong_xa, $dc->quan_huyen, $dc->tinh_thanh])) }}
                            </div>
                        </div>
                        <form method="POST" action="/taiKhoan/dia-chi/{{ $dc->id }}/xoa" onsubmit="return confirm('Xóa địa chỉ này?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors p-1">✕</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Form thêm địa chỉ mới --}}
                <details class="group">
                    <summary class="cursor-pointer text-sm font-semibold flex items-center gap-2 text-gray-700 hover:text-gray-900 list-none">
                        <span class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center text-xs group-open:bg-gray-900 group-open:border-gray-900 group-open:text-white transition-all">+</span>
                        Thêm địa chỉ mới
                    </summary>
                    <form method="POST" action="/taiKhoan/dia-chi" class="mt-4 space-y-3">
                        @csrf
                        <input type="text" name="dia_chi" placeholder="Số nhà, tên đường *" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" name="phuong_xa" placeholder="Phường/Xã"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                            <input type="text" name="quan_huyen" placeholder="Quận/Huyện"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        </div>
                        <input type="text" name="tinh_thanh" placeholder="Tỉnh/Thành phố *" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="la_mac_dinh" class="rounded">
                            Đặt làm địa chỉ mặc định
                        </label>
                        <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                            style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                            Lưu địa chỉ
                        </button>
                    </form>
                </details>
            </div>

            {{-- ĐƠN HÀNG GẦN ĐÂY --}}
            @if($khachHang->donHangs->isNotEmpty())
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-900 flex items-center gap-2"><span>📦</span> Đơn hàng gần đây</h2>
                    <a href="/don-hang" class="text-sm font-medium hover:opacity-80 transition-opacity" style="color:#d4af37">Xem tất cả →</a>
                </div>
                <div class="space-y-3">
                    @foreach($khachHang->donHangs as $dh)
                    @php
                        $mauMap = ['cho_xu_ly'=>'yellow','xuat_kho'=>'blue','dong_goi'=>'blue','van_chuyen'=>'purple','tu_van_chuyen'=>'purple','hoan_thanh'=>'green','huy'=>'red'];
                        $mau = $mauMap[$dh->trang_thai] ?? 'gray';
                    @endphp
                    <a href="/don-hang/{{ $dh->id }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors">
                        <div>
                            <div class="text-sm font-bold text-gray-900">{{ $dh->ma_don_hang }}</div>
                            <div class="text-xs text-gray-400">{{ $dh->created_at->format('d/m/Y') }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold" style="color:#1a1a2e">{{ number_format($dh->tien_thanh_toan, 0, ',', '.') }}đ</span>
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full
                                {{ $mau=='yellow'?'bg-yellow-100 text-yellow-800':'' }}
                                {{ $mau=='blue'?'bg-blue-100 text-blue-800':'' }}
                                {{ $mau=='green'?'bg-green-100 text-green-800':'' }}
                                {{ $mau=='red'?'bg-red-100 text-red-800':'' }}
                                {{ $mau=='purple'?'bg-purple-100 text-purple-800':'' }}
                                {{ $mau=='gray'?'bg-gray-100 text-gray-800':'' }}">
                                {{ match($dh->trang_thai) { 'cho_xu_ly'=>'Chờ xử lý','hoan_thanh'=>'Hoàn thành','huy'=>'Đã hủy',default=>ucfirst($dh->trang_thai) } }}
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
