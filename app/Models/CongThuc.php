<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//Thảo 
class CongThuc extends Model
{
    use HasFactory;

    protected $table = 'congthuc';
    public $timestamps = true;
    protected $primaryKey = 'Ma_CT';

    protected $fillable = [
        'TenMon',
        'MoTa',
        'KhauPhan',
        'DoKho',
        'ThoiGianNau',
        'HinhAnh',
        'TrangThaiDuyet',
        'SoLuotXem',
        'Ma_VM',
        'Ma_LM',
        'Ma_DM',
        'Ma_ND',
        'TrangThai'
    ];

    public function nguoi_dung() {
        return $this->belongsTo(NguoiDung::class, 'Ma_ND', 'Ma_ND');
    }
    
    public function danh_muc() {
        return $this->belongsTo(DanhMuc::class, 'Ma_DM', 'Ma_DM');
    }
}
