<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CongThucController;
use App\Http\Controllers\API\CookbookController;
use App\Http\Controllers\API\QuanLyController;
use App\Http\Controllers\API\NguoiDungController;
use App\Http\Controllers\API\KhachController;

// 1. IMPORT CONTROLLER KIỂM DUYỆT
use App\Http\Controllers\API\KiemDuyetController;

// =========================================================================
// 1. PUBLIC ROUTES (KHÔNG CẦN ĐĂNG NHẬP)
// =========================================================================

// --- Xác thực ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Thảo - danh sách công thức
Route::get('/cong-thuc', [CongThucController::class, 'index']);
// Thảo - Chi tiết công thức
Route::get('/cong-thuc/{id}', [CongThucController::class, 'show']);

// =========================================================================
// 2. PROTECTED ROUTES (YÊU CẦU ĐĂNG NHẬP - Token)
// =========================================================================

Route::middleware(['auth:sanctum'])->group(function () {

    // ----------------------------------------------------------------
    // A. NHÓM API ADMIN (Chỉ VaiTro = 0 mới gọi được)
    // Middleware 'role:0' kiểm tra user->VaiTro === 0
    // ----------------------------------------------------------------
    Route::prefix('admin')
        ->middleware('vaitro:0') // Đảm bảo bạn đã đăng ký alias 'role' trong Kernel
        ->group(function () {

            // --- QUẢN LÝ DUYỆT BLOG (Sử dụng KiemDuyetService) ---

            Route::get('/duyet-blog', [KiemDuyetController::class, 'layDanhSachBlog']);
            Route::post('/duyet-blog/xu-ly', [KiemDuyetController::class, 'xuLyDuyetBlog']);

            // 2. Xử lý duyệt hoặc từ chối bài viết
            // URL: POST /api/admin/duyet-blog/xu-ly
            Route::post('/duyet-blog/xu-ly', [KiemDuyetController::class, 'xuLyDuyetBlog']);
        });


    // ----------------------------------------------------------------
    // B. NHÓM API NGƯỜI DÙNG (Cả admin và user đều có quyền sử dụng)
    // ----------------------------------------------------------------
    Route::prefix('user')
        ->middleware('vaitro:1,0')
        ->group(function () {

            // Thảo - Thêm công thức
            Route::post('/them-cong-thuc', [CongThucController::class, 'themCongThuc']);

            // Khôi - Tạo cookbook
            Route::post('/cookbook/tao-cookbook', [CookbookController::class, 'store']);

            // Đăng xuất
            Route::post('/logout', [AuthController::class, 'logout']);

            // Upload ảnh (Dùng chung cho cả avatar, ảnh bài viết...)
            Route::post('/upload-image', [KhachController::class, 'uploadImage']);
        });
});
