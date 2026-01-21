<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Cookbook;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // Thêm dòng này để dùng Auth
use App\Services\CookbookService;

class CookbookController extends Controller
{
    protected $cookbookService;
    public function __construct(CookbookService $cookbookService)
    {
        $this->cookbookService = $cookbookService;
    }
    public function store(Request $request)
    {
        // 1. Validate dữ liệu
        // LƯU Ý: Đã bỏ dòng check 'Ma_ND' vì server tự biết ai đang đăng nhập
        $validator = Validator::make($request->all(), [
            'TenCookBook' => 'required|string|max:255',
            'TrangThai'   => 'required|integer|in:0,1', // Thêm in:0,1 để chặt chẽ hơn
            'AnhBia'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Lấy ID người dùng từ Token (QUAN TRỌNG)
        // auth()->id() sẽ lấy khóa chính của user đang đăng nhập
        $userId = auth('sanctum')->id();
        
        // Kiểm tra an toàn: Nếu chưa đăng nhập (dù middleware đã chặn, nhưng cứ check cho chắc)
        if (!$userId) {
             return response()->json(['message' => 'Không xác định được người dùng'], 401);
        }

        // 3. Xử lý upload ảnh
        $tenAnh = null; 
        if ($request->hasFile('AnhBia')) {
            $file = $request->file('AnhBia');
            $tenAnh = time() . '_' . $file->getClientOriginalName(); 
            $file->move(public_path('storage/img/CookBook'), $tenAnh);
        }

        // 4. Lưu vào Database
        try {
            $cookbook = Cookbook::create([
                'Ma_ND'       => $userId, // Dùng biến $userId vừa lấy từ Token
                'TenCookBook' => $request->TenCookBook,
                'TrangThai'   => $request->TrangThai,
                'AnhBia'      => $tenAnh 
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Tạo Cookbook thành công',
                'data' => $cookbook
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
        }

        // Gọi service để xử lý ẩn
        // Lưu ý: Dùng $user->Ma_ND khớp với logic trong function danhSach
        $result = $this->cookbookService->anCookbook($id, $user->Ma_ND);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa bộ sưu tập thành công.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bộ sưu tập hoặc bạn không có quyền xóa.'
            ], 404);
        }
    }
    public function danhSach(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: Token hết hạn hoặc không hợp lệ.',
                'debug_info' => 'User is NULL'
            ], 401);
        }

        try {
            // Gọi Service để lấy dữ liệu đã được format đẹp
            $data = $this->cookbookService->layDanhSachTheoUser($user->Ma_ND);

            return response()->json([
                'success' => true,
                'debug_user_id' => $user->id,
                'id_nguoi_dung_thuc_te' => $user->Ma_ND,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function show($id)
    {
        try {
            // 1. Tìm Cookbook và load kèm:
            // - congthucs: Danh sách món ăn
            // - congthucs.nguoidung: Tác giả của từng món ăn (Eager Loading để tối ưu)
            $cookbook = Cookbook::with(['congthucs.nguoidung'])
                                ->where('Ma_CookBook', $id)
                                ->first();

            if (!$cookbook) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy Cookbook'], 404);
            }

            // 2. Xử lý link ảnh bìa Cookbook
            $anhBia = $cookbook->AnhBia;
            if ($anhBia && !str_starts_with($anhBia, 'http')) {
                $anhBia = url('storage/img/CookBook/' . $anhBia);
            }

            // 3. Xử lý danh sách món ăn
            $recipes = $cookbook->congthucs->map(function($ct) {
                
                // --- XỬ LÝ ẢNH MÓN ĂN ---
                $img = $ct->HinhAnh;
                if ($img && !str_starts_with($img, 'http')) {
                    $img = url('storage/img/CongThuc/' . $img);
                }

                // --- XỬ LÝ TÁC GIẢ (CÓ KIỂM TRA NULL) ---
                $authorName = 'Ẩn danh';
                $authorAvatar = 'https://placehold.co/100?text=U';

                // Kiểm tra xem món ăn có liên kết được với người dùng không
                if ($ct->nguoidung) {
                    $authorName = $ct->nguoidung->HoTen; // Lấy tên thật
                    
                    // Xử lý avatar tác giả
                    if ($ct->nguoidung->AnhDaiDien) {
                        $ava = $ct->nguoidung->AnhDaiDien;
                        if (!str_starts_with($ava, 'http')) {
                            $authorAvatar = url('storage/img/NguoiDung/' . $ava);
                        } else {
                            $authorAvatar = $ava;
                        }
                    }
                }

                return [
                    'Ma_CT'        => $ct->Ma_CT,
                    'TenMon'       => $ct->TenMon,
                    'HinhAnh'      => $img ?: 'https://placehold.co/600x400?text=No+Food+Img',
                    'ThoiGianNau'  => $ct->ThoiGianNau ?? 0,
                    
                    // 👇 Dữ liệu thật lấy từ logic ở trên
                    'TacGia'       => $authorName, 
                    'AvatarTacGia' => $authorAvatar,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'info' => [
                        'id'          => $cookbook->Ma_CookBook,
                        'TenCookBook' => $cookbook->TenCookBook,
                        'AnhBia'      => $anhBia ?: 'https://placehold.co/600x400?text=No+Image',
                        'TrangThai'   => $cookbook->TrangThai,
                        'SoLuongMon'  => $recipes->count()
                    ],
                    'recipes' => $recipes
                ]
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi Server: ' . $e->getMessage(),
                'line' => $e->getLine()
            ], 200);
        }
    }
    public function xoaMonKhoiCookbook($cookbookId, $recipeId)
    {
        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['message' => 'Chưa đăng nhập'], 401);

        try {
            // Gọi Service để xử lý logic xóa
            $result = $this->cookbookService->xoaMonKhoiCookbook(
                $cookbookId, 
                $recipeId, 
                $user->Ma_ND
            );

            if ($result) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Đã xóa món ăn khỏi bộ sưu tập.'
                ]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => 'Không tìm thấy hoặc không có quyền xóa.'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        // 1. Validate (Cho phép tên và ảnh)
        $validator = Validator::make($request->all(), [
            'TenCookBook' => 'required|string|max:255',
            'AnhBia'      => 'nullable|image|max:2048', // Ảnh tối đa 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['message' => 'Chưa đăng nhập'], 401);

        // 2. Gọi Service
        // Chú ý: $request->file('AnhBia') sẽ lấy file thật
        $updatedCookbook = $this->cookbookService->capNhatCookbook(
            $id,
            $request->all(),
            $request->file('AnhBia'), // Truyền file ảnh riêng
            $user->Ma_ND
        );

        if ($updatedCookbook) {
            // Xử lý link ảnh để trả về frontend hiển thị ngay
            $anhBiaUrl = $updatedCookbook->AnhBia;
            if ($anhBiaUrl && !str_starts_with($anhBiaUrl, 'http')) {
                $anhBiaUrl = url('storage/img/CookBook/' . $anhBiaUrl);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công',
                'data' => [
                    'TenCookBook' => $updatedCookbook->TenCookBook,
                    'AnhBia' => $anhBiaUrl // Trả về URL đầy đủ để React hiển thị
                ]
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'Lỗi cập nhật'], 404);
        }
    }
    public function myCookbooks(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        // Tận dụng hàm layCookbookCuaUser có sẵn trong Service của bạn
        // Hàm này trả về raw model có withCount, rất phù hợp cho dropdown select
        $data = $this->cookbookService->layCookbookCuaUser($user->Ma_ND);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function themMonVaoCookbook(Request $request)
    {
        // 1. Validate
        $validator = Validator::make($request->all(), [
            'Ma_CookBook' => 'required|integer',
            'Ma_CT'       => 'required|integer|exists:congthuc,Ma_CT',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        // 2. Gọi Service xử lý
        $result = $this->cookbookService->themMonVaoCookbook(
            $user->Ma_ND,
            $request->Ma_CookBook,
            $request->Ma_CT
        );

        // 3. Trả về kết quả
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ], 200);
        } else {
            // Lỗi nghiệp vụ (ví dụ: đã tồn tại, không phải chủ sở hữu)
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400); 
        }
    }
}   