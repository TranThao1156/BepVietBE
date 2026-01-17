<?php

namespace App\Services;

use App\Models\CongThuc;
use Illuminate\Http\Request;

class TimKiemService
{
    public function xuLyTimKiem(Request $request)
    {
        $query = CongThuc::query();

        // 1. TÌM KIẾM (Sửa TieuDe -> TenMon)
        if ($request->has('keyword') && $request->keyword != '') {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('TenMon', 'LIKE', "%{$keyword}%"); 
                // Nếu bảng của bạn có cột NguyenLieu thì thêm dòng dưới, không thì xóa đi:
                // ->orWhere('NguyenLieu', 'LIKE', "%{$keyword}%");
            });
        }

        // 2. SẮP XẾP (Sửa LuotXem -> SoLuotXem)
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('SoLuotXem', 'desc'); // Sửa tên cột ở đây
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