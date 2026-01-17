<?php
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CongThucController;
use App\Http\Controllers\API\DoiMatKhauController; // Trâm - Import DoiMatKhauController
use Illuminate\Support\Facades\Route;
Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

// Thảo
Route::get('/ds-cong-thuc', [CongThucController::class, 'index']);


//Trâm - Các API yêu cầu phải đăng nhập mới dùng được
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/doi-mat-khau', [DoiMatKhauController::class, 'doiMatKhau']);
});

