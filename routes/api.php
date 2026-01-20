<?php

use App\Http\Controllers\API\AIChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CongThucController;


use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CookbookController;
use App\Http\Controllers\API\DanhMucController;
use App\Http\Controllers\API\QuanLyController;
use App\Http\Controllers\API\NguoiDungController;
use App\Http\Controllers\API\KhachController;
use App\Http\Controllers\API\KiemDuyetController;
use App\Http\Controllers\API\BlogController;

// 1. PUBLIC ROUTES (KHÔNG CẦN ĐĂNG NHẬP)

// --- Xác thực ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// 16/01/2026 - Thi - công thức cho trang chủ
Route::get('/cong-thuc/mon-moi', [CongThucController::class, 'layDSCongThucMoi']);

Route::get('/cong-thuc/mon-noi-bat', [CongThucController::class, 'layDSCongThucNoiBat']);

Route::get('/cong-thuc/mien-noi-bat/{mien}', [CongThucController::class, 'layCongThucNoiBatTheoMien']);

// Thảo - danh sách công thức
Route::get('/cong-thuc', [CongThucController::class, 'index']);

// Thảo - Chi tiết công thức
Route::get('/cong-thuc/{id}', [CongThucController::class, 'show']);

Route::get('/tuy-chon-cong-thuc', [CongThucController::class, 'layTuyChon']);

Route::get('/nguyen-lieu/goi-y', [CongThucController::class, 'goiYNguyenLieu']);

Route::post('/upload-anh-buoc', [CongThucController::class, 'uploadAnhBuoc']);

Route::post('/ai-chat', [AIChatController::class, 'chat']);

// Thi - Danh sách Blog
Route::get('/blog', [BlogController::class, 'layDSBlog']);
// Thi - Chi tiết Blog
Route::get('/blog/{id}', [BlogController::class, 'layChiTietBlog']);


// 2. PROTECTED ROUTES (YÊU CẦU ĐĂNG NHẬP - Token)

Route::middleware('auth:sanctum')->group(function () {

    // A. NHÓM API ADMIN (Chỉ VaiTro = 0 mới gọi được)
    Route::prefix('admin')->middleware('role:0')->group(function () {

        Route::get('danh-muc', [DanhMucController::class, 'index']);

        Route::post('danh-muc/tao-danh-muc', [DanhMucController::class, 'store']);

        Route::delete('danh-muc/{id}', [DanhMucController::class, 'destroy']);

        Route::get('danh-muc/sua-danh-muc/{id}', [DanhMucController::class, 'show']);

        Route::put('danh-muc/sua-danh-muc/{id}', [DanhMucController::class, 'update']);

        Route::get('/duyet-blog', [KiemDuyetController::class, 'layDanhSachBlog']);

        Route::post('/duyet-blog/xu-ly', [KiemDuyetController::class, 'xuLyDuyetBlog']);

        // 2. Xử lý duyệt hoặc từ chối bài viết
        // URL: POST /api/admin/duyet-blog/xu-ly
        Route::post('/duyet-blog/xu-ly', [KiemDuyetController::class, 'xuLyDuyetBlog']);
    });
    // B. NHÓM API NGƯỜI DÙNG (Cả admin và user đều có quyền sử dụng các chức năng trên)

    Route::prefix('user')->middleware('role:1,0')->group(function () {
        // Thảo - Thêm công thức
        Route::post('cong-thuc/them-cong-thuc', [CongThucController::class, 'themCongThuc']);

        // Thảo - Danh sách công thức của người dùng
        Route::get('/cong-thuc', [CongThucController::class, 'CongThucCuaToi']);

        // Thảo - Sửa công thức
        Route::post('/cong-thuc/sua-cong-thuc/{Ma_CT}', [CongThucController::class, 'suaCongThuc']);

        // Thảo - Xóa công thức
        Route::post('/cong-thuc/xoa-cong-thuc/{Ma_CT}', [CongThucController::class, 'xoaCongThuc']);

        // 2. Quản lý Công thức cá nhân (My Recipes)


        // 3. Quản lý Blog cá nhân

        // Thi - Thêm blog
        Route::post('/them-blog', [BlogController::class, 'themBlog']);
        
        // Thi - Dánh sách blog cá nhân của người dùng
        Route::get('/blog-ca-nhan', [BlogController::class, 'layDSBlogCaNhan']);

        // Thi - Xóa blog cá nhân
        Route::post('/xoa-blog/{id}', [BlogController::class, 'xoaBlogCaNhan']);

        // Thi - Lấy chi tiết blog cá nhân để sửa
        Route::get('/lay-blog-can-sua/{id}', [BlogController::class, 'layBlogDeSua']);

        // Thi - Sửa blog
        Route::post('/cap-nhat-blog/{id}', [BlogController::class, 'capNhatBlog']);

        // 4. Cookbook (Bộ sưu tập)

        //Khôi
        Route::get('/cookbook', [CookbookController::class, 'danhSach']);

        Route::post('/cookbook/tao-cookbook', [CookbookController::class, 'store']);

        Route::put('/cookbook/{id}', [CookbookController::class, 'destroy']);
        Route::get('/cookbook/chi-tiet/{id}', [CookbookController::class, 'show']);

        // 5. Thêm công thức vào cookbook

        // Đăng xuất
        Route::post('/logout', [AuthController::class, 'logout']);
        // Upload ảnh (Dùng chung cho cả avatar, ảnh bài viết...)
        Route::post('/upload-image', [KhachController::class, 'uploadImage']);
        
    });
});
