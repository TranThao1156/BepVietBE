<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BuocThucHien;
use App\Models\CongThuc;
use App\Models\DanhMuc;
use App\Models\LoaiMon;
use App\Models\NguyenLieu;
use App\Models\VungMien;
use Illuminate\Http\Request;
use App\Services\CongThucService;
use Illuminate\Support\Facades\Cache;

// Thảo
class CongThucController extends Controller
{
    protected $congThucService;

    public function __construct(CongThucService $congThucService)
    {
        $this->congThucService = $congThucService;
    }
    // 16/01/2026 Thi tạo API lấy công thức cho trang chủ
    // Lấy danh sách công thức mới nhất (4 món mới nhất)
    public function layDSCongThucMoi()
    {
            $data = $this->congThucService->layDSCongThucMoi();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách món mới thành công',
                'data' => $data
            ], 200);
    }
    // Lấy danh sách công thức được xem nhiều nhất (4 món nổi bật)
    public function layDSCongThucNoiBat()
    {
            $data = $this->congThucService->layDSCongThucNoiBat();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách món nổi bật thành công',
                'data' => $data
            ], 200);
    }
    // Hiển thị 1 công thức nổi bật theo vùng miền ( miền bắc, miền trung, miền nam )
    public function layCongThucNoiBatTheoMien(string $mien)
    {
        $data = $this->congThucService->layCongThucNoiBatTheoMien($mien);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy công thức nổi bật cho miền ' . $mien
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Lấy công thức nổi bật miền ' . $mien . ' thành công',
            'data' => $data
        ], 200);
    }
    // Thảo - Lấy danh sách công thức
    public function index(Request $request)
    {
        $duLieu = $this->congThucService->layDanhSachCongThuc([
            'Ma_DM'   => $request->Ma_DM,
            'Ma_LM'   => $request->Ma_LM,
            'keyword' => $request->keyword,
            'sap_xep' => $request->sap_xep,
            'limit'   => $request->limit
        ]);

        return response()->json([
            'message' => 'Lấy danh sách công thức thành công',
            'data'    => $duLieu
        ]);
    }

    // Thảo - Thêm công thức
    public function themCongThuc(Request $request)
    {
        $request->validate([
            'TenMon' => 'required|string|max:255',
            'MoTa' => 'nullable|string',
            'KhauPhan' => 'required|integer|min:1',
            'DoKho' => 'required|string|min:1|max:50',
            'ThoiGianNau' => 'required|integer|min:1',
            'HinhAnh' => 'nullable|image',

            'Ma_VM' => 'required|exists:vungmien,Ma_VM',
            'Ma_LM' => 'required|exists:loaimon,Ma_LM',
            'Ma_DM' => 'required|exists:danhmuc,Ma_DM',

            // Nguyên liệu
            'NguyenLieu' => 'required|array|min:1',
            'NguyenLieu.*.TenNguyenLieu' => 'required|string|max:255',
            'NguyenLieu.*.DonViDo' => 'required|string|max:50',
            'NguyenLieu.*.DinhLuong' => 'required|numeric|min:0.1',

            // Bước thực hiện
            'BuocThucHien' => 'required|array|min:1',
            'BuocThucHien.*.STT' => 'required|integer|min:1',
            'BuocThucHien.*.NoiDung' => 'required|string',
            'BuocThucHien.*.HinhAnh' => 'nullable|string',
        ]);


        $user = $request->user(); //  ĐÚNG – Sanctum hiểu guard

        // Xử lý upload ảnh
        $pathHinhAnh = null;
        if ($request->hasFile('HinhAnh')) {
            $file = $request->file('HinhAnh');

            // 1. Lấy tên file gốc (VD: pho-bo.jpg)
            $filename = $file->getClientOriginalName();

            // 2. Lưu file vào thư mục với tên gốc (thay vì tên mã hóa ngẫu nhiên)
            // Cấu trúc: storeAs('thư_mục', 'tên_file', 'disk')
            $file->storeAs('img/CongThuc', $filename, 'public');

            // 3. Chỉ lưu tên file vào biến để đưa vào CSDL (bỏ phần img/CongThuc/ đi)
            $pathHinhAnh = $filename;
        }

        // Merge vào request
        $request->merge(['HinhAnh' => $pathHinhAnh]);

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $congThuc = $this->congThucService->createCongThuc($request, $user);

        return response()->json([
            'success' => true,
            'data' => $congThuc
        ], 201);
    }

    // Thảo - Chi tiết công thức
    public function show($id, Request $request)
    {
        $congThuc = $this->congThucService->chiTietCongThuc($id);
        $key = 'view_ct_' . $id . '_' . request()->ip();
        if (!Cache::has($key)) {
            CongThuc::where('Ma_CT', $id)->increment('SoLuotXem');
            // Hết 10p thì được +1 lượt xem
            Cache::put($key, true, now()->addMinutes(10));
        }
        return response()->json([
            'message' => 'Lấy chi tiết công thức thành công',
            'data' => $congThuc
        ]);
    }

    // Thảo - Lấy danh sách công thức theo người dùng (Đang đăng nhập)
    public function CongThucCuaToi(Request $request)
    {
        // Lấy User hiện tại đang đăng nhập (Sanctum)
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Bạn chưa đăng nhập'], 401);
        }

        // Lấy tham số limit từ URL (ví dụ ?limit=10), mặc định là 5
        $limit = $request->get('limit', 5);

        // Gọi Service
        $data = $this->congThucService->LayDsCongThucByUser($user->Ma_ND, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách công thức của bạn thành công',
            'data' => $data
        ]);
    }

    // Thảo
    public function layTuyChon()
    {
        $danhmuc = DanhMuc::where('TrangThai', 1)->get(); // Chỉ lấy cái đang hoạt động
        $loaimon = LoaiMon::all();
        $vungmien = VungMien::all();

        return response()->json([
            'danhmuc' => $danhmuc,
            'loaimon' => $loaimon,
            'vungmien' => $vungmien
        ]);
    }

    public function uploadAnhBuoc(Request $request)
    {
        // Validate mảng hình ảnh
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:5120' // Mỗi ảnh max 5MB
        ]);

        $uploadedFiles = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Tạo tên file unique: timestamp_tên_gốc
                $filename = time() . '_' . $file->getClientOriginalName();

                // Lưu vào thư mục public/storage/img/BuocThucHien
                $file->storeAs('img/BuocThucHien', $filename, 'public');

                // Thêm tên file vào mảng kết quả
                $uploadedFiles[] = $filename;
            }

            return response()->json([
                'success' => true,
                'images' => $uploadedFiles, // Trả về mảng: ['123_anh1.jpg', '123_anh2.jpg']
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Lỗi upload'], 400);
    }

    // Thảo - Sửa công thức
    public function suaCongThuc(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        // Validate (Giống thêm mới, nhưng ảnh bìa là nullable)
        $request->validate([
            'TenMon' => 'required|string|max:255',
            'MoTa' => 'nullable|string',
            'KhauPhan' => 'required|integer|min:1',
            'ThoiGianNau' => 'required|integer|min:1',
            'HinhAnh' => 'nullable|image', // Ảnh bìa có thể không gửi nếu không đổi
            
            // ... (Các validate khác giữ nguyên như hàm themCongThuc) ...
            'NguyenLieu' => 'required|array',
            'BuocThucHien' => 'required|array',
        ]);

        // Xử lý ảnh bìa MỚI (Nếu có)
        $pathHinhAnh = null;
        if ($request->hasFile('HinhAnh')) {
            $file = $request->file('HinhAnh');
            $filename = $file->getClientOriginalName();
            $file->storeAs('img/CongThuc', $filename, 'public');
            $pathHinhAnh = $filename;
            
            // Merge tên ảnh mới vào request
            $request->merge(['HinhAnh' => $pathHinhAnh]);
        } else {
            // Nếu không gửi ảnh, xóa trường HinhAnh khỏi request để Service không update null
            // (Service đã check if input has HinhAnh)
        }

        try {
            $congThuc = $this->congThucService->updateCongThuc($id, $request, $user);
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $congThuc]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}
