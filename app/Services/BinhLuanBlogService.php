<?php

namespace App\Services;

use App\Models\BinhLuan;
use Illuminate\Support\Facades\Auth;
use App\Events\BinhLuanBlogMoi; // Import Event
use Exception;

class BinhLuanBlogService
{
    // 1. THÊM BÌNH LUẬN (Có Realtime)
    public function themBinhLuan($data)
    {
        $binhLuan = BinhLuan::create([
            'Ma_ND'      => Auth::id(),
            'Ma_Blog'    => $data['Ma_Blog'],
            'Parent_ID'  => $data['Parent_ID'] ?? null, //Thêm cột Parent_ID để hỗ trợ "Trả lời"
            'Ma_CT'      => null,
            'NoiDungBL'  => $data['NoiDungBL'],
            'LoaiBL'     => 1, // 1 là Blog
            'TrangThai'  => 1  // 1 là Hoạt động
        ]);

        // Load thông tin người dùng để trả về frontend
        $binhLuanFull = $binhLuan->load('nguoiDung');

        //  BẮN REALTIME: Gửi cho người khác trừ người vừa comment
        broadcast(new BinhLuanBlogMoi($binhLuanFull))->toOthers();

        return $binhLuanFull;
    }

    // 2. SỬA BÌNH LUẬN
    public function suaBinhLuan($id, $noiDungMoi)
    {
        $binhLuan = BinhLuan::find($id);

        if (!$binhLuan) throw new Exception("Không tìm thấy bình luận.", 404);
        
        // Check chính chủ
        if ($binhLuan->Ma_ND !== Auth::id()) {
            throw new Exception("Không có quyền sửa.", 403);
        }

        $binhLuan->NoiDungBL = $noiDungMoi;
        $binhLuan->save();

        return $binhLuan;
    }

    // 3. XÓA BÌNH LUẬN
    public function xoaBinhLuan($id)
    {
        $binhLuan = BinhLuan::find($id);

        if (!$binhLuan) throw new Exception("Không tìm thấy bình luận.", 404);

        $user = Auth::user();
        // Trâm - đã sửa: dự án dùng khóa chính Ma_ND (không phải id) nên phải lấy đúng userId
        $userId = $user?->Ma_ND ?? Auth::id();
        // Check: Chính chủ HOẶC Admin (VaiTro=0) mới được xóa
        if ($binhLuan->Ma_ND !== $userId && $user->VaiTro !== 0) {
            throw new Exception("Không có quyền xóa.", 403);
        }

        $binhLuan->TrangThai = 0; // Xóa mềm
        $binhLuan->save();

        return true;
    }

    // 4. LẤY DANH SÁCH BÌNH LUẬN (Theo Blog)
    public function layDanhSachBinhLuan($maBlog)
    {
        return BinhLuan::with('nguoiDung')
            ->where('Ma_Blog', $maBlog)
            ->where('TrangThai', 1)
            ->whereNull('Parent_ID') // Lấy bình luận gốc trước
            ->with(['replies' => function($query) {
                $query->with('nguoiDung')->where('TrangThai', 1); // Lấy kèm các câu trả lời
            }])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}