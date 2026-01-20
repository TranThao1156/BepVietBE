<?php

namespace App\Services;

use App\Models\BinhLuan;
use Illuminate\Support\Facades\Auth;
use Exception;

class BinhLuanService
{
    public function taoBinhLuanCongThuc($data)
    {
        $userId = Auth::user()->Ma_ND;
        $maCT   = (int) $data['Ma_CT'];
        $parent_id = null;

        if (isset($data['Parent_ID']) && $data['Parent_ID'] !== null && $data['Parent_ID'] !== '') {
            $parent_id = (int) $data['Parent_ID'];

            $parent = BinhLuan::find($parent_id);
            if (!$parent) {
                throw new Exception("Bình luận cha không tồn tại.", 404);
            }

            // Quan trọng: phải cùng công thức
            if ($parent->Ma_CT !== $maCT) {
                throw new Exception("Không thể trả lời bình luận của công thức khác.", 403);
            }
        }

        $binhLuan = BinhLuan::create([
            'Ma_CT'     => $maCT,
            'Ma_ND'     => $userId,
            'NoiDungBL' => $data['NoiDungBL'],
            'LoaiBL'    => 1,
            'TrangThai' => 1,
            'Ma_Blog'   => null,
            'Parent_ID' => $parent_id,
        ]);

        return $binhLuan;
    }
    public function suaBinhLuan($id, $noiDungMoi)
    {
        $binhLuan = BinhLuan::find($id);

        if (!$binhLuan) {
            throw new Exception("Bình luận không tồn tại.", 404);
        }

        // Kiểm tra quyền sở hữu
        if ($binhLuan->Ma_ND !== Auth::user()->Ma_ND) {
            throw new Exception("Bạn không có quyền chỉnh sửa bình luận này.", 403);
        }

        $binhLuan->NoiDungBL = $noiDungMoi;
        $binhLuan->save();

        return $binhLuan;
    }

    /**
     * Xóa bình luận
     */
    public function xoaBinhLuan($id)
    {
        $binhLuan = BinhLuan::find($id);

        if (!$binhLuan) {
            throw new Exception("Bình luận không tồn tại.", 404);
        }

        // Kiểm tra quyền sở hữu (Hoặc Admin vai trò = 0 có thể xóa)
        // Ở đây mình check chặt: chỉ chủ bình luận mới được xóa
        if ($binhLuan->Ma_ND !== Auth::user()->Ma_ND) {
            throw new Exception("Bạn không có quyền xóa bình luận này.", 403);
        }

        return $binhLuan->delete();
    }
    //Khanh - Lấy danh sách bình luận theo công thức, bao gồm cả câu trả lời đệ quy
    public function layDanhSachBinhLuan($maCT)
    {
        return BinhLuan::where('Ma_CT', $maCT)
            ->whereNull('Parent_ID') // Chỉ lấy bình luận gốc (Cha)
            ->with(['nguoiDung', 'replies']) // Chỉ cần load relation 'replies'
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
