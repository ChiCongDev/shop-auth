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
    duyet_don_order: 'Duyệt đơn',
    bao_hang_ve_order: 'Báo hàng về',
    bao_hang_ve_order_mot_phan: 'Báo hàng về 1 phần',
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
    return value ? new Date(value).toLocaleString('vi-VN') : '-';
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
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function badge(status) {
    const cls = {
        cho_xu_ly: 'bg-yellow-100 text-yellow-800',
        duyet_don_order: 'bg-blue-100 text-blue-800',
        bao_hang_ve_order: 'bg-green-100 text-green-800',
        bao_hang_ve_order_mot_phan: 'bg-amber-100 text-amber-800',
        xuat_kho: 'bg-green-100 text-green-800',
        dong_goi: 'bg-emerald-100 text-emerald-800',
        van_chuyen: 'bg-purple-100 text-purple-800',
        tu_van_chuyen: 'bg-teal-100 text-teal-800',
        hoan_thanh: 'bg-green-100 text-green-800',
        huy: 'bg-red-100 text-red-800',
    }[status] || 'bg-gray-100 text-gray-700';

    return `<span class="inline-flex rounded-md px-3 py-1 text-xs font-semibold ${cls}">${escapeHtml(statusText[status] || status || '-')}</span>`;
}

function syncUrl() {
    const params = new URLSearchParams();
    if (state.page > 1) params.set('page', state.page);
    if (state.search) params.set('search', state.search);
    if (state.ngayTao) params.set('ngay_tao', state.ngayTao);
    if (state.tuNgay) params.set('tu_ngay', state.tuNgay);
    if (state.denNgay) params.set('den_ngay', state.denNgay);
    if (state.trangThai) params.set('trang_thai', state.trangThai);
    if (state.khachHang) params.set('khach_hang', state.khachHang);
    if (state.sanPham) params.set('san_pham', state.sanPham);

    const query = params.toString();
    window.history.replaceState({}, '', `${window.location.pathname}${query ? `?${query}` : ''}`);
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

    document.getElementById('input-search-order').value = state.search;
    document.getElementById('filter-ngay-tao').value = state.ngayTao;
    document.getElementById('filter-tu-ngay').value = state.tuNgay;
    document.getElementById('filter-den-ngay').value = state.denNgay;
    document.getElementById('filter-trang-thai').value = state.trangThai;
    document.getElementById('filter-khach-hang').value = state.khachHang;
    document.getElementById('filter-san-pham').value = state.sanPham;
    toggleCustomDate();
    updateStatusCards();
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

function buildParams(page) {
    const params = new URLSearchParams({
        page,
        per_page: state.perPage,
    });
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

async function taiDanhSach(page = 1) {
    state.page = page;
    syncUrl();
    updateStatusCards();
    document.getElementById('loading-order').classList.remove('hidden');
    document.getElementById('empty-order').classList.add('hidden');
    document.getElementById('tbody-order').innerHTML = '';

    try {
        const res = await fetch(`/api/doi-tac/order-hang/danh-sach-don-order?${buildParams(page)}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        if (!data.success) {
            throw new Error(data.message || 'Không thể tải danh sách đơn order');
        }

        renderStats(data.stats || {});
        renderDanhSach(data.data || []);
        renderPagination(data.pagination);
    } catch (error) {
        showToast('Lỗi', error.message || 'Không thể tải danh sách đơn order');
    } finally {
        document.getElementById('loading-order').classList.add('hidden');
    }
}

function renderStats(stats) {
    Object.keys(statusText).forEach(status => {
        const count = document.getElementById(`stat-${status}`);
        const money = document.getElementById(`stat-${status}-tien`);
        if (count) count.textContent = formatNumber(stats[status]?.so_luong || 0);
        if (money) money.textContent = formatCurrency(stats[status]?.tong_tien || 0);
    });
}

function renderDanhSach(items) {
    const tbody = document.getElementById('tbody-order');
    if (!items.length) {
        document.getElementById('empty-order').classList.remove('hidden');
        return;
    }

    tbody.innerHTML = items.map(item => {
        const detailUrl = `/doi-tac/order-hang/don-ban/${item.id}`;
        const detailClass = 'font-bold text-blue-600 hover:underline';
        return `<tr class="transition hover:bg-gray-50">
            <td class="px-4 py-4 text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </td>
            <td class="px-4 py-4">
                <div class="flex items-center gap-2">
                    <a href="${detailUrl}" class="${detailClass}">${escapeHtml(item.ma_don_hang)}</a>
                    <span title="Tạo từ order" class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-700">Order</span>
                </div>
            </td>
            <td class="px-4 py-4 text-gray-700">${formatDate(item.ngay_dat || item.created_at)}</td>
            <td class="px-4 py-4">
                <div class="font-semibold text-gray-900">${escapeHtml(item.khach_hang?.ten || 'Khách lẻ')}</div>
                <div class="mt-1 text-xs text-gray-500">${escapeHtml(item.khach_hang?.sdt || '')}</div>
            </td>
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
    const wrap = document.getElementById('pagination-order');
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
    document.getElementById('input-search-order').value = state.search;
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

document.getElementById('input-search-order')?.addEventListener('input', debounce(e => {
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
