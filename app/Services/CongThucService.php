<?php

namespace App\Services;

use App\Models\CongThuc;

class CongThucService
{
    //Thảo - Ds công thức
    public function layDanhSachCongThuc(array $boLoc = [])
    {
        $query = CongThuc::query()
            ->where('TrangThai', 1)
            ->where('TrangThaiDuyet', "Chấp nhận");

        // Phân trang
        return $query->paginate($boLoc['limit'] ?? 5);
    }

    // Thảo - Thêm công thức
    public function themCongThuc(array $duLieu)
    {
        return CongThuc::create([
            'TenMon'         => $duLieu['TenMon'],
            'MoTa'           => $duLieu['MoTa'] ?? null,
            'KhauPhan'       => $duLieu['KhauPhan'],
            'DoKho'          => $duLieu['DoKho'],
            'ThoiGianNau'    => $duLieu['ThoiGianNau'],
            'HinhAnh'        => $duLieu['HinhAnh'] ?? null,
            'TrangThaiDuyet' => 0, // mặc định chờ duyệt
            'SoLuotXem'      => 0,
            'Ma_VM'          => $duLieu['Ma_VM'] ?? null,
            'Ma_LM'          => $duLieu['Ma_LM'],
            'Ma_DM'          => $duLieu['Ma_DM'],
            'Ma_ND'          => $duLieu['Ma_ND'],
            'TrangThai'      => 1
        ]);
    }

    // Thảo - Chi tiết công thức
    public function chiTietCongThuc(int $maCT)
    {
        return CongThuc::where('Ma_CT', $maCT)
            ->where('TrangThai', 1)
            ->first();
    }
}
