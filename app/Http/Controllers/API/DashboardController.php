<?php

namespace App\Http\Controllers\API; // Hoặc App\Http\Controllers\API\Admin tùy cấu trúc folder của bạn

use App\Exports\ThongKeExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardService;
use Maatwebsite\Excel\Facades\Excel;

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

    public function export(Request $request)
    {
        try {
            // 1. Lấy dữ liệu thống kê (tái sử dụng Service cũ)
            $data = $this->dashboardService->laySoLieuTongQuan('7days');

            // 2. Xuất ra file Excel
            // Tên file: bao-cao-thong-ke-{ngày-giờ}.xlsx
            $fileName = 'bao-cao-thong-ke-' . now()->format('Y-m-d_H-i') . '.xlsx';

            return Excel::download(new ThongKeExport($data), $fileName);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
