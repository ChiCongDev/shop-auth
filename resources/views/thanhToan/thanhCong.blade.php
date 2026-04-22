@extends('layouts.app')
@section('title', 'Đặt hàng thành công!')

@section('content')
<div class="max-w-xl mx-auto px-4 py-16 text-center">
    <div class="bg-white rounded-3xl p-10 shadow-sm border border-gray-100">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6" style="background: linear-gradient(135deg, #d4af37, #f0d060)">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Đặt hàng thành công! 🎉</h1>
        <p class="text-gray-500 mb-6">Cảm ơn bạn đã mua sắm tại <strong>Shop Auth</strong></p>

        <div class="bg-gray-50 rounded-2xl p-5 mb-6 text-left">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Mã đơn hàng</div>
            <div class="text-xl font-bold" style="color:#1a1a2e">{{ $maDonHang }}</div>
        </div>

        <div class="space-y-3 text-sm text-gray-600 mb-8">
            <div class="flex items-center gap-3 bg-blue-50 rounded-xl p-3">
                <span class="text-2xl">📦</span>
                <div class="text-left">
                    <p class="font-medium text-gray-900">Đơn hàng đang được xử lý</p>
                    <p class="text-xs text-gray-500">Chúng tôi sẽ liên hệ xác nhận trong 30 phút</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-green-50 rounded-xl p-3">
                <span class="text-2xl">🚚</span>
                <div class="text-left">
                    <p class="font-medium text-gray-900">Giao hàng trong 1-3 ngày làm việc</p>
                    <p class="text-xs text-gray-500">Shop Auth tự vận chuyển đến tận nơi</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="/don-hang" class="flex-1 py-3 rounded-xl font-semibold text-white text-center transition-all hover:opacity-90"
               style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                Xem đơn hàng
            </a>
            <a href="/san-pham" class="flex-1 py-3 rounded-xl font-semibold text-gray-700 border-2 border-gray-200 text-center hover:border-gray-400 transition-all">
                Tiếp tục mua
            </a>
        </div>
    </div>
</div>
@endsection
