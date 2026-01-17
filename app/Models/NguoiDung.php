<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NguoiDung extends Model
{
    protected $table = 'nguoidung';
    protected $primaryKey = 'Ma_ND';
    public $timestamps = false;

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

    protected $hidden = ['MatKhau'];
}
