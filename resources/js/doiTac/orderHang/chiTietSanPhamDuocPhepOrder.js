let danhSachPhienBan = [];
let phienBanDangChon = null;

const stateTaoNhanh = {
    khachHang: null,
    nhanVienBanHangId: null,
    dropdownKhach: new Map(),
};

const coTheChonNhanVienOrder = Boolean(window.coTheChonNhanVienOrder);

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

function formatNumber(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
}

function formatCurrency(value) {
    return Number(value || 0) > 0 ? `${formatNumber(value)}đ` : 'Chưa đặt giá';
}

function formatDate(value) {
    return value ? new Date(value).toLocaleString('vi-VN') : '-';
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function debounce(fn, wait) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
}

function getAnh(anh) {
    if (!anh) return '/favicon.ico';
    return `/storage/uploads/sanpham/${anh}`;
}

function showToast(title, message, type = 'success') {
    const toast = document.getElementById('toast-notification');
    if (!toast) return;
    toast.classList.toggle('border-red-200', type === 'error');
    toast.classList.toggle('border-green-200', type !== 'error');
    document.getElementById('toast-title').textContent = title;
    document.getElementById('toast-message').textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function giaRange(thap, cao) {
    if (!Number(thap || 0) && !Number(cao || 0)) return 'Chưa đặt giá';
    if (Number(thap || 0) === Number(cao || 0)) return formatCurrency(thap);
    return `${formatCurrency(thap)} - ${formatCurrency(cao)}`;
}

function renderAnh(data) {
    const anhChinh = document.getElementById('anh-chinh-sp-order');
    const dsAnh = document.getElementById('ds-anh-sp-order');
    const anh = data.anh || [];
    anhChinh.src = getAnh(anh[0]);
    anhChinh.alt = data.ten_chung || 'Sản phẩm';
    anhChinh.onerror = () => {
        anhChinh.parentElement.innerHTML = '<div class="flex h-full w-full items-center justify-center text-6xl text-gray-200">SP</div>';
    };

    dsAnh.innerHTML = anh.slice(0, 5).map((item, index) => `
        <button type="button" class="aspect-square overflow-hidden rounded-lg border-2 ${index === 0 ? 'border-yellow-400' : 'border-gray-100 hover:border-gray-300'}" onclick="doiAnhChinh('${escapeHtml(item)}', this)">
            <img src="${getAnh(item)}" alt="" class="h-full w-full object-cover">
        </button>
    `).join('');
}

window.doiAnhChinh = (anh, button) => {
    document.getElementById('anh-chinh-sp-order').src = getAnh(anh);
    document.querySelectorAll('#ds-anh-sp-order button').forEach(item => {
        item.classList.remove('border-yellow-400');
        item.classList.add('border-gray-100');
    });
    button.classList.add('border-yellow-400');
    button.classList.remove('border-gray-100');
};

function renderPhienBanButtons(phienBans) {
    const wrap = document.getElementById('ds-phien-ban-order');
    wrap.innerHTML = phienBans.map((item, index) => `
        <button type="button"
            class="rounded-xl border-2 px-4 py-2 text-sm font-medium transition ${index === 0 ? 'border-yellow-400 bg-yellow-50 text-gray-900' : 'border-gray-200 text-gray-600 hover:border-gray-400'}"
            onclick="chonPhienBanOrder(${item.id}, this)">
            ${escapeHtml(item.ten || item.ma_sku || '-')}
        </button>
    `).join('');
}

function renderThongTinPhienBan(item) {
    if (!item) return;
    phienBanDangChon = item;

    document.getElementById('pb-ten').textContent = item.ten || '-';
    document.getElementById('pb-ton').textContent = `Tồn ${formatNumber(item.ton_kho || 0)}`;
    document.getElementById('pb-sku').textContent = item.ma_sku || '-';
    document.getElementById('pb-ma-vach').textContent = item.ma_vach || '-';
    document.getElementById('pb-gia-order').textContent = formatCurrency(item.gia_order);
    document.getElementById('pb-gia-ban-le')?.closest('div')?.classList.add('hidden');
    document.getElementById('pb-co-the-ban').textContent = formatNumber(item.co_the_ban || 0);
    document.getElementById('pb-order-listed-at').textContent = formatDate(item.order_listed_at);
}

function laySoLuongOrderNhanh() {
    return Math.max(1, parseInt(document.getElementById('so-luong-order-nhanh')?.value, 10) || 1);
}

async function themVaoGioOrder() {
    if (!phienBanDangChon) {
        showToast('Thiếu phiên bản', 'Vui lòng chọn phiên bản sản phẩm order.', 'error');
        return;
    }

    if (Number(phienBanDangChon.gia_order || 0) <= 0) {
        showToast('Thieu gia order', 'San pham nay chua co gia order hop le.', 'error');
        return;
    }

    const button = document.getElementById('btn-them-gio-order');
    button.disabled = true;
    try {
        const res = await fetch('/api/doi-tac/order-hang/gio-order/them', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                san_pham_id: phienBanDangChon.id,
                so_luong: laySoLuongOrderNhanh(),
            }),
        });
        const data = await res.json();
        if (!data.success) {
            showToast('Lỗi', data.message || 'Không thể thêm vào giỏ order.', 'error');
            return;
        }
        showToast('Đã thêm vào giỏ order', 'Sản phẩm đã được lưu vào giỏ order của tài khoản này.');
    } finally {
        button.disabled = false;
    }
}

function moModalTaoOrderNhanh() {
    if (!phienBanDangChon) {
        showToast('Thiếu phiên bản', 'Vui lòng chọn phiên bản sản phẩm order.', 'error');
        return;
    }

    const soLuong = laySoLuongOrderNhanh();
    document.getElementById('tao-order-nhanh-san-pham').textContent = document.getElementById('ten-sp-order').textContent || '';
    document.getElementById('tao-order-nhanh-phien-ban').textContent = phienBanDangChon.ten || phienBanDangChon.ma_sku || '-';
    document.getElementById('tao-order-nhanh-so-luong').textContent = formatNumber(soLuong);
    document.getElementById('tao-order-nhanh-gia').textContent = formatCurrency(phienBanDangChon.gia_order);
    document.getElementById('modal-tao-order-nhanh').classList.remove('hidden');
    tuChonKhachMacDinhTaoNhanhNeuCo();
}

function dongModalTaoOrderNhanh() {
    document.getElementById('modal-tao-order-nhanh').classList.add('hidden');
}

const timKhachHangTaoNhanh = debounce(async (tuKhoa) => {
    const dropdown = document.getElementById('dropdown-khach-tao-nhanh');
    if (!tuKhoa) {
        dropdown.classList.add('hidden');
        return;
    }
    const res = await fetch(`/api/doi-tac/order-hang/tim-khach-hang?tu_khoa=${encodeURIComponent(tuKhoa)}`);
    const data = await res.json();
    if (data.success) renderKhachHangTaoNhanh(data.data);
}, 250);

function renderKhachHangTaoNhanh(items) {
    const dropdown = document.getElementById('dropdown-khach-tao-nhanh');
    stateTaoNhanh.dropdownKhach = new Map();
    items.forEach(kh => stateTaoNhanh.dropdownKhach.set(Number(kh.id), kh));
    dropdown.innerHTML = items.length
        ? items.map(kh => `
            <button type="button" class="block w-full border-b border-gray-100 p-3 text-left transition last:border-b-0 hover:bg-yellow-50" onclick="chonKhachHangTaoNhanh(${kh.id})">
                <div class="font-semibold text-gray-900">${escapeHtml(kh.ten)}</div>
                <div class="mt-1 text-sm text-gray-500">${escapeHtml(kh.sdt || 'Chưa có SĐT')} ${kh.ma_khach_hang ? '| ' + escapeHtml(kh.ma_khach_hang) : ''}</div>
            </button>
        `).join('')
        : '<div class="p-5 text-center text-sm text-gray-500">Không tìm thấy khách hàng được phân công</div>';
    dropdown.classList.remove('hidden');
}

window.chonKhachHangTaoNhanh = (id) => {
    const khachHang = stateTaoNhanh.dropdownKhach.get(Number(id));
    if (!khachHang) return;
    stateTaoNhanh.khachHang = khachHang;
    stateTaoNhanh.nhanVienBanHangId = null;
    document.getElementById('khach-tao-nhanh-ten').textContent = khachHang.ten;
    document.getElementById('khach-tao-nhanh-info').textContent = [khachHang.sdt, khachHang.ma_khach_hang].filter(Boolean).join(' | ');
    document.getElementById('khach-tao-nhanh-da-chon').classList.remove('hidden');
    document.getElementById('dropdown-khach-tao-nhanh').classList.add('hidden');
    document.getElementById('input-tim-khach-tao-nhanh').value = '';
    renderNhanVienBanHangTaoNhanh();
};

async function tuChonKhachMacDinhTaoNhanhNeuCo() {
    if (stateTaoNhanh.khachHang) return;

    try {
        const res = await fetch('/api/doi-tac/order-hang/khach-hang-mac-dinh', {
            headers: {
                Accept: 'application/json',
            },
        });
        const data = await res.json();
        const khachHang = data.success && data.auto_select ? data.data : null;

        if (!res.ok || !khachHang?.id || stateTaoNhanh.khachHang) {
            return;
        }

        stateTaoNhanh.dropdownKhach.set(Number(khachHang.id), khachHang);
        window.chonKhachHangTaoNhanh(khachHang.id);
    } catch (error) {
        console.error('Loi tu chon khach order mac dinh tao nhanh doi tac:', error);
    }
}

function xoaKhachTaoNhanh() {
    stateTaoNhanh.khachHang = null;
    stateTaoNhanh.nhanVienBanHangId = null;
    document.getElementById('khach-tao-nhanh-da-chon').classList.add('hidden');
    renderNhanVienBanHangTaoNhanh();
}

function layNhanVienBanHangCuaKhachTaoNhanh() {
    const nhanViens = stateTaoNhanh.khachHang?.nhan_viens || [];
    return nhanViens.filter(nv => ['nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'].includes(nv.quyen));
}

function renderNhanVienBanHangTaoNhanh() {
    const box = document.getElementById('box-chon-nhan-vien-tao-nhanh');
    const content = document.getElementById('noi-dung-chon-nhan-vien-tao-nhanh');
    if (!box || !content || !coTheChonNhanVienOrder) return;
    if (!stateTaoNhanh.khachHang) {
        box.classList.add('hidden');
        content.innerHTML = '';
        return;
    }
    const nhanViens = layNhanVienBanHangCuaKhachTaoNhanh();
    box.classList.remove('hidden');
    if (!nhanViens.length) {
        content.innerHTML = '<div class="rounded-xl border border-red-100 bg-white p-3 text-sm font-semibold text-red-600">Khách hàng này chưa có nhân viên bán hàng phụ trách.</div>';
        return;
    }
    if (!stateTaoNhanh.nhanVienBanHangId) stateTaoNhanh.nhanVienBanHangId = Number(nhanViens[0].id);
    content.innerHTML = nhanViens.map(nv => {
        const checked = Number(stateTaoNhanh.nhanVienBanHangId) === Number(nv.id);
        return `<label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border bg-white p-3 text-sm ${checked ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-100'}">
            <span>
                <span class="block font-bold text-gray-900">${escapeHtml(nv.ten)}</span>
                <span class="text-xs text-gray-500">${escapeHtml(tenQuyenNhanVien(nv.quyen))}</span>
            </span>
            <input type="radio" name="nhan_vien_ban_hang_tao_nhanh_id" value="${nv.id}" ${checked ? 'checked' : ''} onchange="chonNhanVienBanHangTaoNhanh(${nv.id})">
        </label>`;
    }).join('');
}

window.chonNhanVienBanHangTaoNhanh = (id) => {
    stateTaoNhanh.nhanVienBanHangId = Number(id);
    renderNhanVienBanHangTaoNhanh();
};

async function taoDonOrderNhanh() {
    if (!stateTaoNhanh.khachHang) {
        showToast('Thiếu khách hàng', 'Vui lòng chọn khách hàng.', 'error');
        return;
    }
    if (coTheChonNhanVienOrder && !stateTaoNhanh.nhanVienBanHangId) {
        showToast('Thiếu nhân viên phụ trách', 'Vui lòng chọn nhân viên bán hàng phụ trách khách hàng.', 'error');
        return;
    }
    if (!phienBanDangChon) {
        showToast('Thiếu phiên bản', 'Vui lòng chọn phiên bản sản phẩm order.', 'error');
        return;
    }

    if (Number(phienBanDangChon.gia_order || 0) <= 0) {
        showToast('Thieu gia order', 'San pham nay chua co gia order hop le.', 'error');
        return;
    }

    const btn = document.getElementById('btn-xac-nhan-tao-order-nhanh');
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
                    khach_hang_id: stateTaoNhanh.khachHang.id,
                    nhan_vien_ban_hang_id: stateTaoNhanh.nhanVienBanHangId || null,
                    ghi_chu: document.getElementById('ghi-chu-tao-nhanh').value,
                    xac_nhan_ton_kho: xacNhanTonKho,
                    san_phams: [{
                        san_pham_id: phienBanDangChon.id,
                        so_luong: laySoLuongOrderNhanh(),
                        gia_ban_du_kien: Number(phienBanDangChon.gia_order || 0),
                        nguon_hang: 'doi_nhap',
                    }],
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

        dongModalTaoOrderNhanh();
        const maDonHang = data.data?.ma_don_hang || '';
        showToast('Thành công', `Đã tạo đơn ${maDonHang || 'order'}.`);
        const query = maDonHang ? `?search=${encodeURIComponent(maDonHang)}` : '';
        setTimeout(() => window.location.href = `/doi-tac/order-hang/danh-sach${query}`, 800);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Xác nhận tạo đơn order';
    }
}

window.chonPhienBanOrder = (id, button) => {
    document.querySelectorAll('#ds-phien-ban-order button').forEach(item => {
        item.classList.remove('border-yellow-400', 'bg-yellow-50', 'text-gray-900');
        item.classList.add('border-gray-200', 'text-gray-600');
    });
    button.classList.add('border-yellow-400', 'bg-yellow-50', 'text-gray-900');
    button.classList.remove('border-gray-200', 'text-gray-600');

    renderThongTinPhienBan(danhSachPhienBan.find(item => Number(item.id) === Number(id)));
};

function renderSanPhamLienQuan(items) {
    const section = document.getElementById('section-san-pham-lien-quan');
    const grid = document.getElementById('grid-san-pham-lien-quan');
    if (!items || !items.length) {
        section.classList.add('hidden');
        return;
    }

    section.classList.remove('hidden');
    grid.innerHTML = items.map(item => `
        <a href="/doi-tac/order-hang/san-pham-duoc-phep/${encodeURIComponent(item.ma_chung)}"
           class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="aspect-square overflow-hidden bg-gray-50">
                <img src="${getAnh(item.anh_chinh)}"
                     alt="${escapeHtml(item.ten_chung || 'Sản phẩm')}"
                     loading="lazy"
                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                     onerror="this.parentElement.innerHTML='<div class=&quot;flex h-full w-full items-center justify-center text-4xl text-gray-200&quot;>SP</div>'">
            </div>
            <div class="p-3">
                ${item.nhan_hieu ? `<div class="mb-1 truncate text-xs font-bold" style="color:#d4af37">${escapeHtml(item.nhan_hieu)}</div>` : ''}
                <h3 class="mb-1.5 line-clamp-2 text-sm font-semibold leading-snug text-gray-900">${escapeHtml(item.ten_chung || 'Sản phẩm')}</h3>
                <div class="font-bold text-sm" style="color:#1a1a2e">${formatCurrency(item.gia_order)}</div>
                <div class="mt-2 text-xs text-gray-500">${formatNumber(item.so_phien_ban || 0)} phiên bản được order</div>
            </div>
        </a>
    `).join('');
}

function renderChiTiet(data) {
    danhSachPhienBan = data.phien_bans || [];

    document.getElementById('breadcrumb-ten-sp').textContent = data.ten_chung || 'Chi tiết sản phẩm';
    document.getElementById('ten-sp-order').textContent = data.ten_chung || 'Sản phẩm';
    document.getElementById('nhan-hieu-sp-order').textContent = data.nhan_hieu || 'Sản phẩm order';
    document.getElementById('gia-sp-order').textContent = giaRange(data.gia_order_thap, data.gia_order_cao);
    document.getElementById('tong-phien-ban-order').textContent = formatNumber(data.so_phien_ban || 0);
    document.getElementById('tong-ton-order').textContent = formatNumber(data.tong_ton_kho || 0);

    renderAnh(data);
    renderPhienBanButtons(danhSachPhienBan);
    renderThongTinPhienBan(danhSachPhienBan[0]);
    renderSanPhamLienQuan(data.san_pham_lien_quan || []);
}

async function taiChiTiet() {
    const root = document.getElementById('chi-tiet-sp-duoc-phep-order');
    const maChung = root.dataset.maChung;

    try {
        const res = await fetch(`/api/doi-tac/order-hang/san-pham-duoc-phep/${encodeURIComponent(maChung)}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        if (!data.success) {
            throw new Error(data.message || 'Không thể tải chi tiết sản phẩm order');
        }

        renderChiTiet(data.data);
        document.getElementById('noi-dung-chi-tiet-sp').classList.remove('hidden');
    } catch (error) {
        document.getElementById('empty-chi-tiet-sp').classList.remove('hidden');
        showToast('Lỗi', error.message || 'Không thể tải chi tiết sản phẩm order');
    } finally {
        document.getElementById('loading-chi-tiet-sp').classList.add('hidden');
    }
}

document.getElementById('btn-them-gio-order')?.addEventListener('click', themVaoGioOrder);
document.getElementById('btn-mo-tao-order-nhanh')?.addEventListener('click', moModalTaoOrderNhanh);
document.getElementById('btn-dong-tao-order-nhanh')?.addEventListener('click', dongModalTaoOrderNhanh);
document.getElementById('btn-xoa-khach-tao-nhanh')?.addEventListener('click', xoaKhachTaoNhanh);
document.getElementById('btn-xac-nhan-tao-order-nhanh')?.addEventListener('click', taoDonOrderNhanh);
document.getElementById('input-tim-khach-tao-nhanh')?.addEventListener('input', e => timKhachHangTaoNhanh(e.target.value));
document.addEventListener('click', e => {
    if (!e.target.closest('#input-tim-khach-tao-nhanh') && !e.target.closest('#dropdown-khach-tao-nhanh')) {
        document.getElementById('dropdown-khach-tao-nhanh')?.classList.add('hidden');
    }
});

taiChiTiet();
