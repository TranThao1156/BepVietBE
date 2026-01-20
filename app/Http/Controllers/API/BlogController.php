<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Services\BlogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BlogController extends Controller
{
    protected $blogService;
    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }
    // Thi - Lấy danh sách blog 
    public function layDSBlog()
    {
        $data = $this->blogService->layDSBlog();

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
        // Validate dữ liệu
        $request->validate([
            'TieuDe'     => 'required|string|max:255',
            'ND_ChiTiet' => 'required|string',
            'HinhAnh'    => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'TieuDe.required' => 'Tiêu đề không được để trống',
            'ND_ChiTiet.required' => 'Nội dung không được để trống',
            'HinhAnh.required' => 'Vui lòng chọn ảnh',
            'HinhAnh.image' => 'File phải là ảnh',
        ]);

        // Lấy user từ token
        $user = auth()->user();
        // Nếu chưa đăng nhập
        if (!$user) {
            return response()->json([
                'message' => 'Chưa đăng nhập'
            ], 401);
        }
        
        $blog = $this->blogService->themBlog($request, $user);

        return response()->json([
            'message' => 'Tạo blog thành công, đang chờ duyệt',
            'data' => $blog
        ], 201);
    }

    // Thi - Xoá blog cá nhân (xoá mềm)
    public function xoaBlogCaNhan(int $maBlog, Request $request)
    {
        try {

            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập'
                ], 401);
            }

            $result = $this->blogService->xoaBlogCaNhan($maBlog, $user);

            return response()->json([
                'success' => true,
                'message' => 'Xoá blog thành công',
                'data' => $result
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    // Thi - Cập nhật blog cá nhân
    public function capNhatBlog(int $id, Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập'
            ], 401);
        }

        // Validate
        $request->validate([
            'TieuDe'     => 'required|string|max:255',
            'ND_ChiTiet' => 'required|string',
            'HinhAnh'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $blog = $this->blogService->capNhatBlog($id, $request, $user);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật blog thành công',
            'data' => $blog
        ], 200);
    }

    // Thi - Lấy chi tiết blog cá nhân để sửa
    public function layBlogDeSua($id, Request $request)
    {
        try {
            $user = $request->user();

            $data = $this->blogService->layBlogDeSua($id, $user);

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
            
        } 
            // 1. Blog có tồn tại không (kể cả đã xoá)
            catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);

        } 
          // 2. Không phải blog của user 
            catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

}
