const state = {
    khachHang: null,
    nhanVienBanHangId: null,
    nhanViensDuocGan: [],
    sanPhams: [],
    cacheKhach: new Map(),
    cacheSP: new Map(),
    dropdownKhach: new Map(),
    dropdownSP: new Map(),
};

const coTheChonNhanVien = Boolean(window.coTheChonNhanVienDonHang);

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

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function getAnh(anh) {
    if (!anh) return '/favicon.ico';
    let file = anh;
    try {
        const parsed = JSON.parse(anh);
        if (Array.isArray(parsed) && parsed.length) file = parsed[0];
    } catch (error) {
        file = anh;
    }
    if (/^https?:\/\//.test(file) || file.startsWith('/')) return file;
    return `/storage/uploads/sanpham/${file}`;
}

function chinhSachGiaId() {
    return state.khachHang?.chinh_sach_gia_id
        || state.khachHang?.nhom_khach_hang?.chinh_sach_gia_id
        || null;
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

const timKhachHang = debounce(async (tuKhoa) => {
    const dropdown = document.getElementById('dropdown-khach-don-hang');
    if (!tuKhoa) {
        dropdown.classList.add('hidden');
        return;
    }

    if (state.cacheKhach.has(tuKhoa)) {
        renderKhachHang(state.cacheKhach.get(tuKhoa));
        return;
    }

    const res = await fetch(`/api/doi-tac/don-hang/tim-khach-hang?tu_khoa=${encodeURIComponent(tuKhoa)}&so_luong=20`);
    const data = await res.json();
    if (data.success) {
        state.cacheKhach.set(tuKhoa, data.data);
        renderKhachHang(data.data);
    }
}, 250);

function renderKhachHang(items) {
    const dropdown = document.getElementById('dropdown-khach-don-hang');
    state.dropdownKhach = new Map();
    items.forEach(kh => state.dropdownKhach.set(Number(kh.id), kh));

    dropdown.innerHTML = items.length
        ? items.map(kh => `
            <button type="button" class="block w-full border-b border-gray-100 p-3 text-left transition last:border-b-0 hover:bg-yellow-50"
                onclick="chonKhachDonHang(${kh.id})">
                <div class="font-semibold text-gray-900">${escapeHtml(kh.ten)}</div>
                <div class="mt-1 text-sm text-gray-500">${escapeHtml(kh.sdt || 'Chưa có SĐT')} ${kh.ma_khach_hang ? '| ' + escapeHtml(kh.ma_khach_hang) : ''}</div>
            </button>
        `).join('')
        : '<div class="p-5 text-center text-sm text-gray-500">Không tìm thấy khách hàng phù hợp</div>';

    dropdown.classList.remove('hidden');
}

window.chonKhachDonHang = async (id) => {
    const khachHang = state.dropdownKhach.get(Number(id));
    if (!khachHang) return;

    state.khachHang = khachHang;
    state.nhanVienBanHangId = null;
    state.nhanViensDuocGan = [];
    document.getElementById('khach-don-hang-ten').textContent = khachHang.ten;
    document.getElementById('khach-don-hang-info').textContent = [khachHang.sdt, khachHang.ma_khach_hang].filter(Boolean).join(' | ');
    document.getElementById('khach-don-hang-da-chon').classList.remove('hidden');
    document.getElementById('dropdown-khach-don-hang').classList.add('hidden');
    document.getElementById('input-tim-khach-don-hang').value = '';

    await loadNhanVienDuocGan(id);
    await capNhatGiaSanPhamTheoKhach();
};

window.xoaKhachDonHangDaChon = () => {
    state.khachHang = null;
    state.nhanVienBanHangId = null;
    state.nhanViensDuocGan = [];
    document.getElementById('khach-don-hang-da-chon').classList.add('hidden');
    document.getElementById('box-chon-nhan-vien-don-hang')?.classList.add('hidden');
    state.sanPhams = [];
    renderBang();
};

async function loadNhanVienDuocGan(khachHangId) {
    const box = document.getElementById('box-chon-nhan-vien-don-hang');
    const content = document.getElementById('noi-dung-chon-nhan-vien-don-hang');
    if (!coTheChonNhanVien || !box || !content) return;

    box.classList.remove('hidden');
    content.innerHTML = '<div class="rounded-xl border border-gray-100 bg-white p-3 text-sm text-gray-500">Đang tải nhân viên phụ trách...</div>';

    const res = await fetch(`/api/doi-tac/don-hang/khach-hang/${khachHangId}/nhan-vien-duoc-gan`);
    const data = await res.json();
    const nhanViens = data.success ? data.data : [];
    state.nhanViensDuocGan = nhanViens.filter(nv => ['nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'].includes(nv.quyen));

    if (state.nhanViensDuocGan.length === 1) {
        state.nhanVienBanHangId = Number(state.nhanViensDuocGan[0].id);
    }

    renderNhanVienDuocGan();
}

function renderNhanVienDuocGan() {
    const content = document.getElementById('noi-dung-chon-nhan-vien-don-hang');
    if (!content) return;

    if (!state.nhanViensDuocGan.length) {
        content.innerHTML = '<div class="rounded-xl border border-red-100 bg-white p-3 text-sm font-semibold text-red-600">Khách hàng này chưa có nhân viên bán hàng phụ trách.</div>';
        return;
    }

    content.innerHTML = state.nhanViensDuocGan.map(nv => {
        const checked = Number(state.nhanVienBanHangId) === Number(nv.id);
        return `<label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border bg-white p-3 text-sm ${checked ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-100'}">
            <span>
                <span class="block font-bold text-gray-900">${escapeHtml(nv.ten)}</span>
                <span class="text-xs text-gray-500">${escapeHtml(nv.quyen || 'Nhân viên')}</span>
            </span>
            <input type="radio" name="nhan_vien_ban_hang_id" value="${nv.id}" ${checked ? 'checked' : ''} onchange="chonNhanVienBanHangDonHang(${nv.id})">
        </label>`;
    }).join('');
}

window.chonNhanVienBanHangDonHang = (id) => {
    state.nhanVienBanHangId = Number(id);
    renderNhanVienDuocGan();
};

async function tuChonKhachMacDinhNeuCo() {
    try {
        const res = await fetch('/api/doi-tac/don-hang/khach-hang-mac-dinh', {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        const khachHang = data.success && data.auto_select ? data.data : null;
        if (!khachHang?.id || state.khachHang) return;

        state.dropdownKhach.set(Number(khachHang.id), khachHang);
        await window.chonKhachDonHang(khachHang.id);
    } catch (error) {
        console.error('Loi tu chon khach hang mac dinh:', error);
    }
}

const timSanPham = debounce(async (tuKhoa) => {
    const dropdown = document.getElementById('dropdown-sp-don-hang');
    if (!tuKhoa) {
        dropdown.classList.add('hidden');
        return;
    }

    if (!state.khachHang) {
        showToast('error', 'Thiếu khách hàng', 'Vui lòng chọn khách hàng trước khi tìm sản phẩm');
        dropdown.classList.add('hidden');
        return;
    }

    const cacheKey = `${chinhSachGiaId() || 'default'}:${tuKhoa}`;
    if (state.cacheSP.has(cacheKey)) {
        renderSanPham(state.cacheSP.get(cacheKey));
        return;
    }

    const params = new URLSearchParams({
        tu_khoa: tuKhoa,
        so_luong: '20',
    });
    if (chinhSachGiaId()) params.set('chinh_sach_gia_id', chinhSachGiaId());

    const res = await fetch(`/api/doi-tac/don-hang/tim-san-pham?${params.toString()}`);
    const data = await res.json();
    if (data.success) {
        state.cacheSP.set(cacheKey, data.data);
        renderSanPham(data.data);
    }
}, 250);

function renderSanPham(items) {
    const dropdown = document.getElementById('dropdown-sp-don-hang');
    state.dropdownSP = new Map();
    items.forEach(sp => state.dropdownSP.set(Number(sp.id), sp));

    dropdown.innerHTML = items.length
        ? items.map(sp => `
            <button type="button" class="flex w-full gap-3 border-b border-gray-100 p-3 text-left transition last:border-b-0 hover:bg-yellow-50 ${sp.co_the_chon ? '' : 'opacity-60'}"
                onclick="chonSanPhamDonHang(${sp.id})">
                <img src="${getAnh(sp.anh_san_pham)}" onerror="this.style.display='none'" class="h-12 w-12 flex-shrink-0 rounded-xl border border-gray-100 bg-gray-50 object-cover">
                <div class="min-w-0">
                    <div class="truncate font-semibold text-gray-900">${escapeHtml(sp.ten)}</div>
                    <div class="mt-1 text-sm text-gray-500">${escapeHtml(sp.ma_sku || '-')} | Giá: ${formatTien(sp.gia)} | Có thể bán: ${formatTien(sp.co_the_ban || 0)}</div>
                </div>
            </button>
        `).join('')
        : '<div class="p-5 text-center text-sm text-gray-500">Không tìm thấy sản phẩm</div>';

    dropdown.classList.remove('hidden');
}

window.chonSanPhamDonHang = (id) => {
    const sp = state.dropdownSP.get(Number(id));
    if (!sp) return;

    if (!sp.co_the_chon || Number(sp.co_the_ban || 0) <= 0) {
        showToast('error', 'Không đủ hàng', 'Sản phẩm này hiện không còn số lượng có thể bán');
        return;
    }

    const index = state.sanPhams.findIndex(item => Number(item.san_pham_id) === Number(id));
    if (index >= 0) {
        state.sanPhams[index].so_luong += 1;
    } else {
        state.sanPhams.unshift({
            san_pham_id: sp.id,
            ten: sp.ten,
            ma_sku: sp.ma_sku,
            anh: sp.anh_san_pham,
            so_luong: 1,
            don_gia: Number(sp.gia || 0),
            co_the_ban: Number(sp.co_the_ban || 0),
        });
    }

    document.getElementById('dropdown-sp-don-hang').classList.add('hidden');
    document.getElementById('input-tim-sp-don-hang').value = '';
    renderBang();
};

async function capNhatGiaSanPhamTheoKhach() {
    if (!state.sanPhams.length || !state.khachHang) return;
    const ids = state.sanPhams.map(sp => sp.san_pham_id);

    const res = await fetch('/api/doi-tac/don-hang/lay-gia-theo-chinh-sach', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({
            san_pham_ids: ids,
            chinh_sach_gia_id: chinhSachGiaId(),
        }),
    });
    const data = await res.json();
    if (!data.success) return;

    state.sanPhams = state.sanPhams.map(sp => ({
        ...sp,
        don_gia: Number(data.data?.[sp.san_pham_id] ?? sp.don_gia),
    }));
    renderBang();
}

window.capNhatSoLuongDonHang = (index, value) => {
    const soLuong = Math.max(1, parseInt(value, 10) || 1);
    const gioiHan = Number(state.sanPhams[index].co_the_ban || soLuong);
    state.sanPhams[index].so_luong = Math.min(soLuong, Math.max(1, gioiHan));
    renderBang();
};

window.capNhatGiaDonHang = (index, value) => {
    state.sanPhams[index].don_gia = Math.max(0, Number(value || 0));
    renderBang();
};

window.xoaSanPhamDonHang = (index) => {
    state.sanPhams.splice(index, 1);
    renderBang();
};

function renderBang() {
    const tbody = document.getElementById('tbody-sp-don-hang');
    const empty = document.getElementById('empty-sp-don-hang');

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
                            <div class="text-xs text-gray-500">${escapeHtml(sp.ma_sku || '-')} | Có thể bán: ${formatTien(sp.co_the_ban || 0)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <input type="number" min="1" max="${sp.co_the_ban || ''}" value="${sp.so_luong}" class="w-20 rounded-xl border border-gray-200 px-2 py-2 text-center text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" onchange="capNhatSoLuongDonHang(${index}, this.value)">
                </td>
                <td class="px-4 py-3 text-right">
                    <input type="number" min="0" value="${sp.don_gia}" class="w-32 rounded-xl border border-gray-200 px-2 py-2 text-right text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" onchange="capNhatGiaDonHang(${index}, this.value)">
                </td>
                <td class="px-4 py-3 text-right font-semibold text-gray-900">${formatTien(sp.so_luong * sp.don_gia)}</td>
                <td class="px-4 py-3 text-right">
                    <button onclick="xoaSanPhamDonHang(${index})" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-600">Xóa</button>
                </td>
            </tr>
        `).join('');
    }

    capNhatTong();
}

function capNhatTong() {
    const tongSL = state.sanPhams.reduce((sum, sp) => sum + sp.so_luong, 0);
    const tamTinh = state.sanPhams.reduce((sum, sp) => sum + sp.so_luong * sp.don_gia, 0);
    const chietKhau = Math.max(0, Math.min(100, Number(document.getElementById('chiet-khau-don-hang')?.value || 0)));
    const tongTien = tamTinh - (tamTinh * chietKhau / 100);
    document.getElementById('tong-sp-don-hang').textContent = state.sanPhams.length;
    document.getElementById('tong-sl-don-hang').textContent = tongSL;
    document.getElementById('tong-tien-don-hang').textContent = formatTien(tongTien);
}

window.taoDonHangThuong = async () => {
    if (!state.khachHang) {
        showToast('error', 'Thiếu khách hàng', 'Vui lòng chọn khách hàng');
        return;
    }
    if (!state.sanPhams.length) {
        showToast('error', 'Thiếu sản phẩm', 'Vui lòng chọn sản phẩm');
        return;
    }
    if (coTheChonNhanVien && !state.nhanVienBanHangId) {
        showToast('error', 'Thiếu nhân viên phụ trách', 'Vui lòng chọn nhân viên bán hàng phụ trách khách hàng');
        return;
    }

    const btn = document.getElementById('btn-tao-don-hang');
    btn.disabled = true;
    btn.textContent = 'Đang tạo...';

    try {
        const res = await fetch('/api/doi-tac/don-hang/tao', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                khach_hang_id: state.khachHang.id,
                dia_chi_id: state.khachHang.dia_chi_mac_dinh?.id || null,
                nhan_vien_ban_hang_id: state.nhanVienBanHangId || null,
                chiet_khau: Number(document.getElementById('chiet-khau-don-hang')?.value || 0),
                hen_giao: document.getElementById('hen-giao-don-hang')?.value || null,
                ghi_chu: document.getElementById('ghi-chu-don-hang').value,
                san_phams: state.sanPhams.map(sp => ({
                    san_pham_id: sp.san_pham_id,
                    so_luong: sp.so_luong,
                    don_gia: sp.don_gia,
                    chiet_khau: 0,
                })),
            }),
        });
        const data = await res.json();

        if (data.success) {
            const maDonHang = data.data?.ma_don_hang || '';
            showToast('success', 'Thành công', `Đã tạo đơn hàng ${maDonHang}`);
            const query = maDonHang ? `?search=${encodeURIComponent(maDonHang)}` : '';
            setTimeout(() => window.location.href = `/doi-tac/don-hang${query}`, 800);
        } else {
            showToast('error', 'Lỗi', data.message || 'Không thể tạo đơn hàng');
        }
    } finally {
        btn.disabled = false;
        btn.textContent = 'Tạo đơn hàng';
    }
};

document.getElementById('input-tim-khach-don-hang')?.addEventListener('input', e => timKhachHang(e.target.value));
document.getElementById('input-tim-sp-don-hang')?.addEventListener('input', e => timSanPham(e.target.value));
document.getElementById('chiet-khau-don-hang')?.addEventListener('input', capNhatTong);
document.addEventListener('click', e => {
    if (!e.target.closest('#input-tim-khach-don-hang') && !e.target.closest('#dropdown-khach-don-hang')) {
        document.getElementById('dropdown-khach-don-hang')?.classList.add('hidden');
    }
    if (!e.target.closest('#input-tim-sp-don-hang') && !e.target.closest('#dropdown-sp-don-hang')) {
        document.getElementById('dropdown-sp-don-hang')?.classList.add('hidden');
    }
});

renderBang();
tuChonKhachMacDinhNeuCo();
