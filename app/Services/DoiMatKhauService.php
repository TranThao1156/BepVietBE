<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Exception;

class DoiMatKhauService
{
    // Trâm - Logic đổi mật khẩu nằm riêng ở đây
    public function xuLyDoiMatKhau($user, $matKhauHienTai, $matKhauMoi)
    {
        // 1. Check pass cũ (So sánh với cột MatKhau trong DB)
        if (!Hash::check($matKhauHienTai, $user->MatKhau)) {
            throw new Exception("Mật khẩu hiện tại không chính xác.");
        }

        // 2. Check trùng (Optional): Mật khẩu mới không được trùng cũ
        if (Hash::check($matKhauMoi, $user->MatKhau)) {
            throw new Exception("Mật khẩu mới không được trùng với mật khẩu cũ.");
        }

        // 3. Update
        $user->MatKhau = Hash::make($matKhauMoi);
        $user->save();

        return true;
    }
}