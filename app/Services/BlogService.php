<?php
namespace App\Services;
use App\Models\Blog;
// import Str helper
use Illuminate\Support\Str;
class BlogService
{
    // Thi 
    // Lấy tất cả danh sách blog 
public function layDSBlog()
{
    return Blog::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
        ->where('TrangThai', 1)
        ->where('TrangThaiDuyet', 'Chấp nhận')
        ->orderBy('created_at', 'desc')
        ->paginate(8)
        ->through(function ($blog) {
            return [
                'Ma_Blog' => $blog->Ma_Blog,
                'TieuDe' => $blog->TieuDe,
                'Slug'     => Str::slug($blog->TieuDe) . '-' . $blog->Ma_Blog,
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

    // Thi - Chi tiết blog
    public function chiTietBlog(int $maBlog)
    {
        $blog = Blog::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('TrangThai', 1)
            ->where('TrangThaiDuyet', 'Chấp nhận')
            ->findOrFail($maBlog);

        return [
            'Ma_Blog'   => $blog->Ma_Blog,
            'TieuDe'    => $blog->TieuDe,
            'Slug'      => Str::slug($blog->TieuDe) . '-' . $blog->Ma_Blog,
            'ND_ChiTiet'=> $blog->ND_ChiTiet, // chi tiết đầy đủ
            'HinhAnh'   => $blog->HinhAnh,
            'NgayDang'  => $blog->created_at,
            'TacGia'    => [
                'Ma_ND'      => $blog->nguoiDung->Ma_ND ?? null,
                'HoTen'      => $blog->nguoiDung->HoTen ?? '',
                'AnhDaiDien' => $blog->nguoiDung->AnhDaiDien ?? 'avatar.png'
            ]
        ];
    }
    
    // Thi Thêm Blog
    public function themBlog(array $duLieu)
    {
        // Upload ảnh
        $tenAnh = null;
        if (!empty($duLieu['HinhAnh'])) {
            $tenAnh = time() . '_' . $duLieu['HinhAnh']->getClientOriginalName();
            $duLieu['HinhAnh']->storeAs(
                'public/img/Blog',
                $tenAnh
            );
        }
        return Blog::create([
            'Ma_ND'          => $duLieu['Ma_ND'],
            'TieuDe'         => $duLieu['TieuDe'],
            'ND_ChiTiet'     => $duLieu['ND_ChiTiet'],
            'HinhAnh'        => $tenAnh,
            'TrangThai'      => 1,
            'TrangThaiDuyet' => 'Chờ duyệt' // Mặc định khi tạo mới
        ]);
    }
}