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
}
