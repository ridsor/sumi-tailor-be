<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    
    protected $table = 'orders';
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();

        static::created(function($order) {
            $order->item_code = 'ST' . sprintf('%03d',$order->id);
            $order->save();
        });
    }
}
