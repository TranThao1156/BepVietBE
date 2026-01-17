<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DoiMatKhauService; // <--- Gọi DoiMatKhauService 
use Exception;

class DoiMatKhauController extends Controller
{
    protected $doiMatKhauService;

    // Trâm - Inject Service vào Controller
    public function __construct(DoiMatKhauService $doiMatKhauService)
    {
        $this->doiMatKhauService = $doiMatKhauService;
    }

    public function doiMatKhau(Request $request)
    {
        // 1. Validate (Giữ nguyên như cũ)
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|regex:/[0-9]/|regex:/[@$!%*#?&]/|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required'     => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min'          => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.regex'        => 'Mật khẩu phải chứa ít nhất 1 số và 1 ký tự đặc biệt.',
            'new_password.confirmed'    => 'Mật khẩu nhập lại không khớp.',
        ]);

        try {
            $user = $request->user();
            
            // 2. Gọi Service rồi truyền tham số vào
            $this->doiMatKhauService->xuLyDoiMatKhau(
                $user, 
                $request->current_password, 
                $request->new_password
            );

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công!',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}