<?php

namespace App\Services;

use App\Models\Cookbook;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CookbookService
{
    /**
     * Lấy danh sách Cookbook của User (Giữ nguyên)
     */
    public function layDanhSachTheoUser($userId)
    {
        $cookbooks = Cookbook::where('Ma_ND', $userId)->orderBy('Ma_CookBook', 'desc')->get();

        return $cookbooks->map(function ($cb) {
            $anhBia = $cb->AnhBia;
            if ($anhBia && !str_starts_with($anhBia, 'http')) {
                $anhBia = url('uploads/cookbooks/' . $anhBia);
            }
            return [
                'id'            => $cb->Ma_CookBook,
                'TenCookBook'   => $cb->TenCookBook,
                'AnhBia'        => $anhBia ?: 'https://placehold.co/600x400?text=No+Image',
                'TrangThai'     => $cb->TrangThai,
                'SoLuongMon'    => $cb->congthucs()->count(), // Đếm trực tiếp an toàn
                'NgayTao'       => $cb->created_at ? $cb->created_at->format('d/m/Y') : 'Chưa cập nhật'
            ];
        });
    }

    /**
     * Lấy chi tiết Cookbook (PHIÊN BẢN AN TOÀN - KHÔNG GỌI USER)
     */
    public function layChiTietCookbook($cookbookId)
    {
        // 1. Chỉ lấy Cookbook và danh sách Công thức (Bỏ qua NguoiDung để tránh lỗi)
        $cookbook = Cookbook::with('congthucs') 
                            ->where('Ma_CookBook', $cookbookId)
                            ->first();

        if (!$cookbook) return null;

        // 2. Xử lý ảnh bìa Cookbook
        $cookbookImg = $cookbook->AnhBia;
        if ($cookbookImg && !str_starts_with($cookbookImg, 'http')) {
            $cookbookImg = url('uploads/cookbooks/' . $cookbookImg);
        }

        // 3. Map danh sách công thức (CHỈ LẤY THÔNG TIN CƠ BẢN)
        $formattedRecipes = $cookbook->congthucs->map(function($ct) {
            
            // Xử lý ảnh món ăn
            $img = $ct->HinhAnh; 
            if ($img && !str_starts_with($img, 'http')) {
                $img = url('uploads/congthuc/' . $img); 
            }

            return [
                'Ma_CT'        => $ct->Ma_CT,
                'TenMon'       => $ct->TenMon,
                'HinhAnh'      => $img ?: 'https://placehold.co/600x400?text=No+Food+Img',
                'ThoiGianNau'  => $ct->ThoiGianNau ?? 0,
                // Gán cứng tác giả để test, sau này sửa sau
                'TacGia'       => 'Bếp Việt', 
                'AvatarTacGia' => 'https://placehold.co/100?text=U',
            ];
        });

        return [
            'info' => [
                'id'          => $cookbook->Ma_CookBook,
                'TenCookBook' => $cookbook->TenCookBook,
                'AnhBia'      => $cookbookImg ?: 'https://placehold.co/800x400?text=Cover',
                'TrangThai'   => $cookbook->TrangThai,
                'Ma_ND'       => $cookbook->Ma_ND,
                'SoLuongMon'  => $formattedRecipes->count()
            ],
            'recipes' => $formattedRecipes
        ];
    }
    
    // Hàm ẩn cookbook (giữ nguyên)
    public function anCookbook($cookbookId, $userId)
    {
        $cookbook = Cookbook::where('Ma_CookBook', $cookbookId)->where('Ma_ND', $userId)->first();
        if (!$cookbook) return false;
        $cookbook->TrangThai = 0;
        return $cookbook->save();
    }
    public function xoaMonKhoiCookbook($cookbookId, $recipeId, $userId)
    {
        // 1. Tìm Cookbook (Phải thuộc về User này để đảm bảo bảo mật)
        $cookbook = Cookbook::where('Ma_CookBook', $cookbookId)
                            ->where('Ma_ND', $userId)
                            ->first();

        if (!$cookbook) {
            return false; // Không tìm thấy hoặc không phải chủ sở hữu
        }

        // 2. Thực hiện gỡ bỏ (Detach)
        // Hàm detach sẽ xóa dòng liên kết trong bảng trung gian ct_cookbook
        $cookbook->congthucs()->detach($recipeId);

        return true;
    }
    public function capNhatCookbook($cookbookId, $data, $fileAnh, $userId)
    {
        // 1. Tìm Cookbook
        $cookbook = Cookbook::where('Ma_CookBook', $cookbookId)
                            ->where('Ma_ND', $userId)
                            ->first();

        if (!$cookbook) return null;

        // 2. Cập nhật tên
        if (isset($data['TenCookBook'])) {
            $cookbook->TenCookBook = $data['TenCookBook'];
        }

        // 3. Xử lý Ảnh Bìa (Nếu có gửi ảnh mới lên)
        if ($fileAnh) {
            // Xóa ảnh cũ nếu có (và không phải ảnh placeholder)
            if ($cookbook->AnhBia && file_exists(public_path('uploads/cookbooks/' . $cookbook->AnhBia))) {
                File::delete(public_path('uploads/cookbooks/' . $cookbook->AnhBia));
            }

            // Lưu ảnh mới
            $tenAnh = time() . '_' . $fileAnh->getClientOriginalName();
            $fileAnh->move(public_path('uploads/cookbooks'), $tenAnh);
            
            // Cập nhật tên ảnh vào DB
            $cookbook->AnhBia = $tenAnh;
        }

        $cookbook->save();

        return $cookbook;
    }
}