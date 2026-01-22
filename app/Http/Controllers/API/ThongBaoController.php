<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ThongBao;
use Illuminate\Support\Facades\Auth;

class ThongBaoController extends Controller
{
    // 1. Lấy danh sách thông báo của người đang đăng nhập
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Debug chi tiết
            \Illuminate\Support\Facades\Log::info('ThongBao@index called', [
                'ip'              => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'auth_header'     => $request->header('Authorization'),
                'user_exists'     => $user !== null,
                'user_class'      => $user ? get_class($user) : 'null',
                'user_id'         => $user ? $user->getKey() : 'null',
                'Ma_ND_exists'    => $user && property_exists($user, 'Ma_ND'),
            ]);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $thongBao = ThongBao::where('Ma_ND', $user->getKey())
                ->orderBy('Ma_TB', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $thongBao
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi ThongBao@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi server nội bộ: ' . $e->getMessage()
            ], 500);
        }
    }

    // 2. Đánh dấu đã đọc (Khi người dùng bấm vào thông báo)
    public function danhDauDaDoc($id, Request $request)
    {
        $user = $request->user();

        // Tìm thông báo và đảm bảo nó thuộc về user này
        $thongBao = ThongBao::where('Ma_TB', $id)
            ->where('Ma_ND', $user->getKey()) // Bảo mật: không cho đọc dùm người khác
            ->first();

        if ($thongBao) {
            $thongBao->TrangThai = 1; // 1 = Đã đọc
            $thongBao->save();
            return response()->json(['success' => true, 'message' => 'Đã đánh dấu đã đọc']);
        }

        return response()->json(['success' => false, 'message' => 'Không tìm thấy thông báo'], 404);
    }

    // 3. Đánh dấu đọc tất cả (Nút "Đánh dấu tất cả là đã đọc")
    public function docTatCa(Request $request)
    {
        $user = $request->user();

        ThongBao::where('Ma_ND', $user->getKey())
            ->where('TrangThai', 0)
            ->update(['TrangThai' => 1]);

        return response()->json(['success' => true, 'message' => 'Đã đọc tất cả']);
    }
}