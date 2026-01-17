<?php
namespace App\Services;
use App\Models\Blog;
class BlogService
{
    // Thi 
    // Lấy danh sách blog mới nhất (6 bài mới nhất)
    public function layDSBlogMoi()
    {
        return Blog::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('TrangThai', 1)
            -> where('TrangThaiDuyet', "Chấp nhận")
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();
    }
    
}