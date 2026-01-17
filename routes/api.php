
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TimKiemController; // Import TimKiemController - Trâm
use App\Http\Controllers\API\DoiMatKhauController; // Import DoiMatKhauController - Trâm
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CongThucController;
Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

// Thảo
Route::get('/ds-cong-thuc', [CongThucController::class, 'index']);


//Trâm - Các API yêu cầu phải đăng nhập mới dùng được
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/doi-mat-khau', [DoiMatKhauController::class, 'doiMatKhau']);
});


//Trâm- API tìm kiếm công thức
Route::get('/tim-kiem', [TimKiemController::class, 'timKiem']);