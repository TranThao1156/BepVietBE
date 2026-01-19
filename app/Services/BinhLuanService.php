<?php

namespace App\Services;

use App\Models\BinhLuan;
use Illuminate\Support\Facades\Auth;

class BinhLuanService
{
    /**
     * Tạo bình luận mới cho công thức
     */
    public function taoBinhLuanCongThuc($data)
    {
        // Lấy ID người dùng đang đăng nhập
        $userId = Auth::user()->Ma_ND;

        return BinhLuan::create([
            'Ma_CT'     => $data['Ma_CT'], 
            'Ma_ND'     => $userId,        
            'NoiDungBL' => $data['NoiDungBL'],
            'LoaiBL'    => 1,
            'TrangThai' => 1,
            'Ma_Blog'   => null,
        ]);
    }
}