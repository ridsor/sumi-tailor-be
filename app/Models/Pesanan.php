<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;
    
    protected $table = 'pesanan';
    protected $guarded = ['id'];

    public static function boot()
{
    parent::boot();

    static::created(function($pesanan) {
        $pesanan->kode_barang = 'ST' . sprintf('%03d',$pesanan->id);
        $pesanan->save();
    });
}
}
