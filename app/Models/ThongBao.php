<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    use HasFactory;

    protected $table = 'thongbao';
    protected $primaryKey = 'Ma_TB';
    
    protected $fillable = [
        'Ma_ND',
        'TieuDe',
        'NoiDung',
        'TrangThai',    // 0: Chưa đọc, 1: Đã đọc
        'LoaiThongBao', // 'Cong thuc' hoặc 'Blog'
        'MaLoai'        // ID của công thức hoặc blog
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_ND', 'Ma_ND');
    }
}