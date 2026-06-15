@extends('layouts.app')
@section('title', 'Đơn hàng')

@section('content')
<div id="danh-sach-don-hang-doi-tac" class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide" style="color:#d4af37">Theo dõi bán hàng</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Đơn hàng</h1>
            <p class="mt-1 text-sm text-gray-500">Danh sách đơn thường được lấy từ hệ thống quản lý nội bộ.</p>
        </div>
        <a href="/doi-tac/order-hang/danh-sach" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Quản lý hàng order
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        @foreach([
            ['ma' => 'cho_xu_ly', 'ten' => 'Chờ xử lý', 'lop' => 'bg-yellow-100 text-yellow-600'],
            ['ma' => 'xuat_kho', 'ten' => 'Xuất kho', 'lop' => 'bg-green-100 text-green-600'],
            ['ma' => 'dong_goi', 'ten' => 'Đóng gói', 'lop' => 'bg-green-100 text-green-600'],
            ['ma' => 'van_chuyen', 'ten' => 'Shipper đã lấy hàng', 'lop' => 'bg-purple-100 text-purple-600'],
            ['ma' => 'hoan_thanh', 'ten' => 'Khách đã nhận hàng', 'lop' => 'bg-green-100 text-green-600'],
            ['ma' => 'huy', 'ten' => 'Đã hủy', 'lop' => 'bg-red-100 text-red-600'],
        ] as $item)
        <button type="button" data-status-card="{{ $item['ma'] }}" class="stat-card flex min-h-[120px] flex-col justify-between rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-yellow-300 hover:shadow-md">
            <div class="flex items-start justify-between gap-3">
                <div class="min-h-[40px] text-sm font-semibold leading-5 text-gray-500">{{ $item['ten'] }}</div>
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $item['lop'] }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-7 4h8m-8 4h5m-7 8h12a2 2 0 002-2V7.5a2 2 0 00-.59-1.41l-3.5-3.5A2 2 0 0014.5 2H6a2 2 0 00-2 2v15a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div id="stat-{{ $item['ma'] }}" class="text-2xl font-bold leading-8 text-gray-900">0</div>
                <div id="stat-{{ $item['ma'] }}-tien" class="mt-1 whitespace-nowrap text-sm font-medium text-gray-500">0</div>
            </div>
        </button>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200">
            <button type="button" class="border-b-2 px-6 py-3 text-sm font-bold" style="border-color:#d4af37;color:#1a1a2e">
                Tất cả đơn hàng
            </button>
        </div>

        <div class="border-b border-gray-100 p-4">
            <div class="flex flex-col gap-3 xl:flex-row">
                <div class="relative flex-1">
                    <input id="input-search-don-hang" class="w-full rounded-lg border border-gray-200 py-3 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Tìm theo mã đơn, tên hoặc SĐT khách hàng">
                    <svg class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select id="filter-ngay-tao" class="rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 xl:w-44">
                    <option value="">Tất cả thời gian</option>
                    <option value="today">Hôm nay</option>
                    <option value="yesterday">Hôm qua</option>
                    <option value="7days">7 ngày qua</option>
                    <option value="this_week">Tuần này</option>
                    <option value="30days">30 ngày qua</option>
                    <option value="this_month">Tháng này</option>
                    <option value="this_year">Năm nay</option>
                    <option value="custom">Tùy chỉnh</option>
                </select>
                <select id="filter-trang-thai" class="rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 xl:w-44">
                    <option value="">Trạng thái</option>
                    <option value="cho_xu_ly">Chờ xử lý</option>
                    <option value="xuat_kho">Xuất kho</option>
                    <option value="dong_goi">Đóng gói</option>
                    <option value="van_chuyen">Shipper đã lấy hàng</option>
                    <option value="tu_van_chuyen">Tự vận chuyển</option>
                    <option value="hoan_thanh">Khách đã nhận hàng</option>
                    <option value="huy">Đã hủy</option>
                </select>
                <input id="filter-khach-hang" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 xl:w-44" placeholder="Mã/tên khách">
                <input id="filter-san-pham" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 xl:w-44" placeholder="SKU/tên SP">
            </div>
            <div id="custom-date-range" class="mt-3 hidden flex-col gap-3 sm:flex-row sm:items-center">
                <input type="date" id="filter-tu-ngay" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <span class="text-sm text-gray-400">đến</span>
                <input type="date" id="filter-den-ngay" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <button id="btn-xoa-bo-loc" type="button" class="rounded-lg px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-50">Xóa lọc</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Mã đơn hàng</th>
                        <th class="px-4 py-3 text-left">Ngày tạo</th>
                        <th class="px-4 py-3 text-left">Khách hàng</th>
                        <th class="px-4 py-3 text-left">Nhân viên</th>
                        <th class="px-4 py-3 text-left">Trạng thái</th>
                        <th class="px-4 py-3 text-right">Khách phải trả</th>
                        <th class="w-20 px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody id="tbody-don-hang" class="divide-y divide-gray-100 bg-white"></tbody>
            </table>
        </div>

        <div id="loading-don-hang" class="py-12 text-center text-sm text-gray-500">Đang tải danh sách đơn hàng...</div>
        <div id="empty-don-hang" class="hidden py-12 text-center text-sm text-gray-500">Không có đơn hàng phù hợp.</div>
        <div id="pagination-don-hang" class="hidden border-t border-gray-100 bg-gray-50 px-4 py-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <span id="pagination-info" class="text-sm text-gray-500"></span>
                <div id="pagination-buttons" class="flex flex-wrap items-center justify-end gap-1.5"></div>
            </div>
        </div>
    </div>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[70] min-w-80 rounded-xl border border-gray-100 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-gray-900"></p>
    <p id="toast-message" class="mt-1 text-sm text-gray-600"></p>
</div>

<script>
const state = {
    page: 1,
    perPage: 15,
    search: '',
    ngayTao: '',
    tuNgay: '',
    denNgay: '',
    trangThai: '',
    khachHang: '',
    sanPham: '',
};

const statusText = {
    cho_xu_ly: 'Chờ xử lý',
    xuat_kho: 'Xuất kho',
    dong_goi: 'Đóng gói',
    van_chuyen: 'Shipper đã lấy hàng',
    tu_van_chuyen: 'Tự vận chuyển',
    hoan_thanh: 'Khách đã nhận hàng',
    huy: 'Đã hủy',
};

function debounce(fn, wait) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function formatDate(value) {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '-';
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

function formatNumber(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
}

function formatCurrency(value) {
    return `${formatNumber(value)}đ`;
}

function showToast(title, message) {
    const toast = document.getElementById('toast-notification');
    if (!toast) return;
    document.getElementById('toast-title').textContent = title;
    document.getElementById('toast-message').textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3500);
}

function badge(status) {
    const style = {
        cho_xu_ly: 'background:#FEF3C7;color:#92400E',
        xuat_kho: 'background:#D1FAE5;color:#065F46',
        dong_goi: 'background:#D1FAE5;color:#065F46',
        van_chuyen: 'background:#EDE9FE;color:#5B21B6',
        tu_van_chuyen: 'background:#CCFBF1;color:#115E59',
        hoan_thanh: 'background:#D1FAE5;color:#065F46',
        huy: 'background:#FEE2E2;color:#991B1B',
    }[status] || 'background:#F3F4F6;color:#374151';

    return `<span class="inline-flex rounded-md px-3 py-1 text-xs font-semibold" style="${style}">${escapeHtml(statusText[status] || status || '-')}</span>`;
}

function buildParams(page) {
    const params = new URLSearchParams({ page, per_page: state.perPage });
    if (state.search) params.set('search', state.search);
    if (state.ngayTao) params.set('ngay_tao', state.ngayTao);
    if (state.ngayTao === 'custom') {
        if (state.tuNgay) params.set('tu_ngay', state.tuNgay);
        if (state.denNgay) params.set('den_ngay', state.denNgay);
    }
    if (state.trangThai) params.set('trang_thai', state.trangThai);
    if (state.khachHang) params.set('khach_hang', state.khachHang);
    if (state.sanPham) params.set('san_pham', state.sanPham);
    return params;
}

function syncUrl() {
    const params = buildParams(state.page);
    params.delete('per_page');
    const query = params.toString();
    window.history.replaceState({}, '', `${window.location.pathname}${query ? `?${query}` : ''}`);
}

function toggleCustomDate() {
    document.getElementById('custom-date-range')?.classList.toggle('hidden', state.ngayTao !== 'custom');
}

function updateStatusCards() {
    document.querySelectorAll('[data-status-card]').forEach(card => {
        const active = card.dataset.statusCard === state.trangThai;
        card.classList.toggle('border-yellow-400', active);
        card.classList.toggle('shadow-md', active);
        card.classList.toggle('bg-yellow-50', active);
    });
}

function renderStats(stats) {
    Object.keys(statusText).forEach(status => {
        const count = document.getElementById(`stat-${status}`);
        const money = document.getElementById(`stat-${status}-tien`);
        if (count) count.textContent = formatNumber(stats?.[status]?.so_luong || 0);
        if (money) money.textContent = formatCurrency(stats?.[status]?.tong_tien || 0);
    });
}

function renderDanhSach(items) {
    const tbody = document.getElementById('tbody-don-hang');
    if (!items.length) {
        document.getElementById('empty-don-hang').classList.remove('hidden');
        return;
    }

    tbody.innerHTML = items.map(item => {
        const detailUrl = `/doi-tac/don-hang/${item.id}`;
        return `<tr class="transition hover:bg-gray-50">
            <td class="px-4 py-4">
                <a href="${detailUrl}" class="font-bold text-blue-600 hover:underline">${escapeHtml(item.ma_don_hang)}</a>
            </td>
            <td class="px-4 py-4 text-gray-700">${formatDate(item.ngay_dat || item.created_at)}</td>
            <td class="px-4 py-4">
                <div class="font-semibold text-gray-900">${escapeHtml(item.khach_hang?.ten || 'Khách lẻ')}</div>
                <div class="mt-1 text-xs text-gray-500">${escapeHtml(item.khach_hang?.sdt || '')}</div>
            </td>
            <td class="px-4 py-4 text-gray-700">${escapeHtml(item.nhan_vien?.ten || '-')}</td>
            <td class="px-4 py-4">${badge(item.trang_thai_hien_thi || item.trang_thai)}</td>
            <td class="px-4 py-4 text-right font-bold text-gray-900">${formatCurrency(item.tien_thanh_toan)}</td>
            <td class="px-4 py-4 text-right">
                <a href="${detailUrl}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Xem</a>
            </td>
        </tr>`;
    }).join('');
}

function pageButton(label, page, options = {}) {
    const disabled = options.disabled ? 'disabled' : '';
    const cls = options.active
        ? 'border-yellow-500 text-white'
        : options.disabled
            ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400'
            : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50';
    const style = options.active ? 'style="background:#d4af37"' : '';
    return `<button ${disabled} ${style} onclick="${options.disabled ? '' : `taiDanhSach(${page})`}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-semibold ${cls}">${label}</button>`;
}

function renderPagination(p) {
    const wrap = document.getElementById('pagination-don-hang');
    if (!p || p.total === 0) {
        wrap.classList.add('hidden');
        return;
    }
    wrap.classList.remove('hidden');
    const from = p.from || ((p.current_page - 1) * p.per_page + 1);
    const to = p.to || Math.min(p.current_page * p.per_page, p.total);
    document.getElementById('pagination-info').textContent = `Hiển thị ${from}-${to} / ${p.total} đơn`;

    const pages = new Set([1, p.last_page, p.current_page - 1, p.current_page, p.current_page + 1]);
    let last = 0;
    const buttons = [pageButton('Trước', Math.max(1, p.current_page - 1), { disabled: p.current_page <= 1 })];
    [...pages]
        .filter(page => page >= 1 && page <= p.last_page)
        .sort((a, b) => a - b)
        .forEach(page => {
            if (last && page - last > 1) {
                buttons.push('<span class="inline-flex h-9 items-center px-2 text-sm text-gray-400">...</span>');
            }
            buttons.push(pageButton(page, page, { active: page === p.current_page }));
            last = page;
        });
    buttons.push(pageButton('Sau', Math.min(p.last_page, p.current_page + 1), { disabled: p.current_page >= p.last_page }));
    document.getElementById('pagination-buttons').innerHTML = buttons.join('');
}

async function taiDanhSach(page = 1) {
    state.page = page;
    syncUrl();
    updateStatusCards();
    document.getElementById('loading-don-hang').classList.remove('hidden');
    document.getElementById('empty-don-hang').classList.add('hidden');
    document.getElementById('tbody-don-hang').innerHTML = '';

    try {
        const res = await fetch(`/api/doi-tac/don-hang/danh-sach?${buildParams(page)}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Không thể tải danh sách đơn hàng');
        }

        renderStats(data.stats || {});
        renderDanhSach(data.data || []);
        renderPagination(data.pagination);
    } catch (error) {
        showToast('Lỗi', error.message || 'Không thể tải danh sách đơn hàng');
    } finally {
        document.getElementById('loading-don-hang').classList.add('hidden');
    }
}

function readUrlState() {
    const params = new URLSearchParams(window.location.search);
    state.page = Math.max(1, Number(params.get('page') || 1));
    state.search = params.get('search') || '';
    state.ngayTao = params.get('ngay_tao') || '';
    state.tuNgay = params.get('tu_ngay') || '';
    state.denNgay = params.get('den_ngay') || '';
    state.trangThai = params.get('trang_thai') || '';
    state.khachHang = params.get('khach_hang') || '';
    state.sanPham = params.get('san_pham') || '';

    document.getElementById('input-search-don-hang').value = state.search;
    document.getElementById('filter-ngay-tao').value = state.ngayTao;
    document.getElementById('filter-tu-ngay').value = state.tuNgay;
    document.getElementById('filter-den-ngay').value = state.denNgay;
    document.getElementById('filter-trang-thai').value = state.trangThai;
    document.getElementById('filter-khach-hang').value = state.khachHang;
    document.getElementById('filter-san-pham').value = state.sanPham;
    toggleCustomDate();
    updateStatusCards();
}

function resetFilters() {
    state.search = '';
    state.ngayTao = '';
    state.tuNgay = '';
    state.denNgay = '';
    state.trangThai = '';
    state.khachHang = '';
    state.sanPham = '';
    readUrlStateFromState();
    taiDanhSach(1);
}

function readUrlStateFromState() {
    document.getElementById('input-search-don-hang').value = state.search;
    document.getElementById('filter-ngay-tao').value = state.ngayTao;
    document.getElementById('filter-tu-ngay').value = state.tuNgay;
    document.getElementById('filter-den-ngay').value = state.denNgay;
    document.getElementById('filter-trang-thai').value = state.trangThai;
    document.getElementById('filter-khach-hang').value = state.khachHang;
    document.getElementById('filter-san-pham').value = state.sanPham;
    toggleCustomDate();
    updateStatusCards();
}

window.taiDanhSach = taiDanhSach;

readUrlState();

document.getElementById('input-search-don-hang')?.addEventListener('input', debounce(e => {
    state.search = e.target.value;
    taiDanhSach(1);
}, 300));

document.getElementById('filter-ngay-tao')?.addEventListener('change', e => {
    state.ngayTao = e.target.value;
    toggleCustomDate();
    taiDanhSach(1);
});

document.getElementById('filter-tu-ngay')?.addEventListener('change', e => {
    state.tuNgay = e.target.value;
    taiDanhSach(1);
});

document.getElementById('filter-den-ngay')?.addEventListener('change', e => {
    state.denNgay = e.target.value;
    taiDanhSach(1);
});

document.getElementById('filter-trang-thai')?.addEventListener('change', e => {
    state.trangThai = e.target.value;
    taiDanhSach(1);
});

document.getElementById('filter-khach-hang')?.addEventListener('input', debounce(e => {
    state.khachHang = e.target.value;
    taiDanhSach(1);
}, 300));

document.getElementById('filter-san-pham')?.addEventListener('input', debounce(e => {
    state.sanPham = e.target.value;
    taiDanhSach(1);
}, 300));

document.getElementById('btn-xoa-bo-loc')?.addEventListener('click', resetFilters);

document.querySelectorAll('[data-status-card]').forEach(card => {
    card.addEventListener('click', () => {
        state.trangThai = state.trangThai === card.dataset.statusCard ? '' : card.dataset.statusCard;
        document.getElementById('filter-trang-thai').value = state.trangThai;
        taiDanhSach(1);
    });
});

taiDanhSach(state.page);
</script>
@endsection
