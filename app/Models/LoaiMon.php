<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoaiMon extends Model
{
    protected $table = 'loaimon';
    protected $primaryKey = 'Ma_LM';

    protected $fillable = [
        'TenLoaiMon'
    ];

    public $timestamps = false;

    public function congThuc()
    {
        return $this->hasMany(CongThuc::class, 'Ma_LM');
    }
}
