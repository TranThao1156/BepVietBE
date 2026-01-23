<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\BinhLuan;
use Illuminate\Support\Facades\Auth;
use App\Events\BinhLuanBlogMoi; // Import Event
use Exception;

class BinhLuanBlogService
{
    // Trâm - đã sửa: logic chuẩn kiểm duyệt - chỉ blog đã "Chấp nhận" mới được xem/thêm bình luận
    private function assertBlogDuocPhepBinhLuan(int $maBlog): void
    {
        $blog = Blog::select(['Ma_Blog', 'Ma_ND', 'TrangThai', 'TrangThaiDuyet'])->find($maBlog);
        if (!$blog || (int) $blog->TrangThai !== 1) {
            throw new Exception('Không tìm thấy bài viết.', 404);
        }

        if ($blog->TrangThaiDuyet !== 'Chấp nhận') {
            throw new Exception('Bài viết chưa được duyệt.', 403);
        }
    }

    // Trâm - đã thêm: build cây bình luận nhiều cấp cho Blog (không phụ thuộc đệ quy trong Model)
    private function buildCommentTree($items)
    {
        $itemsById = [];
        foreach ($items as $item) {
            $arr = $item->toArray();
            $arr['replies'] = [];
            $itemsById[$item->Ma_BL] = $arr;
        }

        $roots = [];
        foreach ($items as $item) {
            $id = $item->Ma_BL;
            $parentId = $item->Parent_ID;

            if ($parentId && isset($itemsById[$parentId])) {
                $itemsById[$parentId]['replies'][] = &$itemsById[$id];
            } else {
                $roots[] = &$itemsById[$id];
            }
        }

        return $roots;
    }

    // 1. THÊM BÌNH LUẬN (Có Realtime)
    public function themBinhLuan($data)
    {
        // Trâm - đã thêm: hỗ trợ trả lời nhiều cấp (Parent_ID) và validate Parent_ID thuộc đúng bài viết
        $maBlog = (int) $data['Ma_Blog'];
        // Trâm - đã thêm: blog chưa duyệt thì không cho người lạ bình luận
        $this->assertBlogDuocPhepBinhLuan($maBlog);
        $parentId = null;
        if (isset($data['Parent_ID']) && $data['Parent_ID'] !== null && $data['Parent_ID'] !== '') {
            $parentId = (int) $data['Parent_ID'];
            $parent = BinhLuan::find($parentId);
            if (!$parent) {
                throw new Exception("Bình luận cha không tồn tại.", 404);
            }
            if ((int) $parent->Ma_Blog !== $maBlog) { // Phải cùng blog
                throw new Exception("Không thể trả lời bình luận của bài viết khác.", 403);
            }
        }
        // Trâm - đã thêm: dự án dùng khóa chính Ma_ND nên lấy userId theo Ma_ND để lưu bình luận đúng
        $userId = Auth::user()?->Ma_ND ?? Auth::id();
        $binhLuan = BinhLuan::create([
            'Ma_ND'      => $userId,
            'Ma_Blog'    => $maBlog,
            'Parent_ID'  => $parentId, //Thêm cột Parent_ID để hỗ trợ "Trả lời"
            'Ma_CT'      => null,
            'NoiDungBL'  => $data['NoiDungBL'],
            'LoaiBL'     => 1, // 1 là Blog
            'TrangThai'  => 1  // 1 là Hoạt động
        ]);
        // Load thông tin người dùng để trả về frontend
        $binhLuanFull = $binhLuan->load('nguoiDung');
        broadcast(new BinhLuanBlogMoi($binhLuanFull))->toOthers(); //REALTIME
        return $binhLuanFull;
    }

    // 2. SỬA BÌNH LUẬN
    public function suaBinhLuan($id, $noiDungMoi)
    {
        $binhLuan = BinhLuan::find($id);

        if (!$binhLuan) throw new Exception("Không tìm thấy bình luận.", 404);

        $userId = Auth::user()?->Ma_ND ?? Auth::id();
        
        // Check chính chủ
        if ($binhLuan->Ma_ND !== $userId) {
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
        // Check: chỉ chính chủ bình luận mới được xóa
        if ($binhLuan->Ma_ND !== $userId) {
            throw new Exception("Không có quyền xóa.", 403);
        }

        $binhLuan->TrangThai = 0; // Xóa mềm
        $binhLuan->save();

        return true;
    }

    // 4. LẤY DANH SÁCH BÌNH LUẬN (Theo Blog)
    public function layDanhSachBinhLuan($maBlog)
    {
        // Trâm - đã thêm: blog chưa duyệt thì không cho người lạ xem bình luận
        $this->assertBlogDuocPhepBinhLuan((int) $maBlog);

        // Trâm - đã thêm: lấy tất cả bình luận (TrangThai=1) và build replies nhiều cấp thủ công
        $items = BinhLuan::where('Ma_Blog', $maBlog)
            ->where('TrangThai', 1)
            ->with('nguoiDung')
            ->orderBy('created_at', 'desc')
            ->get();

        return collect($this->buildCommentTree($items));
    }
}