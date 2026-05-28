const app = document.getElementById('chi-tiet-order-doi-tac');
const orderId = app?.dataset.orderId;
const currentRole = app?.dataset.role || '';
let currentOrder = null;

const statusText = {
    dat_truoc: 'Đặt trước',
    hang_co_san: 'Hàng có sẵn',
    ve_mot_phan: 'Về một phần',
    hang_da_ve: 'Hàng đã về',
    san_sang_tao_don_ban: 'Sẵn sàng tạo đơn bán',
    da_chuyen_don_ban: 'Đã chuyển đơn bán',
    da_huy: 'Đã hủy',
};

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function formatDate(value) {
    return value ? new Date(value).toLocaleString('vi-VN') : '-';
}

function formatTien(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
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
        dat_truoc: 'bg-blue-50 text-blue-700 border-blue-100',
        hang_co_san: 'bg-cyan-50 text-cyan-700 border-cyan-100',
        ve_mot_phan: 'bg-amber-50 text-amber-700 border-amber-100',
        hang_da_ve: 'bg-emerald-50 text-emerald-700 border-emerald-100',
        san_sang_tao_don_ban: 'bg-green-50 text-green-700 border-green-100',
        da_chuyen_don_ban: 'bg-gray-100 text-gray-700 border-gray-200',
        da_huy: 'bg-red-50 text-red-700 border-red-100',
    }[status] || 'bg-gray-50 text-gray-700 border-gray-100';
    return `<span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold ${cls}">${statusText[status] || status}</span>`;
}

function getAnhSanPham(sanPham) {
    const anh = sanPham?.anh_san_pham;
    if (!anh) return '/favicon.ico';
    try {
        const parsed = JSON.parse(anh);
        if (Array.isArray(parsed) && parsed.length > 0) return `/storage/uploads/sanpham/${parsed[0]}`;
    } catch (error) {
        // anh_san_pham có thể là file name hoặc JSON array.
    }
    return `/storage/uploads/sanpham/${anh}`;
}

function stateNote(don) {
    const chiTiets = don.chi_tiets || [];
    const tongSoLuong = chiTiets.reduce((sum, ct) => sum + Number(ct.so_luong || 0), 0);
    const tongDaVe = chiTiets.reduce((sum, ct) => sum + Number(ct.so_luong_da_ve || 0), 0);
    const progress = tongSoLuong > 0 ? Math.min(100, Math.round((tongDaVe / tongSoLuong) * 100)) : 0;
    return `<div class="rounded-2xl border border-yellow-100 bg-yellow-50 p-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="font-bold text-gray-900">${escapeHtml(statusText[don.trang_thai] || don.trang_thai)}</p>
                <p class="mt-1 text-sm text-gray-600">Tiến độ hàng về ${progress}%.</p>
            </div>
            <div class="h-2 w-40 overflow-hidden rounded-full bg-white">
                <div class="h-full rounded-full" style="width:${progress}%; background:#d4af37"></div>
            </div>
        </div>
    </div>`;
}

function renderActions(don) {
    const actions = [];
    const chiTiets = don.chi_tiets || [];
    const activeItems = chiTiets.filter(ct => ct.trang_thai !== 'da_huy');
    const coTheTaoDonBan = activeItems.length > 0 && activeItems.every(ct =>
        ct.trang_thai === 'hang_da_ve'
        && Number(ct.so_luong_da_ve || 0) >= Number(ct.so_luong || 0)
        && Number(ct.so_luong_da_chuyen || 0) < Number(ct.so_luong || 0)
    );

    if (don.trang_thai === 'dat_truoc') {
        actions.push(`<button onclick="huyDonOrder(${don.id})" class="rounded-xl bg-red-500 px-4 py-3 text-sm font-bold text-white hover:bg-red-600">Hủy đơn</button>`);
    }

    if (coTheTaoDonBan) {
        actions.push(`<button onclick="chuyenDonBan(${don.id})" class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Tạo đơn bán</button>`);
    } else if (!['dat_truoc', 'da_huy', 'da_chuyen_don_ban'].includes(don.trang_thai)) {
        actions.push(`<button type="button" disabled title="Chỉ có thể tạo đơn bán khi tất cả sản phẩm trong đơn order đã ở trạng thái Hàng đã về" class="cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 py-3 text-sm font-bold text-gray-400">Tạo đơn bán</button>`);
    }

    document.getElementById('order-action-bar').innerHTML = actions.join('');
}
function renderOrder(don) {
    currentOrder = don;
    const chiTiets = don.chi_tiets || [];
    const tongSoLuong = chiTiets.reduce((sum, ct) => sum + Number(ct.so_luong || 0), 0);
    const tongDaVe = chiTiets.reduce((sum, ct) => sum + Number(ct.so_luong_da_ve || 0), 0);
    const tongGiaTri = chiTiets.reduce((sum, ct) => sum + Number(ct.so_luong || 0) * Number(ct.gia_ban_du_kien || 0), 0);

    document.getElementById('order-title').textContent = don.ma_don_order || 'Chi tiết đơn order';
    document.getElementById('order-subtitle').textContent = `Tạo lúc ${formatDate(don.created_at)} - ${chiTiets.length} dòng sản phẩm`;
    document.getElementById('order-status-slot').innerHTML = badge(don.trang_thai);
    document.getElementById('product-summary').textContent = `Tổng SL ${tongSoLuong} - Đã về ${tongDaVe}`;
    document.getElementById('info-khach-hang').textContent = don.khach_hang?.ten || '-';
    document.getElementById('info-sdt').textContent = don.khach_hang?.sdt || '-';
    document.getElementById('info-nhan-vien').textContent = don.nhan_vien?.ten || '-';
    document.getElementById('stat-gia-tri').textContent = formatTien(tongGiaTri);
    document.getElementById('order-state-note').innerHTML = stateNote(don);

    renderActions(don);
    renderProducts(chiTiets);
    renderHistory(don.lich_sus || []);
}

function renderProducts(items) {
    document.getElementById('tbody-order-detail').innerHTML = items.map(ct => {
        const img = getAnhSanPham(ct.san_pham);
        return `<tr class="transition hover:bg-gray-50">
            <td class="px-5 py-4">
                <div class="flex min-w-0 items-center gap-3">
                    <img src="${img}" onerror="this.style.display='none'" class="h-14 w-14 rounded-xl border border-gray-100 bg-gray-50 object-cover">
                    <div class="min-w-0">
                        <div class="line-clamp-2 font-semibold text-gray-900">${escapeHtml(ct.san_pham?.ten || '')}</div>
                        <div class="mt-1 text-xs text-gray-500">${escapeHtml(ct.san_pham?.ma_sku || '')}</div>
                    </div>
                </div>
            </td>
            <td class="px-4 py-4 text-center font-semibold">${ct.so_luong}</td>
            <td class="px-4 py-4 text-center font-semibold">${ct.so_luong_da_ve}</td>
            <td class="px-4 py-4 text-center font-semibold">${ct.so_luong_da_chuyen}</td>
            <td class="px-4 py-4 text-right font-semibold">${formatTien(ct.gia_ban_du_kien)}</td>
            <td class="px-4 py-4 text-center">${badge(ct.trang_thai)}</td>
            <td class="px-5 py-4 text-right">
                ${ct.trang_thai === 'dat_truoc' ? `<button onclick="huyChiTietOrder(${ct.id})" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600">Hủy</button>` : '<span class="text-gray-400">-</span>'}
            </td>
        </tr>`;
    }).join('');
}

function renderHistory(items) {
    const history = document.getElementById('order-history');
    history.innerHTML = items.length
        ? items.map(ls => `<article class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
            <p class="font-semibold text-gray-900">${escapeHtml(ls.mo_ta || ls.hanh_dong)}</p>
            <p class="mt-1 text-xs text-gray-500">${formatDate(ls.created_at)}${ls.nguoi_thuc_hien ? ' | ' + escapeHtml(ls.nguoi_thuc_hien) : ''}</p>
        </article>`).join('')
        : '<div class="text-sm text-gray-500">Chưa có lịch sử xử lý.</div>';
}

async function loadOrder() {
    document.getElementById('order-loading').classList.remove('hidden');
    document.getElementById('order-error').classList.add('hidden');
    document.getElementById('order-content').classList.add('hidden');
    try {
        const res = await fetch(`/api/doi-tac/order-hang/${orderId}`, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Không thể tải chi tiết order');
        renderOrder(data.data);
        document.getElementById('order-content').classList.remove('hidden');
    } catch (error) {
        const box = document.getElementById('order-error');
        box.textContent = error.message || 'Không thể tải chi tiết order';
        box.classList.remove('hidden');
    } finally {
        document.getElementById('order-loading').classList.add('hidden');
    }
}

window.huyDonOrder = async (id) => {
    if (!confirm('Xác nhận hủy đơn order này?')) return;
    const res = await fetch(`/api/doi-tac/order-hang/${id}/huy`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({}),
    });
    const data = await res.json();
    if (data.success) {
        showToast('Thành công', data.message);
        await loadOrder();
    } else {
        showToast('Lỗi', data.message || 'Không thể hủy đơn order');
    }
};

window.huyChiTietOrder = async (id) => {
    if (!confirm('Xác nhận hủy dòng hàng order này?')) return;
    const res = await fetch(`/api/doi-tac/order-hang/chi-tiet/${id}/huy`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({}),
    });
    const data = await res.json();
    if (data.success) {
        showToast('Thành công', data.message);
        await loadOrder();
    } else {
        showToast('Lỗi', data.message || 'Không thể hủy dòng order');
    }
};

window.chuyenDonBan = async (id) => {
    if (!['admin', 'thu_kho', 'quan_ly_order', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'].includes(currentRole)) {
        showToast('Lỗi', 'Bạn không có quyền tạo đơn bán từ đơn order');
        return;
    }

    if (!confirm('Tạo đơn bán khi tất cả sản phẩm trong đơn order đã về đủ?')) return;

    const res = await fetch(`/api/doi-tac/order-hang/${id}/chuyen-don-ban`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({}),
    });
    const data = await res.json();
    if (data.success) {
        const maDonHang = data.data?.ma_don_hang ? ` ${data.data.ma_don_hang}` : '';
        showToast('Thành công', `Đã tạo đơn bán${maDonHang}`);
        await loadOrder();
    } else {
        showToast('Lỗi', data.message || 'Không thể tạo đơn bán');
    }
};

loadOrder();
