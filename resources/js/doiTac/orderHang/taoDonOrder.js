const state = {
    khachHang: null,
    nhanVienBanHangId: null,
    sanPhams: [],
    cacheKhach: new Map(),
    cacheSP: new Map(),
    dropdownKhach: new Map(),
    dropdownSP: new Map(),
};

const coTheChonNhanVienOrder = Boolean(window.coTheChonNhanVienOrder);

function formatTien(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
}

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

function tenQuyenNhanVien(quyen) {
    return {
        nhan_vien_ban_hang_cap_1: 'Nhân viên bán hàng cấp 1',
        nhan_vien_ban_hang_cap_2: 'Nhân viên bán hàng cấp 2',
    }[quyen] || quyen || 'Nhân viên';
}

function getAnh(anh) {
    if (!anh) return '/favicon.ico';
    return `/storage/uploads/sanpham/${anh}`;
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function showToast(type, title, message) {
    const toast = document.getElementById('toast-notification');
    if (!toast) return;
    toast.classList.toggle('border-red-200', type === 'error');
    toast.classList.toggle('border-green-200', type !== 'error');
    document.getElementById('toast-title').textContent = title;
    document.getElementById('toast-message').textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3500);
}

function layChinhSachGiaCuaKhachHang() {
    return state.khachHang?.nhom_khach_hang?.chinh_sach_gia || null;
}

function layGiaTheoChinhSach(sp, chinhSachGia) {
    if (!chinhSachGia) return null;

    const giaCustom = Array.isArray(sp.chinh_sach_gias)
        ? sp.chinh_sach_gias.find(item => Number(item.chinh_sach_gia_id) === Number(chinhSachGia.id))
        : null;

    if (giaCustom && Number(giaCustom.gia) > 0) return Number(giaCustom.gia);

    const giaTheoCode = {
        gia_ban_le: sp.gia_ban_le,
        gia_ban_buon: sp.gia_ban_buon,
        gia_cong_tac_vien: sp.gia_cong_tac_vien,
        gia_order: sp.gia_order,
    }[chinhSachGia.code];

    return Number(giaTheoCode || 0) > 0 ? Number(giaTheoCode) : null;
}

function tinhGiaDuKien(sp) {
    return Number(sp.gia_order || 0) > 0 ? Number(sp.gia_order) : 0;
}

function capNhatGiaTuDongTheoKhachHang() {
    state.sanPhams.forEach(item => {
        if (item.san_pham_goc) item.gia_ban_du_kien = tinhGiaDuKien(item.san_pham_goc);
    });
    renderBang();
}

const timKhachHang = debounce(async (tuKhoa) => {
    const dropdown = document.getElementById('dropdown-khach-order');
    if (!tuKhoa) {
        dropdown.classList.add('hidden');
        return;
    }
    if (state.cacheKhach.has(tuKhoa)) {
        renderKhachHang(state.cacheKhach.get(tuKhoa));
        return;
    }

    const res = await fetch(`/api/doi-tac/order-hang/tim-khach-hang?tu_khoa=${encodeURIComponent(tuKhoa)}`);
    const data = await res.json();
    if (data.success) {
        state.cacheKhach.set(tuKhoa, data.data);
        renderKhachHang(data.data);
    }
}, 250);

function renderKhachHang(items) {
    const dropdown = document.getElementById('dropdown-khach-order');
    state.dropdownKhach = new Map();
    items.forEach(kh => state.dropdownKhach.set(Number(kh.id), kh));

    dropdown.innerHTML = items.length
        ? items.map(kh => `
            <button type="button" class="block w-full border-b border-gray-100 p-3 text-left transition last:border-b-0 hover:bg-yellow-50"
                onclick="chonKhachHang(${kh.id})">
                <div class="font-semibold text-gray-900">${escapeHtml(kh.ten)}</div>
                <div class="mt-1 text-sm text-gray-500">${escapeHtml(kh.sdt || 'Chưa có SĐT')} ${kh.ma_khach_hang ? '| ' + escapeHtml(kh.ma_khach_hang) : ''}</div>
            </button>
        `).join('')
        : '<div class="p-5 text-center text-sm text-gray-500">Không tìm thấy khách hàng được phân công</div>';

    dropdown.classList.remove('hidden');
}

window.chonKhachHang = (id) => {
    const khachHang = state.dropdownKhach.get(Number(id));
    if (!khachHang) return;
    state.khachHang = khachHang;
    state.nhanVienBanHangId = null;
    document.getElementById('khach-ten').textContent = khachHang.ten;
    document.getElementById('khach-info').textContent = [khachHang.sdt, khachHang.ma_khach_hang].filter(Boolean).join(' | ');
    document.getElementById('khach-da-chon').classList.remove('hidden');
    document.getElementById('dropdown-khach-order').classList.add('hidden');
    document.getElementById('input-tim-khach-order').value = '';
    renderNhanVienBanHang();
    capNhatGiaTuDongTheoKhachHang();
};

async function tuChonKhachMacDinhNeuCo() {
    if (state.khachHang) return;

    try {
        const res = await fetch('/api/doi-tac/order-hang/khach-hang-mac-dinh', {
            headers: {
                Accept: 'application/json',
            },
        });
        const data = await res.json();
        const khachHang = data.success && data.auto_select ? data.data : null;

        if (!res.ok || !khachHang?.id || state.khachHang) {
            return;
        }

        state.dropdownKhach.set(Number(khachHang.id), khachHang);
        window.chonKhachHang(khachHang.id);
    } catch (error) {
        console.error('Loi tu chon khach order mac dinh doi tac:', error);
    }
}

window.xoaKhachDaChon = () => {
    state.khachHang = null;
    state.nhanVienBanHangId = null;
    document.getElementById('khach-da-chon').classList.add('hidden');
    renderNhanVienBanHang();
    capNhatGiaTuDongTheoKhachHang();
};

function layNhanVienBanHangCuaKhach() {
    const nhanViens = state.khachHang?.nhan_viens || [];
    return nhanViens.filter(nv => ['nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'].includes(nv.quyen));
}

function renderNhanVienBanHang() {
    const box = document.getElementById('box-chon-nhan-vien-order');
    const content = document.getElementById('noi-dung-chon-nhan-vien-order');
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

    if (!state.nhanVienBanHangId) {
        state.nhanVienBanHangId = Number(nhanViens[0].id);
    }

    content.innerHTML = nhanViens.map(nv => {
        const checked = Number(state.nhanVienBanHangId) === Number(nv.id);
        return `<label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border bg-white p-3 text-sm ${checked ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-100'}">
            <span>
                <span class="block font-bold text-gray-900">${escapeHtml(nv.ten)}</span>
                <span class="text-xs text-gray-500">${escapeHtml(tenQuyenNhanVien(nv.quyen))}</span>
            </span>
            <input type="radio" name="nhan_vien_ban_hang_id" value="${nv.id}" ${checked ? 'checked' : ''} onchange="chonNhanVienBanHangOrder(${nv.id})">
        </label>`;
    }).join('');
}

window.chonNhanVienBanHangOrder = (id) => {
    state.nhanVienBanHangId = Number(id);
    renderNhanVienBanHang();
};

const timSanPhamOrder = debounce(async (tuKhoa) => {
    const dropdown = document.getElementById('dropdown-sp-order');
    if (!tuKhoa) {
        dropdown.classList.add('hidden');
        return;
    }
    if (state.cacheSP.has(tuKhoa)) {
        renderSanPham(state.cacheSP.get(tuKhoa));
        return;
    }

    const res = await fetch(`/api/doi-tac/order-hang/tim-san-pham-order?tu_khoa=${encodeURIComponent(tuKhoa)}&per_page=20`);
    const data = await res.json();
    if (data.success) {
        state.cacheSP.set(tuKhoa, data.data);
        renderSanPham(data.data);
    }
}, 250);

function renderSanPham(items) {
    const dropdown = document.getElementById('dropdown-sp-order');
    state.dropdownSP = new Map();
    items.forEach(sp => state.dropdownSP.set(Number(sp.id), sp));

    dropdown.innerHTML = items.length
        ? items.map(sp => `
            <button type="button" class="flex w-full gap-3 border-b border-gray-100 p-3 text-left transition last:border-b-0 hover:bg-yellow-50"
                onclick="chonSanPhamOrder(${sp.id})">
                <img src="${getAnh(sp.anh_chinh)}" onerror="this.style.display='none'" class="h-12 w-12 flex-shrink-0 rounded-xl border border-gray-100 bg-gray-50 object-cover">
                <div class="min-w-0">
                    <div class="truncate font-semibold text-gray-900">${escapeHtml(sp.ten)}</div>
                    <div class="mt-1 text-sm text-gray-500">${escapeHtml(sp.ma_sku || '-')} | Giá: ${formatTien(tinhGiaDuKien(sp))} | Tồn: ${formatTien(sp.ton_kho || 0)}</div>
                </div>
            </button>
        `).join('')
        : '<div class="p-5 text-center text-sm text-gray-500">Không tìm thấy sản phẩm order</div>';

    dropdown.classList.remove('hidden');
}

window.chonSanPhamOrder = async (id) => {
    const res = await fetch(`/api/doi-tac/order-hang/san-pham-order/${id}?_t=${Date.now()}`, {
        headers: { Accept: 'application/json' },
    });
    const data = await res.json();
    if (!data.success) {
        showToast('error', 'Không thể chọn sản phẩm', data.message || 'Sản phẩm không hợp lệ');
        return;
    }

    const sp = data.data;
    if (Number(sp.gia_order || 0) <= 0) {
        showToast('error', 'Thieu gia order', 'San pham nay chua co gia order hop le');
        return;
    }

    const index = state.sanPhams.findIndex(item => Number(item.san_pham_id) === Number(id));
    if (index >= 0) {
        state.sanPhams[index].so_luong += 1;
        state.sanPhams[index].san_pham_goc = sp;
        state.sanPhams[index].gia_ban_du_kien = tinhGiaDuKien(sp);
    } else {
        state.sanPhams.unshift({
            san_pham_id: sp.id,
            ten: sp.ten,
            ma_sku: sp.ma_sku,
            anh: sp.anh_chinh,
            so_luong: 1,
            gia_ban_du_kien: tinhGiaDuKien(sp),
            san_pham_goc: sp,
            ton_kho: Number(sp.ton_kho || 0),
            nguon_hang: 'doi_nhap',
        });
    }

    document.getElementById('dropdown-sp-order').classList.add('hidden');
    document.getElementById('input-tim-sp-order').value = '';
    renderBang();
};

function renderBang() {
    const tbody = document.getElementById('tbody-sp-order');
    const empty = document.getElementById('empty-sp-order');

    if (!state.sanPhams.length) {
        tbody.innerHTML = '';
        empty.classList.remove('hidden');
    } else {
        empty.classList.add('hidden');
        tbody.innerHTML = state.sanPhams.map((sp, index) => `
            <tr>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img src="${getAnh(sp.anh)}" onerror="this.style.display='none'" class="h-12 w-12 rounded-xl border border-gray-100 bg-gray-50 object-cover">
                        <div class="min-w-0">
                            <div class="truncate font-semibold text-gray-900">${escapeHtml(sp.ten)}</div>
                            <div class="text-xs text-gray-500">${escapeHtml(sp.ma_sku || '-')} ${Number(sp.ton_kho || 0) > 0 ? '| Đợi hàng nhập về' : ''}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <input type="number" min="1" value="${sp.so_luong}" class="w-20 rounded-xl border border-gray-200 px-2 py-2 text-center text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" onchange="capNhatSoLuong(${index}, this.value)">
                </td>
                <td class="px-4 py-3 text-right font-semibold text-gray-900">${formatTien(sp.gia_ban_du_kien)}</td>
                <td class="px-4 py-3 text-right">
                    <button onclick="xoaSanPham(${index})" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600">Xóa</button>
                </td>
            </tr>
        `).join('');
    }

    capNhatTong();
}

window.capNhatSoLuong = (index, value) => {
    state.sanPhams[index].so_luong = Math.max(1, parseInt(value, 10) || 1);
    renderBang();
};

window.xoaSanPham = (index) => {
    state.sanPhams.splice(index, 1);
    renderBang();
};

function capNhatTong() {
    const tongSL = state.sanPhams.reduce((sum, sp) => sum + sp.so_luong, 0);
    const tongTien = state.sanPhams.reduce((sum, sp) => sum + sp.so_luong * sp.gia_ban_du_kien, 0);
    document.getElementById('tong-sp-order').textContent = state.sanPhams.length;
    document.getElementById('tong-sl-order').textContent = tongSL;
    document.getElementById('tong-tien-order').textContent = formatTien(tongTien);
}

window.taoDonOrder = async () => {
    if (!state.khachHang) {
        showToast('error', 'Thiếu khách hàng', 'Vui lòng chọn khách hàng');
        return;
    }
    if (!state.sanPhams.length) {
        showToast('error', 'Thiếu sản phẩm', 'Vui lòng chọn sản phẩm order');
        return;
    }

    if (coTheChonNhanVienOrder && !state.nhanVienBanHangId) {
        showToast('error', 'Thiếu nhân viên phụ trách', 'Vui lòng chọn nhân viên bán hàng phụ trách khách hàng');
        return;
    }

    const btn = document.getElementById('btn-tao-order');
    btn.disabled = true;
    btn.textContent = 'Đang tạo...';

    try {
        const guiTaoDon = (xacNhanTonKho = false) => fetch('/api/doi-tac/order-hang/tao', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                khach_hang_id: state.khachHang.id,
                nhan_vien_ban_hang_id: state.nhanVienBanHangId || null,
                ghi_chu: document.getElementById('ghi-chu-order').value,
                xac_nhan_ton_kho: xacNhanTonKho,
                san_phams: state.sanPhams.map(sp => ({
                    san_pham_id: sp.san_pham_id,
                    so_luong: sp.so_luong,
                    gia_ban_du_kien: sp.gia_ban_du_kien,
                    nguon_hang: sp.nguon_hang || 'doi_nhap',
                })),
            }),
        });

        let res = await guiTaoDon(false);
        let data = await res.json();

        if (res.status === 409 && data.can_confirm_stock) {
            const dong = (data.data?.san_phams || []).slice(0, 5).map(sp => `${sp.ma_sku || sp.ten}: tồn ${formatTien(sp.ton_kho)}`).join('\n');
            if (!confirm(`${data.message || 'Có sản phẩm còn tồn kho.'}${dong ? `\n\n${dong}` : ''}`)) return;
            res = await guiTaoDon(true);
            data = await res.json();
        }

        if (data.success) {
            const maDonHang = data.data?.ma_don_hang || '';
            showToast('success', 'Thành công', `Đã tạo đơn ${maDonHang || 'order'}`);
            const query = maDonHang ? `?search=${encodeURIComponent(maDonHang)}` : '';
            setTimeout(() => window.location.href = `/doi-tac/order-hang/danh-sach${query}`, 800);
        } else {
            showToast('error', 'Lỗi', data.message || 'Không thể tạo đơn order');
        }
    } finally {
        btn.disabled = false;
        btn.textContent = 'Tạo đơn order';
    }
};

document.getElementById('input-tim-khach-order')?.addEventListener('input', e => timKhachHang(e.target.value));
document.getElementById('input-tim-sp-order')?.addEventListener('input', e => timSanPhamOrder(e.target.value));
document.addEventListener('click', e => {
    if (!e.target.closest('#input-tim-khach-order') && !e.target.closest('#dropdown-khach-order')) {
        document.getElementById('dropdown-khach-order')?.classList.add('hidden');
    }
    if (!e.target.closest('#input-tim-sp-order') && !e.target.closest('#dropdown-sp-order')) {
        document.getElementById('dropdown-sp-order')?.classList.add('hidden');
    }
});
renderBang();
tuChonKhachMacDinhNeuCo();
