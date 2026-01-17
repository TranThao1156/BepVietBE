<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\NguoiDung;

class CongThuc extends Model
{
    //16/01/2026 Thi tạo model Công Thức

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
    
    public function nguoiDung() {
        return $this->belongsTo(NguoiDung::class, 'Ma_ND', 'Ma_ND');
    }
    public function vungMien() {
        return $this->belongsTo(VungMien::class, 'Ma_VM', 'Ma_VM');
    }
    public function loaiMon() {
        return $this->belongsTo(LoaiMon::class, 'Ma_LM', 'Ma_LM');
    }
    public function danhMuc() {
        return $this->belongsTo(DanhMuc::class, 'Ma_DM', 'Ma_DM');
    }
    public function danhGia()
    {
        return $this->hasMany(DanhGia::class, 'Ma_CT', 'Ma_CT');
    }

}
