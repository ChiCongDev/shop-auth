@extends('layouts.app')
@section('title', 'Chi tiết đơn hàng ' . ($donHang['ma_don_hang'] ?? ''))

@php
    $formatMoney = fn($value) => number_format((float) ($value ?? 0), 0, ',', '.') . 'đ';
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
        'cho_xu_ly' => ['Chờ xử lý', 'blue'],
        'xuat_kho' => ['Xuất kho', 'green'],
        'dong_goi' => ['Đóng gói', 'indigo'],
        'van_chuyen' => ['Đang giao', 'purple'],
        'tu_van_chuyen' => ['Tự vận chuyển', 'teal'],
        'hoan_thanh' => ['Khách đã nhận hàng', 'green'],
        'huy' => ['Đã hủy', 'red'],
    ];
    $receiveMap = [
        'van_chuyen' => 'Giao hàng',
        'nhan_tai_quay' => 'Nhận tại quầy',
        'tu_van_chuyen' => 'Tự vận chuyển',
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
    $ghiChuDonHang = trim(preg_replace('/\s*\[VTP:[^\]]+\]/', '', (string) ($donHang['ghi_chu'] ?? '')));

    $timeline = [
        ['key' => 'cho_xu_ly', 'label' => 'Chờ xử lý'],
        ['key' => 'xuat_kho', 'label' => 'Xuất kho'],
        ['key' => 'dong_goi', 'label' => 'Đóng gói'],
        [
            'key' => in_array($cachNhanHang, ['nhan_tai_quay', 'tu_van_chuyen'], true) ? 'tu_van_chuyen' : 'van_chuyen',
            'label' => in_array($cachNhanHang, ['nhan_tai_quay', 'tu_van_chuyen'], true) ? 'Tự vận chuyển' : 'Shipper đã lấy hàng',
        ],
        ['key' => 'hoan_thanh', 'label' => 'Khách đã nhận hàng'],
    ];
    $historyMetaMap = [
        'tao_don' => ['icon' => '📝', 'tone' => 'blue', 'title' => 'Tạo đơn hàng'],
        'duyet_don_order' => ['icon' => '📋', 'tone' => 'sky', 'title' => 'Duyệt đơn order'],
        'bao_hang_ve_order' => ['icon' => '📦', 'tone' => 'teal', 'title' => 'Báo hàng về'],
        'thong_bao_hang_order_da_ve' => ['icon' => '📦', 'tone' => 'emerald', 'title' => 'Thông báo hàng order đã về'],
        'xuat_kho' => ['icon' => '📦', 'tone' => 'green', 'title' => 'Xuất kho'],
        'dong_goi' => ['icon' => '🎁', 'tone' => 'green', 'title' => 'Đóng gói'],
        'van_chuyen' => ['icon' => '🚚', 'tone' => 'indigo', 'title' => 'Shipper đã lấy hàng'],
        'tu_van_chuyen' => ['icon' => '🚚', 'tone' => 'teal', 'title' => 'Tự vận chuyển'],
        'hoan_thanh' => ['icon' => '✓', 'tone' => 'emerald', 'title' => 'Khách đã nhận hàng'],
        'huy' => ['icon' => '×', 'tone' => 'red', 'title' => 'Hủy đơn hàng'],
        'thanh_toan' => ['icon' => '₫', 'tone' => 'yellow', 'title' => 'Thanh toán'],
        'cap_nhat_dia_chi' => ['icon' => '⌖', 'tone' => 'cyan', 'title' => 'Cập nhật địa chỉ giao hàng'],
        'gui_viettel_post' => ['icon' => '✉', 'tone' => 'red', 'title' => 'Gửi Viettel Post'],
        'tao_phieu_tra' => ['icon' => '↩', 'tone' => 'amber', 'title' => 'Tạo phiếu trả hàng'],
        'hoan_tien_tra' => ['icon' => '₫', 'tone' => 'emerald', 'title' => 'Hoàn tiền trả hàng'],
        'nhan_hang_tra' => ['icon' => '↵', 'tone' => 'teal', 'title' => 'Nhận hàng trả lại'],
        'huy_phieu_tra' => ['icon' => '×', 'tone' => 'rose', 'title' => 'Hủy phiếu trả hàng'],
        'sua_so_luong' => ['icon' => '±', 'tone' => 'violet', 'title' => 'Sửa số lượng'],
        'hoan_tac_sua_sl' => ['icon' => '↶', 'tone' => 'slate', 'title' => 'Hoàn tác sửa số lượng'],
    ];
    $statusOrder = collect($timeline)->pluck('key')->values()->all();
    $currentIndex = array_search($donHang['trang_thai'] ?? '', $statusOrder, true);
    $currentIndex = $currentIndex === false ? -1 : $currentIndex;
    $isCanceled = ($donHang['trang_thai'] ?? '') === 'huy';
    $trangThaiDonHang = $donHang['trang_thai'] ?? '';
    $doiTacQuyen = session('doi_tac_quyen');
    $coQuyenDoiTra = in_array($doiTacQuyen, ['admin', 'thu_kho', 'quan_ly_order'], true);
    $coQuyenXuLySauXuatKho = in_array($doiTacQuyen, ['admin', 'thu_kho', 'quan_ly_order', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'], true);
    $normalOrderActions = [];
    if ($coQuyenXuLySauXuatKho) {
        if ($trangThaiDonHang === 'xuat_kho') {
            $normalOrderActions[] = ['label' => 'Đóng gói', 'tone' => 'indigo'];
        } elseif ($trangThaiDonHang === 'dong_goi') {
            $normalOrderActions[] = in_array($cachNhanHang, ['nhan_tai_quay', 'tu_van_chuyen'], true)
                ? ['label' => 'Tự vận chuyển', 'tone' => 'teal']
                : ['label' => 'Shipper đã lấy hàng', 'tone' => 'purple'];
        } elseif (in_array($trangThaiDonHang, ['van_chuyen', 'tu_van_chuyen'], true)) {
            $normalOrderActions[] = ['label' => 'Khách đã nhận hàng', 'tone' => 'green'];
        }
    }
    if ($coQuyenDoiTra && in_array($trangThaiDonHang, ['xuat_kho', 'dong_goi', 'van_chuyen', 'tu_van_chuyen', 'hoan_thanh'], true)) {
        $normalOrderActions[] = ['label' => 'Đổi trả hàng', 'tone' => 'orange'];
    }
    $coQuyenXuatKho = in_array($doiTacQuyen, ['admin', 'thu_kho'], true);
    $coQuyenXuLyDonThuong = in_array($doiTacQuyen, ['admin', 'thu_kho', 'nhan_vien_ban_hang_cap_1', 'nhan_vien_ban_hang_cap_2'], true);
    $normalOrderActions = [];
    if ($trangThaiDonHang === 'cho_xu_ly' && $coQuyenXuatKho) {
        $normalOrderActions[] = ['action' => 'xuat-kho', 'label' => 'Xuất kho', 'tone' => 'orange'];
    } elseif ($coQuyenXuLyDonThuong) {
        if ($trangThaiDonHang === 'xuat_kho') {
            $normalOrderActions[] = ['action' => 'dong-goi', 'label' => 'Đóng gói', 'tone' => 'indigo'];
        } elseif ($trangThaiDonHang === 'dong_goi') {
            $normalOrderActions[] = in_array($cachNhanHang, ['nhan_tai_quay', 'tu_van_chuyen'], true)
                ? ['action' => 'tu-van-chuyen-ntq', 'label' => 'Tự vận chuyển', 'tone' => 'teal']
                : ['action' => 'van-chuyen', 'label' => 'Shipper đã lấy hàng', 'tone' => 'purple'];
        } elseif (in_array($trangThaiDonHang, ['van_chuyen', 'tu_van_chuyen'], true)) {
            $normalOrderActions[] = ['action' => 'hoan-thanh', 'label' => 'Khách đã nhận hàng', 'tone' => 'green'];
        }
    }
    $normalOrderReturnUrl = in_array($doiTacQuyen, ['admin', 'thu_kho'], true)
        && in_array($trangThaiDonHang, ['xuat_kho', 'dong_goi', 'van_chuyen', 'tu_van_chuyen', 'hoan_thanh'], true)
        ? '/doi-tac/don-hang/' . (int) ($donHang['id'] ?? 0) . '/doi-tra'
        : null;
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
    .sell-btn-teal {
        border-color: #14b8a6;
        background: #14b8a6;
        color: #fff;
    }
    .sell-btn-teal:hover {
        background: #0f766e;
    }
    .sell-btn-indigo {
        border-color: #6366f1;
        background: #6366f1;
        color: #fff;
    }
    .sell-btn-indigo:hover {
        background: #4f46e5;
    }
    .sell-btn-purple {
        border-color: #8b5cf6;
        background: #8b5cf6;
        color: #fff;
    }
    .sell-btn-purple:hover {
        background: #7c3aed;
    }
    .sell-btn-green {
        border-color: #22c55e;
        background: #22c55e;
        color: #fff;
    }
    .sell-btn-green:hover {
        background: #16a34a;
    }
    .sell-action-note {
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
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
        padding: 8px 22px 4px;
    }
    .sell-step {
        position: relative;
        min-width: 150px;
        text-align: center;
    }
    .sell-step-track {
        position: relative;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sell-step-line {
        position: absolute;
        top: 50%;
        height: 2px;
        transform: translateY(-50%);
        background: #d1d5db;
    }
    .sell-step-line.done {
        background: #22c55e;
    }
    .sell-step-line-before {
        left: 0;
        right: 50%;
    }
    .sell-step-line-after {
        left: 50%;
        right: 0;
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
        margin-bottom: 2px;
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
    .sell-note-card {
        border: 1px solid #fcd34d;
        background: #fffbeb;
        padding: 18px 20px;
    }
    .sell-note-head {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        color: #78350f;
        font-size: 14px;
        font-weight: 800;
    }
    .sell-note-icon {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #fef3c7;
        color: #b45309;
        font-size: 12px;
        font-weight: 900;
    }
    .sell-note-content {
        margin: 0;
        white-space: pre-wrap;
        color: #78350f;
        font-size: 14px;
        line-height: 1.65;
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
        grid-template-columns: 36px minmax(0, 1fr) auto;
        gap: 16px;
        padding: 0 0 18px;
        border-bottom: 1px solid #eef2f7;
    }
    .sell-history-item + .sell-history-item {
        padding-top: 18px;
    }
    .sell-history-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .sell-history-rail {
        position: relative;
        display: flex;
        justify-content: center;
    }
    .sell-history-rail::after {
        content: "";
        position: absolute;
        top: 34px;
        bottom: -18px;
        width: 1px;
        background: #e5e7eb;
    }
    .sell-history-item:last-child .sell-history-rail::after {
        display: none;
    }
    .sell-history-dot {
        position: relative;
        z-index: 1;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 15px;
        font-weight: 800;
        line-height: 1;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
    }
    .sell-history-dot.blue { background: #dbeafe; color: #2563eb; }
    .sell-history-dot.sky { background: #e0f2fe; color: #0284c7; }
    .sell-history-dot.teal { background: #ccfbf1; color: #0f766e; }
    .sell-history-dot.emerald { background: #d1fae5; color: #059669; }
    .sell-history-dot.green { background: #dcfce7; color: #16a34a; }
    .sell-history-dot.indigo { background: #e0e7ff; color: #4f46e5; }
    .sell-history-dot.red { background: #fee2e2; color: #dc2626; }
    .sell-history-dot.yellow { background: #fef3c7; color: #ca8a04; }
    .sell-history-dot.cyan { background: #cffafe; color: #0891b2; }
    .sell-history-dot.amber { background: #fef3c7; color: #d97706; }
    .sell-history-dot.rose { background: #ffe4e6; color: #e11d48; }
    .sell-history-dot.violet { background: #ede9fe; color: #7c3aed; }
    .sell-history-dot.slate { background: #e2e8f0; color: #475569; }
    .sell-history-dot.gray { background: #f3f4f6; color: #4b5563; }
    .sell-history-time {
        align-self: start;
        color: #9ca3af;
        font-size: 12px;
        white-space: nowrap;
    }
    .sell-history-title {
        color: #1f2937;
        font-size: 15px;
        font-weight: 800;
    }
    .sell-history-meta {
        margin-top: 6px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        color: #9ca3af;
        font-size: 12px;
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
        @if(count($normalOrderActions) > 0 || $normalOrderReturnUrl)
            <div class="sell-action-bar" data-normal-order-action-bar>
                <div class="sell-action-group">
                    @foreach($normalOrderActions as $action)
                        <button type="button" class="sell-btn sell-btn-{{ $action['tone'] }}" data-normal-order-api="{{ $action['action'] }}">
                            {{ $action['label'] }}
                        </button>
                    @endforeach
                </div>
                <div class="sell-action-group">
                    @if($normalOrderReturnUrl)
                        <a href="{{ $normalOrderReturnUrl }}" class="sell-btn sell-btn-orange">Đổi trả hàng</a>
                    @endif
                </div>
            </div>
        @endif

        <section class="sell-card sell-card-pad">
            <div class="sell-order-head">
                <div>
                    <div class="sell-title-row">
                        <h1 class="sell-order-code">{{ $donHang['ma_don_hang'] ?? '-' }}</h1>
                        <span class="sell-badge {{ $statusTone }}">{{ $tenTrangThai }}</span>
                    </div>
                    <p class="sell-muted" style="margin-top:8px;">Tạo lúc {{ $formatDateTime($donHang['created_at'] ?? null) }}</p>
                </div>
            </div>

            <div class="sell-timeline">
                @foreach($timeline as $index => $step)
                    @php
                        $done = !$isCanceled && $currentIndex >= $index;
                        $active = !$isCanceled && $currentIndex === $index;
                    @endphp
                    <div class="sell-step {{ $done ? 'done' : '' }} {{ $active ? 'active' : '' }}">
                        <div class="sell-step-actor">{{ $done ? ($nhanVien['ten'] ?? 'Admin') : '' }}</div>
                        <div class="sell-step-track">
                            @if($index > 0)
                                <span class="sell-step-line sell-step-line-before {{ $done ? 'done' : '' }}"></span>
                            @endif
                            @if($index < count($timeline) - 1)
                                <span class="sell-step-line sell-step-line-after {{ !$isCanceled && $currentIndex > $index ? 'done' : '' }}"></span>
                            @endif
                        <div class="sell-step-dot">{{ $done ? '✓' : $index + 1 }}</div>
                        </div>
                        <div class="sell-step-label">{{ $step['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="sell-info-row">
            <div class="sell-card sell-card-pad">
                <div class="sell-section-title">Thông tin khách hàng</div>
                <div class="sell-field-list">
                    <div class="sell-field">
                        <span class="sell-field-label">Tên KH:</span>
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
                        <span class="sell-field-label">Mã KH:</span>
                        <span class="sell-field-value">{{ $khachHang['ma_khach_hang'] ?? '-' }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">Nhóm KH:</span>
                        <span class="sell-field-value sell-linklike">{{ $khachHang['nhom_khach_hang'] ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="sell-card sell-card-pad">
                <div class="sell-section-title">
                    <span>Cách thức nhận hàng</span>
                    <span class="sell-badge purple">{{ $receiveMap[$cachNhanHang] ?? $cachNhanHang }}</span>
                </div>
                <div class="sell-field-list">
                    <div class="sell-field">
                        <span class="sell-field-label">Hình thức:</span>
                        <span class="sell-field-value">{{ $receiveMap[$cachNhanHang] ?? $cachNhanHang }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">Địa chỉ:</span>
                        <span class="sell-field-value">{{ $diaChiGiaoHang ?: '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="sell-card sell-card-pad">
                <div class="sell-section-title" style="text-transform:none;">Thông tin đơn hàng</div>
                <div class="sell-field-list">
                    <div class="sell-field">
                        <span class="sell-field-label">Ngày đặt</span>
                        <span class="sell-field-value">: {{ $formatDate($donHang['ngay_dat'] ?? null) }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">Ngày giao dự kiến</span>
                        <span class="sell-field-value">: {{ $formatDate($donHang['ngay_giao_du_kien'] ?? null) }}</span>
                    </div>
                    <div class="sell-field">
                        <span class="sell-field-label">Nhân viên</span>
                        <span class="sell-field-value">: {{ $nhanVien['ten'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </section>

        @if($ghiChuDonHang !== '')
            <section id="boxGhiChuDonHang" class="sell-card sell-note-card">
                <div class="sell-note-head">
                    <span class="sell-note-icon">i</span>
                    <span>Ghi chú đơn hàng</span>
                </div>
                <p class="sell-note-content">{!! nl2br(e($ghiChuDonHang)) !!}</p>
            </section>
        @endif

        <section class="sell-card">
            <div class="sell-product-head">
                <div>
                    <h2 class="sell-product-title">Thông tin sản phẩm</h2>
                    <p class="sell-muted" style="margin-top:4px;">{{ $tongDongHang }} dòng - {{ number_format($tongSoLuong) }} sản phẩm</p>
                </div>
                <span class="sell-btn sell-btn-primary" style="cursor:default;">Kiểm tra đơn hàng</span>
            </div>

            <div class="sell-table-wrap">
                <table class="sell-table">
                    <thead>
                        <tr>
                            <th style="width:58px;">STT</th>
                            <th style="width:72px;">Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th class="center" style="width:110px;">Số lượng</th>
                            <th class="center" style="width:120px;">SL order về</th>
                            <th class="right" style="width:130px;">Giá bán</th>
                            <th class="right" style="width:110px;">Chiết khấu</th>
                            <th class="right" style="width:140px;">Thành tiền</th>
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
                                    <div class="sell-empty">Không có sản phẩm.</div>
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
                        <span>Tổng tiền ({{ number_format($tongSoLuong) }} sản phẩm)</span>
                        <strong>{{ number_format((float) ($donHang['tong_tien'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="sell-summary-line">
                        <span>Chiết khấu ({{ (float) ($donHang['chiet_khau'] ?? 0) }}%)</span>
                        <strong style="color:#ef4444;">{{ number_format((float) ($donHang['tien_giam'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="sell-summary-line total">
                        <span>Tiền thanh toán</span>
                        <strong>{{ number_format((float) ($donHang['tien_thanh_toan'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="sell-summary-line">
                        <span>Đã thanh toán</span>
                        <strong style="color:#059669;">{{ number_format((float) ($donHang['da_thanh_toan'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="sell-summary-line">
                        <span>Còn phải trả</span>
                        <strong>{{ number_format($conPhaiTra, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="sell-card">
            <div class="sell-history-head">
                <div class="sell-history-icon">i</div>
                <div>
                    <h2 class="sell-product-title">Lịch sử đơn hàng</h2>
                    <p class="sell-muted">{{ $lichSu->count() }} mục</p>
                </div>
            </div>
            <div class="sell-history-list">
                @forelse($lichSu as $item)
                    @php
                        $hanhDong = $item['hanh_dong'] ?? '';
                        $historyMeta = $historyMetaMap[$hanhDong] ?? ['icon' => '•', 'tone' => 'gray', 'title' => null];
                        $historyTitle = $historyMeta['title'] ?? ($item['hanh_dong_text'] ?? ($hanhDong ?: '-'));
                        $historyActor = $item['nguoi_thuc_hien'] ?? ($item['nhan_vien']['ten'] ?? 'Hệ thống');
                        $historyTime = $formatDateTime($item['created_at'] ?? null);
                    @endphp
                    <div class="sell-history-item">
                        <div class="sell-history-rail">
                            <div class="sell-history-dot {{ $historyMeta['tone'] }}">{{ $historyMeta['icon'] }}</div>
                        </div>
                        <div>
                            <p class="sell-history-title">{{ $historyTitle }}</p>
                            @if(!empty($item['mo_ta']))
                                <p class="sell-muted" style="margin-top:6px;">{{ $item['mo_ta'] }}</p>
                            @endif
                            <p class="sell-history-meta">
                                <span>Bởi: {{ $historyActor }}</span>
                                <span>•</span>
                                <span>{{ $historyTime }}</span>
                            </p>
                        </div>
                        <span class="sell-history-time">{{ $historyTime }}</span>
                    </div>
                @empty
                    <div class="sell-empty">Chưa có lịch sử đơn hàng.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-normal-order-action]').forEach((button) => {
        button.addEventListener('click', () => {
            window.alert('Chức năng thao tác trực tiếp đơn thường từ Shop Auth chưa có API nội bộ an toàn. Vui lòng thao tác trên hệ thống quản lý bán hàng để tránh ảnh hưởng dữ liệu.');
        });
    });
</script>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('[data-normal-order-api]').forEach((button) => {
        button.addEventListener('click', async () => {
            const action = button.dataset.normalOrderApi;
            const label = button.textContent.trim();
            if (!window.confirm(`Xác nhận thực hiện thao tác "${label}"?`)) {
                return;
            }

            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Đang xử lý...';

            try {
                const response = await fetch(`/api/doi-tac/don-hang/{{ (int) ($donHang['id'] ?? 0) }}/${action}`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    window.alert(data.message || 'Không thể thực hiện thao tác.');
                    return;
                }

                window.alert(data.message || 'Đã thực hiện thao tác.');
                window.location.reload();
            } catch (error) {
                window.alert('Không kết nối được máy chủ xử lý thao tác.');
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    });
</script>
@endpush
