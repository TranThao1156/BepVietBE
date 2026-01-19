<?php

namespace App\Services;

use App\Models\CongThuc;
use Illuminate\Http\Request;

class TimKiemService
{
    public function xuLyTimKiem(Request $request)
    {
        $query = CongThuc::query();

        $query->where('TrangThai', 1);
        $query->where('TrangThaiDuyet', 'Chấp nhận');
        // 1. TÌM KIẾM (Sửa TieuDe -> TenMon)
        if ($request->has('keyword') && $request->keyword != '') {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('TenMon', 'LIKE', "%{$keyword}%"); 
                // Nếu bảng của bạn có cột NguyenLieu thì thêm dòng dưới, không thì xóa đi:
                // ->orWhere('NguyenLieu', 'LIKE', "%{$keyword}%");
            });
        }

    // 2. LỌC VÙNG MIỀN (Dựa vào cột Ma_VM)
        // Frontend gửi lên: region=1, region=2...
        if ($request->filled('region') && $request->region != 'all') {
            $query->where('Ma_VM', $request->region);
        }

        // 3. LỌC LOẠI MÓN (Dựa vào cột Ma_LM - Theo ảnh database bạn gửi)
        // Frontend gửi lên: category=1, category=2...
        if ($request->filled('category') && $request->category != 'all') {
            $query->where('Ma_LM', $request->category);
        }

        // 4. LỌC ĐỘ KHÓ (Dựa vào cột DoKho)
        // Frontend gửi lên: difficulty=1 (Dễ), 2 (TB), 3 (Khó)
        // Lưu ý: Nếu DB bạn lưu chữ "Dễ" thì sửa số 1 thành chữ "Dễ"
        if ($request->filled('difficulty') && $request->difficulty != 'all') {
            // Kiểm tra xem DB bạn lưu số hay chữ. 
            // Theo ảnh bạn gửi là chữ "Trung bình", "Dễ".
            // Nên ta cần map số sang chữ hoặc Frontend gửi thẳng chữ lên.
            // Ở đây mình giả định Frontend gửi số, Backend map sang chữ cho chuẩn DB:
            $mapDoKho = [
                '1' => 'Dễ',
                '2' => 'Trung bình', 
                '3' => 'Khó'
            ];
            if (isset($mapDoKho[$request->difficulty])) {
                $query->where('DoKho', $mapDoKho[$request->difficulty]);
            }
        }

        // 5. LỌC THỜI GIAN (Dựa vào cột ThoiGianNau)
        if ($request->filled('time')) {
            switch ($request->time) {
                case 'under_15':
                    $query->where('ThoiGianNau', '<=', 15); // Lấy món <= 15 phút
                    break;
                case 'under_30':
                    $query->where('ThoiGianNau', '<', 30);
                    break;
                case '30_60':
                    $query->whereBetween('ThoiGianNau', [30, 60]);
                    break;
                case 'over_60':
                    $query->where('ThoiGianNau', '>', 60);
                    break;
            }
        }

        // --- 👆 HẾT PHẦN LỌC 👆 ---

        // 2. SẮP XẾP (Sửa LuotXem -> SoLuotXem)
      $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('SoLuotXem', 'desc');
                break;
            case 'oldest':
            
                $query->orderBy('created_at', 'asc');
                break;
            default: // newest
                
                $query->orderBy('created_at', 'desc'); 
                break;
        }

        return $query->paginate(6);
        }
}