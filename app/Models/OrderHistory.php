<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    protected $table = 'history_order';
    protected $primeryKey = 'item_code';
    public $incrementing = false;
    protected $guarded = [];
}
