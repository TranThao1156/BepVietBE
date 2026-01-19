<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NguyenLieu extends Model
{
    protected $table = 'nguyenlieu';
    protected $primaryKey = 'Ma_NL';

    protected $fillable = [
        'TenNguyenLieu',
        'DonViDo',
        'TrangThai'
    ];

    public $timestamps = true;

    public function congThuc()
    {
        return $this->belongsToMany(
            CongThuc::class,
            'congthuc_nguyenlieu',
            'Ma_NL',
            'Ma_CT'
        )->withPivot('SoLuong');
    }
}
