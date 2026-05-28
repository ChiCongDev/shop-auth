const state = {
    gioOrder: null,
    khachHang: null,
    nhanVienBanHangId: null,
    dropdownKhach: new Map(),
};

const coTheChonNhanVienOrder = Boolean(window.coTheChonNhanVienOrder);

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function formatTien(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
}

function getAnh(anh) {
    if (!anh) return '/favicon.ico';
    return `/storage/uploads/sanpham/${anh}`;
}

function anhSanPhamHtml(anh, ten) {
    if (!anh) {
        return '<div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 text-xs font-bold text-gray-300">SP</div>';
    }

    return `<img src="${getAnh(anh)}" alt="${escapeHtml(ten || 'Sản phẩm')}" onerror="this.outerHTML='<div class=&quot;flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-gray-50 text-xs font-bold text-gray-300&quot;>SP</div>'" class="h-16 w-16 shrink-0 rounded-xl border border-gray-100 bg-gray-50 object-cover">`;
}

function tenQuyenNhanVien(quyen) {
    return {
        nhan_vien_ban_hang_cap_1: 'Nhân viên bán hàng cấp 1',
        nhan_vien_ban_hang_cap_2: 'Nhân viên bán hàng cấp 2',
        admin: 'Quản trị viên',
        thu_kho: 'Thủ kho',
        quan_ly_order: 'Quản lý order',
    }[quyen] || quyen || 'Nhân viên';
}

function showToast(title, message, type = 'success') {
    const toast = document.getElementById('toast-notification');
    if (!toast) return;
    toast.classList.toggle('border-red-200', type === 'error');
    toast.classList.toggle('border-green-200', type !== 'error');
    document.getElementById('toast-title').textContent = title;
    document.getElementById('toast-message').textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3500);
}

function debounce(fn, wait) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
}

async function taiGioOrder() {
    document.getElementById('loading-gio-order').classList.remove('hidden');
    try {
        const res = await fetch('/api/doi-tac/order-hang/gio-order', {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Không thể tải giỏ order.');
        state.gioOrder = data.data;
        renderGioOrder();
    } catch (error) {
        showToast('Lỗi', error.message || 'Không thể tải giỏ order.', 'error');
    } finally {
        document.getElementById('loading-gio-order').classList.add('hidden');
    }
}

function renderGioOrder() {
    const items = state.gioOrder?.items || [];
    const empty = document.getElementById('empty-gio-order');
    const table = document.getElementById('table-gio-order');
    const tbody = document.getElementById('tbody-gio-order');

    document.getElementById('gio-order-so-san-pham').textContent = `${formatTien(state.gioOrder?.so_san_pham || 0)} sản phẩm`;
    document.getElementById('tong-sp-gio-order').textContent = formatTien(state.gioOrder?.so_san_pham || 0);
    document.getElementById('tong-sl-gio-order').textContent = formatTien(state.gioOrder?.tong_so_luong || 0);
    document.getElementById('tong-tien-gio-order').textContent = formatTien(state.gioOrder?.tong_tien_tam_tinh || 0);

    if (!items.length) {
        table.classList.add('hidden');
        empty.classList.remove('hidden');
        tbody.innerHTML = '';
        return;
    }

    empty.classList.add('hidden');
    table.classList.remove('hidden');
    tbody.innerHTML = items.map(item => `
        <tr class="align-middle">
            <td class="px-4 py-5">
                <div class="flex min-w-0 items-center gap-4">
                    ${anhSanPhamHtml(item.anh_chinh, item.ten)}
                    <div class="min-w-0 max-w-[320px]">
                        <div class="text-[15px] font-semibold leading-6 text-gray-900">${escapeHtml(item.ten)}</div>
                        <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs leading-5 text-gray-500">
                            <span>${escapeHtml(item.ma_sku || '-')}</span>
                            ${item.ma_vach ? `<span class="text-gray-300">|</span><span>${escapeHtml(item.ma_vach)}</span>` : ''}
                        </div>
                    </div>
                </div>
            </td>
            <td class="px-4 py-5 text-center">
                <input type="number" min="1" value="${item.so_luong}" class="w-20 rounded-xl border border-gray-200 px-2 py-2 text-center text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" onchange="capNhatSoLuongGioOrder(${item.id}, this.value)">
            </td>
            <td class="whitespace-nowrap px-4 py-5 text-right font-semibold text-gray-900">${formatTien(item.gia_order_tam_tinh)}</td>
            <td class="whitespace-nowrap px-4 py-5 text-right font-bold text-gray-950">${formatTien(item.thanh_tien_tam_tinh)}</td>
            <td class="px-4 py-5 text-right">
                <button type="button" onclick="xoaSanPhamGioOrder(${item.id})" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-100">Xóa</button>
            </td>
        </tr>
    `).join('');
}

window.capNhatSoLuongGioOrder = async (chiTietId, soLuong) => {
    const res = await fetch('/api/doi-tac/order-hang/gio-order/cap-nhat', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({ chi_tiet_id: chiTietId, so_luong: Math.max(1, parseInt(soLuong, 10) || 1) }),
    });
    const data = await res.json();
    if (!data.success) {
        showToast('Lỗi', data.message || 'Không thể cập nhật giỏ order.', 'error');
        return;
    }
    state.gioOrder = data.data;
    renderGioOrder();
};

window.xoaSanPhamGioOrder = async (chiTietId) => {
    const res = await fetch('/api/doi-tac/order-hang/gio-order/xoa', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({ chi_tiet_id: chiTietId }),
    });
    const data = await res.json();
    if (!data.success) {
        showToast('Lỗi', data.message || 'Không thể xóa sản phẩm khỏi giỏ order.', 'error');
        return;
    }
    state.gioOrder = data.data;
    renderGioOrder();
};

async function xoaTatCaGioOrder() {
    if (!confirm('Xác nhận xóa toàn bộ giỏ order?')) return;
    const res = await fetch('/api/doi-tac/order-hang/gio-order/xoa-tat-ca', {
        method: 'DELETE',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    });
    const data = await res.json();
    if (!data.success) {
        showToast('Lỗi', data.message || 'Không thể xóa giỏ order.', 'error');
        return;
    }
    state.gioOrder = data.data;
    renderGioOrder();
}

const timKhachHang = debounce(async (tuKhoa) => {
    const dropdown = document.getElementById('dropdown-khach-gio-order');
    if (!tuKhoa) {
        dropdown.classList.add('hidden');
        return;
    }
    const res = await fetch(`/api/doi-tac/order-hang/tim-khach-hang?tu_khoa=${encodeURIComponent(tuKhoa)}`);
    const data = await res.json();
    if (data.success) renderKhachHang(data.data);
}, 250);

function renderKhachHang(items) {
    const dropdown = document.getElementById('dropdown-khach-gio-order');
    state.dropdownKhach = new Map();
    items.forEach(kh => state.dropdownKhach.set(Number(kh.id), kh));
    dropdown.innerHTML = items.length
        ? items.map(kh => `
            <button type="button" class="block w-full border-b border-gray-100 p-3 text-left transition last:border-b-0 hover:bg-yellow-50" onclick="chonKhachHangGioOrder(${kh.id})">
                <div class="font-semibold text-gray-900">${escapeHtml(kh.ten)}</div>
                <div class="mt-1 text-sm text-gray-500">${escapeHtml(kh.sdt || 'Chưa có SĐT')} ${kh.ma_khach_hang ? '| ' + escapeHtml(kh.ma_khach_hang) : ''}</div>
            </button>
        `).join('')
        : '<div class="p-5 text-center text-sm text-gray-500">Không tìm thấy khách hàng được phân công</div>';
    dropdown.classList.remove('hidden');
}

window.chonKhachHangGioOrder = (id) => {
    const khachHang = state.dropdownKhach.get(Number(id));
    if (!khachHang) return;
    state.khachHang = khachHang;
    state.nhanVienBanHangId = null;
    document.getElementById('khach-gio-order-ten').textContent = khachHang.ten;
    document.getElementById('khach-gio-order-info').textContent = [khachHang.sdt, khachHang.ma_khach_hang].filter(Boolean).join(' | ');
    document.getElementById('khach-gio-order-da-chon').classList.remove('hidden');
    document.getElementById('dropdown-khach-gio-order').classList.add('hidden');
    document.getElementById('input-tim-khach-gio-order').value = '';
    renderNhanVienBanHang();
};

function xoaKhachDaChon() {
    state.khachHang = null;
    state.nhanVienBanHangId = null;
    document.getElementById('khach-gio-order-da-chon').classList.add('hidden');
    renderNhanVienBanHang();
}

function layNhanVienBanHangCuaKhach() {
    const nhanViens = state.khachHang?.nhan_viens || [];
    return nhanViens.filter(nv => ['nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'].includes(nv.quyen));
}

function renderNhanVienBanHang() {
    const box = document.getElementById('box-chon-nhan-vien-gio-order');
    const content = document.getElementById('noi-dung-chon-nhan-vien-gio-order');
    if (!box || !content || !coTheChonNhanVienOrder) return;
    if (!state.khachHang) {
        box.classList.add('hidden');
        content.innerHTML = '';
        return;
    }
    const nhanViens = layNhanVienBanHangCuaKhach();
    box.classList.remove('hidden');
    if (!nhanViens.length) {
        content.innerHTML = '<div class="rounded-xl border border-red-100 bg-white p-3 text-sm font-semibold text-red-600">Khách hàng này chưa có nhân viên bán hàng phụ trách.</div>';
        return;
    }
    if (!state.nhanVienBanHangId) state.nhanVienBanHangId = Number(nhanViens[0].id);
    content.innerHTML = nhanViens.map(nv => {
        const checked = Number(state.nhanVienBanHangId) === Number(nv.id);
        return `<label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border bg-white p-3 text-sm ${checked ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-100'}">
                <span>
                    <span class="block font-bold text-gray-900">${escapeHtml(nv.ten)}</span>
                <span class="text-xs text-gray-500">${escapeHtml(tenQuyenNhanVien(nv.quyen))}</span>
            </span>
            <input type="radio" name="nhan_vien_ban_hang_gio_order_id" value="${nv.id}" ${checked ? 'checked' : ''} onchange="chonNhanVienBanHangGioOrder(${nv.id})">
        </label>`;
    }).join('');
}

window.chonNhanVienBanHangGioOrder = (id) => {
    state.nhanVienBanHangId = Number(id);
    renderNhanVienBanHang();
};

async function taoDonOrderTuGio() {
    const items = state.gioOrder?.items || [];
    if (!state.khachHang) {
        showToast('Thiếu khách hàng', 'Vui lòng chọn khách hàng.', 'error');
        return;
    }
    if (!items.length) {
        showToast('Giỏ order trống', 'Vui lòng thêm sản phẩm order.', 'error');
        return;
    }
    if (coTheChonNhanVienOrder && !state.nhanVienBanHangId) {
        showToast('Thiếu nhân viên phụ trách', 'Vui lòng chọn nhân viên bán hàng phụ trách khách hàng.', 'error');
        return;
    }

    const btn = document.getElementById('btn-tao-order-tu-gio');
    btn.disabled = true;
    btn.textContent = 'Đang tạo...';

    try {
        const guiTaoDon = async (xacNhanTonKho = false) => {
            const res = await fetch('/api/doi-tac/order-hang/tao', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    khach_hang_id: state.khachHang.id,
                    nhan_vien_ban_hang_id: state.nhanVienBanHangId || null,
                    ghi_chu: document.getElementById('ghi-chu-gio-order').value,
                    xac_nhan_ton_kho: xacNhanTonKho,
                    xoa_gio_order: true,
                    san_phams: items.map(item => ({
                        san_pham_id: item.san_pham_id,
                        so_luong: item.so_luong,
                        gia_ban_du_kien: item.gia_order_tam_tinh,
                        nguon_hang: 'doi_nhap',
                    })),
                }),
            });
            return { res, data: await res.json() };
        };

        let { res, data } = await guiTaoDon(false);
        if (res.status === 409 && data.can_confirm_stock) {
            if (!confirm(data.message || 'Có sản phẩm còn tồn kho. Xác nhận tiếp tục tạo order?')) return;
            ({ data } = await guiTaoDon(true));
        }

        if (!data.success) {
            showToast('Lỗi', data.message || 'Không thể tạo đơn order.', 'error');
            return;
        }

        showToast('Thành công', `Đã tạo đơn ${data.data?.ma_don_order || 'order'}.`);
        await taiGioOrder();
    } finally {
        btn.disabled = false;
        btn.textContent = 'Tạo đơn order';
    }
}

document.getElementById('input-tim-khach-gio-order')?.addEventListener('input', e => timKhachHang(e.target.value));
document.getElementById('btn-xoa-khach-gio-order')?.addEventListener('click', xoaKhachDaChon);
document.getElementById('btn-xoa-gio-order')?.addEventListener('click', xoaTatCaGioOrder);
document.getElementById('btn-tao-order-tu-gio')?.addEventListener('click', taoDonOrderTuGio);
document.addEventListener('click', e => {
    if (!e.target.closest('#input-tim-khach-gio-order') && !e.target.closest('#dropdown-khach-gio-order')) {
        document.getElementById('dropdown-khach-gio-order')?.classList.add('hidden');
    }
});

taiGioOrder();
