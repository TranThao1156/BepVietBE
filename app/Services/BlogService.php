<?php
namespace App\Services;
use App\Models\Blog;
use Illuminate\Support\Str;
class BlogService
{
    // Thi  -Lấy tất cả danh sách blog 
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
            $blog = Blog::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien,Email,GioiTinh'])
                ->where('TrangThai', 1)
                // ->where('TrangThaiDuyet', 'Chấp nhận') // Cho phép xem cả blog chờ duyệt dành cho tác giả
                ->findOrFail($maBlog);

            // Thi - Lấy Blog liên quan (cùng tác giả)
            $blogLienQuan = Blog::where('Ma_Blog', '!=', $maBlog)
                ->where('Ma_ND', $blog->Ma_ND)
                ->where('TrangThai', 1)
                ->where('TrangThaiDuyet', 'Chấp nhận')
                ->orderByDesc('created_at')
                ->limit(4)
                ->get()
                ->map(function ($item) {
                    return [
                        'Ma_Blog'  => $item->Ma_Blog,
                        'TieuDe'   => $item->TieuDe,
                        'Slug'     => Str::slug($item->TieuDe) . '-' . $item->Ma_Blog,
                        'HinhAnh'  => $item->HinhAnh,
                        'NgayDang' => $item->created_at
                    ];
                });
            // Lấy thông tin bình luận
            $binhLuan = $blog->binhLuan->map(function ($item) {
                return [
                    'Ma_BL'     => $item->Ma_BL,
                    'NoiDungBL' => $item->NoiDungBL,
                    'LoaiBL'    => $item->LoaiBL,
                    'NgayBL'    => $item->created_at,
                    'NguoiDung' => [
                        'Ma_ND'      => $item->nguoiDung->Ma_ND ?? null,
                        'HoTen'      => $item->nguoiDung->HoTen ?? '',
                        'AnhDaiDien' => $item->nguoiDung->AnhDaiDien ?? 'avatar.png',
                    ]
                ];
            });
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
                    'AnhDaiDien' => $blog->nguoiDung->AnhDaiDien ?? 'avatar.png',
                    'Email'      => $blog->nguoiDung->Email ?? '',
                    'GioiTinh'   => $blog->nguoiDung->GioiTinh ?? '',
                ],
                'SoBinhLuan' => $blog->binhLuan->count(),
                // Gắn thêm blog liên quan
                'BlogLienQuan' => $blogLienQuan,
                // Gắn thêm bình luận
                'BinhLuan'     => $binhLuan
            ];
        }
    //Thi - Lấy danh sách blog của người dùng
    public function layDSBlogCaNhan($user)
    {
        return Blog::where('Ma_ND', $user->Ma_ND)
            ->where('TrangThai', 1)
            ->select(
                'Ma_Blog',
                'TieuDe',
                'ND_ChiTiet',
                'HinhAnh',
                'created_at',
                'TrangThaiDuyet',
            )
            ->withCount('binhLuan')
            ->orderByDesc('created_at')
            ->get();
    }


    // Thi - Thêm Blog
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