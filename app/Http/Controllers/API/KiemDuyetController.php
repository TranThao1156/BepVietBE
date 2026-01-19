<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KiemDuyetService;

class KiemDuyetController extends Controller
{
    protected $dichVu;

    public function __construct(KiemDuyetService $service)
    {
        $this->dichVu = $service;
    }

    public function layDanhSachBlog(Request $request)
    {
        $trangThai = $request->query('trang_thai', 'pending');
        $data = $this->dichVu->layDanhSachBlog($trangThai);

        return response()->json([
            'status' => 200,
            'data'   => $data
        ]);
    }

    public function xuLyDuyetBlog(Request $request)
    {
        $request->validate([
            'ma_blog'   => 'required',
            'hanh_dong' => 'required|in:approve,reject'
        ]);

        $ketQua = $this->dichVu->capNhatTrangThai(
            $request->input('ma_blog'), 
            $request->input('hanh_dong')
        );

        if (!$ketQua['thanh_cong']) {
            return response()->json(['message' => $ketQua['thong_bao']], 404);
        }

        return response()->json([
            'status'  => 200,
            'message' => $ketQua['thong_bao'],
            'data'    => $ketQua['du_lieu']
        ]);
    }
}