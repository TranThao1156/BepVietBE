<?php

namespace App\Http\Controllers\API; // Hoặc App\Http\Controllers\API\Admin tùy cấu trúc folder của bạn

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        try {
            // 1. Lấy tham số 'filter' từ URL (Frontend gửi lên ?filter=month)
            // Nếu không có thì mặc định là '7days'
            $filter = $request->input('filter', '7days');

            // 2. Truyền $filter sang Service để xử lý logic
            $data = $this->dashboardService->laySoLieuTongQuan($filter);

            return response()->json([
                'success' => true,
                'message' => 'Lấy dữ liệu Dashboard thành công',
                'data'    => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage()
            ], 500);
        }
    }
}