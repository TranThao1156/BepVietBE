<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    use HasFactory;

    // Khai báo tên bảng trùng khớp với ảnh bạn gửi
    protected $table = 'thongbao';

    // Khai báo khóa chính
    protected $primaryKey = 'Ma_TB';

    // Các trường được phép ghi dữ liệu
    protected $fillable = [
        'Ma_ND',
        'TieuDe',
        'NoiDung',
        'TrangThai',
        'LoaiThongBao',
        'MaLoai'
    ];
    public $timestamps = false;
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_ND', 'Ma_ND');
    }
}
