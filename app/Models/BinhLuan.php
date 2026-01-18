<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BinhLuan extends Model
{
    // Thi - Model Bình luận 
    protected $table = 'binhluan';
    protected $primaryKey = 'Ma_BL';
    public $timestamps = true;

    protected $fillable = [
        'Ma_Blog',
        'Ma_CT',
        'Ma_ND',
        'NoiDungBL',
        'LoaiBL',
        'TrangThai'
    ];

    // Quan hệ với Blog
    public function blog()
    {
        return $this->belongsTo(Blog::class, 'Ma_Blog', 'Ma_Blog');
    }
    // Quan hệ với Người dùng
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_ND', 'Ma_ND');    
    }
    // Quan hệ với Công thức
    public function congThuc()
    {
        return $this->belongsTo(CongThuc::class, 'Ma_CT', 'Ma_CT');    
    }
}