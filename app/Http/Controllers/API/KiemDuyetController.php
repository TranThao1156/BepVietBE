<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KiemDuyetService;
use App\Services\ThongBaoService;

class KiemDuyetController extends Controller
{
    protected $dichVu;
    protected $thongBaoService;

    public function __construct(KiemDuyetService $service, ThongBaoService $thongBaoService)
    {
        $this->dichVu = $service;
        $this->thongBaoService = $thongBaoService;
    }

    public function layDanhSachBlog(Request $request)
    {
        $trangThai = $request->query('trang_thai', 'pending');
        $data = $this->dichVu->layDanhSachBlog($trangThai);

        return response()->json([
            'status' => 200,
            'data'   => $data
        ]);
    }

    public function xuLyDuyetBlog(Request $request)
    {
        $request->validate([
            'ma_blog'   => 'required',
            'hanh_dong' => 'required|in:approve,reject' 
        ]);

        $action = $request->input('hanh_dong');

        // Gọi Service để cập nhật DB
        $ketQua = $this->dichVu->capNhatTrangThai(
            $request->input('ma_blog'),
            $action
        );

        if (!$ketQua['thanh_cong']) {
            return response()->json(['message' => $ketQua['thong_bao']], 404);
        }

        // Lấy đối tượng Blog vừa được cập nhật từ kết quả trả về
        $blog = $ketQua['du_lieu'];

        if ($blog) {
            // Mapping hành động sang trạng thái thông báo
            $trangThaiDuyet = ($action === 'approve') ? 'duyet' : 'tu_choi';

            $this->thongBaoService->guiThongBaoChoNguoiDung(
                $blog->Ma_ND,     
                'Blog',            
                $blog->Ma_Blog,   
                $blog->TieuDe,   
                $trangThaiDuyet   
            );
        }
        // ---------------------------------

        return response()->json([
            'status'  => 200,
            'message' => $ketQua['thong_bao'],
            'data'    => $blog
        ]);
    }
    // Trâm - đã thêm: API duyệt công thức (giống duyệt blog)
    public function layDanhSachCongThuc(Request $request)
    {
        $trangThai = $request->query('trang_thai', 'pending');
        $data = $this->dichVu->layDanhSachCongThuc($trangThai);
        return response()->json([
            'status' => 200,
            'data'   => $data
        ]);
    }
    public function xuLyDuyetCongThuc(Request $request)
    {
        $request->validate([
            'ma_ct'     => 'required',
            'hanh_dong' => 'required|in:approve,reject'
        ]);
        $action = $request->input('hanh_dong');
        $ketQua = $this->dichVu->capNhatTrangThaiCongThuc( // Gọi Service cập nhật DB
            $request->input('ma_ct'), 
            $action
        );
        if (!$ketQua['thanh_cong']) {
            return response()->json(['message' => $ketQua['thong_bao']], 404);
        }
        $congThuc = $ketQua['du_lieu']; // GỬI THÔNG BÁO CHO USER 
        if ($congThuc) {
            $trangThaiDuyet = ($action === 'approve') ? 'duyet' : 'tu_choi';
            $this->thongBaoService->guiThongBaoChoNguoiDung(
                $congThuc->Ma_ND, 
                'CongThuc',        
                $congThuc->Ma_CT,   
                $congThuc->TieuDe, 
                $trangThaiDuyet     
            );
        }
        return response()->json([
            'status'  => 200,
            'message' => $ketQua['thong_bao'],
            'data'    => $congThuc
        ]);
    }
    
}
