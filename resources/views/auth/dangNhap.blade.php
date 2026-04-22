@extends('layouts.app')
@section('title', 'Đăng nhập')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-2" style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                <div class="bg-white rounded-2xl p-8">
                    <div class="text-center mb-8">
                        <a href="/" class="inline-flex items-center gap-2 mb-6">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                                <span class="text-white font-bold">SA</span>
                            </div>
                            <span class="font-bold text-xl text-gray-900">Shop <span style="color:#d4af37">Auth</span></span>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">Chào mừng trở lại!</h1>
                        <p class="text-gray-500 text-sm mt-1">Đăng nhập để tiếp tục mua sắm</p>
                    </div>

                    <form method="POST" action="/dang-nhap" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent transition-all"
                                style="focus:ring-color: #d4af37"
                                placeholder="email@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                            <input type="password" name="mat_khau" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 transition-all"
                                placeholder="••••••••">
                        </div>
                        <button type="submit" class="w-full py-3 rounded-xl font-semibold text-white transition-all hover:opacity-90 hover:shadow-lg mt-2"
                            style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                            Đăng nhập
                        </button>
                    </form>

                    <p class="text-center text-sm text-gray-500 mt-6">
                        Chưa có tài khoản?
                        <a href="/dang-ky" class="font-semibold hover:opacity-80 transition-opacity" style="color:#d4af37">Đăng ký ngay</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
