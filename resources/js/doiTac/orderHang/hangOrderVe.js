const state = { page: 1, perPage: 10, search: '', trangThai: '' };

const statusText = {
    dat_truoc: 'Đặt trước',
    hang_co_san: 'Hàng có sẵn',
    ve_mot_phan: 'Về một phần',
    hang_da_ve: 'Hàng đã về',
    san_sang_tao_don_ban: 'Sẵn sàng tạo đơn bán',
    da_chuyen_don_ban: 'Đã chuyển đơn bán',
    da_huy: 'Đã hủy',
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
        dat_truoc: 'border-blue-200 bg-blue-50 text-blue-700',
        hang_co_san: 'border-cyan-200 bg-cyan-50 text-cyan-700',
        ve_mot_phan: 'border-yellow-300 bg-yellow-50 text-yellow-700',
        hang_da_ve: 'border-green-200 bg-green-50 text-green-700',
        san_sang_tao_don_ban: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        da_chuyen_don_ban: 'border-slate-200 bg-slate-100 text-slate-700',
        da_huy: 'border-red-200 bg-red-50 text-red-700',
    }[status] || 'border-gray-200 bg-gray-50 text-gray-700';
    return `<span class="inline-flex min-w-28 justify-center rounded-md border px-3 py-1 text-xs font-semibold ${cls}">${escapeHtml(statusText[status] || status || '-')}</span>`;
}

function syncUrl() {
    const params = new URLSearchParams();
    if (state.page > 1) params.set('page', state.page);
    if (state.search) params.set('search', state.search);
    if (state.trangThai) params.set('trang_thai', state.trangThai);
    const query = params.toString();
    window.history.replaceState({}, '', `${window.location.pathname}${query ? `?${query}` : ''}`);
}

function readUrlState() {
    const params = new URLSearchParams(window.location.search);
    state.page = Math.max(1, Number(params.get('page') || 1));
    state.search = params.get('search') || '';
    state.trangThai = params.get('trang_thai') || '';
    document.getElementById('input-search-hang-ve').value = state.search;
    document.getElementById('select-trang-thai-hang-ve').value = state.trangThai;
    capNhatTrangThaiCard();
}

function capNhatTrangThaiCard() {
    document.querySelectorAll('[data-order-status-card]').forEach(card => {
        const active = card.dataset.orderStatusCard === state.trangThai;
        card.classList.toggle('border-blue-300', active);
        card.classList.toggle('bg-blue-50', active);
        card.classList.toggle('shadow-md', active);
        card.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
}

function locTheoTrangThaiCard(status) {
    state.trangThai = state.trangThai === status ? '' : status;
    const select = document.getElementById('select-trang-thai-hang-ve');
    if (select) select.value = state.trangThai;
    taiDanhSach(1);
}

function buildParams(page) {
    const params = new URLSearchParams({
        page,
        per_page: state.perPage,
    });
    if (state.search) params.set('search', state.search);
    if (state.trangThai) params.set('trang_thai', state.trangThai);
    return params;
}

async function taiDanhSach(page = 1) {
    state.page = page;
    syncUrl();
    capNhatTrangThaiCard();
    document.getElementById('loading-hang-order-ve').classList.remove('hidden');
    document.getElementById('empty-hang-order-ve').classList.add('hidden');
    document.getElementById('tbody-hang-order-ve').innerHTML = '';

    try {
        const res = await fetch(`/api/doi-tac/order-hang/danh-sach?${buildParams(page)}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();

        if (!data.success) {
            throw new Error(data.message || 'Không thể tải danh sách order');
        }

        renderDanhSach(data.data || []);
        renderPagination(data.pagination);
    } catch (error) {
        showToast('Lỗi', error.message || 'Không thể tải danh sách order');
    } finally {
        document.getElementById('loading-hang-order-ve').classList.add('hidden');
    }
}

async function taiThongKeTrangThai() {
    try {
        const res = await fetch('/api/doi-tac/order-hang/thong-ke-trang-thai', {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        if (!data.success) {
            throw new Error(data.message || 'Không thể tải thống kê trạng thái');
        }
        renderThongKeTrangThai(data.data || {});
    } catch (error) {
        console.error('Lỗi tải thống kê order:', error);
    }
}

function renderThongKeTrangThai(stats) {
    Object.keys(statusText).forEach(status => {
        const el = document.getElementById(`stat-order-${status}`);
        if (el) el.textContent = formatNumber(stats[status] || 0);
    });
}

function renderDanhSach(items) {
    const tbody = document.getElementById('tbody-hang-order-ve');
    if (!items.length) {
        document.getElementById('empty-hang-order-ve').classList.remove('hidden');
        return;
    }

    tbody.innerHTML = items.map(item => {
        const url = `/doi-tac/order-hang/chi-tiet/${item.id}`;
        return `<tr class="bg-white transition hover:bg-gray-50">
            <td class="px-5 py-3">
                <a class="font-medium text-blue-600 hover:text-blue-800 hover:underline" href="${url}">Order</a>
            </td>
            <td class="px-4 py-3">
                <div class="font-medium text-gray-900">${escapeHtml(item.khach_hang?.ten || '-')}</div>
                <div class="mt-1 text-xs text-gray-500">${escapeHtml(item.khach_hang?.sdt || '')}</div>
            </td>
            <td class="px-4 py-3 text-gray-800">${escapeHtml(item.nhan_vien?.ten || '-')}</td>
            <td class="px-4 py-3 text-center font-medium text-gray-900">${formatNumber(item.chi_tiets_count || 0)}</td>
            <td class="px-4 py-3 text-center">${badge(item.trang_thai)}</td>
            <td class="px-4 py-3 text-center font-medium text-gray-700">${formatDate(item.created_at)}</td>
            <td class="px-5 py-3 text-right">
                <a href="${url}" class="inline-flex rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-600 transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-800">Xem chi tiết</a>
            </td>
        </tr>`;
    }).join('');
}

function pageButton(label, page, options = {}) {
    const disabled = options.disabled ? 'disabled' : '';
    const active = options.active;
    const base = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-semibold transition';
    const cls = active
        ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-600/15'
        : options.disabled
            ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400'
            : 'border-gray-300 bg-white text-gray-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700';
    return `<button ${disabled} onclick="${options.disabled ? '' : `taiDanhSachHangOrderVe(${page})`}" class="${base} ${cls}">${label}</button>`;
}

function renderPagination(p) {
    const wrap = document.getElementById('pagination-hang-order-ve');
    if (!p || p.total === 0) {
        wrap.classList.add('hidden');
        return;
    }
    wrap.classList.remove('hidden');
    const from = (p.current_page - 1) * p.per_page + 1;
    const to = Math.min(p.current_page * p.per_page, p.total);
    document.getElementById('pagination-hang-ve-info').textContent = `Hiển thị ${from}-${to} / ${p.total} đơn`;

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
    document.getElementById('pagination-hang-ve-buttons').innerHTML = buttons.join('');
}

window.taiDanhSachHangOrderVe = taiDanhSach;

readUrlState();
document.getElementById('input-search-hang-ve')?.addEventListener('input', debounce(e => {
    state.search = e.target.value;
    taiDanhSach(1);
}, 300));
document.getElementById('select-trang-thai-hang-ve')?.addEventListener('change', e => {
    state.trangThai = e.target.value;
    taiDanhSach(1);
});
document.querySelectorAll('[data-order-status-card]').forEach(card => {
    card.addEventListener('click', () => locTheoTrangThaiCard(card.dataset.orderStatusCard));
});
taiThongKeTrangThai();
taiDanhSach(state.page);
