<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BinhLuanService;
class BinhLuanController extends Controller
{
    protected $binhLuanService;
    
    public function __construct(BinhLuanService $binhLuanService)
    {
        $this->binhLuanService = $binhLuanService;
    }

    public function luuBinhLuan(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'Ma_CT' => 'required|integer',
            'NoiDungBL' => 'required|string',
        ]);

        try {
            // Gọi service để xử lý logic
            $binhLuan = $this->binhLuanService->taoBinhLuanCongThuc($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Gửi bình luận thành công!',
                'data' => $binhLuan
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}
