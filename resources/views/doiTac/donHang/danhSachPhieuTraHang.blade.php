@extends('layouts.app')
@section('title', 'Khách trả hàng')
@section('hideFooter', true)

@section('content')
<div id="danh-sach-phieu-tra-don-thuong" data-doi-tac-quyen="{{ session('doi_tac_quyen') }}" class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide" style="color:#d4af37">Theo dõi bán hàng</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Khách trả hàng</h1>
            <p class="mt-1 text-sm text-gray-500">Danh sách phiếu đổi/trả phát sinh từ đơn hàng thường.</p>
        </div>
        <a href="/doi-tac/don-hang" class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">
            Danh sách đơn hàng
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['ma' => 'tong', 'ten' => 'Tổng phiếu', 'lop' => 'bg-slate-100 text-slate-700'],
            ['ma' => 'da_nhan_hang', 'ten' => 'Đã nhận hàng', 'lop' => 'bg-blue-100 text-blue-700'],
            ['ma' => 'da_hoan_tien', 'ten' => 'Đã hoàn tiền', 'lop' => 'bg-emerald-100 text-emerald-700'],
            ['ma' => 'huy', 'ten' => 'Đã hủy', 'lop' => 'bg-red-100 text-red-700'],
        ] as $item)
            <button type="button" data-return-status-card="{{ $item['ma'] }}" class="rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-yellow-300 hover:shadow-md">
                <div class="text-sm font-semibold text-gray-500">{{ $item['ten'] }}</div>
                <div id="stat-return-{{ $item['ma'] }}" class="mt-3 text-2xl font-bold text-gray-900">0</div>
                <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $item['lop'] }}">Đơn thường</span>
            </button>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 p-4">
            <div class="flex flex-col gap-3 lg:flex-row">
                <input id="input-search-return" class="flex-1 rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" placeholder="Tìm theo mã phiếu, mã đơn, tên hoặc SĐT khách hàng">
                <select id="filter-return-status" class="rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 lg:w-48">
                    <option value="">Tất cả trạng thái</option>
                    <option value="da_nhan_hang">Đã nhận hàng</option>
                    <option value="da_hoan_tien">Đã hoàn tiền</option>
                    <option value="huy">Đã hủy</option>
                </select>
                <input id="filter-return-from" type="date" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <input id="filter-return-to" type="date" class="rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1080px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Mã phiếu</th>
                        <th class="px-4 py-3 text-left">Đơn hàng</th>
                        <th class="px-4 py-3 text-left">Khách hàng</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-right">Tiền trả</th>
                        <th class="px-4 py-3 text-left">Ngày tạo</th>
                        <th class="min-w-[120px] px-4 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="tbody-return" class="divide-y divide-gray-100 bg-white"></tbody>
            </table>
        </div>
        <div id="loading-return" class="py-12 text-center text-sm text-gray-500">Đang tải danh sách phiếu trả hàng...</div>
        <div id="empty-return" class="hidden py-12 text-center text-sm text-gray-500">Không có phiếu trả hàng phù hợp.</div>
        <div id="pagination-return" class="hidden border-t border-gray-100 bg-gray-50 px-4 py-3">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <span id="pagination-return-info" class="text-sm text-gray-500"></span>
                <div id="pagination-return-buttons" class="flex flex-wrap items-center justify-end gap-1.5"></div>
            </div>
        </div>
    </div>
</div>

<div id="return-detail-modal" class="fixed inset-0 z-[80] hidden bg-black/40 p-4">
    <div class="mx-auto mt-8 max-h-[88vh] max-w-4xl overflow-hidden rounded-xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4">
            <div>
                <h2 id="modal-return-title" class="text-lg font-bold text-gray-950">Chi tiết phiếu trả</h2>
                <p id="modal-return-subtitle" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <button type="button" id="btn-close-return-modal" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100">Đóng</button>
        </div>
        <div id="modal-return-body" class="max-h-[calc(88vh-76px)] overflow-y-auto p-5"></div>
    </div>
</div>

<div id="return-refund-modal" class="fixed inset-0 z-[85] hidden bg-black/40 p-4">
    <div class="mx-auto mt-12 max-w-lg overflow-hidden rounded-xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-gray-950">Ho&#224;n ti&#7873;n cho kh&#225;ch</h2>
                <p id="refund-modal-subtitle" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <button type="button" id="btn-close-refund-modal" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100">&#272;&#243;ng</button>
        </div>
        <div class="space-y-4 p-5">
            <input type="hidden" id="refund-phieu-id">
            <div class="grid gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">T&#7893;ng ti&#7873;n c&#7847;n ho&#224;n</span>
                    <span id="refund-tong-tien" class="font-semibold text-gray-950">0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">&#272;&#227; ho&#224;n</span>
                    <span id="refund-da-hoan" class="font-semibold text-emerald-700">0</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-3">
                    <span class="font-semibold text-gray-800">C&#242;n c&#7847;n ho&#224;n</span>
                    <span id="refund-con-lai" class="font-bold text-red-600">0</span>
                </div>
            </div>
            <label class="block text-sm font-semibold text-gray-700">
                S&#7889; ti&#7873;n ho&#224;n
                <input id="refund-so-tien" class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-3 text-right text-sm font-bold focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </label>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-refund-percent="25" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">25%</button>
                <button type="button" data-refund-percent="50" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">50%</button>
                <button type="button" data-refund-percent="100" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Ho&#224;n &#273;&#7911;</button>
            </div>
            <button id="btn-confirm-refund" type="button" class="w-full rounded-lg bg-yellow-600 px-4 py-3 text-sm font-bold text-white hover:bg-yellow-700 disabled:cursor-not-allowed disabled:opacity-60">
                X&#225;c nh&#7853;n ho&#224;n ti&#7873;n
            </button>
        </div>
    </div>
</div>

<div id="toast-notification" class="hidden fixed right-5 top-5 z-[90] min-w-80 rounded-xl border border-gray-100 bg-white p-4 shadow-xl">
    <p id="toast-title" class="font-semibold text-gray-900"></p>
    <p id="toast-message" class="mt-1 text-sm text-gray-600"></p>
</div>

<script>
const state = {
    page: 1,
    perPage: 15,
    search: '',
    trangThai: '',
    tuNgay: '',
    denNgay: '',
};

const root = document.getElementById('danh-sach-phieu-tra-don-thuong');
const coQuyenHoanTien = root?.dataset.doiTacQuyen === 'admin';
let phieuDangHoanTien = null;

const statusText = {
    da_nhan_hang: 'Đã nhận hàng',
    da_hoan_tien: 'Đã hoàn tiền',
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

function parseMoney(value) {
    return Number(String(value || '').replace(/[^\d]/g, '')) || 0;
}

function layAnhSanPham(value) {
    if (!value) return '';
    try {
        const parsed = JSON.parse(value);
        if (Array.isArray(parsed) && parsed.length) {
            return `/storage/uploads/sanpham/${parsed[0]}`;
        }
    } catch (error) {
    }
    return `/storage/uploads/sanpham/${value}`;
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
    const cls = {
        da_nhan_hang: 'bg-blue-100 text-blue-800',
        da_hoan_tien: 'bg-emerald-100 text-emerald-800',
        huy: 'bg-red-100 text-red-800',
    }[status] || 'bg-gray-100 text-gray-700';
    return `<span class="inline-flex rounded-md px-3 py-1 text-xs font-semibold ${cls}">${escapeHtml(statusText[status] || status || '-')}</span>`;
}

function buildParams(page) {
    const params = new URLSearchParams({ page, per_page: state.perPage });
    if (state.search) params.set('search', state.search);
    if (state.trangThai) params.set('trang_thai', state.trangThai);
    if (state.tuNgay) params.set('tu_ngay', state.tuNgay);
    if (state.denNgay) params.set('den_ngay', state.denNgay);
    return params;
}

async function taiDanhSach(page = 1) {
    state.page = page;
    document.getElementById('loading-return').classList.remove('hidden');
    document.getElementById('empty-return').classList.add('hidden');
    document.getElementById('tbody-return').innerHTML = '';

    try {
        const response = await fetch(`/api/doi-tac/don-hang/phieu-tra-hang/danh-sach?${buildParams(page)}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Không tải được danh sách phiếu trả hàng.');
        }

        renderStats(data.stats || {});
        renderRows(data.data || []);
        renderPagination(data.pagination);
    } catch (error) {
        showToast('Lỗi', error.message || 'Không tải được danh sách phiếu trả hàng.');
    } finally {
        document.getElementById('loading-return').classList.add('hidden');
    }
}

function renderStats(stats) {
    ['tong', 'da_nhan_hang', 'da_hoan_tien', 'huy'].forEach((key) => {
        const el = document.getElementById(`stat-return-${key}`);
        if (el) el.textContent = formatNumber(stats[key] || 0);
    });
}

function renderRows(items) {
    const tbody = document.getElementById('tbody-return');
    if (!items.length) {
        document.getElementById('empty-return').classList.remove('hidden');
        return;
    }

    tbody.innerHTML = items.map((item) => {
        const khach = item.khach_hang || {};
        const tienHoan = Number(item.tien_hoan || item.tong_tien_tra || 0);
        const daHoan = Number(item.da_hoan || 0);
        const conLai = Math.max(0, tienHoan - daHoan);
        const coTheHoanTien = coQuyenHoanTien && conLai > 0 && !['huy', 'da_hoan_tien'].includes(item.trang_thai);
        return `<tr class="transition hover:bg-gray-50">
            <td class="px-4 py-4">
                <div class="font-bold text-gray-950">${escapeHtml(item.ma_phieu)}</div>
                <div class="mt-1 text-xs text-gray-500">${escapeHtml(item.nguoi_tao || '')}</div>
            </td>
            <td class="px-4 py-4">
                <a href="/doi-tac/don-hang/${item.don_hang_id}" class="font-semibold text-blue-600 hover:underline">${escapeHtml(item.ma_don_hang_goc || '-')}</a>
                <div class="mt-1 text-xs text-gray-500">Đơn thường</div>
            </td>
            <td class="px-4 py-4">
                <div class="font-semibold text-gray-900">${escapeHtml(khach.ten || 'Khách lẻ')}</div>
                <div class="mt-1 text-xs text-gray-500">${escapeHtml(khach.sdt || '')}</div>
            </td>
            <td class="px-4 py-4 text-center font-semibold text-gray-950">${formatNumber(item.tong_so_luong || 0)}</td>
            <td class="px-4 py-4 text-center">${badge(item.trang_thai)}</td>
            <td class="px-4 py-4 text-right">
                <div class="font-bold text-gray-950">${formatNumber(item.tong_tien_tra)}</div>
                ${daHoan > 0 ? `<div class="mt-1 text-xs font-semibold text-emerald-700">&#272;&#227; ho&#224;n: ${formatNumber(daHoan)}</div>` : ''}
                ${tienHoan > daHoan ? `<div class="mt-1 text-xs text-gray-500">C&#242;n: ${formatNumber(tienHoan - daHoan)}</div>` : ''}
            </td>
            <td class="px-4 py-4 text-gray-600">${formatDate(item.created_at)}</td>
            <td class="px-4 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="xemChiTiet(${Number(item.id)})" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-200 px-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Xem</button>
                    ${coTheHoanTien ? `<button type="button" onclick="moHoanTien(${Number(item.id)})" class="inline-flex h-10 items-center justify-center whitespace-nowrap rounded-lg bg-yellow-600 px-3 text-sm font-semibold text-white hover:bg-yellow-700">Ho&#224;n ti&#7873;n</button>` : ''}
                </div>
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
    const wrap = document.getElementById('pagination-return');
    if (!p || p.total === 0 || p.tong_so === 0) {
        wrap.classList.add('hidden');
        return;
    }
    const current = p.current_page || p.trang_hien_tai || 1;
    const last = p.last_page || p.tong_trang || 1;
    const total = p.total || p.tong_so || 0;
    const from = p.from || p.first_item || ((current - 1) * state.perPage + 1);
    const to = p.to || p.last_item || Math.min(current * state.perPage, total);
    wrap.classList.remove('hidden');
    document.getElementById('pagination-return-info').textContent = `Hiển thị ${from}-${to} / ${total} phiếu`;

    const pages = new Set([1, last, current - 1, current, current + 1]);
    let previous = 0;
    const buttons = [pageButton('Trước', Math.max(1, current - 1), { disabled: current <= 1 })];
    [...pages].filter(page => page >= 1 && page <= last).sort((a, b) => a - b).forEach((page) => {
        if (previous && page - previous > 1) buttons.push('<span class="px-2 text-gray-400">...</span>');
        buttons.push(pageButton(page, page, { active: page === current }));
        previous = page;
    });
    buttons.push(pageButton('Sau', Math.min(last, current + 1), { disabled: current >= last }));
    document.getElementById('pagination-return-buttons').innerHTML = buttons.join('');
}

async function xemChiTiet(id) {
    try {
        const response = await fetch(`/api/doi-tac/don-hang/phieu-tra-hang/${id}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Không tải được chi tiết phiếu trả hàng.');
        }
        renderModal(data.data || {});
    } catch (error) {
        showToast('Lỗi', error.message || 'Không tải được chi tiết phiếu trả hàng.');
    }
}

function renderModal(phieu) {
    const tienHoan = Number(phieu.tien_hoan || phieu.tong_tien_tra || 0);
    const daHoan = Number(phieu.da_hoan || 0);
    const conLai = Math.max(0, tienHoan - daHoan);
    document.getElementById('modal-return-title').textContent = phieu.ma_phieu || 'Chi tiết phiếu trả';
    document.getElementById('modal-return-subtitle').textContent = phieu.ma_don_hang_goc || '';
    document.getElementById('modal-return-body').innerHTML = `
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Khách hàng</p>
                <p class="mt-2 font-semibold text-gray-950">${escapeHtml(phieu.khach_hang?.ten || '-')}</p>
                <p class="mt-1 text-xs text-gray-500">${escapeHtml(phieu.khach_hang?.sdt || '')}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Trạng thái</p>
                <p class="mt-2">${badge(phieu.trang_thai)}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Tiền trả</p>
                <p class="mt-2 text-lg font-bold text-gray-950">${formatNumber(phieu.tong_tien_tra)}</p>
                <p class="mt-1 text-xs font-semibold text-emerald-700">&#272;&#227; ho&#224;n: ${formatNumber(daHoan)}</p>
                <p class="mt-1 text-xs font-semibold text-red-600">C&#242;n c&#7847;n ho&#224;n: ${formatNumber(conLai)}</p>
            </div>
        </div>
        <div class="mt-5 overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full min-w-[820px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="w-16 px-4 py-3 text-left">&#7842;nh</th>
                        <th class="px-4 py-3 text-left">Sản phẩm</th>
                        <th class="px-4 py-3 text-center">Số lượng</th>
                        <th class="px-4 py-3 text-right">Giá trả</th>
                        <th class="px-4 py-3 text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    ${(phieu.chi_tiets || []).map(item => {
                        const imageUrl = layAnhSanPham(item.anh_san_pham);
                        return `<tr>
                        <td class="px-4 py-3">
                            <div class="h-12 w-12 overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                ${imageUrl ? `<img src="${escapeHtml(imageUrl)}" alt="" class="h-full w-full object-cover" onerror="this.style.display='none'">` : ''}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-950">${escapeHtml(item.ten_san_pham || 'Sản phẩm')}</div>
                            <div class="mt-1 text-xs text-gray-500">${escapeHtml(item.ma_sku || '')}</div>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold">${formatNumber(item.so_luong)}</td>
                        <td class="px-4 py-3 text-right">${formatNumber(item.gia_tra)}</td>
                        <td class="px-4 py-3 text-right font-bold">${formatNumber(item.thanh_tien)}</td>
                    </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
        <div class="mt-4 rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
            <p><span class="font-semibold text-gray-950">Lý do:</span> ${escapeHtml(phieu.ly_do_tra || '-')}</p>
            <p class="mt-1"><span class="font-semibold text-gray-950">Ghi chú:</span> ${escapeHtml(phieu.ghi_chu || '-')}</p>
        </div>
    `;
    document.getElementById('return-detail-modal').classList.remove('hidden');
}

async function moHoanTien(id) {
    if (!coQuyenHoanTien) {
        showToast('Khong co quyen', 'Tai khoan nay khong co quyen hoan tien phieu tra don hang thuong.');
        return;
    }

    try {
        const response = await fetch(`/api/doi-tac/don-hang/phieu-tra-hang/${id}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Khong tai duoc thong tin phieu tra hang.');
        }

        phieuDangHoanTien = data.data || {};
        const tienHoan = Number(phieuDangHoanTien.tien_hoan || phieuDangHoanTien.tong_tien_tra || 0);
        const daHoan = Number(phieuDangHoanTien.da_hoan || 0);
        const conLai = Math.max(0, tienHoan - daHoan);

        if (conLai <= 0 || ['huy', 'da_hoan_tien'].includes(phieuDangHoanTien.trang_thai)) {
            showToast('Khong the hoan tien', 'Phieu nay da hoan du, da huy hoac khong con tien can hoan.');
            return;
        }

        document.getElementById('refund-phieu-id').value = id;
        document.getElementById('refund-modal-subtitle').textContent = phieuDangHoanTien.ma_phieu || '';
        document.getElementById('refund-tong-tien').textContent = formatNumber(tienHoan);
        document.getElementById('refund-da-hoan').textContent = formatNumber(daHoan);
        document.getElementById('refund-con-lai').textContent = formatNumber(conLai);
        document.getElementById('refund-so-tien').value = formatNumber(conLai);
        document.getElementById('return-refund-modal').classList.remove('hidden');
    } catch (error) {
        showToast('Loi', error.message || 'Khong tai duoc thong tin phieu tra hang.');
    }
}

async function xacNhanHoanTien() {
    if (!phieuDangHoanTien) return;

    const phieuId = document.getElementById('refund-phieu-id').value;
    const soTien = parseMoney(document.getElementById('refund-so-tien').value);
    const tienHoan = Number(phieuDangHoanTien.tien_hoan || phieuDangHoanTien.tong_tien_tra || 0);
    const daHoan = Number(phieuDangHoanTien.da_hoan || 0);
    const conLai = Math.max(0, tienHoan - daHoan);

    if (soTien <= 0) {
        showToast('Thieu so tien', 'Vui long nhap so tien hoan lon hon 0.');
        return;
    }

    if (soTien > conLai) {
        showToast('Vuot qua so tien', `So tien toi da co the hoan la ${formatNumber(conLai)}.`);
        return;
    }

    if (!window.confirm(`Xac nhan hoan ${formatNumber(soTien)} cho khach?`)) {
        return;
    }

    const button = document.getElementById('btn-confirm-refund');
    button.disabled = true;
    button.textContent = 'Dang hoan tien...';

    try {
        const response = await fetch(`/api/doi-tac/don-hang/phieu-tra-hang/${phieuId}/hoan-tien`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ so_tien: soTien }),
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Khong hoan tien duoc cho phieu nay.');
        }

        document.getElementById('return-refund-modal').classList.add('hidden');
        phieuDangHoanTien = null;
        showToast('Thanh cong', data.message || 'Da hoan tien cho khach.');
        taiDanhSach(state.page);
    } catch (error) {
        showToast('Loi', error.message || 'Khong hoan tien duoc cho phieu nay.');
    } finally {
        button.disabled = false;
        button.textContent = 'Xac nhan hoan tien';
    }
}

function chonSoTienTheoPhanTram(phanTram) {
    if (!phieuDangHoanTien) return;
    const tienHoan = Number(phieuDangHoanTien.tien_hoan || phieuDangHoanTien.tong_tien_tra || 0);
    const daHoan = Number(phieuDangHoanTien.da_hoan || 0);
    const conLai = Math.max(0, tienHoan - daHoan);
    document.getElementById('refund-so-tien').value = formatNumber(Math.round(conLai * phanTram / 100));
}

function bindFilters() {
    document.getElementById('input-search-return')?.addEventListener('input', debounce((event) => {
        state.search = event.target.value.trim();
        taiDanhSach(1);
    }, 350));
    document.getElementById('filter-return-status')?.addEventListener('change', (event) => {
        state.trangThai = event.target.value;
        taiDanhSach(1);
    });
    document.getElementById('filter-return-from')?.addEventListener('change', (event) => {
        state.tuNgay = event.target.value;
        taiDanhSach(1);
    });
    document.getElementById('filter-return-to')?.addEventListener('change', (event) => {
        state.denNgay = event.target.value;
        taiDanhSach(1);
    });
}

document.getElementById('btn-close-return-modal')?.addEventListener('click', () => {
    document.getElementById('return-detail-modal').classList.add('hidden');
});
document.getElementById('btn-close-refund-modal')?.addEventListener('click', () => {
    document.getElementById('return-refund-modal').classList.add('hidden');
    phieuDangHoanTien = null;
});
document.getElementById('btn-confirm-refund')?.addEventListener('click', xacNhanHoanTien);
document.getElementById('refund-so-tien')?.addEventListener('input', (event) => {
    const value = parseMoney(event.target.value);
    event.target.value = value ? formatNumber(value) : '';
});
document.querySelectorAll('[data-refund-percent]').forEach((button) => {
    button.addEventListener('click', () => chonSoTienTheoPhanTram(Number(button.dataset.refundPercent || 100)));
});

window.taiDanhSach = taiDanhSach;
window.xemChiTiet = xemChiTiet;
window.moHoanTien = moHoanTien;

document.querySelector('#tbody-return')?.closest('table')?.querySelector('thead tr th:nth-child(4)')?.insertAdjacentHTML(
    'beforebegin',
    '<th class="px-4 py-3 text-center">SL tr&#7843;</th>'
);

bindFilters();
taiDanhSach();
</script>
@endsection
