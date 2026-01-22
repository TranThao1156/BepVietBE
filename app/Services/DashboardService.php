<?php

namespace App\Services;

use App\Models\NguoiDung;
use App\Models\CongThuc;
use App\Models\DanhMuc;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function laySoLieuTongQuan($filter = '7days')
    {
        $homNay = Carbon::today();

        // 1. Tổng hợp số liệu thống kê
        $tongNguoiDung = NguoiDung::count();
        $userMoiHomNay = NguoiDung::whereDate('created_at', $homNay)->count();
        $tongCongThuc = CongThuc::count();
        $congThucMoi = CongThuc::whereDate('created_at', $homNay)->count();
        $choPheDuyet = CongThuc::where('TrangThaiDuyet', 'Chờ duyệt')->count();
        $tongLuotXem = CongThuc::sum('SoLuotXem');
        $tongDanhMuc = DanhMuc::where('TrangThai', 1)->count();
        // 2. Dữ liệu biểu đồ
        $bieuDoUser = $this->layDuLieuBieuDoUser($filter);
        // Trả về key
        return [
            'users' => [
                'total' => $tongNguoiDung,
                'new_today' => $userMoiHomNay
            ],
            'recipes' => [
                'total' => $tongCongThuc,
                'new_today' => $congThucMoi,
                'pending' => $choPheDuyet
            ],
            'views' => [
                'total' => $tongLuotXem,
                'growth' => 12 // Giả định
            ],
            'categories' => [
                'total' => $tongDanhMuc
            ],
            'chart' => $bieuDoUser
        ];
    }

    // Hàm xử lý logic biểu đồ theo bộ lọc
    private function layDuLieuBieuDoUser($filter)
    {
        $chartResult = [];
        // --- TRƯỜNG HỢP 1: LỌC THEO NĂM (Gom nhóm theo THÁNG) ---
        if ($filter == 'year') {
            $startOfYear = Carbon::now()->startOfYear();
            $endOfYear = Carbon::now()->endOfYear();

            // Query gom nhóm theo tháng: MONTH(created_at)
            $data = NguoiDung::select(
                    DB::raw('MONTH(created_at) as month'), 
                    DB::raw('count(*) as count')
                )
                ->whereBetween('created_at', [$startOfYear, $endOfYear])
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get()
                ->keyBy('month');

            // Vòng lặp tạo dữ liệu cho 12 tháng (để tháng nào không có user vẫn hiện 0)
            for ($m = 1; $m <= 12; $m++) {
                $chartResult[] = [
                    'date'  => "Tháng $m",
                    'label' => "Tháng $m",
                    'count' => isset($data[$m]) ? $data[$m]->count : 0
                ];
            }
        } 
        // --- TRƯỜNG HỢP 2: LỌC THEO NGÀY (7 ngày hoặc Tháng này) ---
        else {
            if ($filter == 'month') {
                // Lấy từ ngày 1 đến hết tháng này
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
            } else {
                // Mặc định: 7 ngày gần nhất
                $endDate = Carbon::now();
                $startDate = Carbon::now()->subDays(6);
            }
            $data = NguoiDung::select( // Query gom nhóm theo ngày: DATE(created_at)
                    DB::raw('DATE(created_at) as date'), 
                    DB::raw('count(*) as count')
                )
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get()
                ->keyBy('date');

            // Vòng lặp lấp đầy khoảng trống các ngày không có dữ liệu
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateString = $currentDate->format('Y-m-d');
                // Tạo Label hiển thị (VD: T2 hoặc 01/01)
                if ($filter == 'month') {
                    $label = $currentDate->format('d/m'); // VD: 01/01
                } else {
                    $label = $this->getThuTiengViet($currentDate->dayOfWeek); // VD: T2
                }
                $chartResult[] = [
                    'date'  => $dateString,
                    'label' => $label,
                    'count' => isset($data[$dateString]) ? $data[$dateString]->count : 0
                ];
                $currentDate->addDay();
            }
        }
        return $chartResult;
    }

    // Hàm phụ trợ đổi thứ sang tiếng Việt
    private function getThuTiengViet($dayInt) {
        $days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        return $days[$dayInt];
    }
}