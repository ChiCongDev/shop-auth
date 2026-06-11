<?php

namespace App\Http\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DoiTacDonHangNoiBoService
{
    public function layDanhSach(int $nhanVienId, array $boLoc = []): array
    {
        return $this->guiGet('/api/noi-bo/don-hang/danh-sach', array_merge($boLoc, [
            'nhan_vien_id' => $nhanVienId,
        ]), [
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'lay_danh_sach_don_hang',
        ]);
    }

    public function layChiTiet(int $donHangId, int $nhanVienId): array
    {
        return $this->guiGet('/api/noi-bo/don-hang/' . $donHangId, [
            'nhan_vien_id' => $nhanVienId,
        ], [
            'don_hang_id' => $donHangId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'lay_chi_tiet_don_hang',
        ]);
    }

    private function guiGet(string $path, array $query = [], array $logContext = []): array
    {
        if (!config('services.sell_internal.enabled', false)) {
            return [
                'success' => false,
                'message' => 'Ket noi API noi bo sell dang tat.',
                'status' => 503,
            ];
        }

        $baseUrl = rtrim((string) config('services.sell_internal.url'), '/');
        $token = (string) config('services.sell_internal.token');

        if ($baseUrl === '' || $token === '') {
            return [
                'success' => false,
                'message' => 'Chua cau hinh ket noi API noi bo voi he thong sell.',
                'status' => 503,
            ];
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.sell_internal.timeout', 10))
                ->withToken($token)
                ->get($baseUrl . $path, $query);

            $json = $response->json();

            if (!is_array($json)) {
                return [
                    'success' => false,
                    'message' => 'API noi bo sell tra ve du lieu khong hop le.',
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => (bool) ($json['success'] ?? $json['thanh_cong'] ?? $response->successful()),
                'message' => (string) ($json['message'] ?? $json['thong_bao'] ?? 'Da lay du lieu tu he thong sell.'),
                'data' => $json['data'] ?? $json['du_lieu'] ?? null,
                'pagination' => $json['pagination'] ?? $json['phan_trang'] ?? null,
                'stats' => $json['stats'] ?? $json['thong_ke'] ?? null,
                'status' => $response->status(),
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error('Loi goi API don hang noi bo sell tu khu doi tac: ' . $e->getMessage(), array_merge($logContext, [
                'path' => $path,
            ]));

            return [
                'success' => false,
                'message' => 'Khong ket noi duoc API noi bo sell.',
                'status' => 503,
            ];
        }
    }
}
