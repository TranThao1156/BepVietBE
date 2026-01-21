<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NguoiDung;
use App\Services\QuanLyNguoiDungService;
use Illuminate\Database\Eloquent\ModelNotFoundException;


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
    // Thi - Lấy chi tiết người dùng
    public function layThongTinCaNhan($id)
    {
        $data = $this->nguoiDungService->layChiTietNguoiDung($id);
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết người dùng thành công',
            'data' => $data
        ]);;
    }
    // Thi - Cập nhật thông tin người dùng
    public function capNhatNguoiDung(Request $request, $id)
    {
        $data = $this->nguoiDungService->capNhatNguoiDung($request, $id);
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin người dùng thành công',
            'data' => $data
        ]);;
    }
    // Thi - Xoá người dùng 
    public function xoaNguoiDung(Request $request, $id)
    {
        try {

            $result = $this->nguoiDungService->xoaNguoiDung($id);

            return response()->json([
                'success' => true,
                'message' => 'Xoá người dùng thành công',
                'data' => $result
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    // Thi - tạo tài khoản người dùng
    public function themNguoiDung(Request $request)
    {
        // Validate dữ liệu 
        $request->validate([
            'TenTK'   => 'required|unique:nguoidung,TenTK',
            'Email'   => 'required|email|unique:nguoidung,Email',
            'MatKhau' => 'required|min:6',
            'HoTen'   => 'required',
            'VaiTro'  => 'required|in:0,1',
            'Sdt'     => 'required|numeric',
        ], [
            'required' => 'Vui lòng nhập đầy đủ các trường bắt buộc.',
            'email'    => 'Email không đúng định dạng.',
            'unique'   => 'Dữ liệu đã tồn tại trong hệ thống.',
            'min'      => 'Mật khẩu phải từ 6 kí tự.',
        ]);

        $user = $this->nguoiDungService->themNguoiDung($request);
 
        return response()->json([
            'success' => true,
            'message' => 'Tạo người dùng thành công',
            'data'    => $user
        ]);
    }
}