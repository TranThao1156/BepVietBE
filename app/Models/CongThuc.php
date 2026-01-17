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
    public $incrementing = true;
    protected $keyType = 'int';
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

    public function nguoidung()
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_ND', 'Ma_ND');
    }

    public function danh_muc()
    {
        return $this->belongsTo(DanhMuc::class, 'Ma_DM', 'Ma_DM');
    }

    public function loaiMon()
    {
        return $this->belongsTo(LoaiMon::class, 'Ma_LM');
    }

    public function vungMien()
    {
        return $this->belongsTo(VungMien::class, 'Ma_VM');
    }

    public function nguyenLieu()
    {
        return $this->belongsToMany(
            NguyenLieu::class,
            'nl_cthuc',
            'Ma_CT',
            'Ma_NL'
        )->withPivot('DinhLuong');
    }

    public function buocThucHien()
    {
        return $this->hasMany(
            BuocThucHien::class,
            'Ma_CT',
            'Ma_CT'
        )->orderBy('STT');
    }
}
