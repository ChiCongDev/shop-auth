const root = document.querySelector('[data-don-ban-order]');
const messageBox = document.getElementById('don-ban-message');

const noiDungXacNhan = {
    'duyet': 'Xác nhận duyệt đơn order này?',
    'xuat-kho': 'Xác nhận xuất kho đơn hàng này?',
    'dong-goi': 'Xác nhận bắt đầu đóng gói đơn hàng?',
    'van-chuyen': 'Xác nhận shipper đã lấy hàng?',
    'tu-van-chuyen-ntq': 'Xác nhận tự vận chuyển cho đơn nhận tại quầy?',
    'hoan-thanh': 'Xác nhận khách đã nhận hàng?',
};

function hienThongBao(message, type = 'info') {
    if (!messageBox) return;

    const styles = {
        success: 'border-green-200 bg-green-50 text-green-700',
        error: 'border-red-200 bg-red-50 text-red-700',
        info: 'border-sky-200 bg-sky-50 text-sky-700',
    };

    messageBox.className = `mb-5 rounded-lg border px-4 py-3 text-sm font-semibold ${styles[type] || styles.info}`;
    messageBox.textContent = message;
    messageBox.classList.remove('hidden');
}

function formatNumber(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function hienThiNutTheoTrangThai() {
    if (!root) return;

    const trangThai = root.dataset.trangThai || '';
    const daDuyetOrder = root.dataset.daDuyetOrder === '1';
    const trangThaiHangOrder = root.dataset.trangThaiHangOrder || 'chua_ve';
    const cachThucNhanHang = root.dataset.cachThucNhanHang || '';
    const quyen = root.dataset.doiTacQuyen || '';
    const actions = [];
    const coQuyenDuyet = ['admin', 'thu_kho', 'quan_ly_order'].includes(quyen);
    const coQuyenXuatKho = ['admin', 'thu_kho', 'quan_ly_order'].includes(quyen);
    const coQuyenXuLySauXuatKho = [
        'admin',
        'thu_kho',
        'quan_ly_order',
        'nhan_vien_ban_hang_cap_1',
        'nhan_vien_ban_hang_cap_2',
    ].includes(quyen);

    if (trangThai === 'cho_xu_ly' && !daDuyetOrder && coQuyenDuyet) {
        actions.push('duyet');
    } else if (trangThai === 'cho_xu_ly' && daDuyetOrder && trangThaiHangOrder !== 've_du' && coQuyenXuatKho) {
        actions.push('lay-hang-trong-kho');
    } else if (trangThai === 'cho_xu_ly' && trangThaiHangOrder === 've_du' && coQuyenXuatKho) {
        actions.push('xuat-kho');
    } else if (trangThai === 'xuat_kho' && coQuyenXuLySauXuatKho) {
        actions.push('dong-goi');
    } else if (trangThai === 'dong_goi' && cachThucNhanHang === 'nhan_tai_quay' && coQuyenXuLySauXuatKho) {
        actions.push('tu-van-chuyen-ntq');
    } else if (trangThai === 'dong_goi' && coQuyenXuLySauXuatKho) {
        actions.push('van-chuyen');
    } else if (['van_chuyen', 'tu_van_chuyen'].includes(trangThai) && coQuyenXuLySauXuatKho) {
        actions.push('hoan-thanh');
    }

    document.querySelectorAll('[data-action]').forEach((button) => {
        button.classList.toggle('hidden', !actions.includes(button.dataset.action));
    });

    document.querySelector('[data-return-action]')?.classList.toggle(
        'hidden',
        !['xuat_kho', 'dong_goi', 'van_chuyen', 'tu_van_chuyen', 'hoan_thanh'].includes(trangThai)
    );

    if (actions.length === 0 && !['hoan_thanh', 'huy'].includes(trangThai)) {
        hienThongBao('Hiện chưa có thao tác phù hợp với quyền đối tác hoặc trạng thái đơn hàng này.', 'info');
    }
}

function dongModalLayHangTrongKho() {
    document.getElementById('modal-lay-hang-trong-kho-doi-tac')?.remove();
}

function renderModalLayHangTrongKho(items) {
    dongModalLayHangTrongKho();

    const modal = document.createElement('div');
    modal.id = 'modal-lay-hang-trong-kho-doi-tac';
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-gray-950/50 p-4';

    const rows = items.map((item, index) => {
        const max = Number(item.co_the_lay || 0);
        const disabled = max <= 0;

        return `
            <label class="flex gap-3 rounded-lg border border-gray-200 bg-white p-4 ${disabled ? 'opacity-60' : ''}">
                <input type="checkbox" class="mt-1 h-4 w-4 rounded border-gray-300 text-teal-600" data-lay-hang-check data-index="${index}" ${disabled ? 'disabled' : 'checked'}>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-950">${escapeHtml(item.ten || 'Sản phẩm')}</p>
                    <p class="mt-1 text-xs text-gray-500">${escapeHtml(item.ma_sku || '-')}</p>
                    <div class="mt-2 grid gap-2 text-xs text-gray-600 sm:grid-cols-3">
                        <span>Còn thiếu: <b>${formatNumber(item.so_luong_con_thieu)}</b></span>
                        <span>Có thể bán: <b>${formatNumber(item.co_the_ban)}</b></span>
                        <span>Có thể lấy: <b>${formatNumber(max)}</b></span>
                    </div>
                </div>
                <input type="number" min="1" max="${max}" value="${max > 0 ? max : 1}" data-lay-hang-qty data-index="${index}" ${disabled ? 'disabled' : ''} class="h-10 w-24 rounded-lg border border-gray-300 px-3 text-center text-sm font-semibold outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500">
            </label>
        `;
    }).join('');

    modal.innerHTML = `
        <div class="w-full max-w-3xl rounded-xl bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-950">Lấy hàng trong kho</h3>
                    <p class="mt-1 text-sm text-gray-500">Chọn sản phẩm và số lượng muốn lấy để báo hàng order về.</p>
                </div>
                <button type="button" data-close-modal class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Đóng</button>
            </div>
            <div class="border-b border-gray-100 px-5 py-3">
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <input type="checkbox" data-lay-hang-check-all class="h-4 w-4 rounded border-gray-300 text-teal-600" checked>
                    <span>Chọn tất cả sản phẩm có thể lấy</span>
                </label>
            </div>
            <div class="max-h-[60vh] space-y-3 overflow-y-auto bg-gray-50 px-5 py-4">
                ${rows || '<div class="rounded-lg border border-gray-200 bg-white p-5 text-center text-sm font-semibold text-gray-500">Không có sản phẩm nào có thể lấy trong kho.</div>'}
            </div>
            <div class="flex flex-col gap-2 border-t border-gray-100 px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" data-close-modal class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Hủy</button>
                <button type="button" data-submit-lay-hang class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Xác nhận lấy hàng</button>
            </div>
        </div>
    `;

    modal.addEventListener('click', (event) => {
        if (event.target === modal || event.target.closest('[data-close-modal]')) {
            dongModalLayHangTrongKho();
        }
    });

    const checkAll = modal.querySelector('[data-lay-hang-check-all]');
    const itemChecks = Array.from(modal.querySelectorAll('[data-lay-hang-check]'));
    const enabledChecks = itemChecks.filter((checkbox) => !checkbox.disabled);

    const capNhatChonTatCa = () => {
        if (!checkAll) return;

        const checkedCount = enabledChecks.filter((checkbox) => checkbox.checked).length;
        checkAll.checked = enabledChecks.length > 0 && checkedCount === enabledChecks.length;
        checkAll.indeterminate = checkedCount > 0 && checkedCount < enabledChecks.length;
        checkAll.disabled = enabledChecks.length === 0;
    };

    checkAll?.addEventListener('change', () => {
        enabledChecks.forEach((checkbox) => {
            checkbox.checked = checkAll.checked;
        });
        capNhatChonTatCa();
    });

    itemChecks.forEach((checkbox) => {
        checkbox.addEventListener('change', capNhatChonTatCa);
    });
    capNhatChonTatCa();

    modal.querySelector('[data-submit-lay-hang]')?.addEventListener('click', () => guiLayHangTrongKho(items));
    document.body.appendChild(modal);
}

async function moModalLayHangTrongKho(button) {
    if (!root) return;

    const donHangId = root.dataset.donHangId;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Đang kiểm tra...';

    try {
        const response = await fetch(`/api/doi-tac/order-hang/don-ban/${donHangId}/lay-hang-trong-kho`, {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            hienThongBao(data.message || 'Không kiểm tra được hàng trong kho.', 'error');
            return;
        }

        const items = data.data?.items || [];
        if (!items.length) {
            hienThongBao('Không có sản phẩm nào còn thiếu có thể lấy trong kho.', 'info');
            return;
        }

        renderModalLayHangTrongKho(items);
    } catch (error) {
        hienThongBao('Không kết nối được máy chủ kiểm tra hàng trong kho.', 'error');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

async function guiLayHangTrongKho(items) {
    const modal = document.getElementById('modal-lay-hang-trong-kho-doi-tac');
    if (!modal || !root) return;

    const sanPhams = Array.from(modal.querySelectorAll('[data-lay-hang-check]:checked')).map((checkbox) => {
        const index = Number(checkbox.dataset.index);
        const item = items[index];
        const qtyInput = modal.querySelector(`[data-lay-hang-qty][data-index="${index}"]`);
        const soLuong = Math.max(1, Number(qtyInput?.value || 0));
        const max = Number(item.co_the_lay || 0);

        return {
            san_pham_id: Number(item.san_pham_id),
            so_luong: Math.min(soLuong, max),
        };
    }).filter(item => item.san_pham_id > 0 && item.so_luong > 0);

    if (!sanPhams.length) {
        hienThongBao('Vui lòng chọn ít nhất một sản phẩm có thể lấy trong kho.', 'error');
        return;
    }

    const submitButton = modal.querySelector('[data-submit-lay-hang]');
    const originalText = submitButton?.textContent || '';
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Đang xử lý...';
    }

    try {
        const response = await fetch(`/api/doi-tac/order-hang/don-ban/${root.dataset.donHangId}/lay-hang-trong-kho`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ san_phams: sanPhams }),
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            hienThongBao(data.message || 'Không thể lấy hàng trong kho.', 'error');
            return;
        }

        hienThongBao(data.message || 'Đã lấy hàng trong kho.', 'success');
        dongModalLayHangTrongKho();
        window.setTimeout(() => window.location.reload(), 800);
    } catch (error) {
        hienThongBao('Không kết nối được máy chủ xử lý lấy hàng trong kho.', 'error');
    } finally {
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    }
}

async function guiThaoTac(action, button) {
    if (!root) return;

    if (action === 'lay-hang-trong-kho') {
        await moModalLayHangTrongKho(button);
        return;
    }

    const donHangId = root.dataset.donHangId;
    const ok = window.confirm(noiDungXacNhan[action] || 'Xác nhận thao tác đơn hàng?');
    if (!ok) return;

    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Đang xử lý...';

    try {
        const response = await fetch(`/api/doi-tac/order-hang/don-ban/${donHangId}/${action}`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            hienThongBao(data.message || 'Không thực hiện được thao tác.', 'error');
            return;
        }

        hienThongBao(data.message || 'Đã thực hiện thao tác.', 'success');
        window.setTimeout(() => window.location.reload(), 800);
    } catch (error) {
        hienThongBao('Không kết nối được máy chủ xử lý thao tác.', 'error');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

document.querySelectorAll('[data-action]').forEach((button) => {
    button.addEventListener('click', () => guiThaoTac(button.dataset.action, button));
});

hienThiNutTheoTrangThai();
