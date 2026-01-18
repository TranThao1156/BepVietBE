<?php


namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;

class KiemTraVaiTro
{
    // $role: 0 là Quản lý, 1 là Người dùng
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

        $vaiTro = (int) $user->VaiTro;
        $roles = array_map('intval', $roles);

        if (!in_array($vaiTro, $roles)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        return $next($request);
    }
    
}

