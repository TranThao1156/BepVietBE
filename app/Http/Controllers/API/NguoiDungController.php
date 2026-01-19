<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\NguoiDungService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NguoiDungController extends Controller
{
    protected $nguoiDungService;

    public function __construct(NguoiDungService $nguoiDungService)
    {
        $this->nguoiDungService = $nguoiDungService;
    }

    // Thảo - Lấy thông tin người dùng
    public function layThongTinCaNhan(Request $request)
    {
        $user = $request->user(); // Lấy user từ Token

        if (!$user) {
            return response()->json(['message' => 'Chưa đăng nhập'], 401);
        }

        // Gọi service (hoặc trả về trực tiếp $user cũng được, nhưng qua service để thống nhất)
        $data = $this->nguoiDungService->layThongTinChiTiet($user->Ma_ND);

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin thành công',
            'data' => $data
        ]);
    }

    // Thảo - Cập nhật thông tin cá nhân
    public function capNhatHoSo(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Chưa đăng nhập'], 401);
        }

        // Validate dữ liệu
        $request->validate([
            'HoTen'      => 'required|string|max:255',
            // Email phải unique trong bảng nguoidung, trừ Ma_ND hiện tại ra
            'Email'      => [
                'required', 
                'email', 
                Rule::unique('nguoidung', 'Email')->ignore($user->Ma_ND, 'Ma_ND')
            ],
            'Sdt'        => 'nullable|string|max:15',
            'DiaChi'     => 'nullable|string|max:255',
            'GioiTinh'   => 'nullable|string|in:Nam,Nữ,Khác',
            'QuocTich'   => 'nullable|string|max:100',
            'AnhDaiDien' => 'nullable|image|max:5120', // Max 5MB
        ], [
            'Email.unique' => 'Email này đã được sử dụng bởi tài khoản khác.',
            'AnhDaiDien.image' => 'File tải lên phải là hình ảnh.',
            'AnhDaiDien.max' => 'Ảnh không được vượt quá 5MB.'
        ]);

        try {
            $updatedUser = $this->nguoiDungService->capNhatThongTin($request, $user->Ma_ND);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hồ sơ thành công',
                'data' => $updatedUser
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }
}
