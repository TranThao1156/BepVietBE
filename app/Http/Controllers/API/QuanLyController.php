<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CongThuc;
use App\Models\DanhMuc;

class QuanLyController extends Controller
{
    // // Lấy bài chờ duyệt (Giả sử TrangThaiDuyet = 'ChoDuyet')
    // public function layBaiChoDuyet() {
    //     $ds = CongThuc::where('TrangThaiDuyet', 'ChoDuyet')
    //                   ->with('nguoi_dung')
    //                   ->get();
    //     return response()->json(['success' => true, 'data' => $ds]);
    // }

    // // Duyệt bài (Cập nhật TrangThaiDuyet = 'DaDuyet')
    // public function duyetBai($ma_ct) {
    //     $congthuc = CongThuc::find($ma_ct);
    //     if($congthuc) {
    //         $congthuc->update(['TrangThaiDuyet' => 'DaDuyet']);
    //         return response()->json(['success' => true, 'message' => 'Đã duyệt']);
    //     }
    //     return response()->json(['success' => false], 404);
    // }
    
    // // Thêm danh mục (Khớp ảnh 2)
    // public function themDanhMuc(Request $request) {
    //     DanhMuc::create([
    //         'TenDM' => $request->TenDM,
    //         'LoaiDM' => $request->LoaiDM,
    //         'TrangThai' => 1
    //     ]);
    //     return response()->json(['success' => true]);
    // 
}