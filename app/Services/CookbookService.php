<?php

namespace App\Services;

use App\Models\Cookbook;
use Illuminate\Support\Str; // Import Str để xử lý chuỗi

class CookbookService
{
    /**
     * Lấy danh sách Cookbook của một User cụ thể
     */
    public function layDanhSachTheoUser($userId)
    {
        // 1. Truy vấn Database
            $cookbooks = Cookbook::where('Ma_ND', $userId)
                                ->orderBy('Ma_CookBook', 'desc')
                                ->get();

        // 2. Xử lý dữ liệu (Mapping)
        // Logic này chuyển từ Controller sang đây để Controller chỉ lo việc nhận/trả request
        return $cookbooks->map(function ($cb) {
            
            $anhBia = $cb->AnhBia;
            if ($anhBia && !str_starts_with($anhBia, 'http')) {
                // 👇 THAY ĐỔI: Thêm dấu / sau cookbooks để đúng đường dẫn
                $anhBia = url('uploads/cookbooks/' . $anhBia);
            }

            return [
                'id'            => $cb->Ma_CookBook,
                'TenCookBook'   => $cb->TenCookBook,
                'AnhBia'        => $anhBia ?: 'https://placehold.co/600x400?text=No+Image', // Ảnh mặc định nếu null
                'TrangThai'     => $cb->TrangThai,
                'SoLuongMon'    => 0, // Sau này count quan hệ ở đây
                'NgayTao'       => $cb->created_at ? $cb->created_at->format('d/m/Y') : 'Chưa cập nhật'
            ];
        });
    }
}