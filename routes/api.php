<?php
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\CookbookController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
//Khôi
Route::post('/cookbook/create', [CookbookController::class, 'store']);
