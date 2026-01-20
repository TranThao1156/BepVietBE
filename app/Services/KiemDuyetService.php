<?php

namespace App\Services;

use App\Models\Blog;

class KiemDuyetService
{
    public function layDanhSachBlog($trangThaiFrontend)
    {
        // SỬA LẠI KHÚC NÀY ĐỂ KHỚP VỚI DATABASE CỦA BẠN
        $mapTrangThai = [
            'pending'  => 'Chờ duyệt',
            'approved' => 'Chấp nhận',
            'rejected' => 'Từ chối'    
        ];

        // Mặc định tìm 'Chờ duyệt' nếu không gửi gì lên
        $trangThaiDB = $mapTrangThai[$trangThaiFrontend] ?? 'Chờ duyệt';

        return Blog::where('TrangThaiDuyet', $trangThaiDB)
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    public function capNhatTrangThai($maBlog, $hanhDong)
    {
        $baiViet = Blog::find($maBlog);

        if (!$baiViet) {
            return [
                'thanh_cong' => false,
                'thong_bao'  => 'Không tìm thấy bài viết'
            ];
        }

        // Cập nhật đúng từ khóa tiếng Việt có dấu vào DB
        if ($hanhDong === 'approve') {
            $baiViet->TrangThaiDuyet = 'Chấp nhận'; //
            $baiViet->TrangThai = 1; 
        } elseif ($hanhDong === 'reject') {
            $baiViet->TrangThaiDuyet = 'Từ chối';   //
            $baiViet->TrangThai = 0; 
        }

        $baiViet->save();

        return [
            'thanh_cong' => true,
            'thong_bao'  => 'Cập nhật thành công',
            'du_lieu'    => $baiViet
        ];
    }
}