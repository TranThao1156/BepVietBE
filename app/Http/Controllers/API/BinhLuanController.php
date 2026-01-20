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

    //Khanh - Lưu bình luận (Hoặc trả lời)
    public function luuBinhLuan(Request $request)
    {
        $request->validate([
            'Ma_CT' => 'required|integer',
            'NoiDungBL' => 'required|string',
            'parent_id' => 'nullable|integer|exists:binhluan,Ma_BL'
        ]);

        try {
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

    //Khanh - Sửa bình luận
    public function suaBinhLuan(Request $request, $id)
    {
        $request->validate(['NoiDungBL' => 'required|string']);

        try {
            $binhLuan = $this->binhLuanService->suaBinhLuan($id, $request->NoiDungBL);
            return response()->json(['status' => true, 'message' => 'Cập nhật thành công', 'data' => $binhLuan], 200);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json(['status' => false, 'message' => $e->getMessage()], $code);
        }
    }

    //Khanh - Xóa bình luận
    public function xoaBinhLuan($id)
    {
        try {
            $this->binhLuanService->xoaBinhLuan($id);
            return response()->json(['status' => true, 'message' => 'Đã xóa bình luận'], 200);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return response()->json(['status' => false, 'message' => $e->getMessage()], $code);
        }
    }
    // Khanh - Lấy danh sách bình luận theo công thức
    public function layBinhLuanTheoCongThuc($maCT)
    {
        try {
            $comments = $this->binhLuanService->layDanhSachBinhLuan($maCT);

            return response()->json([
                'status' => true,
                'data' => $comments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi tải bình luận: ' . $e->getMessage()
            ], 500);
        }
    }
}
