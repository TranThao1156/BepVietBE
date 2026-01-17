<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cookbook;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // Thêm dòng này để dùng Auth

class CookbookController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate dữ liệu
        // LƯU Ý: Đã bỏ dòng check 'Ma_ND' vì server tự biết ai đang đăng nhập
        $validator = Validator::make($request->all(), [
            'TenCookBook' => 'required|string|max:255',
            'TrangThai'   => 'required|integer|in:0,1', // Thêm in:0,1 để chặt chẽ hơn
            'AnhBia'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Lấy ID người dùng từ Token (QUAN TRỌNG)
        // auth()->id() sẽ lấy khóa chính của user đang đăng nhập
        $userId = auth('sanctum')->id();
        
        // Kiểm tra an toàn: Nếu chưa đăng nhập (dù middleware đã chặn, nhưng cứ check cho chắc)
        if (!$userId) {
             return response()->json(['message' => 'Không xác định được người dùng'], 401);
        }

        // 3. Xử lý upload ảnh
        $tenAnh = null; 
        if ($request->hasFile('AnhBia')) {
            $file = $request->file('AnhBia');
            $tenAnh = time() . '_' . $file->getClientOriginalName(); 
            $file->move(public_path('uploads/cookbooks'), $tenAnh);
        }

        // 4. Lưu vào Database
        try {
            $cookbook = Cookbook::create([
                'Ma_ND'       => $userId, // Dùng biến $userId vừa lấy từ Token
                'TenCookBook' => $request->TenCookBook,
                'TrangThai'   => $request->TrangThai,
                'AnhBia'      => $tenAnh 
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Tạo Cookbook thành công',
                'data' => $cookbook
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
    }
}