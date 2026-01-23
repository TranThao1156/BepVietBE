<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BinhLuanBlogService;
use Illuminate\Support\Facades\Validator;

class BinhLuanBlogController extends Controller
{
    protected $service;

    public function __construct(BinhLuanBlogService $service)
    {
        $this->service = $service;
    }

    // API Thêm
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Ma_Blog'   => 'required|integer',
            'NoiDungBL' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $this->service->themBinhLuan($request->all());
            return response()->json(['success' => true, 'message' => 'Đã gửi bình luận', 'data' => $data]);
        } catch (\Exception $e) {
            // Trâm - đã thêm: trả đúng HTTP status (403/404/...) theo Exception code thay vì luôn 500
            $code = (int) $e->getCode();
            $status = ($code >= 400 && $code < 600) ? $code : 500;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $status);
        }
    }

    // API Sửa
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'NoiDungBL' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $this->service->suaBinhLuan($id, $request->NoiDungBL);
            return response()->json(['success' => true, 'message' => 'Đã sửa bình luận', 'data' => $data]);
        } catch (\Exception $e) {
            // Trâm - đã thêm: trả đúng HTTP status (403/404/...) theo Exception code thay vì luôn 500
            $code = (int) $e->getCode();
            $status = ($code >= 400 && $code < 600) ? $code : 500;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $status);
        }
    }

    // API Xóa
    public function destroy($id)
    {
        try {
            $this->service->xoaBinhLuan($id);
            return response()->json(['success' => true, 'message' => 'Đã xóa bình luận']);  
        } catch (\Exception $e) {
            // Trâm - đã thêm: trả đúng HTTP status (403/404/...) theo Exception code thay vì luôn 500
            $code = (int) $e->getCode();
            $status = ($code >= 400 && $code < 600) ? $code : 500;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $status);
        }
    }

    // API Lấy danh sách bình luận của 1 bài Blog
    public function index($maBlog)
    {
        try {
            // Gọi hàm từ BinhLuanBlogService
            $data = $this->service->layDanhSachBinhLuan($maBlog);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            // Trâm - đã thêm: trả đúng HTTP status (403/404/...) theo Exception code thay vì luôn 500
            $code = (int) $e->getCode();
            $status = ($code >= 400 && $code < 600) ? $code : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $status);
        }
    }
    
}
