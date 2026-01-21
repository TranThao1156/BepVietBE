<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CongThuc;
use App\Models\DanhMuc;
use App\Models\LoaiMon;
use App\Models\VungMien;
use App\Services\CongThucService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BinhLuan;

class CongThucController extends Controller
{
    protected $congThucService;

    public function __construct(CongThucService $congThucService)
    {
        $this->congThucService = $congThucService;
    }
    // Validate chung cho thêm và sửa công thức

    private function getValidationRules()
    {
        return [
            'TenMon' => 'required|string|max:255',
            'MoTa' => 'nullable|string',
            'KhauPhan' => 'required|integer|min:1',
            'DoKho' => 'required|string|min:1|max:50',
            'ThoiGianNau' => 'required|integer|min:1',
            'HinhAnh' => 'nullable|image|max:5120', // Giới hạn 5MB
            'Ma_VM' => 'required|exists:vungmien,Ma_VM',
            'Ma_LM' => 'required|exists:loaimon,Ma_LM',
            'Ma_DM' => 'required|exists:danhmuc,Ma_DM',
            'NguyenLieu' => 'required|array|min:1',
            'NguyenLieu.*.TenNguyenLieu' => 'required|string|max:255',
            'NguyenLieu.*.DonViDo' => 'required|string|max:50',
            'NguyenLieu.*.DinhLuong' => 'required|numeric|min:0.1',
            'BuocThucHien' => 'required|array|min:1',
            'BuocThucHien.*.STT' => 'required|integer|min:1',
            'BuocThucHien.*.NoiDung' => 'required|string',
            'BuocThucHien.*.HinhAnh' => 'nullable|string',
        ];
    }

    // Hàm upload và chuẩn hóa file ảnh
    private function handleUploadImage(Request $request, $fieldName, $folder)
    {
        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);

            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $file->storeAs($folder, $filename, 'public');
            return $filename;
        }
        return null;
    }

    public function timKiem(Request $request)
    {
        // Gọi sang Service xử lý
        $ketQua = $this->congThucService->xuLyTimKiem($request);

        return response()->json([
            'success' => true,
            'data' => $ketQua
        ]);
    }

    // Thi - Lấy danh sách công thức mới nhất (4 món mới nhất)
    public function layDSCongThucMoi()
    {
        $data = $this->congThucService->layDSCongThucMoi();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách món mới thành công',
            'data' => $data
        ], 200);
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
        $data = $this->congThucService->layDSCongThucNoiBat();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách món nổi bật thành công',
            'data' => $data
        ], 200);
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

    // Thảo - Gợi ý nguyên liệu
    public function goiYNguyenLieu(Request $request)
    {
        $keyword = $request->get('q'); // Lấy từ khóa từ ?q=thit

        if (!$keyword) {
            return response()->json([]);
        }

        $data = $this->congThucService->timKiemNguyenLieu($keyword);

        return response()->json($data);
    }

    // Thảo - Thêm công thức
    public function themCongThuc(Request $request)
    {
        $request->validate($this->getValidationRules());
        $user = $request->user(); //  Lấy mã người dùng đăng nhập

        // Xử lý upload ảnh bìa
        $pathHinhAnh = $this->handleUploadImage($request, 'HinhAnh', 'img/CongThuc');
        if ($pathHinhAnh) {
            $request->merge(['HinhAnh' => $pathHinhAnh]);
        }
        // Kiểm tra đăng nhập
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
        $this->congThucService->tangLuotXem($id, $request);

        $user = $request->user('sanctum'); // Kiểm tra user token

        if ($user) {
            $this->congThucService->ghiNhanLichSuXem($user->Ma_ND, $id);
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
            return response()->json(['message' => 'Unauthenticated'], 401);
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

    // Thảo - Upload ảnh các bước (Không trùng ảnh)
    public function uploadAnhBuoc(Request $request)
    {
        // 1. Chỉ nhận 1 file (name là 'image')
        $request->validate([
            'image' => 'required|image|max:5120' // Max 5MB
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // 2. Tạo tên file dựa trên nội dung ảnh (MD5 Hash)
            // Cách này đảm bảo 100% không bao giờ lưu 2 ảnh giống hệt nhau
            $md5Hash = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $filename = $md5Hash . '.' . $extension;

            $path = 'img/BuocThucHien/' . $filename;

            // 3. Kiểm tra nếu ảnh này đã có trên server chưa
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                // Nếu chưa có thì mới lưu
                $file->storeAs('img/BuocThucHien', $filename, 'public');
            }

            // Trả về 1 tên file duy nhất
            return response()->json([
                'success' => true,
                'image' => $filename,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Lỗi upload'], 400);
    }

    // Thảo - Sửa công thức
    public function suaCongThuc(Request $request, $id)
    {
        // Lấy mã người dùng
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        $recipe = CongThuc::find($id);

        if (!$recipe) {
            return response()->json(['error' => 'Công thức không tồn tại'], 404);
        }

        // KIỂM TRA QUYỀN SỞ HỮU (Quan trọng nhất)
        if ((int)$recipe->Ma_ND !== (int)$user->Ma_ND) {
            return response()->json(['error' => 'Không có quyền sửa bài này'], 403);
        }


        $request->validate($this->getValidationRules());

        // Xử lý ảnh bìa MỚI (Nếu có)
        $pathHinhAnh = $this->handleUploadImage($request, 'HinhAnh', 'img/CongThuc');
        if ($pathHinhAnh) {
            $request->merge(['HinhAnh' => $pathHinhAnh]);
        }

        try {
            $congThuc = $this->congThucService->updateCongThuc($id, $request, $user);
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $congThuc]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Thảo - Lấy danh sách công thức đã xem
    public function layDsDaXem(Request $request)
    {
        $user = $request->user(); // Đã qua middleware auth:sanctum

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        try {
            $data = $this->congThucService->layLichSuXemCuaUser($user->Ma_ND, 10);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy lịch sử: ' . $e->getMessage()
            ], 500);
        }
    }


    // Thảo - Xóa công thức
    public function xoaCongThuc($id, Request $request)
    {
        try {
            $user = $request->user(); // đã qua auth middleware
            $this->congThucService->xoaCongThuc($id, $user);
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa công thức thành công'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    //Khanh - Hiển thị bình luận công thức
    public function showBinhLuan($id)
    {
        try {
            // Kiểm tra công thức tồn tại
            $congThuc = CongThuc::find($id);
            if (!$congThuc) {
                return response()->json(['message' => 'Không tìm thấy công thức'], 404);
            }

            // Lấy danh sách bình luận
            $binhLuan = BinhLuan::where('Ma_CT', $id)
                ->whereNull('parent_id') // 1. Chỉ lấy bình luận CHA (Gốc)
                ->with(['nguoiDung', 'replies.nguoiDung']) // 2. Lấy kèm Con và Info người dùng
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Lấy bình luận thành công',
                'data' => $binhLuan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi server',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
