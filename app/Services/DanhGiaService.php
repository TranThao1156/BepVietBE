<?php

namespace App\Services;
use Illuminate\Support\Facades\DB; // <--- THÊM DÒNG NÀY
use App\Models\DanhGia;
use App\Models\CongThuc;
use App\Events\DanhGiaMoi; // Import Event vừa tạo
use Illuminate\Support\Facades\Auth;
use Exception;

class DanhGiaService
{
    // HÀM XỬ LÝ ĐÁNH GIÁ (Gộp chung Thêm & Sửa)
    public function xuLyDanhGia($data)
    {
        $userId = Auth::id();
        $maCongThuc = $data['Ma_CT'];
        $soSao = $data['SoSao'];

        // 1. Thêm hoặc Sửa (Update if exists, Insert if new)
        $danhGia = DanhGia::updateOrCreate(
            [
                'Ma_ND' => $userId, 
                'Ma_CT' => $maCongThuc
            ],
            [
                'SoSao' => $soSao
            ]
        );

        // 2. Tính lại trung bình sao ngay lập tức
        $trungBinhMoi = $this->capNhatTrungBinhSao($maCongThuc);

        // 3. 🔥 REALTIME: Bắn sự kiện cho mọi người biết
        // Dùng toOthers() để không bắn ngược lại cho người vừa bấm (tránh lag UI)
        broadcast(new DanhGiaMoi($maCongThuc, $trungBinhMoi))->toOthers();

        return [
            'danh_gia' => $danhGia,
            'trung_binh_moi' => $trungBinhMoi
        ];
    }

    // Hàm phụ: Tính toán và lưu vào bảng CongThuc
   public function capNhatTrungBinhSao($maCongThuc)
{
    // 1. Tính trung bình cộng cột 'SoSao' trong bảng 'danhgia' của món ăn này
    $avg = DB::table('danhgia')
             ->where('Ma_CT', $maCongThuc)
             ->avg('SoSao');

             $finalAvg = round($avg, 1);
    // 2. Cập nhật kết quả vào cột 'TrungBinhSao' của bảng 'congthuc'
    DB::table('congthuc')
      ->where('Ma_CT', $maCongThuc)
      ->update(['TrungBinhSao' => $finalAvg]); // Làm tròn 1 chữ số thập phân
      return $finalAvg;
}

    // Hàm lấy đánh giá của user (để hiện màu sao cũ)
    public function layDanhGiaCuaUser($maCongThuc)
    {
        return DanhGia::where('Ma_ND', Auth::id())
                      ->where('Ma_CT', $maCongThuc)
                      ->first();
    }
}