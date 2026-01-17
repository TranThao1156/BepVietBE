<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\NguoiDung;
use App\Models\CongThuc;
use App\Models\Blog;     // Đảm bảo bạn đã có Model Blog
use App\Models\Cookbook; // Đảm bảo bạn đã có Model Cookbook

class NguoiDungController extends Controller
{
    // // ====================================================
    // // 1. QUẢN LÝ TÀI KHOẢN CÁ NHÂN
    // // ====================================================

    // // Lấy thông tin profile
    // public function layThongTin()
    // {
    //     // Lấy user đang đăng nhập từ Token
    //     $user = auth()->user(); 
    //     return response()->json(['success' => true, 'data' => $user]);
    // }

    // // Cập nhật thông tin cá nhân
    // public function capNhatThongTin(Request $request)
    // {
    //     $user = auth()->user(); // Lấy model User hiện tại

    //     // Validate dữ liệu
    //     $request->validate([
    //         'HoTen' => 'required|string|max:255',
    //         // Email phải unique nhưng trừ cái Email hiện tại của user này ra
    //         'Email' => 'required|email|unique:nguoidung,Email,' . $user->Ma_ND . ',Ma_ND',
    //     ]);

    //     // Cập nhật các trường cho phép
    //     $user->update([
    //         'HoTen'      => $request->HoTen,
    //         'Email'      => $request->Email,
    //         'Sdt'        => $request->Sdt,
    //         'DiaChi'     => $request->DiaChi,
    //         'GioiTinh'   => $request->GioiTinh,
    //         'AnhDaiDien' => $request->AnhDaiDien // Frontend gửi link ảnh sau khi upload
    //     ]);

    //     return response()->json([
    //         'success' => true, 
    //         'message' => 'Cập nhật thông tin thành công', 
    //         'data' => $user
    //     ]);
    // }

    // // Đổi mật khẩu
    // public function doiMatKhau(Request $request)
    // {
    //     $request->validate([
    //         'mat_khau_cu' => 'required',
    //         'mat_khau_moi' => 'required|min:6|confirmed', // Cần field mat_khau_moi_confirmation ở frontend
    //     ]);

    //     $user = auth()->user();

    //     // 1. Kiểm tra mật khẩu cũ có đúng không
    //     if (!Hash::check($request->mat_khau_cu, $user->MatKhau)) {
    //         return response()->json([
    //             'success' => false, 
    //             'message' => 'Mật khẩu cũ không chính xác'
    //         ], 400);
    //     }

    //     // 2. Cập nhật mật khẩu mới
    //     $user->update([
    //         'MatKhau' => Hash::make($request->mat_khau_moi)
    //     ]);

    //     return response()->json([
    //         'success' => true, 
    //         'message' => 'Đổi mật khẩu thành công'
    //     ]);
    // }

    // // ====================================================
    // // 2. QUẢN LÝ CÔNG THỨC CỦA TÔI (My Recipes)
    // // ====================================================

    // // Lấy danh sách công thức do chính mình tạo
    // public function layDsCongThucCuaToi()
    // {
    //     $user_id = auth()->id(); // Lấy Ma_ND
        
    //     // Lấy bài viết của User này, sắp xếp mới nhất
    //     $ds = CongThuc::where('Ma_ND', $user_id)
    //                   ->orderBy('created_at', 'desc')
    //                   ->get();

    //     return response()->json(['success' => true, 'data' => $ds]);
    // }

    // // Thêm công thức mới
    // public function themCongThuc(Request $request)
    // {
    //     $request->validate([
    //         'TenMon' => 'required|string|max:255',
    //         'MoTa' => 'required',
    //         // Các validate khác...
    //     ]);

    //     $congthuc = CongThuc::create([
    //         'Ma_ND'          => auth()->id(), // Tự động gắn ID người đăng
    //         'TenMon'         => $request->TenMon,
    //         'MoTa'           => $request->MoTa,
    //         'NguyenLieu'     => $request->NguyenLieu, // Lưu dạng JSON hoặc String
    //         'CacBuoc'        => $request->CacBuoc,    // Lưu dạng JSON hoặc String
    //         'HinhAnh'        => $request->HinhAnh,
    //         'KhauPhan'       => $request->KhauPhan,
    //         'ThoiGianNau'    => $request->ThoiGianNau,
    //         'DoKho'          => $request->DoKho,
    //         'Ma_VM'          => $request->Ma_VM,
    //         'Ma_LM'          => $request->Ma_LM,
    //         'Ma_DM'          => $request->Ma_DM,
            
    //         // QUAN TRỌNG: Mặc định là 'ChoDuyet' hoặc 0 tùy quy ước database của bạn
    //         'TrangThaiDuyet' => 'ChoDuyet', 
    //         'TrangThai'      => 1, // 1 là hiện (nhưng phải chờ duyệt), 0 là ẩn (bản nháp)
    //         'SoLuotXem'      => 0
    //     ]);

    //     return response()->json([
    //         'success' => true, 
    //         'message' => 'Đã gửi công thức, vui lòng chờ duyệt', 
    //         'data' => $congthuc
    //     ]);
    // }

    // // Xem chi tiết công thức CỦA TÔI để sửa (Edit)
    // public function chiTietCongThuc($ma_ct)
    // {
    //     // Phải tìm đúng ID công thức VÀ phải là của user đang đăng nhập
    //     // Tránh trường hợp User A đoán ID sửa bài của User B
    //     $ct = CongThuc::where('Ma_CT', $ma_ct)
    //                   ->where('Ma_ND', auth()->id())
    //                   ->first();

    //     if (!$ct) {
    //         return response()->json(['success' => false, 'message' => 'Không tìm thấy công thức'], 404);
    //     }

    //     return response()->json(['success' => true, 'data' => $ct]);
    // }

    // // Cập nhật công thức
    // public function capNhatCongThuc(Request $request, $ma_ct)
    // {
    //     $ct = CongThuc::where('Ma_CT', $ma_ct)->where('Ma_ND', auth()->id())->first();

    //     if (!$ct) return response()->json(['success' => false], 404);

    //     $ct->update([
    //         'TenMon'      => $request->TenMon,
    //         'MoTa'        => $request->MoTa,
    //         'NguyenLieu'  => $request->NguyenLieu,
    //         'CacBuoc'     => $request->CacBuoc,
    //         'HinhAnh'     => $request->HinhAnh,
    //         'KhauPhan'    => $request->KhauPhan,
    //         'ThoiGianNau' => $request->ThoiGianNau,
    //         'DoKho'       => $request->DoKho,
    //         'Ma_VM'       => $request->Ma_VM,
    //         'Ma_LM'       => $request->Ma_LM,
    //         'Ma_DM'       => $request->Ma_DM,
            
    //         // Logic tùy chọn: Sửa xong có cần duyệt lại không? 
    //         // Nếu có thì set lại thành 'ChoDuyet'
    //         'TrangThaiDuyet' => 'ChoDuyet' 
    //     ]);

    //     return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
    // }

    // // Xóa công thức
    // public function xoaCongThuc($ma_ct)
    // {
    //     $ct = CongThuc::where('Ma_CT', $ma_ct)->where('Ma_ND', auth()->id())->first();

    //     if (!$ct) return response()->json(['success' => false], 404);

    //     $ct->delete();
    //     return response()->json(['success' => true, 'message' => 'Đã xóa công thức']);
    // }

    // // ====================================================
    // // 3. QUẢN LÝ BLOG (Tương tự công thức)
    // // ====================================================
    // // Bạn có thể copy logic của công thức ở trên và đổi Model thành Blog
    // // Ví dụ: layDsBlogCuaToi, themBlog, ...

    
    // // ====================================================
    // // 4. COOKBOOK (BỘ SƯU TẬP)
    // // ====================================================

    // public function layDsCookbook()
    // {
    //     $cb = Cookbook::where('Ma_ND', auth()->id())->get();
    //     return response()->json(['success' => true, 'data' => $cb]);
    // }

    // public function taoCookbook(Request $request)
    // {
    //     $request->validate(['TenBST' => 'required']);
        
    //     $cb = Cookbook::create([
    //         'Ma_ND'  => auth()->id(),
    //         'TenBST' => $request->TenBST,
    //         // 'MoTa' => $request->MoTa
    //     ]);
    //     return response()->json(['success' => true, 'data' => $cb]);
    // }

    // public function xoaCookbook($ma_bst)
    // {
    //     $cb = Cookbook::where('Ma_BST', $ma_bst)->where('Ma_ND', auth()->id())->first();
    //     if ($cb) {
    //         $cb->delete();
    //         return response()->json(['success' => true, 'message' => 'Đã xóa']);
    //     }
    //     return response()->json(['success' => false], 404);
    // }
}