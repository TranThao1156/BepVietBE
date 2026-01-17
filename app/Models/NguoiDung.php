<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class NguoiDung extends Model
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
    
}
