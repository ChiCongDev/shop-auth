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

    public function guiThaoTac(int $donHangId, int $nhanVienId, string $hanhDong): array
    {
        return $this->guiPost('/api/noi-bo/don-hang/' . $donHangId . '/' . $hanhDong, [
            'nhan_vien_id' => $nhanVienId,
            'nguon' => 'shop_auth_doi_tac',
        ], [
            'don_hang_id' => $donHangId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => $hanhDong,
        ]);
    }

    public function laySoLuongDaTra(int $donHangId, int $nhanVienId): array
    {
        return $this->guiGet('/api/noi-bo/phieu-tra-hang/so-luong-da-tra/' . $donHangId, [
            'nhan_vien_id' => $nhanVienId,
            'loai_don' => 'thuong',
        ], [
            'don_hang_id' => $donHangId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'lay_so_luong_da_tra_don_thuong',
        ]);
    }

    public function taoPhieuTra(int $nhanVienId, array $payload): array
    {
        return $this->guiPost('/api/noi-bo/phieu-tra-hang/tao', array_merge($payload, [
            'nhan_vien_id' => $nhanVienId,
            'loai_don' => 'thuong',
            'nguon' => 'shop_auth_doi_tac',
        ]), [
            'don_hang_id' => $payload['don_hang_id'] ?? null,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'tao_phieu_tra_don_thuong',
        ]);
    }

    public function layDanhSachPhieuTra(int $nhanVienId, array $boLoc = []): array
    {
        return $this->guiGet('/api/noi-bo/phieu-tra-hang/danh-sach', array_merge($boLoc, [
            'nhan_vien_id' => $nhanVienId,
            'loai_don' => 'thuong',
        ]), [
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'lay_danh_sach_phieu_tra_don_thuong',
        ]);
    }

    public function layChiTietPhieuTra(int $phieuTraHangId, int $nhanVienId): array
    {
        return $this->guiGet('/api/noi-bo/phieu-tra-hang/' . $phieuTraHangId, [
            'nhan_vien_id' => $nhanVienId,
            'loai_don' => 'thuong',
        ], [
            'phieu_tra_hang_id' => $phieuTraHangId,
            'nhan_vien_id' => $nhanVienId,
            'hanh_dong' => 'lay_chi_tiet_phieu_tra_don_thuong',
        ]);
    }

    private function guiGet(string $path, array $query = [], array $logContext = []): array
    {
        return $this->guiRequest('get', $path, $query, $logContext);
    }

    private function guiPost(string $path, array $payload = [], array $logContext = []): array
    {
        return $this->guiRequest('post', $path, $payload, $logContext);
    }

    private function guiRequest(string $method, string $path, array $data = [], array $logContext = []): array
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
            $request = Http::acceptJson()
                ->asJson()
                ->timeout((int) config('services.sell_internal.timeout', 10))
                ->withToken($token);

            $response = $method === 'post'
                ? $request->post($baseUrl . $path, $data)
                : $request->get($baseUrl . $path, $data);

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
