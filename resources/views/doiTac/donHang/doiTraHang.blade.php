@extends('layouts.app')
@section('title', 'Đổi/trả hàng ' . ($donHang['ma_don_hang'] ?? ''))
@section('hideFooter', true)

@php
    $chiTiets = collect($donHang['chi_tiets'] ?? []);
    $khachHang = $donHang['khach_hang'] ?? [];
    $trangThaiMap = [
        'xuat_kho' => ['Xuất kho', 'text-blue-700'],
        'dong_goi' => ['Đóng gói', 'text-indigo-700'],
        'van_chuyen' => ['Shipper đã lấy hàng', 'text-purple-700'],
        'tu_van_chuyen' => ['Tự vận chuyển', 'text-teal-700'],
        'hoan_thanh' => ['Khách đã nhận hàng', 'text-emerald-700'],
    ];
    [$tenTrangThai, $lopTrangThai] = $trangThaiMap[$donHang['trang_thai'] ?? ''] ?? [$donHang['trang_thai'] ?? '-', 'text-gray-700'];
@endphp

@section('content')
<div class="bg-gray-50 px-4 pb-10 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <a href="/doi-tac/don-hang/{{ $donHang['id'] ?? 0 }}" class="text-sm font-semibold text-gray-500 hover:text-gray-900">Quay lại chi tiết đơn</a>
                    <h1 class="mt-3 text-2xl font-bold text-gray-950">Đổi/trả hàng</h1>
                    <p class="mt-2 text-sm text-gray-500">
                        Đơn hàng <span class="font-semibold text-gray-900">{{ $donHang['ma_don_hang'] ?? '-' }}</span>
                    </p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm">
                    <div class="font-semibold text-gray-950">{{ $khachHang['ten'] ?? '-' }}</div>
                    <div class="mt-1 text-gray-500">{{ $khachHang['sdt'] ?? '-' }}</div>
                </div>
            </div>
        </section>

        <div id="doi-tra-message" class="hidden rounded-lg border px-4 py-3 text-sm font-semibold"></div>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase text-gray-500">Sản phẩm</p>
                <p class="mt-2 text-2xl font-bold text-gray-950">{{ number_format($chiTiets->count(), 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase text-gray-500">Tổng số lượng</p>
                <p class="mt-2 text-2xl font-bold text-gray-950">{{ number_format($chiTiets->sum(fn($item) => (int) ($item['so_luong'] ?? 0)), 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase text-gray-500">Trạng thái</p>
                <p class="mt-2 text-lg font-bold {{ $lopTrangThai }}">{{ $tenTrangThai }}</p>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
            id="doi-tra-don-thuong"
            data-don-hang-id="{{ (int) ($donHang['id'] ?? 0) }}"
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
                        @foreach($chiTiets as $chiTiet)
                            @php
                                $anhRaw = $chiTiet['anh_san_pham'] ?? null;
                                $anhArr = is_string($anhRaw) ? (json_decode($anhRaw, true) ?: [$anhRaw]) : (is_array($anhRaw) ? $anhRaw : []);
                                $anh = $anhArr[0] ?? null;
                            @endphp
                            <tr
                                data-return-row
                                data-san-pham-id="{{ (int) ($chiTiet['san_pham_id'] ?? 0) }}"
                                data-chi-tiet-don-hang-id="{{ (int) ($chiTiet['id'] ?? 0) }}"
                                data-ten-san-pham="{{ $chiTiet['ten'] ?? 'Sản phẩm' }}"
                                data-ma-sku="{{ $chiTiet['ma_sku'] ?? '' }}"
                                data-don-vi-tinh="Chiếc"
                                data-so-luong-ban="{{ (int) ($chiTiet['so_luong'] ?? 0) }}"
                                data-gia-ban="{{ (float) ($chiTiet['gia_ban'] ?? 0) }}"
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
                                            <p class="font-semibold text-gray-950">{{ $chiTiet['ten'] ?? 'Sản phẩm' }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $chiTiet['ma_sku'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center font-semibold text-gray-800">{{ number_format((int) ($chiTiet['so_luong'] ?? 0), 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-center font-semibold text-gray-500" data-da-tra>0</td>
                                <td class="px-4 py-4 text-center font-semibold text-gray-950" data-con-tra>{{ number_format((int) ($chiTiet['so_luong'] ?? 0), 0, ',', '.') }}</td>
                                <td class="px-4 py-4">
                                    <input type="number" min="0" value="0" data-so-luong-tra class="w-full rounded-lg border border-gray-200 px-3 py-2 text-center text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-yellow-400">
                                </td>
                                <td class="px-4 py-4">
                                    <input type="text" value="{{ number_format((float) ($chiTiet['gia_ban'] ?? 0), 0, ',', '.') }}" data-gia-tra class="w-full rounded-lg border border-gray-200 px-3 py-2 text-right text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-yellow-400">
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
<script>
const root = document.getElementById('doi-tra-don-thuong');
const messageBox = document.getElementById('doi-tra-message');

function formatNumber(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
}

function parseMoney(value) {
    return Number(String(value || '').replace(/[^\d]/g, '')) || 0;
}

function hienThongBao(message, type = 'info') {
    if (!messageBox) return;
    const styles = {
        success: 'border-green-200 bg-green-50 text-green-700',
        error: 'border-red-200 bg-red-50 text-red-700',
        info: 'border-sky-200 bg-sky-50 text-sky-700',
    };
    messageBox.className = `rounded-lg border px-4 py-3 text-sm font-semibold ${styles[type] || styles.info}`;
    messageBox.textContent = message;
    messageBox.classList.remove('hidden');
}

function tinhTong() {
    let tongSoLuong = 0;
    let tongTien = 0;
    document.querySelectorAll('[data-return-row]').forEach((row) => {
        const soLuong = Number(row.querySelector('[data-so-luong-tra]')?.value || 0);
        const giaTra = parseMoney(row.querySelector('[data-gia-tra]')?.value || 0);
        tongSoLuong += soLuong;
        tongTien += soLuong * giaTra;
    });
    document.getElementById('tong-so-luong-tra').textContent = formatNumber(tongSoLuong);
    document.getElementById('tong-tien-tra').textContent = formatNumber(tongTien);
}

async function taiSoLuongDaTra() {
    if (!root) return;
    try {
        const response = await fetch(`/api/doi-tac/don-hang/phieu-tra-hang/so-luong-da-tra/${root.dataset.donHangId}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Không tải được số lượng đã trả.');
        }
        const daTra = data.data || {};
        document.querySelectorAll('[data-return-row]').forEach((row) => {
            const sanPhamId = row.dataset.sanPhamId;
            const soLuongBan = Number(row.dataset.soLuongBan || 0);
            const soLuongDaTra = Number(daTra[sanPhamId] || 0);
            const conTra = Math.max(0, soLuongBan - soLuongDaTra);
            row.querySelector('[data-da-tra]').textContent = formatNumber(soLuongDaTra);
            row.querySelector('[data-con-tra]').textContent = formatNumber(conTra);
            const input = row.querySelector('[data-so-luong-tra]');
            input.max = conTra;
            input.disabled = conTra <= 0;
        });
    } catch (error) {
        hienThongBao(error.message || 'Không tải được số lượng đã trả.', 'error');
    }
}

function laySanPhamTra() {
    const sanPhams = [];
    document.querySelectorAll('[data-return-row]').forEach((row) => {
        const soLuong = Number(row.querySelector('[data-so-luong-tra]')?.value || 0);
        const conTra = Number(String(row.querySelector('[data-con-tra]')?.textContent || '0').replace(/[^\d]/g, '')) || 0;
        if (soLuong <= 0) return;
        if (soLuong > conTra) {
            throw new Error(`Số lượng trả của ${row.dataset.tenSanPham} vượt quá số lượng còn được trả.`);
        }
        sanPhams.push({
            san_pham_id: Number(row.dataset.sanPhamId),
            chi_tiet_don_hang_id: Number(row.dataset.chiTietDonHangId),
            ten_san_pham: row.dataset.tenSanPham || '',
            ma_sku: row.dataset.maSku || '',
            don_vi_tinh: row.dataset.donViTinh || 'Chiếc',
            so_luong: soLuong,
            gia_goc: Number(row.dataset.giaBan || 0),
            gia_tra: parseMoney(row.querySelector('[data-gia-tra]')?.value || 0),
            ly_do: row.querySelector('[data-ly-do]')?.value || '',
        });
    });
    return sanPhams;
}

async function taoPhieuTraHang() {
    const button = document.getElementById('btn-tao-phieu-tra');
    try {
        const sanPhams = laySanPhamTra();
        if (!sanPhams.length) {
            hienThongBao('Vui lòng nhập số lượng trả cho ít nhất một sản phẩm.', 'error');
            return;
        }
        if (!window.confirm('Xác nhận tạo phiếu trả hàng?')) {
            return;
        }
        button.disabled = true;
        button.textContent = 'Đang tạo phiếu...';
        const response = await fetch('/api/doi-tac/don-hang/phieu-tra-hang/tao', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                don_hang_id: Number(root.dataset.donHangId),
                ly_do_tra: document.getElementById('ly-do-tra')?.value || '',
                ghi_chu: document.getElementById('ghi-chu-tra')?.value || '',
                san_phams: sanPhams,
            }),
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Không tạo được phiếu trả hàng.');
        }
        hienThongBao(data.message || 'Đã tạo phiếu trả hàng.', 'success');
        window.setTimeout(() => {
            window.location.href = `/doi-tac/don-hang/${root.dataset.donHangId}`;
        }, 900);
    } catch (error) {
        hienThongBao(error.message || 'Không tạo được phiếu trả hàng.', 'error');
    } finally {
        button.disabled = false;
        button.textContent = 'Tạo phiếu trả hàng';
    }
}

document.querySelectorAll('[data-so-luong-tra]').forEach((input) => {
    input.addEventListener('input', () => {
        const max = Number(input.max || 0);
        const value = Math.max(0, Number(input.value || 0));
        input.value = max > 0 ? Math.min(value, max) : 0;
        tinhTong();
    });
});
document.querySelectorAll('[data-gia-tra]').forEach((input) => {
    input.addEventListener('input', () => {
        const raw = parseMoney(input.value);
        input.value = raw ? formatNumber(raw) : '';
        tinhTong();
    });
});
document.getElementById('btn-tao-phieu-tra')?.addEventListener('click', taoPhieuTraHang);
taiSoLuongDaTra();
tinhTong();
</script>
@endpush
