<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cookbook extends Model
{
    use HasFactory;
//Khôiiiiiiiiiiiii
    protected $table = 'cookbook';
    protected $primaryKey = 'Ma_CookBook';
    public $timestamps = false;
    protected $fillable = [
        'Ma_ND',
        'TenCookBook',
        'TrangThai',
        'AnhBia'
    ];
    protected $appends = ['anh_bia_url'];

    public function getAnhBiaUrlAttribute()
    {
        if ($this->AnhBia) {
            return asset('uploads/cookbooks/' . $this->AnhBia);
        }
        return 'https://placehold.co/600x400?text=No+Image';
    }
}