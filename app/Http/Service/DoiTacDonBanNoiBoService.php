<?php

namespace App\Http\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DoiTacDonBanNoiBoService
{
    private array $hanhDongDuocPhep = [
        'duyet',
        'bao-hang-ve',
        'xuat-kho',
        'dong-goi',
        'van-chuyen',
        'tu-van-chuyen-ntq',
        'hoan-thanh',
    ];

    public function guiThaoTac(int $donHangId, int $nhanVienId, string $hanhDong): array
    {
        if (!in_array($hanhDong, $this->hanhDongDuocPhep, true)) {
            return [
                'success' => false,
                'message' => 'Thao tác không hợp lệ.',
                'status' => 422,
            ];
        }

        return $this->guiPost('/api/noi-bo/don-order/' . $donHangId . '/' . $hanhDong, [
            'nhan_vien_id' => $nhanVienId,
            'nguon' => 'shop_auth_doi_tac',
        ], [
            'don_hang_id' => $donHangId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => $hanhDong,
        ]);
    }

    public function guiTaoDonOrder(int $nhanVienId, array $payload): array
    {
        return $this->guiPost('/api/noi-bo/don-order/tao', array_merge($payload, [
            'nhan_vien_id' => $nhanVienId,
            'nguon' => 'shop_auth_doi_tac',
        ]), [
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'tao_don_order',
        ]);
    }

    public function guiChuyenDonBan(int $donOrderId, int $nhanVienId, array $payload = []): array
    {
        return $this->guiPost('/api/noi-bo/don-order/' . $donOrderId . '/chuyen-don-ban', array_merge($payload, [
            'nhan_vien_id' => $nhanVienId,
            'nguon' => 'shop_auth_doi_tac',
        ]), [
            'don_order_id' => $donOrderId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'chuyen_don_ban',
        ]);
    }

    public function guiHuyDonOrder(int $donOrderId, int $nhanVienId): array
    {
        return $this->guiPost('/api/noi-bo/don-order/' . $donOrderId . '/huy', [
            'nhan_vien_id' => $nhanVienId,
            'nguon' => 'shop_auth_doi_tac',
        ], [
            'don_order_id' => $donOrderId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'huy_don_order',
        ]);
    }

    public function guiHuyChiTietDonOrder(int $chiTietId, int $nhanVienId): array
    {
        return $this->guiPost('/api/noi-bo/don-order/chi-tiet/' . $chiTietId . '/huy', [
            'nhan_vien_id' => $nhanVienId,
            'nguon' => 'shop_auth_doi_tac',
        ], [
            'chi_tiet_id' => $chiTietId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'huy_chi_tiet_don_order',
        ]);
    }

    private function guiPost(string $path, array $payload, array $logContext = []): array
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
            $response = Http::acceptJson()
                ->asJson()
                ->timeout((int) config('services.sell_internal.timeout', 10))
                ->withToken($token)
                ->post($baseUrl . $path, $payload);

            $data = $response->json();

            if (!is_array($data)) {
                return [
                    'success' => false,
                    'message' => 'API nội bộ sell trả về dữ liệu không hợp lệ.',
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => (bool) ($data['success'] ?? $data['thanh_cong'] ?? $response->successful()),
                'message' => (string) ($data['message'] ?? $data['thong_bao'] ?? 'Đã gửi thao tác sang hệ thống sell.'),
                'data' => $data['data'] ?? $data['du_lieu'] ?? null,
                'status' => $response->status(),
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi gọi API nội bộ sell từ khu đối tác: ' . $e->getMessage(), array_merge($logContext, [
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
