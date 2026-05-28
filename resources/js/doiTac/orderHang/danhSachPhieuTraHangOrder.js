const state = {
    page: 1,
    perPage: 15,
    search: '',
    trangThai: '',
    tuNgay: '',
    denNgay: '',
};

const root = document.getElementById('danh-sach-phieu-tra-order');
const coQuyenHoanTien = ['admin', 'quan_ly_order'].includes(root?.dataset.doiTacQuyen || '');
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
        const response = await fetch(`/api/doi-tac/order-hang/phieu-tra-hang/danh-sach?${buildParams(page)}`, {
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
                <a href="/doi-tac/order-hang/don-ban/${item.don_hang_id}" class="font-semibold text-blue-600 hover:underline">${escapeHtml(item.ma_don_hang_goc || '-')}</a>
                <div class="mt-1 text-xs text-sky-700">${escapeHtml(item.ma_don_order || 'Order')}</div>
            </td>
            <td class="px-4 py-4">
                <div class="font-semibold text-gray-900">${escapeHtml(khach.ten || 'Khách lẻ')}</div>
                <div class="mt-1 text-xs text-gray-500">${escapeHtml(khach.sdt || '')}</div>
            </td>
            <td class="px-4 py-4 text-center">${badge(item.trang_thai)}</td>
            <td class="px-4 py-4 text-right font-bold text-gray-950">${formatNumber(item.tong_tien_tra)}</td>
            <td class="px-4 py-4 text-gray-600">${formatDate(item.created_at)}</td>
            <td class="px-4 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="xemChiTiet(${item.id})" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-200 px-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Xem</button>
                    ${coTheHoanTien ? `<button type="button" onclick="moHoanTien(${item.id})" class="inline-flex h-10 items-center justify-center whitespace-nowrap rounded-lg bg-yellow-600 px-3 text-sm font-semibold text-white hover:bg-yellow-700">Hoàn tiền cho khách</button>` : ''}
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
    if (!p || p.tong_so === 0 || p.total === 0) {
        wrap.classList.add('hidden');
        return;
    }
    const current = p.trang_hien_tai || p.current_page || 1;
    const last = p.tong_trang || p.last_page || 1;
    const total = p.tong_so || p.total || 0;
    const from = p.first_item || p.from || ((current - 1) * state.perPage + 1);
    const to = p.last_item || p.to || Math.min(current * state.perPage, total);
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
        const response = await fetch(`/api/doi-tac/order-hang/phieu-tra-hang/${id}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Không tải được chi tiết phiếu trả hàng.');
        }
        renderModal(data.data);
    } catch (error) {
        showToast('Lỗi', error.message || 'Không tải được chi tiết phiếu trả hàng.');
    }
}

function renderModal(phieu) {
    document.getElementById('modal-return-title').textContent = phieu.ma_phieu || 'Chi tiết phiếu trả';
    document.getElementById('modal-return-subtitle').textContent = `${phieu.ma_don_hang_goc || ''} ${phieu.ma_don_order ? `- ${phieu.ma_don_order}` : ''}`;
    document.getElementById('modal-return-body').innerHTML = `
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Khách hàng</p>
                <p class="mt-2 font-semibold text-gray-950">${escapeHtml(phieu.khach_hang?.ten || '-')}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Trạng thái</p>
                <p class="mt-2">${badge(phieu.trang_thai)}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Tiền trả</p>
                <p class="mt-2 text-lg font-bold text-gray-950">${formatNumber(phieu.tong_tien_tra)}</p>
            </div>
        </div>
        <div class="mt-5 overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Sản phẩm</th>
                        <th class="px-4 py-3 text-center">Số lượng</th>
                        <th class="px-4 py-3 text-right">Giá trả</th>
                        <th class="px-4 py-3 text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    ${(phieu.chi_tiets || []).map(item => `<tr>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-950">${escapeHtml(item.ten_san_pham || 'Sản phẩm')}</div>
                            <div class="mt-1 text-xs text-gray-500">${escapeHtml(item.ma_sku || '')}</div>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold">${formatNumber(item.so_luong)}</td>
                        <td class="px-4 py-3 text-right">${formatNumber(item.gia_tra)}</td>
                        <td class="px-4 py-3 text-right font-bold">${formatNumber(item.thanh_tien)}</td>
                    </tr>`).join('')}
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
    try {
        const response = await fetch(`/api/doi-tac/order-hang/phieu-tra-hang/${id}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Không tải được thông tin phiếu trả hàng.');
        }

        phieuDangHoanTien = data.data;
        const tienHoan = Number(phieuDangHoanTien.tien_hoan || phieuDangHoanTien.tong_tien_tra || 0);
        const daHoan = Number(phieuDangHoanTien.da_hoan || 0);
        const conLai = Math.max(0, tienHoan - daHoan);

        if (conLai <= 0 || ['huy', 'da_hoan_tien'].includes(phieuDangHoanTien.trang_thai)) {
            showToast('Không thể hoàn tiền', 'Phiếu này đã hoàn đủ, đã hủy hoặc không còn tiền cần hoàn.');
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
        showToast('Lỗi', error.message || 'Không tải được thông tin phiếu trả hàng.');
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
        showToast('Thiếu số tiền', 'Vui lòng nhập số tiền hoàn lớn hơn 0.');
        return;
    }

    if (soTien > conLai) {
        showToast('Vượt quá số tiền', `Số tiền tối đa có thể hoàn là ${formatNumber(conLai)}.`);
        return;
    }

    if (!window.confirm(`Xác nhận hoàn ${formatNumber(soTien)} cho khách?`)) {
        return;
    }

    const button = document.getElementById('btn-confirm-refund');
    button.disabled = true;
    button.textContent = 'Đang hoàn tiền...';

    try {
        const response = await fetch(`/api/doi-tac/order-hang/phieu-tra-hang/${phieuId}/hoan-tien`, {
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
            throw new Error(data.message || 'Không hoàn tiền được cho phiếu này.');
        }

        document.getElementById('return-refund-modal').classList.add('hidden');
        phieuDangHoanTien = null;
        showToast('Thành công', data.message || 'Đã hoàn tiền cho khách.');
        taiDanhSach(state.page);
    } catch (error) {
        showToast('Lỗi', error.message || 'Không hoàn tiền được cho phiếu này.');
    } finally {
        button.disabled = false;
        button.textContent = 'Xác nhận hoàn tiền';
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

bindFilters();
taiDanhSach();
