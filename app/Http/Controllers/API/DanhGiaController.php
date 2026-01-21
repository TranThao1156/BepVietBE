<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DanhGiaService;
use App\Models\CongThuc; // Phải import Model này vào
use Illuminate\Support\Facades\Validator;

class DanhGiaController extends Controller
{
    protected $danhGiaService;

    public function __construct(DanhGiaService $service)
    {
        $this->danhGiaService = $service;
    }

    // API: Gửi đánh giá mới hoặc cập nhật
    public function danhGia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Ma_CT' => 'required|exists:congthuc,Ma_CT',
            'SoSao' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->danhGiaService->xuLyDanhGia($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Đánh giá thành công!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            // Trâm - đã thêm: trả đúng HTTP status (403/404/...) theo Exception code thay vì luôn 500
            $code = (int) $e->getCode();
            $status = ($code >= 400 && $code < 600) ? $code : 500;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $status);
        }
    }

    // API: Lấy đánh giá của chính người đang đăng nhập
    public function layDanhGiaCuaToi($maCongThuc)
    {
        try {
            $danhGia = $this->danhGiaService->layDanhGiaCuaUser($maCongThuc);
            return response()->json([
                'rated' => $danhGia ? true : false,
                'so_sao' => $danhGia ? $danhGia->SoSao : 0
            ]);
        } catch (\Exception $e) {
            // Trâm - đã thêm: trả đúng HTTP status (403/404/...) theo Exception code
            $code = (int) $e->getCode();
            $status = ($code >= 400 && $code < 600) ? $code : 500;
            return response()->json([
                'rated' => false,
                'so_sao' => 0,
                'message' => $e->getMessage()
            ], $status);
        }
    }

    // Trâm - đã sửa: API public lấy danh sách đánh giá của một công thức
    public function layDanhGia($maCongThuc)
    {
        try {
            $data = $this->danhGiaService->layDanhSachDanhGia($maCongThuc);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            // Trâm - đã thêm: trả đúng HTTP status (403/404/...) theo Exception code
            $code = (int) $e->getCode();
            $status = ($code >= 400 && $code < 600) ? $code : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $status);
        }
    }

    // API quan trọng: Lấy chi tiết công thức kèm DANH SÁCH NGƯỜI ĐÁNH GIÁ
    public function show($id) {
        $recipe = CongThuc::with([
            'nguyen_lieu', 
            'buoc_thuc_hien', 
            'danh_gia.nguoidung:id,HoTen,AnhDaiDien' // Chỉ lấy các cột cần thiết của User cho nhẹ
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $recipe
        ]);
    }
}