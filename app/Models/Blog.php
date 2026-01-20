<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;


class Blog extends Model
{
    // 17/01/2026 Thi tạo model Blog
    use HasFactory;

    protected $table = 'blog'; // đổi nếu tên bảng khác

    protected $primaryKey = 'Ma_Blog';

    public $timestamps = true;

    protected $fillable = [
        'Ma_ND',
        'TieuDe',
        'ND_ChiTiet',
        'HinhAnh',
        'TrangThaiDuyet',
        'TrangThai'
    ];
    // Quan hệ với người dùng (User)
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_ND', 'Ma_ND');
    }
    public function binhLuan()
    {
        return $this->hasMany(BinhLuan::class, 'Ma_Blog', 'Ma_Blog');
    }
    // Thi 
    // Tự tạo slug khi tạo mới
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            $blog->Slug = Str::slug($blog->TieuDe) . '-' . time();
            $blog->TrangThaiDuyet = 'cho_duyet';
        });
    }
    // Quan hệ: Một bình luận có nhiều câu trả lời (replies)
    public function replies()
    {
        return $this->hasMany(BinhLuan::class, 'parent_id', 'Ma_BL');
    }
}
