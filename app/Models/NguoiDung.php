<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class NguoiDung extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'nguoidung';
    protected $primaryKey = 'Ma_ND';
    public $timestamps = true;

    protected $fillable = [
        'TenTK',
        'MatKhau',
        'HoTen',
        'AnhDaiDien',
        'Email',
        'Sdt',
        'DiaChi',
        'GioiTinh',
        'QuocTich',
        'VaiTro',
        'TrangThai'
    ];
    protected $hidden = ['MatKhau', 'remember_token'];
    public function getAuthPassword()
    {
        return $this->MatKhau;
    }
}
