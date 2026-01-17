<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CongThuc;
use App\Services\CongThucService;
use Illuminate\Http\Request;

class CongThucController extends Controller
{
    protected $congThucService;

    public function __construct(CongThucService $congThucService)
    {
        $this->congThucService = $congThucService;
    }
    // 16/01/2026 Thi tạo API lấy công thức cho trang chủ
    // Lấy danh sách công thức mới nhất (4 món mới nhất)
    public function layDSCongThucMoi()
    {
            $data = $this->congThucService->layDSCongThucMoi();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách món mới thành công',
                'data' => $data
            ], 200);
    }
    // Lấy danh sách công thức được xem nhiều nhất (4 món nổi bật)
    public function layDSCongThucNoiBat()
    {
            $data = $this->congThucService->layDSCongThucNoiBat();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách món nổi bật thành công',
                'data' => $data
            ], 200);
    }
    // Hiển thị 1 công thức nổi bật theo vùng miền ( miền bắc, miền trung, miền nam )
    public function layCongThucNoiBatTheoMien(string $mien)
    {
        $data = $this->congThucService->layCongThucNoiBatTheoMien($mien);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy công thức nổi bật cho miền ' . $mien
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Lấy công thức nổi bật miền ' . $mien . ' thành công',
            'data' => $data
        ], 200);
    }
}
