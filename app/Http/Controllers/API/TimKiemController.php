<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TimKiemService; // Import Service tìm kiếm

class TimKiemController extends Controller
{
    protected $timKiemService;

    // Inject Service vào Controller
    public function __construct(TimKiemService $timKiemService)
    {
        $this->timKiemService = $timKiemService;
    }

    public function timKiem(Request $request)
    {
        // Gọi sang Service xử lý
        $ketQua = $this->timKiemService->xuLyTimKiem($request);

        return response()->json([
            'success' => true,
            'data' => $ketQua
        ]);
    }
}