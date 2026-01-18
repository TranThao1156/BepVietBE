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
    public function show($id)
    {
        try {
            $item = $this->danhMucService->getDetail($id);
            return response()->json([
                'success' => true,
                'data'    => $item
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Không tìm thấy danh mục'], 404);
        }
    }
    public function update(Request $request, $id)
    {
        $validTypes = 'Món ăn,Đồ uống,Làm bánh,Tráng miệng,Ăn vặt,Khác';

        // Validate dữ liệu cập nhật
        $request->validate([
            'TenDM'     => 'sometimes|required|string|max:255',
            'LoaiDM'    => 'sometimes|required|string|in:' . $validTypes,
            'TrangThai' => 'sometimes|integer|in:0,1'
        ]);

        try {
            $item = $this->danhMucService->update($id, $request->all());

            return response()->json([
                'message' => 'Cập nhật thành công',
                'data'    => $item
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi cập nhật: ' . $e->getMessage()], 500);
        }
    }
}