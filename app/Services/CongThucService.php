<?php

namespace App\Services;

use App\Models\BuocThucHien;
use App\Models\CongThuc;
use App\Models\NguyenLieu;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\NguoiDung;

class CongThucService
{
    // Thi tạo Service lấy công thức cho trang chủ
    // Thi Lấy danh sách công thức mới nhất (4 món mới nhất)
    public function layDSCongThucMoi()
    {
        return CongThuc::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('TrangThai', 1)
            ->where('TrangThaiDuyet', "Chấp nhận")
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
    }
    // Thi Lấy danh sách công thức được xem nhiều nhất (4 món nổi bật)
    public function layDSCongThucNoiBat()
    {
        return CongThuc::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('TrangThai', 1)
            ->where('TrangThaiDuyet', "Chấp nhận")
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
            ->with(['nguoiDung', 'danhMuc'])
            ->where('TrangThai', 1)
            ->where('TrangThaiDuyet', "Chấp nhận");

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
            ->select('Ma_CT', 'TenMon', 'HinhAnh', 'created_at', 'TrangThaiDuyet', 'Ma_LM', 'Ma_VM')
            ->where('TrangThai', 1)
            // Nạp trước dữ liệu bảng LoaiMon và VungMien để hiển thị (VD: Món nước - Miền Bắc)
            ->with([
                'loaiMon:Ma_LM,TenLoaiMon',   // Chỉ lấy cột TenLoaiMon
                'vungMien:Ma_VM,TenVungMien' // Chỉ lấy cột TenVungMien
            ])
            // 3. Sắp xếp mới nhất lên đầu
            ->orderByDesc('created_at')
            // 4. Phân trang
            ->paginate($limit);
    }

    // --- MỚI: Hàm tìm kiếm để gợi ý cho Frontend ---
    public function timKiemNguyenLieu($keyword)
    {
        // Chuẩn hóa từ khóa tìm kiếm
        $keyword = trim($keyword);

        return NguyenLieu::query()
            ->select('TenNguyenLieu', 'DonViDo')
            ->where('TrangThai', 1)
            ->where('TenNguyenLieu', 'LIKE', "%{$keyword}%")
            // Gom nhóm để tránh hiển thị trùng lặp
            ->groupBy('TenNguyenLieu', 'DonViDo')
            ->distinct()
            ->limit(10) // Chỉ lấy 10 kết quả để gợi ý nhanh
            ->get();
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
                'Ma_ND' => $user->Ma_ND,
                'TrangThai' => 1
            ]);

            // Thêm nguyên liệu
            foreach ($request->NguyenLieu as $nl) {
                $tenChuanHoa = Str::ucfirst(Str::lower(trim(preg_replace('/\s+/', ' ', $nl['TenNguyenLieu']))));
                $donViChuanHoa = Str::lower(trim($nl['DonViDo']));

                // Logic này đáp ứng yêu cầu: "nhập đơn vị khác nhưng tên vẫn thế thì thêm dòng mới"
                // Vì firstOrCreate tìm theo cả 'TenNguyenLieu' VÀ 'DonViDo'.
                $nguyenLieu = NguyenLieu::firstOrCreate(
                    [
                        'TenNguyenLieu' => $tenChuanHoa,
                        'DonViDo'       => $donViChuanHoa
                    ],
                    [
                        'TrangThai'     => 1
                    ]
                );

                DB::table('nl_cthuc')->insert([
                    'Ma_CT'     => $congThuc->Ma_CT,
                    'Ma_NL'     => $nguyenLieu->Ma_NL,
                    'DinhLuong' => $nl['DinhLuong']
                ]);
            }

            $buocData = [];
            foreach ($request->BuocThucHien as $buoc) {
                $buocData[] = [
                    'Ma_CT' => $congThuc->Ma_CT,
                    'STT' => $buoc['STT'],
                    'NoiDung' => $buoc['NoiDung'],
                    'HinhAnh' => $buoc['HinhAnh'] ?? null,
                ];
            }
            if (!empty($buocData)) {
                BuocThucHien::insert($buocData);
            }

            return $congThuc;
        });
    }

    // Thảo - Tăng lượt xem
    public function tangLuotXem(int $maCT, $request): void
    {
        $user = $request->user();
        $viewer = $user ? 'u_' . $user->Ma_ND : 'g_' . $request->ip();
        $key = "view_ct_{$maCT}_{$viewer}";

        if (!Cache::has($key)) {
            CongThuc::where('Ma_CT', $maCT)->increment('SoLuotXem');
            Cache::put($key, true, now()->addMinutes(10));
        }
    }

    // Thảo - Sửa công thức
    public function updateCongThuc($id, Request $request, $user)
    {
        return DB::transaction(function () use ($id, $request, $user) {
            $congThuc = CongThuc::where('Ma_CT', $id)
                ->where('Ma_ND', $user->Ma_ND)
                ->firstOrFail();

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

            if ($request->input('HinhAnh')) {
                $dataUpdate['HinhAnh'] = $request->input('HinhAnh');
            }

            $congThuc->update($dataUpdate);

            // Xử lý Nguyên Liệu: Xóa cũ -> Thêm mới
            DB::table('nl_cthuc')->where('Ma_CT', $id)->delete();

            foreach ($request->NguyenLieu as $nl) {
                $tenChuanHoa = Str::ucfirst(Str::lower(trim(preg_replace('/\s+/', ' ', $nl['TenNguyenLieu']))));
                $donViChuanHoa = Str::lower(trim($nl['DonViDo']));

                $nguyenLieu = NguyenLieu::firstOrCreate(
                    [
                        'TenNguyenLieu' => $tenChuanHoa,
                        'DonViDo'       => $donViChuanHoa
                    ],
                    [
                        'TrangThai'     => 1
                    ]
                );

                DB::table('nl_cthuc')->insert([
                    'Ma_CT'     => $congThuc->Ma_CT,
                    'Ma_NL'     => $nguyenLieu->Ma_NL,
                    'DinhLuong' => $nl['DinhLuong']
                ]);
            }

            // Xử lý Bước Thực Hiện
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

    // Thảo - Xóa công thức (Chuyển trạng thái sang 0 - Soft Delete)
    public function xoaCongThuc(int $maCT, $user): bool
    {
        $congThuc = CongThuc::where('Ma_CT', $maCT)
            ->where('Ma_ND', $user->Ma_ND)
            ->where('TrangThai', 1) // chỉ xóa khi còn hoạt động
            ->first();

        if (!$congThuc) {
            throw new \Exception('Công thức không tồn tại hoặc không có quyền xóa');
        }

        $congThuc->TrangThai = 0;
        $congThuc->save();

        return true;
    }

    // Thảo - Ghi nhận lịch sử xem
    public function ghiNhanLichSuXem(int $userId, int $maCT)
    {
        // Tên bảng là 'lichsuxem' (không có gạch dưới giữa lich và su)
        DB::table('lichsuxem')->updateOrInsert(
            [
                'Ma_ND' => $userId,
                'Ma_CT' => $maCT
            ],
            [
                'ThoiGianXem' => now(),
            ]
        );
    }

    // Thảo - Lấy lịch sử xem
    public function layLichSuXemCuaUser(int $userId, int $limit = 10)
    {
        return DB::table('lichsuxem') // Tên bảng: lichsuxem
            ->join('congthuc', 'lichsuxem.Ma_CT', '=', 'congthuc.Ma_CT')
            ->join('nguoidung', 'congthuc.Ma_ND', '=', 'nguoidung.Ma_ND')
            ->where('lichsuxem.Ma_ND', $userId)
            ->where('congthuc.TrangThai', 1)
            ->orderByDesc('lichsuxem.ThoiGianXem')
            ->limit($limit)
            ->select(
                'congthuc.Ma_CT',
                'congthuc.TenMon',
                'congthuc.HinhAnh',
                'congthuc.ThoiGianNau',
                'congthuc.DoKho',
                'congthuc.TrangThaiDuyet',
                'nguoidung.HoTen as TenTacGia',
                'lichsuxem.ThoiGianXem as NgayXem' // Alias để frontend dễ dùng
            )
            ->get();
    }

    public function xuLyTimKiem(Request $request)
    {
        $query = CongThuc::query()->with(['nguoiDung', 'danhMuc']);

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
