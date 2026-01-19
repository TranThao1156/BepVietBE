<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Services\BlogService;

class BlogController extends Controller
{
    protected $blogService;
    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }
    // Thi - Lấy danh sách blog 
    public function layDSBlog(BlogService $blogService)
    {
        $data = $blogService->layDSBlog();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách blog mới thành công',
                'data' => $data
        ], 200);
    }
    // Thi - Chi tiết blog
    public function layChiTietBlog(int $id)
    {
        $data = $this->blogService->chiTietBlog($id);
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết blog thành công',
            'data' => $data
        ], 200);
    }
    // Thi - Dánh sách blog cá nhân của người dùng
    public function layDSBlogCaNhan(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập'
            ], 401);
        }
        $blogs = $this->blogService->layDSBlogCaNhan($user);
        return response()->json([
            'success' => true,
            'data' => $blogs
        ], 200);
    }
 
    // Thi - Thêm blog
    public function themBlog(Request $request)
    {
        $request->validate([
            'TieuDe'     => 'required|string|max:255',
            'ND_ChiTiet' => 'required|string',
            'HinhAnh'    => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'Ma_ND'      => 'required|integer'
        ], [
            'TieuDe.required' => 'Tiêu đề không được để trống',
            'ND_ChiTiet.required' => 'Nội dung không được để trống',
            'HinhAnh.required' => 'Vui lòng chọn ảnh',
        ]);

        $blog = $this->blogService->themBlog($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Blog đã được gửi và đang chờ duyệt',
            'data' => $blog
        ], 201);
    }
}
