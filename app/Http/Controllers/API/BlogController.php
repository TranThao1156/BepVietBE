<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Services\BlogService;

class BlogController extends Controller
{
    //Thi
    // Lấy danh sách blog 
    public function layDSBlog(BlogService $blogService)
    {
        $data = $blogService->layDSBlog();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách blog mới thành công',
                'data' => $data
        ], 200);
    }
}
