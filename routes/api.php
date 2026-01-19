
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CongThucController;
use App\Http\Controllers\API\CookbookController;
use App\Http\Controllers\API\DanhMucController;
use App\Http\Controllers\API\QuanLyController;
use App\Http\Controllers\API\NguoiDungController;
use App\Http\Controllers\API\KhachController;


// 1. PUBLIC ROUTES (KHÔNG CẦN ĐĂNG NHẬP)


// --- Xác thực ---
Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

// Thảo - danh sách công thức
Route::get('/cong-thuc', [CongThucController::class, 'index']);

// Thảo - Chi tiết công thức
Route::get('/cong-thuc/{id}', [CongThucController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {

    // ----------------------------------------------------------------
    // A. NHÓM API ADMIN (Chỉ VaiTro = 0 mới gọi được)
    // Middleware 'role:0' kiểm tra user->VaiTro === 0
    // ----------------------------------------------------------------
    Route::prefix('admin')->middleware('role:0')->group(function () {
        //Khôiii------
        Route::get('danh-muc', [DanhMucController::class, 'index']);
        Route::post('danh-muc/tao-danh-muc', [DanhMucController::class, 'store']);
        Route::delete('danh-muc/{id}', [DanhMucController::class, 'destroy']);
        Route::get('danh-muc/sua-danh-muc/{id}', [DanhMucController::class, 'show']);
        Route::put('danh-muc/sua-danh-muc/{id}', [DanhMucController::class, 'update']);
        //------------

    });
    // B. NHÓM API NGƯỜI DÙNG (Cả admin và user đều có quyền sử dụng các chức năng trên)
    Route::prefix('user')->middleware('role:1')->group(function () {
    // Thảo - Thêm công thức

        Route::post('/them-cong-thuc', [CongThucController::class, 'themCongThuc']);
    // 4. Cookbook (Bộ sưu tập)
        //Khôi------
        Route::get('/cookbook', [CookbookController::class, 'danhSach']);

        Route::post('/cookbook/tao-cookbook', [CookbookController::class, 'store']);

        Route::put('/cookbook/{id}', [CookbookController::class, 'destroy']);

        Route::get('/cookbook/chi-tiet/{id}', [CookbookController::class, 'show']);

        Route::post('/cookbook/{cookbook_id}/xoa-mon/{recipe_id}', [CookbookController::class, 'xoaMonKhoiCookbook']);

        Route::put('/cookbook/{id}', [CookbookController::class, 'update']);
        
        //----------------

    // 5. Thêm công thức vào cookbook





        // Đăng xuất
        Route::post('/logout', [AuthController::class, 'logout']);
        // Upload ảnh (Dùng chung cho cả avatar, ảnh bài viết...)
        Route::post('/upload-image', [KhachController::class, 'uploadImage']);
    });
});
