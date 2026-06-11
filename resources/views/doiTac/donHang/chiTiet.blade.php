@extends('layouts.app')
@section('title', 'Chi tiet don hang ' . ($donHang['ma_don_hang'] ?? ''))

@php
    $formatMoney = fn($value) => number_format((float) ($value ?? 0), 0, ',', '.') . 'd';
    $formatDateTime = function ($value) {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('H:i d/m/Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };
    $formatDate = function ($value) {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $statusMap = [
        'cho_xu_ly' => ['Cho xu ly', 'blue'],
        'xuat_kho' => ['Xuat kho', 'green'],
        'dong_goi' => ['Dong goi', 'indigo'],
        'van_chuyen' => ['Dang giao', 'purple'],
        'tu_van_chuyen' => ['Tu van chuyen', 'teal'],
        'hoan_thanh' => ['Hoan thanh', 'green'],
        'huy' => ['Da huy', 'red'],
    ];
    $receiveMap = [
        'van_chuyen' => 'Giao hang',
        'nhan_tai_quay' => 'Nhan tai quay',
        'tu_van_chuyen' => 'Tu van chuyen',
    ];
    [$tenTrangThai, $statusTone] = $statusMap[$donHang['trang_thai'] ?? ''] ?? [$donHang['trang_thai'] ?? '-', 'gray'];

    $khachHang = $donHang['khach_hang'] ?? [];
    $nhanVien = $donHang['nhan_vien'] ?? [];
    $chiTiets = collect($donHang['chi_tiets'] ?? []);
    $lichSu = collect($donHang['lich_su'] ?? []);
    $tongSoLuong = $chiTiets->sum(fn($item) => (int) ($item['so_luong'] ?? 0));
    $tongDongHang = $chiTiets->count();
    $conPhaiTra = (float) ($donHang['con_phai_tra'] ?? max(0, (float) ($donHang['tien_thanh_toan'] ?? 0) - (float) ($donHang['da_thanh_toan'] ?? 0)));
    $tyLeThanhToan = min(100, max(0, (float) ($donHang['ty_le_thanh_toan'] ?? 0)));
    $diaChiGiaoHang = $donHang['dia_chi_giao_hang_day_du'] ?? '';
    $cachNhanHang = $donHang['cach_thuc_nhan_hang'] ?? 'van_chuyen';

    $timeline = [
        ['key' => 'cho_xu_ly', 'label' => 'Cho xu ly'],
        ['key' => 'xuat_kho', 'label' => 'Xuat kho'],
        ['key' => 'dong_goi', 'label' => 'Dong goi'],
        ['key' => $cachNhanHang === 'nhan_tai_quay' ? 'tu_van_chuyen' : 'van_chuyen', 'label' => $cachNhanHang === 'nhan_tai_quay' ? 'Nhan tai quay' : 'Tu van chuyen'],
        ['key' => 'hoan_thanh', 'label' => 'Khach da nhan hang'],
    ];
    $statusOrder = collect($timeline)->pluck('key')->values()->all();
    $currentIndex = array_search($donHang['trang_thai'] ?? '', $statusOrder, true);
    $currentIndex = $currentIndex === false ? -1 : $currentIndex;
    $isCanceled = ($donHang['trang_thai'] ?? '') === 'huy';
@endphp

@push('styles')
<style>
    .sell-order-page {
        min-height: calc(100vh - 64px);
        background: #f3f4f6;
        padding: 24px;
    }
    .sell-order-shell {
        max-width: 1680px;
        margin: 0 auto;
    }
    .sell-order-stack {
        display: grid;
        gap: 18px;
    }
    .sell-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-height: 56px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .sell-action-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .sell-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        border-radius: 5px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #4b5563;
        padding: 0 14px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: background .15s ease, border-color .15s ease;
    }
    .sell-btn:hover {
        background: #f9fafb;
        border-color: #cbd5e1;
    }
    .sell-btn-primary {
        border-color: #6366f1;
        background: #6366f1;
        color: #fff;
    }
    .sell-btn-primary:hover {
        background: #4f46e5;
    }
    .sell-btn-orange {
        border-color: #f97316;
        background: #f97316;
        color: #fff;
    }
    .sell-btn-orange:hover {
        background: #ea580c;
    }
    .sell-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .sell-card-pad {
        padding: 20px;
    }
    .sell-order-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 28px;
    }
    .sell-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .sell-order-code {
        color: #1f2937;
        font-size: 20px;
        line-height: 1.2;
        font-weight: 800;
    }
    .sell-badge {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        border-radius: 999px;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .sell-badge.blue { background: #dbeafe; color: #2563eb; }
    .sell-badge.green { background: #dcfce7; color: #16a34a; }
    .sell-badge.indigo { background: #e0e7ff; color: #4f46e5; }
    .sell-badge.purple { background: #ede9fe; color: #7c3aed; }
    .sell-badge.teal { background: #ccfbf1; color: #0f766e; }
    .sell-badge.red { background: #fee2e2; color: #dc2626; }
    .sell-badge.gray { background: #f3f4f6; color: #4b5563; }
    .sell-muted {
        color: #6b7280;
        font-size: 13px;
    }
    .sell-timeline {
        display: grid;
        grid-template-columns: repeat(5, minmax(150px, 1fr));
        gap: 0;
        overflow-x: auto;
        padding: 0 22px 4px;
    }
    .sell-step {
        position: relative;
        min-width: 150px;
        text-align: center;
    }
    .sell-step::before {
        content: "";
        position: absolute;
        top: 18px;
        left: 0;
        right: 0;
        height: 2px;
        background: #d1d5db;
    }
    .sell-step:first-child::before {
        left: 50%;
    }
    .sell-step:last-child::before {
        right: 50%;
    }
    .sell-step.done::before {
        background: #22c55e;
    }
    .sell-step-dot {
        position: relative;
        z-index: 1;
        width: 36px;
        height: 36px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 2px solid #d1d5db;
        background: #fff;
        color: #9ca3af;
        font-size: 13px;
        font-weight: 800;
    }
    .sell-step.done .sell-step-dot {
        border-color: #22c55e;
        background: #22c55e;
        color: #fff;
    }
    .sell-step.active .sell-step-dot {
        box-shadow: 0 0 0 4px rgba(34, 197, 94, .18);
    }
    .sell-step-actor {
        min-height: 17px;
        margin-bottom: 5px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
    }
    .sell-step-label {
        margin-top: 8px;
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
    }
    .sell-step.done .sell-step-label {
        color: #16a34a;
    }
    .sell-info-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 300px;
        gap: 18px;
    }
    .sell-section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 30px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .sell-field-list {
        display: grid;
        gap: 11px;
        color: #374151;
        font-size: 14px;
    }
    .sell-field {
        display: grid;
        grid-template-columns: 110px minmax(0, 1fr);
        gap: 14px;
        align-items: start;
    }
    .sell-field-label {
        color: #6b7280;
        font-size: 12px;
    }
    .sell-field-value {
        min-width: 0;
        color: #374151;
        font-weight: 600;
        overflow-wrap: anywhere;
    }
    .sell-linklike {
        color: #2563eb;
        font-weight: 700;
    }
    .sell-product-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px 14px;
        border-bottom: 1px solid #e5e7eb;
    }
    .sell-product-title {
        color: #1f2937;
        font-size: 15px;
        font-weight: 800;
    }
    .sell-table-wrap {
        overflow-x: auto;
        padding: 0 20px 18px;
    }
    .sell-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
        font-size: 14px;
    }
    .sell-table th {
        background: #f9fafb;
        color: #6b7280;
        padding: 13px 12px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        text-align: left;
    }
    .sell-table td {
        padding: 16px 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
        vertical-align: middle;
    }
    .sell-table tbody tr:last-child td {
        border-bottom: 0;
    }
    .sell-table .right {
        text-align: right;
    }
    .sell-table .center {
        text-align: center;
    }
    .sell-product-cell {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .sell-product-img {
        width: 44px;
        height: 54px;
        flex: 0 0 44px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        background: #f3f4f6;
    }
    .sell-product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .sell-product-placeholder {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 11px;
        font-weight: 800;
    }
    .sell-product-name {
        color: #2563eb;
        font-size: 14px;
        font-weight: 700;
    }
    .sell-product-sku {
        margin-top: 3px;
        color: #6b7280;
        font-size: 12px;
    }
    .sell-product-summary {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 18px;
        padding: 0 20px 20px;
    }
    .sell-summary-box {
        justify-self: end;
        width: 320px;
        max-width: 100%;
    }
    .sell-summary-line {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 8px 0;
        border-bottom: 1px solid #eef2f7;
        color: #6b7280;
        font-size: 14px;
    }
    .sell-summary-line strong {
        color: #111827;
        font-weight: 800;
    }
    .sell-summary-line.total strong {
        color: #4f46e5;
    }
    .sell-history-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 20px 8px;
    }
    .sell-history-icon {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #e0e7ff;
        color: #4f46e5;
        font-size: 12px;
        font-weight: 900;
    }
    .sell-history-list {
        padding: 8px 20px 22px;
    }
    .sell-history-item {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 16px;
        padding: 0 0 18px 38px;
        border-bottom: 1px solid #eef2f7;
    }
    .sell-history-item + .sell-history-item {
        padding-top: 18px;
    }
    .sell-history-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .sell-history-item::before {
        content: "";
        position: absolute;
        left: 4px;
        top: 3px;
        width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
    }
    .sell-history-item::after {
        content: "";
        position: absolute;
        left: 15px;
        top: 28px;
        bottom: 0;
        width: 1px;
        background: #e5e7eb;
    }
    .sell-history-item:last-child::after {
        display: none;
    }
    .sell-history-title {
        color: #1f2937;
        font-size: 15px;
        font-weight: 800;
    }
    .sell-empty {
        padding: 34px 16px;
        color: #6b7280;
        font-size: 14px;
        text-align: center;
    }
    @media (max-width: 1180px) {
        .sell-info-row,
        .sell-product-summary {
            grid-template-columns: 1fr;
        }
        .sell-summary-box {
            justify-self: stretch;
            width: auto;
        }
    }
    @media (max-width: 760px) {
        .sell-order-page {
            padding: 14px;
        }
        .sell-action-bar,
        .sell-order-head,
        .sell-product-head {
            align-items: stretch;
            flex-direction: column;
        }
        .sell-action-group {
            width: 100%;
        }
        .sell-btn {
            flex: 1;
        }
        .sell-card-pad,
        .sell-table-wrap,
        .sell-product-summary,
        .sell-history-list,
        .sell-history-head {
            padding-left: 14px;
            padding-right: 14px;
        }
        .sell-field {
            grid-template-columns: 90px minmax(0, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="sell-order-page">
    <div class="sell-order-shell sell-order-stack">
        <div class="sell-action-bar">
            <div class="sell-action-group">
                <a href="/doi-tac/don-hang" class="sell-btn sell-btn-primary">Don hang</a>
                <a href="/doi-tac/order-hang/danh-sach" class="sell-btn">Quan ly hang order</a>
            </div>
            <div class="sell-action-group">
                <span class="sell-btn sell-btn-orange" style="cursor:default;">Chi tiet don hang</span>
            </div>
        </div>

        <section class="sell-card sell-card-pad">
            <div class="sell-order-head">
                <div>
                    <div class="sell-title-row">
                        <h1 class="sell-order-code">{{ $donHang['ma_don_hang'] ?? '-' }}</h1>
                        <span class="sell-badge {{ $statusTone }}">{{ $tenTrangThai }}</span>
                    </div>
                    <p class="sell-muted" style="margin-top:8px;">Tao luc {{ $formatDateTime($donHang['created_at'] ?? null) }}</p>
                </div>
                <span class="sell-badge gray">Don hang thuong</span>
            </div>

            <div class="sell-timeline">
                @foreach($timeline as $index => $step)
                    @php
                        $done = !$isCanceled && $currentIndex >= $index;
                        $active = !$isCanceled && $currentIndex === $index;
                    @endphp
                    <div class="sell-step {{ $done ? 'done' : '' }} {{ $active ? 'active' : '' }}">
                        <div class="sell-step-actor">{{ $done ? ($nhanVien['ten'] ?? 'Admin') : '' }}</div>
                        <div class="sell-step-dot">{{ $done ? '✓' : $index + 1 }}</div>
                        <div class="sell-step-label">{{ $step['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="sell-info-row">
            <div class="sell-card sell-card-pad">
                <div class="sell-section-title">Thong tin khach hang</div>
                <div class="sell-field-list">
                    <div class="sell-field">
                        <span class="sell-field-label">Ten KH:</span>
                        <span class="sell-field-value sell-linklike">{{ $khachHang['ten'] ?? '-' }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">SDT:</span>
                        <span class="sell-field-value">{{ $khachHang['sdt'] ?? '-' }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">Email:</span>
                        <span class="sell-field-value">{{ $khachHang['email'] ?? '-' }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">Ma KH:</span>
                        <span class="sell-field-value">{{ $khachHang['ma_khach_hang'] ?? '-' }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">Nhom KH:</span>
                        <span class="sell-field-value sell-linklike">{{ $khachHang['nhom_khach_hang'] ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="sell-card sell-card-pad">
                <div class="sell-section-title">
                    <span>Cach thuc nhan hang</span>
                    <span class="sell-badge purple">{{ $receiveMap[$cachNhanHang] ?? $cachNhanHang }}</span>
                </div>
                <div class="sell-field-list">
                    <div class="sell-field">
                        <span class="sell-field-label">Hinh thuc:</span>
                        <span class="sell-field-value">{{ $receiveMap[$cachNhanHang] ?? $cachNhanHang }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">Dia chi:</span>
                        <span class="sell-field-value">{{ $diaChiGiaoHang ?: '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="sell-card sell-card-pad">
                <div class="sell-section-title" style="text-transform:none;">Thong tin don hang</div>
                <div class="sell-field-list">
                    <div class="sell-field">
                        <span class="sell-field-label">Ngay dat</span>
                        <span class="sell-field-value">: {{ $formatDate($donHang['ngay_dat'] ?? null) }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">Ngay giao du kien</span>
                        <span class="sell-field-value">: {{ $formatDate($donHang['ngay_giao_du_kien'] ?? null) }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">Nhan vien</span>
                        <span class="sell-field-value">: {{ $nhanVien['ten'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="sell-card">
            <div class="sell-product-head">
                <div>
                    <h2 class="sell-product-title">Thong tin san pham</h2>
                    <p class="sell-muted" style="margin-top:4px;">{{ $tongDongHang }} dong - {{ number_format($tongSoLuong) }} san pham</p>
                </div>
                <span class="sell-btn sell-btn-primary" style="cursor:default;">Kiem tra don hang</span>
            </div>

            <div class="sell-table-wrap">
                <table class="sell-table">
                    <thead>
                        <tr>
                            <th style="width:58px;">STT</th>
                            <th style="width:72px;">Anh</th>
                            <th>Ten san pham</th>
                            <th class="center" style="width:110px;">So luong</th>
                            <th class="center" style="width:120px;">SL order ve</th>
                            <th class="right" style="width:130px;">Gia ban</th>
                            <th class="right" style="width:110px;">Chiet khau</th>
                            <th class="right" style="width:140px;">Thanh tien</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chiTiets as $ct)
                            @php
                                $anhRaw = $ct['anh_san_pham'] ?? ($ct['san_pham']['anh_san_pham'] ?? null);
                                $anhArr = is_string($anhRaw) ? (json_decode($anhRaw, true) ?: [$anhRaw]) : (is_array($anhRaw) ? $anhRaw : []);
                                $anh = $anhArr[0] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="sell-product-img">
                                        @if($anh)
                                            <img src="{{ asset('storage/uploads/sanpham/' . $anh) }}" alt="">
                                        @else
                                            <div class="sell-product-placeholder">SP</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="sell-product-cell">
                                        <div>
                                            <p class="sell-product-name">{{ $ct['ten'] ?? '-' }}</p>
                                            <p class="sell-product-sku">{{ $ct['ma_sku'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="center">{{ number_format((int) ($ct['so_luong'] ?? 0)) }}</td>
                                <td class="center" style="color:#059669; font-weight:700;">{{ number_format((int) ($ct['so_luong_order_ve'] ?? 0)) }}</td>
                                <td class="right">{{ number_format((float) ($ct['gia_ban'] ?? 0), 0, ',', '.') }}</td>
                                <td class="right">{{ (float) ($ct['chiet_khau'] ?? 0) }}%</td>
                                <td class="right" style="font-weight:800; color:#111827;">{{ number_format((float) ($ct['thanh_tien'] ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="sell-empty">Khong co san pham.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="sell-product-summary">
                <div></div>
                <div class="sell-summary-box">
                    <div class="sell-summary-line">
                        <span>Tong tien ({{ number_format($tongSoLuong) }} san pham)</span>
                        <strong>{{ number_format((float) ($donHang['tong_tien'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="sell-summary-line">
                        <span>Chiet khau ({{ (float) ($donHang['chiet_khau'] ?? 0) }}%)</span>
                        <strong style="color:#ef4444;">{{ number_format((float) ($donHang['tien_giam'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="sell-summary-line total">
                        <span>Tien thanh toan</span>
                        <strong>{{ number_format((float) ($donHang['tien_thanh_toan'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="sell-summary-line">
                        <span>Da thanh toan</span>
                        <strong style="color:#059669;">{{ number_format((float) ($donHang['da_thanh_toan'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="sell-summary-line">
                        <span>Con phai tra</span>
                        <strong>{{ number_format($conPhaiTra, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="sell-card">
            <div class="sell-history-head">
                <div class="sell-history-icon">i</div>
                <div>
                    <h2 class="sell-product-title">Lich su don hang</h2>
                    <p class="sell-muted">{{ $lichSu->count() }} muc</p>
                </div>
            </div>
            <div class="sell-history-list">
                @forelse($lichSu as $item)
                    <div class="sell-history-item">
                        <div>
                            <p class="sell-history-title">{{ $item['hanh_dong_text'] ?? ($item['hanh_dong'] ?? '-') }}</p>
                            @if(!empty($item['mo_ta']))
                                <p class="sell-muted" style="margin-top:6px;">{{ $item['mo_ta'] }}</p>
                            @endif
                            <p class="sell-muted" style="margin-top:6px;">
                                Boi: {{ $item['nguoi_thuc_hien'] ?? ($item['nhan_vien']['ten'] ?? 'He thong') }}
                                - {{ $formatDateTime($item['created_at'] ?? null) }}
                            </p>
                        </div>
                        <span class="sell-muted">-</span>
                    </div>
                @empty
                    <div class="sell-empty">Chua co lich su don hang.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
