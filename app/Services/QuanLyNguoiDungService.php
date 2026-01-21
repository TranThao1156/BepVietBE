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

    // Thi - Lấy chi tiết người dùng theo Ma_ND
    public function layChiTietNguoiDung($maND)
    {
        $user = NguoiDung::where('TrangThai', 1)
            ->findOrFail($maND);

        return [
            'Ma_ND'      => $user->Ma_ND,
            'TenTK'      => $user->TenTK,
            'HoTen'      => $user->HoTen,
            'Email'      => $user->Email,
            'Sdt'        => $user->Sdt,
            'DiaChi'     => $user->DiaChi,
            'GioiTinh'   => $user->GioiTinh,
            'QuocTich'   => $user->QuocTich,
            'VaiTro'     => $user->VaiTro,
            'AnhDaiDien' => $user->AnhDaiDien
                ? asset('storage/img/NguoiDung/' . rawurlencode($user->AnhDaiDien))
                : null,
        ];
    }

    // Thi - Cập nhật thông tin người dùng theo Ma_ND
    public function capNhatNguoiDung(Request $request, $maND)
    {
        $user = NguoiDung::where('TrangThai', 1)
            ->findOrFail($maND);

        // Cập nhật thông tin
        $user->HoTen    = $request->input('HoTen', $user->HoTen);
        $user->Email    = $request->input('Email', $user->Email);
        $user->Sdt      = $request->input('Sdt', $user->Sdt);
        $user->VaiTro   = $request->input('VaiTro',$user->VaiTro);
        $user->DiaChi   = $request->input('DiaChi', $user->DiaChi);
        $user->GioiTinh = $request->input('GioiTinh', $user->GioiTinh);
        $user->QuocTich = $request->input('QuocTich', $user->QuocTich);

        // Lưu ảnh đại diện nếu có
        if ($request->hasFile('AnhDaiDien')) {
            // Xóa ảnh cũ nếu có
            if ($user->AnhDaiDien && Storage::exists('public/img/NguoiDung/' . $user->AnhDaiDien)) {
                Storage::delete('public/img/NguoiDung/' . $user->AnhDaiDien);
            }

            // Lưu ảnh mới
            $file = $request->file('AnhDaiDien');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/img/NguoiDung', $filename);
            $user->AnhDaiDien = $filename;
        }

        $user->save();

        return $this->layChiTietNguoiDung($maND);
    }

}