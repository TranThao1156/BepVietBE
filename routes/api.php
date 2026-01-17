<?php
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CongThucController;
use App\Http\Controllers\API\CookbookController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

// Thảo - danh sách công thức
Route::get('/cong-thuc', [CongThucController::class, 'index']);

Route::get('/cong-thuc/{id}', [CongThucController::class, 'show']);

// Thảo - Thêm công thức
Route::post('/them-cong-thuc', [CongThucController::class, 'themCongThuc']);

// Thảo - Lấy danh sách công thức theo user

//Khôi
Route::post('/cookbook/create', [CookbookController::class, 'store']);
