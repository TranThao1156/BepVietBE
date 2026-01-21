<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\DanhGia;
use App\Models\CongThuc;
use App\Events\DanhGiaMoi;
use Illuminate\Support\Facades\Auth;
use Exception;

class DanhGiaService
{
    // HÀM XỬ LÝ ĐÁNH GIÁ (Gộp chung Thêm & Sửa)
    public function xuLyDanhGia($data)
    {
        // Trâm - đã sửa: hệ thống dùng khóa chính Ma_ND
        $userId = Auth::user()?->Ma_ND ?? Auth::id();
        $maCongThuc = $data['Ma_CT'];
        $soSao = $data['SoSao'];

        // Sử dụng Transaction để đảm bảo an toàn dữ liệu
        return DB::transaction(function () use ($userId, $maCongThuc, $soSao) {
            
            // 1. Thêm hoặc Sửa
            $danhGia = DanhGia::updateOrCreate(
                [
                    'Ma_ND' => $userId, 
                    'Ma_CT' => $maCongThuc
                ],
                [
                    'SoSao' => $soSao,
                    // Nếu bạn muốn lưu cả nội dung bình luận, hãy thêm vào đây
                    'NoiDung' => $data['NoiDung'] ?? null 
                ]
            );

            // 2. Tính lại trung bình sao ngay lập tức
            $trungBinhMoi = $this->capNhatTrungBinhSao($maCongThuc);

            // 3. 🔥 REALTIME: Bắn sự kiện
            broadcast(new DanhGiaMoi($maCongThuc, $trungBinhMoi))->toOthers();

            return [
                'danh_gia' => $danhGia,
                'trung_binh_moi' => $trungBinhMoi
            ];
        });
    }

    // Hàm phụ: Tính toán và lưu vào bảng CongThuc
    public function capNhatTrungBinhSao($maCongThuc)
    {
        // 1. Tính trung bình cộng dùng Model cho sạch code
        $avg = DanhGia::where('Ma_CT', $maCongThuc)->avg('SoSao');

        $finalAvg = round($avg, 1);

        // 2. Cập nhật vào bảng công thức
        CongThuc::where('Ma_CT', $maCongThuc)->update(['TrungBinhSao' => $finalAvg]);
        
        return $finalAvg;
    }

    // Hàm lấy đánh giá của user (để hiện màu sao cũ)
    public function layDanhGiaCuaUser($maCongThuc)
    {
        // Trâm - đã sửa: hệ thống dùng khóa chính Ma_ND
        $userId = Auth::user()?->Ma_ND ?? Auth::id();

        return DanhGia::where('Ma_ND', $userId)
                      ->where('Ma_CT', $maCongThuc)
                      ->first();
    }

    // Trâm - đã sửa: API public lấy danh sách đánh giá theo công thức
    public function layDanhSachDanhGia($maCongThuc)
    {
        return DanhGia::with(['nguoidung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('Ma_CT', $maCongThuc)
            ->orderByDesc('Ma_DG')
            ->get();
    }
}