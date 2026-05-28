const state = {
    page: 1,
    perPage: 15,
    search: '',
    nhomId: '',
    groupsLoaded: false,
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

function formatNumber(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
}

function formatDate(value) {
    return value ? new Date(value).toLocaleDateString('vi-VN') : '-';
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
    if (state.nhomId) params.set('nhom_id', state.nhomId);
    const query = params.toString();
    window.history.replaceState({}, '', `${window.location.pathname}${query ? `?${query}` : ''}`);
}

function readUrlState() {
    const params = new URLSearchParams(window.location.search);
    state.page = Math.max(1, Number(params.get('page') || 1));
    state.search = params.get('search') || '';
    state.nhomId = params.get('nhom_id') || '';

    document.getElementById('input-search-khach-hang').value = state.search;
    document.getElementById('filter-nhom-khach-hang').value = state.nhomId;
}

function buildParams(page) {
    const params = new URLSearchParams({
        page,
        per_page: state.perPage,
    });
    if (state.search) params.set('search', state.search);
    if (state.nhomId) params.set('nhom_id', state.nhomId);
    return params;
}

function renderStats(stats = {}) {
    document.getElementById('stat-tong-khach-hang').textContent = formatNumber(stats.tong_khach_hang || 0);
    document.getElementById('stat-thang-nay').textContent = formatNumber(stats.thang_nay || 0);
    document.getElementById('stat-so-nhom').textContent = formatNumber(stats.so_nhom || 0);
}

function renderGroups(groups = []) {
    const select = document.getElementById('filter-nhom-khach-hang');
    if (!select || state.groupsLoaded) return;

    const current = state.nhomId;
    select.innerHTML = '<option value="">Tất cả nhóm khách hàng</option>' + groups.map(group => (
        `<option value="${group.id}">${escapeHtml(group.ten)}</option>`
    )).join('');
    select.value = current;
    state.groupsLoaded = true;
}

function renderRows(items = []) {
    const tbody = document.getElementById('tbody-khach-hang-order');
    document.getElementById('empty-khach-hang-order').classList.toggle('hidden', items.length > 0);

    if (!items.length) {
        tbody.innerHTML = '';
        return;
    }

    tbody.innerHTML = items.map(item => `
        <tr class="transition hover:bg-gray-50">
            <td class="px-4 py-4">
                <span class="inline-flex rounded-md bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">
                    ${escapeHtml(item.ma_khach_hang || '-')}
                </span>
            </td>
            <td class="px-4 py-4">
                <div class="font-bold text-gray-900">${escapeHtml(item.ten || '-')}</div>
                <div class="mt-1 text-xs text-gray-500">Tạo lúc ${formatDate(item.created_at)}</div>
            </td>
            <td class="px-4 py-4">
                <div class="font-medium text-gray-900">${escapeHtml(item.sdt || 'Chưa có SĐT')}</div>
                <div class="mt-1 text-xs text-gray-500">${escapeHtml(item.email || 'Chưa có email')}</div>
            </td>
            <td class="max-w-[300px] px-4 py-4 text-gray-700">
                <div class="line-clamp-2">${escapeHtml(item.dia_chi || '-')}</div>
            </td>
            <td class="px-4 py-4">
                <span class="inline-flex rounded-md bg-purple-50 px-2.5 py-1 text-xs font-semibold text-purple-700">
                    ${escapeHtml(item.nhom_khach_hang?.ten || 'Chưa phân nhóm')}
                </span>
            </td>
            <td class="px-4 py-4 text-center font-bold text-gray-900">${formatNumber(item.so_don_order || 0)}</td>
        </tr>
    `).join('');
}

function pageButton(label, page, options = {}) {
    const disabled = options.disabled ? 'disabled' : '';
    const cls = options.active
        ? 'border-yellow-500 text-white'
        : options.disabled
            ? 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400'
            : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50';
    const style = options.active ? 'style="background:#d4af37"' : '';
    return `<button ${disabled} ${style} onclick="${options.disabled ? '' : `taiDanhSachKhachHang(${page})`}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-semibold ${cls}">${label}</button>`;
}

function renderPagination(p) {
    const wrap = document.getElementById('pagination-khach-hang-order');
    if (!p || p.total === 0) {
        wrap.classList.add('hidden');
        return;
    }

    wrap.classList.remove('hidden');
    document.getElementById('pagination-khach-hang-info').textContent = `Hiển thị ${p.from || 0}-${p.to || 0} / ${p.total} khách hàng`;

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
    document.getElementById('pagination-khach-hang-buttons').innerHTML = buttons.join('');
}

async function taiDanhSachKhachHang(page = 1) {
    state.page = page;
    syncUrl();

    document.getElementById('loading-khach-hang-order').classList.remove('hidden');
    document.getElementById('empty-khach-hang-order').classList.add('hidden');
    document.getElementById('tbody-khach-hang-order').innerHTML = '';

    try {
        const res = await fetch(`/api/doi-tac/order-hang/danh-sach-khach-hang?${buildParams(page)}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        if (!data.success) {
            throw new Error(data.message || 'Không thể tải danh sách khách hàng');
        }

        renderStats(data.stats || {});
        renderGroups(data.groups || []);
        renderRows(data.data || []);
        renderPagination(data.pagination);
    } catch (error) {
        showToast('Lỗi', error.message || 'Không thể tải danh sách khách hàng');
    } finally {
        document.getElementById('loading-khach-hang-order').classList.add('hidden');
    }
}

window.taiDanhSachKhachHang = taiDanhSachKhachHang;

readUrlState();
taiDanhSachKhachHang(state.page);

document.getElementById('input-search-khach-hang')?.addEventListener('input', debounce(e => {
    state.search = e.target.value.trim();
    taiDanhSachKhachHang(1);
}, 350));

document.getElementById('filter-nhom-khach-hang')?.addEventListener('change', e => {
    state.nhomId = e.target.value;
    taiDanhSachKhachHang(1);
});
