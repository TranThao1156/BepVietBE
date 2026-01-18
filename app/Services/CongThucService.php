<?php

namespace App\Services;

use App\Models\BuocThucHien;
use App\Models\CongThuc;
use App\Models\NguyenLieu;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CongThucService
{
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
        $monLienQuan = CongThuc::select('Ma_CT', 'TenMon', 'HinhAnh', 'Ma_ND', 'SoLuotXem')
            ->where('Ma_CT', '!=', $maCT)
            ->where('TrangThai', 1)
            ->where('TrangThaiDuyet', 'Chấp nhận')
            ->where(function ($q) use ($congThuc) {
                $q->where('Ma_LM', $congThuc->Ma_LM)
                    ->orWhere('Ma_VM', $congThuc->Ma_VM);
            })
            ->with('nguoidung:Ma_ND,HoTen,AnhDaiDien')
            ->orderByDesc('SoLuotXem')
            ->limit(4)
            ->get();

        // Gắn thêm vào object trả về
        $congThuc->mon_lien_quan = $monLienQuan;

        return $congThuc;
    }

    // Thảo - Lấy danh sách công thức bởi người dùng
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

            // Cập nhật thông tin chính
            $dataUpdate = $request->only([
                'TenMon',
                'MoTa',
                'KhauPhan',
                'DoKho',
                'ThoiGianNau',
                'Ma_VM',
                'Ma_LM',
                'Ma_DM'
            ]);
            $dataUpdate['TrangThaiDuyet'] = 'Chờ duyệt';

            // Chỉ cập nhật ảnh bìa nếu có ảnh mới gửi lên (đã xử lý ở Controller)
            if ($request->input('HinhAnh')) {
                $dataUpdate['HinhAnh'] = $request->input('HinhAnh');
            }

            $congThuc->update($dataUpdate);

            // Xử lý Nguyên Liệu (Xóa cũ -> Thêm mới bulk insert)
            DB::table('nl_cthuc')->where('Ma_CT', $id)->delete();

            $nlPivotData = [];
            foreach ($request->NguyenLieu as $nl) {
                $nguyenLieu = NguyenLieu::firstOrCreate(
                    ['TenNguyenLieu' => $nl['TenNguyenLieu']],
                    ['DonViDo' => $nl['DonViDo']]
                );
                $nlPivotData[] = [
                    'Ma_CT' => $congThuc->Ma_CT,
                    'Ma_NL' => $nguyenLieu->Ma_NL,
                    'DinhLuong' => $nl['DinhLuong']
                ];
            }
            if (!empty($nlPivotData)) {
                DB::table('nl_cthuc')->insert($nlPivotData);
            }

            // Xử lý Bước Thực Hiện (Xóa cũ -> Thêm mới bulk insert)
            BuocThucHien::where('Ma_CT', $id)->delete();

            $buocData = [];
            foreach ($request->BuocThucHien as $buoc) {
                $buocData[] = [
                    'Ma_CT' => $congThuc->Ma_CT,
                    'STT' => $buoc['STT'],
                    'NoiDung' => $buoc['NoiDung'],
                    'HinhAnh' => $buoc['HinhAnh'] ?? null
                ];
            }
            if (!empty($buocData)) {
                BuocThucHien::insert($buocData);
            }

            return $congThuc;
        });
    }
}
