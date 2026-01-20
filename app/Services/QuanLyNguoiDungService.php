<?php
namespace App\Services;

use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;


class QuanLyNguoiDungService
{
    // Thi - Lấy danh sách toàn bộ người dùng hệ thống 
    public function layDSNguoiDung($tuKhoa = null, $vaiTro = null, $perPage = 10)
    {
        $query = NguoiDung::where('TrangThai', 1);

        // Tìm kiếm
        if ($tuKhoa) {
            $query->where(function ($q) use ($tuKhoa) {
                $q->where('HoTen', 'like', "%{$tuKhoa}%")
                ->orWhere('Email', 'like', "%{$tuKhoa}%");
            });
        }

        // Lọc vai trò
        if ($vaiTro !== null && $vaiTro !== '') {
            $query->where('VaiTro', $vaiTro);
        }

        // Phân trang
        $users = $query->orderByDesc('created_at')
            ->paginate($perPage);

        // Map lại data + giữ pagination
        $users->getCollection()->transform(function ($user) {
            return [
                'Ma_ND' => $user->Ma_ND,
                'HoTen' => $user->HoTen,
                'Email' => $user->Email,
                'VaiTro' => $user->VaiTro,
                'AnhDaiDien' => $user->AnhDaiDien
                    ? asset('storage/img/NguoiDung/' . rawurlencode($user->AnhDaiDien))
                    : null,
            ];
        });

        return $users;
    }
}