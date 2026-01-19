<?php

namespace App\Services;

use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NguoiDungService
{
    // Thảo - Lấy thông tin chi tiết người dùng
    public function layThongTinChiTiet($userId)
    {
        return NguoiDung::findOrFail($userId);
    }

    // Thảo - Cập nhật thông tin cá nhân
    public function capNhatThongTin(Request $request, $userId)
    {
        return DB::transaction(function () use ($request, $userId) {
            $user = NguoiDung::findOrFail($userId);

            // Các trường được phép cập nhật
            $dataUpdate = $request->only([
                'HoTen',
                'Email',
                'Sdt',
                'DiaChi',
                'GioiTinh',
                'QuocTich'
            ]);

            // Xử lý ảnh đại diện (Nếu có upload ảnh mới)
            if ($request->hasFile('AnhDaiDien')) {
                // 1. Xóa ảnh cũ nếu không phải là ảnh mặc định hoặc link online
                if ($user->AnhDaiDien && !Str::startsWith($user->AnhDaiDien, 'http')) {
                    $oldPath = 'img/NguoiDung/' . $user->AnhDaiDien;
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                // 2. Lưu ảnh mới
                $file = $request->file('AnhDaiDien');
                $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('img/NguoiDung', $filename, 'public');

                // 3. Cập nhật tên ảnh vào mảng data
                $dataUpdate['AnhDaiDien'] = $filename;
            }

            // Thực hiện update
            $user->update($dataUpdate);

            return $user;
        });
    }
}