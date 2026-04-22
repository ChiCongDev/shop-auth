import './bootstrap';

// ============================================================
// SEARCH AUTOCOMPLETE — Tìm kiếm sản phẩm realtime
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const input    = document.getElementById('input-tim-kiem');
    const dropdown = document.getElementById('dropdown-goi-y');
    const ketQua   = document.getElementById('ket-qua-tim-kiem');
    const xemTatCa = document.getElementById('xem-tat-ca');
    const linkTatCa = document.getElementById('link-xem-tat-ca');

    if (!input) return;

    let debounceTimer = null;

    // Mỗi khi gõ → gọi API sau 300ms
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const keyword = input.value.trim();

        if (keyword.length < 2) {
            dropdown.classList.add('hidden');
            return;
        }

        debounceTimer = setTimeout(() => timKiem(keyword), 300);
    });

    // Enter → vào trang danh sách
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && input.value.trim()) {
            window.location = '/san-pham?search=' + encodeURIComponent(input.value.trim());
        }
        if (e.key === 'Escape') {
            dropdown.classList.add('hidden');
            input.blur();
        }
    });

    // Click ngoài → đóng dropdown
    document.addEventListener('click', (e) => {
        const box = document.getElementById('box-tim-kiem');
        if (box && !box.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    async function timKiem(keyword) {
        try {
            const res  = await fetch(`/api/tim-kiem-san-pham?search=${encodeURIComponent(keyword)}`);
            const data = await res.json();

            if (!data.success) return;

            ketQua.innerHTML = '';

            if (data.data.length === 0) {
                ketQua.innerHTML = `
                    <div class="px-4 py-6 text-center text-gray-400 text-sm">
                        <div class="text-3xl mb-2">🔍</div>
                        Không tìm thấy "<strong>${escHtml(keyword)}</strong>"
                    </div>`;
                xemTatCa.classList.add('hidden');
            } else {
                data.data.forEach(sp => {
                    const anhUrl = sp.anh
                        ? `/storage/uploads/sanpham/${sp.anh}`
                        : null;

                    ketQua.insertAdjacentHTML('beforeend', `
                        <a href="${sp.url}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden shrink-0">
                                ${anhUrl
                                    ? `<img src="${anhUrl}" alt="${escHtml(sp.ten_chung)}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<div class=\\'w-full h-full flex items-center justify-center text-xl text-gray-300\\'>👕</div>'">`
                                    : '<div class="w-full h-full flex items-center justify-center text-xl text-gray-300">👕</div>'}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate">${escHtml(sp.ten_chung)}</div>
                                <div class="text-sm font-bold" style="color:#d4af37">${sp.gia}đ</div>
                            </div>
                        </a>`);
                });

                // Nút xem tất cả
                if (linkTatCa) {
                    linkTatCa.href = '/san-pham?search=' + encodeURIComponent(keyword);
                }
                xemTatCa.classList.remove('hidden');
            }

            dropdown.classList.remove('hidden');
        } catch (err) {
            console.error('Lỗi tìm kiếm:', err);
        }
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }
});
