<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NguoiDung;
use Illuminate\Support\Facades\Hash;
//Khanh - Xử lý đăng nhập, đăng ký, đăng xuất API
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Tìm user theo tên tài khoản
        $user = NguoiDung::where('TenTK', $request->TenTK)->first();

        // 2. Kiểm tra user tồn tại
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại'
            ], 401);
        }

        // 3. Kiểm tra mật khẩu
        if (!Hash::check($request->MatKhau, $user->MatKhau)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu sai'
            ], 401);
        }

        // 4. Tạo Token (QUAN TRỌNG: Để dùng cho Middleware auth:sanctum)
        $token = $user->createToken('authToken')->plainTextToken;

        // 5. Trả về dữ liệu (Đã lọc bỏ mật khẩu)
        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'token' => $token, 
            'user' => [
                // Chỉ lấy những thông tin cần thiết
                'id' => $user->getKey(), // Lấy ID (dù là id hay Ma_ND)
                'TenTK' => $user->TenTK,
                'HoTen' => $user->HoTen,
                'Email' => $user->Email,
                'AnhDaiDien' => $user->AnhDaiDien,
                'VaiTro' => $user->VaiTro, // QUAN TRỌNG: React cần cái này để phân quyền
            ]
        ]);
    }

    public function register(Request $request)
    {
        if (NguoiDung::where('TenTK', $request->TenTK)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tên tài khoản đã tồn tại'
            ], 409);
        }

        $user = NguoiDung::create([
            'TenTK'     => $request->TenTK,
            'MatKhau'   => Hash::make($request->MatKhau),
            'HoTen'     => $request->HoTen,
            'Email'     => $request->Email,
            'Sdt'       => $request->Sdt,
            'DiaChi'    => $request->DiaChi,
            'GioiTinh'  => $request->GioiTinh,
            'QuocTich'  => $request->QuocTich,
            'VaiTro'    => 1, 
            'TrangThai' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công',
            
        ]);
    }
    public function logout(Request $request)
    {
        // Xóa token hiện tại mà người dùng đang dùng
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ], 200);
    }
}