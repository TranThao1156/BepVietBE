<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DanhGiaService; // Import Service
use Illuminate\Support\Facades\Validator;

class DanhGiaController extends Controller
{
    protected $danhGiaService;

    // Inject Service vào Controller
    public function __construct(DanhGiaService $service)
    {
        $this->danhGiaService = $service;
    }

    public function danhGia(Request $request)
    {
        // 1. Validate
        $validator = Validator::make($request->all(), [
            'Ma_CT' => 'required|exists:congthuc,Ma_CT', // Sửa 'id' thành 'Ma_CT' nếu khóa chính là Ma_CT
            'SoSao' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // 2. Gọi Service xử lý
            $result = $this->danhGiaService->xuLyDanhGia($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá thành công!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function layDanhGiaCuaToi($maCongThuc)
    {
        $danhGia = $this->danhGiaService->layDanhGiaCuaUser($maCongThuc);

        return response()->json([
            'rated' => $danhGia ? true : false,
            'so_sao' => $danhGia ? $danhGia->SoSao : 0
        ]);
    }
}