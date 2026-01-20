<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NguoiDung;
use App\Services\QuanLyNguoiDungService;

class QuanLyController extends Controller
{
    protected $nguoiDungService;

    public function __construct(QuanLyNguoiDungService $nguoiDungService)
        {
            $this->nguoiDungService = $nguoiDungService;
        }

    // Thi - Lấy danh sách người dùng
    public function layDSNguoiDung(Request $request)
        {
            $tuKhoa  = $request->query('tuKhoa');
            $vaiTro  = $request->query('vaiTro');
            $perPage = $request->query('perPage', 10);

            $data = $this->nguoiDungService->layDSNguoiDung(
                $tuKhoa,
                $vaiTro,
                $perPage
            );

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách người dùng thành công',
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                    'per_page'     => $data->perPage(),
                    'total'        => $data->total(),
                ]
            ]);
        }



    // Thi - Tìm kiếm theo tên hoặc email
    // public function timKiemNguoiDung(Request $request)
    // {
    //     $tuKhoa = $request->query('tuKhoa', '');

    //     $data = $this->nguoiDungService->timKiemNguoiDung($tuKhoa);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Tìm kiếm người dùng thành công',
    //         'data' => $data
    //     ]);
    // }

    // Thi -Lọc theo vai trò
    // public function locNguoiDungTheoVaiTro(Request $request)
    // {
    //     $vaiTro = $request->query('vaiTro');

    //     // không chọn vai trò → trả tất cả
    //     if ($vaiTro === null || $vaiTro === '') {
    //         $data = $this->nguoiDungService->layDSNguoiDung();
    //     } else {
    //         $data = $this->nguoiDungService->locNguoiDungTheoVaiTro($vaiTro);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Lấy danh sách người dùng thành công',
    //         'data' => $data
    //     ]);
    // }
}