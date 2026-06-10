import './bootstrap';

// ============================================================
// SEARCH AUTOCOMPLETE - shared desktop/mobile behavior
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    setupSearchAutocomplete({
        inputId: 'input-tim-kiem',
        dropdownId: 'dropdown-goi-y',
        resultId: 'ket-qua-tim-kiem',
        footerId: 'xem-tat-ca',
        linkAllId: 'link-xem-tat-ca',
        boxId: 'box-tim-kiem',
    });

    setupSearchAutocomplete({
        inputId: 'input-tim-kiem-mobile',
        dropdownId: 'dropdown-goi-y-mobile',
        resultId: 'ket-qua-tim-kiem-mobile',
        footerId: 'xem-tat-ca-mobile',
        linkAllId: 'link-xem-tat-ca-mobile',
        boxId: 'search-mobile-bar',
    });

    function setupSearchAutocomplete({ inputId, dropdownId, resultId, footerId, linkAllId, boxId }) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        const ketQua = document.getElementById(resultId);
        const xemTatCa = document.getElementById(footerId);
        const linkTatCa = document.getElementById(linkAllId);

        if (!input || !dropdown || !ketQua) return;

        let debounceTimer = null;
        let requestController = null;
        let latestRequestId = 0;

        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const keyword = input.value.trim();

            if (keyword.length < 2) {
                huyRequestDangChay();
                anDropdown();
                return;
            }

            debounceTimer = setTimeout(() => timKiem(keyword), 300);
        });

        input.addEventListener('keydown', (e) => {
            const keyword = input.value.trim();

            if (e.key === 'Enter' && keyword) {
                e.preventDefault();
                window.location = buildSearchUrl(keyword);
                return;
            }

            if (e.key === 'Escape') {
                anDropdown();
                input.blur();
            }
        });

        document.addEventListener('click', (e) => {
            const box = document.getElementById(boxId);
            if (box && !box.contains(e.target)) {
                anDropdown();
            }
        });

        function anDropdown() {
            dropdown.classList.add('hidden');
            xemTatCa?.classList.add('hidden');
        }

        function huyRequestDangChay() {
            if (requestController) {
                requestController.abort();
                requestController = null;
            }
        }

        async function timKiem(keyword) {
            const requestId = ++latestRequestId;
            huyRequestDangChay();
            requestController = new AbortController();

            try {
                const res = await fetch(buildAutocompleteUrl(keyword), {
                    signal: requestController.signal,
                });
                const data = await res.json();

                if (requestId !== latestRequestId) return;
                if (!data.success) return;

                ketQua.innerHTML = '';

                if (data.data.length === 0) {
                    ketQua.innerHTML = `
                        <div class="px-4 py-6 text-center text-gray-400 text-sm">
                            <div class="text-3xl mb-2">&#128269;</div>
                            Kh&#244;ng t&#236;m th&#7845;y "<strong>${escHtml(keyword)}</strong>"
                        </div>`;
                    xemTatCa?.classList.add('hidden');
                } else {
                    data.data.forEach(sp => {
                        const tenChung = escHtml(String(sp.ten_chung ?? ''));
                        const gia = escHtml(String(sp.gia ?? ''));
                        const url = escHtml(String(sp.url ?? '#'));
                        const anhUrl = sp.anh
                            ? `/storage/uploads/sanpham/${encodeURIComponent(sp.anh)}`
                            : null;

                        ketQua.insertAdjacentHTML('beforeend', `
                            <a href="${url}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                                <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden shrink-0">
                                    ${anhUrl
                                        ? `<img src="${anhUrl}" alt="${tenChung}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<div class=\\'w-full h-full flex items-center justify-center text-xl text-gray-300\\'>&#128085;</div>'">`
                                        : '<div class="w-full h-full flex items-center justify-center text-xl text-gray-300">&#128085;</div>'}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">${tenChung}</div>
                                    <div class="text-sm font-bold" style="color:#d4af37">${gia}&#273;</div>
                                </div>
                            </a>`);
                    });

                    if (linkTatCa) {
                        linkTatCa.href = buildSearchUrl(keyword);
                    }
                    xemTatCa?.classList.remove('hidden');
                }

                dropdown.classList.remove('hidden');
            } catch (err) {
                if (err.name === 'AbortError') return;
                console.error('Lỗi tìm kiếm:', err);
            } finally {
                if (requestId === latestRequestId) {
                    requestController = null;
                }
            }
        }

        function escHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function buildSearchUrl(keyword) {
            const params = new URLSearchParams(window.location.search);
            params.set('search', keyword);
            params.delete('page');

            return '/san-pham?' + params.toString();
        }

        function buildAutocompleteUrl(keyword) {
            const params = new URLSearchParams(window.location.search);
            params.set('search', keyword);
            params.delete('page');

            return '/api/tim-kiem-san-pham?' + params.toString();
        }
    }
});
