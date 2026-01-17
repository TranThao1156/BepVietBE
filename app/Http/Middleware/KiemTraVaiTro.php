<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class KiemTraVaiTro
{
    // $role: 0 là Quản lý, 1 là Người dùng
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Chưa đăng nhập'], 401);
        }

        // So sánh cột VaiTro trong DB
        if ((string)$user->VaiTro !== (string)$role) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        return $next($request);
    }
}