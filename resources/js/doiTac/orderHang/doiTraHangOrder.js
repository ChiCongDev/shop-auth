const root = document.getElementById('doi-tra-order');
const messageBox = document.getElementById('doi-tra-message');

function formatNumber(value) {
    return new Intl.NumberFormat('vi-VN').format(Number(value || 0));
}

function parseMoney(value) {
    return Number(String(value || '').replace(/[^\d]/g, '')) || 0;
}

function hienThongBao(message, type = 'info') {
    if (!messageBox) return;
    const styles = {
        success: 'border-green-200 bg-green-50 text-green-700',
        error: 'border-red-200 bg-red-50 text-red-700',
        info: 'border-sky-200 bg-sky-50 text-sky-700',
    };
    messageBox.className = `rounded-lg border px-4 py-3 text-sm font-semibold ${styles[type] || styles.info}`;
    messageBox.textContent = message;
    messageBox.classList.remove('hidden');
}

function tinhTong() {
    let tongSoLuong = 0;
    let tongTien = 0;

    document.querySelectorAll('[data-return-row]').forEach((row) => {
        const soLuong = Number(row.querySelector('[data-so-luong-tra]')?.value || 0);
        const giaTra = parseMoney(row.querySelector('[data-gia-tra]')?.value || 0);
        tongSoLuong += soLuong;
        tongTien += soLuong * giaTra;
    });

    document.getElementById('tong-so-luong-tra').textContent = formatNumber(tongSoLuong);
    document.getElementById('tong-tien-tra').textContent = formatNumber(tongTien);
}

function ganDinhDangGia(input) {
    input.addEventListener('input', () => {
        const raw = parseMoney(input.value);
        input.value = raw ? formatNumber(raw) : '';
        tinhTong();
    });
}

async function taiSoLuongDaTra() {
    if (!root) return;

    try {
        const response = await fetch(`/api/doi-tac/order-hang/phieu-tra-hang/so-luong-da-tra/${root.dataset.donHangId}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Không tải được số lượng đã trả.');
        }

        const daTra = data.data || {};
        document.querySelectorAll('[data-return-row]').forEach((row) => {
            const sanPhamId = row.dataset.sanPhamId;
            const soLuongBan = Number(row.dataset.soLuongBan || 0);
            const soLuongDaTra = Number(daTra[sanPhamId] || 0);
            const conTra = Math.max(0, soLuongBan - soLuongDaTra);
            row.querySelector('[data-da-tra]').textContent = formatNumber(soLuongDaTra);
            row.querySelector('[data-con-tra]').textContent = formatNumber(conTra);
            const input = row.querySelector('[data-so-luong-tra]');
            input.max = conTra;
            input.disabled = conTra <= 0;
        });
    } catch (error) {
        hienThongBao(error.message || 'Không tải được số lượng đã trả.', 'error');
    }
}

function laySanPhamTra() {
    const sanPhams = [];

    document.querySelectorAll('[data-return-row]').forEach((row) => {
        const soLuong = Number(row.querySelector('[data-so-luong-tra]')?.value || 0);
        const conTra = Number(String(row.querySelector('[data-con-tra]')?.textContent || '0').replace(/[^\d]/g, '')) || 0;
        if (soLuong <= 0) return;

        if (soLuong > conTra) {
            throw new Error(`Số lượng trả của ${row.dataset.tenSanPham} vượt quá số lượng còn được trả.`);
        }

        sanPhams.push({
            san_pham_id: Number(row.dataset.sanPhamId),
            chi_tiet_don_hang_id: Number(row.dataset.chiTietDonHangId),
            ten_san_pham: row.dataset.tenSanPham || '',
            ma_sku: row.dataset.maSku || '',
            don_vi_tinh: row.dataset.donViTinh || 'Chiếc',
            so_luong: soLuong,
            gia_goc: Number(row.dataset.giaBan || 0),
            gia_tra: parseMoney(row.querySelector('[data-gia-tra]')?.value || 0),
            ly_do: row.querySelector('[data-ly-do]')?.value || '',
        });
    });

    return sanPhams;
}

async function taoPhieuTraHang() {
    const button = document.getElementById('btn-tao-phieu-tra');
    try {
        const sanPhams = laySanPhamTra();
        if (!sanPhams.length) {
            hienThongBao('Vui lòng nhập số lượng trả cho ít nhất một sản phẩm.', 'error');
            return;
        }

        if (!window.confirm('Xác nhận tạo phiếu trả hàng order?')) {
            return;
        }

        button.disabled = true;
        button.textContent = 'Đang tạo phiếu...';

        const response = await fetch('/api/doi-tac/order-hang/phieu-tra-hang/tao', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                don_hang_id: Number(root.dataset.donHangId),
                ly_do_tra: document.getElementById('ly-do-tra')?.value || '',
                ghi_chu: document.getElementById('ghi-chu-tra')?.value || '',
                san_phams: sanPhams,
            }),
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Không tạo được phiếu trả hàng.');
        }

        hienThongBao(data.message || 'Đã tạo phiếu trả hàng.', 'success');
        window.setTimeout(() => {
            window.location.href = '/doi-tac/order-hang/khach-tra-hang-order';
        }, 900);
    } catch (error) {
        hienThongBao(error.message || 'Không tạo được phiếu trả hàng.', 'error');
    } finally {
        button.disabled = false;
        button.textContent = 'Tạo phiếu trả hàng';
    }
}

document.querySelectorAll('[data-so-luong-tra]').forEach((input) => {
    input.addEventListener('input', () => {
        const max = Number(input.max || 0);
        const value = Math.max(0, Number(input.value || 0));
        input.value = max > 0 ? Math.min(value, max) : 0;
        tinhTong();
    });
});

document.querySelectorAll('[data-gia-tra]').forEach(ganDinhDangGia);
document.getElementById('btn-tao-phieu-tra')?.addEventListener('click', taoPhieuTraHang);

taiSoLuongDaTra();
tinhTong();
