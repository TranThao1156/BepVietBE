<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\CongThuc;

class KhachController extends Controller
{
    // public function layDsCongThuc() {
    //     // Chỉ lấy bài đã duyệt
    //     $ds = CongThuc::where('TrangThaiDuyet', 'DaDuyet')->get();
    //     return response()->json(['success' => true, 'data' => $ds]);
    // }

    // public function chiTietCongThuc($ma_ct) {
    //     $ct = CongThuc::where('Ma_CT', $ma_ct)->first();
    //     return response()->json(['success' => true, 'data' => $ct]);
    // }
}