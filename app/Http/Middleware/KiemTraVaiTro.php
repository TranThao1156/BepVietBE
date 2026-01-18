<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KiemTraVaiTro
{
    // $role: 0 là Quản lý, 1 là Người dùng
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Chưa đăng nhập'], 401);
        }

        $vaiTro = (int) $user->VaiTro;
        $roles = array_map('intval', $roles);

        if (!in_array($vaiTro, $roles)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        return $next($request);
    }
    
}
