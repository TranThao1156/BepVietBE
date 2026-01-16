<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CongThucService;

// Thảo
class CongThucController extends Controller
{
    protected $congThucService;

    public function __construct(CongThucService $congThucService)
    {
        $this->congThucService = $congThucService;
    }
    // Thảo - Lấy danh sách công thức
    public function index(Request $request)
    {
        $duLieu = $this->congThucService->layDanhSachCongThuc([
            'Ma_DM'   => $request->Ma_DM,
            'Ma_LM'   => $request->Ma_LM,
            'keyword' => $request->keyword,
            'sap_xep' => $request->sap_xep,
            'limit'   => $request->limit
        ]);

        return response()->json([
            'message' => 'Lấy danh sách công thức thành công',
            'data'    => $duLieu
        ]);
    }
    
    // Thảo - Thêm công thức
    public function themCongThuc(Request $request)
    {
        $request->validate([
            'TenMon'      => 'required|string|max:255',
            'KhauPhan'    => 'required|integer|min:1',
            'DoKho'       => 'required|string|min:1|max:100',
            'ThoiGianNau' => 'required|integer|min:1',
            'Ma_LM'       => 'required|integer',
            'Ma_DM'       => 'required|integer',
            'Ma_ND'       => 'required|integer'
        ], [
            'TenMon.required' => 'Tên món không được để trống',
            'KhauPhan.required' => 'Khẩu phần không được để trống',
            'DoKho.required' => 'Độ khó không được để trống',
            'ThoiGianNau.required' => 'Thời gian nấu không được để trống'
        ]);

        $congThuc = $this->congThucService->themCongThuc($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Thêm công thức mới thành công',
            'data' => $congThuc
        ], 201);
    }
}
