const state = { page: 1, perPage: 12, search: '' };

function debounce(fn, wait) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function formatDate(value) {
    return value ? new Date(value).toLocaleString('vi-VN') : '-';
}

function formatNumber(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
}

function formatCurrency(value) {
    return Number(value || 0) > 0 ? `${formatNumber(value)}đ` : 'Chưa đặt giá';
}

function getAnh(anh) {
    if (!anh) return '/favicon.ico';
    return `/storage/uploads/sanpham/${anh}`;
}

function showToast(title, message) {
    const toast = document.getElementById('toast-notification');
    if (!toast) return;
    document.getElementById('toast-title').textContent = title;
    document.getElementById('toast-message').textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function syncUrl() {
    const params = new URLSearchParams();
    if (state.page > 1) params.set('page', state.page);
    if (state.search) params.set('search', state.search);
    const query = params.toString();
    window.history.replaceState({}, '', `${window.location.pathname}${query ? `?${query}` : ''}`);
}

function readUrlState() {
    const params = new URLSearchParams(window.location.search);
    state.page = Math.max(1, Number(params.get('page') || 1));
    state.search = params.get('search') || '';
    document.getElementById('input-search-sp-duoc-phep').value = state.search;
}

async function taiDanhSach(page = 1) {
    state.page = page;
    syncUrl();
    document.getElementById('loading-sp-duoc-phep').classList.remove('hidden');
    document.getElementById('empty-sp-duoc-phep').classList.add('hidden');
    document.getElementById('grid-sp-duoc-phep').innerHTML = '';

    const params = new URLSearchParams({
        page,
        per_page: state.perPage,
        tu_khoa: state.search,
    });

    const res = await fetch(`/api/doi-tac/order-hang/san-pham-duoc-phep?${params}`, {
        headers: { Accept: 'application/json' },
    });
    const data = await res.json();
    document.getElementById('loading-sp-duoc-phep').classList.add('hidden');

    if (!data.success) {
        showToast('Lỗi', data.message || 'Không thể tải danh mục hàng order');
        return;
    }

    renderDanhSach(data.data || []);
    renderPagination(data.pagination);
}

function renderDanhSach(items) {
    const grid = document.getElementById('grid-sp-duoc-phep');
    if (!items.length) {
        document.getElementById('empty-sp-duoc-phep').classList.remove('hidden');
        return;
    }

    grid.innerHTML = items.map(item => {
        const url = `/doi-tac/order-hang/san-pham-duoc-phep/${encodeURIComponent(item.ma_chung)}`;
        return `
            <a href="${url}" class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                <div class="relative aspect-square overflow-hidden bg-gray-50">
                    <img
                        src="${getAnh(item.anh_chinh)}"
                        alt="${escapeHtml(item.ten_chung || 'Sản phẩm')}"
                        loading="lazy"
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        onerror="this.parentElement.innerHTML='<div class=&quot;flex h-full w-full items-center justify-center text-4xl text-gray-200&quot;>SP</div>'"
                    >
                    <span class="absolute left-2 top-2 rounded-full border border-green-100 bg-green-50 px-2 py-1 text-[11px] font-bold text-green-700 shadow-sm">Được order</span>
                </div>
                <div class="p-2.5 sm:p-3">
                    ${item.nhan_hieu ? `<div class="mb-1 truncate text-xs font-bold" style="color:#d4af37">${escapeHtml(item.nhan_hieu)}</div>` : ''}
                    <h3 class="mb-1.5 line-clamp-2 min-h-[34px] text-xs font-semibold leading-snug text-gray-900 sm:text-sm">
                        ${escapeHtml(item.ten_chung || 'Sản phẩm')}
                    </h3>
                    <div class="mb-2 truncate text-xs text-gray-500">${escapeHtml(item.ma_chung || '-')}</div>
                    <div class="flex items-end justify-between gap-2">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-gray-400">Giá order từ</div>
                            <div class="truncate text-sm font-bold sm:text-base" style="color:#1a1a2e">${formatCurrency(item.gia_order)}</div>
                        </div>
                        <span class="shrink-0 rounded-full bg-gray-100 px-2 py-1 text-xs font-bold text-gray-700">${formatNumber(item.so_phien_ban)} phiên bản</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-2 text-xs text-gray-400">
                        <span>Tồn ${formatNumber(item.ton_kho || 0)}</span>
                        <span>Bật order: ${formatDate(item.order_listed_at)}</span>
                    </div>
                </div>
            </a>
        `;
    }).join('');
}

function pageButton(label, page, options = {}) {
    const disabled = options.disabled ? 'disabled' : '';
    const cls = options.active
        ? 'bg-gray-100 text-gray-900'
        : options.disabled
            ? 'cursor-not-allowed bg-gray-50 text-gray-400'
            : 'bg-white text-gray-700 hover:bg-gray-50';
    const content = options.icon === 'prev'
        ? '<span aria-hidden="true">‹</span><span class="sr-only">Previous</span>'
        : options.icon === 'next'
            ? '<span aria-hidden="true">›</span><span class="sr-only">Next</span>'
            : label;

    return `<button ${disabled} onclick="${options.disabled ? '' : `taiDanhSachSanPhamDuocPhep(${page})`}" class="inline-flex h-10 min-w-10 items-center justify-center border-r border-gray-200 px-3 text-sm font-medium transition last:border-r-0 ${cls}">${content}</button>`;
}

function renderPagination(p) {
    const wrap = document.getElementById('pagination-sp-duoc-phep');
    if (!p || p.total === 0) {
        wrap.classList.add('hidden');
        return;
    }

    wrap.classList.remove('hidden');
    const from = (p.current_page - 1) * p.per_page + 1;
    const to = Math.min(p.current_page * p.per_page, p.total);
    document.getElementById('pagination-sp-info').textContent = `Hiển thị ${from}-${to} / ${p.total} sản phẩm`;

    const pages = new Set([1, p.last_page, p.current_page - 1, p.current_page, p.current_page + 1]);
    let last = 0;
    const buttons = [
        pageButton('Previous', Math.max(1, p.current_page - 1), {
            disabled: p.current_page <= 1,
            icon: 'prev',
        }),
    ];

    [...pages]
        .filter(page => page >= 1 && page <= p.last_page)
        .sort((a, b) => a - b)
        .forEach(page => {
            if (last && page - last > 1) {
                buttons.push('<span class="inline-flex h-10 min-w-10 items-center justify-center border-r border-gray-200 bg-white px-3 text-sm text-gray-400 last:border-r-0">...</span>');
            }
            buttons.push(pageButton(page, page, { active: page === p.current_page }));
            last = page;
        });

    buttons.push(pageButton('Next', Math.min(p.last_page, p.current_page + 1), {
        disabled: p.current_page >= p.last_page,
        icon: 'next',
    }));

    document.getElementById('pagination-sp-buttons').innerHTML = buttons.join('');
}

window.taiDanhSachSanPhamDuocPhep = taiDanhSach;

readUrlState();
document.getElementById('input-search-sp-duoc-phep')?.addEventListener('input', debounce(e => {
    state.search = e.target.value;
    taiDanhSach(1);
}, 300));
taiDanhSach(state.page);
