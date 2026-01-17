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
}