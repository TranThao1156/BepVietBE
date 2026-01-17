<?php
namespace App\Services;
use App\Models\CongThuc;
class CongThucService
{
    // 16/01/2026 Thi tạo Service lấy công thức cho trang chủ
    // Lấy danh sách công thức mới nhất (4 món mới nhất)
    public function layDSCongThucMoi()
    {
        return CongThuc::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('TrangThai', 1)
            -> where('TrangThaiDuyet', "Chấp nhận")
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
    }
    // Lấy danh sách công thức được xem nhiều nhất (4 món nổi bật)
    public function layDSCongThucNoiBat()
    {
        return CongThuc::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('TrangThai', 1)
            -> where('TrangThaiDuyet', "Chấp nhận")
            ->orderBy('SoLuotXem', 'desc')
            ->take(4)
            ->get();
    }
    // Hiển thị 1 công thức nổi bật theo vùng miền
    // miền bắc
    public function layCongThucNoiBatTheoMien($mien)
    {
        $mapMien = [
            'bac'   => 1,
            'trung' => 2,
            'nam'   => 3
        ];
        if (!isset($mapMien[$mien])) {
            return null;
        }
        return CongThuc::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('Ma_VM', $mapMien[$mien])
            ->where('TrangThai', 1)
            ->where('TrangThaiDuyet', 'Chấp nhận')
            ->orderBy('SoLuotXem', 'desc')
            ->first();
    }
}