<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>@yield('title', 'Shop Auth') — Thời Trang Chính Hãng</title>
    <meta name="description" content="@yield('description', 'Shop Auth - Quần áo, giày dép hàng chính hãng chất lượng cao')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        :root {
            --safe-top: env(safe-area-inset-top, 0px);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
        }
        /* Bottom nav chiều cao + safe area iPhone home indicator */
        .bottom-nav {
            padding-bottom: max(0.5rem, calc(0.25rem + var(--safe-bottom)));
        }
        /* Nội dung chính bù khoảng bottom nav (~68px) */
        .main-content {
            padding-bottom: calc(4.5rem + var(--safe-bottom));
        }
        /* Ẩn scrollbar ngang */
        .scroll-hide::-webkit-scrollbar { display: none; }
        .scroll-hide { -ms-overflow-style: none; scrollbar-width: none; }
        /* Tắt iOS tap highlight */
        * { -webkit-tap-highlight-color: transparent; }
        /* Touch target tối thiểu Apple HIG 44x44px */
        .touch-min { min-height: 44px; display: flex; align-items: center; }
    </style>
</head>
<body class="bg-gray-50 font-['Inter']">

    {{-- ══ HEADER ══ --}}
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-14 sm:h-16">

                {{-- Logo --}}
                <a href="/" class="flex items-center gap-2 shrink-0 touch-min">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                         style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">
                        <span class="text-white font-bold text-sm">SA</span>
                    </div>
                    <span class="font-bold text-lg sm:text-xl text-gray-900">Shop <span style="color:#d4af37">Auth</span></span>
                </a>

                {{-- Nav desktop --}}
                <nav class="hidden md:flex items-center gap-6">
                    <a href="/" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Trang chủ</a>
                    <a href="/san-pham" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Sản phẩm</a>
                    <a href="/san-pham?loai=quan-ao" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Quần áo</a>
                    <a href="/san-pham?loai=giay-dep" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Giày dép</a>
                    <a href="/hang-moi-ve"
                       class="relative inline-flex items-center gap-1.5 font-semibold transition-colors"
                       style="color: #ff4d4f">
                        🆕 Hàng mới về
                        <span class="absolute -top-2 -right-5 px-1.5 py-0.5 rounded-full text-white font-bold leading-none"
                              style="font-size:9px; background:#ff4d4f">NEW</span>
                    </a>
                </nav>

                {{-- Icons bên phải --}}
                <div class="flex items-center gap-1">

                    {{-- Search mobile toggle --}}
                    <button id="btn-search-mobile"
                            class="md:hidden touch-min w-11 justify-center text-gray-600 rounded-full hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    {{-- Search desktop --}}
                    <div class="hidden md:block relative" id="box-tim-kiem">
                        <div class="flex items-center bg-gray-100 rounded-full px-3 py-1.5 gap-2
                                    focus-within:ring-2 focus-within:ring-yellow-400 focus-within:bg-white transition-all">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input id="input-tim-kiem" type="text" placeholder="Tìm sản phẩm..." autocomplete="off"
                                   class="bg-transparent text-sm outline-none w-44 text-gray-700 placeholder-gray-400">
                        </div>
                        <div id="dropdown-goi-y"
                             class="hidden absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl
                                    border border-gray-100 z-50 overflow-hidden min-w-72">
                            <div id="ket-qua-tim-kiem" class="max-h-72 overflow-y-auto divide-y divide-gray-50"></div>
                            <div id="xem-tat-ca" class="hidden px-4 py-2.5 text-center border-t border-gray-100">
                                <a id="link-xem-tat-ca" href="/san-pham"
                                   class="text-sm font-semibold hover:opacity-80 transition-opacity"
                                   style="color:#d4af37">Xem tất cả kết quả →</a>
                            </div>
                        </div>
                    </div>

                    {{-- Giỏ hàng --}}
                    @if(session('khach_hang_id'))
                    <a href="/gio-hang"
                       class="relative touch-min w-11 justify-center text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        @if(isset($tongSoLuongGioHang) && $tongSoLuongGioHang > 0)
                        <span class="absolute top-1 right-0.5 bg-red-500 text-white rounded-full w-4 h-4
                                     flex items-center justify-center font-bold leading-none" style="font-size:10px">
                            {{ $tongSoLuongGioHang > 9 ? '9+' : $tongSoLuongGioHang }}
                        </span>
                        @endif
                    </a>
                    @endif

                    {{-- Tài khoản --}}
                    @if(session('khach_hang_id'))
                    <div class="relative" id="wrap-tai-khoan">
                        <button id="btn-tai-khoan"
                                class="flex items-center gap-1.5 touch-min px-1 text-gray-700 hover:text-gray-900 transition-colors">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                 style="background:#1a1a2e">
                                {{ mb_substr(session('tenDangNhap', 'K'), 0, 1) }}
                            </div>
                            <span class="hidden md:block text-sm font-medium">{{ session('tenDangNhap') }}</span>
                        </button>
                        <div id="dropdown-tai-khoan"
                             class="hidden absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg py-1 z-50 border border-gray-100">
                            <a href="/don-hang" class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Đơn hàng của tôi
                            </a>
                            <a href="/gio-hang" class="flex items-center gap-2 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 3h2l.4 2M7 13h10l4-8H5.4"/>
                                </svg>
                                Giỏ hàng
                            </a>
                            <hr class="my-1 border-gray-100">
                            <a href="/dang-xuat" class="flex items-center gap-2 px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Đăng xuất
                            </a>
                        </div>
                    </div>
                    @else
                        <a href="/dang-nhap" class="hidden md:flex touch-min px-3 text-sm font-medium text-gray-600 hover:text-gray-900">Đăng nhập</a>
                        <a href="/dang-ky" class="hidden md:flex touch-min px-4 text-sm font-semibold text-white rounded-full hover:opacity-90"
                           style="background: linear-gradient(135deg, #1a1a2e, #d4af37)">Đăng ký</a>
                        <a href="/dang-nhap" class="md:hidden touch-min w-11 justify-center text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Thanh search mobile (ẩn/hiện) ── --}}
        <div id="search-mobile-bar" class="hidden md:hidden border-t border-gray-100 bg-white px-4 py-2.5">
            <div class="flex items-center gap-2 bg-gray-100 rounded-full px-4 py-2.5
                        focus-within:ring-2 focus-within:ring-yellow-400 focus-within:bg-white transition-all">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="input-tim-kiem-mobile" type="search" placeholder="Tìm sản phẩm..." autocomplete="off"
                       class="bg-transparent text-sm outline-none flex-1 text-gray-700 placeholder-gray-400">
                <button id="btn-dong-search" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="dropdown-goi-y-mobile"
                 class="hidden mt-2 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div id="ket-qua-tim-kiem-mobile" class="max-h-64 overflow-y-auto divide-y divide-gray-50"></div>
                <div id="xem-tat-ca-mobile" class="hidden px-4 py-2.5 text-center border-t border-gray-100">
                    <a id="link-xem-tat-ca-mobile" href="/san-pham"
                       class="text-sm font-semibold" style="color:#d4af37">Xem tất cả kết quả →</a>
                </div>
            </div>
        </div>
    </header>

    {{-- Thông báo --}}
    @if(session('thongBao'))
    <div class="max-w-7xl mx-auto px-4 mt-3" id="thong-bao-toast">
        <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
            <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium flex-1">{{ session('thongBao') }}</span>
            <button onclick="document.getElementById('thong-bao-toast').remove()"
                    class="text-green-500 hover:text-green-700 shrink-0 p-0.5">✕</button>
        </div>
    </div>
    @endif

    @if(session('loi'))
    <div class="max-w-7xl mx-auto px-4 mt-3" id="loi-toast">
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium flex-1">{{ session('loi') }}</span>
            <button onclick="document.getElementById('loi-toast').remove()"
                    class="text-red-500 hover:text-red-700 shrink-0 p-0.5">✕</button>
        </div>
    </div>
    @endif

    {{-- Nội dung chính --}}
    <main class="main-content md:pb-0">
        @yield('content')
    </main>

    {{-- Footer (desktop only) --}}
    <footer class="hidden md:block mt-20 text-white py-12" style="background: #1a1a2e">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                             style="background: linear-gradient(135deg, #d4af37, #f0d060)">
                            <span class="text-gray-900 font-bold text-sm">SA</span>
                        </div>
                        <span class="font-bold text-xl">Shop <span style="color:#d4af37">Auth</span></span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Chuyên cung cấp quần áo, giày dép hàng chính hãng chất lượng cao.<br>
                        Cam kết 100% hàng thật, hoàn tiền nếu hàng giả.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-3" style="color:#d4af37">Danh mục</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="/san-pham" class="hover:text-white transition-colors">Tất cả sản phẩm</a></li>
                        <li><a href="/san-pham?loai=quan-ao" class="hover:text-white transition-colors">Quần áo</a></li>
                        <li><a href="/san-pham?loai=giay-dep" class="hover:text-white transition-colors">Giày dép</a></li>
                        <li><a href="/#hang-moi-ve" class="hover:text-white transition-colors" style="color:#ff7a45">🆕 Hàng mới về</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3" style="color:#d4af37">Hỗ trợ</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="/dang-nhap" class="hover:text-white transition-colors">Đăng nhập</a></li>
                        <li><a href="/dang-ky" class="hover:text-white transition-colors">Đăng ký</a></li>
                        <li><a href="/don-hang" class="hover:text-white transition-colors">Theo dõi đơn hàng</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-500 text-xs">
                © {{ date('Y') }} Shop Auth. Tất cả quyền được bảo lưu.
            </div>
        </div>
    </footer>

    {{-- ══ BOTTOM NAVIGATION (chỉ mobile) ══ --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40 bottom-nav">
        <div class="grid grid-cols-4">

            <a href="/"
               class="flex flex-col items-center justify-center py-1.5 gap-0.5
                      {{ Request::is('/') ? 'text-yellow-500' : 'text-gray-500 hover:text-gray-700' }}">
                <svg class="w-6 h-6" fill="{{ Request::is('/') ? 'currentColor' : 'none' }}"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-xs font-medium">Trang chủ</span>
            </a>

            <a href="/san-pham"
               class="flex flex-col items-center justify-center py-1.5 gap-0.5
                      {{ Request::is('san-pham*') ? 'text-yellow-500' : 'text-gray-500 hover:text-gray-700' }}">
                <svg class="w-6 h-6" fill="{{ Request::is('san-pham*') ? 'currentColor' : 'none' }}"
                     stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span class="text-xs font-medium">Sản phẩm</span>
            </a>

            <a href="/gio-hang"
               class="flex flex-col items-center justify-center py-1.5 gap-0.5 relative
                      {{ Request::is('gio-hang') ? 'text-yellow-500' : 'text-gray-500 hover:text-gray-700' }}">
                <div class="relative">
                    <svg class="w-6 h-6" fill="{{ Request::is('gio-hang') ? 'currentColor' : 'none' }}"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    @if(isset($tongSoLuongGioHang) && $tongSoLuongGioHang > 0)
                    <span class="absolute -top-1 -right-2 bg-red-500 text-white rounded-full w-4 h-4
                                 flex items-center justify-center font-bold leading-none" style="font-size:10px">
                        {{ $tongSoLuongGioHang > 9 ? '9+' : $tongSoLuongGioHang }}
                    </span>
                    @endif
                </div>
                <span class="text-xs font-medium">Giỏ hàng</span>
            </a>

            @if(session('khach_hang_id'))
            <a href="/don-hang"
               class="flex flex-col items-center justify-center py-1.5 gap-0.5
                      {{ Request::is('don-hang*') ? 'text-yellow-500' : 'text-gray-500 hover:text-gray-700' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-xs font-medium">Tài khoản</span>
            </a>
            @else
            <a href="/dang-nhap"
               class="flex flex-col items-center justify-center py-1.5 gap-0.5 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-xs font-medium">Đăng nhập</span>
            </a>
            @endif
        </div>
    </nav>

    @stack('scripts')

    <script>
    (() => {
        // Search mobile toggle
        const btnSearch  = document.getElementById('btn-search-mobile');
        const searchBar  = document.getElementById('search-mobile-bar');
        const btnDong    = document.getElementById('btn-dong-search');
        const inputMob   = document.getElementById('input-tim-kiem-mobile');

        btnSearch?.addEventListener('click', () => {
            const isHidden = searchBar.classList.toggle('hidden');
            if (!isHidden) setTimeout(() => inputMob?.focus(), 80);
        });
        btnDong?.addEventListener('click', () => searchBar.classList.add('hidden'));

        // Dropdown tài khoản — click thay hover (mobile-friendly)
        const btnTK = document.getElementById('btn-tai-khoan');
        const ddTK  = document.getElementById('dropdown-tai-khoan');
        if (btnTK && ddTK) {
            btnTK.addEventListener('click', e => {
                e.stopPropagation();
                ddTK.classList.toggle('hidden');
            });
            document.addEventListener('click', () => ddTK.classList.add('hidden'));
        }
    })();
    </script>
</body>
</html>
