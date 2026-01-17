
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CongThucController;


use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CookbookController;

use App\Http\Controllers\API\QuanLyController;
use App\Http\Controllers\API\NguoiDungController;
use App\Http\Controllers\API\KhachController;
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


// Danh sách Blog
// Thi - Lấy danh sách blog mới nhất (6 bài mới nhất)
Route::get('/blog', [BlogController::class, 'layDSBlogMoiNhat']);
// Chi tiết Blog


// Lấy danh mục để lọc (dùng chung hàm của Admin cũng được)



// 2. PROTECTED ROUTES (YÊU CẦU ĐĂNG NHẬP - Token)

Route::middleware(['auth:sanctum'])->group(function () {

    // ----------------------------------------------------------------
    // A. NHÓM API ADMIN (Chỉ VaiTro = 0 mới gọi được)
    // Middleware 'role:0' kiểm tra user->VaiTro === 0
    // ----------------------------------------------------------------
    Route::prefix('admin')->middleware('role:0')->group(function () {

    // 1. Dashboard


    // 2. Quản lý Người dùng


    // 3. Quản lý Danh mục


    // 4. Kiểm duyệt nội dung

    });



    // B. NHÓM API NGƯỜI DÙNG (Cả admin và user đều có quyền sử dụng các chức năng trên)

    Route::prefix('user')->middleware('role:1,0')->group(function () {

    // 1. Thông tin cá nhân & Tài khoản





    // 2. Quản lý Công thức cá nhân (My Recipes)




    // Thảo - Thêm công thức
        Route::post('/them-cong-thuc', [CongThucController::class, 'themCongThuc']);



    // 3. Quản lý Blog cá nhân




    // 4. Cookbook (Bộ sưu tập)

        //Khôi
        Route::post('/cookbook/tao-cookbook', [CookbookController::class, 'store']);


    // 5. Thêm công thức vào cookbook





        // Đăng xuất
        Route::post('/logout', [AuthController::class, 'logout']);

        // Upload ảnh (Dùng chung cho cả avatar, ảnh bài viết...)
        Route::post('/upload-image', [KhachController::class, 'uploadImage']);
    });

});
