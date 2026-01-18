<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $table = 'blog'; 
    protected $primaryKey = 'Ma_Blog'; // Quan trọng: Khai báo khóa chính theo DB
    
    // Nếu bảng không có created_at, updated_at thì set false
    // public $timestamps = false; 

    protected $fillable = [
        'Ma_ND',
        'TieuDe',
        'ND_ChiTiet',
        'HinhAnh',
        'TrangThaiDuyet', // Cột Service sẽ update
        'TrangThai'
    ];
}