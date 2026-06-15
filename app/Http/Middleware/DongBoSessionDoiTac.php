<?php

namespace App\Http\Middleware;

use App\Http\Service\DoiTacService;
use Closure;
use Illuminate\Http\Request;

class DongBoSessionDoiTac
{
    public function handle(Request $request, Closure $next)
    {
        if (session('doi_tac_id')) {
            $nhanVien = DoiTacService::layNhanVienDoiTacHopLeTheoSession();

            if (!$nhanVien) {
                DoiTacService::xoaSessionDoiTac();
            } else {
                $request->attributes->set('doi_tac_nhan_vien_hop_le', true);
            }
        }

        return $next($request);
    }
}
