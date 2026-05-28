<?php

namespace App\Http\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DoiTacPhieuTraHangNoiBoService
{
    public function layDanhSach(int $nhanVienId, array $boLoc = []): array
    {
        return $this->guiGet('/api/noi-bo/phieu-tra-hang/danh-sach', array_merge($boLoc, [
            'nhan_vien_id' => $nhanVienId,
        ]), [
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'lay_danh_sach_phieu_tra_hang',
        ]);
    }

    public function layChiTiet(int $phieuTraHangId, int $nhanVienId): array
    {
        return $this->guiGet('/api/noi-bo/phieu-tra-hang/' . $phieuTraHangId, [
            'nhan_vien_id' => $nhanVienId,
        ], [
            'phieu_tra_hang_id' => $phieuTraHangId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'lay_chi_tiet_phieu_tra_hang',
        ]);
    }

    public function laySoLuongDaTra(int $donHangId, int $nhanVienId): array
    {
        return $this->guiGet('/api/noi-bo/phieu-tra-hang/so-luong-da-tra/' . $donHangId, [
            'nhan_vien_id' => $nhanVienId,
        ], [
            'don_hang_id' => $donHangId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'lay_so_luong_da_tra',
        ]);
    }

    public function taoPhieu(int $nhanVienId, array $payload): array
    {
        return $this->guiPost('/api/noi-bo/phieu-tra-hang/tao', array_merge($payload, [
            'nhan_vien_id' => $nhanVienId,
            'nguon' => 'shop_auth_doi_tac',
        ]), [
            'nhan_vien_id' => $nhanVienId,
            'don_hang_id' => $payload['don_hang_id'] ?? null,
            'hanh_dong' => 'tao_phieu_tra_hang',
        ]);
    }

    public function hoanTien(int $phieuTraHangId, int $nhanVienId, array $payload): array
    {
        return $this->guiPost('/api/noi-bo/phieu-tra-hang/' . $phieuTraHangId . '/hoan-tien', array_merge($payload, [
            'nhan_vien_id' => $nhanVienId,
            'nguon' => 'shop_auth_doi_tac',
        ]), [
            'phieu_tra_hang_id' => $phieuTraHangId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'hoan_tien_phieu_tra_hang',
        ]);
    }

    private function guiGet(string $path, array $query = [], array $logContext = []): array
    {
        return $this->guiRequest('get', $path, $query, $logContext);
    }

    private function guiPost(string $path, array $payload, array $logContext = []): array
    {
        return $this->guiRequest('post', $path, $payload, $logContext);
    }

    private function guiRequest(string $method, string $path, array $data, array $logContext = []): array
    {
        $baseUrl = rtrim((string) config('services.sell_internal.url'), '/');
        $token = (string) config('services.sell_internal.token');

        if ($baseUrl === '' || $token === '') {
            return [
                'success' => false,
                'message' => 'Chưa cấu hình kết nối API nội bộ với hệ thống sell.',
                'status' => 503,
            ];
        }

        try {
            $request = Http::acceptJson()
                ->asJson()
                ->timeout((int) config('services.sell_internal.timeout', 10))
                ->withToken($token);

            $response = $method === 'get'
                ? $request->get($baseUrl . $path, $data)
                : $request->post($baseUrl . $path, $data);

            $json = $response->json();

            if (!is_array($json)) {
                return [
                    'success' => false,
                    'message' => 'API nội bộ sell trả về dữ liệu không hợp lệ.',
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => (bool) ($json['success'] ?? $json['thanh_cong'] ?? $response->successful()),
                'message' => (string) ($json['message'] ?? $json['thong_bao'] ?? 'Đã gửi thao tác sang hệ thống sell.'),
                'data' => $json['data'] ?? $json['du_lieu'] ?? null,
                'pagination' => $json['pagination'] ?? $json['phan_trang'] ?? null,
                'stats' => $json['stats'] ?? $json['thong_ke'] ?? null,
                'status' => $response->status(),
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi gọi API phiếu trả hàng nội bộ sell từ khu đối tác: ' . $e->getMessage(), array_merge($logContext, [
                'path' => $path,
            ]));

            return [
                'success' => false,
                'message' => 'Không kết nối được API nội bộ sell.',
                'status' => 503,
            ];
        }
    }
}
