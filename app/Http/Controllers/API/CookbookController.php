<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cookbook;
use Illuminate\Support\Facades\Validator;

class CookbookController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'TenCookBook' => 'required|string|max:255',
            'TrangThai'   => 'required|integer', // 1 hoặc 0
            'AnhBia'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Chỉ cho phép ảnh
            'Ma_ND'       => 'required' // Tạm thời bắt buộc gửi lên    
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Xử lý upload ảnh
        $tenAnh = null; // Mặc định nếu không up ảnh
        if ($request->hasFile('AnhBia')) {
            $file = $request->file('AnhBia');
            // Đặt tên file để tránh trùng: time_tenfilegoc
            $tenAnh = time() . '_' . $file->getClientOriginalName(); 
            // Lưu vào folder public/uploads/cookbooks
            $file->move(public_path('uploads/cookbooks'), $tenAnh);
        }

        // 3. Lưu vào Database
        try {
            $cookbook = Cookbook::create([
                'Ma_ND'       => $request->Ma_ND, // Lấy từ request (hoặc từ Auth user)
                'TenCookBook' => $request->TenCookBook,
                'TrangThai'   => $request->TrangThai,
                'AnhBia'      => $tenAnh // Lưu tên file vào DB (ví dụ: cookbook1.jpg)
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