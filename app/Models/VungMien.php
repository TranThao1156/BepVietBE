<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VungMien extends Model
{
    protected $table = 'vungmien';
    protected $primaryKey = 'Ma_VM';

    protected $fillable = [
        'TenVungMien'
    ];

    public $timestamps = false;

    public function congThuc()
    {
        return $this->hasMany(CongThuc::class, 'Ma_VM');
    }
}
