
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import các Controller
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\QuanLyController;
use App\Http\Controllers\API\NguoiDungController; 
use App\Http\Controllers\API\KhachController;

// ====================================================
// 1. PUBLIC ROUTES (KHÔNG CẦN ĐĂNG NHẬP)
// ====================================================

// --- Xác thực ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// --- Trang chủ & Hiển thị công khai (Client) ---
// Giả sử bạn có KhachController để lấy dữ liệu cho trang chủ
Route::get('/public/recipes', [KhachController::class, 'getAllRecipes']);      // Danh sách công thức
Route::get('/public/recipes/{id}', [KhachController::class, 'getRecipeDetail']); // Chi tiết công thức
Route::get('/public/blogs', [KhachController::class, 'getAllBlogs']);          // Danh sách Blog
Route::get('/public/blogs/{id}', [KhachController::class, 'getBlogDetail']);   // Chi tiết Blog
Route::get('/public/categories', [QuanLyController::class, 'getCategories']);    // Lấy danh mục để lọc (dùng chung hàm của Admin cũng được)


// ====================================================
// 2. PROTECTED ROUTES (YÊU CẦU ĐĂNG NHẬP - Token)
// ====================================================
Route::middleware(['auth:sanctum'])->group(function () {

    // ----------------------------------------------------------------
    // A. NHÓM API ADMIN (Chỉ VaiTro = 0 mới gọi được)
    // Middleware 'role:0' kiểm tra user->VaiTro === 0
    // ----------------------------------------------------------------
    Route::prefix('admin')->middleware('role:0')->group(function () {
        
        // 1. Dashboard
        Route::get('/dashboard', [QuanLyController::class, 'index']);

        // 2. Quản lý Người dùng
        Route::get('/users', [QuanLyController::class, 'getUsers']);           // Lấy danh sách
        Route::post('/users', [QuanLyController::class, 'storeUser']);         // Thêm mới
        Route::get('/users/{id}', [QuanLyController::class, 'showUser']);      // Lấy chi tiết (để sửa)
        Route::put('/users/{id}', [QuanLyController::class, 'updateUser']);    // Lưu sửa
        Route::delete('/users/{id}', [QuanLyController::class, 'deleteUser']); // Xóa

        // 3. Quản lý Danh mục
        Route::get('/danh-muc', [QuanLyController::class, 'getCategories']);
        Route::post('/danh-muc', [QuanLyController::class, 'storeDanhMuc']);
        Route::get('/danh-muc/{id}', [QuanLyController::class, 'showDanhMuc']);
        Route::put('/danh-muc/{id}', [QuanLyController::class, 'updateDanhMuc']);
        Route::delete('/danh-muc/{id}', [QuanLyController::class, 'deleteDanhMuc']);

        // 4. Kiểm duyệt nội dung
        Route::get('/kiem-duyet', [QuanLyController::class, 'getPendingRecipes']);         // Lấy bài chờ
        Route::post('/kiem-duyet/duyet/{id}', [QuanLyController::class, 'approveRecipe']); // Duyệt
        Route::delete('/kiem-duyet/huy/{id}', [QuanLyController::class, 'rejectRecipe']);  // Từ chối/Xóa
    });


    // ----------------------------------------------------------------
    // B. NHÓM API NGƯỜI DÙNG (Chỉ VaiTro = 1 mới gọi được)
    // Middleware 'role:1' kiểm tra user->VaiTro === 1
    // ----------------------------------------------------------------
    Route::prefix('user')->middleware('role:1')->group(function () {
        
        // 1. Thông tin cá nhân & Tài khoản
        Route::get('/profile', [NguoiDungController::class, 'getProfile']);
        Route::put('/profile', [NguoiDungController::class, 'updateProfile']);
        Route::put('/doi-mat-khau', [NguoiDungController::class, 'changePassword']);
        Route::get('/lich-su-truy-cap', [NguoiDungController::class, 'getHistory']);

        // 2. Quản lý Công thức cá nhân (My Recipes)
        Route::get('/cong-thuc', [NguoiDungController::class, 'getMyRecipes']);
        Route::post('/cong-thuc', [NguoiDungController::class, 'storeRecipe']);
        Route::get('/cong-thuc/{id}', [NguoiDungController::class, 'showRecipe']);
        Route::put('/cong-thuc/{id}', [NguoiDungController::class, 'updateRecipe']);
        Route::delete('/cong-thuc/{id}', [NguoiDungController::class, 'deleteRecipe']);

        // 3. Quản lý Blog cá nhân
        Route::get('/blog', [NguoiDungController::class, 'getMyBlogs']);
        Route::post('/blog', [NguoiDungController::class, 'storeBlog']);
        Route::get('/blog/{id}', [NguoiDungController::class, 'showBlog']);
        Route::put('/blog/{id}', [NguoiDungController::class, 'updateBlog']);
        Route::delete('/blog/{id}', [NguoiDungController::class, 'deleteBlog']);

        // 4. Cookbook (Bộ sưu tập)
        Route::get('/cookbook', [NguoiDungController::class, 'getCookbooks']);
        Route::post('/cookbook', [NguoiDungController::class, 'createCookbook']);
        Route::get('/cookbook/{id}', [NguoiDungController::class, 'getCookbookDetail']);
        Route::delete('/cookbook/{id}', [NguoiDungController::class, 'deleteCookbook']);
        
        // Thêm công thức vào cookbook
        Route::post('/cookbook/add-recipe', [NguoiDungController::class, 'addRecipeToCookbook']);
    });


    // ----------------------------------------------------------------
    // C. NHÓM DÙNG CHUNG (Cả Admin và User đều dùng được)
    // Middleware 'role:0,1'
    // ----------------------------------------------------------------
    Route::middleware('role:0,1')->group(function () {
        
        // Đăng xuất
        Route::post('/logout', [AuthController::class, 'logout']);

        // Upload ảnh (Dùng chung cho cả avatar, ảnh bài viết...)
        Route::post('/upload-image', [KhachController::class, 'uploadImage']);
    });

});