<?php
namespace App\Services;
use App\Models\Blog;
// import Str helper
use Illuminate\Support\Str;
class BlogService
{
    // Thi 
    // Lấy danh sách blog mới nhất (6 bài mới nhất)
    public function layDSBlogMoi()
    {
        $blogs = Blog::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('TrangThai', 1)
            -> where('TrangThaiDuyet', "Chấp nhận")
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();
        // Định dạng dữ liệu trả về
        return $blogs-> map(function ($blog) {
            return [
                'Ma_Blog' => $blog->Ma_Blog,
                'TieuDe' => $blog->TieuDe,
                // Cột mô tả ngắn, lấy 120 ký tự đầu tiên từ nội dung chi tiết
                'MoTaNgan' => Str::limit(strip_tags($blog->ND_ChiTiet), 120),
                'HinhAnh' => $blog->HinhAnh,
                'NgayDang' => $blog->created_at,

                'TacGia' => [
                    'Ma_ND' => $blog->nguoiDung->Ma_ND ?? null,
                    'HoTen' => $blog->nguoiDung->HoTen ?? '',
                    'AnhDaiDien' => $blog->nguoiDung->AnhDaiDien ?? 'avatar.png'
                ]
            ];
        });
    }
    
}