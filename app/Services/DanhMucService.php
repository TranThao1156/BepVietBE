<?php

namespace App\Services;

use App\Models\DanhMuc;

class DanhMucService
{
    public function getList($request)
    {
        $query = DanhMuc::query();

        // Xử lý tìm kiếm (nếu FE gửi lên tham số search)
        if ($request->filled('search')) {
            $keyword = $request->input('search');
            $query->where('TenDM', 'like', "%{$keyword}%");
        }

        // Sắp xếp mới nhất lên đầu, phân trang 10 dòng
        return $query->orderBy('created_at', 'asc')->paginate(10);
    }
    public function create(array $data)
    {
        // Dùng create của Eloquent để thêm mới
        return DanhMuc::create([
            'TenDM'     => $data['TenDM'],
            'LoaiDM'    => $data['LoaiDM'] ?? 'Món ăn', // Mặc định nếu thiếu
            'TrangThai' => $data['TrangThai'] ?? 1,     // Mặc định là Hiển thị (1)
        ]);
    }

    public function getDetail($id)
    {
        return DanhMuc::findOrFail($id);
    }
    /**
     * Cập nhật danh mục
     */
    public function update($id, array $data)
    {
        $danhMuc = DanhMuc::findOrFail($id);
        
        // Dùng fill để gán dữ liệu, chỉ những trường nào có gửi lên mới update
        $danhMuc->fill([
            'TenDM'     => $data['TenDM'] ?? $danhMuc->TenDM,
            'LoaiDM'    => $data['LoaiDM'] ?? $danhMuc->LoaiDM,
            'TrangThai' => $data['TrangThai'] ?? $danhMuc->TrangThai,
        ]);
        
        $danhMuc->save();
        
        return $danhMuc;
    }

    /**
     * Xóa danh mục
     */
    public function delete($id)
    {
        $danhMuc = DanhMuc::findOrFail($id);
        
        // Thay vì xóa hẳn: return $danhMuc->delete();
        
        // Ta cập nhật trạng thái về 0
        $danhMuc->update(['TrangThai' => 0]); 
        
        return $danhMuc;
    }
}