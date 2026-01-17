<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KiemTraVaiTro
{
    // 👇 THAY ĐỔI 1: Thêm dấu "..." trước $roles để nhận danh sách (mảng) các quyền
    // Lúc này 'role:1,0' sẽ biến thành mảng $roles = ['1', '0']
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Thử lấy user từ request, nếu không có thì thử lấy qua guard sanctum
        $user = $request->user() ?? auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Middleware Role: Không tìm thấy thông tin đăng nhập (User is Null)',
                'debug_token' => $request->bearerToken() ? 'Token có tồn tại' : 'Token trống'
            ], 401);
        }

        // Kiểm tra vai trò
        if (!in_array((string)$user->VaiTro, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập chức năng này'
            ], 403);
        }

        return $next($request);
    }
}
