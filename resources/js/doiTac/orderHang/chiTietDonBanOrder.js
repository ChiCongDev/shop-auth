const root = document.querySelector('[data-don-ban-order]');
const messageBox = document.getElementById('don-ban-message');

const noiDungXacNhan = {
    'duyet': 'Xác nhận duyệt đơn order này?',
    'bao-hang-ve': 'Xác nhận báo hàng về cho đơn order này?',
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

function hienThiNutTheoTrangThai() {
    if (!root) return;

    const trangThai = root.dataset.trangThai || '';
    const daDuyetOrder = root.dataset.daDuyetOrder === '1';
    const daBaoHangVeOrder = root.dataset.daBaoHangVeOrder === '1';
    const cachThucNhanHang = root.dataset.cachThucNhanHang || '';
    const quyen = root.dataset.doiTacQuyen || '';
    const actions = [];
    const coQuyenDuyet = ['admin', 'quan_ly_order'].includes(quyen);
    const coQuyenBaoHangVe = ['admin', 'thu_kho', 'quan_ly_order'].includes(quyen);
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
    } else if (trangThai === 'cho_xu_ly' && daDuyetOrder && !daBaoHangVeOrder && coQuyenBaoHangVe) {
        actions.push('bao-hang-ve');
    } else if (trangThai === 'cho_xu_ly' && daBaoHangVeOrder && coQuyenXuatKho) {
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

async function guiThaoTac(action, button) {
    if (!root) return;

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
