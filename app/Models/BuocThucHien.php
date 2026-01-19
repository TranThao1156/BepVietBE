<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuocThucHien extends Model
{
    protected $table = 'buocthuchien';
    protected $primaryKey = 'Ma_BTH';

    protected $fillable = [
        'Ma_CT',
        'STT',
        'HinhAnh',
        'NoiDung'
    ];

    public function congThuc()
    {
        return $this->belongsTo(CongThuc::class, 'Ma_CT');
    }
}
