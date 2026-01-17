<?php
namespace App\Services;
use App\Models\CongThuc;
class CongThucService
{
    // Thi tạo Service lấy công thức cho trang chủ
    // Thi Lấy danh sách công thức mới nhất (4 món mới nhất)
    public function layDSCongThucMoi()
    {
        return CongThuc::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('TrangThai', 1)
            -> where('TrangThaiDuyet', "Chấp nhận")
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
    }
    // Thi Lấy danh sách công thức được xem nhiều nhất (4 món nổi bật)
    public function layDSCongThucNoiBat()
    {
        return CongThuc::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('TrangThai', 1)
            -> where('TrangThaiDuyet', "Chấp nhận")
            ->orderBy('SoLuotXem', 'desc')
            ->take(4)
            ->get();
    }
    // Thi Hiển thị 1 công thức nổi bật theo vùng miền
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
    //Thảo - Ds công thức
    public function layDanhSachCongThuc(array $boLoc = [])
    {
        $query = CongThuc::query()
            ->where('TrangThai', 1)
            ->where('TrangThaiDuyet', "Chấp nhận");

        // Phân trang
        return $query->paginate($boLoc['limit'] ?? 6);
    }

    // Thảo - Chi tiết công thức
    public function chiTietCongThuc(int $maCT)
    {
        $congThuc = CongThuc::with([
            'loaiMon',
            'vungMien',
            'nguyenLieu',
            'buocThucHien',
            'nguoidung'
        ])->findOrFail($maCT);

        // Lấy món liên quan
        $monLienQuan = CongThuc::where('Ma_CT', '!=', $maCT)
            ->where('TrangThai', 1)
            ->where('TrangThaiDuyet', 'Chấp nhận')
            ->where(function ($q) use ($congThuc) {
                $q->where('Ma_LM', $congThuc->Ma_LM)
                    ->orWhere('Ma_VM', $congThuc->Ma_VM);
            })
            ->with('nguoidung')
            ->orderByDesc('SoLuotXem')
            ->limit(4)
            ->get();

        // Gắn thêm vào object trả về
        $congThuc->mon_lien_quan = $monLienQuan;

        return $congThuc;
    }



    public function LayDsCongThucByUser(int $userId, int $limit = 10)
    {
        return CongThuc::where('Ma_ND', $userId)
            ->orderByDesc('created_at')
            ->paginate($limit);
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
}
