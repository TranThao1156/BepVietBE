<?php
namespace App\Services;
use App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;


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
                    // 'HinhAnh'  => $item->HinhAnh,
                    'HinhAnh' => $item->HinhAnh
                    ? asset('storage/img/Blog/' . rawurlencode($item->HinhAnh))
                    : null,
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
            // 'HinhAnh'   => $blog->HinhAnh,
            'HinhAnh' => $blog->HinhAnh
                ? asset('storage/img/Blog/' . rawurlencode($blog->HinhAnh))
                : null,
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
            ->get()
            ->map(function ($blog) {
        return [
            'Ma_Blog' => $blog->Ma_Blog,
            'TieuDe'  => $blog->TieuDe,
            'ND_ChiTiet' => $blog->ND_ChiTiet,
            'TrangThaiDuyet' => $blog->TrangThaiDuyet,
            'created_at' => $blog->created_at,
            'binh_luan_count' => $blog->binh_luan_count,

            //Lấy đường dẫn ảnh đầy đủ
            'HinhAnh' => $blog->HinhAnh
                ? asset('storage/img/Blog/' . rawurlencode($blog->HinhAnh))
                : null,
            ];
        });
    }
    
    // Thi - Thêm Blog
    public function themBlog(Request $request, $user)
    {
        return DB::transaction(function () use ($request, $user) {

        // Upload ảnh
            $tenAnh = null;

            if ($request->hasFile('HinhAnh')) {
                $file = $request->file('HinhAnh');

                // tạo tên ảnh an toàn
                $tenAnh = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                // LƯU ẢNH
                $file->storeAs('img/Blog', $tenAnh, 'public');
            }

            return Blog::create([
                'Ma_ND'          => $user->Ma_ND,
                'TieuDe'         => $request->TieuDe,
                'ND_ChiTiet'     => $request->ND_ChiTiet,
                'HinhAnh'        => $tenAnh, // CHỈ LƯU TÊN ẢNH
                'TrangThai'      => 1,
                'TrangThaiDuyet' => 'Chờ duyệt'
            ]);
        });
    }
    
    // Thi - Xoá blog cá nhân (xoá mềm) đổi trạng thái 
    public function xoaBlogCaNhan($blogId, $user)
    {
        $blog = Blog::where('Ma_Blog', $blogId)
            ->where('Ma_ND', $user->Ma_ND) // đảm bảo đúng chủ blog
            ->where('TrangThai', 1)        // chỉ xoá blog đang hoạt động
            ->first();

        if (!$blog) {
            throw new ModelNotFoundException('Blog không tồn tại hoặc bạn không có quyền xoá');
        }
        // Xoá mềm
        $blog->TrangThai = 0;
        $blog->save();

        return [
            'Ma_Blog' => $blog->Ma_Blog,
            'TrangThai' => $blog->TrangThai
        ];
    }

    // Thi - Lấy chi tiết blog cá nhân để sửa
    public function layBlogDeSua($blogId, $user)
    {
        // 1. Blog có tồn tại không (kể cả đã xoá)
        $blog = Blog::where('Ma_Blog', $blogId)->first();

        if (!$blog) {
            throw new ModelNotFoundException('Blog không tồn tại');
        }

        // 2. Không phải blog của user
        if ($blog->Ma_ND !== $user->Ma_ND) {
            throw new AuthorizationException('Bạn không có quyền sửa blog này');
        }

        // 3. Blog đã bị xoá mềm
        if ($blog->TrangThai != 1) {
            throw new AuthorizationException('Blog này đã bị xoá, không thể chỉnh sửa');
        }

        // 4. OK
        return [
            'Ma_Blog'    => $blog->Ma_Blog,
            'TieuDe'     => $blog->TieuDe,
            'ND_ChiTiet' => $blog->ND_ChiTiet,
            'HinhAnh'    => $blog->HinhAnh
                ? asset('storage/img/Blog/' . rawurlencode($blog->HinhAnh))
                : null,
            'TrangThaiDuyet' => $blog->TrangThaiDuyet
        ];
    }
    // Thi - Cập nhật blog cá nhân
    public function capNhatBlog($blogId, Request $request, $user)
    {
        return DB::transaction(function () use ($blogId, $request, $user) {

            $blog = Blog::where('Ma_Blog', $blogId)
                ->where('Ma_ND', $user->Ma_ND)
                ->where('TrangThai', 1)
                ->first();

            if (!$blog) {
                throw new ModelNotFoundException('Blog không tồn tại hoặc bạn không có quyền sửa');
            }

            // Upload ảnh mới (nếu có)
            if ($request->hasFile('HinhAnh')) {
                $file = $request->file('HinhAnh');
                $tenAnh = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->storeAs('img/Blog', $tenAnh, 'public');

                $blog->HinhAnh = $tenAnh;
            }

            $blog->TieuDe     = $request->TieuDe;
            $blog->ND_ChiTiet = $request->ND_ChiTiet;

            // Khi sửa → chuyển về chờ duyệt lại (nếu muốn)
            $blog->TrangThaiDuyet = 'Chờ duyệt';

            $blog->save();

            return $blog;
        });
    }

}