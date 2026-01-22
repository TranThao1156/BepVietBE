<?php

namespace App\Services;

use App\Models\ThongBao;
use App\Models\NguoiDung;


class ThongBaoService
{
    /**
     * Gửi thông báo cho tất cả Admin khi có bài mới cần duyệt
     * @param string $loai 'CongThuc' hoặc 'Blog'
     * @param int $idBaiViet ID của bài viết
     * @param string $tenBaiViet Tên bài viết để hiển thị
     * @param string $tenNguoiGui Tên người đăng bài
     */
    public function guiThongBaoChoAdmin($loai, $idBaiViet, $tenBaiViet, $tenNguoiGui)
    {
        $admins = NguoiDung::where('VaiTro', 0)->get();
        foreach ($admins as $admin) {
            ThongBao::create([
                'Ma_ND' => $admin->Ma_ND, 
                'TieuDe' => 'Yêu cầu duyệt bài mới',
                'NoiDung' => "Người dùng {$tenNguoiGui} vừa đăng {$loai}: {$tenBaiViet}. Vui lòng kiểm duyệt.",
                'TrangThai' => 0,
                'LoaiThongBao' => $loai,
                'MaLoai' => $idBaiViet
            ]);
        }
    }

    /**
     * Gửi thông báo cho User khi bài viết được Duyệt hoặc Từ chối
     * @param int $maNguoiDung ID người nhận
     * @param string $loai 'CongThuc' hoặc 'Blog'
     * @param int $idBaiViet ID bài viết
     * @param string $tenBaiViet Tên bài viết
     * @param string $trangThaiDuyet 'duyet' hoặc 'tu_choi'
     */
    public function guiThongBaoChoNguoiDung($maNguoiDung, $loai, $idBaiViet, $tenBaiViet, $trangThaiDuyet)
    {
        $tieuDe = ($trangThaiDuyet == 'duyet') ? 'Bài viết đã được duyệt' : 'Bài viết bị từ chối';

        $noiDung = ($trangThaiDuyet == 'duyet')
            ? "Tuyệt vời! Bài viết '{$tenBaiViet}' của bạn đã được duyệt và hiển thị công khai."
            : "Rất tiếc, bài viết '{$tenBaiViet}' của bạn đã bị từ chối do vi phạm quy tắc cộng đồng.";

        ThongBao::create([
            'Ma_ND' => $maNguoiDung,
            'TieuDe' => $tieuDe,
            'NoiDung' => $noiDung,
            'TrangThai' => 0,
            'LoaiThongBao' => $loai,
            'MaLoai' => $idBaiViet
        ]);
    }
}
