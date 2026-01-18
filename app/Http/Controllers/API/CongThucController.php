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
use Illuminate\Support\Str;

// Thảo
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
            // Tạo tên file an toàn: time + slug tên gốc + đuôi file
            // Ví dụ: "Sushi Ngon.jpg" -> "170568999_sushi-ngon.jpg"
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $file->storeAs($folder, $filename, 'public');
            return $filename;
        }
        return null;
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
            // Thêm $index để đảm bảo không trùng lặp dù chạy cực nhanh
            foreach ($request->file('images') as $index => $file) {

                $nameSlug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $extension = $file->getClientOriginalExtension();

                $filename = time() . '_' . uniqid() . '_' . $nameSlug . '.' . $extension;

                $file->storeAs('img/BuocThucHien', $filename, 'public');
                $uploadedFiles[] = $filename;
            }

            return response()->json([
                'success' => true,
                'images' => $uploadedFiles,
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
}
