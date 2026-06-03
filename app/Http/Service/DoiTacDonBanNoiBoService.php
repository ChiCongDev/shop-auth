<?php

namespace App\Http\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DoiTacDonBanNoiBoService
{
    private array $hanhDongDuocPhep = [
        'duyet',
        'bao-hang-ve',
        'lay-hang-trong-kho',
        'xuat-kho',
        'dong-goi',
        'van-chuyen',
        'tu-van-chuyen-ntq',
        'hoan-thanh',
    ];

    public function guiThaoTac(int $donHangId, int $nhanVienId, string $hanhDong, array $payload = []): array
    {
        if (!in_array($hanhDong, $this->hanhDongDuocPhep, true)) {
            return [
                'success' => false,
                'message' => 'Thao tác không hợp lệ.',
                'status' => 422,
            ];
        }

        return $this->guiPost('/api/noi-bo/don-order/' . $donHangId . '/' . $hanhDong, array_merge($payload, [
            'nhan_vien_id' => $nhanVienId,
            'nguon' => 'shop_auth_doi_tac',
        ]), [
            'don_hang_id' => $donHangId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => $hanhDong,
        ]);
    }

    public function kiemTraLayHangTrongKho(int $donHangId, int $nhanVienId): array
    {
        return $this->guiGet('/api/noi-bo/don-order/' . $donHangId . '/lay-hang-trong-kho', [
            'nhan_vien_id' => $nhanVienId,
            'nguon' => 'shop_auth_doi_tac',
        ], [
            'don_hang_id' => $donHangId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'kiem_tra_lay_hang_trong_kho',
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
                'message' => 'ChÆ°a cáº¥u hÃ¬nh káº¿t ná»‘i API ná»™i bá»™ vá»›i há»‡ thá»‘ng sell.',
                'status' => 503,
            ];
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.sell_internal.timeout', 10))
                ->withToken($token)
                ->get($baseUrl . $path, $query);

            $data = $response->json();

            if (!is_array($data)) {
                return [
                    'success' => false,
                    'message' => 'API ná»™i bá»™ sell tráº£ vá» dá»¯ liá»‡u khÃ´ng há»£p lá»‡.',
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => (bool) ($data['success'] ?? $data['thanh_cong'] ?? $response->successful()),
                'message' => (string) ($data['message'] ?? $data['thong_bao'] ?? 'ÄÃ£ gá»­i thao tÃ¡c sang há»‡ thá»‘ng sell.'),
                'data' => $data['data'] ?? $data['du_lieu'] ?? null,
                'status' => $response->status(),
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Lá»—i gá»i API ná»™i bá»™ sell tá»« khu Ä‘á»‘i tÃ¡c: ' . $e->getMessage(), array_merge($logContext, [
                'path' => $path,
            ]));

            return [
                'success' => false,
                'message' => 'KhÃ´ng káº¿t ná»‘i Ä‘Æ°á»£c API ná»™i bá»™ sell.',
                'status' => 503,
            ];
        }
    }
}
