<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhMuc extends Model
{
    use HasFactory;
    protected $table = 'danhmuc';
    protected $primaryKey = 'Ma_DM'; 

    protected $fillable = [
        'TenDM',     
        'TrangThai', 
        'LoaiDM'     
    ];
}