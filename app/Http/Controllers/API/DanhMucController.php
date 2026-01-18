<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DanhMucService;
use Illuminate\Http\Request;

class DanhMucController extends Controller
{
    protected $danhMucService;

    public function __construct(DanhMucService $danhMucService)
    {
        $this->danhMucService = $danhMucService;
    }

    // API: GET /api/quan-tri/danh-muc
    public function index(Request $request)
    {
        $result = $this->danhMucService->getList($request);
        return response()->json($result);
    }
    public function store(Request $request)
    {
        // 1. Validate dữ liệu: Bắt buộc phải có tên và loại
        $request->validate([
            'TenDM'  => 'required|string|max:255',
            'LoaiDM' => 'required|string|max:100',
            'TrangThai' => 'nullable|integer'
        ]);

        // 2. Gọi Service tạo mới
        $item = $this->danhMucService->create($request->all());

        // 3. Trả về kết quả
        return response()->json([
            'message' => 'Tạo danh mục thành công',
            'data'    => $item
        ], 201);
    }
    public function destroy($id)
    {
        // Gọi hàm delete bên Service (Hàm mà chúng ta vừa sửa thành update TrangThai = 0)
        $this->danhMucService->delete($id);
        
        return response()->json([
            'message' => 'Đã chuyển danh mục sang trạng thái Ẩn thành công'
        ]);
    }
}