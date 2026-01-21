<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\CongThuc;

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

    // Trâm - đã thêm: lấy danh sách công thức theo trạng thái duyệt (giống duyệt blog)
    public function layDanhSachCongThuc($trangThaiFrontend)
    {
        // Trâm - đã thêm: map trạng thái giống duyệt blog
        $mapTrangThai = [
            'pending'  => 'Chờ duyệt',
            'approved' => 'Chấp nhận',
            'rejected' => 'Từ chối'
        ];

        $trangThaiDB = $mapTrangThai[$trangThaiFrontend] ?? 'Chờ duyệt';

        return CongThuc::with(['nguoiDung:Ma_ND,HoTen,AnhDaiDien'])
            ->where('TrangThaiDuyet', $trangThaiDB)
            // Trâm - đã sửa: sắp xếp giống kiểm duyệt bài viết (ưu tiên mới nhất theo created_at)
            // ->orderBy('created_at', 'desc')
            // Trâm - đã thêm: nếu trùng created_at thì ưu tiên mã nhỏ hơn
            ->orderBy('Ma_CT', 'asc')
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

    // Trâm - đã thêm: xử lý duyệt/từ chối công thức
    public function capNhatTrangThaiCongThuc($maCT, $hanhDong)
    {
        $congThuc = CongThuc::find($maCT);

        if (!$congThuc) {
            return [
                'thanh_cong' => false,
                'thong_bao'  => 'Không tìm thấy công thức'
            ];
        }

        if ($hanhDong === 'approve') {
            $congThuc->TrangThaiDuyet = 'Chấp nhận';
            $congThuc->TrangThai = 1;
        } elseif ($hanhDong === 'reject') {
            $congThuc->TrangThaiDuyet = 'Từ chối';
            $congThuc->TrangThai = 0;
        }

        $congThuc->save();

        return [
            'thanh_cong' => true,
            'thong_bao'  => 'Cập nhật thành công',
            'du_lieu'    => $congThuc
        ];
    }
}