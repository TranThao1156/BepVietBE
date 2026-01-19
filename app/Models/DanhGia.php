<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(CongThuc::class, 'Ma_CT');
    }

    public function nguoiDung()
    {
        return $this->belongsTo(User::class, 'Ma_ND');
    }
}
