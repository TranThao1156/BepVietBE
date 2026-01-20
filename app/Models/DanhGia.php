<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\NguoiDung;

class DanhGia extends Model
{
    // 17/01/2026 Thi tạo model Đánh Giá
    protected $table = 'danhgia';
    protected $primaryKey = 'Ma_DG';

    protected $fillable = [
        'Ma_ND',
        'Ma_CT',
        'SoSao'
    ];

    public function congThuc()
    {
        // Trâm - đã sửa: chỉ rõ khóa ngoại/khóa chính cho quan hệ công thức
        return $this->belongsTo(CongThuc::class, 'Ma_CT', 'Ma_CT');
    }

    public function nguoiDung()
    {
        // Trâm - đã sửa: trả về đúng model NguoiDung (không phải User) để load HoTen/AnhDaiDien
        return $this->belongsTo(NguoiDung::class, 'Ma_ND', 'Ma_ND');
    }
}
