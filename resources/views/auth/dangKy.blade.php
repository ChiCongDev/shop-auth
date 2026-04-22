@extends('layouts.app')
@section('title', 'Đăng ký tài khoản')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-2" style="background: linear-gradient(135deg, #d4af37, #1a1a2e)">
                <div class="bg-white rounded-2xl p-8">
                    <div class="text-center mb-8">
                        <a href="/" class="inline-flex items-center gap-2 mb-6">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                                <span class="text-white font-bold">SA</span>
                            </div>
                            <span class="font-bold text-xl text-gray-900">Shop <span style="color:#d4af37">Auth</span></span>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">Tạo tài khoản mới</h1>
                        <p class="text-gray-500 text-sm mt-1">Đăng ký để bắt đầu mua sắm</p>
                    </div>

                    <form method="POST" action="/dang-ky" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                            <input type="text" name="ten" value="{{ old('ten') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all"
                                placeholder="Nguyễn Văn A">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                            <input type="tel" name="sdt" value="{{ old('sdt') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all"
                                placeholder="0912345678">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all"
                                placeholder="email@example.com">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                                <input type="password" name="mat_khau" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all"
                                    placeholder="••••••••">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận</label>
                                <input type="password" name="xac_nhan" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all"
                                    placeholder="••••••••">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-3 rounded-xl font-semibold text-gray-900 transition-all hover:opacity-90 hover:shadow-lg mt-2"
                            style="background: linear-gradient(135deg, #d4af37, #f0d060)">
                            🎉 Đăng ký ngay
                        </button>
                    </form>

                    <p class="text-center text-sm text-gray-500 mt-6">
                        Đã có tài khoản?
                        <a href="/dang-nhap" class="font-semibold hover:opacity-80 transition-opacity" style="color:#1a1a2e">Đăng nhập</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
