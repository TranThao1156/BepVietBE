<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NguoiDung;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = NguoiDung::where('TenTK', $request->TenTK)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại'
            ], 401);
        }

        if (!Hash::check($request->MatKhau, $user->MatKhau)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu sai'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'user' => $user
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
            'user' => $user
        ]);
    }
}
