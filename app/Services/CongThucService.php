<?php
namespace App\Services;

use App\Models\BuocThucHien;
use App\Models\CongThuc;
use App\Models\NguyenLieu;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

    public function LayDsCongThucByUser(int $userId, int $limit = 5)
    {
        return CongThuc::where('Ma_ND', $userId)
            // 1. Chỉ lấy các cột cần thiết
            ->select('Ma_CT', 'TenMon', 'HinhAnh', 'created_at', 'TrangThaiDuyet', 'Ma_LM', 'Ma_VM')

            // 2. Nạp trước dữ liệu bảng LoaiMon và VungMien để hiển thị (Món nước • Miền Bắc)
            ->with([
                'loaiMon:Ma_LM,TenLoaiMon',   // Chỉ lấy cột TenLoaiMon
                'vungMien:Ma_VM,TenVungMien' // Chỉ lấy cột TenVungMien
            ])

            // 3. Sắp xếp mới nhất lên đầu
            ->orderByDesc('created_at')

            // 4. Phân trang
            ->paginate($limit);
    }

    // Thảo - Thêm công thức
    public function createCongThuc(Request $request, $user)
    {
        return DB::transaction(function () use ($request, $user) {

            $congThuc = CongThuc::create([
                'TenMon' => $request->TenMon,
                'MoTa' => $request->MoTa,
                'KhauPhan' => $request->KhauPhan,
                'DoKho' => $request->DoKho,
                'ThoiGianNau' => $request->ThoiGianNau,
                'HinhAnh' => $request->input('HinhAnh') ?? null,
                'TrangThaiDuyet' => 'Chờ duyệt',
                'SoLuotXem' => 0,
                'Ma_VM' => $request->Ma_VM,
                'Ma_LM' => $request->Ma_LM,
                'Ma_DM' => $request->Ma_DM,
                'Ma_ND' => $user->Ma_ND, // ✅ ĐÚNG
                'TrangThai' => 1
            ]);

            foreach ($request->NguyenLieu as $nl) {

                // 1. Tạo hoặc lấy nguyên liệu
                $nguyenLieu = NguyenLieu::firstOrCreate(
                    ['TenNguyenLieu' => $nl['TenNguyenLieu']],
                    ['DonViDo' => $nl['DonViDo']]
                );

                // 2. Gắn vào công thức
                DB::table('nl_cthuc')->insert([
                    'Ma_CT' => $congThuc->Ma_CT,
                    'Ma_NL' => $nguyenLieu->Ma_NL,
                    'DinhLuong' => $nl['DinhLuong']
                ]);
            }

            foreach ($request->BuocThucHien as $buoc) {
                BuocThucHien::create([
                    'Ma_CT' => $congThuc->Ma_CT,
                    'STT' => $buoc['STT'],
                    'NoiDung' => $buoc['NoiDung'],
                    'HinhAnh' => $buoc['HinhAnh'] ?? null
                ]);
            }
            return $congThuc;
        });
    }

    // Thảo - Sửa công thức
    public function updateCongThuc($id, Request $request, $user)
    {
        return DB::transaction(function () use ($id, $request, $user) {
            $congThuc = CongThuc::where('Ma_CT', $id)
                                ->where('Ma_ND', $user->Ma_ND)
                                ->firstOrFail();

            // 1. Cập nhật thông tin chính
            $dataUpdate = [
                'TenMon' => $request->TenMon,
                'MoTa' => $request->MoTa,
                'KhauPhan' => $request->KhauPhan,
                'DoKho' => $request->DoKho,
                'ThoiGianNau' => $request->ThoiGianNau,
                'Ma_VM' => $request->Ma_VM,
                'Ma_LM' => $request->Ma_LM,
                'Ma_DM' => $request->Ma_DM,
                // Nếu User sửa lại thì trạng thái quay về chờ duyệt
                'TrangThaiDuyet' => 'Chờ duyệt', 
            ];

            // Chỉ cập nhật ảnh bìa nếu có ảnh mới gửi lên (đã xử lý ở Controller)
            if ($request->input('HinhAnh')) {
                $dataUpdate['HinhAnh'] = $request->input('HinhAnh');
            }

            $congThuc->update($dataUpdate);

            // 2. Xử lý Nguyên Liệu: Xóa hết cũ -> Tạo lại mới
            DB::table('nl_cthuc')->where('Ma_CT', $id)->delete();
            
            foreach ($request->NguyenLieu as $nl) {
                $nguyenLieu = NguyenLieu::firstOrCreate(
                    ['TenNguyenLieu' => $nl['TenNguyenLieu']],
                    ['DonViDo' => $nl['DonViDo']]
                );

                DB::table('nl_cthuc')->insert([
                    'Ma_CT' => $congThuc->Ma_CT,
                    'Ma_NL' => $nguyenLieu->Ma_NL,
                    'DinhLuong' => $nl['DinhLuong']
                ]);
            }

            // 3. Xử lý Bước Thực Hiện: Xóa hết cũ -> Tạo lại mới
            BuocThucHien::where('Ma_CT', $id)->delete();

            foreach ($request->BuocThucHien as $buoc) {
                BuocThucHien::create([
                    'Ma_CT' => $congThuc->Ma_CT,
                    'STT' => $buoc['STT'],
                    'NoiDung' => $buoc['NoiDung'],
                    'HinhAnh' => $buoc['HinhAnh'] ?? null
                ]);
            }

            return $congThuc;
        });
    }
}
